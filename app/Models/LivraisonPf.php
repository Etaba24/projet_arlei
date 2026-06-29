<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonPf extends Model
{
    protected $fillable = [
        'numero_bordereau', 'commande_pf_id', 'numero_lot',
        'grammage', 'type_emballage', 'quantite_expediee', 'date_livraison',
    ];

    protected $casts = [
        'quantite_expediee' => 'decimal:2',
        'date_livraison' => 'date',
    ];

    /**
     * Génère automatiquement le numéro de bordereau au format BL-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (LivraisonPf $livraison) {
            if (empty($livraison->numero_bordereau)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $livraison->numero_bordereau = 'BL-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * La commande PF liée à cette livraison.
     */
    public function commandePf()
    {
        return $this->belongsTo(CommandePf::class);
    }
}
