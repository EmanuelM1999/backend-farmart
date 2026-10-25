<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrasladoResource extends JsonResource
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
            'bodega_origen_id' => $this->bodega_origen_id,
            'bodega_destino_id' => $this->bodega_destino_id,
            'solicitante_id' => $this->solicitante_id,
            'aprobador_id' => $this->aprobador_id,
            'estado' => $this->estado,

            'detalles' => TrasladoDetalleResource::collection(
                $this->whenLoaded('detalles')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
