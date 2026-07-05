<?php

namespace App\Http\Controllers;

use App\Models\Transformation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransformationController extends Controller
{
    public function index(Request $request)
    {
        $query = Transformation::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('designation', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $transformations = $query->orderBy('code')->paginate(7)->withQueryString();
        $totalCount = Transformation::count();

        return view('transformations.index', compact('transformations', 'totalCount'));
    }

    public function create()
    {
        return view('transformations.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'designation' => mb_strtolower(trim($request->designation ?? '')),
        ]);

        $request->validate([
            'designation' => ['required', 'string', 'max:255', Rule::unique('transformations', 'designation')],
            'description' => 'nullable|string',
        ], [
            'designation.unique' => 'Ce type de transformation existe déjà.',
        ]);

        Transformation::create($request->only(['designation', 'description']));

        return redirect()->route('transformations.index')
            ->with('status', 'Type de transformation créé avec succès.');
    }

    public function edit(Transformation $transformation)
    {
        return view('transformations.index');
    }

    public function update(Request $request, Transformation $transformation)
    {
        $designation = mb_strtolower(trim($request->designation ?? ''));

        if (Transformation::where('designation', $designation)->where('id', '!=', $transformation->id)->exists()) {
            return redirect()->back()->with('error', 'Ce type de transformation existe déjà.');
        }

        $request->merge(['designation' => $designation]);

        $request->validate([
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $transformation->update($request->only(['designation', 'description']));

        return redirect()->route('transformations.index')
            ->with('status', 'Type de transformation mis à jour avec succès.');
    }

    public function destroy(Transformation $transformation)
    {
        if ($transformation->phaseProductions()->exists()) {
            return redirect()->route('transformations.index')
                ->with('error', 'Impossible de supprimer cette transformation car elle est historisée dans des phases de production.');
        }

        $transformation->delete();

        return redirect()->route('transformations.index')
            ->with('status', 'Type de transformation supprimé avec succès.');
    }
}
