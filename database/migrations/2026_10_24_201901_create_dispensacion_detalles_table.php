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
        Schema::create('dispensacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensacion_id')->constrained('dispensaciones')->restrictOnDelete();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->restrictOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->restrictOnDelete();
            $table->foreignId('prescripcion_detalle_id')->constrained('prescripcion_detalles')->restrictOnDelete();
            $table->integer('cantidad_dispensada')->comment('Cantidad de medicamento dispensada')->check('cantidad_dispensada > 0');
            $table->integer('cantidad_existencia_despues')->comment('Cantidad de medicamento en existencia')->check('cantidad_existencia_despues >= 0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensacion_detalles');
    }
};
