<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barcode',
        'name',
        'type',
        'category_id',
        'supplier_id',
        'branch_id',
        'cost_price',
        'sale_price',
        'credit_price',
        'stock_current',
        'stock_min',
        'model',
        'serial_number',
        'color',
        'substance',
        'page_number',
        'sequence',
        'notes',
        'status',
        'image'
    ];

    protected $casts = [
        'cost_price'    => 'decimal:2',
        'sale_price'    => 'decimal:2',
        'credit_price'  => 'decimal:2',
        'stock_current' => 'integer',
        'stock_min'     => 'integer',
        'status'        => 'boolean',
    ];

    /**
     * Relación con las sucursales y su stock individual
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_product')
                    ->withPivot('stock_current', 'stock_min')
                    ->withTimestamps();
    }

    /**
     * Relación con el Proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relación con la Sucursal de origen
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con los movimientos de Kárdex
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Obtener el stock disponible de este producto en una sucursal específica
     */
    public function getStockInBranch($branchId): int
    {
        $pivot = $this->branches()->where('branch_id', $branchId)->first();
        return $pivot ? (int) $pivot->pivot->stock_current : 0;
    }

    /**
     * Accesor para obtener la URL pública de la imagen de forma segura
     */
    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }

        return null;
    }
}