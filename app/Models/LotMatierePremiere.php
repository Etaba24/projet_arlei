<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotMatierePremiere extends Model
{
    protected $table = 'lots_matieres_premieres';

    protected $fillable = [
        'code_lot', 'matiere_premiere_id', 'fournisseur_id', 'numero_commande',
        'date_reception', 'date_peremption', 'quantite_initiale', 'quantite_disponible',
        'statut', 'notes',
    ];

    protected $casts = [
        'date_reception'   => 'date',
        'date_peremption'  => 'date',
        'quantite_initiale'   => 'decimal:3',
        'quantite_disponible' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::creating(function (LotMatierePremiere $lot) {
            if (empty($lot->code_lot)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $lot->code_lot = 'LOT-MP-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        // When a new lot is created, increase MP total stock
        static::created(function (LotMatierePremiere $lot) {
            $lot->matierePremiere()->increment('qte_en_stock', $lot->quantite_initiale);
        });
    }

    public function matierePremiere()
    {
        return $this->belongsTo(MatierePremiere::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function ordreProductions()
    {
        return $this->belongsToMany(
            OrdreProduction::class,
            'ordre_production_lots',
            'lot_matiere_id',
            'ordre_production_id'
        )->withPivot('quantite_utilisee')->withTimestamps();
    }

    /** Age in days since reception */
    public function getAgeJoursAttribute(): int
    {
        return (int) now()->diffInDays($this->date_reception);
    }

    /**
     * AI quality assessment based on age.
     * Returns: label, color (Tailwind), lossRate (0-1), score (0-5)
     */
    public function getQualiteAttribute(): array
    {
        $age = $this->age_jours;

        return match(true) {
            $age <= 14  => ['label' => 'Fraîche',    'color' => 'emerald', 'lossRate' => 0.015, 'score' => 5],
            $age <= 30  => ['label' => 'Excellente', 'color' => 'emerald', 'lossRate' => 0.020, 'score' => 5],
            $age <= 60  => ['label' => 'Très bonne', 'color' => 'green',   'lossRate' => 0.035, 'score' => 4],
            $age <= 90  => ['label' => 'Bonne',      'color' => 'lime',    'lossRate' => 0.050, 'score' => 4],
            $age <= 180 => ['label' => 'Correcte',   'color' => 'yellow',  'lossRate' => 0.080, 'score' => 3],
            $age <= 365 => ['label' => 'Acceptable', 'color' => 'amber',   'lossRate' => 0.120, 'score' => 2],
            $age <= 730 => ['label' => 'Dégradée',   'color' => 'orange',  'lossRate' => 0.200, 'score' => 1],
            default     => ['label' => 'Périmée',    'color' => 'red',     'lossRate' => 0.350, 'score' => 0],
        };
    }
}
