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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')
                ->after('id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('telefono')
                ->nullable()
                ->after('email');

            $table->boolean('activo')
                ->default(true)
                ->after('password');

            $table->timestamp('ultimo_acceso')
                ->nullable()
                ->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn(['empresa_id', 'telefono', 'activo', 'ultimo_acceso']);
        });
    }
};
