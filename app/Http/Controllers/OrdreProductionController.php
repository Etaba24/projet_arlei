<?php

namespace App\Http\Controllers;

use App\Models\OrdreProduction;
use App\Models\LotMatierePremiere;
use App\Models\ProduitFini;
use App\Models\ProduitSemiFini;
use App\Models\Machine;
use App\Models\Equipe;
use App\Models\Transformation;
use App\Models\PhaseProduction;
use App\Models\Conditionnement;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdreProductionController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdreProduction::with(['produitFini', 'matierePremiere', 'employe']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('numero_lot', 'like', "%{$search}%")
                  ->orWhereHas('produitFini', fn($sq) => $sq->where('designation', 'like', "%{$search}%"));
            });
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        $ops        = $query->orderBy('created_at', 'desc')->paginate(request('print') == 'true' ? 1000 : 7)->withQueryString();
        $totalCount = OrdreProduction::count();

        return view('ordre-productions.index', compact('ops', 'totalCount'));
    }

    public function create()
    {
        $produits        = ProduitFini::orderBy('designation')->get();
        $employes        = Employe::orderBy('nom')->get();
        $machines        = Machine::orderBy('designation')->get(); // all — JS handles etat display
        $equipes         = Equipe::orderBy('nom')->get();
        $transformations = Transformation::orderBy('designation')->get();

        // Lots grouped by matiere_premiere_id (with MP info), only available ones
        $lotsGrouped = LotMatierePremiere::with('matierePremiere')
            ->where('statut', 'disponible')
            ->where('quantite_disponible', '>', 0)
            ->orderBy('date_reception', 'asc') // oldest first so user sees FIFO naturally
            ->get()
            ->groupBy('matiere_premiere_id');

        $produitsSemiFinis = ProduitSemiFini::orderBy('designation')->get();

        return view('ordre-productions.create', compact(
            'produits', 'employes', 'machines', 'equipes', 'transformations', 'lotsGrouped', 'produitsSemiFinis'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produit_fini_id'    => 'required|exists:produits_finis,id',
            'employe_id'         => 'required|exists:employes,id',
            'quantite_pf_cible'  => 'nullable|numeric|min:0.01',
            'date_debut'         => 'required|date',
            // Lots : at least one
            'lots'               => 'required|array|min:1',
            'lots.*.lot_id'      => 'required|exists:lots_matieres_premieres,id',
            'lots.*.quantite'    => 'required|numeric|min:0.001',
            // Phases (mandatory: at least initial and final phase)
            'phases'             => 'required|array|min:2',
            'phases.*.transformation_id' => 'required|exists:transformations,id',
            'phases.*.equipe_id'         => 'required|exists:equipes,id',
            'phases.*.machine_id'        => 'required|exists:machines,id',
            'phases.*.date_attribution'  => 'required|date',
            'phases.*.matiere_premiere_id' => 'nullable|exists:matieres_premieres,id',
            'phases.*.produit_semi_fini_id' => 'nullable|exists:produits_semi_finis,id',
            'phases.*.quantite_mp_phase' => 'nullable|numeric|min:0',
            'phases.*.produit_semi_fini_obtenu_id' => 'nullable|exists:produits_semi_finis,id',
            'phases.*.quantite_obtenue' => 'nullable|numeric|min:0',
            // AI simulation results
            'quantite_pf_estimee'  => 'nullable|numeric',
            'taux_perte_estime'    => 'nullable|numeric',
            'duree_estimee_min'    => 'nullable|integer',
        ]);

        // Validate stock for each lot
        foreach ($request->lots as $lotInput) {
            $lot = LotMatierePremiere::findOrFail($lotInput['lot_id']);
            if ($lot->quantite_disponible < $lotInput['quantite']) {
                return redirect()->back()->withInput()->with(
                    'error',
                    "Stock insuffisant pour le lot {$lot->code_lot} ({$lot->matierePremiere->libelle}). "
                    . "Disponible : {$lot->quantite_disponible} {$lot->matierePremiere->unite_mesure}"
                );
            }
        }

        $phasesData   = array_values($request->input('phases', []));

        // Validate phase dates: none inferior to initial, none superior to final
        if (count($phasesData) >= 2) {
            $dateInit = $phasesData[0]['date_attribution'] ?? null;
            $dateFinale = $phasesData[count($phasesData) - 1]['date_attribution'] ?? null;

            if ($dateInit && $dateFinale) {
                if ($dateInit > $dateFinale) {
                    return redirect()->back()->withInput()->with(
                        'error',
                        "La date d'attribution de la phase initiale ne peut pas être supérieure à celle de la phase finale."
                    );
                }

                foreach ($phasesData as $idx => $pData) {
                    $pDate = $pData['date_attribution'] ?? null;
                    if ($pDate) {
                        if ($pDate < $dateInit) {
                            return redirect()->back()->withInput()->with(
                                'error',
                                "La date d'attribution de la phase #" . ($idx + 1) . " ne peut pas être antérieure à la date de la phase initiale."
                            );
                        }
                        if ($pDate > $dateFinale) {
                            return redirect()->back()->withInput()->with(
                                'error',
                                "La date d'attribution de la phase #" . ($idx + 1) . " ne peut pas être postérieure à la date de la phase finale."
                            );
                        }
                    }
                }
            }
        }

        $totalLotsQte = collect($request->lots)->sum('quantite');

        $op = DB::transaction(function () use ($request, $phasesData, $totalLotsQte) {

            // Determine primary MP from first lot (backward compat)
            $firstLot  = LotMatierePremiere::find($request->lots[0]['lot_id']);
            $primaryMpId = $firstLot?->matiere_premiere_id;

            $op = OrdreProduction::create([
                'produit_fini_id'      => $request->produit_fini_id,
                'matiere_premiere_id'  => $primaryMpId,
                'employe_id'           => $request->employe_id,
                'quantite_mp_injectee' => $totalLotsQte,
                'quantite_pf_cible'    => $request->quantite_pf_cible,
                'quantite_pf_estimee'  => $request->quantite_pf_estimee,
                'taux_perte_estime'    => $request->taux_perte_estime,
                'duree_estimee_min'    => $request->duree_estimee_min,
                'date_debut'           => $request->date_debut,
                'statut'               => 'en_cours',
            ]);

            // QR code
            $showUrl = route('ordre-productions.show', $op);
            $op->update(['qr_code_path' =>
                'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($showUrl)
            ]);

            // Attach lots and decrement their stock
            foreach ($request->lots as $lotInput) {
                $lot = LotMatierePremiere::find($lotInput['lot_id']);
                $qte = (float) $lotInput['quantite'];

                $op->lots()->attach($lot->id, ['quantite_utilisee' => $qte]);

                $lot->decrement('quantite_disponible', $qte);
                if ($lot->quantite_disponible <= 0) {
                    $lot->update(['statut' => 'epuise']);
                }

                // Keep MP total stock in sync
                $lot->matierePremiere()->decrement('qte_en_stock', $qte);
            }

            // Create phases
            $totalPhases = count($phasesData);
            foreach ($phasesData as $index => $phaseData) {
                if ($totalPhases === 1) {
                    $numeroPhase = 'initiale';
                } elseif ($index === 0) {
                    $numeroPhase = 'initiale';
                } elseif ($index === $totalPhases - 1) {
                    $numeroPhase = 'finale';
                } else {
                    $numeroPhase = 'intermediaire';
                }

                PhaseProduction::create([
                    'ordre_production_id' => $op->id,
                    'transformation_id'   => $phaseData['transformation_id'],
                    'equipe_id'           => $phaseData['equipe_id'],
                    'machine_id'          => $phaseData['machine_id'],
                    'date_attribution'    => $phaseData['date_attribution'],
                    'matiere_premiere_id' => $phaseData['matiere_premiere_id'] ?? null,
                    'produit_semi_fini_id' => $phaseData['produit_semi_fini_id'] ?? null,
                    'quantite_mp_phase'   => $phaseData['quantite_mp_phase'] ?? null,
                    'produit_semi_fini_obtenu_id' => $phaseData['produit_semi_fini_obtenu_id'] ?? null,
                    'quantite_obtenue'    => $phaseData['quantite_obtenue'] ?? null,
                    'numero_phase'        => $numeroPhase,
                    'ordre'               => $index + 1,
                    'duree_estimee_min'   => $phaseData['duree_estimee_min'] ?? null,
                    'statut'              => 'en_attente',
                ]);
            }

            // Trigger reordering of intermediate phases to be chronological by date_attribution
            $op->reordonnerPhases();

            return $op;
        });

        $msg = count($phasesData) > 0
            ? "Ordre de production lancé avec " . count($phasesData) . " phase(s) configurée(s)."
            : "Ordre de production lancé. Les phases seront assignées via le suivi.";

        return redirect()->route('ordre-productions.show', $op)->with('status', $msg);
    }

    public function show(OrdreProduction $ordreProduction)
    {
        $op = $ordreProduction->load([
            'produitFini', 'matierePremiere', 'employe',
            'lots.matierePremiere',
            'phaseProductions.transformation', 'phaseProductions.equipe', 'phaseProductions.machine',
            'conditionnement.equipe',
        ]);

        $equipes         = Equipe::orderBy('nom')->get();
        $transformations = Transformation::orderBy('designation')->get();
        $machines        = Machine::where('etat', 'en_marche')->orderBy('designation')->get();
        $produitsSemiFinis = ProduitSemiFini::orderBy('designation')->get();

        return view('ordre-productions.show', compact('op', 'equipes', 'transformations', 'machines', 'produitsSemiFinis'));
    }

    public function addTransformation(Request $request, OrdreProduction $ordreProduction)
    {
        if (!Auth::user()->hasPermission('production.creer')) abort(403);

        $ordreProduction->load('phaseProductions');

        $prochainePhase = $ordreProduction->prochainePhaseAAssigner();

        if (!$prochainePhase) {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('error', 'Toutes les phases sont déjà assignées ou la phase en cours n\'est pas terminée.');
        }

        $request->validate([
            'transformation_id'  => 'required|exists:transformations,id',
            'equipe_id'          => 'required|exists:equipes,id',
            'machine_id'         => 'required|exists:machines,id',
            'date_attribution'   => 'required|date',
            'matiere_premiere_id' => 'nullable|exists:matieres_premieres,id',
            'produit_semi_fini_id' => 'nullable|exists:produits_semi_finis,id',
            'quantite_mp_phase'   => 'nullable|numeric|min:0',
            'produit_semi_fini_obtenu_id' => 'nullable|exists:produits_semi_finis,id',
            'quantite_obtenue'            => 'nullable|numeric|min:0',
            'is_finale'          => 'nullable|boolean',
        ]);

        $initiale = $ordreProduction->phaseProductions()->where('numero_phase', 'initiale')->first();
        $finale = $ordreProduction->phaseProductions()->where('numero_phase', 'finale')->first();

        $dateInit = $initiale ? $initiale->date_attribution : null;
        $dateFinale = $finale ? $finale->date_attribution : null;

        $newDate = $request->input('date_attribution');
        if ($dateInit && $newDate < $dateInit->format('Y-m-d\TH:i')) {
            return redirect()->back()->withInput()->with(
                'error',
                "La date d'attribution ne peut pas être antérieure à celle de la phase initiale (" . $dateInit->format('d/m/Y H:i') . ")."
            );
        }
        if ($dateFinale && $newDate > $dateFinale->format('Y-m-d\TH:i')) {
            return redirect()->back()->withInput()->with(
                'error',
                "La date d'attribution ne peut pas être postérieure à celle de la phase finale (" . $dateFinale->format('d/m/Y H:i') . ")."
            );
        }

        $nextOrdre   = $ordreProduction->phaseProductions()->max('ordre') + 1;
        $isFinale    = (bool) $request->input('is_finale', false);
        $numeroPhase = $isFinale ? 'finale' : $prochainePhase;

        PhaseProduction::create([
            'ordre_production_id' => $ordreProduction->id,
            'transformation_id'   => $request->transformation_id,
            'equipe_id'           => $request->equipe_id,
            'machine_id'          => $request->machine_id,
            'date_attribution'    => $request->date_attribution,
            'matiere_premiere_id' => $request->matiere_premiere_id ?? null,
            'produit_semi_fini_id' => $request->produit_semi_fini_id ?? null,
            'quantite_mp_phase'   => $request->quantite_mp_phase ?? null,
            'produit_semi_fini_obtenu_id' => $request->produit_semi_fini_obtenu_id ?? null,
            'quantite_obtenue'    => $request->quantite_obtenue ?? null,
            'numero_phase'        => $numeroPhase,
            'ordre'               => $nextOrdre,
            'statut'              => 'en_attente',
        ]);

        // Reorder phases dynamically by date_attribution
        $ordreProduction->reordonnerPhases();

        $label = $isFinale ? 'Phase finale' : "Phase intermédiaire";
        return redirect()->route('ordre-productions.show', $ordreProduction)
            ->with('status', "✅ {$label} assignée avec succès !");
    }

    public function validerPhase(OrdreProduction $ordreProduction, PhaseProduction $phase)
    {
        if ($phase->statut !== 'termine') {
            return redirect()->back()
                ->with('error', 'Impossible de valider cette phase (elle doit être terminée).');
        }

        DB::transaction(function () use ($ordreProduction, $phase) {
            $phase->valider(Auth::user());

            // 1. Decrement input stock consumed by this phase (MP brute ou PSF d'une phase précédente)
            if ($phase->quantite_mp_phase > 0) {
                if ($phase->matiere_premiere_id && $phase->matiere_premiere_id != $ordreProduction->matiere_premiere_id) {
                    $phase->matierePremiere()->decrement('qte_en_stock', $phase->quantite_mp_phase);
                } elseif ($phase->produit_semi_fini_id) {
                    $phase->produitSemiFini()->decrement('qte_en_stock', $phase->quantite_mp_phase);
                }
            }

            // 2. Increment le stock du produit semi-fini obtenu en sortie de cette phase
            if ($phase->produit_semi_fini_obtenu_id && $phase->quantite_obtenue > 0) {
                $phase->produitSemiFiniObtenu()->increment('qte_en_stock', $phase->quantite_obtenue);
            }
        });

        return redirect()->back()
            ->with('status', "Phase '{$phase->transformation->designation}' validée.");
    }

    public function invaliderPhase(Request $request, OrdreProduction $ordreProduction, PhaseProduction $phase)
    {
        $request->validate([
            'motif' => 'required|string|max:1000',
        ]);

        if ($phase->ordre_production_id !== $ordreProduction->id) {
            abort(404);
        }

        if (!$phase->invalider(Auth::user(), $request->motif)) {
            return redirect()->back()
                ->with('error', 'Impossible d\'invalider cette phase (elle doit être terminée par l\'équipe).');
        }

        $ordreProduction->phaseProductions()
            ->where('ordre', '>', $phase->ordre)
            ->whereIn('statut', ['en_attente', 'en_cours', 'termine'])
            ->update([
                'statut' => 'en_attente',
                'date_debut' => null,
                'date_fin' => null,
            ]);

        return redirect()->back()
            ->with('status', "Phase '{$phase->transformation->designation}' invalidée et renvoyée à l'équipe {$phase->equipe?->nom}.");
    }

    public function conditionner(Request $request, OrdreProduction $ordreProduction)
    {
        $request->validate([
            'equipe_id'             => 'required|exists:equipes,id',
            'type_emballage'        => 'required|string|max:255',
            'quantite_produite'     => 'required|numeric|min:0.01',
            'quantite_mp_consommee' => 'required|numeric|min:0.01',
            'date_fabrication'      => 'required|date',
            'date_peremption'       => 'required|date|after:date_fabrication',
        ]);

        if (!$ordreProduction->toutesLesPhasesSontValidees()) {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('error', 'Toutes les phases doivent être validées avant le conditionnement.');
        }

        Conditionnement::create([
            'ordre_production_id'   => $ordreProduction->id,
            'equipe_id'             => $request->equipe_id,
            'type_emballage'        => $request->type_emballage,
            'quantite_produite'     => $request->quantite_produite,
            'quantite_mp_consommee' => $request->quantite_mp_consommee,
            'date_fabrication'      => $request->date_fabrication,
            'date_peremption'       => $request->date_peremption,
            'statut'                => 'termine',
        ]);

        $ordreProduction->update(['statut' => 'conditionne']);

        return redirect()->route('ordre-productions.show', $ordreProduction)
            ->with('status', 'Conditionnement enregistré. En attente de validation admin.');
    }

    public function validerConditionnement(OrdreProduction $ordreProduction)
    {
        $conditionnement = $ordreProduction->conditionnement;

        if (!$conditionnement) {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('error', 'Aucun conditionnement à valider.');
        }

        if ($conditionnement->valider(Auth::user())) {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('status', 'Ordre de production clôturé et stock produits finis mis à jour.');
        }

        return redirect()->route('ordre-productions.show', $ordreProduction)
            ->with('error', 'Erreur lors de la validation du conditionnement.');
    }

    public function interrompre(Request $request, OrdreProduction $ordreProduction)
    {
        $request->validate(['motif' => 'nullable|string|max:500']);

        if ($ordreProduction->interrompre($request->motif ?? '')) {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('status', '⛔ Ordre de production interrompu.');
        }

        return redirect()->route('ordre-productions.show', $ordreProduction)
            ->with('error', "Impossible d'interrompre (statut actuel : {$ordreProduction->statut}).");
    }

    public function reprendre(OrdreProduction $ordreProduction)
    {
        if ($ordreProduction->reprendre()) {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('status', '▶ Ordre de production repris.');
        }

        return redirect()->route('ordre-productions.show', $ordreProduction)
            ->with('error', 'Impossible de reprendre cet ordre.');
    }

    public function annuler(Request $request, OrdreProduction $ordreProduction)
    {
        $request->validate(['motif' => 'required|string|max:500']);

        if ($ordreProduction->annuler($request->motif)) {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('status', 'Ordre de production annulé.');
        }

        return redirect()->route('ordre-productions.show', $ordreProduction)
            ->with('error', "Impossible d'annuler cet ordre (statut actuel : {$ordreProduction->statut}).");
    }

    public function scan(Request $request)
    {
        $request->validate(['code_op' => 'required|string']);

        $op = OrdreProduction::where('code', $request->code_op)
            ->orWhere('numero_lot', $request->code_op)
            ->first();

        if (!$op) {
            return redirect()->route('dashboard')
                ->with('error', "Aucun OP trouvé pour \"{$request->code_op}\".");
        }

        return redirect()->route('ordre-productions.show', $op);
    }

    public function suivi()
    {
        $opsActives = OrdreProduction::whereIn('statut', ['en_cours', 'conditionne'])
            ->with(['produitFini', 'matierePremiere', 'phaseProductions.equipe',
                    'phaseProductions.transformation', 'phaseProductions.machine', 'employe'])
            ->orderBy('created_at', 'desc')->get();

        $opsTerminees = OrdreProduction::where('statut', 'termine')
            ->with(['produitFini', 'matierePremiere', 'employe'])
            ->orderBy('created_at', 'desc')->limit(7)->get();

        $totalActives    = $opsActives->count();
        $totalTerminees  = OrdreProduction::where('statut', 'termine')->count();
        $phasesEnAttente = PhaseProduction::where('statut', 'en_attente')->count();
        $phasesEnCours   = PhaseProduction::where('statut', 'en_cours')->count();
        $phasesTerminees = PhaseProduction::where('statut', 'termine')->count();
        $phasesValidees  = PhaseProduction::where('statut', 'valide')->count();

        $phasesAValider = PhaseProduction::where('statut', 'termine')
            ->with(['ordreProduction.produitFini', 'transformation', 'equipe'])
            ->orderBy('updated_at', 'desc')->limit(5)->get();

        return view('suivi-productions.index', compact(
            'opsActives', 'opsTerminees', 'totalActives', 'totalTerminees',
            'phasesEnAttente', 'phasesEnCours', 'phasesTerminees', 'phasesValidees',
            'phasesAValider'
        ));
    }

    public function destroy(OrdreProduction $ordreProduction)
    {
        if ($ordreProduction->statut !== 'annule') {
            return redirect()->route('ordre-productions.show', $ordreProduction)
                ->with('error', "Impossible de supprimer un ordre qui n'est pas annulé.");
        }

        $code = $ordreProduction->code;
        $ordreProduction->delete();

        return redirect()->route('ordre-productions.index')
            ->with('status', "Ordre de production {$code} supprimé avec succès.");
    }
}
