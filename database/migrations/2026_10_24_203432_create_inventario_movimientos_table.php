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
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('existencia_id')->constrained('existencias')->restrictOnDelete();
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'salida_por_traslado', 'entrada_por_traslado', 'ajuste'])->comment('Tipo de movimiento: entrada o salida');
            $table->integer('cantidad')->comment('Cantidad de medicamento movida')->check('cantidad > 0');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('motivo')->comment('Motivo del movimiento de inventario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_movimientos');
    }
};
