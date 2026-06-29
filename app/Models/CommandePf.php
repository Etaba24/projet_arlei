<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandePf extends Model
{
    protected $fillable = [
        'numero', 'produit_fini_id', 'client_id',
        'quantite_commandee', 'date_commande', 'statut',
    ];

    protected $casts = [
        'quantite_commandee' => 'decimal:2',
        'date_commande' => 'date',
    ];

    /**
     * Génère automatiquement le numéro au format CMD-PF-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (CommandePf $commande) {
            if (empty($commande->numero)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $commande->numero = 'CMD-PF-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Le produit fini commandé.
     */
    public function produitFini()
    {
        return $this->belongsTo(ProduitFini::class);
    }

    /**
     * Le client qui a passé la commande.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Les livraisons associées à cette commande.
     */
    public function livraisonPfs()
    {
        return $this->hasMany(LivraisonPf::class);
    }
}
