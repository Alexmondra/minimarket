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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('sucursal_id')
                ->nullable()
                ->constrained('sucursales')
                ->nullOnDelete();
            $table->string('nombre'); // Nombre comercial o nombre visible
            $table->enum('tipo_documento', ['RUC', 'DNI', 'CE', 'OTRO'])->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('contacto_principal')->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->string('rubro')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['empresa_id', 'numero_documento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
