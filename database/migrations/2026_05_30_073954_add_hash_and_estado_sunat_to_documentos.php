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
        Schema::table('documentos', function (Blueprint $table) {
            $table->string('hash')->nullable()->after('observaciones');
            $table->string('estado_sunat')->default('PENDIENTE')->after('hash')->comment('PENDIENTE, ACEPTADA, RECHAZADA, OBSERVADA, NO_APLICA');
            $table->string('codigo_error_sunat')->nullable()->after('estado_sunat');
            $table->text('mensaje_sunat')->nullable()->after('codigo_error_sunat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn(['hash', 'estado_sunat', 'codigo_error_sunat', 'mensaje_sunat']);
        });
    }
};
