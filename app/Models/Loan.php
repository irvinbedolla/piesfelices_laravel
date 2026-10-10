<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $table = 'prestamos';
    protected $primaryKey = 'pres_id';

    protected $fillable = [
        'pres_descripcion',
        'pres_costo',
        'pres_restante',
        'pres_fecha',
        'usuario',
        'user_id',
        'sucursal',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario', 'id');
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class, 'id', 'pres_id');
    }
}