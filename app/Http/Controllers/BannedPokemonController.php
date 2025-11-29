<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBannedPokemonRequest;
use App\Http\Resources\BannedPokemonResource;
use App\Models\BannedPokemon;
use App\Services\BannedPokemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class BannedPokemonController extends Controller
{
    public function __construct(
        private readonly BannedPokemonService $service
    )
    {
    }

    public function index(): JsonResource
    {
        $banned = $this->service->list();

        return BannedPokemonResource::collection($banned);
    }

    public function store(StoreBannedPokemonRequest $request): JsonResponse
    {
        $pokemon = $this->service->add($request->input('name'));

        return response()->json(
            new BannedPokemonResource($pokemon),
            201
        );
    }

    public function destroy(BannedPokemon $bannedPokemon): JsonResponse
    {
        $this->service->remove($bannedPokemon);

        return response()->json(null, 204);
    }
}

