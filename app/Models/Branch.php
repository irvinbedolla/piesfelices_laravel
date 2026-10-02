<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'status','is_matrix'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public static function getMatrixBranch()
    {
        return self::where('is_matrix', true)->first() ?? self::first();
    }
}