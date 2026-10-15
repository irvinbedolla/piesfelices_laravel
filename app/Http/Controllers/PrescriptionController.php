<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionController extends Controller
{

    public function index(Request $request)
    {
        // Obtener todas las sucursales para el select
        $branches = Branch::orderBy('name', 'asc')->get();

        // Determinar la sucursal activa seleccionada por ID (o la del usuario autenticado por defecto)
        $selectedBranchId = $request->get('branch_id', Auth::user()->branch_id ?? $branches->first()?->id);

        // Consultar recetas filtrando por branch_id
        $prescriptions = Prescription::when($selectedBranchId, function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        })->latest('id')->paginate(10);

        return view('prescriptions.index', compact('prescriptions', 'branches', 'selectedBranchId'));
    }
    
    public function create()
    {
        $user = Auth::user();

        // Si es Admin (ajusta según cómo manejes tus roles, e.g. $user->role_id == 1 o $user->role->name == 'Administrador')
        if ($user->role_id == 1 || $user->role?->name == 'Administrador') {
            $branches = Branch::orderBy('name', 'asc')->get();
        } else {
            // Solo obtiene la sucursal asignada al usuario
            $branches = Branch::where('id', $user->branch_id)->get();
        }

        return view('prescriptions.create', compact('branches'));
    }

    /**
     * Guarda la nueva receta
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = ($user->role_id == 1 || $user->role?->name == 'Administrador');

        // Si es usuario normal, forzamos branch_id al de su cuenta
        if (!$isAdmin) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        $request->validate([
            'patient_name' => 'required|string|max:255',
            'diagnosis'    => 'required|string',
            'indications'  => 'required|string',
            'branch_id'    => 'required|exists:branches,id',
        ]);

        $prescription = Prescription::create([
            'patient_name'     => $request->patient_name,
            'diagnosis'        => $request->diagnosis,
            'indications'      => $request->indications,
            'next_appointment' => $request->next_appointment,
            'branch_id'        => $request->branch_id,
            //'user_id'          => $user->id,
        ]);

        return redirect()->route('prescriptions.index', ['branch_id' => $request->branch_id])
            ->with('success', 'Receta registrada correctamente.');
    }


    /**
     * Formulario de Edición
     */
    public function edit($id)
    {
        $prescription = Prescription::findOrFail($id);
        $user = Auth::user();
        // Cargar las sucursales según el rol del usuario
        if ($user->role_id == 1 || $user->role?->name == 'Administrador') {
            $branches = Branch::orderBy('name', 'asc')->get();
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
        }
        return view('prescriptions.edit', compact('prescription', 'user'));
    }

    /**
     * Actualizar Receta Existente
     */
    public function update(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);

        $user = Auth::user();
        $isAdmin = ($user->role_id == 1 || $user->role?->name == 'Administrador');

        // Si no es admin, forzar el branch_id original o el del usuario
        if (!$isAdmin) {
            $request->merge(['branch_id' => $prescription->branch_id]);
        }

        $request->validate([
            'patient_name'     => 'required|string|max:255',
            'diagnosis'        => 'required|string|max:255',
            'indications'      => 'required|string',
            'next_appointment' => 'nullable|date',
        ]);

        $prescription->update([
            'patient_name'     => $request->patient_name,
            'diagnosis'        => $request->diagnosis,
            'indications'      => $request->indications,
            'next_appointment' => $request->next_appointment,
        ]);

        return redirect()->route('prescriptions.index', ['branch_id' => $prescription->branch_id])
            ->with('success', 'Receta actualizada correctamente.');
    }

    /**
     * Eliminar Receta
     */
    public function destroy(Prescription $prescription)
    {
        $branchId = $prescription->branch_id;
        $prescription->delete();

        return redirect()->route('prescriptions.index', ['branch_id' => $branchId])
            ->with('success', 'Receta eliminada correctamente.');
    }

    /**
     * Generar PDF con datos dinámicos de la sucursal
     */
    public function pdf($id)
    {
        $prescription = Prescription::findOrFail($id);

        // Obtener los datos reales de la sucursal registrada en la receta
        $branch = Branch::where('id', $prescription->branch_id)->first() 
                ?? Branch::first();

        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription', 'branch'))
            ->setPaper('a5', 'landscape');

        return $pdf->stream("receta_#{$prescription->id}_{$prescription->patient_name}.pdf");
    }
}