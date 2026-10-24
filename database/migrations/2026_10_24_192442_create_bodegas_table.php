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
        Schema::create('bodegas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código único de la bodega');
            $table->string('nombre')->comment('Nombre de la bodega');
            $table->string('ubicacion')->comment('Ubicación de la bodega');
            $table->tinyInteger('estado')->default(1)->comment('Estado de la bodega: 1 para activa, 0 para inactiva');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bodegas');
    }
};
