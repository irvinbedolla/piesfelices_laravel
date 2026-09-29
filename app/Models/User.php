<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importar BelongsTo
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'username',
        'email',
        'password',
        'user_type',      // 0: Admin, 1: Operador
        'branch_name',    // Sucursal asignada
        'role_id',        // <-- Rol asignado
        'has_permission', // boolean
        'status',         // activo / inactivo
        'is_doctor',      // boolean
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'has_permission'    => 'boolean',
            'is_doctor'         => 'boolean',
            'user_type'         => 'integer',
        ];
    }

    /**
     * Relación con el Rol asignado.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relación uno a uno con la matriz de permisos individuales.
     */
    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class, 'user_id');
    }

    /**
     * Evaluación de acceso por Módulo (Jerarquía: Admin -> Permiso Usuario -> Permiso Rol).
     */
    public function hasAccessTo(string $module): bool
    {
        if ($this->status !== 'activo') {
            return false;
        }

        // Administrador General -> Acceso Total
        if ($this->user_type === 0) {
            return true;
        }

        // Permisos heredados del Rol asignado
        if ($this->role && $this->role->permission) {
            return (bool) ($this->role->permission->{$module} ?? false);
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 0;
    }
}