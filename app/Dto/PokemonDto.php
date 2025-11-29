<?php

namespace App\Dto;

class PokemonDto
{
    public function __construct(
        public string $name,
        public ?int $height,
        public ?int $weight,
        public array $types,
        public bool $is_custom = false,
    ) {}
}

