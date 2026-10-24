<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispensacionDetalle extends Model
{
    protected $table = 'dispensacion_detalles';

    protected $fillable = ['dispensacion_id', 'medicamento_id', 'lote_id', 'cantidad_dispensada', 'existencia'];
}
