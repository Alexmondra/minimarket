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
        Schema::create('lote_presentacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->foreignId('producto_presentacion_id')->constrained('producto_presentacion')->onDelete('cascade');
            $table->integer('stock')->default(0);
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['lote_id', 'producto_presentacion_id']); // evita duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_presentacion');
    }
};
