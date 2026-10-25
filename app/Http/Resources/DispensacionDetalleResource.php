<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DispensacionDetalleResource extends JsonResource
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
            'medicamento_id' => $this->medicamento_id,
            'lote_id' => $this->lote_id,
            'prescripcion_detalle_id' => $this->prescripcion_detalle_id,
            'cantidad_dispensada' => $this->cantidad_dispensada,
            'cantidad_existencia_despues' => $this->cantidad_existencia_despues,
        ];
    }
}
