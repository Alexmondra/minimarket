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
        Schema::create('documento_referencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->string('tipo_relacion');
            $table->foreignId('documento_referenciado_id')->constrained('documentos')->cascadeOnDelete();
            $table->string('tipo_documento_ref')->nullable();
            $table->string('serie_ref')->nullable();
            $table->string('numero_ref')->nullable();
            $table->string('motivo_codigo')->nullable();
            $table->string('motivo_descripcion')->nullable();
            $table->date('fecha_emision_ref')->nullable();
            $table->string('moneda_ref')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_referencia');
    }
};
