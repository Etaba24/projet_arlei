<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class ProduitSemiFini extends Model
{
    use HasUuid;

    protected $table = 'produits_semi_finis';

    protected $fillable = ['code', 'designation', 'unite_mesure', 'qte_en_stock'];

    protected $casts = [
        'qte_en_stock' => 'decimal:2',
    ];

    /**
     * Génère automatiquement le code au format PSF-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (ProduitSemiFini $psf) {
            if (empty($psf->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $psf->code = 'PSF-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /** Les phases qui consomment ce semi-fini en entrée. */
    public function phaseProductionsConsommees()
    {
        return $this->hasMany(PhaseProduction::class, 'produit_semi_fini_id');
    }

    /** Les phases qui produisent ce semi-fini en sortie. */
    public function phaseProductionsObtenues()
    {
        return $this->hasMany(PhaseProduction::class, 'produit_semi_fini_obtenu_id');
    }
}
