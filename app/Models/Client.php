<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Client extends Model
{
    use HasUuid;
    protected $fillable = [
        'code', 'nom', 'prenom', 'entreprise',
        'raison_sociale', 'telephone', 'email',
    ];

    /**
     * Génère automatiquement le code au format CLT-00001.
     */
    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $client->code = 'CLT-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Nom d'affichage (entreprise ou nom/prénom).
     */
    public function getNomAffichageAttribute(): string
    {
        if ($this->entreprise) {
            return $this->entreprise;
        }
        return trim($this->prenom . ' ' . $this->nom);
    }

    /**
     * Les commandes PF de ce client.
     */
    public function commandePfs()
    {
        return $this->hasMany(CommandePf::class);
    }
}
