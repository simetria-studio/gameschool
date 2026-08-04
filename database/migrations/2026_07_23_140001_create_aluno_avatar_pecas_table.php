<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_avatar_pecas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('avatar_peca_id')->constrained('avatar_pecas')->cascadeOnDelete();
            $table->timestamp('desbloqueado_em')->useCurrent();
            $table->timestamps();

            $table->unique(['aluno_id', 'avatar_peca_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_avatar_pecas');
    }
};
