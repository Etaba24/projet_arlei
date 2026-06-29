<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles       = Role::withCount('users')->with('permissions')->orderBy('is_system', 'desc')->orderBy('name')->get();
        $permissions = Permission::orderBy('groupe')->orderBy('name')->get()->groupBy('groupe');

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'couleur'        => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description'    => 'nullable|string|max:255',
            'permissions'    => 'nullable|array',
            'permissions.*'  => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'couleur'     => $request->couleur,
            'description' => $request->description,
            'is_system'   => false,
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')
            ->with('status', "Rôle \"{$role->name}\" créé avec {$role->permissions()->count()} permission(s).");
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'couleur'        => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description'    => 'nullable|string|max:255',
            'permissions'    => 'nullable|array',
            'permissions.*'  => 'exists:permissions,id',
        ]);

        $role->update([
            'name'        => $request->name,
            'description' => $request->description,
            'couleur'     => $request->couleur,
        ]);

        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')
            ->with('status', "Rôle \"{$role->name}\" mis à jour.");
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'Les rôles système ne peuvent pas être supprimés.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('roles.index')
                ->with('error', "Impossible de supprimer \"{$role->name}\" : {$role->users()->count()} utilisateur(s) l'utilise encore.");
        }

        $role->delete();
        return redirect()->route('roles.index')->with('status', "Rôle supprimé.");
    }
}
