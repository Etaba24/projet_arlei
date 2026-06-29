<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transformation extends Model
{
    protected $fillable = ['code', 'designation', 'description'];

    /**
     * Génère automatiquement le code au format TRF-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (Transformation $transformation) {
            if (empty($transformation->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $transformation->code = 'TRF-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Les phases de production utilisant cette transformation.
     */
    public function phaseProductions()
    {
        return $this->hasMany(PhaseProduction::class);
    }
}
