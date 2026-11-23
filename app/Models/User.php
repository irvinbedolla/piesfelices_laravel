<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected ?array $cachedModulePermissions = null;

    protected $fillable = [
        'username',
        'email',
        'password',
        'employee_id',
        'user_type',      // 0: Admin, 1: Operador
        'branch_name',    // Sucursal asignada
        'role_id',        // Rol asignado
        'has_permission', // boolean
        'status',         // activo / inactivo
        'is_doctor',      // boolean
        'branch_id',
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
            'role_id'           => 'integer',
        ];
    }

    /**
     * Alias seguro por si alguna vista o controlador accede a $user->name
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['name']
            ?? $this->attributes['username']
            ?? 'Usuario';
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
     * Determina si el usuario es Administrador Global.
     */
    public function isAdmin(): bool
    {
        return (int) ($this->user_type ?? 1) === 0 || (int) ($this->role_id ?? 0) === 1;
    }

    /**
     * Evaluación de acceso por Módulo (Jerarquía: Admin -> Permiso Usuario -> Permiso Rol)
     * Cacheada en memoria durante la petición para evitar consultas repetidas en el menú.
     */
    public function hasAccessTo(string $module): bool
    {
        if (($this->status ?? 'activo') !== 'activo') {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        if ($this->cachedModulePermissions === null) {
            if ($this->has_permission && $this->permission) {
                $this->cachedModulePermissions = $this->permission->toArray();
            } elseif ($this->role && $this->role->permission) {
                $this->cachedModulePermissions = $this->role->permission->toArray();
            } else {
                $this->cachedModulePermissions = [];
            }
        }

        return (bool) ($this->cachedModulePermissions[$module] ?? false);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Obtiene la etiqueta legible del rol
     */
    public function getRoleNameAttribute(): string
    {
        return match ((int) ($this->role_id ?? 2)) {
            1 => 'Administrador',
            2 => 'Administrador de Centro',
            3 => 'Recepción',
            default => 'Podólogo',
        };
    }
}