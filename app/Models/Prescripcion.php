<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescripcion extends Model
{
    protected $table = 'prescripciones';

    protected $fillable = ['paciente_id', 'user_id'];
}
