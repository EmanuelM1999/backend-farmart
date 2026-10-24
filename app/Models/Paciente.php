<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    protected $fillable = ['ingreso', 'nombre', 'apellido', 'num_identidad', 'fecha_nacimiento'];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function prescripciones(): HasMany
    {
        return $this->hasMany(Prescripcion::class);
    }

    public function dispensaciones(): HasMany
    {
        return $this->hasMany(Dispensacion::class);
    }
}
