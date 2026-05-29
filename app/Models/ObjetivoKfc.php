<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjetivoKfc extends Model
{
    protected $table = 'objetivos_kfc';

    protected $fillable = ['anio', 'rotacion', 'rgm', 'me', 'retencion'];

    protected $casts = [
        'anio'      => 'integer',
        'rotacion'  => 'float',
        'rgm'       => 'float',
        'me'        => 'float',
        'retencion' => 'float',
    ];
}
