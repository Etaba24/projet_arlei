<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Departement;
use App\Models\Equipe;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        $employes = $query->paginate(request('print') == 'true' ? 1000 : 7)->withQueryString();
        $totalCount = Employe::count();

        // 2. Récupérer les données indispensables pour les listes déroulantes des modals
        $departements = Departement::all();
        $equipes = Equipe::all();
        $users = User::all();
        $roles = Role::orderBy('name')->get();

        // 3. Envoyer le tout à la vue unique index.blade.php
        return view('employes.index', compact('employes', 'departements', 'equipes', 'users', 'roles', 'totalCount'));
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

        $validated = $request->validateWithBag('create', [
            'nom'            => ['required', 'string', 'max:255', Rule::unique('employes', 'nom')->where(fn($q) => $q->where('prenom', $prenomNorm))],
            'prenom'         => 'required|string|max:255',
            'residence'      => 'nullable|string|max:255',
            'fonction'       => 'required|string|max:255',
            'telephone'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'departement_id' => 'required|exists:departements,id',
            'equipe_id'      => 'required|exists:equipes,id',
            'user_id'        => 'nullable|exists:users,id|unique:employes,user_id',
        ], [
            'nom.unique' => 'Un employé avec ce nom et ce prénom existe déjà.',
        ]);

        if ($request->boolean('create_user_account')) {
            $request->validateWithBag('create', [
                'email'    => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
                'role_id'  => 'required|exists:roles,id',
            ], [
                'role_id.required' => 'Veuillez sélectionner un rôle pour le nouveau compte.',
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            if ($request->boolean('create_user_account')) {
                $user = User::create([
                    'name'     => $request->prenom . ' ' . $request->nom,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id'  => $request->role_id,
                ]);
                $validated['user_id'] = $user->id;
            }

            Employe::create($validated);
        });

        return redirect()->route('employes.index')
            ->with('status', 'Employé créé avec succès.');
    }

    public function edit(Employe $employe)
    {
        // L'édition se fait via le modal de la page index.
        return redirect()->route('employes.index');
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

        $validated = $request->validateWithBag('edit', [
            'nom'            => ['required', 'string', 'max:255', Rule::unique('employes', 'nom')->where(fn($q) => $q->where('prenom', $prenomNorm))->ignore($employe->id)],
            'prenom'         => 'required|string|max:255',
            'residence'      => 'nullable|string|max:255',
            'fonction'       => 'required|string|max:255',
            'telephone'      => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'departement_id' => 'required|exists:departements,id',
            'equipe_id'      => 'required|exists:equipes,id',
            'user_id'        => 'nullable|exists:users,id|unique:employes,user_id,' . $employe->id,
        ], [
            'nom.unique' => 'Un employé avec ce nom et ce prénom existe déjà.',
        ]);

        // Si l'employé n'avait pas de compte utilisateur et qu'on demande d'en créer un
        $creerCompte = !$employe->user_id && $request->boolean('create_user_account');
        if ($creerCompte) {
            $request->validateWithBag('edit', [
                'email'    => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
                'role_id'  => 'required|exists:roles,id',
            ], [
                'role_id.required' => 'Veuillez sélectionner un rôle pour le nouveau compte.',
            ]);
        }

        DB::transaction(function () use ($request, $employe, $validated, $creerCompte) {
            if ($creerCompte) {
                $user = User::create([
                    'name'     => $request->prenom . ' ' . $request->nom,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role_id'  => $request->role_id,
                ]);
                $validated['user_id'] = $user->id;
            }

            $employe->update($validated);

            // Si un compte existant est lié, on autorise seulement la modification du mot de passe
            // (on ne synchronise plus le nom et l'email pour éviter d'écraser les identifiants de connexion)
            if (!$creerCompte && $employe->user_id) {
                if ($request->filled('password')) {
                    $user = User::find($employe->user_id);
                    if ($user) {
                        $user->update([
                            'password' => Hash::make($request->password),
                        ]);
                    }
                }
            }
        });

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
