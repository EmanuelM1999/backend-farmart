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
        Schema::create('prescripcion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescripcion_id')->constrained('prescripciones')->restrictOnDelete();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->restrictOnDelete();
            $table->integer('cantidad_prescrita')->comment('Cantidad de medicamento prescrita')->check('cantidad_prescrita > 0');
            $table->string('cantidad_dispensada')->nullable()->comment('Cantidad de medicamento dispensada, puede ser nula si no se ha dispensado aún');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescripcion_detalles');
    }
};
