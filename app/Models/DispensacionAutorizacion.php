<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DispensacionAutorizacion extends Model
{
    protected $table = 'dispensacion_autorizaciones';

    protected $fillable = [
        'dispensacion_id',
        'regente_id',
    ];

    public function dispensacion(): BelongsTo
    {
        return $this->belongsTo(Dispensacion::class);
    }

    public function regente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'regente_id');
    }

    public function autorizacion(): HasOne
    {
        return $this->hasOne(DispensacionAutorizacion::class);
    }
}
