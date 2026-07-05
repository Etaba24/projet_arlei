<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class PhaseProduction extends Model
{
    use HasUuid;
    protected $fillable = [
        'ordre_production_id', 'transformation_id', 'equipe_id', 'machine_id',
        'date_attribution',
        'matiere_premiere_id', 'produit_semi_fini_id', 'quantite_mp_phase',
        'produit_semi_fini_obtenu_id', 'quantite_obtenue',
        'numero_phase', 'ordre', 'duree_estimee_min',
        'date_debut', 'date_fin', 'statut', 'observations',
        'validated_by', 'validated_at',
    ];

    protected $casts = [
        'date_attribution' => 'datetime',
        'date_debut'       => 'datetime',
        'date_fin'         => 'datetime',
        'validated_at'     => 'datetime',
    ];

    public function ordreProduction() { return $this->belongsTo(OrdreProduction::class); }
    public function transformation()  { return $this->belongsTo(Transformation::class); }
    public function equipe()          { return $this->belongsTo(Equipe::class); }
    public function machine()         { return $this->belongsTo(Machine::class); }
    public function validateur()      { return $this->belongsTo(User::class, 'validated_by'); }
    public function matierePremiere() { return $this->belongsTo(MatierePremiere::class); }
    public function produitSemiFini()        { return $this->belongsTo(ProduitSemiFini::class, 'produit_semi_fini_id'); }
    public function produitSemiFiniObtenu()  { return $this->belongsTo(ProduitSemiFini::class, 'produit_semi_fini_obtenu_id'); }

    /**
     * Checks whether the previous phase (by ordre) is done/validated.
     */
    public function phasePrecedenteEstValidee(): bool
    {
        if ($this->ordre <= 1) return true;

        return PhaseProduction::where('ordre_production_id', $this->ordre_production_id)
            ->where('ordre', $this->ordre - 1)
            ->where('statut', 'valide')
            ->exists();
    }

    public function demarrer(): bool
    {
        if ($this->statut !== 'en_attente' || !$this->phasePrecedenteEstValidee()) return false;

        $this->update(['statut' => 'en_cours', 'date_debut' => now()]);

        if ($this->machine_id) {
            Machine::where('id', $this->machine_id)->update(['etat' => 'en_marche']);
        }

        return true;
    }

    public function marquerTermine(): bool
    {
        if ($this->statut !== 'en_cours') return false;

        $this->update(['statut' => 'termine', 'date_fin' => now()]);

        if ($this->machine_id) {
            Machine::where('id', $this->machine_id)->update(['etat' => 'pret']);
        }

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

    public function invalider(User $admin, string $motif): bool
    {
        if ($this->statut !== 'termine') return false;

        $note = sprintf(
            "[%s] Phase invalidée par %s : %s",
            now()->format('d/m/Y H:i'),
            $admin->name,
            $motif
        );

        $this->update([
            'statut'       => 'en_attente',
            'date_debut'   => null,
            'date_fin'     => null,
            'observations' => trim(($this->observations ? $this->observations . "\n" : '') . $note),
            'validated_by' => null,
            'validated_at' => null,
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
