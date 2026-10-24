<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescripcionDetalle extends Model
{
    protected $table = 'prescripcion_detalles';

    protected $fillable = ['prescripcion_id', 'medicamento_id', 'cantidad_prescrita', 'cantidad_dispensada'];
}
