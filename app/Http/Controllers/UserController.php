<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listado de usuarios con sus relaciones.
     */
    public function index(Request $request)
    {
        // 1. Parámetros de la DataTable nativa
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 2. Consulta Base con Eager Loading de relaciones
        $query = User::with(['permission', 'role', 'branch']);

        // 3. Buscador Global por usuario, correo, sucursal o estado
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhereHas('role', function ($r) use ($search) {
                    $r->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('branch', function ($b) use ($search) {
                    $b->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 4. Ordenamiento seguro
        $allowedSorts = ['username', 'email', 'user_type', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // 5. Paginación manteniendo la query string en las URLs de navegación
        $users = $query->paginate($perPage)->withQueryString();

        return view('users.index', compact('users', 'perPage', 'sortBy', 'sortOrder'));
    }

    /**
     * Formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $branches = Branch::where('status', true)->get();
        $roles = Role::all();
        return view('users.create', compact('branches', 'roles'));
    }

    /**
     * Guarda el nuevo usuario en la base de datos.
     */
   public function store(Request $request)
    {
        $request->validate([
            'username'    => 'required|string|max:50|unique:users,username',
            'email'       => 'nullable|email|max:255|unique:users,email',
            'password'    => 'required|string|min:6',
            'branch_name' => 'required|string|max:100',
            'role_id'     => 'required|exists:roles,id',
            'status'      => 'required|string',
        ]);

        User::create([
            'username'    => $request->username,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'user_type'   => 1,
            'branch_name' => $request->branch_name,
            'role_id'     => $request->role_id,
            'status'      => $request->status,
            'is_doctor'   => $request->has('is_doctor'),
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario registrado con éxito.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username'    => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email'       => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'branch_name' => 'required|string|max:100',
            'role_id'     => 'required|exists:roles,id',
            'status'      => 'required|string',
        ]);

        $data = [
            'username'    => $request->username,
            'email'       => $request->email,
            'branch_name' => $request->branch_name,
            'role_id'     => $request->role_id,
            'status'      => $request->status,
            'is_doctor'   => $request->has('is_doctor'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Formulario para editar un usuario existente.
     */
    public function edit(User $user)
    {
        $user->load(['permission', 'role']);
        $branches = Branch::where('status', true)->get();
        $roles = Role::all();
        return view('users.edit', compact('user', 'branches', 'roles'));
    }

    

    /**
     * Eliminación lógica de un usuario (Soft Delete).
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario desactivado correctamente.');
    }
}