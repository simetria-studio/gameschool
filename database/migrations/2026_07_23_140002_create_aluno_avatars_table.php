<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_avatars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->unique()->constrained('alunos')->cascadeOnDelete();
            $table->string('genero', 16); // masculino|feminino
            $table->json('configuracao_json')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_avatars');
    }
};
