<?php

namespace App\Repositories;

use App\Models\CustomPokemon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomPokemonRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return CustomPokemon::orderBy('name')->paginate($perPage);
    }

    public function all(): Collection
    {
        return CustomPokemon::orderBy('name')->get();
    }

    public function create(array $data): CustomPokemon
    {
        return CustomPokemon::create($data);
    }

    public function update(CustomPokemon $pokemon, array $data): CustomPokemon
    {
        $pokemon->update($data);
        return $pokemon;
    }

    public function delete(CustomPokemon $pokemon): void
    {
        $pokemon->delete();
    }

    public function nameExists(string $name): bool
    {
        return CustomPokemon::where('name', strtolower($name))->exists();
    }
}
