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
        Schema::table('lote_presentacion', function (Blueprint $table) {
            $table->index(['producto_presentacion_id', 'stock'], 'lote_pres_prod_pres_stock_idx');
        });

        Schema::table('producto_sucursal', function (Blueprint $table) {
            $table->index(['sucursal_id', 'activo'], 'prod_suc_suc_id_activo_idx');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->index(['sucursal_id', 'fecha_emision', 'tipo_comprobante'], 'doc_suc_fecha_tipo_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lote_presentacion', function (Blueprint $table) {
            $table->dropIndex('lote_pres_prod_pres_stock_idx');
        });

        Schema::table('producto_sucursal', function (Blueprint $table) {
            $table->dropIndex('prod_suc_suc_id_activo_idx');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->dropIndex('doc_suc_fecha_tipo_idx');
        });
    }
};
