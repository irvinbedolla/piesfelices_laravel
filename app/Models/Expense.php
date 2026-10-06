<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'retiros';

    protected $fillable = [
        'concepto',
        'cantida',
        'fecha',
        'sucursal',
        'tipo',
        'status',
        'user_id',
    ];
}