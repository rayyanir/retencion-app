<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricaReportada extends Model
{
    use HasFactory;

    protected $table = 'metricas_reportadas';

    protected $fillable = [
        'categoria',
        'anio',
        'mes',
        'seccion',
        'zona_num',
        'codigo',
        'nombre',
        'tipo_registro',
        'activos',
        'salidas',
        'rotacion',
        'salidas_0_12',
        'retencion',
        'ingresos'
    ];

    protected $casts = [
        'anio' => 'integer',
        'mes' => 'integer',
        'zona_num' => 'integer',
        'activos' => 'integer',
        'salidas' => 'integer',
        'rotacion' => 'double',
        'salidas_0_12' => 'integer',
        'retencion' => 'double',
        'ingresos' => 'integer',
    ];
}
