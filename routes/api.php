<?php

use App\Http\Controllers\CustomPokemonController;
use App\Http\Controllers\PokemonInfoController;
use App\Http\Middleware\SuperSecretKeyMiddleware;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BannedPokemonController;

Route::middleware([SuperSecretKeyMiddleware::class])->prefix('banned')->group(function () {
    Route::get('/', [BannedPokemonController::class, 'index']);
    Route::post('/', [BannedPokemonController::class, 'store']);
    Route::delete('{bannedPokemon}', [BannedPokemonController::class, 'destroy']);
});

Route::middleware(SuperSecretKeyMiddleware::class)->group(function () {
    Route::apiResource('custom-pokemons', CustomPokemonController::class);
});


Route::post('/info', PokemonInfoController::class);
