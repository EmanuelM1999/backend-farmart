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
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bodega_origen_id')->constrained('bodegas')->restrictOnDelete();
            $table->foreignId('bodega_destino_id')->constrained('bodegas')->restrictOnDelete();
            $table->foreignId('solicitante_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('aprobador_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->enum('estado', ['BORRADOR', 'SOLICITADO', 'APROBADO', 'EN_TRANSITO', 'RECIBIDO', 'RECIBIDO_PARCIAL', 'ANULADO'])->default('BORRADOR')->comment('Estado del traslado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traslados');
    }
};
