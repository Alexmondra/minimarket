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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('codigo_lote')->nullable();
            $table->string('producto_nombre');
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('ubicacion')->nullable();
            $table->decimal('precio_compra', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado_lote')->default('bueno');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
