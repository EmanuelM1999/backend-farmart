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
        Schema::create('inventario_minimos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bodega_id')->constrained('bodegas')->restrictOnDelete();
            $table->foreignId('medicamento_id')->constrained('medicamentos')->restrictOnDelete();
            $table->unsignedInteger('cantidad_minima');
            $table->timestamps();
            $table->unique(['bodega_id', 'medicamento_id'], 'inventario_minimos_bodega_medicamento_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_minimos');
    }
};
