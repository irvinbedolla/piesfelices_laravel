<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $table = 'abonos';
    protected $primaryKey = 'abono_id';

    protected $fillable = [
        'venta_id',
        'abono_cantidad',
        'abono_fecha',
        'abono_sucursal',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'venta_id', 'venta_id');
    }
}