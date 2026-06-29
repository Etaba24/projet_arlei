<?php

namespace App\Http\Controllers;

use App\Models\MatierePremiere;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MatierePremiereController extends Controller
{
    public function index(Request $request)
    {
        $query = MatierePremiere::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('libelle', 'like', "%{$search}%")
                  ->orWhere('variete', 'like', "%{$search}%");
            });
        }

        if ($etat = $request->input('etat')) {
            if ($etat === 'alerte') {
                $query->whereRaw('qte_en_stock <= seuil_securite');
            } elseif ($etat === 'correct') {
                $query->whereRaw('qte_en_stock > seuil_securite');
            }
        }

        if ($unite = $request->input('unite_mesure')) {
            $query->where('unite_mesure', $unite);
        }

        $matieres = $query->orderBy('code')->paginate(10)->withQueryString();
        $totalCount = MatierePremiere::count();

        return view('matieres-premieres.index', compact('matieres', 'totalCount'));
    }

    public function create()
    {
        return redirect()->route('matieres-premieres.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'libelle' => mb_strtolower(trim($request->libelle ?? '')),
            'variete' => $request->filled('variete') ? mb_strtolower(trim($request->variete)) : null,
        ]);

        $varieteNorm = mb_strtolower(trim($request->variete ?? ''));

        $validated = $request->validate([
            'libelle'        => ['required', 'string', 'max:255', Rule::unique('matieres_premieres', 'libelle')->where(fn($q) => $q->where('variete', $varieteNorm))],
            'variete'        => 'nullable|string|max:255',
            'unite_mesure'   => 'required|in:Kg,Litres,Tonnes',
            'seuil_securite' => 'required|numeric|min:0',
        ], [
            'libelle.unique' => 'Cette matière première (libellé + variété) existe déjà.',
        ]);

        $validated['qte_en_stock'] = 0;

        MatierePremiere::create($validated);

        $request->session()->forget('_old_input');

        return redirect()->route('matieres-premieres.index')
            ->with('status', 'Matière première créée avec succès.');
    }

    public function edit($id)
    {
        return redirect()->route('matieres-premieres.index');
    }

    public function update(Request $request, MatierePremiere $matieresPremiere)
    {
        $libelle = mb_strtolower(trim($request->libelle ?? ''));
        $variete = $request->filled('variete') ? mb_strtolower(trim($request->variete)) : null;

        if (MatierePremiere::where('libelle', $libelle)->where('variete', $variete)->where('id', '!=', $matieresPremiere->id)->exists()) {
            return redirect()->back()->with('error', 'Cette matière première (libellé + variété) existe déjà.');
        }

        $request->merge([
            'libelle' => $libelle,
            'variete' => $variete,
        ]);

        $validated = $request->validate([
            'libelle'        => 'required|string|max:255',
            'variete'        => 'nullable|string|max:255',
            'unite_mesure'   => 'required|in:Kg,Litres,Tonnes',
            'seuil_securite' => 'required|numeric|min:0',
            'qte_en_stock'   => 'required|numeric|min:0',
        ]);

        $matieresPremiere->update($validated);

        return redirect()->route('matieres-premieres.index')
            ->with('status', 'Matière première mise à jour avec succès.');
    }

    public function destroy(MatierePremiere $matieresPremiere)
    {
        if ($matieresPremiere->ordreProductions()->exists()) {
            return redirect()->route('matieres-premieres.index')
                ->with('error', 'Impossible de supprimer cette matière car elle est liée à des ordres de production.');
        }

        $matieresPremiere->delete();

        return redirect()->route('matieres-premieres.index')
            ->with('status', 'Matière première supprimée avec succès.');
    }
}
