<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        // 1. Parámetros de DataTable nativa
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 2. Consulta Base
        $query = Branch::query();

        // 3. Buscador Global por Nombre, Teléfono o Dirección
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // 4. Ordenamiento dinámico seguro
        $allowedSorts = ['name', 'phone', 'address', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // 5. Paginación manteniendo parámetros en URL
        $branches = $query->paginate($perPage)->withQueryString();

        return view('branches.index', compact('branches', 'perPage', 'sortBy', 'sortOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:branches,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Branch::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => true,
        ]);

        return redirect()->route('branches.index')->with('success', 'Sucursal registrada con éxito.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Sucursal eliminada con éxito.');
    }

    public function setMatrix(Branch $branch)
    {
        try {
            DB::transaction(function () use ($branch) {
                // 1. Quitar la marca de Matriz a todas las sucursales
                Branch::query()->update(['is_matrix' => false]);

                // 2. Establecer la sucursal seleccionada como la nueva Matriz
                $branch->update(['is_matrix' => true]);
            });

            return redirect()->route('branches.index')
                ->with('success', "La sucursal '{$branch->name}' ahora es la Matriz Central.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cambiar la sucursal matriz: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
        ]);

        // Si se marca como matriz esta sucursal, desmarcar las demás (opcional)
        if ($request->has('is_matrix') && $request->is_matrix == 1) {
            Branch::where('id', '!=', $id)->update(['is_matrix' => 0]);
        }

        $branch->update([
            'name'      => $request->name,
            'address'   => $request->address,
            'phone'     => $request->phone,
            'is_matrix' => $request->has('is_matrix') ? 1 : 0,
        ]);

        return redirect()->route('branches.index')->with('success', '¡Sucursal actualizada correctamente!');
    }
}