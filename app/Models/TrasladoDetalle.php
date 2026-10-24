<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrasladoDetalle extends Model
{
    protected $table = 'traslado_detalles';

    protected $fillable = ['traslado_id', 'medicamento_id', 'lote_id', 'cantidad_solicitada', 'cantidad_despachada', 'cantidad_recibida'];
}
