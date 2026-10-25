<?php

namespace App\Services\InventoryAI;

use App\Contracts\InventoryAiProvider;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Throwable;

class InventoryAssistantService
{
    private const ALLOWED_TOOLS = [
        'buscar_lotes',
        'consultar_stock',
        'consultar_vencimientos',
        'consultar_stock_minimo',
    ];

    public function __construct(
        private InventoryAiProvider $provider,
        private InventoryTools $inventoryTools,
    ) {}

    public function ask(User $user, string $question): array
    {
        if (! $user->can('consultar-inventario')) {
            return [
                'type' => 'forbidden',
                'message' => 'No tienes permisos para consultar el inventario.',
            ];
        }

        $interpretation = $this->provider->interpret($question);

        if (($interpretation['type'] ?? null) === 'out_of_scope') {
            return $interpretation;
        }

        if (($interpretation['type'] ?? null) !== 'tool_call') {
            return [
                'type' => 'error',
                'message' => 'No se pudo interpretar la consulta.',
            ];
        }

        $tool = $interpretation['tool'] ?? '';
        $arguments = $interpretation['arguments'] ?? [];

        if (
            ! in_array($tool, self::ALLOWED_TOOLS, true)
            || ! is_array($arguments)
        ) {
            return [
                'type' => 'out_of_scope',
                'message' => 'La operación solicitada no está permitida.',
            ];
        }

        try {
            $result = $this->inventoryTools->execute(
                $user,
                $tool,
                $arguments
            );

            return [
                'type' => 'answer',
                'tool' => $tool,
                'arguments' => $arguments,
                'data' => $result,
            ];
        } catch (ValidationException $exception) {
            return [
                'type' => 'validation_rejected',
                'tool' => $tool,
                'arguments' => $arguments,
                'message' => 'Los parámetros están fuera de los límites permitidos.',
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'type' => 'error',
                'message' => 'No fue posible completar la consulta.',
            ];
        }
    }
}
