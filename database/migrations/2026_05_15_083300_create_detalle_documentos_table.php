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
        Schema::create('detalle_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->string('producto_nombre');
            $table->foreignId('producto_presentacion_id')->constrained('producto_presentacion')->cascadeOnDelete();
            $table->decimal('precio_unitario', 12,2);
            $table->decimal('valor_unitario', 12,2);
            $table->decimal('igv', 12,2)->nullable();
            $table->string('tipo_afectacion')->nullable();
            $table->decimal('descuento_unitario', 12,2)->nullable();
            $table->decimal('subtotal_bruto', 12,2)->nullable();
            $table->decimal('subtotal_descuento', 12,2)->nullable();
            $table->decimal('subtotal_neto', 12,2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_documentos');
    }
};
