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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('ingreso')->unique()->comment('Código único de ingreso del paciente');
            $table->string('nombre')->comment('Nombre del paciente');
            $table->string('apellido')->comment('Apellido del paciente');
            $table->string('num_identidad')->unique()->comment('Documento de Identidad del paciente');
            $table->date('fecha_nacimiento')->comment('Fecha de nacimiento del paciente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
