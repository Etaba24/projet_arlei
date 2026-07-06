<?php

namespace App\Http\Controllers;

use App\Models\TypeConditionnement;
use Illuminate\Http\Request;

class TypeConditionnementController extends Controller
{
    public function index(Request $request)
    {
        $query = TypeConditionnement::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $types = $query->orderBy('libelle')->paginate(7)->withQueryString();
        $totalCount = TypeConditionnement::count();

        return view('conditionnements.index', compact('types', 'totalCount'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'libelle' => ucfirst(mb_strtolower(trim($request->libelle ?? ''))),
        ]);

        $request->validate([
            'libelle'             => 'required|string|max:100|unique:types_conditionnement,libelle',
            'description'         => 'nullable|string|max:255',
            'unite'               => 'nullable|string|max:30',
            'quantite_par_unite'  => 'nullable|numeric|min:0.0001',
        ], [
            'libelle.unique' => 'Ce type de conditionnement existe déjà.',
        ]);

        TypeConditionnement::create($request->only(['libelle', 'description', 'unite', 'quantite_par_unite']));

        return redirect()->route('conditionnements.index')
            ->with('status', 'Type de conditionnement créé avec succès.');
    }

    public function update(Request $request, TypeConditionnement $conditionnement)
    {
        $libelle = ucfirst(mb_strtolower(trim($request->libelle ?? '')));

        if (TypeConditionnement::where('libelle', $libelle)->where('id', '!=', $conditionnement->id)->exists()) {
            return redirect()->back()->with('error', 'Ce type de conditionnement existe déjà.');
        }

        $request->merge(['libelle' => $libelle]);
        $request->validate([
            'libelle'             => 'required|string|max:100',
            'description'         => 'nullable|string|max:255',
            'unite'               => 'nullable|string|max:30',
            'quantite_par_unite'  => 'nullable|numeric|min:0.0001',
        ]);

        $conditionnement->update($request->only(['libelle', 'description', 'unite', 'quantite_par_unite']));

        return redirect()->route('conditionnements.index')
            ->with('status', 'Type de conditionnement mis à jour.');
    }

    public function destroy(TypeConditionnement $conditionnement)
    {
        $conditionnement->delete();

        return redirect()->route('conditionnements.index')
            ->with('status', 'Type de conditionnement supprimé.');
    }
}
