<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'empleado_id',
        'tienda_id',
        'fecha_ingreso',
        'fecha_egreso',
        'banda',
        'puesto',
        'turno',
        'status'
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_egreso' => 'date'
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'cedula');
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }
}
