<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'cliente_id';

    protected $fillable = [
        'nombre',
        'direccion',
        'cp',
        'telefono',
        'localidad',
        'rfc',
        'correo',
        'sucursal',
        'fecha_nacimiento',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];
}