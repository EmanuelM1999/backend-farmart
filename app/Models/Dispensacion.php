<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispensacion extends Model
{
    protected $table = 'dispensaciones';

    protected $fillable = ['paciente_id', 'prescripcion_id', 'user_id', 'clave_idempotencia'];
}
