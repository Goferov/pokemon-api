<?php

namespace App\Services;

use App\Clients\PokeApiClient;
use App\Dto\PokemonDto;
use App\Repositories\BannedPokemonRepository;
use Illuminate\Support\Collection;

class PokemonInfoService
{
    public function __construct(
        private readonly PokeApiClient $client,
        private readonly BannedPokemonRepository $bannedRepository,
    ) {}

    public function getInfoForNames(array $names): Collection
    {
        $banned = $this->bannedRepository->getBannedNames();
        $bannedSet = array_map('strtolower', $banned);

        $allowedNames = array_filter($names, function (string $name) use ($bannedSet) {
            return !in_array(strtolower($name), $bannedSet, true);
        });

        $result = collect();

        foreach ($allowedNames as $name) {
            $data = $this->client->getPokemon($name);

            if (!$data) {
                continue;
            }

            $result->push($this->mapPokeApiToDto($data));
        }

        return $result;
    }

    private function mapPokeApiToDto(array $data): PokemonDto
    {
        return new PokemonDto(
            name: $data['name'],
            height: $data['height'] ?? null,
            weight: $data['weight'] ?? null,
            types: collect($data['types'] ?? [])->pluck('type.name')->toArray(),
            is_custom: false,
        );
    }
}
