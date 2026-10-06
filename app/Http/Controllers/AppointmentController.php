<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Vista principal del Calendario filtrada por Rol
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role_id ?? 2; // 0/3: Admin, 1: Admin Centro, 2: Podólogo/Estándar

        // 1. Obtener sucursales permitidas
        if (in_array($userRole, [1, 3])) {
            $branches = Branch::all();
            $selectedBranch = $request->get('branch', 'TODOS');
        } else {
            $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';
            $branches = Branch::where('name', $userBranch)->get();
            $selectedBranch = $userBranch; // Fijado a su centro
        }

        // 2. Obtener clientes según la sucursal activa
        $customers = Customer::when(!in_array($userRole, [0, 3]), function ($q) use ($selectedBranch) {
            $q->where('sucursal', $selectedBranch);
        })->get();

        // 3. Obtener podólogos
        if (in_array($userRole, [0, 3, 1])) {
            $podiatrists = User::when($userRole == 1, function ($q) use ($selectedBranch) {
                $q->where('branch_name', $selectedBranch);
            })->get();
        } else {
            // Si es podólogo, sólo se muestra a sí mismo
            $podiatrists = collect([$user]);
        }

        return view('appointments.index', compact('branches', 'selectedBranch', 'customers', 'podiatrists', 'user', 'userRole'));
    }

    /**
     * Eventos JSON para FullCalendar según el Rol
     */
    public function getEvents(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $branch = $request->get('branch', 'TODOS');

        $query = Appointment::with(['customer', 'podiatrist']);

        // Filtro 1: Si es Podólogo (Rol 2), sólo ve SUS PROPIAS citas
        if (!in_array($userRole, [0, 1, 3])) {
            $query->where('podiatrist_id', $user->id);
        }
        // Filtro 2: Si es Admin de Centro (Rol 1), sólo ve las de SU sucursal
        elseif ($userRole == 1) {
            $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';
            $query->where('branch_name', $userBranch);
        }
        // Filtro 3: Si es Admin General (Rol 0/3), usa el filtro seleccionado del dropdown
        elseif ($branch && $branch !== 'TODOS') {
            $query->where('branch_name', $branch);
        }

        $appointments = $query->get();

        $events = $appointments->map(function ($app) {
            return [
                'id'            => $app->id,
                'title'         => $app->title,
                'start'         => $app->start,
                'end'           => $app->end,
                'color'         => $app->color,
                'extendedProps' => [
                    'podiatrist' => $app->podiatrist?->name ?? $app->podiatrist?->username ?? 'Sin Podólogo',
                    'customer'   => $app->customer?->nombre ?? 'Cliente General',
                    'branch'     => $app->branch_name,
                    'status'     => $app->status,
                    'notes'      => $app->notes,
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Guardar Cita validando la Sucursal de creación
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'start'         => 'required|date',
            'end'           => 'required|date|after:start',
            'podiatrist_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;

        // Determinar la sucursal de la cita
        if (in_array($userRole, [0, 3])) {
            $branchName = $request->get('branch_name', $user->branch?->name ?? 'MATRIZ');
        } else {
            // El Admin de Centro o Podólogo sólo asigna a su propia sucursal
            $branchName = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';
        }

        $appointment = Appointment::create([
            'title'         => $request->title,
            'start'         => $request->start,
            'end'           => $request->end,
            'customer_id'   => $request->customer_id ?: null,
            'podiatrist_id' => $request->podiatrist_id,
            'branch_name'   => $branchName,
            'color'         => $request->color ?? '#8e24aa',
            'notes'         => $request->notes,
        ]);

        return response()->json(['success' => true, 'appointment' => $appointment]);
    }

    /**
     * Actualizar Fechas al Mover en el Calendario (Drag & Drop)
     */
    public function updateDates(Request $request, $id)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $appointment = Appointment::findOrFail($id);

        // Si es podólogo o admin de centro, verificar que le corresponda
        if ($userRole == 1 && $appointment->branch_name !== ($user->branch?->name ?? $user->branch_name)) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para modificar citas de otra sucursal.'], 403);
        }

        if (!in_array($userRole, [0, 1, 3]) && $appointment->podiatrist_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Solo puedes mover tus propias citas.'], 403);
        }

        $appointment->update([
            'start' => $request->start,
            'end'   => $request->end,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Cancelar / Eliminar Cita
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $appointment = Appointment::findOrFail($id);

        if ($userRole == 1 && $appointment->branch_name !== ($user->branch?->name ?? $user->branch_name)) {
            return response()->json(['success' => false, 'message' => 'No puedes borrar citas de otra sucursal.'], 403);
        }

        $appointment->delete();

        return response()->json(['success' => true]);
    }
}