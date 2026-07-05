<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('entreprise', 'like', "%{$search}%")
                  ->orWhere('raison_sociale', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('code')->paginate(7)->withQueryString();
        $totalCount = Client::count();

        return view('clients.index', compact('clients', 'totalCount'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'nom'           => mb_strtolower(trim($request->nom ?? '')),
            'prenom'        => mb_strtolower(trim($request->prenom ?? '')),
            'entreprise'    => $request->filled('entreprise')    ? mb_strtolower(trim($request->entreprise))    : null,
            'raison_sociale'=> $request->filled('raison_sociale')? mb_strtolower(trim($request->raison_sociale)): null,
        ]);

        $prenomNorm = mb_strtolower(trim($request->prenom ?? ''));

        $request->validate([
            'nom'           => ['required', 'string', 'max:255', Rule::unique('clients', 'nom')->where(fn($q) => $q->where('prenom', $prenomNorm))],
            'prenom'        => 'nullable|string|max:255',
            'entreprise'    => 'nullable|string|max:255',
            'raison_sociale'=> 'nullable|string|max:255',
            'telephone'     => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
        ], [
            'nom.unique' => 'Un client avec ce nom et ce prénom existe déjà.',
        ]);

        Client::create($request->only(['nom', 'prenom', 'entreprise', 'raison_sociale', 'telephone', 'email']));

        return redirect()->route('clients.index')
            ->with('status', 'Client créé avec succès.');
    }

    public function edit(Client $client)
    {
        return view('clients.index');
    }

    public function update(Request $request, Client $client)
    {
        $nom    = mb_strtolower(trim($request->nom ?? ''));
        $prenom = mb_strtolower(trim($request->prenom ?? ''));

        if (Client::where('nom', $nom)->where('prenom', $prenom)->where('id', '!=', $client->id)->exists()) {
            return redirect()->back()->with('error', 'Un client avec ce nom et ce prénom existe déjà.');
        }

        $request->merge([
            'nom'           => $nom,
            'prenom'        => $prenom,
            'entreprise'    => $request->filled('entreprise')    ? mb_strtolower(trim($request->entreprise))    : null,
            'raison_sociale'=> $request->filled('raison_sociale')? mb_strtolower(trim($request->raison_sociale)): null,
        ]);

        $request->validate([
            'nom'           => 'required|string|max:255',
            'prenom'        => 'nullable|string|max:255',
            'entreprise'    => 'nullable|string|max:255',
            'raison_sociale'=> 'nullable|string|max:255',
            'telephone'     => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
        ]);

        $client->update($request->only(['nom', 'prenom', 'entreprise', 'raison_sociale', 'telephone', 'email']));

        return redirect()->route('clients.index')
            ->with('status', 'Client mis à jour avec succès.');
    }

    public function destroy(Client $client)
    {
        if ($client->commandePfs()->exists()) {
            return redirect()->route('clients.index')
                ->with('error', 'Impossible de supprimer ce client car il a des commandes de produits finis.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('status', 'Client supprimé avec succès.');
    }
}
