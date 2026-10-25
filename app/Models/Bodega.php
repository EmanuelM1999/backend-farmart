<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bodega extends Model
{
    protected $fillable = ['codigo', 'nombre', 'ubicacion', 'estado'];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function existencias(): HasMany
    {
        return $this->hasMany(Existencia::class);
    }

    public function trasladosOrigen(): HasMany
    {
        return $this->hasMany(Traslado::class, 'bodega_origen_id');
    }

    public function trasladosDestino(): HasMany
    {
        return $this->hasMany(Traslado::class, 'bodega_destino_id');
    }
}
