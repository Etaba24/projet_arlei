<?php

namespace App\Http\Controllers;

use App\Models\UniteMesure;
use Illuminate\Http\Request;

class UniteMesureController extends Controller
{
    /**
     * Retourne toutes les unités en JSON (pour le bouton "+" inline).
     */
    public function index()
    {
        return response()->json(UniteMesure::orderBy('libelle')->get(['id', 'libelle', 'type']));
    }

    /**
     * Crée une nouvelle unité de mesure (appel AJAX depuis les formulaires MP/PF).
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:50|unique:unites_mesure,libelle',
            'type'    => 'nullable|in:poids,volume,unité,longueur',
        ], [
            'libelle.unique' => 'Cette unité de mesure existe déjà.',
        ]);

        $unite = UniteMesure::create([
            'libelle' => trim($request->libelle),
            'type'    => $request->type ?? 'unité',
        ]);

        return response()->json(['success' => true, 'unite' => $unite]);
    }
}
