<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BannedPokemonController;

Route::prefix('banned')->group(function () {
    Route::get('/', [BannedPokemonController::class, 'index']);
    Route::post('/', [BannedPokemonController::class, 'store']);
    Route::delete('{bannedPokemon}', [BannedPokemonController::class, 'destroy']);
});
