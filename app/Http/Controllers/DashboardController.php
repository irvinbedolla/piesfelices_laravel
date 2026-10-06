<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Appointment;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        // Detección segura de columnas en 'appointments'
        $dateCol = 'created_at';
        if (Schema::hasColumn('appointments', 'cita_fecha')) {
            $dateCol = 'cita_fecha';
        } elseif (Schema::hasColumn('appointments', 'fecha_cita')) {
            $dateCol = 'fecha_cita';
        } elseif (Schema::hasColumn('appointments', 'fecha')) {
            $dateCol = 'fecha';
        }

        $timeCol = $dateCol;
        if (Schema::hasColumn('appointments', 'cita_hora')) {
            $timeCol = 'cita_hora';
        } elseif (Schema::hasColumn('appointments', 'hora_cita')) {
            $timeCol = 'hora_cita';
        } elseif (Schema::hasColumn('appointments', 'hora')) {
            $timeCol = 'hora';
        }

        $appointmentsQuery = Appointment::whereDate($dateCol, date('Y-m-d'));

        if (!$user->isAdmin()) {
            $appointmentsQuery->where('sucursal', $userBranch);
        }

        $upcomingAppointments = $appointmentsQuery->orderBy($timeCol, 'asc')
            ->take(10)
            ->get();

        return view('dashboard', compact('userRole', 'userBranch', 'upcomingAppointments'));
    }

    /**
     * API JSON para alimentar las Gráficas en tiempo real
     */
    public function stats(Request $request)
    {
        try {
            $user = Auth::user();
            $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';
            $isAdmin = method_exists($user, 'isAdmin') ? $user->isAdmin() : in_array((int)($user->tipo_seccion ?? 2), [0, 3]);

            // -------------------------------------------------------------
            // 1. TOP 10 ARTÍCULOS MÁS VENDIDOS DEL MES ACTUAL
            // -------------------------------------------------------------
            $topProductsQuery = DB::table('venta_producto')
                ->join('ventas', 'venta_producto.venta_id', '=', 'ventas.venta_id')
                ->select('venta_producto.nombre', DB::raw('SUM(venta_producto.cantidad) as total_vendido'))
                ->where('ventas.venta_tipo', '!=', 3)
                ->whereMonth('ventas.venta_fecha', date('m'))
                ->whereYear('ventas.venta_fecha', date('Y'));

            if (!$isAdmin) {
                $topProductsQuery->where('ventas.venta_sucursal', $userBranch);
            }

            $topProducts = $topProductsQuery->groupBy('venta_producto.nombre')
                ->orderBy('total_vendido', 'desc')
                ->limit(10)
                ->get();

            // -------------------------------------------------------------
            // 2. CITAS ATENDIDAS POR MES (AÑO ACTUAL)
            // -------------------------------------------------------------
            $dateCol = 'created_at';
            if (Schema::hasColumn('appointments', 'cita_fecha')) {
                $dateCol = 'cita_fecha';
            } elseif (Schema::hasColumn('appointments', 'fecha_cita')) {
                $dateCol = 'fecha_cita';
            } elseif (Schema::hasColumn('appointments', 'fecha')) {
                $dateCol = 'fecha';
            }

            $citasQuery = DB::table('appointments')
                ->select(DB::raw("MONTH({$dateCol}) as mes"), DB::raw('COUNT(*) as total'))
                ->whereYear($dateCol, date('Y'));

            if (!$isAdmin) {
                $citasQuery->where('sucursal', $userBranch);
            }

            $citasMesRaw = $citasQuery->groupBy('mes')->pluck('total', 'mes')->toArray();

            $citasPorMes = [];
            for ($m = 1; $m <= 12; $m++) {
                $citasPorMes[] = (int)($citasMesRaw[$m] ?? 0);
            }

            // -------------------------------------------------------------
            // 3. INGRESOS POR DÍA DE LA SEMANA ACTUAL (EXCLUSIVO ADMIN)
            // -------------------------------------------------------------
            $ingresosPorDia = [0, 0, 0, 0, 0, 0];
            if ($isAdmin) {
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek   = date('Y-m-d', strtotime('saturday this week'));

                $ingresosQuery = DB::table('ventas')
                    ->select(DB::raw('DATE(venta_fecha) as fecha'), DB::raw('SUM(venta_total) as total'))
                    ->where('venta_tipo', '!=', 3)
                    ->whereBetween('venta_fecha', [$startOfWeek, $endOfWeek])
                    ->groupBy('fecha')
                    ->pluck('total', 'fecha')
                    ->toArray();

                $ingresosPorDia = [];
                for ($i = 0; $i < 6; $i++) {
                    $dayDate = date('Y-m-d', strtotime("monday this week +{$i} days"));
                    $ingresosPorDia[] = (float) ($ingresosQuery[$dayDate] ?? 0);
                }
            }

            return response()->json([
                'top_products'    => $topProducts,
                'citas_mes'       => $citasPorMes,
                'ingresos_semana' => $ingresosPorDia,
                'user_branch'     => $userBranch,
                'mes_nombre'      => now()->locale('es')->monthName,
                'anio'            => date('Y')
            ]);

        } catch (\Throwable $e) {
            // Retornar fallback estructurado en caso de error de SQL
            return response()->json([
                'error'           => $e->getMessage(),
                'top_products'    => [],
                'citas_mes'       => array_fill(0, 12, 0),
                'ingresos_semana' => array_fill(0, 6, 0),
                'mes_nombre'      => now()->locale('es')->monthName,
                'anio'            => date('Y')
            ], 200);
        }
    }
}