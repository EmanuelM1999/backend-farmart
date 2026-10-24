<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $fillable = ['codigo', 'medicamento_id', 'fecha_vencimiento'];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
        ];
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class);
    }

    public function dispensacionDetalles(): HasMany
    {
        return $this->hasMany(DispensacionDetalle::class);
    }

    public function trasladoDetalles(): HasMany
    {
        return $this->hasMany(TrasladoDetalle::class);
    }
}
