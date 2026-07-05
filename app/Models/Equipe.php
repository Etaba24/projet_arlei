<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Equipe extends Model
{
    use HasUuid;
    protected $fillable = ['code', 'nom', 'description'];

    /**
     * Génère automatiquement le code au format EQP-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (Equipe $equipe) {
            if (empty($equipe->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $equipe->code = 'EQP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Les employés de cette équipe.
     */
    public function employes()
    {
        return $this->hasMany(Employe::class);
    }

    /**
     * Les phases de production attribuées à cette équipe.
     */
    public function phaseProductions()
    {
        return $this->hasMany(PhaseProduction::class);
    }

    /**
     * Les conditionnements attribués à cette équipe.
     */
    public function conditionnements()
    {
        return $this->hasMany(Conditionnement::class);
    }
}
