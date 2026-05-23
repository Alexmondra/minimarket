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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('producto_nombre');
            $table->foreignId('producto_presentacion_id')->constrained('producto_presentacion')->cascadeOnDelete();
            $table->string('tipo');
            $table->integer('cantidad');
            $table->text('motivo')->nullable();
            $table->string('referencia')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('stock_final')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
