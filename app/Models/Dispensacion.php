<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispensacion extends Model
{
    protected $table = 'dispensaciones';

    protected $fillable = ['paciente_id', 'prescripcion_id', 'user_id', 'clave_idempotencia', 'request_hash'];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function prescripcion(): BelongsTo
    {
        return $this->belongsTo(Prescripcion::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DispensacionDetalle::class);
    }
}
