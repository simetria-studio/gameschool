<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('roleta_item_id')->constrained('roleta_itens')->cascadeOnDelete();
            $table->unsignedInteger('quantidade')->default(1);
            $table->timestamps();

            $table->unique(['aluno_id', 'roleta_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_itens');
    }
};
