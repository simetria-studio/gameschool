<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_turma', function (Blueprint $table) {
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->primary(['quiz_id', 'turma_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_turma');
    }
};
