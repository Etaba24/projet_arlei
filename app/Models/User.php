<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasUuid;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasUuid;
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'role_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Le rôle personnalisé (role_id) est la seule source de vérité :
     * la colonne legacy `role` est recalculée automatiquement à chaque
     * sauvegarde pour ne jamais se désynchroniser.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->role_id) {
                $role = Role::with('permissions')->find($user->role_id);
                $user->role = $role && $role->hasPermission('admin.utilisateurs')
                    ? 'admin'
                    : 'operateur';
            }
        });
    }

    public function employe()
    {
        return $this->hasOne(Employe::class);
    }

    /** Relation vers le rôle personnalisé */
    public function customRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin'
            || ($this->customRole && $this->customRole->hasPermission('admin.utilisateurs'));
    }

    /**
     * Détermine si l'utilisateur doit voir l'interface complète (admin dashboard + nav complète).
     * Tout rôle personnalisé autre que "Opérateur Terrain" accède à l'interface complète,
     * filtrée ensuite par ses permissions dans la navigation.
     */
    public function hasAdminInterface(): bool
    {
        if ($this->role === 'admin') return true;

        if (!$this->role_id || !$this->customRole) return false;

        return $this->customRole->slug !== 'operateur-terrain';
    }

    public function isOperateur(): bool
    {
        return !$this->hasAdminInterface();
    }

    /**
     * Check if the user has a specific permission.
     * System admins bypass all checks.
     */
    public function hasPermission(string $slug): bool
    {
        if ($this->role === 'admin') return true;

        if (!$this->role_id || !$this->customRole) return false;

        // Eager load once
        if (!$this->customRole->relationLoaded('permissions')) {
            $this->customRole->load('permissions');
        }

        return $this->customRole->hasPermission($slug);
    }
}
