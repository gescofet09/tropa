<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repartidor_zona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repartidor_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repartidor_zona');
    }
};
