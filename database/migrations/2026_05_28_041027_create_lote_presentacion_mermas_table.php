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
        Schema::create('lote_presentacion_mermas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_presentacion_id')->constrained('lote_presentacion')->cascadeOnDelete();
            $table->integer('cantidad');
            $table->string('tipo_merma');
            $table->text('motivo')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_presentacion_mermas');
    }
};
