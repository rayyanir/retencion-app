<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncuestaEngagement extends Model
{
    protected $table = 'encuestas_engagement';

    protected $fillable = [
        'anio',
        'mes',
        'codigo_local',
        'nombre_local',
        'compromiso',
        'experiencia',
        'intencion_permanecer',
        'n_encuestados',
        'notas',
    ];

    protected $casts = [
        'anio'                 => 'integer',
        'mes'                  => 'integer',
        'compromiso'           => 'float',
        'experiencia'          => 'float',
        'intencion_permanecer' => 'float',
        'n_encuestados'        => 'integer',
    ];
}
