<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_opcoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained('quiz_perguntas')->cascadeOnDelete();
            $table->string('texto');
            $table->boolean('correta')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_opcoes');
    }
};
