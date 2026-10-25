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
        Schema::create('dispensacion_autorizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensacion_id')->constrained('dispensaciones')->restrictOnDelete();
            $table->foreignId('regente_id')->constrained('users')->restrictOnDelete();
            $table->unique('dispensacion_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensacion_autorizaciones');
    }
};
