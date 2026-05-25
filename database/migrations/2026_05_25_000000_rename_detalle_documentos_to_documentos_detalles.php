<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('detalle_documentos') && ! Schema::hasTable('documentos_detalles')) {
            Schema::rename('detalle_documentos', 'documentos_detalles');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documentos_detalles') && ! Schema::hasTable('detalle_documentos')) {
            Schema::rename('documentos_detalles', 'detalle_documentos');
        }
    }
};
