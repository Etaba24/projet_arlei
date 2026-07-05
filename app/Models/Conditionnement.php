<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Conditionnement extends Model
{
    use HasUuid;
    protected $fillable = [
        'ordre_production_id', 'equipe_id', 'type_emballage',
        'quantite_produite', 'quantite_mp_consommee', 'perte',
        'date_fabrication', 'date_peremption', 'statut',
        'validated_by', 'validated_at',
    ];

    protected $casts = [
        'quantite_produite' => 'decimal:2',
        'quantite_mp_consommee' => 'decimal:2',
        'perte' => 'decimal:2',
        'date_fabrication' => 'date',
        'date_peremption' => 'date',
        'validated_at' => 'datetime',
    ];

    /**
     * L'ordre de production parent.
     */
    public function ordreProduction()
    {
        return $this->belongsTo(OrdreProduction::class);
    }

    /**
     * L'équipe de conditionnement.
     */
    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }

    /**
     * L'admin qui a validé le conditionnement.
     */
    public function validateur()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Calcule automatiquement la perte avant la sauvegarde.
     */
    protected static function booted(): void
    {
        static::saving(function (Conditionnement $conditionnement) {
            if ($conditionnement->quantite_mp_consommee && $conditionnement->quantite_produite) {
                $conditionnement->perte = $conditionnement->quantite_mp_consommee - $conditionnement->quantite_produite;
            }
        });
    }

    /**
     * Valide le conditionnement et met à jour le stock de produits finis.
     */
    public function valider(User $admin): bool
    {
        if ($this->statut !== 'termine') {
            return false;
        }

        $this->update([
            'statut' => 'valide',
            'validated_by' => $admin->id,
            'validated_at' => now(),
        ]);

        // Incrémenter le stock de produits finis
        $ordreProduction = $this->ordreProduction;
        $produitFini = $ordreProduction->produitFini;
        $produitFini->increment('qte_en_stock', $this->quantite_produite);

        // Clôturer l'ordre de production
        $ordreProduction->update(['statut' => 'termine']);

        return true;
    }
}
