<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetricaOperativa extends Model
{
    use HasFactory;

    protected $table = 'metricas_operativas';

    protected $fillable = [
        'tienda_id',
        'mes',
        'anio',
        'participacion_encuesta',
        'compromiso_encuesta',
        'transacciones',
        'horas_hombre'
    ];

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }
}
