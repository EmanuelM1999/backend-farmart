<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarioMinimoResource extends JsonResource
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
            'cantidad_minima' => $this->cantidad_minima,
            'cantidad_actual' => (int) $this->cantidad_actual,
            'faltante' => (int) $this->faltante,

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
            ],
        ];
    }
}
