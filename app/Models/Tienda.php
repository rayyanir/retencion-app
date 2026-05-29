<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tienda extends Model
{
    use HasFactory;

    protected $fillable = ['cdc', 'codigo_corto', 'tipo', 'zona_id'];

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    public function metricasOperativas(): HasMany
    {
        return $this->hasMany(MetricaOperativa::class);
    }
}
