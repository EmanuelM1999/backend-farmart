<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispensacionDetalle extends Model
{
    protected $table = 'dispensacion_detalles';

    protected $fillable = ['dispensacion_id', 'medicamento_id', 'prescripcion_detalle_id', 'lote_id', 'cantidad_dispensada', 'cantidad_existencia_despues'];

    protected function casts(): array
    {
        return [
            'cantidad_dispensada' => 'integer',
            'cantidad_existencia_despues' => 'integer',
        ];
    }

    public function dispensacion(): BelongsTo
    {
        return $this->belongsTo(Dispensacion::class);
    }

    public function prescripcionDetalle(): BelongsTo
    {
        return $this->belongsTo(
            PrescripcionDetalle::class,
            'prescripcion_detalle_id'
        );
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
