<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missao_turma', function (Blueprint $table) {
            $table->foreignId('missao_id')->constrained('missoes')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->primary(['missao_id', 'turma_id']);
        });

        $rows = DB::table('missoes')->select('id', 'turma_id')->get();
        foreach ($rows as $row) {
            if ($row->turma_id) {
                DB::table('missao_turma')->insert([
                    'missao_id' => $row->id,
                    'turma_id' => $row->turma_id,
                ]);
            }
        }

        Schema::table('missoes', function (Blueprint $table) {
            $table->dropForeign(['turma_id']);
            $table->dropColumn('turma_id');
        });
    }

    public function down(): void
    {
        Schema::table('missoes', function (Blueprint $table) {
            $table->foreignId('turma_id')->nullable()->after('unidade_id')->constrained('turmas')->cascadeOnDelete();
        });

        $pairs = DB::table('missao_turma')->select('missao_id', 'turma_id')->get();
        foreach ($pairs as $p) {
            DB::table('missoes')->where('id', $p->missao_id)->update(['turma_id' => $p->turma_id]);
        }

        Schema::dropIfExists('missao_turma');
    }
};
