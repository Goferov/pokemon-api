<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomPokemonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'        => $this->name,
            'height'      => $this->height,
            'weight'      => $this->weight,
            'types'       => $this->types ?? [],
            'stats'       => $this->stats ?? [],
            'description' => $this->description,
            'is_custom'   => true,
        ];
    }
}
