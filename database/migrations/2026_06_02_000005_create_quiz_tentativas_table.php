<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_tentativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->unsignedSmallInteger('acertos')->default(0);
            $table->unsignedSmallInteger('total_perguntas')->default(0);
            $table->unsignedTinyInteger('nota')->default(0);
            $table->integer('xp_ganho')->default(0);
            $table->integer('coins_ganho')->default(0);
            $table->boolean('aprovado')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'aluno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_tentativas');
    }
};
