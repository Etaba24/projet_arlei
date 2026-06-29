<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'couleur', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->pluck('slug')->contains($slug);
    }
}
