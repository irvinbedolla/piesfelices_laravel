<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        // 1. Parámetros de DataTable nativa
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 2. Consulta Base
        $query = Supplier::query();

        // 3. Buscador Global
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                ->orWhere('contact_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // 4. Ordenamiento dinámico seguro
        $allowedSorts = ['company_name', 'contact_name', 'city', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // 5. Paginación con parámetros en URL
        $suppliers = $query->paginate($perPage)->withQueryString();

        return view('suppliers.index', compact('suppliers', 'perPage', 'sortBy', 'sortOrder'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:150',
            'contact_name' => 'nullable|string|max:150',
            'phone'        => 'nullable|string|max:20',
            'city'         => 'nullable|string|max:100',
            'email'        => 'nullable|email|max:150',
            'address'      => 'nullable|string',
        ]);

        Supplier::create([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'city'         => $request->city,
            'email'        => $request->email,
            'address'      => $request->address,
            'status'       => $request->has('status'),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor registrado exitosamente.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'company_name' => 'required|string|max:150',
            'contact_name' => 'nullable|string|max:150',
            'phone'        => 'nullable|string|max:20',
            'city'         => 'nullable|string|max:100',
            'email'        => 'nullable|email|max:150',
            'address'      => 'nullable|string',
        ]);

        $supplier->update([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'phone'        => $request->phone,
            'city'         => $request->city,
            'email'        => $request->email,
            'address'      => $request->address,
            'status'       => $request->has('status'),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado correctamente.');
    }

    /**
     * Genera y descarga el PDF de proveedores
     */
    public function generatePdf()
    {
        $suppliers = Supplier::where('status', true)->get();
        
        $pdf = Pdf::loadView('suppliers.pdf', compact('suppliers'));
        return $pdf->download('Reporte_Proveedores_PiesFelices.pdf');
    }
}