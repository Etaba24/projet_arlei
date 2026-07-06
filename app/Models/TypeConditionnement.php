<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class TypeConditionnement extends Model
{
    use HasUuid;
    protected $table = 'types_conditionnement';

    protected $fillable = ['code', 'libelle', 'description', 'unite', 'quantite_par_unite'];

    protected static function booted(): void
    {
        static::creating(function (TypeConditionnement $tc) {
            if (empty($tc->code)) {
                $last = static::orderByDesc('id')->first();
                $nextId = $last ? $last->id + 1 : 1;
                $tc->code = 'TC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
