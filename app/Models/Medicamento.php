<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicamento extends Model
{
    protected $fillable = ['codigo', 'nombre', 'unidad_medida', 'control_especial'];

    protected function casts(): array
    {
        return [
            'control_especial' => 'boolean',
        ];
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class);
    }

    public function prescripcionDetalles(): HasMany
    {
        return $this->hasMany(PrescripcionDetalle::class);
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
