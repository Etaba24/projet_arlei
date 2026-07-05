# Documentation technique — Simulation IA & Technologies du projet

> **Partie I** — [Moteur de Simulation IA](#moteur-de-simulation-ia--documentation-technique) : fonctionnement détaillé du moteur prédictif.
> **Partie II** — [Technologies du projet](#partie-ii--technologies-utilisées-dans-le-projet) : inventaire complet de la stack et utilité de chaque brique.

---

# Moteur de Simulation IA — Documentation technique

> **Fichiers concernés**
> | Rôle | Fichier |
> |---|---|
> | Moteur (toute l'intelligence) | `app/Services/ProductionSimulator.php` |
> | Endpoint API | `app/Http/Controllers/SimulationController.php` |
> | Route | `POST /api/simulation-op` (protégée par `perm:production.creer`) |
> | Frontend (appel + rapport) | `resources/views/ordre-productions/create.blade.php` |

---

## 1. Vue d'ensemble

Quand l'utilisateur clique sur **« Lancer la Simulation IA »** dans le formulaire de
création d'un ordre de production (OP), le navigateur envoie la configuration
(lots sélectionnés, phases, cible) au serveur. Le moteur `ProductionSimulator`
exécute alors **6 étapes** et renvoie un rapport probabiliste complet.

```
┌─────────────────────────────  NAVIGATEUR  ─────────────────────────────┐
│  Formulaire OP : lots + phases + cible                                 │
│        │  POST /api/simulation-op (JSON)          animation en cours…  │
└────────┼────────────────────────────────────────────────────────────────┘
         ▼
┌─────────────────────────────  SERVEUR  ────────────────────────────────┐
│  ProductionSimulator::simuler()                                        │
│                                                                         │
│  ① Analyse des lots        (qualité, âge, péremption, stock)           │
│  ② Chargement historique   (rendements + durées réels des phases)      │
│  ③ Paramétrage des phases  (rendement appris par transformation×machine)│
│  ④ Auto-calibration        (prédictions passées vs productions réelles)│
│  ⑤ Monte Carlo             (1 500 scénarios stochastiques)             │
│  ⑥ Risques & recommandations + indice de confiance                     │
│                                                                         │
│  → JSON : P10/P50/P90, proba cible, confiance, risques, trace…         │
└─────────────────────────────────────────────────────────────────────────┘
```

**Principe fondamental : le moteur n'utilise aucune constante inventée.**
Chaque paramètre est estimé à partir des données réellement enregistrées dans
l'application. Les constantes de repli (`DEFAULT_YIELD`, `BASE_PHASE_MIN`) ne
servent que lorsqu'aucun historique n'existe encore.

---

## 2. Les données que le moteur exploite

| Source (table) | Donnée | Usage |
|---|---|---|
| `phase_productions` (statut `valide`) | `quantite_obtenue / quantite_mp_phase` | **Rendement réel observé** de chaque phase |
| `phase_productions` | `date_debut` → `date_fin` | **Durée réelle** de chaque phase (5 min à 48 h retenues) |
| `phase_productions` | statuts `interrompu` / `annule` | **Taux de fiabilité** de chaque machine |
| `phase_productions` (statuts actifs) | comptage par machine | **Charge actuelle** des machines (file d'attente) |
| `lots_matieres_premieres` | âge, `date_peremption`, `quantite_disponible`, statut | **Perte qualité** pondérée + risques FIFO |
| `machines` | `etat` (`en_marche` / `arret` / `en_panne`) | Modificateur de rendement + risques bloquants |
| `ordre_productions` | `quantite_pf_estimee` (prédictions passées) | **Auto-calibration** du modèle |
| `conditionnements` (statut `valide`) | `quantite_produite` (production réelle) | **Auto-calibration** du modèle |

---

## 3. Détail des 6 étapes

### ① Analyse des lots

Pour chaque lot sélectionné avec sa quantité `q` :

- La **perte qualité** vient de la courbe d'âge du modèle `LotMatierePremiere`
  (`Fraîche` 1,5 % → `Périmée` 35 %).
- **Pénalité de péremption** : lot périmé → perte forcée à ≥ 35 % + risque
  *critique* ; péremption ≤ 15 jours → +3 % de perte + risque *attention*
  (consigne FIFO).
- Contrôles bloquants : quantité demandée > `quantite_disponible`, lot en
  `quarantaine` → risques *critiques*.

La perte lot globale est la **moyenne pondérée par les quantités prélevées** :

```
lotLoss = Σ (perteᵢ × qᵢ) / Σ qᵢ        (plafonnée à 45 %)
```

### ② Chargement de l'historique

Le moteur lit **toutes les phases validées** ayant des quantités saisies et
construit, pour chaque observation, un **poids temporel à demi-vie** :

```
poids = 0,5 ^ (âge_en_jours / 90)
```

→ une observation d'aujourd'hui pèse 1, une de 90 jours pèse 0,5, une de
180 jours pèse 0,25. **Le modèle suit donc la dérive de l'usine** : si une
machine s'use ou si une équipe progresse, les prédictions suivent en quelques
semaines, sans intervention.

Les observations sont agrégées à deux niveaux :
- `parTransfoMachine["transformation-machine"]` — le plus précis ;
- `parTransfo[transformation]` — repli si la machine n'a pas d'historique.

On calcule aussi le **prior global** (rendement moyen pondéré toutes phases
confondues), la **fiabilité par machine** (proportion de phases interrompues ou
annulées) et la **charge instantanée** (phases `en_attente`/`en_cours` par machine).

### ③ Paramétrage de chaque phase (cœur statistique)

Pour chaque phase du scénario, le rendement est choisi selon la cascade :

```
1. historique transformation × machine   (le plus spécifique)
2. historique transformation             (toutes machines)
3. prior global                          (aucun historique)
```

Puis on applique le **lissage bayésien (shrinkage)** — la protection contre les
petits échantillons :

```
rendement = (n_eff × moyenne_observée + K × prior) / (n_eff + K)      K = 3
```

- `n_eff` = somme des poids temporels (l'« effectif efficace »).
- Avec 0 observation → le prior s'impose.
- Avec 2 observations récentes → mélange 40 % observé / 60 % prior.
- Avec 20 observations → l'historique domine presque totalement.

**Pourquoi ?** Sans shrinkage, une machine ayant eu *une seule* phase
exceptionnellement bonne (98 %) serait prédite à 98 % pour toujours. Le
shrinkage dit : « prometteur, mais je n'y crois qu'à proportion des preuves ».

Modificateurs appliqués ensuite :

| Situation | Effet |
|---|---|
| Machine `en_marche` | ×1,00 |
| Machine `arret` | ×0,95 + durée ×1,15 + risque *attention* |
| Machine `en_panne` | ×0,78 + durée ×1,60 + risque *critique* (phase bloquée) |
| Taux d'interruption historique > 25 % (≥ 3 phases) | malus proportionnel + risque *attention* |
| Machine déjà sur ≥ 2 phases actives | risque *info* (file d'attente) |
| Phase sans machine | ×0,95 + risque *info* (estimation moins précise) |

**Durée de phase** : moyenne pondérée des durées réelles du couple
transformation×machine (repli : transformation seule, puis 90 min), ajustée à
la quantité par une **loi d'échelle sous-linéaire** :

```
durée = durée_historique × √(quantité_scénario / quantité_historique_moyenne)
        (facteur borné entre ×0,6 et ×1,8)
```

Doubler la quantité n'a jamais doublé le temps de traitement — la racine carrée
modélise l'économie d'échelle sans risquer d'extrapolation absurde.

**Suggestion de machine** : pour chaque phase, le moteur balaye les autres
machines `en_marche` ayant un historique sur la même transformation ; si l'une
d'elles a un rendement lissé supérieur d'au moins 3 points, il recommande la
permutation (une suggestion max par phase).

### ④ Auto-calibration (le modèle apprend de ses erreurs)

Le moteur compare, sur tous les OP terminés, sa **prédiction d'époque**
(`ordre_productions.quantite_pf_estimee`) à la **production réelle**
(`conditionnements.quantite_produite`, statut `valide`) :

```
biais = moyenne_géométrique_pondérée( réel / prédit )      borné à [0,70 ; 1,30]
```

- Moyenne **géométrique** : les ratios (0,5 et 2,0 doivent se compenser) se
  moyennent en log, pas en arithmétique.
- Même pondération temporelle à demi-vie 90 j.
- Le biais multiplie l'estimation finale : si le modèle a historiquement
  surestimé de 20 %, toutes les nouvelles prédictions sont réduites d'autant.
- On calcule aussi la **MAPE** (erreur absolue moyenne en %) qui alimente
  l'indice de confiance.

> C'est cette boucle qui rend le système **auto-apprenant** : chaque OP
> conditionné et validé raffine automatiquement les prédictions suivantes.
> Aucun réentraînement manuel n'est nécessaire.

### ⑤ Simulation Monte Carlo (1 500 scénarios)

Un chiffre unique cache l'incertitude. Le moteur exécute donc 1 500 fois la
chaîne complète en **tirant chaque paramètre dans sa distribution** :

```
pour chaque scénario r ∈ [1..1500] :
    perte_lot ~  N(lotLoss, σ_lot)            bornée [0 ; 0,5]
    qté = MP_totale × (1 − perte_lot)
    pour chaque phase p :
        rendement ~ N(μₚ, σₚ)                 borné [0,30 ; 1,0]
        qté = qté × rendement
        durée += N(durée_p, 20 %)             bornée ≥ 10 min
    PF_r  = qté × biais
```

- `σₚ` est l'**écart-type réellement observé** dans l'historique de la phase
  (repli 6 %) : une machine irrégulière produit un intervalle large, une
  machine stable un intervalle serré.
- Tirages normaux par transformée de **Box-Muller**.

Des 1 500 résultats triés, on extrait :

| Sortie | Signification |
|---|---|
| **P10** | scénario pessimiste — 90 % de chances de faire mieux |
| **P50** | production probable (médiane) — c'est le « PF estimé » affiché |
| **P90** | scénario optimiste — 10 % de chances de faire mieux |
| **probaCible** | proportion des scénarios où `PF ≥ quantite_pf_cible` |
| durées P10/P50/P90 | idem pour la durée totale |

### ⑥ Verdict cible, confiance, risques

- **Cible** : si `probaCible ≥ 85 %` → plan robuste. Sinon, le moteur inverse
  la chaîne de rendements pour calculer la **quantité de MP exacte à injecter** :

  ```
  MP_requis = cible / [ (1 − lotLoss) × Π rendementₚ ]
  ```

  et émet une recommandation chiffrée (+ risque *attention* ou *critique* si
  la probabilité est < 40 %).

- **Indice de confiance (0–100)** :

  ```
  30  (base)
  + min(35, n_eff_total × 4)        ← volume d'historique pertinent
  + max(0, 20 − MAPE × 100)         ← précision passée du modèle
  − dispersion Monte Carlo          ← (P90−P10)/P50, plafonné à 15
  − 8 par risque critique
  ```
  borné à [10 ; 97]. **Il monte mécaniquement à mesure que l'historique se
  remplit et que le modèle prouve sa précision.**

---

## 4. Contrat de l'API

### Requête — `POST /api/simulation-op`

```json
{
  "lots":   [ { "lot_id": 3, "quantite": 100 } ],
  "phases": [
    { "transformation_id": 1, "machine_id": 2 },
    { "transformation_id": 4, "machine_id": null }
  ],
  "quantite_pf_cible": 80
}
```

### Réponse (extraits)

```json
{
  "estimatedPF": 35.408,          // P50 — compatible champs cachés du formulaire
  "totalLossRate": 0.6459,
  "lotLoss": 0.015,
  "machineLoss": 0.6309,
  "totalDurationMin": 322,
  "durationLabel": "5h 22min",

  "pfP10": 29.393,  "pfP90": 41.876,
  "durP10": 289,    "durP90": 373,
  "probaCible": 0.0,
  "confiance": 10,
  "biais": 0.7,
  "nHistorique": 0,

  "phasesDetail": [
    { "label": "Initiale — couper", "rendement": 0.702, "ecartType": 0.06,
      "dureeMin": 144, "source": "prior global", "nObs": 0 }
  ],
  "risques": [
    { "niveau": "critique", "titre": "Machine en panne",
      "detail": "Laveuse Industrielle L1 est EN PANNE — la phase « Initiale » ne pourra pas démarrer." }
  ],
  "recommandations": [
    "Pour atteindre la cible de 80.0 avec ≥50% de probabilité, injectez ≈156.6 de MP (+56.6)."
  ],
  "trace": [ { "msg": "Historique chargé : …", "type": "info" } ]
}
```

- `trace` alimente la console animée (`ia_simulation.log`) du frontend.
- Les champs `estimatedPF` / `totalLossRate` / `totalDurationMin` sont
  **rétro-compatibles** : ils remplissent les mêmes champs cachés
  (`quantite_pf_estimee`, `taux_perte_estime`, `duree_estimee_min`) envoyés au
  `store()` de l'OP — indispensable pour que l'auto-calibration (④) continue
  de fonctionner sur les OP futurs.

### Repli hors-ligne

Si l'appel échoue (réseau, erreur serveur), le frontend bascule sur l'ancien
calcul local `computeAI()` et l'indique dans la console (`⚠ Moteur serveur
indisponible`). Le rapport probabiliste (confiance, P10/P90, risques) n'est
alors pas affiché — ces blocs sont conditionnés à `simResults.confiance`.

---

## 5. Comment rendre le moteur de plus en plus précis

Le moteur est aussi bon que les données qu'on lui donne. Trois leviers :

1. **Saisir les quantités à chaque phase** (`quantite_mp_phase` et
   `quantite_obtenue`) lors de la validation. C'est **le carburant principal** :
   sans elles, les rendements restent sur le prior global (confiance ~10-30 %).
2. **Renseigner `date_debut` / `date_fin`** des phases (fait automatiquement si
   les opérateurs utilisent Démarrer/Terminer) → durées apprises au lieu du
   forfait 90 min.
3. **Valider les conditionnements** avec la `quantite_produite` réelle → la
   boucle d'auto-calibration s'active et corrige le biais systématique.

Ordres de grandeur : ~5 phases saisies par transformation suffisent pour que
l'historique domine le prior ; ~10 OP conditionnés donnent une calibration
stable.

---

## 6. Constantes réglables

Toutes dans `app/Services/ProductionSimulator.php` :

| Constante | Valeur | Rôle |
|---|---|---|
| `HALF_LIFE_DAYS` | 90 | Demi-vie du poids temporel. ↓ = modèle plus réactif mais plus nerveux |
| `PRIOR_STRENGTH` | 3.0 | Force du shrinkage. ↑ = plus prudent avec les petits historiques |
| `MC_RUNS` | 1500 | Nombre de scénarios Monte Carlo (coût ≈ linéaire, ~10 ms) |
| `DEFAULT_YIELD` / `DEFAULT_YIELD_SD` | 0.90 / 0.06 | Prior de rendement quand la base est vide |
| `BASE_PHASE_MIN` | 90 | Durée de repli d'une phase sans historique |
| `ETAT_MODIFIER` | 1.00 / 0.95 / 0.78 | Malus de rendement selon l'état machine |

---

## 7. Limites connues et pistes d'évolution

- **Pas de saisonnalité** : la pondération temporelle capte la dérive lente,
  pas les cycles (ex. humidité saisonnière affectant le séchage). Piste :
  facteur mensuel appris.
- **Équipes non modélisées** : les équipes sont affectées après la création de
  l'OP, le moteur ne peut donc pas encore apprendre un « facteur équipe ».
  Piste : l'intégrer dans le suivi de production.
- **Indépendance des phases** : le Monte Carlo tire chaque rendement
  indépendamment ; en réalité une MP dégradée pénalise toutes les phases
  (corrélation positive) → les intervalles réels peuvent être légèrement plus
  larges que P10–P90.
- **Loi d'échelle unique** (√quantité) : si un procédé est strictement
  proportionnel au volume, la durée sera sous-estimée aux grandes quantités
  (borne ×1,8). Piste : régression par transformation quand n ≥ 10.

---
---

# Partie II — Technologies utilisées dans le projet

Cette partie inventorie **toutes** les briques techniques de l'application de
gestion de chaîne de production, avec le rôle exact de chacune dans le projet.

## 1. Architecture générale

L'application est un **monolithe Laravel classique rendu côté serveur** :
le PHP génère les pages HTML (Blade), et l'interactivité côté navigateur est
assurée par Alpine.js sans framework JavaScript lourd (pas de React/Vue).
Ce choix donne une base simple à déployer (un seul serveur, pas d'API à
synchroniser) tout en restant très réactive à l'usage grâce à Alpine.

```
Navigateur ──HTTP──▶ Laravel (routes → middleware → contrôleurs → Eloquent) ──▶ MySQL/MariaDB
    ▲                                   │
    └── HTML (Blade) + Alpine.js + Tailwind CSS (compilés par Vite)
```

## 2. Socle backend

| Technologie | Version | Utilité dans ce projet |
|---|---|---|
| **PHP** | ≥ 8.3 (8.4 installé) | Langage serveur. Le projet utilise ses apports modernes : `match(true)` (courbe qualité des lots), fonctions fléchées, opérateur nullsafe `?->`, promotion de propriétés. |
| **Laravel** | 13.x | Framework central : routage, contrôleurs, validation, sessions, authentification. **Tout le métier passe par lui.** |
| **Laravel Breeze** | 2.x | Scaffolding d'authentification : login, inscription, réinitialisation de mot de passe, vérification email (`resources/views/auth/`, `app/Http/Controllers/Auth/`). |
| **Laravel Tinker** | 3.x | Console interactive (`php artisan tinker`) pour interroger/tester les modèles directement en base — utilisée notamment pour vérifier le moteur de simulation. |

### Composants Laravel réellement exploités

- **Eloquent ORM** — chaque table a son modèle (`OrdreProduction`, `Employe`,
  `LotMatierePremiere`, `ProduitSemiFini`…). Le projet utilise intensivement :
  - les **relations** (`belongsTo`, `hasMany`, `belongsToMany` avec pivot
    `ordre_production_lots` et `role_permissions`) ;
  - les **accessors** (`getNomCompletAttribute`, `getQualiteAttribute` — la
    courbe qualité/âge des lots, `getLabelAttribute` des phases) ;
  - les **hooks de cycle de vie** (`booted()`/`creating`) pour générer les
    codes métier (`EMP-00001`, `OP-2026-0001`, `LOT-2026-0001`, `PSF-00001`,
    `TRF-00001`…) et synchroniser la colonne `role` legacy depuis `role_id` ;
  - les **casts** (`decimal:2`, `datetime`, `hashed` pour les mots de passe).
- **Migrations** — tout le schéma est versionné dans `database/migrations/`
  (34 fichiers), y compris les *seeds* structurels (permissions, rôles
  système) exécutés dans les migrations elles-mêmes.
- **Validation** — règles déclaratives dans les contrôleurs, avec *error bags*
  nommés (`validateWithBag('create')` / `'edit'`) pour router les erreurs vers
  le bon modal d'une même page.
- **Transactions** (`DB::transaction`) — garantissent l'atomicité des écritures
  multi-tables : création employé + compte, validation de phase + mouvements
  de stock + création du produit semi-fini.
- **Middleware maison** (`app/Http/Middleware/`) :
  - `AdminMiddleware` (alias `admin`) — réserve l'interface complète aux
    profils non-opérateurs ;
  - `CheckPermission` (alias `perm:slug1,slug2`) — le cœur du RBAC : chaque
    groupe de routes exige une permission précise (`production.creer`,
    `stocks.semi-finis`…).
- **Service applicatif** — `app/Services/ProductionSimulator.php` isole toute
  l'intelligence prédictive (Partie I) hors des contrôleurs, injecté par le
  conteneur de services de Laravel.
- **Trait maison** `app/Traits/HasUuid.php` — ajoute un UUID à chaque modèle et
  l'utilise comme clé de routage (`getRouteKeyName`) : les URLs exposent
  `/employes/9b2f…` au lieu d'IDs séquentiels devinables (sécurité par
  non-énumérabilité, renforcée par des contraintes regex `Route::pattern`).

## 3. Base de données

| Technologie | Utilité |
|---|---|
| **MySQL / MariaDB** (11.8) | SGBD relationnel, base `chaine_prod`. Toute la donnée métier : production, stocks, RH, RBAC. Intégrité garantie par des **clés étrangères** avec stratégies explicites (`cascadeOnDelete`, `nullOnDelete`, `restrict`). |
| Driver `database` pour **sessions** | Les sessions utilisateurs sont stockées en base (table `sessions`) plutôt qu'en fichiers — survit aux redéploiements. |
| Driver `database` pour **cache** | Table `cache` — pas de dépendance Redis à installer. |
| Driver `database` pour **queue** | Table `jobs` — les tâches asynchrones sont prêtes à l'emploi (le script `composer dev` lance déjà un worker `queue:listen`). |

Particularités du schéma : doubles identifiants (id auto-incrémenté interne +
`uuid` public + `code` métier lisible), colonnes décimales précises pour les
quantités (`decimal(12,3)`), enums de statut sur chaque flux (OP, phases,
conditionnements, lots, machines, semi-finis).

## 4. Frontend

| Technologie | Version | Utilité dans ce projet |
|---|---|---|
| **Blade** | (Laravel) | Moteur de templates. Layout unique `layouts/app.blade.php` + composant `<x-app-layout>`, navigation par permissions dans `layouts/navigation-links.blade.php`, une vue par module (`employes/`, `ordre-productions/`, `produits-semi-finis/`…). |
| **Alpine.js** | 3.x | Toute l'interactivité : modals de création/édition, formulaires dynamiques (ajout de phases, sélection de lots multi-lignes), **l'écran de simulation IA** (animation, console de log, rapport), calculs réactifs (totaux MP, erreurs de stock). Bundlé via `resources/js/app.js`. |
| **Tailwind CSS** | 3.x | Tout le style, en classes utilitaires directement dans les vues. Plugin **`@tailwindcss/forms`** pour normaliser les champs de formulaire. Config `tailwind.config.js` : police `Figtree`, animations maison (`modal-pop`, `fade-in`). |
| **Thème sombre maison** | — | Système à **variables CSS** (`--dk-bg`, `--dk-surface`, `--dk-t1`…) dans `app.blade.php`, activé par `data-theme="dark"` sur `<html>`. Palette « Industrial Night » validée WCAG AA/AAA (voir les commentaires du fichier pour les ratios mesurés). Des sélecteurs `[data-theme="dark"]` réinterprètent les classes Tailwind claires — les vues n'ont pas besoin de classes `dark:` partout. |
| **SweetAlert2** | 11 (CDN jsDelivr) | Boîtes de dialogue riches : confirmations de suppression, motifs d'invalidation de phase (avec champ texte obligatoire), toasts de succès/erreur — stylées pour les deux thèmes. |
| **Polices** | Figtree (Bunny Fonts) + Outfit (Google Fonts) | Typographie de l'interface. Bunny Fonts est un miroir européen respectueux du RGPD pour Figtree. |

## 5. Chaîne de build

| Outil | Version | Utilité |
|---|---|---|
| **Vite** | 8.x | Bundler : compile `resources/css/app.css` et `resources/js/app.js` vers `public/build/`, avec rechargement à chaud en développement (`npm run dev`) et minification en production (`npm run build`). Intégré aux vues par la directive `@vite`. |
| **laravel-vite-plugin** | 3.x | Pont Laravel ↔ Vite : injecte les bonnes URLs d'assets (serveur de dev ou manifest de production) et rafraîchit le navigateur quand une vue Blade change. |
| **PostCSS + Autoprefixer** | 8.x / 10.x | Pipeline CSS : exécute Tailwind puis ajoute les préfixes navigateurs (`-webkit-`…) automatiquement. |
| **concurrently** | 9.x | Utilisé par `composer dev` pour lancer en parallèle, dans un seul terminal : serveur PHP, worker de queue, logs temps réel et Vite. |
| **npm** | — | Gestionnaire des dépendances JavaScript. |
| **Composer** | — | Gestionnaire des dépendances PHP + scripts projet (`composer setup` installe tout, `composer dev` lance l'environnement complet, `composer test`). |

> ⚠️ **Remarque d'audit** : `package.json` déclare aussi `@tailwindcss/vite`
> (plugin Tailwind v4) mais il n'est **pas branché** dans `vite.config.js` —
> c'est la v3 via PostCSS qui est active. Dépendance à supprimer ou migration
> v4 à terminer, au choix.

## 6. Services externes

| Service | Utilité |
|---|---|
| **api.qrserver.com** (goqr.me) | Génération des **QR codes des ordres de production** : à la création d'un OP, l'URL de sa fiche est encodée en QR (150×150) imprimable sur la fiche physique ; l'opérateur terrain scanne ou saisit le code (`OP-2026-XXXX`) sur son tableau de bord pour retrouver l'OP. Service gratuit, sans clé API. *Limite : nécessite Internet ; une génération locale (ex. paquet `endroid/qr-code`) serait l'évolution naturelle pour un atelier hors-ligne.* |
| **fonts.bunny.net / fonts.googleapis.com** | Distribution des polices (voir § 4). |
| **cdn.jsdelivr.net** | Distribution de SweetAlert2 (voir § 4). |

## 7. Outils de développement et de qualité

| Outil | Version | Utilité |
|---|---|---|
| **PHPUnit** | 12.x | Framework de tests. Tests présents : authentification Breeze et profil (`tests/Feature/`). *Le métier (production, stocks, RBAC, simulateur) n'est pas encore couvert — meilleur investissement qualité suivant.* |
| **Laravel Pint** | 1.x | Formateur de code PHP (style Laravel officiel) : `./vendor/bin/pint`. |
| **Laravel Pail** | 1.x | Suivi des logs applicatifs en temps réel dans le terminal (`php artisan pail`), lancé automatiquement par `composer dev`. |
| **Collision** | 8.x | Rendu lisible des erreurs et exceptions en console (utilisé par Artisan et PHPUnit). |
| **Mockery** | 1.x | Création de doublures (mocks) dans les tests. |
| **FakerPHP** | 1.x | Génération de données factices pour les factories/seeders (`UserFactory`). |
| **laravel/pao** | 1.x | Sortie optimisée des outils de test PHP pour les **agents IA** (PHPUnit/Pest/PHPStan) — améliore la lisibilité des résultats quand un assistant IA travaille sur le code. |

## 8. Sécurité — récapitulatif des mécanismes en place

| Mécanisme | Où |
|---|---|
| Authentification par session + hachage bcrypt des mots de passe | Breeze + cast `hashed` du modèle `User` |
| **RBAC granulaire** : Employé → Compte (`user_id`) → Rôle (`role_id`) → Permissions (pivot) | Tables `roles`, `permissions`, `role_permissions` ; middleware `perm:` ; navigation filtrée par `hasPermission()` |
| Protection **CSRF** sur tous les formulaires et l'appel AJAX de simulation | `@csrf` / en-tête `X-CSRF-TOKEN` |
| Protection contre l'**énumération d'IDs** | UUIDs en URL (`HasUuid`) + contraintes regex sur les paramètres de route |
| Protection contre l'**assignement de masse** | `$fillable` sur chaque modèle + données validées uniquement |
| **Injections SQL** neutralisées | Requêtes préparées d'Eloquent/Query Builder partout |
| Atomicité des écritures sensibles | `DB::transaction` (comptes, stocks, semi-finis) |

## 9. Cartographie du code (où chercher quoi)

```
app/
├── Http/
│   ├── Controllers/          # 21 contrôleurs : 1 par module métier + Auth/
│   └── Middleware/            # AdminMiddleware, CheckPermission (RBAC)
├── Models/                    # 23 modèles Eloquent (relations + logique métier)
├── Services/
│   └── ProductionSimulator.php  # Moteur prédictif (Partie I)
└── Traits/
    └── HasUuid.php            # UUID + routage public

database/migrations/           # Schéma complet versionné + seeds structurels
resources/
├── views/                     # Blade : 1 dossier par module, layouts/, auth/
├── css/app.css                # Point d'entrée Tailwind
└── js/app.js                  # Point d'entrée Alpine
routes/web.php                 # Toutes les routes, groupées par permission
tailwind.config.js / vite.config.js / postcss.config.js   # Build frontend
SIMULATION-IA.md               # Ce document
```

