<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_name',
        'phone',
        'city',
        'email',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Genera el enlace formateado para WhatsApp
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        if (!$this->phone) return null;

        // Limpiar caracteres no numéricos del teléfono
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);
        
        // Si el número tiene 10 dígitos (México), anteponer la clave del país 52
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '52' . $cleanPhone;
        }

        $message = urlencode("Hola {$this->contact_name}, le saludamos de Pies Felices.");
        return "https://wa.me/{$cleanPhone}?text={$message}";
    }
}