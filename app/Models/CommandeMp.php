<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeMp extends Model
{
    protected $fillable = [
        'numero', 'matiere_premiere_id', 'fournisseur_id',
        'employe_id', 'quantite_commandee', 'date_commande', 'statut',
    ];

    protected $casts = [
        'quantite_commandee' => 'decimal:2',
        'date_commande' => 'date',
    ];

    /**
     * Génère automatiquement le numéro au format CMD-MP-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (CommandeMp $commande) {
            if (empty($commande->numero)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $commande->numero = 'CMD-MP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * La matière première commandée.
     */
    public function matierePremiere()
    {
        return $this->belongsTo(MatierePremiere::class);
    }

    /**
     * Le fournisseur de cette commande.
     */
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**
     * L'employé qui a passé la commande.
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    /**
     * Les livraisons associées à cette commande.
     */
    public function livraisonMps()
    {
        return $this->hasMany(LivraisonMp::class);
    }
}
