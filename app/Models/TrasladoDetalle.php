<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrasladoDetalle extends Model
{
    protected $table = 'traslado_detalles';

    protected $fillable = ['traslado_id', 'medicamento_id', 'lote_id', 'cantidad_solicitada', 'cantidad_despachada', 'cantidad_recibida'];

    protected function casts(): array
    {
        return [
            'cantidad_solicitada' => 'integer',
            'cantidad_despachada' => 'integer',
            'cantidad_recibida' => 'integer',
        ];
    }

    public function traslado(): BelongsTo
    {
        return $this->belongsTo(Traslado::class);
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
