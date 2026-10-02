<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'branch_id',
        'name',
        'phone',
        'position',
        'salary',
        'commission_rate',
        'status',
        'hired_at',
    ];

    protected $casts = [
        'salary'          => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'status'          => 'boolean',
        'hired_at'        => 'date',
    ];

    /**
     * Relación opcional con el Usuario del sistema.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la Sucursal asignada.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}