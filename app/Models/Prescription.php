<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_name',
        'diagnosis',
        'indications',
        'next_appointment',
        'branch_name',
        'user_id',
    ];
}