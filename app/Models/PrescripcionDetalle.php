<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescripcionDetalle extends Model
{
    protected $table = 'prescripcion_detalles';

    protected $fillable = ['prescripcion_id', 'medicamento_id', 'cantidad_prescrita'];

    protected function casts(): array
    {
        return [
            'cantidad_prescrita' => 'integer',
        ];
    }

    public function prescripcion(): BelongsTo
    {
        return $this->belongsTo(Prescripcion::class);
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function dispensacionDetalles(): HasMany
    {
        return $this->hasMany(
            DispensacionDetalle::class,
            'prescripcion_detalle_id'
        );
    }
}
