<?php

namespace App\Http\Controllers;

use App\Models\MatierePremiere;
use App\Models\ProduitFini;
use App\Models\Fournisseur;
use App\Models\Client;
use App\Models\CommandeMp;
use App\Models\LivraisonMp;
use App\Models\CommandePf;
use App\Models\LivraisonPf;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LogistiqueController extends Controller
{
    /**
     * Page des flux Matières Premières (Commandes et Réceptions)
     */
    public function indexMp(Request $request)
    {
        $tab = $request->input('tab', 'commandes');
        $matieres = MatierePremiere::orderBy('libelle')->get();
        $fournisseurs = Fournisseur::orderBy('designation')->get();
        $employes = Employe::orderBy('nom')->get();

        $commandes = CommandeMp::with(['matierePremiere', 'fournisseur', 'employe', 'livraisonMps'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $livraisons = LivraisonMp::with(['commandeMp.matierePremiere', 'employe'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Liste non paginée pour le menu déroulant du modal "Pesée Balance"
        $commandesEnAttente = CommandeMp::with(['matierePremiere', 'livraisonMps'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('logistique.mp', compact('matieres', 'fournisseurs', 'employes', 'commandes', 'livraisons', 'commandesEnAttente', 'tab'));
    }

    /**
     * Enregistrer une nouvelle commande de matière première avec CODE AUTOMATIQUE
     */
    /**
     * Enregistrer une nouvelle commande de matière première avec CODE AUTOMATIQUE
     */
    public function storeCommandeMp(Request $request)
    {
        $request->validate([
            'employe_id'          => 'required|exists:employes,id',
            'matiere_premiere_id' => 'required|exists:matieres_premieres,id',
            'fournisseur_id'      => 'required|exists:fournisseurs,id',
            'quantite_commandee'  => 'required|numeric|min:0.01',
            'date_commande'       => 'required|date',
        ]);

        // Génération automatique d'un code unique pour la commande : CMD-AAAAMMJJ-XXXX
        do {
            $code = 'CMD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (CommandeMp::where('numero', $code)->exists());

        // Construction sécurisée du tableau d'insertion
        $commandeData = [
            'numero'                => $code,
            'matiere_premiere_id' => $request->matiere_premiere_id,
            'fournisseur_id'      => $request->fournisseur_id,
            'quantite_commandee'  => $request->quantite_commandee,
            'date_commande'       => $request->date_commande,
            'statut'              => 'en_attente',
            // On associe l'employé lié à l'utilisateur connecté s'il existe, sinon null
            'employe_id'          => $request->employe_id, // ou Auth::user()->employe_id si tu veux l'associer automatiquement à l'utilisateur connecté
            ];

        CommandeMp::create($commandeData);

        return redirect()->back()->with('success', 'Commande ' . $code . ' enregistrée avec succès.');
    }

    /**
     * Réceptionner / Livrer une commande de matière première (Pesée Balance)
     */
    /**
     * Réceptionner / Livrer une commande de matière première (Pesée Balance)
     */
    public function storeLivraisonMp(Request $request)
    {
        $request->validate([
            'commande_mp_id' => 'required|exists:commande_mps,id',
            'employe_id'     => 'required|exists:employes,id',
            'poids_net'      => 'required|numeric|min:0.01',
            'date_pesee'     => 'required|date',
        ]);

        // 1. On charge la commande avec le bon nom de relation : livraisonMps
        $commande = CommandeMp::with('livraisonMps')->findOrFail($request->commande_mp_id);

        // 2. Calcul avec le bon nom de relation
        $quantiteDejaRecue = $commande->livraisonMps->sum('quantite_recue');
        $resteALivrer = $commande->quantite_commandee - $quantiteDejaRecue;

        // 3. Blocage si dépassement
        if ($request->poids_net > $resteALivrer) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'poids_net' => "Action impossible. La quantité saisie ({$request->poids_net}) dépasse le reste à livrer pour cette commande ({$resteALivrer})."
            ]);
        }

        DB::transaction(function () use ($request, $commande, $quantiteDejaRecue) {
            do {
                $numBordereau = 'REC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            } while (LivraisonMp::where('numero_bordereau', $numBordereau)->exists());

            $livraison = LivraisonMp::create([
                'numero_bordereau' => $numBordereau,
                'commande_mp_id'   => $request->commande_mp_id,
                'employe_id'       => $request->employe_id,
                'quantite_recue'   => $request->poids_net, 
                'date_reception'   => $request->date_pesee,
            ]);
            
            $nouveauTotalRecu = $quantiteDejaRecue + $request->poids_net;

            // Mise à jour du statut selon l'avancement global
            if ($nouveauTotalRecu >= $commande->quantite_commandee) {
                $commande->update(['statut' => 'livree']);
            } else {
                $commande->update(['statut' => 'en_attente']);
            }

            // Incrémenter le stock usine
            $mp = $commande->matierePremiere;
            $mp->increment('qte_en_stock', $request->poids_net);
        });

        return redirect()->back()->with('success', 'Réception enregistrée avec succès.');
    }
    /**
     * Page des flux Produits Finis (Commandes et Expéditions)
     */
    public function indexPf(Request $request)
    {
        $tab = $request->input('tab', 'commandes');
        $produits = ProduitFini::orderBy('designation')->get();
        $clients = Client::orderBy('nom')->get();

        $commandes = CommandePf::with(['produitFini', 'client', 'livraisonPfs'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $livraisons = LivraisonPf::with(['commandePf.produitFini'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Liste non paginée pour le menu déroulant du modal "Expédition"
        $commandesPendantes = CommandePf::with(['produitFini', 'livraisonPfs'])
            ->whereIn('statut', ['en_attente', 'en_preparation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('logistique.pf', compact('produits', 'clients', 'commandes', 'livraisons', 'commandesPendantes', 'tab'));
    }

    /**
     * Enregistrer une nouvelle commande de produit fini (Optionnel : Code généré si nécessaire)
     */
    public function storeCommandePf(Request $request)
    {
        $request->validate([
            'produit_fini_id' => 'required|exists:produits_finis,id',
            'client_id'       => 'required|exists:clients,id',
            'quantite_commandee' => 'required|numeric|min:0.01',
            'date_commande'   => 'required|date',
        ]);

        $produit = ProduitFini::findOrFail($request->produit_fini_id);

        if ($produit->qte_en_stock < $request->quantite_commandee) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantite_commandee' => "Stock insuffisant. La quantité en stock est de {$produit->qte_en_stock} {$produit->unite_mesure}."
            ]);
        }

        // Génération automatique du code : CMD-PF-ANNEEMOISJOUR-COMPTEUR
        $dateKey = date('Ymd');
        $nombreCommandesAujourdhui = CommandePf::whereDate('created_at', today())->count();
        $sequence = str_pad($nombreCommandesAujourdhui + 1, 4, '0', STR_PAD_LEFT);
        
        $codeAutomatique = "CMD-PF-{$dateKey}-{$sequence}"; // ex: CMD-PF-20260611-0001

        CommandePf::create([
            'numero'             => $codeAutomatique,
            'produit_fini_id'    => $request->produit_fini_id,
            'client_id'          => $request->client_id,
            'quantite_commandee' => $request->quantite_commandee,
            'date_commande'      => $request->date_commande,
            'statut'             => 'en_attente',
        ]);

        return redirect()->back()->with('success', 'Commande client enregistrée avec succès sous le code ' . $codeAutomatique);
    }

    /**
     * Expédier / Livrer une commande de produit fini
     */
    public function storeLivraisonPf(Request $request)
    {
        $request->validate([
            'commande_pf_id'    => 'required|exists:commande_pfs,id',
            'quantite_expediee' => 'required|numeric|min:0.01',
            'date_livraison'    => 'required|date',
            'type_emballage'    => 'nullable|string',
            'grammage'          => 'nullable|string',
        ]);

        $commande = CommandePf::with('livraisonPfs')->findOrFail($request->commande_pf_id);
        
        $quantiteDejaExpediee = $commande->livraisonPfs->sum('quantite_expediee');
        $resteAExpedier = $commande->quantite_commandee - $quantiteDejaExpediee;

        if ($request->quantite_expediee > $resteAExpedier) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantite_expediee' => "Action impossible. La quantité saisie ({$request->quantite_expediee}) dépasse le reste à expédier pour cette commande ({$resteAExpedier})."
            ]);
        }

        $produit = $commande->produitFini;
        if ($produit->qte_en_stock < $request->quantite_expediee) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantite_expediee' => "Stock insuffisant au moment de l'expédition. Quantité en stock: {$produit->qte_en_stock} {$produit->unite_mesure}."
            ]);
        }

        DB::transaction(function () use ($request, $commande, $quantiteDejaExpediee, $produit) {
            // Génération automatique du numéro de lot : LOT-ANNEEMOISJOUR-COMPTEUR
            $dateKey = date('Ymd');
            $nombreExpeditionsAujourdhui = LivraisonPf::whereDate('created_at', today())->count();
            $sequence = str_pad($nombreExpeditionsAujourdhui + 1, 4, '0', STR_PAD_LEFT);
            
            $lotAutomatique = "LOT-{$dateKey}-{$sequence}"; // ex: LOT-20260611-0001

            // Création de l'expédition
            LivraisonPf::create([
                'numero_lot'        => $lotAutomatique,
                'commande_pf_id'    => $request->commande_pf_id,
                'quantite_expediee' => $request->quantite_expediee,
                'date_livraison'    => $request->date_livraison,
                'type_emballage'    => $request->type_emballage,
                'grammage'          => $request->grammage,
            ]);

            $nouveauTotalExpedie = $quantiteDejaExpediee + $request->quantite_expediee;

            if ($nouveauTotalExpedie >= $commande->quantite_commandee) {
                $commande->update(['statut' => 'livree']);
            } else {
                $commande->update(['statut' => 'en_preparation']);
            }

            // Décrémenter le stock usine
            $produit->decrement('qte_en_stock', $request->quantite_expediee);
        });

        return redirect()->back()->with('success', 'Expédition validée avec succès.');
    }
}