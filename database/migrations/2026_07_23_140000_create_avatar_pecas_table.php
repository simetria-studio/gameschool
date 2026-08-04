<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avatar_pecas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->nullable()->constrained('unidades')->nullOnDelete();
            $table->string('titulo');
            $table->string('slot', 32); // base|cabelo|roupa|calcado|acessorio
            $table->string('genero', 16); // masculino|feminino|unissex
            $table->string('asset_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('tipo_asset', 16)->default('png'); // png|spine
            $table->string('raridade', 16)->default('comum');
            $table->string('status', 16)->default('ativo');
            $table->boolean('is_starter')->default(false);
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->index(['slot', 'genero', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avatar_pecas');
    }
};
