<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Matriz de permisos asociada a este Rol.
     */
    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class, 'role_id');
    }

    /**
     * Usuarios que tienen asignado este Rol.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    // En la seccion de relaciones:
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Jerarquia de Permisos:
     * 1. Admin General (user_type == 0) -> Acceso Total
     * 2. Si el usuario tiene 'has_permission' = true -> Utiliza sus permisos de usuario ($user->permission)
     * 3. Si no -> Hereda la matriz de permisos asignada a su Rol ($user->role->permission)
     */
    public function hasAccessTo(string $module): bool
    {
        if ($this->status !== 'activo') {
            return false;
        }

        if ($this->user_type === 0) {
            return true;
        }

        // Permisos personalizados a nivel usuario
        if ($this->has_permission && $this->permission) {
            return (bool) ($this->permission->{$module} ?? false);
        }

        // Permisos por defecto segun su Rol
        if ($this->role && $this->role->permission) {
            return (bool) ($this->role->permission->{$module} ?? false);
        }

        return false;
    }
}