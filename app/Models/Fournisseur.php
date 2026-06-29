<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $fillable = [
        'code', 'designation', 'nationalite', 'localite',
        'raison_sociale', 'telephone', 'email',
    ];

    /**
     * Génère automatiquement le code au format FRN-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (Fournisseur $fournisseur) {
            if (empty($fournisseur->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $fournisseur->code = 'FRN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Les commandes MP passées à ce fournisseur.
     */
    public function commandeMps()
    {
        return $this->hasMany(CommandeMp::class);
    }
}
