<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Departement;
use App\Models\Equipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employe::with(['departement', 'equipe', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('matricule', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('fonction', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($departement_id = $request->input('departement_id')) {
            $query->where('departement_id', $departement_id);
        }

        if ($equipe_id = $request->input('equipe_id')) {
            $query->where('equipe_id', $equipe_id);
        }

        $employes = $query->paginate(10)->withQueryString();
        $totalCount = Employe::count();

        // 2. Récupérer les données indispensables pour les listes déroulantes des modals
        $departements = Departement::all();
        $equipes = Equipe::all();
        $users = User::all();

        // 3. Envoyer le tout à la vue unique index.blade.php
        return view('employes.index', compact('employes', 'departements', 'equipes', 'users', 'totalCount'));
    }

    public function create()
    {
        $departements = Departement::orderBy('designation')->get();
        $equipes = Equipe::orderBy('nom')->get();
        // Récupérer les utilisateurs n'ayant pas encore d'employé lié
        $users = User::whereDoesntHave('employe')->orderBy('name')->get();
        
        return view('employes.create', compact('departements', 'equipes', 'users'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nom'       => mb_strtolower(trim($request->nom ?? '')),
            'prenom'    => mb_strtolower(trim($request->prenom ?? '')),
            'fonction'  => mb_strtolower(trim($request->fonction ?? '')),
            'residence' => $request->filled('residence') ? mb_strtolower(trim($request->residence)) : null,
        ]);

        $prenomNorm = mb_strtolower(trim($request->prenom ?? ''));

        $request->validateWithBag('create', [
            'nom'            => ['required', 'string', 'max:255', Rule::unique('employes', 'nom')->where(fn($q) => $q->where('prenom', $prenomNorm))],
            'prenom'         => 'required|string|max:255',
            'residence'      => 'nullable|string|max:255',
            'fonction'       => 'required|string|max:255',
            'telephone'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'departement_id' => 'required|exists:departements,id',
            'equipe_id'      => 'required|exists:equipes,id',
            'user_id'        => 'nullable|exists:users,id|unique:employes,user_id',
            'password'       => 'nullable|string|min:6',
        ], [
            'nom.unique' => 'Un employé avec ce nom et ce prénom existe déjà.',
        ]);

        $userData = $request->all();

        if ($request->boolean('create_user_account')) {
            $request->validateWithBag('create', [
                'email'    => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
            ]);
            $user = User::create([
                'name'     => $request->prenom . ' ' . $request->nom,
                'email'    => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role'     => 'operateur',
            ]);
            $userData['user_id'] = $user->id;
        }

        Employe::create($userData);

        return redirect()->route('employes.index')
            ->with('status', 'Employé créé avec succès.');
    }

    public function edit(Employe $employe)
    {
        $departements = Departement::orderBy('designation')->get();
        $equipes = Equipe::orderBy('nom')->get();
        // Récupérer les utilisateurs libres ou déjà liés à cet employé
        $users = User::whereDoesntHave('employe', function ($query) use ($employe) {
            $query->where('id', '!=', $employe->id);
        })->orderBy('name')->get();

        return view('employes.index', compact('employe', 'departements', 'equipes', 'users'));
    }

    public function update(Request $request, Employe $employe)
    {
        $request->merge([
            'nom'       => mb_strtolower(trim($request->nom ?? '')),
            'prenom'    => mb_strtolower(trim($request->prenom ?? '')),
            'fonction'  => mb_strtolower(trim($request->fonction ?? '')),
            'residence' => $request->filled('residence') ? mb_strtolower(trim($request->residence)) : null,
        ]);

        $prenomNorm = mb_strtolower(trim($request->prenom ?? ''));

        $request->validateWithBag('edit', [
            'nom'            => ['required', 'string', 'max:255', Rule::unique('employes', 'nom')->where(fn($q) => $q->where('prenom', $prenomNorm))->ignore($employe->id)],
            'prenom'         => 'required|string|max:255',
            'residence'      => 'nullable|string|max:255',
            'fonction'       => 'required|string|max:255',
            'telephone'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'departement_id' => 'required|exists:departements,id',
            'equipe_id'      => 'required|exists:equipes,id',
            'user_id'        => 'nullable|exists:users,id|unique:employes,user_id,' . $employe->id,
            'password'       => 'nullable|string|min:6',
        ], [
            'nom.unique' => 'Un employé avec ce nom et ce prénom existe déjà.',
        ]);

        $userData = $request->all();

        // Si l'employé n'avait pas de compte utilisateur et qu'on demande d'en créer un
        if (!$employe->user_id && $request->boolean('create_user_account')) {
            $request->validateWithBag('edit', [
                'email'    => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
            ]);
            $user = User::create([
                'name'     => $request->prenom . ' ' . $request->nom,
                'email'    => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role'     => 'operateur',
            ]);
            $userData['user_id'] = $user->id;
        }

        $employe->update($userData);

        // Si l'utilisateur est lié, synchroniser son compte
        if ($employe->user) {
            $updateData = [];

            if ($request->filled('email')) {
                $newEmail = mb_strtolower(trim($request->email));
                // Vérifier unicité en excluant l'utilisateur courant
                if (\App\Models\User::where('email', $newEmail)->where('id', '!=', $employe->user->id)->exists()) {
                    return redirect()->back()
                        ->with('error', 'L\'adresse email « ' . $newEmail . ' » est déjà utilisée par un autre compte.')
                        ->withInput();
                }
                $updateData['email'] = $newEmail;
            }

            if ($request->filled('nom') || $request->filled('prenom')) {
                $updateData['name'] = $request->prenom . ' ' . $request->nom;
            }
            if ($request->filled('password')) {
                $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
            }
            if (!empty($updateData)) {
                $employe->user->update($updateData);
            }
        }

        return redirect()->route('employes.index')
            ->with('status', 'Employé mis à jour avec succès.');
    }

    public function destroy(Employe $employe)
    {
        if ($employe->ordreProductions()->exists() || $employe->commandeMps()->exists() || $employe->livraisonMps()->exists()) {
            return redirect()->route('employes.index')
                ->with('error', 'Impossible de supprimer cet employé car il a enregistré des flux logistiques ou de production.');
        }

        $employe->delete();

        return redirect()->route('employes.index')
            ->with('status', 'Employé supprimé avec succès.');
    }
}
