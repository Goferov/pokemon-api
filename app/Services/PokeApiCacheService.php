<?php

namespace App\Services;

use App\Clients\PokeApiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PokeApiCacheService
{
    public function __construct(
        private readonly PokeApiClient $client
    ) {}

    public function getPokemon(string $name): ?array
    {
        $name = strtolower(trim($name));

        if ($name === '') {
            return null;
        }

        $cacheKey = "pokeapi.pokemon.{$name}";
        $ttl      = $this->secondsUntilNextNoon();

        if ($ttl <= 0) {
            $ttl = 60;
        }

        return Cache::remember($cacheKey, $ttl, function () use ($name) {
            return $this->client->getPokemon($name);
        });
    }

    private function secondsUntilNextNoon(): int
    {
        $tz  = config('app.timezone', 'Europe/Warsaw');
        $now = Carbon::now($tz);

        $nextNoon = $now->copy()->setTime(12, 0, 0);

        if ($now->greaterThanOrEqualTo($nextNoon)) {
            $nextNoon->addDay();
        }

        return $now->diffInSeconds($nextNoon);
    }
}
