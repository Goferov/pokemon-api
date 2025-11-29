<?php

namespace App\Services;

use App\Repositories\BannedPokemonRepository;
use App\Models\BannedPokemon;
use Illuminate\Support\Collection;

class BannedPokemonService
{
    public function __construct(
        private readonly BannedPokemonRepository $repository
    )
    {
    }

    public function list(): Collection
    {
        return $this->repository->all();
    }

    public function add(string $name): BannedPokemon
    {
        return $this->repository->create($name);
    }

    public function remove(BannedPokemon $bannedPokemon): void
    {
        $this->repository->delete($bannedPokemon);
    }

    public function bannedNames(): array
    {
        return $this->repository->getBannedNames();
    }
}
