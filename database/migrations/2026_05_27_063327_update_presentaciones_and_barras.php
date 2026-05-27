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
        // 1. Add presentacion_base_id to producto_presentacion
        Schema::table('producto_presentacion', function (Blueprint $table) {
            $table->foreignId('presentacion_base_id')
                ->after('producto_id')
                ->nullable()
                ->constrained('producto_presentacion')
                ->nullOnDelete();
        });

        // 2. Create the producto_presentacion_barras table
        Schema::create('producto_presentacion_barras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_presentacion_id')
                ->constrained('producto_presentacion')
                ->cascadeOnDelete();
            $table->string('codigo_barra')->unique();
            $table->timestamps();
        });

        // 3. Drop codigo_barra column from producto_presentacion
        Schema::table('producto_presentacion', function (Blueprint $table) {
            $table->dropColumn('codigo_barra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add codigo_barra to producto_presentacion
        Schema::table('producto_presentacion', function (Blueprint $table) {
            $table->string('codigo_barra')->nullable()->after('imagen');
        });

        // 2. Drop producto_presentacion_barras table
        Schema::dropIfExists('producto_presentacion_barras');

        // 3. Drop presentacion_base_id from producto_presentacion
        Schema::table('producto_presentacion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presentacion_base_id');
        });
    }
};
