<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. AGREGAR HASH A LA TABLA SUNAT (donde pertenece, junto con la respuesta)
        Schema::table('sunat', function (Blueprint $table) {
            $table->string('hash')->nullable()->after('mensaje_sunat');
        });

        // 2. LIMPIAR CAMPOS INNECESARIOS DE DOCUMENTOS
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn([
                'puntos_ganados',
                'puntos_canjeados',
                'vuelto',
                'observaciones',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('sunat', function (Blueprint $table) {
            $table->dropColumn('hash');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->integer('puntos_ganados')->default(0)->after('monto_recibido');
            $table->integer('puntos_canjeados')->default(0)->after('puntos_ganados');
            $table->decimal('vuelto', 12, 2)->default(0)->after('puntos_canjeados');
            $table->text('observaciones')->nullable()->after('vuelto');
        });
    }
};
