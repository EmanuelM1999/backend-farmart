<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';

    protected $fillable = ['existencia_id', 'tipo_movimiento', 'cantidad', 'user_id', 'motivo'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    public function existencia(): BelongsTo
    {
        return $this->belongsTo(Existencia::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
