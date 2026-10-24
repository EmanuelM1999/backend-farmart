<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $fillable = ['codigo', 'medicamento_id', 'fecha_vencimiento'];
}
