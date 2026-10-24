<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    protected $table = 'inventario_movimientos';

    protected $fillable = ['existencia_id', 'tipo_movimiento', 'cantidad', 'user_id', 'motivo'];
}
