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
    public function index()
    {
        $users = User::with(['permission', 'role'])->latest()->paginate(10);
        return view('users.index', compact('users'));
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
            'user_type'   => 'required|integer',
            'branch_name' => 'required|string|max:100',
            'role_id'     => 'required|exists:roles,id',
            'status'      => 'required|string',
        ]);

        User::create([
            'username'    => $request->username,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'user_type'   => $request->user_type,
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
            'user_type'   => 'required|integer',
            'branch_name' => 'required|string|max:100',
            'role_id'     => 'required|exists:roles,id',
            'status'      => 'required|string',
        ]);

        $data = [
            'username'    => $request->username,
            'email'       => $request->email,
            'user_type'   => $request->user_type,
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