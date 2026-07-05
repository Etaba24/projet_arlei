<?php

namespace App\Services;

use App\Models\LotMatierePremiere;
use App\Models\Machine;
use App\Models\OrdreProduction;
use App\Models\PhaseProduction;
use App\Models\Transformation;
use Carbon\Carbon;

/**
 * Moteur de simulation prédictive de production.
 *
 * Contrairement à l'ancienne simulation (constantes fixes côté navigateur),
 * ce moteur apprend des données réelles de l'application :
 *
 *  1. RENDEMENTS APPRIS — chaque phase validée fournit un rendement observé
 *     (quantite_obtenue / quantite_mp_phase). Les observations sont :
 *       - pondérées dans le temps (demi-vie 90 j : le passé récent pèse plus),
 *       - lissées par inférence bayésienne (shrinkage vers la moyenne globale
 *         quand l'historique d'une transformation/machine est mince).
 *
 *  2. DURÉES APPRISES — médiane pondérée des durées réelles (date_debut →
 *     date_fin) par couple transformation×machine, ajustée à la quantité
 *     traitée par une loi d'échelle sous-linéaire (durée ∝ √quantité).
 *
 *  3. QUALITÉ DES LOTS — perte pondérée par la quantité prélevée dans chaque
 *     lot (courbe d'âge du modèle LotMatierePremiere) + pénalité de péremption.
 *
 *  4. AUTO-CALIBRATION — le moteur compare ses prédictions passées
 *     (quantite_pf_estimee) aux productions réelles (conditionnements validés)
 *     et corrige son biais systématique. Il devient plus juste à chaque OP.
 *
 *  5. MONTE CARLO — 1 500 exécutions stochastiques de la chaîne de phases
 *     produisent une distribution complète : P10 / P50 / P90, probabilité
 *     d'atteindre la cible, et un indice de confiance.
 *
 *  6. ANALYSE DE RISQUES — péremption des lots, stock insuffisant, machines
 *     en panne, taux d'interruption historique par machine, charge machine,
 *     et recommandations actionnables (quantité MP à injecter pour la cible,
 *     machine alternative plus performante).
 */
class ProductionSimulator
{
    /** Demi-vie du poids temporel : une observation de 90 j pèse moitié moins. */
    private const HALF_LIFE_DAYS = 90;

    /** Force du prior bayésien (équivalent en nombre d'observations). */
    private const PRIOR_STRENGTH = 3.0;

    private const MC_RUNS = 1500;

    /** Valeurs de repli quand aucun historique n'existe encore. */
    private const DEFAULT_YIELD    = 0.90;
    private const DEFAULT_YIELD_SD = 0.06;
    private const BASE_PHASE_MIN   = 90;

    /** Modificateur de rendement selon l'état machine. */
    private const ETAT_MODIFIER = ['en_marche' => 1.00, 'arret' => 0.95, 'en_panne' => 0.78];

    private array $trace = [];
    private array $risques = [];
    private array $recommandations = [];

    /**
     * @param array $input [
     *   'lots'   => [['lot_id' => int, 'quantite' => float], ...],
     *   'phases' => [['transformation_id' => int, 'machine_id' => ?int], ...],
     *   'quantite_pf_cible' => ?float,
     * ]
     */
    public function simuler(array $input): array
    {
        $this->trace = $this->risques = $this->recommandations = [];

        // ── 1. Lots : quantité totale et perte qualité pondérée ──────────────
        [$totalMp, $lotLoss] = $this->analyserLots($input['lots'] ?? []);
        if ($totalMp <= 0) {
            return ['error' => 'Aucun lot sélectionné avec une quantité valide.'];
        }

        // ── 2. Historique : statistiques de rendement et de durée ────────────
        $histo = $this->chargerHistorique();
        $this->log("Historique chargé : {$histo['nPhases']} phase(s) validée(s), " .
                   "{$histo['nDurees']} durée(s) réelle(s) exploitables.", 'info');

        // ── 3. Paramètres de chaque phase du scénario ────────────────────────
        $phasesParams = $this->parametrerPhases($input['phases'] ?? [], $histo, $totalMp);

        // ── 4. Auto-calibration sur les prédictions passées ──────────────────
        [$biais, $mape, $nCalib] = $this->calibrer();
        if ($nCalib > 0) {
            $this->log(sprintf(
                'Auto-calibration : %d OP comparé(s) prédiction/réel → biais correctif ×%.3f (erreur moyenne %.1f%%).',
                $nCalib, $biais, ($mape ?? 0) * 100
            ), 'ai');
        }

        // ── 5. Monte Carlo ────────────────────────────────────────────────────
        $mc = $this->monteCarlo($totalMp, $lotLoss, $phasesParams, $biais);
        $this->log(sprintf(
            'Monte Carlo : %d scénarios simulés → P10 %.1f | P50 %.1f | P90 %.1f.',
            self::MC_RUNS, $mc['pfP10'], $mc['pfP50'], $mc['pfP90']
        ), 'ai');

        // ── 6. Cible, risques transverses, confiance ─────────────────────────
        $cible = isset($input['quantite_pf_cible']) && $input['quantite_pf_cible'] > 0
            ? (float) $input['quantite_pf_cible'] : null;

        $probaCible = null;
        if ($cible !== null) {
            $probaCible = count(array_filter($mc['pfRuns'], fn($v) => $v >= $cible)) / count($mc['pfRuns']);
            $this->evaluerCible($cible, $probaCible, $mc, $totalMp, $lotLoss, $phasesParams);
        }

        $confiance = $this->indiceConfiance($histo, $phasesParams, $mape, $mc);

        $totalLossRate = max(0.0, 1 - ($totalMp > 0 ? $mc['pfP50'] / $totalMp : 0));
        $machineLoss   = max(0.0, $totalLossRate - $lotLoss);

        return [
            // Champs historiques (compatibilité formulaire / rapport existant)
            'estimatedPF'      => round($mc['pfP50'], 3),
            'totalLossRate'    => round($totalLossRate, 4),
            'lotLoss'          => round($lotLoss, 4),
            'machineLoss'      => round($machineLoss, 4),
            'totalDurationMin' => (int) round($mc['durP50']),
            'durationLabel'    => $this->labelDuree((int) round($mc['durP50'])),
            'totalMp'          => round($totalMp, 3),

            // Intelligence ajoutée
            'pfP10'            => round($mc['pfP10'], 3),
            'pfP90'            => round($mc['pfP90'], 3),
            'durP10'           => (int) round($mc['durP10']),
            'durP90'           => (int) round($mc['durP90']),
            'probaCible'       => $probaCible !== null ? round($probaCible, 3) : null,
            'confiance'        => $confiance,
            'biais'            => round($biais, 3),
            'nHistorique'      => $histo['nPhases'],
            'phasesDetail'     => array_map(fn($p) => [
                'label'     => $p['label'],
                'rendement' => round($p['yield'], 3),
                'ecartType' => round($p['sd'], 3),
                'dureeMin'  => (int) round($p['duration']),
                'source'    => $p['source'],
                'nObs'      => round($p['nEff'], 1),
            ], $phasesParams),
            'risques'          => $this->risques,
            'recommandations'  => $this->recommandations,
            'trace'            => $this->trace,
        ];
    }

    /* ═══════════════════ 1. LOTS ═══════════════════ */

    private function analyserLots(array $lots): array
    {
        $totalMp = 0.0;
        $weighted = 0.0;

        foreach ($lots as $sel) {
            $qte = (float) ($sel['quantite'] ?? 0);
            $lot = LotMatierePremiere::with('matierePremiere')->find($sel['lot_id'] ?? null);
            if (!$lot || $qte <= 0) continue;

            $perte = $lot->qualite['lossRate'];

            // Pénalité de péremption imminente (au-delà de la courbe d'âge)
            if ($lot->date_peremption) {
                $joursRestants = now()->diffInDays($lot->date_peremption, false);
                if ($joursRestants < 0) {
                    $perte = max($perte, 0.35);
                    $this->risque('critique', 'Lot périmé',
                        "Le lot {$lot->code_lot} est périmé depuis " . abs((int) $joursRestants) . " jour(s).");
                } elseif ($joursRestants <= 15) {
                    $perte += 0.03;
                    $this->risque('attention', 'Péremption imminente',
                        "Le lot {$lot->code_lot} périme dans " . (int) $joursRestants . " jour(s) — à consommer en priorité (FIFO).");
                }
            }

            if ($qte > (float) $lot->quantite_disponible + 1e-9) {
                $this->risque('critique', 'Stock lot insuffisant',
                    "Le lot {$lot->code_lot} ne dispose que de {$lot->quantite_disponible} — demandé : {$qte}.");
            }

            if ($lot->statut === 'quarantaine') {
                $this->risque('critique', 'Lot en quarantaine',
                    "Le lot {$lot->code_lot} est en quarantaine et ne devrait pas être engagé en production.");
            }

            $weighted += $perte * $qte;
            $totalMp  += $qte;
        }

        $lotLoss = $totalMp > 0 ? $weighted / $totalMp : 0.05;
        $this->log(sprintf('Analyse lots : %.2f injectés, perte qualité pondérée %.1f%%.',
            $totalMp, $lotLoss * 100), 'phase');

        return [$totalMp, min($lotLoss, 0.45)];
    }

    /* ═══════════════════ 2. HISTORIQUE ═══════════════════ */

    private function chargerHistorique(): array
    {
        $phases = PhaseProduction::where('statut', 'valide')
            ->where('quantite_mp_phase', '>', 0)
            ->where('quantite_obtenue', '>', 0)
            ->get(['transformation_id', 'machine_id', 'quantite_mp_phase',
                   'quantite_obtenue', 'date_debut', 'date_fin', 'validated_at', 'updated_at']);

        $parTransfo = [];        // tid => [[yield, poids], ...]
        $parTransfoMachine = []; // "tid-mid" => [[yield, poids], ...]
        $durees = [];            // même clé => [[minutes, poids, quantite], ...]
        $nDurees = 0;

        foreach ($phases as $p) {
            $yield = min(1.0, (float) $p->quantite_obtenue / (float) $p->quantite_mp_phase);
            $age   = Carbon::parse($p->validated_at ?? $p->updated_at)->diffInDays(now());
            $poids = pow(0.5, $age / self::HALF_LIFE_DAYS);

            $parTransfo[$p->transformation_id][] = [$yield, $poids];
            if ($p->machine_id) {
                $parTransfoMachine["{$p->transformation_id}-{$p->machine_id}"][] = [$yield, $poids];
            }

            if ($p->date_debut && $p->date_fin) {
                $min = Carbon::parse($p->date_debut)->diffInMinutes(Carbon::parse($p->date_fin));
                if ($min >= 5 && $min <= 2880) {
                    $durees["{$p->transformation_id}-" . ($p->machine_id ?? '0')][] =
                        [$min, $poids, (float) $p->quantite_mp_phase];
                    $durees["t{$p->transformation_id}"][] = [$min, $poids, (float) $p->quantite_mp_phase];
                    $nDurees++;
                }
            }
        }

        // Prior global : rendement moyen toutes phases confondues
        $tous = array_merge(...array_values($parTransfo) ?: [[]]);
        $global = $this->statsPonderees($tous);

        // Fiabilité machine : taux d'interruption/annulation historique
        $fiabilite = PhaseProduction::selectRaw(
                "machine_id,
                 AVG(CASE WHEN statut IN ('interrompu','annule') THEN 1.0 ELSE 0.0 END) AS taux,
                 COUNT(*) AS n")
            ->whereNotNull('machine_id')
            ->groupBy('machine_id')
            ->get()
            ->keyBy('machine_id');

        // Charge machine : phases actives en ce moment
        $charge = PhaseProduction::selectRaw('machine_id, COUNT(*) AS n')
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->whereNotNull('machine_id')
            ->groupBy('machine_id')
            ->pluck('n', 'machine_id');

        return [
            'parTransfo'        => $parTransfo,
            'parTransfoMachine' => $parTransfoMachine,
            'durees'            => $durees,
            'priorYield'        => $global['nEff'] > 0 ? $global['mean'] : self::DEFAULT_YIELD,
            'priorSd'           => $global['nEff'] > 1 ? max(0.02, $global['sd']) : self::DEFAULT_YIELD_SD,
            'fiabilite'         => $fiabilite,
            'charge'            => $charge,
            'nPhases'           => $phases->count(),
            'nDurees'           => $nDurees,
        ];
    }

    /** Moyenne, écart-type et effectif efficace pondérés. */
    private function statsPonderees(array $obs): array
    {
        $sw = $swx = 0.0;
        foreach ($obs as [$x, $w]) { $sw += $w; $swx += $w * $x; }
        if ($sw <= 0) return ['mean' => 0, 'sd' => 0, 'nEff' => 0];

        $mean = $swx / $sw;
        $var = 0.0;
        foreach ($obs as [$x, $w]) { $var += $w * ($x - $mean) ** 2; }
        return ['mean' => $mean, 'sd' => sqrt($var / $sw), 'nEff' => $sw];
    }

    /** Shrinkage bayésien : tire l'estimation vers le prior quand n est faible. */
    private function shrink(float $mean, float $nEff, float $prior): float
    {
        return ($nEff * $mean + self::PRIOR_STRENGTH * $prior) / ($nEff + self::PRIOR_STRENGTH);
    }

    /* ═══════════════════ 3. PARAMÈTRES DE PHASE ═══════════════════ */

    private function parametrerPhases(array $phases, array $histo, float $totalMp): array
    {
        $params = [];
        $n = count($phases);

        foreach ($phases as $i => $ph) {
            $tid = $ph['transformation_id'] ?? null;
            $mid = $ph['machine_id'] ?? null;
            $label = $i === 0 ? 'Initiale' : ($i === $n - 1 ? 'Finale' : "Intermédiaire {$i}");

            $transfo = $tid ? Transformation::find($tid) : null;
            $machine = $mid ? Machine::find($mid) : null;

            // — Rendement : machine×transformation > transformation > prior global —
            $source = 'prior global';
            $stats = ['mean' => $histo['priorYield'], 'sd' => $histo['priorSd'], 'nEff' => 0];

            if ($tid && isset($histo['parTransfoMachine']["{$tid}-{$mid}"])) {
                $stats = $this->statsPonderees($histo['parTransfoMachine']["{$tid}-{$mid}"]);
                $source = 'historique machine';
            } elseif ($tid && isset($histo['parTransfo'][$tid])) {
                $stats = $this->statsPonderees($histo['parTransfo'][$tid]);
                $source = 'historique transformation';
            }

            $yield = $this->shrink($stats['mean'], $stats['nEff'], $histo['priorYield']);
            $sd    = max(0.02, $stats['nEff'] > 1 ? $stats['sd'] : $histo['priorSd']);

            // — Modificateur d'état machine + risques —
            $etat = $machine->etat ?? null;
            if ($machine) {
                $yield *= self::ETAT_MODIFIER[$etat] ?? 0.9;

                if ($etat === 'en_panne') {
                    $this->risque('critique', 'Machine en panne',
                        "{$machine->designation} est EN PANNE — la phase « {$label} » ne pourra pas démarrer.");
                } elseif ($etat === 'arret') {
                    $this->risque('attention', 'Machine à l\'arrêt',
                        "{$machine->designation} est à l'arrêt : prévoir sa remise en marche avant la phase « {$label} ».");
                }

                $fiab = $histo['fiabilite'][$machine->id] ?? null;
                if ($fiab && $fiab->n >= 3 && $fiab->taux > 0.25) {
                    $yield *= (1 - $fiab->taux * 0.1);
                    $this->risque('attention', 'Machine peu fiable',
                        sprintf('%s a un taux d\'interruption historique de %.0f%% (%d phases).',
                            $machine->designation, $fiab->taux * 100, $fiab->n));
                }

                $nActives = (int) ($histo['charge'][$machine->id] ?? 0);
                if ($nActives >= 2) {
                    $this->risque('info', 'Machine chargée',
                        "{$machine->designation} est déjà affectée à {$nActives} phase(s) active(s) — risque de file d'attente.");
                }

                // — Suggestion d'une machine plus performante pour cette transformation —
                $this->suggererMachine($tid, $machine, $histo, $label);
            } else {
                $yield *= 0.95;
                $this->risque('info', 'Phase sans machine',
                    "La phase « {$label} » n'a pas de machine affectée : estimation moins précise.");
            }

            // — Durée : historique machine > historique transformation > repli —
            $duration = $this->estimerDuree($tid, $mid, $histo, $totalMp, $etat);

            $params[] = [
                'label'    => $label . ($transfo ? " — {$transfo->designation}" : ''),
                'yield'    => max(0.30, min(1.0, $yield)),
                'sd'       => min(0.15, $sd),
                'duration' => $duration,
                'source'   => $source,
                'nEff'     => $stats['nEff'],
            ];

            $this->log(sprintf('[%s] rendement appris %.1f%% (±%.1f, %s, n≈%.1f) | durée %.0f min.',
                $params[$i]['label'], $params[$i]['yield'] * 100, $sd * 100,
                $source, $stats['nEff'], $duration), 'phase');
        }

        return $params;
    }

    private function estimerDuree(?int $tid, ?int $mid, array $histo, float $totalMp, ?string $etat): float
    {
        $obs = $histo['durees']["{$tid}-" . ($mid ?? '0')] ?? $histo['durees']["t{$tid}"] ?? null;

        if ($obs) {
            $sw = $sd = $sq = 0.0;
            foreach ($obs as [$min, $w, $qte]) { $sw += $w; $sd += $w * $min; $sq += $w * max(0.001, $qte); }
            $durMoy = $sd / $sw;
            $qteMoy = $sq / $sw;
            // Loi d'échelle sous-linéaire : durée ∝ √(quantité relative)
            $facteur = max(0.6, min(1.8, sqrt($totalMp / max(0.001, $qteMoy))));
            $duree = $durMoy * $facteur;
        } else {
            $duree = self::BASE_PHASE_MIN;
        }

        // Une machine dégradée travaille plus lentement
        if ($etat === 'arret')    $duree *= 1.15;
        if ($etat === 'en_panne') $duree *= 1.60;

        return $duree;
    }

    private function suggererMachine(?int $tid, Machine $actuelle, array $histo, string $label): void
    {
        if (!$tid) return;

        $actuelleStats = isset($histo['parTransfoMachine']["{$tid}-{$actuelle->id}"])
            ? $this->statsPonderees($histo['parTransfoMachine']["{$tid}-{$actuelle->id}"]) : null;

        foreach (Machine::where('etat', 'en_marche')->where('id', '!=', $actuelle->id)->get() as $alt) {
            $key = "{$tid}-{$alt->id}";
            if (!isset($histo['parTransfoMachine'][$key])) continue;

            $altStats = $this->statsPonderees($histo['parTransfoMachine'][$key]);
            if ($altStats['nEff'] < 1.5) continue;

            $altYield = $this->shrink($altStats['mean'], $altStats['nEff'], $histo['priorYield']);
            $curYield = $actuelleStats
                ? $this->shrink($actuelleStats['mean'], $actuelleStats['nEff'], $histo['priorYield'])
                : $histo['priorYield'] * (self::ETAT_MODIFIER[$actuelle->etat] ?? 0.9);

            if ($altYield > $curYield + 0.03) {
                $this->recommander(sprintf(
                    'Phase « %s » : %s obtient historiquement %.1f%% de rendement sur cette transformation, contre %.1f%% pour %s. Envisagez de permuter.',
                    $label, $alt->designation, $altYield * 100, $curYield * 100, $actuelle->designation));
                return; // une seule suggestion par phase
            }
        }
    }

    /* ═══════════════════ 4. AUTO-CALIBRATION ═══════════════════ */

    private function calibrer(): array
    {
        $ops = OrdreProduction::where('quantite_pf_estimee', '>', 0)
            ->whereHas('conditionnement', fn($q) => $q->where('statut', 'valide')->where('quantite_produite', '>', 0))
            ->with('conditionnement')
            ->get(['id', 'quantite_pf_estimee', 'updated_at']);

        if ($ops->isEmpty()) return [1.0, null, 0];

        $sw = $slog = $sape = 0.0;
        foreach ($ops as $op) {
            $reel  = (float) $op->conditionnement->quantite_produite;
            $pred  = (float) $op->quantite_pf_estimee;
            $age   = Carbon::parse($op->updated_at)->diffInDays(now());
            $poids = pow(0.5, $age / self::HALF_LIFE_DAYS);

            $sw   += $poids;
            $slog += $poids * log(max(0.05, min(20.0, $reel / $pred)));
            $sape += $poids * abs($reel - $pred) / max(0.001, $reel);
        }

        $biais = exp($slog / $sw);                    // moyenne géométrique pondérée
        $biais = max(0.7, min(1.3, $biais));          // garde-fou
        $mape  = $sape / $sw;

        return [$biais, $mape, $ops->count()];
    }

    /* ═══════════════════ 5. MONTE CARLO ═══════════════════ */

    private function monteCarlo(float $totalMp, float $lotLoss, array $phases, float $biais): array
    {
        $pfRuns = $durRuns = [];

        for ($r = 0; $r < self::MC_RUNS; $r++) {
            $perteLot = max(0.0, min(0.5, $lotLoss + $this->randn() * ($lotLoss * 0.2 + 0.005)));
            $qty = $totalMp * (1 - $perteLot);
            $dur = 0.0;

            foreach ($phases as $p) {
                $y = max(0.30, min(1.0, $p['yield'] + $this->randn() * $p['sd']));
                $qty *= $y;
                $dur += max(10.0, $p['duration'] * (1 + $this->randn() * 0.20));
            }

            $pfRuns[]  = $qty * $biais;
            $durRuns[] = $dur;
        }

        sort($pfRuns);
        sort($durRuns);

        return [
            'pfRuns' => $pfRuns,
            'pfP10'  => $this->percentile($pfRuns, 0.10),
            'pfP50'  => $this->percentile($pfRuns, 0.50),
            'pfP90'  => $this->percentile($pfRuns, 0.90),
            'durP10' => $this->percentile($durRuns, 0.10),
            'durP50' => $this->percentile($durRuns, 0.50),
            'durP90' => $this->percentile($durRuns, 0.90),
        ];
    }

    /** Tirage normal standard (Box-Muller). */
    private function randn(): float
    {
        $u = max(1e-12, mt_rand() / mt_getrandmax());
        $v = mt_rand() / mt_getrandmax();
        return sqrt(-2 * log($u)) * cos(2 * M_PI * $v);
    }

    /** Percentile sur tableau trié. */
    private function percentile(array $sorted, float $p): float
    {
        $idx = $p * (count($sorted) - 1);
        $lo = (int) floor($idx);
        $hi = (int) ceil($idx);
        return $sorted[$lo] + ($sorted[$hi] - $sorted[$lo]) * ($idx - $lo);
    }

    /* ═══════════════════ 6. CIBLE, CONFIANCE, HELPERS ═══════════════════ */

    private function evaluerCible(float $cible, float $proba, array $mc,
                                  float $totalMp, float $lotLoss, array $phases): void
    {
        if ($proba >= 0.85) {
            $this->log(sprintf('Cible %.1f : atteinte dans %.0f%% des scénarios — plan robuste.',
                $cible, $proba * 100), 'done');
            return;
        }

        // Quantité MP nécessaire pour atteindre la cible au rendement médian
        $rendementChaine = (1 - $lotLoss);
        foreach ($phases as $p) $rendementChaine *= $p['yield'];

        if ($rendementChaine > 0.01) {
            $mpRequis = $cible / $rendementChaine;
            $manque = $mpRequis - $totalMp;
            if ($manque > 0.01) {
                $this->recommander(sprintf(
                    'Pour atteindre la cible de %.1f avec ≥50%% de probabilité, injectez ≈%.1f de MP (+%.1f par rapport au plan actuel).',
                    $cible, $mpRequis, $manque));
            }
        }

        $niveau = $proba < 0.40 ? 'critique' : 'attention';
        $this->risque($niveau, 'Cible incertaine', sprintf(
            'La cible de %.1f n\'est atteinte que dans %.0f%% des %d scénarios simulés (P50 : %.1f).',
            $cible, $proba * 100, self::MC_RUNS, $mc['pfP50']));
    }

    private function indiceConfiance(array $histo, array $phases, ?float $mape, array $mc): int
    {
        $nEffTotal = array_sum(array_column($phases, 'nEff'));

        $score = 30
            + min(35, $nEffTotal * 4)                                   // volume d'historique pertinent
            + ($mape !== null ? max(0, 20 - $mape * 100) : 0)           // précision passée du modèle
            - min(15, (($mc['pfP90'] - $mc['pfP10']) / max(0.001, $mc['pfP50'])) * 25); // dispersion

        foreach ($this->risques as $r) {
            if ($r['niveau'] === 'critique') $score -= 8;
        }

        return (int) max(10, min(97, round($score)));
    }

    private function labelDuree(int $min): string
    {
        $h = intdiv($min, 60);
        $m = $min % 60;
        return $h > 0 ? "{$h}h {$m}min" : "{$m} min";
    }

    private function log(string $msg, string $type = 'info'): void
    {
        $this->trace[] = ['msg' => $msg, 'type' => $type];
    }

    private function risque(string $niveau, string $titre, string $detail): void
    {
        $this->risques[] = compact('niveau', 'titre', 'detail');
    }

    private function recommander(string $detail): void
    {
        $this->recommandations[] = $detail;
    }
}
