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
        $userRole = $user->role_id ?? $user->role ?? $user->tipo_seccion ?? 0;

        // 1. Obtener sucursales permitidas
        if (in_array($userRole, [1])) {
            $branches = Branch::all();
            $selectedBranch = $request->get('branch', 'TODOS');
        } else {
            $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';
            $branches = Branch::where('name', $userBranch)->get();
            $selectedBranch = $userBranch;
        }

        // 2. Obtener clientes según la sucursal activa
        $customers = Customer::when(!in_array($userRole, [0, 1]), function ($q) use ($selectedBranch) {
            $q->where('sucursal', $selectedBranch);
        })->get();

        // 3. Obtener podólogos (Asegurar que siempre traiga lista válida)
        if (in_array($userRole, [0, 1])) {
            $podiatrists = User::when($userRole == 3, function ($q) use ($selectedBranch) {
                $q->where('branch_name', $selectedBranch);
            })->get();
            
            // Fallback si la consulta de podólogos viniera vacía
            if ($podiatrists->isEmpty()) {
                $podiatrists = User::all();
            }
        } else {
            $podiatrists = collect([$user]);
        }

        return view('appointments.index', compact('branches', 'selectedBranch', 'customers', 'podiatrists', 'user', 'userRole'));
    }

    /**
     * Guardar Cita obligando el envío del Podólogo
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'start'         => 'required',
                'podiatrist_id' => 'required', // OBLIGATORIO: Debe asignarse un podólogo
                'customer_id'   => 'required',
            ], [
                'podiatrist_id.required' => 'Por favor selecciona un podólogo asignado.',
                'customer_id.required'   => 'Por favor selecciona un cliente/paciente de la lista.'
            ]);

            $customerId = $request->filled('customer_id') ? (int) $request->customer_id : null;

            $customer = Customer::find($request->customer_id);
            $customerName = $customer ? $customer->nombre : 'Cliente General';
            $title = $request->filled('title') ? $request->title : 'Cita con ' . $customerName;

            $startDate = \Carbon\Carbon::parse($request->start);
            $repeatCount = max(1, min((int) $request->get('repeat_count', 1), 12));

            for ($i = 0; $i < $repeatCount; $i++) {
                $currentStart = $startDate->copy()->addWeeks($i);
                // Fecha fin = Fecha inicio + 1 segundo
                $currentEnd = $currentStart->copy()->addSecond();

                Appointment::create([
                    'title'         => $title,
                    'start'         => $currentStart->toDateTimeString(),
                    'end'           => $currentEnd->toDateTimeString(),
                    'branch_name'   => $request->branch_name ?? $request->branch ?? 'MATRIZ',
                    'podiatrist_id' => $request->podiatrist_id, // Asignado desde el selector
                    'customer_id'   => $customerId,
                    'color'         => $request->color ?? '#8e24aa',
                    'notes'         => $request->notes ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cita(s) agendada(s) correctamente.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => collect($e->errors())->first()[0] ?? 'Datos incompletos.'
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Error al agendar cita: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eventos para FullCalendar
     */
    public function getEvents(Request $request)
    {
        $query = Appointment::with(['customer', 'podiatrist']);

        if ($request->has('branch') && $request->branch !== 'TODOS') {
            $query->where(function($q) use ($request) {
                $q->where('branch_name', $request->branch);
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $appointments = $query->get();

        $events = $appointments->map(function ($app) {
            return [
                'id'    => $app->id,
                'title' => $app->title,
                'start' => $app->start,
                'end'   => $app->end,
                'color' => $app->color ?? '#8e24aa',
                'extendedProps' => [
                    'branch'      => $app->branch_name ?? 'PODOFARMA',
                    'podiatrist' => $app->podiatrist?->name ?? $app->podiatrist?->username ?? 'Sin asignar',
                    'customer'    => $app->customer->nombre ?? 'Cliente General',
                    'customer_id' => $app->customer_id,
                    'notes'       => $app->notes,
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Actualizar Fechas al arrastrar (Drag & Drop)
     */
    public function updateDates(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

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
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Obtener Historial de Citas del Cliente (Soporta ID o Nombre)
     */
    public function getCustomerAppointments($customerId)
    {
        try {
            $appointments = Appointment::where(function($q) use ($customerId) {
                    $q->where('customer_id', $customerId)
                      ->orWhereHas('customer', function($subQ) use ($customerId) {
                          $subQ->where('nombre', 'LIKE', "%{$customerId}%");
                      });
                })
                ->with(['podiatrist', 'customer'])
                ->orderBy('start', 'desc')
                ->get()
                ->map(function ($app) {
                    return [
                        'id'          => $app->id,
                        'title'       => $app->title,
                        'start'       => $app->start,
                        'end'         => $app->end,
                        'branch'      => $app->branch_name ?? 'PODOFARMA',
                        'customer'    => $app->customer->nombre ?? 'N/A',
                        'status'      => $app->status ?? 'Pendiente',
                        'notes'       => $app->notes,
                        'podiatrist' => $app->podiatrist?->name ?? $app->podiatrist?->username ?? 'Sin asignar',
                    ];
                });

            return response()->json($appointments);
        } catch (\Exception $e) {
            \Log::error("Error en getCustomerAppointments: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}