<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $equipeQuery = Equipe::query();
        $deptQuery   = Departement::query();

        if ($search) {
            $equipeQuery->where(fn($q) => $q->where('nom', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
            $deptQuery->where(fn($q)   => $q->where('designation', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
        }

        $equipes      = $equipeQuery->orderBy('code')->paginate(10)->withQueryString();
        $departements = $deptQuery->orderBy('code')->get();
        $totalEquipes = Equipe::count();

        return view('equipes.index', compact('equipes', 'departements', 'totalEquipes'));
    }

    public function create()
    {
        return view('equipes.create');
    }

    public function store(Request $request)
    {
        if ($request->input('type') === 'departement') {
            $request->merge([
                'designation' => mb_strtolower(trim($request->designation ?? '')),
            ]);

            $request->validate([
                'designation' => ['required', 'string', 'max:255', Rule::unique('departements', 'designation')],
                'description' => 'nullable|string',
            ], [
                'designation.unique' => 'Ce département existe déjà.',
            ]);

            Departement::create($request->only('designation', 'description'));
            return redirect()->route('equipes.index')->with('status', 'Département créé avec succès.');
        } else {
            $request->merge([
                'nom' => mb_strtolower(trim($request->nom ?? '')),
            ]);

            $request->validate([
                'nom'         => ['required', 'string', 'max:255', Rule::unique('equipes', 'nom')],
                'description' => 'nullable|string',
            ], [
                'nom.unique' => 'Cette équipe existe déjà.',
            ]);

            Equipe::create($request->only('nom', 'description'));
            return redirect()->route('equipes.index')->with('status', 'Équipe créée avec succès.');
        }
    }

    public function edit(Equipe $equipe)
    {
        return view('equipes.edit', compact('equipe'));
    }

    public function update(Request $request, Equipe $equipe)
    {
        $nom = mb_strtolower(trim($request->nom ?? ''));

        if (Equipe::where('nom', $nom)->where('id', '!=', $equipe->id)->exists()) {
            return redirect()->back()->with('error', 'Cette équipe existe déjà.');
        }

        $request->merge(['nom' => $nom]);

        $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $equipe->update($request->only(['nom', 'description']));

        return redirect()->route('equipes.index')
            ->with('status', 'Équipe mise à jour avec succès.');
    }

    public function destroy(Request $request, $uuid)
    {
        if ($request->input('type') === 'departement') {
            $dep = Departement::where('uuid', $uuid)->firstOrFail();
            if ($dep->employes()->exists()) {
                return redirect()->route('equipes.index')->with('error', 'Impossible de supprimer ce département car il contient des employés.');
            }
            $dep->delete();
            return redirect()->route('equipes.index')->with('status', 'Département supprimé avec succès.');
        } else {
            $eq = Equipe::where('uuid', $uuid)->firstOrFail();
            if ($eq->employes()->exists() || $eq->phaseProductions()->exists() || $eq->conditionnements()->exists()) {
                return redirect()->route('equipes.index')->with('error', 'Impossible de supprimer cette équipe car elle est liée à des employés ou des tâches de production.');
            }
            $eq->delete();
            return redirect()->route('equipes.index')->with('status', 'Équipe supprimée avec succès.');
        }
    }
}
