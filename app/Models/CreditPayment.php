<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $table = 'abonos';
    protected $primaryKey = 'abono_id';

    protected $fillable = [
        'abono_venta',      // Columna FK correcta en la tabla abonos
        'abono_cantidad',
        'abono_fecha',
        'abono_sucursal',
    ];

    public $timestamps = true;

    // Relación con la Venta
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'abono_venta', 'venta_id');
    }
}