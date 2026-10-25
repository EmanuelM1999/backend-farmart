<?php

namespace App\Contracts;

interface InventoryAiProvider
{
    /**
     * Interpreta la pregunta y devuelve una intención estructurada.
     *
     * No debe ejecutar consultas ni modificar datos.
     */
    public function interpret(string $question): array;
}
