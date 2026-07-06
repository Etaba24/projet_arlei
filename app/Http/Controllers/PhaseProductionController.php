<?php

namespace App\Http\Controllers;

use App\Models\PhaseProduction;
use App\Models\User;
use App\Notifications\TacheTerminee;
use Illuminate\Http\Request;

class PhaseProductionController extends Controller
{
    /**
     * Démarrer une phase de production (action opérateur terrain)
     */
    public function demarrer(PhaseProduction $phase)
    {
        if ($phase->demarrer()) {
            return redirect()->route('dashboard')
                ->with('status', 'Phase "' . $phase->transformation->designation . '" démarrée avec succès.');
        }

        return redirect()->route('dashboard')
            ->with('error', 'Impossible de démarrer cette phase. Vérifiez que la phase précédente a bien été validée par un administrateur.');
    }

    /**
     * Terminer une phase de production (action opérateur terrain)
     */
    public function terminer(Request $request, PhaseProduction $phase)
    {
        if ($phase->marquerTermine()) {
            $admins = User::query()
                ->where(function ($query) {
                    $query->where('role', 'admin')
                        ->orWhereHas('customRole', function ($roleQuery) {
                            $roleQuery->where('slug', '!=', 'operateur-terrain');
                        });
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new TacheTerminee($phase));
            }

            return redirect()->route('dashboard')
                ->with('status', 'Phase "' . $phase->transformation->designation . '" marquée comme terminée. En attente de validation de l\'administrateur.');
        }

        return redirect()->route('dashboard')
            ->with('error', 'Impossible de terminer cette phase.');
    }
}
