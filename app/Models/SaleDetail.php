<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    // Nombre real de tu tabla de detalles
    protected $table = 'venta_producto';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio',
        'nombre',
        'sku',
        'vp_tipopago',
    ];

    public function sale()
    {
        // Indicamos que la relación se conecta mediante 'venta_id'
        return $this->belongsTo(Sale::class, 'venta_id', 'venta_id');
    }
}