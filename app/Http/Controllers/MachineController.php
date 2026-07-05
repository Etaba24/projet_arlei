<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        $query = Machine::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($etat = $request->input('etat')) {
            $query->where('etat', $etat);
        }

        $machines = $query->orderBy('code')->paginate(10)->withQueryString();
        $totalCount = Machine::count();

        return view('machines.index', compact('machines', 'totalCount'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'designation' => mb_strtolower(trim($request->designation ?? '')),
        ]);

        $request->validate([
            'designation' => ['required', 'string', 'max:255', Rule::unique('machines', 'designation')],
            'etat'        => 'required|in:pret,en_marche,arret,en_panne,en_maintenance',
        ], [
            'designation.unique' => 'Cette machine existe déjà.',
        ]);

        Machine::create($request->only(['designation', 'etat']));

        return redirect()->route('machines.index')
            ->with('status', 'Machine ajoutée avec succès.');
    }

    public function edit(Machine $machine)
    {
        return view('machines.index');
    }

    public function update(Request $request, Machine $machine)
    {
        $designation = mb_strtolower(trim($request->designation ?? ''));

        if (Machine::where('designation', $designation)->where('id', '!=', $machine->id)->exists()) {
            return redirect()->back()->with('error', 'Cette machine existe déjà.');
        }

        $request->merge(['designation' => $designation]);

        $request->validate([
            'designation' => 'required|string|max:255',
            'etat'        => 'required|in:pret,en_marche,arret,en_panne,en_maintenance',
        ]);

        $machine->update($request->only(['designation', 'etat']));

        return redirect()->route('machines.index')
            ->with('status', 'Machine mise à jour avec succès.');
    }

    public function destroy(Machine $machine)
    {
        if ($machine->phaseProductions()->exists()) {
            return redirect()->route('machines.index')
                ->with('error', 'Impossible de supprimer cette machine car elle est historisée dans des phases de production.');
        }

        $machine->delete();

        return redirect()->route('machines.index')
            ->with('status', 'Machine supprimée avec succès.');
    }

    public function updateState(Request $request, Machine $machine)
    {
        $request->validate([
            'etat' => 'required|in:pret,en_panne,en_maintenance',
        ]);

        $machine->update(['etat' => $request->etat]);

        return redirect()->route('machines.index')
            ->with('status', 'État de la machine mis à jour avec succès.');
    }
}
