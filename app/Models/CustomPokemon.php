<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomPokemon extends Model
{
    protected $fillable = [
        'name',
        'types',
        'height',
        'weight',
        'stats',
        'description',
    ];

    protected $casts = [
        'types' => 'array',
        'stats' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (CustomPokemon $model) {
            $model->name = strtolower($model->name);
        });
        static::updating(function (CustomPokemon $model) {
            $model->name = strtolower($model->name);
        });
    }
}
