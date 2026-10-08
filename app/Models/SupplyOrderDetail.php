<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyOrderDetail extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo (opcional si sigue la convención plural).
     */
    protected $table = 'supply_order_details';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'supply_order_id',
        'product_id',
        'quantity_requested',
        'stock_at_request',
        'quantity_requested',
    ];

    /**
     * Relación con la cabecera de la Orden de Surtido.
     */
    public function order()
    {
        return $this->belongsTo(SupplyOrder::class, 'supply_order_id');
    }

    /**
     * Relación con el Producto o Material solicitado.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}