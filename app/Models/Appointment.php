<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start',
        'end',
        'branch_name',
        'customer_id',
        'podiatrist_id',
        'color',
        'status',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function podiatrist()
    {
        return $this->belongsTo(User::class, 'podiatrist_id');
    }
}