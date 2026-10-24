<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExistenciaResource extends JsonResource
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
            'cantidad' => $this->cantidad,

            'bodega' => [
                'id' => $this->bodega->id,
                'codigo' => $this->bodega->codigo,
                'nombre' => $this->bodega->nombre,
            ],

            'medicamento' => [
                'id' => $this->medicamento->id,
                'codigo' => $this->medicamento->codigo,
                'nombre' => $this->medicamento->nombre,
                'unidad_medida' => $this->medicamento->unidad_medida,
                'control_especial' => (bool) $this->medicamento->control_especial,
            ],

            'lote' => [
                'id' => $this->lote->id,
                'codigo' => $this->lote->codigo,
                'fecha_vencimiento' => $this->lote->fecha_vencimiento,
            ],
        ];
    }
}
