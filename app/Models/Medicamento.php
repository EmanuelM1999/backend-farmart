<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $fillable = ['codigo', 'nombre', 'unidad_medida', 'control_especial'];
}
