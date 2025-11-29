<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_pokemons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('types')->nullable();
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->json('stats')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_pokemon');
    }
};
