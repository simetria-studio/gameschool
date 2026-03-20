<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('access_role', 20)->default('professor')->after('username');
            $table->foreignId('unidade_id')->nullable()->after('access_role')->constrained('unidades')->nullOnDelete();
        });

        // Garante que o usuário admin existente seja master.
        DB::table('users')
            ->where('username', 'admin')
            ->update([
                'access_role' => 'master',
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unidade_id']);
            $table->dropColumn(['access_role', 'unidade_id']);
        });
    }
};

