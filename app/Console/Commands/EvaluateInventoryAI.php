<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\InventoryAI\InventoryAssistantService;
use Illuminate\Console\Command;

class EvaluateInventoryAI extends Command
{
    protected $signature = 'inventory-ai:evaluate';

    protected $description = 'Evalúa las preguntas del asistente de inventario';

    public function handle(InventoryAssistantService $assistant): int
    {
        $path = base_path('tests/Fixtures/inventory_ai_eval.json');

        if (! is_file($path)) {
            $this->error("No existe el archivo de evaluación: {$path}");

            return self::FAILURE;
        }

        $cases = json_decode(file_get_contents($path), true);

        if (! is_array($cases)) {
            $this->error('El JSON de evaluación no es válido.');

            return self::FAILURE;
        }

        $user = User::query()
            ->get()
            ->first(fn (User $candidate) => $candidate->can('consultar-inventario')
            );

        if (! $user) {
            $this->error(
                'No hay un usuario con permiso consultar-inventario. '.
                'Ejecuta los seeders correspondientes primero.'
            );

            return self::FAILURE;
        }

        $hits = 0;
        $rows = [];

        foreach ($cases as $case) {
            $id = $case['id'] ?? 'SIN_ID';
            $question = $case['pregunta'] ?? '';
            $expectedTool = $case['herramienta_esperada'] ?? '';
            $expectedType = $case['tipo_esperado'] ?? 'answer';
            $expectedArguments = $case['argumentos_esperados'] ?? [];

            try {
                $actual = $assistant->ask($user, $question);

                $actualType = $actual['type'] ?? '';
                $actualTool = $actual['tool'] ?? null;
                $actualArguments = $actual['arguments'] ?? [];

                $toolMatches = $expectedTool === 'fuera_de_alcance'
                    ? $actualType === 'out_of_scope'
                    : $actualTool === $expectedTool;

                $typeMatches = $actualType === $expectedType;

                // Compara solo los parámetros que el caso declara.
                $argumentsMatch = true;

                foreach ($expectedArguments as $key => $value) {
                    if (
                        ! array_key_exists($key, $actualArguments)
                        || $actualArguments[$key] !== $value
                    ) {
                        $argumentsMatch = false;
                        break;
                    }
                }

                $passed = $toolMatches
                    && $typeMatches
                    && $argumentsMatch;

                if ($passed) {
                    $hits++;
                }

                $rows[] = [
                    $id,
                    $passed ? 'ACIERTO' : 'FALLO',
                    $expectedTool,
                    $actualTool ?? $actualType,
                    $argumentsMatch ? 'Sí' : 'No',
                ];

            } catch (\Throwable $exception) {
                report($exception);

                // Mostrar el error real durante la evaluación.
                $this->newLine();
                $this->error("Error en el caso {$id}");
                $this->line('Tipo: '.get_class($exception));
                $this->line('Mensaje: '.$exception->getMessage());
                $this->line(
                    'Ubicación: '.$exception->getFile()
                    .':'.$exception->getLine()
                );

                $rows[] = [
                    $id,
                    'ERROR',
                    $expectedTool,
                    'Excepción',
                    'No',
                ];
            }

        }

        $this->table(
            ['Caso', 'Resultado', 'Herramienta esperada', 'Resultado real', 'Parámetros'],
            $rows
        );

        $total = count($cases);
        $percentage = $total > 0
            ? round(($hits / $total) * 100, 2)
            : 0;

        $this->newLine();
        $this->info("Aciertos: {$hits}/{$total} ({$percentage}%)");

        return $hits === $total
            ? self::SUCCESS
            : self::FAILURE;
    }
}
