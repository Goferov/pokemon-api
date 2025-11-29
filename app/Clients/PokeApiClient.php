<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;

class PokeApiClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.pokeapi.base_url'), '/');
    }

    public function getPokemon(string $name): ?array
    {
        $name = strtolower(trim($name));

        $response = Http::get("{$this->baseUrl}/pokemon/{$name}");

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }
}
