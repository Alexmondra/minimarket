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
        Schema::create('ubigeos', function (Blueprint $table) {
            $table->id();
            $table->string('ubigeo')->unique();
            $table->string('departamento');
            $table->string('provincia');
            $table->string('distrito');
            $table->string('capital')->nullable();
            $table->string('region_natural')->nullable();
            $table->string('Superficie')->nullable();
            $table->string('Y')->nullable();
            $table->string('x')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubigeos');
    }
};
