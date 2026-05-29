<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    use HasFactory;

    protected $primaryKey = 'cedula';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['cedula', 'nombre', 'sexo', 'pcd'];

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'empleado_id', 'cedula');
    }
}
