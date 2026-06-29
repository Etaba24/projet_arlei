<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivraisonMp extends Model
{
    protected $fillable = [
        'numero_bordereau', 'commande_mp_id', 'employe_id',
        'quantite_recue', 'date_reception', 'observations',
    ];

    protected $casts = [
        'quantite_recue' => 'decimal:2',
        'date_reception' => 'date',
    ];

    /**
     * La commande MP liée à cette livraison.
     */
    public function commandeMp()
    {
        return $this->belongsTo(CommandeMp::class);
    }

    /**
     * L'employé réceptionnaire.
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}
