<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    protected $fillable = [
        'matricule', 'nom', 'prenom', 'residence', 'fonction',
        'telephone', 'email', 'departement_id', 'equipe_id', 'user_id',
    ];

    /**
     * Génère automatiquement le matricule au format EMP-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (Employe $employe) {
            if (empty($employe->matricule)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $employe->matricule = 'EMP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Nom complet de l'employé.
     */
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Le département de l'employé.
     */
    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    /**
     * L'équipe de l'employé.
     */
    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }

    /**
     * Le compte utilisateur lié (si existe).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les commandes MP passées par cet employé.
     */
    public function commandeMps()
    {
        return $this->hasMany(CommandeMp::class);
    }

    /**
     * Les livraisons MP réceptionnées par cet employé.
     */
    public function livraisonMps()
    {
        return $this->hasMany(LivraisonMp::class);
    }

    /**
     * Les ordres de production lancés par cet employé.
     */
    public function ordreProductions()
    {
        return $this->hasMany(OrdreProduction::class);
    }
}
