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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('caja_sesion_id')->nullable();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_comprobante');
            $table->string('serie')->nullable();
            $table->string('numero')->nullable();
            $table->date('fecha_emision');
            $table->decimal('total_bruto', 12,2)->default(0);
            $table->decimal('total_descuento', 12,2)->default(0);
            $table->decimal('total_neto', 12,2)->default(0);
            $table->decimal('op_gravada', 12,2)->nullable();
            $table->decimal('op_exonerada', 12,2)->nullable();
            $table->decimal('op_inafecta', 12,2)->nullable();
            $table->decimal('total_igv', 12,2)->nullable();
            $table->decimal('porcentaje_igv', 5,2)->nullable();
            $table->string('tipo_moneda')->nullable();
            $table->string('medio_pago')->nullable();
            $table->decimal('monto_recibido', 12,2)->nullable();
            $table->string('referencia_pago')->nullable();
            $table->boolean('estado')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
