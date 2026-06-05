<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roleta_bau_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segmento_id')->constrained('roleta_segmentos')->cascadeOnDelete();
            $table->foreignId('roleta_item_id')->constrained('roleta_itens')->cascadeOnDelete();
            $table->unsignedInteger('peso')->default(1);
            $table->timestamps();

            $table->unique(['segmento_id', 'roleta_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roleta_bau_itens');
    }
};
