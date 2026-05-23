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
        Schema::create('empresa_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_certificado')->nullable();
            $table->string('certificado')->nullable();
            $table->string('certificado_pass')->nullable();
            $table->string('user_sol')->nullable();
            $table->string('pass_sol')->nullable();
            $table->string('sunat_client_id')->nullable();
            $table->string('sunat_client_secret')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_config');
    }
};
