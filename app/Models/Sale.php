<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'venta_id';

    protected $fillable = [
        'venta_idempleado',
        'venta_idcliente',
        'nombre_cliente',
        'correo_cliente',
        'venta_fecha',
        'venta_hora',
        'venta_tipopago',
        'venta_diagnostico',
        'venta_abono',
        'venta_total',
        'venta_tipo',
        'tipo',
        'venta_sucursal',
        'venta_descuento',
        'venta_factura',
        'numero_factura',
        'venta_cfdi',
        'venta_tipo_factura',
        'venta_tipo',
        'total',
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

    // Relación con la nueva tabla de abonos
    public function payments()
    {
        return $this->hasMany(CreditPayment::class, 'venta_id', 'venta_id');
    }
}