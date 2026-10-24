<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Existencia extends Model
{
    protected $fillable = ['lote_id', 'medicamento_id', 'bodega_id', 'cantidad'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class);
    }
}
