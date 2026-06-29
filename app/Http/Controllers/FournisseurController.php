<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        $query = Fournisseur::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('nationalite', 'like', "%{$search}%")
                  ->orWhere('localite', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $fournisseurs = $query->orderBy('code')->paginate(10)->withQueryString();
        $totalCount = Fournisseur::count();

        return view('fournisseurs.index', compact('fournisseurs', 'totalCount'));
    }

    public function create()
    {
        return view('fournisseurs.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'designation'    => mb_strtolower(trim($request->designation ?? '')),
            'nationalite'    => $request->filled('nationalite')    ? mb_strtolower(trim($request->nationalite))    : null,
            'localite'       => $request->filled('localite')       ? mb_strtolower(trim($request->localite))       : null,
            'raison_sociale' => $request->filled('raison_sociale') ? mb_strtolower(trim($request->raison_sociale)) : null,
        ]);

        $request->validate([
            'designation'    => ['required', 'string', 'max:255', Rule::unique('fournisseurs', 'designation')],
            'nationalite'    => 'nullable|string|max:255',
            'localite'       => 'nullable|string|max:255',
            'raison_sociale' => 'nullable|string|max:255',
            'telephone'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
        ], [
            'designation.unique' => 'Ce fournisseur existe déjà.',
        ]);

        Fournisseur::create($request->only(['designation', 'nationalite', 'localite', 'raison_sociale', 'telephone', 'email']));

        return redirect()->route('fournisseurs.index')
            ->with('status', 'Fournisseur créé avec succès.');
    }

    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.index', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $designation = mb_strtolower(trim($request->designation ?? ''));

        if (Fournisseur::where('designation', $designation)->where('id', '!=', $fournisseur->id)->exists()) {
            return redirect()->back()->with('error', 'Ce fournisseur existe déjà.');
        }

        $request->merge([
            'designation'    => $designation,
            'nationalite'    => $request->filled('nationalite')    ? mb_strtolower(trim($request->nationalite))    : null,
            'localite'       => $request->filled('localite')       ? mb_strtolower(trim($request->localite))       : null,
            'raison_sociale' => $request->filled('raison_sociale') ? mb_strtolower(trim($request->raison_sociale)) : null,
        ]);

        $request->validate([
            'designation'    => 'required|string|max:255',
            'nationalite'    => 'nullable|string|max:255',
            'localite'       => 'nullable|string|max:255',
            'raison_sociale' => 'nullable|string|max:255',
            'telephone'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
        ]);

        $fournisseur->update($request->only(['designation', 'nationalite', 'localite', 'raison_sociale', 'telephone', 'email']));

        return redirect()->route('fournisseurs.index')
            ->with('status', 'Fournisseur mis à jour avec succès.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        if ($fournisseur->commandeMps()->exists()) {
            return redirect()->route('fournisseurs.index')
                ->with('error', 'Impossible de supprimer ce fournisseur car il a des commandes en cours.');
        }

        $fournisseur->delete();

        return redirect()->route('fournisseurs.index')
            ->with('status', 'Fournisseur supprimé avec succès.');
    }
}
