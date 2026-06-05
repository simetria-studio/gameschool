<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roleta_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('tipo', 20);
            $table->string('emoji', 16)->nullable();
            $table->string('imagem')->nullable();
            $table->string('raridade', 20)->default('comum');
            $table->string('status', 20)->default('ativo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roleta_itens');
    }
};
