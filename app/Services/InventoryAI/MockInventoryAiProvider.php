<?php

namespace App\Services\InventoryAI;

use App\Contracts\InventoryAiProvider;
use Illuminate\Support\Str;

class MockInventoryAiProvider implements InventoryAiProvider
{
    public function interpret(string $question): array
    {
        $text = Str::lower(Str::ascii($question));

        // Rechazar solicitudes de escritura o cambios de inventario.
        if (preg_match(
            '/\b(aumentar|incrementar|disminuir|modificar|actualizar|eliminar|borrar|crear|transferir|dispensar|ajustar)\b/',
            $text
        )) {
            return [
                'type' => 'out_of_scope',
                'message' => 'Solo puedo realizar consultas de inventario.',
            ];
        }

        // No permitir acceso a datos de pacientes ni SQL arbitrario.
        if (
            preg_match('/\b(paciente|pacientes|historia clinica)\b/', $text)
            || preg_match('/\b(sql|select|drop table|insert into|delete from)\b/', $text)
            || str_contains($text, 'ignora todas las reglas')
        ) {
            return [
                'type' => 'out_of_scope',
                'message' => 'No puedo realizar esa consulta. Solo puedo ayudar con consultas autorizadas de inventario.',
            ];
        }

        // Preguntas que no corresponden a inventario.
        if (
            preg_match('/\b(clima|temperatura|futbol|politica|capital de|receta de cocina)\b/', $text)
        ) {
            return [
                'type' => 'out_of_scope',
                'message' => 'Solo puedo ayudar con consultas de inventario.',
            ];
        }

        $arguments = [];

        if (preg_match('/\b(\d+)\s+dias?\b/', $text, $matches)) {
            $arguments['dias'] = (int) $matches[1];
        }

        if (preg_match('/\bbodega(?:\s+con)?\s+(?:id\s*)?(\d+)\b/', $text, $matches)) {
            $arguments['bodega_id'] = (int) $matches[1];
        }

        if (preg_match('/\b(acetaminofen|clonazepam)\b/', $text, $matches)) {
            $arguments['medicamento'] = $matches[1];
        }

        if (
            str_contains($text, 'stock minimo')
            || str_contains($text, 'stock mínimo')
            || str_contains($text, 'por debajo de su stock')
        ) {
            return [
                'type' => 'tool_call',
                'tool' => 'consultar_stock_minimo',
                'arguments' => [],
            ];
        }

        if (
            str_contains($text, 'vencen')
            || str_contains($text, 'vencimiento')
            || str_contains($text, 'proximos')
        ) {
            $arguments['dias'] ??= 60;

            return [
                'type' => 'tool_call',
                'tool' => 'consultar_vencimientos',
                'arguments' => $arguments,
            ];
        }

        if (
            str_contains($text, 'lotes')
            && (
                str_contains($text, 'disponibles')
                || str_contains($text, 'unidades')
            )
        ) {
            return [
                'type' => 'tool_call',
                'tool' => 'buscar_lotes',
                'arguments' => $arguments,
            ];
        }

        if (
            str_contains($text, 'stock')
            || str_contains($text, 'existencias')
            || str_contains($text, 'inventario')
            || str_contains($text, 'cuanto inventario')
        ) {
            return [
                'type' => 'tool_call',
                'tool' => 'consultar_stock',
                'arguments' => $arguments,
            ];
        }

        return [
            'type' => 'out_of_scope',
            'message' => 'No pude identificar una consulta de inventario válida.',
        ];
    }
}
