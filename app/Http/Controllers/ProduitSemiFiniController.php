<?php

namespace App\Http\Controllers;

use App\Models\ProduitSemiFini;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProduitSemiFiniController extends Controller
{
    public function index(Request $request)
    {
        $query = ProduitSemiFini::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        $produits = $query->orderBy('code')->paginate(7)->withQueryString();

        $totalCount = ProduitSemiFini::count();
        $qteEnStock = ProduitSemiFini::sum('qte_en_stock');
        $unites     = UniteMesure::orderBy('libelle')->get();

        return view('produits-semi-finis.index', compact('produits', 'totalCount', 'qteEnStock', 'unites'));
    }

    public function create()
    {
        return redirect()->route('produits-semi-finis.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'designation' => mb_strtolower(trim($request->designation ?? '')),
        ]);

        $validated = $request->validate([
            'designation'  => ['required', 'string', 'max:255', Rule::unique('produits_semi_finis', 'designation')],
            'unite_mesure' => 'nullable|string|max:255',
        ], [
            'designation.unique' => 'Ce produit semi-fini existe déjà.',
        ]);

        $psf = ProduitSemiFini::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'produit' => [
                    'id'          => $psf->id,
                    'designation' => $psf->designation,
                ],
            ]);
        }

        return redirect()->route('produits-semi-finis.index')
            ->with('status', 'Produit semi-fini créé avec succès.');
    }

    public function edit(ProduitSemiFini $produitSemiFini)
    {
        return redirect()->route('produits-semi-finis.index');
    }

    public function update(Request $request, ProduitSemiFini $produitSemiFini)
    {
        $designation = mb_strtolower(trim($request->designation ?? ''));

        if (ProduitSemiFini::where('designation', $designation)->where('id', '!=', $produitSemiFini->id)->exists()) {
            return redirect()->back()->with('error', 'Ce produit semi-fini existe déjà.');
        }

        $request->merge(['designation' => $designation]);

        $validated = $request->validate([
            'designation'  => 'required|string|max:255',
            'unite_mesure' => 'nullable|string|max:255',
        ]);

        $produitSemiFini->update($validated);

        return redirect()->route('produits-semi-finis.index')
            ->with('status', 'Produit semi-fini mis à jour avec succès.');
    }

    public function destroy(ProduitSemiFini $produitSemiFini)
    {
        if ($produitSemiFini->phaseProductionsConsommees()->exists() || $produitSemiFini->phaseProductionsObtenues()->exists()) {
            return redirect()->route('produits-semi-finis.index')
                ->with('error', 'Impossible de supprimer ce produit semi-fini car il est utilisé par des phases de transformation.');
        }

        $produitSemiFini->delete();

        return redirect()->route('produits-semi-finis.index')
            ->with('status', 'Produit semi-fini supprimé avec succès.');
    }
}
