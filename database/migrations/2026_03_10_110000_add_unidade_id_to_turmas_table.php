<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turmas', function (Blueprint $table) {
            $table->foreignId('unidade_id')->nullable()->after('id')->constrained('unidades')->cascadeOnDelete();
        });

        $firstUnidade = DB::table('unidades')->orderBy('id')->value('id');
        if ($firstUnidade) {
            DB::table('turmas')->whereNull('unidade_id')->update(['unidade_id' => $firstUnidade]);
        }
    }

    public function down(): void
    {
        Schema::table('turmas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidade_id');
        });
    }
};
