<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdreProduction;
use App\Models\MatierePremiere;
use App\Models\ProduitFini;
use App\Models\Machine;
use App\Models\Equipe;
use App\Models\Employe;
use App\Models\Departement;
use App\Models\Transformation;
use App\Models\PhaseProduction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord approprié selon le rôle de l'utilisateur.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasAdminInterface()) {
            return $this->adminDashboard($request);
        }

        return $this->operateurDashboard($user);
    }

    /**
     * Tableau de bord Administrateur (Visibilité 360°)
     */
    private function adminDashboard(Request $request)
    {
        // -------------------------------------------------------------------------
        // ÉTAPE 1 : GESTION DES STOCKS & CATALOGUES ARTICLES
        // -------------------------------------------------------------------------
        // Version optimisée pour ton filtre d'alerte :
        $alertesStockMP = MatierePremiere::all()->filter(fn($mp) => $mp->estEnAlerte());
        $totalMatieresPremieres = MatierePremiere::count();
        $totalProduitsFinis = ProduitFini::count();

        // -------------------------------------------------------------------------
        // ÉTAPE 2 : RESSOURCES HUMAINES
        // -------------------------------------------------------------------------
        $totalEmployes = Employe::count();
        $totalDepartements = Departement::count();
        $totalEquipes = Equipe::count();

        // -------------------------------------------------------------------------
        // ÉTAPE 3 : FLUX LOGISTIQUES (COMMANDES ET RECEPTIONS TIERS)
        // -------------------------------------------------------------------------
        // Si tes tables de liaison logistique existent déjà en BD, on compte leurs lignes :
        $commandesMpCount = 0;
        $livraisonsMpCount = 0;
        $commandesPfCount = 0;
        $livraisonsPfCount = 0;
        
        // -------------------------------------------------------------------------
        // ÉTAPE 4 : PARC MACHINES
        // -------------------------------------------------------------------------
        $machinesCount = Machine::count();
        $machinesEnMarche = Machine::where('etat', 'en_marche')->count();
        $machinesEnPanne = Machine::where('etat', 'en_panne')->count();

        // -------------------------------------------------------------------------
        // ÉTAPE 5 : SUIVI DE PRODUCTION & PHASES EN PARALLÈLE
        // -------------------------------------------------------------------------
        $totalOP = OrdreProduction::count();
        $opEnCours = OrdreProduction::where('statut', 'en_cours')->count();
        $opTermines = OrdreProduction::where('statut', 'termine')->count();
        $opConditionnes = OrdreProduction::where('statut', 'conditionne')->count();
        
        $phasesEnAttente = PhaseProduction::where('statut', 'en_attente')->count();
        $phasesEnCours = PhaseProduction::where('statut', 'en_cours')->count();
        $phasesTerminees = PhaseProduction::where('statut', 'termine')->count();
        $phasesValidees = PhaseProduction::where('statut', 'valide')->count();

        // Taux de complétude globale des phases
        $totalPhases = PhaseProduction::count();
        $tauxCompletude = $totalPhases > 0 ? round(($phasesValidees / $totalPhases) * 100, 1) : 0;

        // On charge les derniers ordres lancés avec leurs relations
        $derniersOP = OrdreProduction::with(['produitFini', 'matierePremiere'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // -------------------------------------------------------------------------
        // ÉTAPE 6 : CATALOGUE DES REFEERENTIELS DE TRANSFORMATION
        // -------------------------------------------------------------------------
        $totalTransformations = Transformation::count();

        // Statistiques pour le Graphique et les Cartes
        $totalProduction = OrdreProduction::count() > 0 ? OrdreProduction::count() : 1;
        $tauxRealisation = (($opTermines + $opConditionnes) / $totalProduction) * 100;
        // Au lieu de ProduitFini::sum('qte_stock'), utilisez :
        $qteMp = \App\Models\MatierePremiere::query()->sum('qte_en_stock');
        $qtePf = \App\Models\ProduitFini::query()->sum('qte_en_stock'); // Assurez-vous que la colonne 'qte_stock' existe

        // ... (votre code existant pour $derniersOP)

        return view('dashboard.admin', compact(
            'alertesStockMP', 'totalMatieresPremieres', 'totalProduitsFinis',
            'totalEmployes', 'totalDepartements', 'totalEquipes',
            'commandesMpCount', 'livraisonsMpCount', 'commandesPfCount', 'livraisonsPfCount',
            'machinesCount', 'machinesEnMarche', 'machinesEnPanne',
            'totalOP', 'opEnCours', 'opTermines', 'opConditionnes',
            'phasesEnAttente', 'phasesEnCours', 'phasesTerminees', 'phasesValidees',
            'tauxCompletude', 'derniersOP', 'totalTransformations',
            'tauxRealisation', 'qteMp', 'qtePf' // <--- Ajouté ici
        ));
    }

    /**
     * Tableau de bord Opérateur / Équipe (Interface terrain)
     */
    private function operateurDashboard($user)
    {
        $employe = $user->employe;

        if (!$employe) {
            return view('dashboard.operateur', [
                'equipe' => null,
                'tachesAfaire' => collect(),
                'tachesEnCours' => collect(),
                'historique' => new LengthAwarePaginator([], 0, 8, 1),
            ]);
        }

        $equipe = $employe->equipe;

        if (!$equipe) {
            return view('dashboard.operateur', [
                'equipe'       => null,
                'tachesAfaire' => collect(),
                'tachesEnCours'=> collect(),
                'historique'   => new LengthAwarePaginator([], 0, 8, 1),
            ]);
        }

        $taches = PhaseProduction::with(['ordreProduction.matierePremiere', 'ordreProduction.produitFini', 'transformation', 'machine'])
            ->where('equipe_id', $equipe->id)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->get();

        $tachesAfaire = $taches->filter(function($tache) {
            return $tache->statut === 'en_attente' && $tache->phasePrecedenteEstValidee();
        });

        $tachesEnCours = $taches->filter(fn($tache) => $tache->statut === 'en_cours');

        // Historique : phases terminées ou validées de cette équipe
        $historique = PhaseProduction::with(['ordreProduction.produitFini', 'transformation', 'machine'])
            ->where('equipe_id', $equipe->id)
            ->whereIn('statut', ['termine', 'valide'])
            ->orderBy('updated_at', 'desc')
            ->paginate(8);

        return view('dashboard.operateur', compact('equipe', 'tachesAfaire', 'tachesEnCours', 'historique'));
    }
}