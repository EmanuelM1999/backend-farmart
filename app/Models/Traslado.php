<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traslado extends Model
{
    protected $table = 'traslados';

    protected $fillable = ['bodega_origen_id', 'bodega_destino_id', 'solicitante_id', 'aprobador_id', 'estado'];
}
