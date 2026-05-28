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
        Schema::create('producto_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('lote_presentacion_id')->constrained('lote_presentacion')->onDelete('cascade');
            $table->integer('stock_minimo')->default(0);
            $table->decimal('precio', 10, 2);
            $table->integer('minimo_mayorista')->default(2);
            $table->decimal('precio_mayorista', 10, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_sucursal');
    }
};
