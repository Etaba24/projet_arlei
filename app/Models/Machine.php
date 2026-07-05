<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Machine extends Model
{
    use HasUuid;
    protected $fillable = ['code', 'designation', 'etat'];

    /**
     * Génère automatiquement le code au format MAC-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (Machine $machine) {
            if (empty($machine->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $machine->code = 'MAC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Vérifie si la machine est opérationnelle.
     */
    public function estDisponible(): bool
    {
        return in_array($this->etat, ['pret', 'en_marche']);
    }

    /**
     * Les phases de production utilisant cette machine.
     */
    public function phaseProductions()
    {
        return $this->hasMany(PhaseProduction::class);
    }
}
