<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loja_itens', function (Blueprint $table) {
            $table->foreignId('unidade_id')->after('id')->constrained('unidades')->cascadeOnDelete();
            $table->string('titulo');
            $table->unsignedInteger('quantidade')->default(0);
            $table->unsignedInteger('coins')->default(0);
            $table->string('status', 20)->default('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('loja_itens', function (Blueprint $table) {
            $table->dropForeign(['unidade_id']);
            $table->dropColumn(['unidade_id', 'titulo', 'quantidade', 'coins', 'status']);
        });
    }
};
