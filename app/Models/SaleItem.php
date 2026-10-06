<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Sale::class, 'venta_id', 'venta_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'producto_id', 'id');
    }
}