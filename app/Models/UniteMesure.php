<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniteMesure extends Model
{
    protected $table = 'unites_mesure';

    protected $fillable = ['libelle', 'type'];
}
