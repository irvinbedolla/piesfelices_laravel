<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerController extends Controller
{
    /**
     * Listado principal con DataTable Nativa
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('status', true)->get();
        $matrixBranch = Branch::getMatrixBranch();

        // 1. Determinar sucursal activa
        if ($user->type == 1 && $user->branch_id) {
            $userBranchName = Branch::find($user->branch_id)?->name;
            $selectedBranch = $userBranchName;
        } else {
            $selectedBranch = $request->get('sucursal_filtro', 'TODOS');
        }

        // 2. Parámetros DataTable nativa
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'nombre');
        $sortOrder = $request->get('sort_order', 'asc');

        // 3. Consulta Base
        $query = Customer::query();

        if ($selectedBranch !== 'TODOS' && !empty($selectedBranch)) {
            $query->where('sucursal', $selectedBranch);
        }

        // 4. Buscador Global en MySQL
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('rfc', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%")
                  ->orWhere('localidad', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%");
            });
        }

        // 5. Ordenamiento seguro
        $allowedSorts = ['nombre', 'telefono', 'rfc', 'sucursal', 'fecha_nacimiento', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $customers = $query->paginate($perPage)->withQueryString();
        $totalCustomers = $query->count();

        // 6. Consultar cumpleañeros del día (MM-DD)
        $todayMMDD = date('m-d');
        $birthdaysQuery = Customer::whereRaw("DATE_FORMAT(fecha_nacimiento, '%m-%d') = ?", [$todayMMDD]);
        if ($selectedBranch !== 'TODOS' && !empty($selectedBranch)) {
            $birthdaysQuery->where('sucursal', $selectedBranch);
        }
        $birthdays = $birthdaysQuery->orderBy('nombre', 'asc')->get();

        return view('customers.index', compact(
            'customers', 'branches', 'selectedBranch', 'totalCustomers', 
            'birthdays', 'user', 'perPage', 'sortBy', 'sortOrder'
        ));
    }

    /**
     * Guardar nuevo cliente
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:100',
            'telefono'         => 'nullable|string|max:30',
            'correo'           => 'nullable|email|max:50',
            'rfc'              => 'nullable|string|max:13',
            'direccion'        => 'nullable|string|max:100',
            'cp'               => 'nullable|string|max:10',
            'localidad'        => 'nullable|string|max:40',
            'sucursal'         => 'required|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        Customer::create($request->all());

        return redirect()->back()->with('success', 'Cliente registrado exitosamente.');
    }

    /**
     * Actualizar cliente existente
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'nombre'           => 'required|string|max:100',
            'telefono'         => 'nullable|string|max:30',
            'correo'           => 'nullable|email|max:50',
            'rfc'              => 'nullable|string|max:13',
            'direccion'        => 'nullable|string|max:100',
            'cp'               => 'nullable|string|max:10',
            'localidad'        => 'nullable|string|max:40',
            'sucursal'         => 'required|string|max:50',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $customer->update($request->all());

        return redirect()->back()->with('success', 'Información del cliente actualizada.');
    }

    /**
     * Eliminar cliente individual
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->back()->with('success', 'Cliente eliminado correctamente.');
    }

    /**
     * Borrado masivo de clientes seleccionados
     */
    public function destroyBulk(Request $request)
    {
        $request->validate([
            'clientes_ids'   => 'required|array|min:1',
            'clientes_ids.*' => 'exists:clientes,cliente_id',
        ], [
            'clientes_ids.required' => 'Debes seleccionar al menos un cliente para eliminar.'
        ]);

        Customer::whereIn('cliente_id', $request->clientes_ids)->delete();

        return redirect()->back()->with('success', 'Clientes seleccionados eliminados con éxito.');
    }

    /**
     * Exportar reporte de clientes a PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $selectedBranch = $request->get('sucursal_filtro', 'TODOS');

        $query = Customer::query();
        if ($selectedBranch !== 'TODOS' && !empty($selectedBranch)) {
            $query->where('sucursal', $selectedBranch);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('rfc', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('nombre', 'asc')->get();

        $pdf = Pdf::loadView('customers.pdf', compact('customers', 'selectedBranch', 'user'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('reporte_clientes_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Pantalla Principal de Selección: Gestión de Directorio (Clientes / Pacientes)
     */
    public function directory()
    {
        return view('customers.directory');
    }
}