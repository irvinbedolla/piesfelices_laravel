<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
   public function index(Request $request)
    {
        // 1. Parámetros de DataTable nativa
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 2. Consulta Base con Eager Loading y Conteo de Usuarios
        $query = Role::with('permission')->withCount('users');

        // 3. Buscador Global por Nombre o Descripción del Rol
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 4. Ordenamiento dinámico seguro
        $allowedSorts = ['name', 'description', 'users_count', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // 5. Paginación manteniendo parámetros en URL
        $roles = $query->paginate($perPage)->withQueryString();

        return view('roles.index', compact('roles', 'perPage', 'sortBy', 'sortOrder'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('roles.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        Permission::create([
            'role_id'           => $role->id,
            'can_sales'         => $request->has('can_sales'),
            'can_inventory'     => $request->has('can_inventory'),
            'can_clients'       => $request->has('can_clients'),
            'can_prescriptions' => $request->has('can_prescriptions'),
            'can_expenses'      => $request->has('can_expenses'),
            'can_credit'        => $request->has('can_credit'),
            'can_cash_closing'  => $request->has('can_cash_closing'),
            'can_patients'      => $request->has('can_patients'),
            'can_suppliers'     => $request->has('can_suppliers'),
            'can_employees'     => $request->has('can_employees'),
            'can_users'         => $request->has('can_users'),
        ]);

        return redirect()->route('roles.index')->with('success', 'Rol creado con exito.');
    }

    public function edit(Role $role)
    {
        $role->load('permission');
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|string|max:50|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        $role->permission()->updateOrCreate(
            ['role_id' => $role->id],
            [
                'can_sales'         => $request->has('can_sales'),
                'can_inventory'     => $request->has('can_inventory'),
                'can_clients'       => $request->has('can_clients'),
                'can_prescriptions' => $request->has('can_prescriptions'),
                'can_expenses'      => $request->has('can_expenses'),
                'can_credit'        => $request->has('can_credit'),
                'can_cash_closing'  => $request->has('can_cash_closing'),
                'can_patients'      => $request->has('can_patients'),
                'can_suppliers'     => $request->has('can_suppliers'),
                'can_employees'     => $request->has('can_employees'),
                'can_users'         => $request->has('can_users'),
            ]
        );

        return redirect()->route('roles.index')->with('success', 'Rol actualizado con exito.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un rol asignado a usuarios activos.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}