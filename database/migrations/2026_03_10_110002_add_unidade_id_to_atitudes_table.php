<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atitudes', function (Blueprint $table) {
            $table->foreignId('unidade_id')->nullable()->after('id')->constrained('unidades')->cascadeOnDelete();
        });

        $firstUnidade = DB::table('unidades')->orderBy('id')->value('id');
        if ($firstUnidade) {
            DB::table('atitudes')->whereNull('unidade_id')->update(['unidade_id' => $firstUnidade]);
        }
    }

    public function down(): void
    {
        Schema::table('atitudes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidade_id');
        });
    }
};
