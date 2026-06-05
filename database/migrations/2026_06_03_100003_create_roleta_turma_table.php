<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roleta_turma', function (Blueprint $table) {
            $table->foreignId('roleta_id')->constrained('roletas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->primary(['roleta_id', 'turma_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roleta_turma');
    }
};
