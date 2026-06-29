<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhaseProduction extends Model
{
    protected $fillable = [
        'ordre_production_id', 'transformation_id', 'equipe_id', 'machine_id',
        'numero_phase', 'ordre', 'duree_estimee_min',
        'date_debut', 'date_fin', 'statut', 'observations',
        'validated_by', 'validated_at',
    ];

    protected $casts = [
        'date_debut'   => 'datetime',
        'date_fin'     => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function ordreProduction() { return $this->belongsTo(OrdreProduction::class); }
    public function transformation()  { return $this->belongsTo(Transformation::class); }
    public function equipe()          { return $this->belongsTo(Equipe::class); }
    public function machine()         { return $this->belongsTo(Machine::class); }
    public function validateur()      { return $this->belongsTo(User::class, 'validated_by'); }

    /**
     * Checks whether the previous phase (by ordre) is done/validated.
     */
    public function phasePrecedenteEstValidee(): bool
    {
        if ($this->ordre <= 1) return true;

        return PhaseProduction::where('ordre_production_id', $this->ordre_production_id)
            ->where('ordre', $this->ordre - 1)
            ->whereIn('statut', ['valide', 'termine'])
            ->exists();
    }

    public function demarrer(): bool
    {
        if ($this->statut !== 'en_attente' || !$this->phasePrecedenteEstValidee()) return false;

        $this->update(['statut' => 'en_cours', 'date_debut' => now()]);
        return true;
    }

    public function marquerTermine(): bool
    {
        if ($this->statut !== 'en_cours') return false;

        $this->update(['statut' => 'termine', 'date_fin' => now()]);
        return true;
    }

    public function valider(User $admin): bool
    {
        if ($this->statut !== 'termine') return false;

        $this->update([
            'statut'       => 'valide',
            'validated_by' => $admin->id,
            'validated_at' => now(),
        ]);
        return true;
    }

    /** Label displayed in the UI based on ordre and numero_phase */
    public function getLabelAttribute(): string
    {
        return match($this->numero_phase) {
            'initiale' => 'Phase 1 — Initiale',
            'finale'   => "Phase {$this->ordre} — Finale",
            default    => "Phase {$this->ordre} — Intermédiaire",
        };
    }
}
