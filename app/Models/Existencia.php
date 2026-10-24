<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Existencia extends Model
{
    protected $fillable = ['lote_id', 'bodega_id', 'cantidad'];
}
