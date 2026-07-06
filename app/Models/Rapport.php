<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'titre',
        'contenu',
        'statut',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
