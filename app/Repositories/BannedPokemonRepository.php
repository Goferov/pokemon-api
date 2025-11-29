<?php

namespace App\Repositories;

use App\Models\BannedPokemon;
use Illuminate\Support\Collection;

class BannedPokemonRepository
{
    public function all(): Collection
    {
        return BannedPokemon::orderBy('name')->get();
    }

    public function create(string $name): BannedPokemon
    {
        return BannedPokemon::create(['name' => strtolower($name)]);
    }

    public function delete(BannedPokemon $bannedPokemon): void
    {
        $bannedPokemon->delete();
    }

    public function getBannedNames(): array
    {
        return BannedPokemon::pluck('name')->toArray();
    }

    public function isBanned(string $name): bool
    {
        return BannedPokemon::where('name', strtolower($name))->exists();

    }

}
