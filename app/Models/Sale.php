<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'venta_id';

    // Usar $guarded vacio para no tener restricciones de asignación masiva
    protected $guarded = [];

    protected $attributes = [
        'status' => 'Pendiente',
    ];
    
    public function items()
    {
        return $this->hasMany(SaleItem::class, 'venta_id', 'venta_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'venta_idempleado', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'venta_idcliente', 'cliente_id');
    }

    // Relación con los productos/servicios vendidos en la tabla normal de detalles
    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'venta_id', 'venta_id');
    }

    // Relación con los abonos
    public function abonos()
    {
        // hasMany(ModeloAsociado, 'llave_foranea_en_tabla_abonos', 'llave_local_en_tabla_ventas')
        return $this->hasMany(CreditPayment::class, 'abono_venta', 'venta_id');
    }

    // Si tu vista o controlador llama a la relación como payments():
    public function payments()
    {
        return $this->hasMany(CreditPayment::class, 'abono_venta', 'venta_id');
    }
}