<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_presentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remetente_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('destinatario_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('roleta_item_id')->constrained('roleta_itens')->cascadeOnDelete();
            $table->unsignedInteger('quantidade')->default(1);
            $table->string('mensagem', 500)->nullable();
            $table->boolean('lido')->default(false);
            $table->timestamps();

            $table->index(['destinatario_id', 'lido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_presentes');
    }
};
