<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedPokemon extends Model
{
    protected $fillable = ['name'];

    protected static function booted(): void
    {
        static::creating(function (BannedPokemon $model) {
            $model->name = strtolower($model->name);
        });
        static::updating(function (BannedPokemon $model) {
            $model->name = strtolower($model->name);
        });
    }
}
