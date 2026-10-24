<?php

namespace Database\Seeders;

use App\Models\Bodega;
use App\Models\Existencia;
use App\Models\InventarioMovimiento;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\Paciente;
use App\Models\Prescripcion;
use App\Models\PrescripcionDetalle;
use App\Models\InventarioMinimo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Bodegas
            $bodegasData = [
                [
                    'codigo' => 'BOD-CEN',
                    'nombre' => 'Farmacia Central',
                ],
                [
                    'codigo' => 'BOD-URG',
                    'nombre' => 'Farmacia Urgencias',
                ],
                [
                    'codigo' => 'BOD-HOSP',
                    'nombre' => 'Bodega Hospitalización',
                ],
            ];

            $bodegas = [];

            foreach ($bodegasData as $data) {
                $bodegas[$data['codigo']] = Bodega::firstOrCreate(
                    ['codigo' => $data['codigo']],
                    [
                        'nombre' => $data['nombre'],
                        'ubicacion' => $data['nombre'],
                        'estado' => true,
                    ]
                );
            }

            // 2. Medicamentos
            $medicamentosData = [
                [
                    'codigo' => 'MED-001',
                    'nombre' => 'Acetaminofén',
                    'unidad_medida' => 'tableta',
                    'control_especial' => false,
                ],
                [
                    'codigo' => 'MED-002',
                    'nombre' => 'Ibuprofeno',
                    'unidad_medida' => 'tableta',
                    'control_especial' => false,
                ],
                [
                    'codigo' => 'MED-003',
                    'nombre' => 'Amoxicilina',
                    'unidad_medida' => 'cápsula',
                    'control_especial' => false,
                ],
                [
                    'codigo' => 'MED-004',
                    'nombre' => 'Omeprazol',
                    'unidad_medida' => 'cápsula',
                    'control_especial' => false,
                ],
                [
                    'codigo' => 'MED-005',
                    'nombre' => 'Loratadina',
                    'unidad_medida' => 'tableta',
                    'control_especial' => false,
                ],
                [
                    'codigo' => 'MED-006',
                    'nombre' => 'Clonazepam',
                    'unidad_medida' => 'tableta',
                    'control_especial' => true,
                ],
            ];

            $medicamentos = [];

            foreach ($medicamentosData as $data) {
                $medicamentos[$data['codigo']] = Medicamento::firstOrCreate(
                    ['codigo' => $data['codigo']],
                    [
                        'nombre' => $data['nombre'],
                        'unidad_medida' => $data['unidad_medida'],
                        'control_especial' => $data['control_especial'],
                    ]
                );
            }

            // 3. Lotes: dos por medicamento.
            // MED-001 incluye un lote vencido.
            // MED-002 incluye un lote que vence en 15 días.
            $lotesData = [];

            foreach ($medicamentosData as $index => $data) {
                $codigo = $data['codigo'];

                $lotesData[] = [
                    'codigo' => "LOTE-{$codigo}-A",
                    'medicamento_codigo' => $codigo,
                    'fecha_vencimiento' => now()
                        ->addMonths(6 + $index)
                        ->toDateString(),
                ];

                $fechaLoteB = match ($codigo) {
                    'MED-001' => now()->subDays(10)->toDateString(),
                    'MED-002' => now()->addDays(15)->toDateString(),
                    default => now()
                        ->addMonths(12 + $index)
                        ->toDateString(),
                };

                $lotesData[] = [
                    'codigo' => "LOTE-{$codigo}-B",
                    'medicamento_codigo' => $codigo,
                    'fecha_vencimiento' => $fechaLoteB,
                ];
            }

            $lotes = [];

            foreach ($lotesData as $data) {
                $medicamento = $medicamentos[$data['medicamento_codigo']];

                $lotes[$data['codigo']] = Lote::firstOrCreate(
                    ['codigo' => $data['codigo']],
                    [
                        'medicamento_id' => $medicamento->id,
                        'fecha_vencimiento' => $data['fecha_vencimiento'],
                    ]
                );
            }

            // 4. Existencias iniciales y registro de entradas en kardex.

            foreach ($lotes as $codigoLote => $lote) {
                $bodegaCodigo = match (true) {
                    str_contains($codigoLote, 'MED-006') => 'BOD-CEN',
                    str_contains($codigoLote, 'MED-002') => 'BOD-URG',
                    default => 'BOD-HOSP',
                };

                DB::transaction(function () use (
                    $lote,
                    $bodegas,
                    $bodegaCodigo
                ) {
                    $existencia = Existencia::firstOrCreate(
                        [
                            'lote_id' => $lote->id,
                            'medicamento_id' => $lote->medicamento_id,
                            'bodega_id' => $bodegas[$bodegaCodigo]->id,
                        ],
                        [
                            'cantidad' => 100,
                        ]
                    );

                    $movimientoInicialExiste = InventarioMovimiento::where(
                        'existencia_id',
                        $existencia->id
                    )
                        ->where('tipo_movimiento', 'entrada')
                        ->where(
                            'motivo',
                            'Carga inicial de inventario de demostración.'
                        )
                        ->exists();

                    if (! $movimientoInicialExiste) {
                        $rolRegente = Role::where('name', 'medico')->firstOrFail();

                        $regente = User::where('role_id', $rolRegente->id)
                            ->firstOrFail();

                        InventarioMovimiento::create([
                            'existencia_id' => $existencia->id,
                            'tipo_movimiento' => 'entrada',
                            'cantidad' => $existencia->cantidad,
                            'user_id' => $regente->id,
                            'motivo' => 'Carga inicial de inventario de demostración.',
                        ]);
                    }
                });
            }

            // 5. Pacientes sintéticos
            $pacientesData = [
                [
                    'ingreso' => 'ING-DEMO-001',
                    'nombre' => 'Paciente',
                    'apellido' => 'Prueba Uno',
                    'num_identidad' => 'DEMO-100001',
                    'fecha_nacimiento' => '1985-04-12',
                ],
                [
                    'ingreso' => 'ING-DEMO-002',
                    'nombre' => 'Paciente',
                    'apellido' => 'Prueba Dos',
                    'num_identidad' => 'DEMO-100002',
                    'fecha_nacimiento' => '1992-08-23',
                ],
                [
                    'ingreso' => 'ING-DEMO-003',
                    'nombre' => 'Paciente',
                    'apellido' => 'Prueba Tres',
                    'num_identidad' => 'DEMO-100003',
                    'fecha_nacimiento' => '1978-11-05',
                ],
            ];

            $pacientes = [];

            foreach ($pacientesData as $data) {
                $pacientes[] = Paciente::firstOrCreate(
                    ['ingreso' => $data['ingreso']],
                    [
                        'nombre' => $data['nombre'],
                        'apellido' => $data['apellido'],
                        'num_identidad' => $data['num_identidad'],
                        'fecha_nacimiento' => $data['fecha_nacimiento'],
                    ]
                );
            }

            // 6. Usuario médico para registrar las prescripciones
            $rolMedico = Role::where('name', 'medico')->firstOrFail();

            $medico = User::where('role_id', $rolMedico->id)
                ->firstOrFail();

            // Tres prescripciones, una por paciente.
            // La tercera incluye el medicamento de control especial.
            $prescripcionesData = [
                [
                    'paciente_indice' => 0,
                    'medicamentos' => [
                        ['codigo' => 'MED-001', 'cantidad' => 20],
                        ['codigo' => 'MED-004', 'cantidad' => 14],
                    ],
                ],
                [
                    'paciente_indice' => 1,
                    'medicamentos' => [
                        ['codigo' => 'MED-003', 'cantidad' => 21],
                        ['codigo' => 'MED-005', 'cantidad' => 10],
                    ],
                ],
                [
                    'paciente_indice' => 2,
                    'medicamentos' => [
                        ['codigo' => 'MED-006', 'cantidad' => 10],
                    ],
                ],
            ];

            foreach ($prescripcionesData as $data) {
                $paciente = $pacientes[$data['paciente_indice']];

                $prescripcion = Prescripcion::firstOrCreate([
                    'paciente_id' => $paciente->id,
                    'user_id' => $medico->id,
                ]);

                foreach ($data['medicamentos'] as $detalleData) {
                    $medicamento = $medicamentos[$detalleData['codigo']];

                    PrescripcionDetalle::firstOrCreate(
                        [
                            'prescripcion_id' => $prescripcion->id,
                            'medicamento_id' => $medicamento->id,
                        ],
                        [
                            'cantidad_prescrita' => $detalleData['cantidad'],
                        ]
                    );
                }
            }

            // Configuración de stock mínimo por bodega y medicamento.
            $minimos = [
                ['bodega' => 'BOD-CEN', 'medicamento' => 'MED-001', 'cantidad_minima' => 20],
                ['bodega' => 'BOD-CEN', 'medicamento' => 'MED-006', 'cantidad_minima' => 120],
                ['bodega' => 'BOD-URG', 'medicamento' => 'MED-002', 'cantidad_minima' => 20],
                ['bodega' => 'BOD-HOSP', 'medicamento' => 'MED-003', 'cantidad_minima' => 20],
            ];

            foreach ($minimos as $minimo) {
                InventarioMinimo::updateOrCreate(
                    [
                        'bodega_id' => $bodegas[$minimo['bodega']]->id,
                        'medicamento_id' => $medicamentos[$minimo['medicamento']]->id,
                    ],
                    [
                        'cantidad_minima' => $minimo['cantidad_minima'],
                    ]
                );
            }
        });
    }
}
