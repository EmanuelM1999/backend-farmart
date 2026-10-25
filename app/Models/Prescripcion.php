<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescripcion extends Model
{
    protected $table = 'prescripciones';

    protected $fillable = ['paciente_id', 'user_id'];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PrescripcionDetalle::class);
    }

    public function dispensaciones(): HasMany
    {
        return $this->hasMany(Dispensacion::class);
    }
}
