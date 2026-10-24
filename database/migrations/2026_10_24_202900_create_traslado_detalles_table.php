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
        Schema::create('traslado_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_id')->constrained('traslados')->restrictOnDelete();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->restrictOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->restrictOnDelete();
            $table->integer('cantidad_solicitada')->comment('Cantidad de medicamento solicitada')->check('cantidad_solicitada > 0');
            $table->integer('cantidad_despachada')->comment('Cantidad de medicamento despachada')->check('cantidad_despachada >= 0');
            $table->integer('cantidad_recibida')->comment('Cantidad de medicamento recibida')->check('cantidad_recibida >= 0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traslado_detalles');
    }
};
