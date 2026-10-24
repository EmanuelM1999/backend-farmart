<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioMovimientoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_movimiento' => $this->tipo_movimiento,
            'cantidad' => $this->cantidad,
            'motivo' => $this->motivo,
            'created_at' => $this->created_at,

            'usuario' => $this->whenLoaded('usuario', function () {
                return [
                    'id' => $this->usuario->id,
                    'nombre' => $this->usuario->name,
                    'email' => $this->usuario->email,
                ];
            }),

            'existencia' => $this->whenLoaded('existencia', function () {
                return [
                    'id' => $this->existencia->id,
                    'cantidad_actual' => $this->existencia->cantidad,

                    'bodega' => [
                        'id' => $this->existencia->bodega->id,
                        'codigo' => $this->existencia->bodega->codigo,
                        'nombre' => $this->existencia->bodega->nombre,
                    ],

                    'medicamento' => [
                        'id' => $this->existencia->medicamento->id,
                        'codigo' => $this->existencia->medicamento->codigo,
                        'nombre' => $this->existencia->medicamento->nombre,
                    ],

                    'lote' => [
                        'id' => $this->existencia->lote->id,
                        'codigo' => $this->existencia->lote->codigo,
                        'fecha_vencimiento' => $this->existencia->lote->fecha_vencimiento,
                    ],
                ];
            }),
        ];
    }
}
