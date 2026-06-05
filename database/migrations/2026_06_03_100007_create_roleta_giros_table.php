<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roleta_giros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roleta_id')->constrained('roletas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('segmento_id')->nullable()->constrained('roleta_segmentos')->nullOnDelete();
            $table->string('tipo', 20);
            $table->unsignedInteger('custo_coins')->default(0);
            $table->unsignedInteger('coins_ganho')->default(0);
            $table->unsignedInteger('xp_ganho')->default(0);
            $table->json('premios_json')->nullable();
            $table->timestamps();

            $table->index(['roleta_id', 'aluno_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roleta_giros');
    }
};
