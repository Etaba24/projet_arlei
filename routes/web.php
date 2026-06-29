<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrdreProductionController;
use App\Http\Controllers\MatierePremiereController;
use App\Http\Controllers\ProduitFiniController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\TransformationController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\LogistiqueController;
use App\Http\Controllers\PhaseProductionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LotMatierePremiereController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// ── Routes authentifiées ──────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Opérateur terrain : démarrer / terminer une phase ──
    Route::post('/phase-productions/{phase}/demarrer', [PhaseProductionController::class, 'demarrer'])->name('phase-productions.demarrer');
    Route::post('/phase-productions/{phase}/terminer', [PhaseProductionController::class, 'terminer'])->name('phase-productions.terminer');

    // Scan & consultation OP (opérateur peut voir sa fiche)
    Route::post('/ordre-productions/scan', [OrdreProductionController::class, 'scan'])->name('ordre-productions.scan');
    Route::get('/ordre-productions/{ordre_production}', [OrdreProductionController::class, 'show'])
        ->name('ordre-productions.show')
        ->where('ordre_production', '[0-9]+');

    // ── Interface admin (hasAdminInterface) ──────────────────────────────────
    Route::middleware('admin')->group(function () {

        // ════════════════════════════════════════════════════════════════════
        // PRODUCTION
        // ════════════════════════════════════════════════════════════════════

        // Lecture
        Route::middleware('perm:production.voir')->group(function () {
            Route::get('/ordre-productions', [OrdreProductionController::class, 'index'])->name('ordre-productions.index');
        });
        Route::middleware('perm:production.suivi')->group(function () {
            Route::get('/suivi-production', [OrdreProductionController::class, 'suivi'])->name('suivi-production.index');
        });

        // Écriture — chaque action protégée individuellement
        Route::middleware('perm:production.creer')->group(function () {
            Route::get('/ordre-productions/create', [OrdreProductionController::class, 'create'])->name('ordre-productions.create');
            Route::post('/ordre-productions', [OrdreProductionController::class, 'store'])->name('ordre-productions.store');
            Route::post('/ordre-productions/{ordre_production}/add-transformation', [OrdreProductionController::class, 'addTransformation'])->name('ordre-productions.add-transformation');
        });
        Route::middleware('perm:production.valider-phase')->group(function () {
            Route::post('/ordre-productions/{ordre_production}/valider-phase/{phase}', [OrdreProductionController::class, 'validerPhase'])->name('ordre-productions.valider-phase');
        });
        Route::middleware('perm:production.conditionner')->group(function () {
            Route::post('/ordre-productions/{ordre_production}/conditionner', [OrdreProductionController::class, 'conditionner'])->name('ordre-productions.conditionner');
        });
        Route::middleware('perm:production.valider-conditionnement')->group(function () {
            Route::post('/ordre-productions/{ordre_production}/valider-conditionnement', [OrdreProductionController::class, 'validerConditionnement'])->name('ordre-productions.valider-conditionnement');
        });
        Route::middleware('perm:production.interrompre')->group(function () {
            Route::post('/ordre-productions/{ordre_production}/interrompre', [OrdreProductionController::class, 'interrompre'])->name('ordre-productions.interrompre');
        });
        Route::middleware('perm:production.reprendre')->group(function () {
            Route::post('/ordre-productions/{ordre_production}/reprendre', [OrdreProductionController::class, 'reprendre'])->name('ordre-productions.reprendre');
        });

        // ════════════════════════════════════════════════════════════════════
        // STOCKS & RESSOURCES
        // .voir  → lecture seule (index + show uniquement)
        // .gérer → accès complet (create/store/edit/update/destroy)
        // ════════════════════════════════════════════════════════════════════

        // Matières premières
        Route::middleware('perm:stocks.matieres-premieres,stocks.voir')->group(function () {
            Route::resource('matieres-premieres', MatierePremiereController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:stocks.matieres-premieres')->group(function () {
            Route::resource('matieres-premieres', MatierePremiereController::class)->except(['index', 'show']);
        });

        // Produits finis
        Route::middleware('perm:stocks.produits-finis,stocks.voir')->group(function () {
            Route::resource('produits-finis', ProduitFiniController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:stocks.produits-finis')->group(function () {
            Route::resource('produits-finis', ProduitFiniController::class)->except(['index', 'show']);
        });

        // Lots de MP
        Route::middleware('perm:stocks.lots,stocks.voir')->group(function () {
            Route::get('/lots', [LotMatierePremiereController::class, 'index'])->name('lots.index');
        });
        Route::middleware('perm:stocks.lots')->group(function () {
            Route::post('/lots', [LotMatierePremiereController::class, 'store'])->name('lots.store');
            Route::delete('/lots/{lot}', [LotMatierePremiereController::class, 'destroy'])->name('lots.destroy');
        });

        // Machines
        Route::middleware('perm:stocks.machines,stocks.voir')->group(function () {
            Route::resource('machines', MachineController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:stocks.machines')->group(function () {
            Route::resource('machines', MachineController::class)->except(['index', 'show']);
        });

        // Transformations
        Route::middleware('perm:stocks.transformations,stocks.voir')->group(function () {
            Route::resource('transformations', TransformationController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:stocks.transformations')->group(function () {
            Route::resource('transformations', TransformationController::class)->except(['index', 'show']);
        });

        // ════════════════════════════════════════════════════════════════════
        // LOGISTIQUE
        // .voir  → lecture seule (GET uniquement)
        // .gérer → POST (créer commandes/livraisons)
        // ════════════════════════════════════════════════════════════════════

        // Fournisseurs
        Route::middleware('perm:logistique.fournisseurs,logistique.voir')->group(function () {
            Route::resource('fournisseurs', FournisseurController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:logistique.fournisseurs')->group(function () {
            Route::resource('fournisseurs', FournisseurController::class)->except(['index', 'show']);
        });

        // Clients
        Route::middleware('perm:logistique.clients,logistique.voir')->group(function () {
            Route::resource('clients', ClientController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:logistique.clients')->group(function () {
            Route::resource('clients', ClientController::class)->except(['index', 'show']);
        });

        // Flux MP (entrées)
        Route::middleware('perm:logistique.mp,logistique.voir')->group(function () {
            Route::get('/logistique/mp', [LogistiqueController::class, 'indexMp'])->name('logistique.mp');
        });
        Route::middleware('perm:logistique.mp')->group(function () {
            Route::post('/logistique/mp/commande', [LogistiqueController::class, 'storeCommandeMp'])->name('logistique.mp.commande');
            Route::post('/logistique/mp/livraison', [LogistiqueController::class, 'storeLivraisonMp'])->name('logistique.mp.livraison');
        });

        // Flux PF (sorties)
        Route::middleware('perm:logistique.pf,logistique.voir')->group(function () {
            Route::get('/logistique/pf', [LogistiqueController::class, 'indexPf'])->name('logistique.pf');
        });
        Route::middleware('perm:logistique.pf')->group(function () {
            Route::post('/logistique/pf/commande', [LogistiqueController::class, 'storeCommandePf'])->name('logistique.pf.commande');
            Route::post('/logistique/pf/livraison', [LogistiqueController::class, 'storeLivraisonPf'])->name('logistique.pf.livraison');
        });

        // ════════════════════════════════════════════════════════════════════
        // RH & ORGANISATION
        // ════════════════════════════════════════════════════════════════════

        // Équipes
        Route::middleware('perm:rh.equipes,rh.voir')->group(function () {
            Route::resource('equipes', EquipeController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:rh.equipes')->group(function () {
            Route::resource('equipes', EquipeController::class)->except(['index', 'show']);
        });

        // Employés
        Route::middleware('perm:rh.employes,rh.voir')->group(function () {
            Route::resource('employes', EmployeController::class)->only(['index', 'show']);
        });
        Route::middleware('perm:rh.employes')->group(function () {
            Route::resource('employes', EmployeController::class)->except(['index', 'show']);
        });

        // ════════════════════════════════════════════════════════════════════
        // ADMINISTRATION
        // ════════════════════════════════════════════════════════════════════

        // Comptes utilisateurs — aucune lecture seule, gestion complète seulement
        Route::middleware('perm:admin.utilisateurs')->group(function () {
            Route::resource('users', UserController::class);
        });

        // Rôles : lecture avec admin.voir, écriture avec admin.roles
        Route::middleware('perm:admin.roles,admin.voir')->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        });
        Route::middleware('perm:admin.roles')->group(function () {
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });
    });
});

require __DIR__.'/auth.php';
