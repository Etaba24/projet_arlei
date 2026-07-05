<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Departement extends Model
{
    use HasUuid;
    protected $fillable = ['code', 'designation', 'description'];

    /**
     * Génère automatiquement le code au format DEP-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (Departement $departement) {
            if (empty($departement->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $departement->code = 'DEP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Les employés de ce département.
     */
    public function employes()
    {
        return $this->hasMany(Employe::class);
    }
}
