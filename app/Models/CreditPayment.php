<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $table = 'abonos';
    protected $primaryKey = 'abono_id';

    protected $guarded = [];

    // Relación inversa con la venta
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'abono_venta', 'venta_id');
    }
}