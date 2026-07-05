<?php

namespace App\Http\Controllers;

use App\Models\ProduitFini;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProduitFiniController extends Controller
{
    public function index(Request $request)
    {
        $query = ProduitFini::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($unite = $request->input('unite_mesure')) {
            $query->where('unite_mesure', $unite);
        }

        $produits = $query->orderBy('code')->paginate(request('print') == 'true' ? 1000 : 7)->withQueryString();
        $totalCount = ProduitFini::count();
        $unites = UniteMesure::orderBy('libelle')->get();

        return view('produits-finis.index', compact('produits', 'totalCount', 'unites'));
    }

    public function create()
    {
        return view('produits-finis.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'designation' => mb_strtolower(trim($request->designation ?? '')),
        ]);

        $validated = $request->validate([
            'designation'  => ['required', 'string', 'max:255', Rule::unique('produits_finis', 'designation')],
            'unite_mesure' => 'required|string|max:255',
        ], [
            'designation.unique' => 'Ce produit fini existe déjà.',
        ]);

        ProduitFini::create($validated);

        return redirect()->route('produits-finis.index')
            ->with('status', 'Produit fini créé avec succès.');
    }

    public function edit(ProduitFini $produitFini)
    {
        return view('produits-finis.index');
    }

    public function update(Request $request, ProduitFini $produitFini)
    {
        $designation = mb_strtolower(trim($request->designation ?? ''));

        if (ProduitFini::where('designation', $designation)->where('id', '!=', $produitFini->id)->exists()) {
            return redirect()->back()->with('error', 'Ce produit fini existe déjà.');
        }

        $request->merge(['designation' => $designation]);

        $validated = $request->validate([
            'designation'  => 'required|string|max:255',
            'unite_mesure' => 'required|string|max:255',
        ]);

        $produitFini->update($validated);

        return redirect()->route('produits-finis.index')
            ->with('status', 'Produit fini mis à jour avec succès.');
    }

    public function destroy(ProduitFini $produitFini)
    {
        if ($produitFini->ordreProductions()->exists()) {
            return redirect()->route('produits-finis.index')
                ->with('error', 'Impossible de supprimer ce produit car il est lié à des ordres de production.');
        }

        $produitFini->delete();

        return redirect()->route('produits-finis.index')
            ->with('status', 'Produit fini supprimé avec succès.');
    }
}
