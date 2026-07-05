<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class ProduitFini extends Model
{
    use HasUuid;
    protected $table = 'produits_finis';

    protected $fillable = ['code', 'designation', 'qte_en_stock', 'unite_mesure'];

    protected $casts = [
        'qte_en_stock' => 'decimal:2',
    ];

    /**
     * Génère automatiquement le code au format PF-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (ProduitFini $pf) {
            if (empty($pf->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $pf->code = 'PF-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Les commandes PF pour ce produit.
     */
    public function commandePfs()
    {
        return $this->hasMany(CommandePf::class);
    }

    /**
     * Les ordres de production pour ce produit fini.
     */
    public function ordreProductions()
    {
        return $this->hasMany(OrdreProduction::class);
    }
}
