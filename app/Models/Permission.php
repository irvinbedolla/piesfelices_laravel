<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_id',
        'can_sales',
        'can_inventory',
        'can_clients',
        'can_prescriptions',
        'can_expenses',
        'can_credit',
        'can_cash_closing',
        'can_patients',
        'can_suppliers',
        'can_employees',
        'can_users',
    ];

    protected $casts = [
        'can_sales' => 'boolean',
        'can_inventory' => 'boolean',
        'can_clients' => 'boolean',
        'can_prescriptions' => 'boolean',
        'can_expenses' => 'boolean',
        'can_credit' => 'boolean',
        'can_cash_closing' => 'boolean',
        'can_patients' => 'boolean',
        'can_suppliers' => 'boolean',
        'can_employees' => 'boolean',
        'can_users' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}