<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missoes', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->foreignId('unidade_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->text('descricao')->nullable();
            $table->integer('xp')->default(0);
            $table->integer('coins')->default(0);
            $table->string('status', 20)->default('ativa');
            $table->date('data_encerramento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missoes');
    }
};
