<?php

namespace App\Http\Controllers;

use App\Services\ProductionSimulator;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    /**
     * Simulation prédictive d'un ordre de production (appelée en AJAX
     * depuis le formulaire de création d'OP, avant confirmation).
     */
    public function simuler(Request $request, ProductionSimulator $simulator)
    {
        $validated = $request->validate([
            'lots'                        => 'required|array|min:1',
            'lots.*.lot_id'               => 'required|exists:lots_matieres_premieres,id',
            'lots.*.quantite'             => 'required|numeric|min:0.001',
            'phases'                      => 'required|array|min:1',
            'phases.*.transformation_id'  => 'nullable|exists:transformations,id',
            'phases.*.machine_id'         => 'nullable|exists:machines,id',
            'quantite_pf_cible'           => 'nullable|numeric|min:0',
        ]);

        $result = $simulator->simuler($validated);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
