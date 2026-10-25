<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrasladoDetalleResource extends JsonResource
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
            'cantidad_solicitada' => $this->cantidad_solicitada,
            'cantidad_despachada' => $this->cantidad_despachada,
            'cantidad_recibida' => $this->cantidad_recibida,
        ];
    }
}
