<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->index('activo', 'prod_activo_idx');
            $table->index('nombre', 'prod_nombre_idx');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->index('fecha_vencimiento', 'lotes_fecha_vencimiento_idx');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->index('estado', 'doc_estado_idx');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->index('documento', 'cli_documento_idx');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('prod_activo_idx');
            $table->dropIndex('prod_nombre_idx');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropIndex('lotes_fecha_vencimiento_idx');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex('doc_estado_idx');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('cli_documento_idx');
        });
    }
};
