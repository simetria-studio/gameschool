<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_tentativa_id')->constrained('quiz_tentativas')->cascadeOnDelete();
            $table->foreignId('pergunta_id')->constrained('quiz_perguntas')->cascadeOnDelete();
            $table->foreignId('opcao_id')->constrained('quiz_opcoes')->cascadeOnDelete();
            $table->boolean('correta')->default(false);
            $table->timestamps();

            $table->unique(['quiz_tentativa_id', 'pergunta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_respostas');
    }
};
