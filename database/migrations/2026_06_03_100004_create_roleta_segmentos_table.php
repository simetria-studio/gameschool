<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roleta_segmentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roleta_id')->constrained('roletas')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('tipo', 20);
            $table->foreignId('roleta_item_id')->nullable()->constrained('roleta_itens')->nullOnDelete();
            $table->unsignedInteger('coins')->default(0);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('peso')->default(1);
            $table->string('cor', 7)->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roleta_segmentos');
    }
};
