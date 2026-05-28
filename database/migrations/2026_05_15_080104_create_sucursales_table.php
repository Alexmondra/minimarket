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
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('codigo')->unique();
            $table->foreignId('ubigeo')->constrained('ubigeos');
            $table->string('direccion');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('nombre_sucursal');
            $table->string('imagen_sucursal')->nullable();
            $table->decimal('impuesto_porcentaje', 5, 2)->default(0);
            $table->json('configuracion_extra')->nullable();
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
        Schema::dropIfExists('sucursales');
    }
};
