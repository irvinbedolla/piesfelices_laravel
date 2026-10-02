<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Listado de empleados.
     */
    public function index(Request $request)
    {
        // 1. Parámetros de DataTable nativa
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 2. Consulta Base con Eager Loading
        $query = Employee::with(['user', 'branch']);

        // 3. Buscador Global por Nombre, Teléfono, Puesto, Sucursal o Usuario
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%")
                ->orWhereHas('branch', function ($b) use ($search) {
                    $b->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($u) use ($search) {
                    $u->where('username', 'like', "%{$search}%");
                });
            });
        }

        // 4. Ordenamiento dinámico seguro
        $allowedSorts = ['name', 'position', 'salary', 'commission_rate', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // 5. Paginación manteniendo parámetros en URL
        $employees = $query->paginate($perPage)->withQueryString();

        return view('employees.index', compact('employees', 'perPage', 'sortBy', 'sortOrder'));
    }

    /**
     * Formulario para registrar un nuevo empleado.
     */
    public function create()
    {
        $branches = Branch::where('status', true)->get();
        // Usuarios que no están vinculados a ningún empleado
        $users = User::whereDoesntHave('employee')->get();
        
        return view('employees.create', compact('branches', 'users'));
    }

    /**
     * Almacena el empleado en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'phone'           => 'nullable|string|max:20',
            'position'        => 'nullable|string|max:100',
            'salary'          => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'branch_id'       => 'nullable|exists:branches,id',
            'user_id'         => 'nullable|exists:users,id',
            'hired_at'        => 'nullable|date',
        ]);

        Employee::create([
            'name'            => $request->name,
            'phone'           => $request->phone,
            'position'        => $request->position,
            'salary'          => $request->salary,
            'commission_rate' => $request->commission_rate ?? 0,
            'branch_id'       => $request->branch_id,
            'user_id'         => $request->user_id,
            'status'          => $request->has('status'),
            'hired_at'        => $request->hired_at,
        ]);

        return redirect()->route('employees.index')->with('success', 'Empleado registrado exitosamente.');
    }

    /**
     * Formulario para editar un empleado.
     */
    public function edit(Employee $employee)
    {
        $branches = Branch::where('status', true)->get();
        // Usuarios disponibles o el asignado a este empleado
        $users = User::whereDoesntHave('employee')
            ->orWhere('id', $employee->user_id)
            ->get();

        return view('employees.edit', compact('employee', 'branches', 'users'));
    }

    /**
     * Actualiza la información del empleado.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'phone'           => 'nullable|string|max:20',
            'position'        => 'nullable|string|max:100',
            'salary'          => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'branch_id'       => 'nullable|exists:branches,id',
            'user_id'         => 'nullable|exists:users,id',
            'hired_at'        => 'nullable|date',
        ]);

        $employee->update([
            'name'            => $request->name,
            'phone'           => $request->phone,
            'position'        => $request->position,
            'salary'          => $request->salary,
            'commission_rate' => $request->commission_rate ?? 0,
            'branch_id'       => $request->branch_id,
            'user_id'         => $request->user_id,
            'status'          => $request->has('status'),
            'hired_at'        => $request->hired_at,
        ]);

        return redirect()->route('employees.index')->with('success', 'Empleado actualizado correctamente.');
    }

    /**
     * Eliminación lógica (Soft Delete).
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Empleado desactivado correctamente.');
    }
}