<?php

namespace App\Services;

use App\Clients\PokeApiClient;
use App\Models\CustomPokemon;
use App\Repositories\CustomPokemonRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class CustomPokemonService
{
    public function __construct(
        private readonly CustomPokemonRepository $repository,
        private readonly PokeApiClient $client
    ) {}

    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function create(array $data): CustomPokemon
    {
        $name = strtolower($data['name']);

        if ($this->repository->nameExists($name)) {
            throw new RuntimeException("Pokemon with name '{$name}' already exists locally.");
        }

        $existsInPokeApi = $this->client->getPokemon($name);
        if ($existsInPokeApi) {
            throw new RuntimeException("Pokemon with name '{$name}' already exists in PokeAPI.");
        }

        return $this->repository->create($data);
    }

    public function update(CustomPokemon $pokemon, array $data): CustomPokemon
    {
        return $this->repository->update($pokemon, $data);
    }

    public function delete(CustomPokemon $pokemon): void
    {
        $this->repository->delete($pokemon);
    }
}
