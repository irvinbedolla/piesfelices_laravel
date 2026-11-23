<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Appointment;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $userBranch = strtoupper(trim($user->branch?->name ?? $user->branch_name ?? 'MATRIZ'));

        // Verificación segura de rol Administrador
        $isAdmin = method_exists($user, 'isAdmin') 
            ? $user->isAdmin() 
            : in_array((int)($user->tipo_seccion ?? 2), [0, 3]);

        // -------------------------------------------------------------
        // CONSULTA DE CUMPLEAÑEROS DEL DÍA (MM-DD)
        // -------------------------------------------------------------
        $todayMMDD = date('m-d');
        $birthdaysQuery = Customer::whereRaw("DATE_FORMAT(fecha_nacimiento, '%m-%d') = ?", [$todayMMDD]);

        if (!$isAdmin && !empty($userBranch) && $userBranch !== 'TODOS') {
            $birthdaysQuery->where('sucursal', $userBranch);
        }

        $birthdays = $birthdaysQuery->orderBy('nombre', 'asc')->get();

        // -------------------------------------------------------------
        // PRÓXIMAS CITAS DEL DÍA (Rango con horas para usar índice 'start')
        // -------------------------------------------------------------
        $inicioHoy = date('Y-m-d 00:00:00');
        $finHoy    = date('Y-m-d 23:59:59');

        $appointmentsQuery = Appointment::whereBetween('start', [$inicioHoy, $finHoy])
            ->where('status', '!=', 'Cancelada');

        if (!empty($userBranch) && $userBranch !== 'TODOS') {
            $appointmentsQuery->where('branch_name', $userBranch);
        }

        $upcomingAppointments = $appointmentsQuery->orderBy('start', 'asc')
            ->take(10)
            ->get();

        return view('dashboard', compact('userRole', 'userBranch', 'upcomingAppointments', 'isAdmin', 'birthdays'));
    }

    /**
     * API JSON para alimentar las Gráficas en tiempo real
     */
    public function stats(Request $request)
    {
        try {
            $user = Auth::user();
            $userBranch = strtoupper(trim($user->branch?->name ?? $user->branch_name ?? 'MATRIZ'));
            $isAdmin = method_exists($user, 'isAdmin') 
                ? $user->isAdmin() 
                : in_array((int)($user->tipo_seccion ?? 2), [0, 3]);

            // -------------------------------------------------------------
            // 1. TOP 10 ARTÍCULOS MÁS VENDIDOS DEL MES ACTUAL
            // -------------------------------------------------------------
            $inicioMes = date('Y-m-01 00:00:00');
            $finMes    = date('Y-m-t 23:59:59');

            $topProductsQuery = DB::table('venta_producto')
                ->join('ventas', 'venta_producto.venta_id', '=', 'ventas.venta_id')
                ->select('venta_producto.nombre', DB::raw('SUM(venta_producto.cantidad) as total_vendido'))
                ->where('ventas.venta_tipo', '!=', 3)
                ->whereBetween('ventas.venta_fecha', [$inicioMes, $finMes]);

            if (!$isAdmin) {
                if (Schema::hasColumn('ventas', 'venta_sucursal')) {
                    $topProductsQuery->where('ventas.venta_sucursal', $userBranch);
                } elseif (Schema::hasColumn('ventas', 'sucursal_id')) {
                    $topProductsQuery->where('ventas.sucursal_id', $user->sucursal_id);
                }
            }

            $topProducts = $topProductsQuery->groupBy('venta_producto.nombre')
                ->orderBy('total_vendido', 'desc')
                ->limit(10)
                ->get();

            // -------------------------------------------------------------
            // 2. CITAS ATENDIDAS POR MES (AÑO ACTUAL - Rango indexado)
            // -------------------------------------------------------------
            $inicioAnio = date('Y-01-01 00:00:00');
            $finAnio    = date('Y-12-31 23:59:59');

            $citasQuery = DB::table('appointments')
                ->select(DB::raw("MONTH(start) as mes"), DB::raw('COUNT(*) as total'))
                ->whereBetween('start', [$inicioAnio, $finAnio])
                ->where('status', '!=', 'Cancelada');

            if (!$isAdmin && !empty($userBranch) && $userBranch !== 'TODOS') {
                $citasQuery->where('branch_name', $userBranch);
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
                $startOfWeek = date('Y-m-d 00:00:00', strtotime('monday this week'));
                $endOfWeek   = date('Y-m-d 23:59:59', strtotime('saturday this week'));

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