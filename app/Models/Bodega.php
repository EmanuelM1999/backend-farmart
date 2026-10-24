<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    protected $fillable = ['codigo', 'nombre', 'ubicacion', 'estado'];
}
