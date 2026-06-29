<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->with('customRole')->orderBy('name')->paginate(10)->withQueryString();
        $totalCount = User::count();
        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'totalCount', 'roles'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'name'  => mb_strtolower(trim($request->name ?? '')),
            'email' => mb_strtolower(trim($request->email ?? '')),
        ]);

        $request->validateWithBag('create', [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'required|string|min:6',
            'role_id'  => 'required|exists:roles,id',
        ], [
            'email.unique'    => 'Cet email est déjà utilisé.',
            'role_id.required' => 'Veuillez sélectionner un rôle.',
        ]);

        // Infer legacy role from selected custom role if needed
        $selectedRole = $request->role_id ? Role::find($request->role_id) : null;
        $legacyRole = $request->role;
        if ($selectedRole && $selectedRole->hasPermission('admin.utilisateurs')) {
            $legacyRole = 'admin';
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $legacyRole,
            'role_id'  => $request->role_id,
        ]);

        return redirect()->route('users.index')
            ->with('status', 'Compte utilisateur créé avec succès.');
    }

    public function update(Request $request, User $user)
    {
        $email = mb_strtolower(trim($request->email ?? ''));

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return redirect()->back()->with('error', 'Cet email est déjà utilisé par un autre compte.');
        }

        $request->merge([
            'name'  => mb_strtolower(trim($request->name ?? '')),
            'email' => $email,
        ]);

        $request->validateWithBag('edit', [
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255',
            'role_id'          => 'required|exists:roles,id',
            'password'         => 'nullable|string|min:6|required_with:current_password',
            'current_password' => 'nullable|string|required_with:password',
        ], [
            'role_id.required' => 'Veuillez sélectionner un rôle.',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'L\'ancien mot de passe est incorrect.'], 'edit')
                    ->withInput()
                    ->with('open_edit_user_id', $user->id);
            }
        }

        // Infer legacy role from selected custom role
        $selectedRole = $request->role_id ? Role::find($request->role_id) : null;
        $legacyRole = $request->role;
        if ($selectedRole && $selectedRole->hasPermission('admin.utilisateurs')) {
            $legacyRole = 'admin';
        } elseif ($selectedRole && !$selectedRole->hasPermission('admin.utilisateurs')) {
            $legacyRole = 'operateur';
        }

        $updateData = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role'    => $legacyRole,
            'role_id' => $request->role_id ?: null,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('status', 'Compte utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer le compte actuellement connecté.');
        }

        if ($user->employe) {
            $user->employe->update(['user_id' => null]);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('status', 'Compte utilisateur supprimé avec succès.');
    }
}
