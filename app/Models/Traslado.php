<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Traslado extends Model
{
    protected $table = 'traslados';

    protected $fillable = ['bodega_origen_id', 'bodega_destino_id', 'solicitante_id', 'aprobador_id', 'estado'];

    public function bodegaOrigen(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_origen_id');
    }

    public function bodegaDestino(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_destino_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TrasladoDetalle::class);
    }
}
