<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomPokemonRequest;
use App\Http\Requests\UpdateCustomPokemonRequest;
use App\Http\Resources\CustomPokemonResource;
use App\Models\CustomPokemon;
use App\Services\CustomPokemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RuntimeException;

class CustomPokemonController extends Controller
{
    public function __construct(
        private readonly CustomPokemonService $service
    ) {}

    public function index(Request $request): JsonResource
    {
        $perPage = (int) $request->get('per_page', 15);
        $pokemons = $this->service->list($perPage);

        return CustomPokemonResource::collection($pokemons);
    }

    public function store(StoreCustomPokemonRequest $request): JsonResponse
    {
        $pokemon = $this->service->create($request->validated());

        return response()->json(new CustomPokemonResource($pokemon), 201);
    }

    public function show(CustomPokemon $customPokemon): JsonResponse
    {
        return response()->json(new CustomPokemonResource($customPokemon));
    }

    public function update(UpdateCustomPokemonRequest $request, CustomPokemon $customPokemon): JsonResponse
    {
        $pokemon = $this->service->update($customPokemon, $request->validated());

        return response()->json(new CustomPokemonResource($pokemon));
    }

    public function destroy(CustomPokemon $customPokemon): JsonResponse
    {
        $this->service->delete($customPokemon);

        return response()->json(null, 204);
    }
}

