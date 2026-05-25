<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table): void {
            if (Schema::hasColumn('documentos', 'caja_sesion_id')) {
                $table->dropColumn('caja_sesion_id');
            }
        });

        Schema::table('documentos', function (Blueprint $table): void {
            $table->foreignId('caja_sesion_id')
                ->nullable()
                ->after('id')
                ->constrained('sesiones_caja')
                ->nullOnDelete();

            $table->decimal('subtotal', 12, 2)->default(0)->after('total_descuento');
            $table->decimal('vuelto', 12, 2)->default(0)->after('monto_recibido');
            $table->integer('puntos_ganados')->default(0)->after('vuelto');
            $table->integer('puntos_canjeados')->default(0)->after('puntos_ganados');
            $table->decimal('descuento_puntos', 12, 2)->default(0)->after('puntos_canjeados');
        });

        DB::statement('ALTER TABLE documentos MODIFY cliente_id BIGINT UNSIGNED NULL');

        Schema::table('documentos', function (Blueprint $table): void {
            $table->unique(['sucursal_id', 'tipo_comprobante', 'serie', 'numero'], 'documentos_serie_numero_unique');
        });

        Schema::table('detalle_documentos', function (Blueprint $table): void {
            $table->foreignId('producto_id')
                ->nullable()
                ->after('lote_id')
                ->constrained('productos')
                ->nullOnDelete();

            $table->foreignId('producto_sucursal_id')
                ->nullable()
                ->after('producto_presentacion_id')
                ->constrained('producto_sucursal')
                ->nullOnDelete();

            $table->decimal('cantidad', 12, 3)->default(1)->after('producto_presentacion_id');
            $table->decimal('total_igv', 12, 2)->default(0)->after('igv');
            $table->decimal('total_linea', 12, 2)->default(0)->after('subtotal_neto');
        });

        Schema::table('archivos', function (Blueprint $table): void {
            if (Schema::hasColumn('archivos', 'documento_id')) {
                $table->dropColumn('documento_id');
            }
        });

        Schema::table('archivos', function (Blueprint $table): void {
            $table->foreignId('documento_id')
                ->nullable()
                ->after('id')
                ->constrained('documentos')
                ->cascadeOnDelete();
        });

        Schema::table('sunat', function (Blueprint $table): void {
            if (Schema::hasColumn('sunat', 'documento_id')) {
                $table->dropColumn('documento_id');
            }
        });

        Schema::table('sunat', function (Blueprint $table): void {
            $table->foreignId('documento_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('documentos')
                ->cascadeOnDelete();
        });

        Schema::create('cliente_punto_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('documento_id')->nullable()->constrained('documentos')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo');
            $table->integer('puntos');
            $table->decimal('monto_descuento', 12, 2)->default(0);
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_punto_movimientos');

        Schema::table('sunat', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('documento_id');
            $table->string('documento_id')->nullable()->after('empresa_id');
        });

        Schema::table('archivos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('documento_id');
            $table->string('documento_id')->nullable()->after('id');
        });

        Schema::table('detalle_documentos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('producto_id');
            $table->dropConstrainedForeignId('producto_sucursal_id');
            $table->dropColumn([
                'cantidad',
                'total_igv',
                'total_linea',
            ]);
        });

        Schema::table('documentos', function (Blueprint $table): void {
            $table->dropUnique('documentos_serie_numero_unique');
            $table->dropConstrainedForeignId('caja_sesion_id');
            $table->dropColumn([
                'subtotal',
                'vuelto',
                'puntos_ganados',
                'puntos_canjeados',
                'descuento_puntos',
            ]);
        });

        DB::statement('ALTER TABLE documentos MODIFY cliente_id BIGINT UNSIGNED NOT NULL');

        Schema::table('documentos', function (Blueprint $table): void {
            $table->string('caja_sesion_id')->nullable()->after('id');
        });
    }
};
