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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId("usuario_id")->constrained("usuarios")->ondelete("cascade");
            $table->foreignId("repartidor_id")->nullable()->constrained("usuarios")->nullOnDeleted();
            $table->enum("estado", ["recibido", "preparacion", "reparto", "entregado"])->default("recibido");
            $table->decimal("total", 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
