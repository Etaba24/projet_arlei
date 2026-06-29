<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatierePremiere extends Model
{
    protected $table = 'matieres_premieres';

    protected $fillable = [
        'code', 'libelle', 'variete', 'qte_en_stock', 'unite_mesure', 'seuil_securite',
    ];

    protected $casts = [
        'qte_en_stock' => 'decimal:2',
        'seuil_securite' => 'decimal:2',
    ];

    /**
     * Génère automatiquement le code au format MP-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (MatierePremiere $mp) {
            if (empty($mp->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $mp->code = 'MP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Vérifie si le stock est en dessous du seuil de sécurité.
     */
    public function estEnAlerte(): bool
    {
        return $this->qte_en_stock <= $this->seuil_securite;
    }

    /**
     * Les commandes MP liées à cette matière.
     */
    public function commandeMps()
    {
        return $this->hasMany(CommandeMp::class);
    }

    /**
     * Les ordres de production qui consomment cette matière.
     */
    public function ordreProductions()
    {
        return $this->hasMany(OrdreProduction::class);
    }
}
