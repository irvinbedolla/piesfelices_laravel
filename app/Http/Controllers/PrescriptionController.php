<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionController extends Controller
{
    /**
     * Listado de Recetas / Expedientes con filtro por sucursal
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::all();
        $selectedBranch = $request->get('branch', $user->branch?->name ?? 'MATRIZ');

        $prescriptions = Prescription::when($selectedBranch && $selectedBranch !== 'TODOS', function ($q) use ($selectedBranch) {
            $q->where('branch_name', $selectedBranch);
        })->latest()->paginate(25);

        return view('prescriptions.index', compact('prescriptions', 'branches', 'selectedBranch'));
    }

    /**
     * Formulario para Generar Nueva Receta
     */
    public function create()
    {
        return view('prescriptions.create');
    }

    /**
     * Guardar Nueva Receta
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_name'     => 'required|string|max:255',
            'diagnosis'        => 'required|string|max:255',
            'indications'      => 'required|string',
            'next_appointment' => 'nullable|date',
        ]);

        $user = Auth::user();

        Prescription::create([
            'patient_name'     => $request->patient_name,
            'diagnosis'        => $request->diagnosis,
            'indications'      => $request->indications,
            'next_appointment' => $request->next_appointment,
            'branch_name'      => $user->branch?->name ?? 'MATRIZ',
            'user_id'          => $user->id,
        ]);

        return redirect()->route('prescriptions.index')->with('success', 'Receta guardada con éxito.');
    }

    /**
     * Formulario de Edición
     */
    public function edit($id)
    {
        $prescription = Prescription::findOrFail($id);
        $user = Auth::user();

        return view('prescriptions.edit', compact('prescription', 'user'));
    }

    /**
     * Actualizar Receta Existente
     */
    public function update(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);

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

        return redirect()->route('prescriptions.index')->with('success', 'Receta actualizada con éxito.');
    }

    /**
     * Eliminar Receta
     */
    public function destroy($id)
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->delete();

        return redirect()->route('prescriptions.index')->with('success', 'Receta eliminada.');
    }

    /**
     * Generar PDF con datos dinámicos de la sucursal
     */
    public function pdf($id)
    {
        $prescription = Prescription::findOrFail($id);
        
        // Obtener los datos reales de la sucursal registrada en la receta
        $branch = Branch::where('name', $prescription->branch_name)->first() 
                ?? Branch::first();

        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription', 'branch'))
            ->setPaper('a5', 'landscape');

        return $pdf->stream("receta_#{$prescription->id}_{$prescription->patient_name}.pdf");
    }
}