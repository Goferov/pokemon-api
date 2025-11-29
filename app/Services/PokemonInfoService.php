<?php

namespace App\Services;

use App\Dto\PokemonDto;
use App\Repositories\BannedPokemonRepository;
use App\Repositories\CustomPokemonRepository;
use Illuminate\Support\Collection;

class PokemonInfoService
{
    public function __construct(
        private readonly PokeApiCacheService $client,
        private readonly BannedPokemonRepository $bannedRepository,
        private readonly CustomPokemonRepository $customRepository,
    ) {}

    public function getInfoForNames(array $names): Collection
    {
        $names = $this->normalizeNames($names);

        if (empty($names)) {
            return collect();
        }

        $allowedNames = $this->filterBannedNames($names);

        if (empty($allowedNames)) {
            return collect();
        }

        $result = collect();

        $customPokemons = $this->customRepository->findByNames($allowedNames);
        $result = $result->merge(
            $this->mapCustomCollectionToDtos($customPokemons)
        );

        $remainingNames = $this->getRemainingNames($allowedNames, $customPokemons);

        $result = $result->merge(
            $this->fetchFromApi($remainingNames)
        );

        return $result;
    }

    private function normalizeName(string $name): string
    {
        return strtolower(trim($name));
    }

    private function normalizeNames(array $names): array
    {
        $names = array_map(
            fn (string $n) => $this->normalizeName($n),
            $names
        );

        $names = array_filter($names);

        return array_values(array_unique($names));
    }

    private function filterBannedNames(array $names): array
    {
        $banned = $this->bannedRepository->getBannedNames();

        $banned = array_map(
            fn (string $n) => $this->normalizeName($n),
            $banned
        );

        return array_values(array_filter(
            $names,
            fn (string $name) => !in_array($name, $banned, true)
        ));
    }

    private function getRemainingNames(array $allowedNames, Collection $customPokemons): array
    {
        $customNames = $customPokemons
            ->pluck('name')
            ->map(fn (string $n) => $this->normalizeName($n))
            ->toArray();

        return array_values(array_diff($allowedNames, $customNames));
    }

    private function fetchFromApi(array $names): Collection
    {
        $result = collect();

        foreach ($names as $name) {
            $data = $this->client->getPokemon($name);

            if (!$data) {
                continue;
            }

            $result->push($this->mapPokeApiToDto($data));
        }

        return $result;
    }

    private function mapCustomCollectionToDtos(Collection $customPokemons): Collection
    {
        return $customPokemons->map(
            fn ($custom) => $this->mapCustomToDto($custom)
        );
    }

    private function mapPokeApiToDto(array $data): PokemonDto
    {
        return new PokemonDto(
            name: $data['name'],
            height: $data['height'] ?? null,
            weight: $data['weight'] ?? null,
            types: collect($data['types'] ?? [])
                ->pluck('type.name')
                ->toArray(),
            is_custom: false,
        );
    }

    private function mapCustomToDto($custom): PokemonDto
    {
        return new PokemonDto(
            name: $custom->name,
            height: $custom->height,
            weight: $custom->weight,
            types: $custom->types ?? [],
            is_custom: true,
        );
    }
}
