<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $table = 'pago_prestamo';

    protected $fillable = [
        'idPrestamo',
        'monto',
        'fechaPretamo',
        'sucursal',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'idPrestamo', 'pres_id');
    }
}