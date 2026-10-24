<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMinimo extends Model
{
    protected $table = 'inventario_minimos';

    protected $fillable = [
        'bodega_id',
        'medicamento_id',
        'cantidad_minima',
    ];

    protected function casts(): array
    {
        return [
            'bodega_id' => 'integer',
            'medicamento_id' => 'integer',
            'cantidad_minima' => 'integer',
        ];
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class);
    }
}
