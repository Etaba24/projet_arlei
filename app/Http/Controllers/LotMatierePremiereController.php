<?php

namespace App\Http\Controllers;

use App\Models\LotMatierePremiere;
use App\Models\MatierePremiere;
use App\Models\Fournisseur;
use Illuminate\Http\Request;

class LotMatierePremiereController extends Controller
{
    public function index(Request $request)
    {
        $query = LotMatierePremiere::with(['matierePremiere', 'fournisseur']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code_lot', 'like', "%{$search}%")
                  ->orWhereHas('matierePremiere', fn($sq) => $sq->where('libelle', 'like', "%{$search}%"));
            });
        }

        $lots      = $query->orderBy('date_reception', 'desc')->paginate(7)->withQueryString();
        $matieres  = MatierePremiere::orderBy('libelle')->get();
        $fournisseurs = Fournisseur::orderBy('nom')->get();

        return view('lots.index', compact('lots', 'matieres', 'fournisseurs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'matiere_premiere_id' => 'required|exists:matieres_premieres,id',
            'fournisseur_id'      => 'nullable|exists:fournisseurs,id',
            'numero_commande'     => 'nullable|string|max:100',
            'date_reception'      => 'required|date',
            'date_peremption'     => 'nullable|date|after:date_reception',
            'quantite_initiale'   => 'required|numeric|min:0.001',
            'notes'               => 'nullable|string|max:500',
        ]);

        $data['quantite_disponible'] = $data['quantite_initiale'];
        $data['statut'] = 'disponible';

        LotMatierePremiere::create($data);

        return redirect()->route('lots.index')->with('status', 'Lot créé et stock mis à jour.');
    }

    public function destroy(LotMatierePremiere $lot)
    {
        if ($lot->ordreProductions()->exists()) {
            return redirect()->route('lots.index')
                ->with('error', 'Ce lot a été utilisé dans un ordre de production — suppression impossible.');
        }

        // Subtract from MP stock before deleting
        $lot->matierePremiere()->decrement('qte_en_stock', $lot->quantite_disponible);
        $lot->delete();

        return redirect()->route('lots.index')->with('status', 'Lot supprimé.');
    }
}
