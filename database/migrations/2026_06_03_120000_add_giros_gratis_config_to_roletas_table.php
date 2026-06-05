<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roletas', function (Blueprint $table) {
            $table->unsignedInteger('giros_gratis_por_semana')->default(1)->after('custo_coins');
            $table->boolean('somente_gratis')->default(false)->after('giros_gratis_por_semana');
        });
    }

    public function down(): void
    {
        Schema::table('roletas', function (Blueprint $table) {
            $table->dropColumn(['giros_gratis_por_semana', 'somente_gratis']);
        });
    }
};
