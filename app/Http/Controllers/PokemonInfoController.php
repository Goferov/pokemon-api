<?php

namespace App\Http\Controllers;

use App\Http\Requests\PokemonInfoRequest;
use App\Http\Resources\PokemonResource;
use App\Services\PokemonInfoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class PokemonInfoController extends Controller
{
    public function __construct(
        private readonly PokemonInfoService $service
    ) {}

    public function __invoke(PokemonInfoRequest $request): JsonResource
    {
        $names = $request->input('names', []);

        $pokemons = $this->service->getInfoForNames($names);
        return PokemonResource::collection($pokemons);
    }
}
