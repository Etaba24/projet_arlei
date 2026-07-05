<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class OrdreProduction extends Model
{
    use HasUuid;
    protected $fillable = [
        'code', 'produit_fini_id', 'matiere_premiere_id', 'employe_id',
        'quantite_mp_injectee', 'quantite_pf_cible', 'quantite_pf_estimee',
        'taux_perte_estime', 'duree_estimee_min',
        'numero_lot', 'qr_code_path', 'date_debut',
        'date_interruption', 'motif_interruption', 'statut',
    ];

    protected $casts = [
        'quantite_mp_injectee' => 'decimal:2',
        'quantite_pf_cible'    => 'decimal:3',
        'quantite_pf_estimee'  => 'decimal:3',
        'taux_perte_estime'    => 'decimal:2',
        'date_debut'           => 'datetime',
        'date_interruption'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrdreProduction $op) {
            $year = now()->format('Y');

            if (empty($op->code)) {
                $lastCode = static::where('code', 'like', "OP-{$year}-%")->max('code');
                $seq = $lastCode ? ((int) substr($lastCode, -4)) + 1 : 1;
                $op->code = 'OP-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }

            if (empty($op->numero_lot)) {
                $lastLot = static::where('numero_lot', 'like', "LOT-{$year}-%")->max('numero_lot');
                $seq = $lastLot ? ((int) substr($lastLot, -4)) + 1 : 1;
                $op->numero_lot = 'LOT-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /* ── Relations ── */

    public function produitFini()    { return $this->belongsTo(ProduitFini::class); }
    public function matierePremiere(){ return $this->belongsTo(MatierePremiere::class); }
    public function employe()        { return $this->belongsTo(Employe::class); }
    public function conditionnement(){ return $this->hasOne(Conditionnement::class); }

    /** Lots de matières premières utilisés (pivot) */
    public function lots()
    {
        return $this->belongsToMany(
            LotMatierePremiere::class,
            'ordre_production_lots',
            'ordre_production_id',
            'lot_matiere_id'
        )->withPivot('quantite_utilisee')->withTimestamps();
    }

    /** Phases ordonnées par ordre (supporte N phases dynamiques) */
    public function phaseProductions()
    {
        return $this->hasMany(PhaseProduction::class)->orderBy('ordre');
    }

    /* ── Business logic ── */

    /**
     * Reordonner les phases :
     * - Phase 'initiale' est la première (ordre = 1).
     * - Phase 'finale' est la dernière (ordre = total_phases).
     * - Les phases 'intermediaire' sont triées par date_attribution croissante.
     */
    public function reordonnerPhases()
    {
        $phases = $this->phaseProductions()->get();
        if ($phases->isEmpty()) return;

        $initiale = $phases->where('numero_phase', 'initiale')->first();
        $finale = $phases->where('numero_phase', 'finale')->first();
        $intermediaires = $phases->where('numero_phase', 'intermediaire')
                                 ->sortBy('date_attribution')
                                 ->values();

        $ordre = 1;
        if ($initiale) {
            $initiale->update(['ordre' => $ordre++]);
        }

        foreach ($intermediaires as $p) {
            $p->update(['ordre' => $ordre++]);
        }

        if ($finale) {
            $finale->update(['ordre' => $ordre++]);
        }
    }

    public function toutesLesPhasesSontValidees(): bool
    {
        $phases = $this->phaseProductions()->get();
        if ($phases->isEmpty()) return false;
        if ($phases->last()->numero_phase !== 'finale') return false;
        return $phases->every(fn($p) => $p->statut === 'valide');
    }

    /** Returns the type of the next phase that can be assigned, or null. */
    public function prochainePhaseAAssigner(): ?string
    {
        $phases = $this->phaseProductions()->orderBy('ordre')->get();

        if ($phases->isEmpty()) return 'initiale';

        $last = $phases->last();

        if ($last->numero_phase === 'finale') return null;
        if (!in_array($last->statut, ['termine', 'valide'])) return null;

        return $phases->count() === 1 ? 'intermediaire' : 'intermediaire';
    }

    public function interrompre(string $motif = ''): bool
    {
        if (!in_array($this->statut, ['en_attente', 'en_cours', 'conditionne'])) return false;

        // Find machines used in current or pending phases before updating phase status
        $machineIds = $this->phaseProductions()
                           ->whereIn('statut', ['en_attente', 'en_cours'])
                           ->pluck('machine_id')
                           ->filter()
                           ->unique();

        $this->update([
            'statut'             => 'interrompu',
            'date_interruption'  => now(),
            'motif_interruption' => $motif,
        ]);

        $this->phaseProductions()
             ->whereIn('statut', ['en_attente', 'en_cours'])
             ->update(['statut' => 'interrompu']);

        // Update machines state to 'arret'
        if ($machineIds->isNotEmpty()) {
            Machine::whereIn('id', $machineIds)->update(['etat' => 'arret']);
        }

        return true;
    }

    /** Resume an interrupted order */
    public function reprendre(): bool
    {
        if ($this->statut !== 'interrompu') return false;

        // Fetch machine IDs before updating phases
        $machineIds = $this->phaseProductions()
                           ->where('statut', 'interrompu')
                           ->pluck('machine_id')
                           ->filter()
                           ->unique();

        $this->update([
            'statut'             => 'en_cours',
            'date_interruption'  => null,
            'motif_interruption' => null,
        ]);

        $this->phaseProductions()
             ->where('statut', 'interrompu')
             ->update(['statut' => 'en_attente']);

        // Set machines back to 'pret' since phases are now waiting to be started
        if ($machineIds->isNotEmpty()) {
            Machine::whereIn('id', $machineIds)->update(['etat' => 'pret']);
        }

        return true;
    }

    public function annuler(string $motif = ''): bool
    {
        if (!in_array($this->statut, ['en_attente', 'en_cours', 'interrompu'])) return false;

        // Find machines before updating phase statuses
        $machineIds = $this->phaseProductions()
                           ->whereIn('statut', ['en_attente', 'en_cours', 'interrompu'])
                           ->pluck('machine_id')
                           ->filter()
                           ->unique();

        $this->update([
            'statut'             => 'annule',
            'date_interruption'  => now(),
            'motif_interruption' => $motif,
        ]);

        $this->phaseProductions()
             ->whereIn('statut', ['en_attente', 'en_cours', 'termine', 'interrompu'])
             ->update(['statut' => 'annule']);

        // Update machines state to 'pret'
        if ($machineIds->isNotEmpty()) {
            Machine::whereIn('id', $machineIds)->update(['etat' => 'pret']);
        }

        return true;
    }
}
