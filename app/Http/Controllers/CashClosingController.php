<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class CashClosingController extends Controller
{
    /**
     * Vista Principal con Pestañas para cada tipo de Cierre
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = (int) ($user->role_id ?? $user->type ?? 2);
        $userBranchId = $user->branch_id ?? null;
        $userBranchName = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        // Determinar si es Admin/Inventario (Tipo 1 o 3)
        $canAccessAll = in_array($userRole, [1, 3]);

        if ($canAccessAll) {
            $branches = Branch::all();
            $users = User::all();
        } else {
            // Tipo 2: Filtrar exclusivamente su sucursal y los empleados pertenecientes a ella
            $branches = Branch::where('id', $userBranchId)
                ->orWhere('name', $userBranchName)
                ->get();

            $users = User::where('branch_id', $userBranchId)
                ->orWhere('branch_name', $userBranchName)
                ->get();
        }

        return view('cash_closing.index', compact('branches', 'users', 'user', 'canAccessAll', 'userBranchName'));
    }

    /**
     * 1. Cierre Diario (Por Sucursal o Colectivo)
     */
    public function dailyReport(Request $request)
    {
        $request->validate([
            'fecha1' => 'required|date',
            'fecha2' => 'required|date',
        ]);

        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $branchesSelected = $request->get('sucursales', []);
        $sucursalSingle = $request->get('sucursal');

        // Detectar dinámicamente las columnas de la tabla 'products'
        $categoryCol = Schema::hasColumn('products', 'category_id') 
            ? 'products.category_id' 
            : (Schema::hasColumn('products', 'type') ? 'products.type' : DB::raw('4'));

        $typeStrCol = Schema::hasColumn('products', 'type') ? 'products.type' : DB::raw("''");

        // Consulta de ventas usando LEFT JOIN
        $querySales = DB::table('ventas')
            ->leftJoin('venta_producto', 'ventas.venta_id', '=', 'venta_producto.venta_id')
            ->leftJoin('products', 'venta_producto.producto_id', '=', 'products.id')
            ->whereBetween('ventas.venta_fecha', [$fecha1, $fecha2])
            ->where('ventas.venta_tipo', '!=', 3) // Excluir cancelados
            ->where('ventas.venta_tipo', '!=', 'CANCELADO')
            ->select(
                'ventas.venta_id',
                'ventas.venta_tipo',
                'ventas.venta_descuento',
                'ventas.venta_sucursal',
                'ventas.venta_total',
                'venta_producto.producto_id',
                'venta_producto.cantidad',
                'venta_producto.precio',
                DB::raw("{$categoryCol} as producto_categoria"),
                DB::raw("{$typeStrCol} as producto_tipo_str")
            );

        if (!empty($branchesSelected) && is_array($branchesSelected)) {
            $querySales->whereIn('ventas.venta_sucursal', $branchesSelected);
            $sucursalTitle = implode(', ', $branchesSelected);
        } elseif ($sucursalSingle && $sucursalSingle !== 'TODOS') {
            $querySales->where('ventas.venta_sucursal', $sucursalSingle);
            $sucursalTitle = $sucursalSingle;
        } else {
            $sucursalTitle = 'TODAS LAS SUCURSALES';
        }

        $salesData = $querySales->get();

        // Acumuladores
        $credito   = ['medicamento' => 0, 'calzado' => 0, 'consulta' => 0];
        $contado   = ['medicamento' => 0, 'calzado' => 0, 'consulta' => 0];
        $liquidado = ['medicamento' => 0, 'calzado' => 0, 'consulta' => 0];

        $descuentoContado = 0;
        $descuentoCredito = 0;

        // Rastreo de IDs procesados para no duplicar el descuento de la misma venta por cada producto
        $processedSalesForDiscount = [];

        foreach ($salesData as $item) {
            $monto = (float)($item->cantidad ?? 1) * (float)($item->precio ?? $item->venta_total ?? 0);
            $productId = (int)($item->producto_id ?? 0);
            $catId     = (int)($item->producto_categoria ?? 0);
            $tipoStr   = strtoupper($item->producto_tipo_str ?? '');

            // Clasificación de Categoria
            $cat = 'consulta';
            if ($productId !== 0) {
                if ($catId === 3 || $tipoStr === 'CALZADO' || $item->producto_categoria == 3) {
                    $cat = 'calzado';
                } elseif ($catId === 2 || $tipoStr === 'MEDICAMENTO' || $item->producto_categoria == 2) {
                    $cat = 'medicamento';
                } else {
                    $cat = 'consulta';
                }
            }

            $tipoVenta = (int)$item->venta_tipo;

            if ($tipoVenta === 2 || $tipoVenta === 'CREDITO') {
                $credito[$cat] += $monto;
            } elseif ($tipoVenta === 1 || $tipoVenta === 'CONTADO' || $tipoVenta === 'PAGADO') {
                $contado[$cat] += $monto;
            } elseif ($tipoVenta === 3) {
                $liquidado[$cat] += $monto;
            } else {
                $contado[$cat] += $monto;
            }

            // Sumar el descuento en pesos ($) de la cabecera de la venta solo una vez por venta
            if (!in_array($item->venta_id, $processedSalesForDiscount)) {
                $processedSalesForDiscount[] = $item->venta_id;
                $desc = (float)($item->venta_descuento ?? 0);

                if ($tipoVenta === 2 || $tipoVenta === 'CREDITO') {
                    $descuentoCredito += $desc;
                } else {
                    $descuentoContado += $desc;
                }
            }
        }

        // Consultar Abonos
        $queryAbonos = DB::table('abonos')->whereBetween('abono_fecha', [$fecha1, $fecha2]);
        if (!empty($branchesSelected) && is_array($branchesSelected)) {
            $queryAbonos->whereIn('abono_sucursal', $branchesSelected);
        } elseif ($sucursalSingle && $sucursalSingle !== 'TODOS') {
            $queryAbonos->where('abono_sucursal', $sucursalSingle);
        }
        $totalAbonos = $queryAbonos->sum('abono_cantidad') ?? 0;

        // Consultar Retiros / Gastos
        $queryRetiros = DB::table('retiros')
            ->whereBetween('fecha', [$fecha1, $fecha2])
            ->where(function($q) {
                $q->where('status', 'Activo')
                ->orWhere('status', 'activo');
            });

        if (!empty($branchesSelected) && is_array($branchesSelected)) {
            $queryRetiros->whereIn('sucursal', $branchesSelected);
        } elseif ($sucursalSingle && $sucursalSingle !== 'TODOS') {
            $queryRetiros->where('sucursal', $sucursalSingle);
        }

        $retiros = $queryRetiros->get();

        // Detectar el campo de monto de retiros
        $montoCol = Schema::hasColumn('retiros', 'cantidad') ? 'cantidad' : 'cantidad';

        // Gastos Proveedores (Tipo 1)
        $gastosProveedor = $retiros->where('tipo', 1)->sum($montoCol);
        $gastosConsultorio = $retiros->where('tipo', 2)->sum($montoCol);
        $gastosSueldos = $retiros->where('tipo', 3)->sum($montoCol);

        /*
        // Gastos Consultorio: Tipo 2 descartando los que digan 'SUELDO'
        $gastosConsultorio = $retiros->filter(function($r) {
            $concepto = strtoupper($r->concepto ?? $r->motivo ?? '');
            return (int)$r->tipo === 2 && !str_contains($concepto, 'SUELDO');
        })->sum($montoCol);

        // Gastos Sueldos: Tipo 3 O cualquier concepto que diga 'SUELDO'
        $gastosSueldos = $retiros->filter(function($r) {
            $concepto = strtoupper($r->concepto ?? $r->motivo ?? '');
            return (int)$r->tipo === 3 || str_contains($concepto, 'SUELDO');
        })->sum($montoCol);
        */
        // Total de Gastos
        $totalGastos = $retiros->sum($montoCol);

        // Subtotales antes de descuento
        $subtotalCredito   = array_sum($credito);
        $subtotalContado   = array_sum($contado);
        $totalLiquidado    = array_sum($liquidado);

        // Totales netos descontando el descuento de ventas
        $totalCredito = $subtotalCredito - $descuentoCredito;
        $totalContado = $subtotalContado - $descuentoContado;

        // FÓRMULAS DE TOTALES FINAL EN CAJA:
        // Efectivo en Caja = Contado Neto + Abonos Cobrados - Gastos Totales
        $totalEfectivoNeto       = ($totalContado + $totalAbonos) - $totalGastos;

        // Total Neto con Crédito = Efectivo Neto + Crédito Neto
        $totalEfectivoMasCredito = $totalEfectivoNeto + $totalCredito;

        $pdf = Pdf::loadView('cash_closing.daily', compact(
            'fecha1', 'fecha2', 'sucursalTitle',
            'credito', 'contado', 'liquidado',
            'subtotalCredito', 'subtotalContado',
            'totalCredito', 'totalContado', 'totalLiquidado',
            'totalAbonos', 'gastosProveedor', 'gastosConsultorio', 'gastosSueldos', 'totalGastos',
            'descuentoContado', 'descuentoCredito',
            'totalEfectivoNeto', 'totalEfectivoMasCredito', 'retiros'
        ))->setPaper([0, 0, 226.77, 720], 'portrait');

        return $pdf->stream("cierre_diario_{$fecha1}_{$fecha2}.pdf");
    }

    /**
     * 2. Cierre de Préstamos
     */
    public function loansReport(Request $request)
    {
        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $sucursal = $request->sucursal;

        $loans = DB::table('prestamos')
            ->whereBetween('pres_fecha', [$fecha1, $fecha2])
            ->when($sucursal && $sucursal !== 'TODOS', function ($q) use ($sucursal) {
                $q->where('sucursal', $sucursal);
            })->get();

        $loanPayments = DB::table('pago_prestamo')
            ->whereBetween('fechaPretamo', [$fecha1, $fecha2])
            ->when($sucursal && $sucursal !== 'TODOS', function ($q) use ($sucursal) {
                $q->where('sucursal', $sucursal);
            })->get();

        $totalPrestamos = $loans->sum('pres_costo');
        $totalAbonosPrestamo = $loanPayments->sum('monto');
        $restanteTotal = $totalPrestamos - $totalAbonosPrestamo;

        $pdf = Pdf::loadView('cash_closing.tickets.loans', compact(
            'fecha1', 'fecha2', 'sucursal', 'loans', 'loanPayments',
            'totalPrestamos', 'totalAbonosPrestamo', 'restanteTotal'
        ))->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->stream("reporte_prestamos_{$fecha1}.pdf");
    }

    /**
     * 3. Reporte de Comisiones por Empleado
     */
    public function commissionsReport(Request $request)
    {
        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $userId = (int) $request->usuario;
        $sucursal = $request->sucursal;

        // Obtener datos del usuario
        $user = User::findOrFail($userId);
        $employeeName = $user->name ?? $user->username ?? ("Empleado #" . $user->id);

        // Obtener comisión personalizada y teléfono desde la tabla 'employees'
        $employeeRecord = DB::table('employees')->where('user_id', $userId)->first();
        
        // Porcentaje dinámico para Consultas (si no existe, default 10%)
        $pctConsulta = $employeeRecord && $employeeRecord->commission_rate > 0 
            ? (float) $employeeRecord->commission_rate 
            : 10.0;

        // Número de teléfono para WhatsApp
        $employeePhone = $employeeRecord->phone ?? $user->phone ?? $user->telefono ?? '';
        // Limpiar caracteres no numéricos del teléfono
        $employeePhoneClean = preg_replace('/[^0-9]/', '', $employeePhone);

        // Detección de columna de categoría
        $typeColumn = Schema::hasColumn('products', 'category_id') 
            ? 'products.category_id' 
            : (Schema::hasColumn('products', 'type') ? 'products.type' : DB::raw('4'));

        $salesQuery = DB::table('ventas')
            ->join('venta_producto', 'ventas.venta_id', '=', 'venta_producto.venta_id')
            ->leftJoin('products', 'venta_producto.producto_id', '=', 'products.id')
            ->whereBetween('ventas.venta_fecha', [$fecha1, $fecha2])
            ->where('ventas.venta_idempleado', $userId)
            ->where('ventas.venta_tipo', '!=', 3) // Excluir canceladas
            ->select(
                'ventas.venta_id',
                'ventas.venta_idempleado',
                'venta_producto.producto_id',
                'venta_producto.cantidad',
                'venta_producto.precio',
                'venta_producto.nombre as producto_nombre',
                DB::raw("{$typeColumn} as producto_categoria"),
                DB::raw("products.type as producto_tipo")
            );

        if ($sucursal && $sucursal !== 'TODOS') {
            $salesQuery->where('ventas.venta_sucursal', $sucursal);
        }

        $items = $salesQuery->get();

        $totalCalzado     = 0;
        $totalMedicamento = 0;
        $totalConsulta    = 0;
        $totalGeneral     = 0;

        foreach ($items as $item) {
            $subtotal = (float)($item->cantidad * $item->precio);
            $productId = (int)($item->producto_id ?? 0);

            if ($productId === 0) {
                $totalConsulta += $subtotal;
            } else {
                $catId = (int)($item->producto_categoria ?? 0);
                $tipoStr = strtoupper($item->producto_tipo ?? '');

                if ($catId === 3 || $tipoStr === 'CALZADO') {
                    $totalCalzado += $subtotal;
                } elseif ($catId === 2 || $tipoStr === 'MEDICAMENTO') {
                    $totalMedicamento += $subtotal;
                } else {
                    $totalGeneral += $subtotal;
                }
            }
        }

        // Porcentajes
        $pctGeneral     = 10;
        $pctCalzado     = 10;
        $pctMedicamento = 10;

        // Cálculo de comisiones
        $comisionGeneral     = ($totalGeneral * $pctGeneral) / 100;
        $comisionCalzado     = ($totalCalzado * $pctCalzado) / 100;
        $comisionMedicamento = ($totalMedicamento * $pctMedicamento) / 100;
        $comisionConsulta    = ($totalConsulta * $pctConsulta) / 100;

        $totalVentas     = $totalCalzado + $totalMedicamento + $totalConsulta + $totalGeneral;
        $totalComisiones = $comisionCalzado + $comisionMedicamento + $comisionConsulta + $comisionGeneral;

        return view('cash_closing.commissions_view', compact(
            'userId', 'employeeName', 'employeePhoneClean', 'fecha1', 'fecha2', 'sucursal',
            'totalCalzado', 'totalMedicamento', 'totalConsulta', 'totalGeneral', 'totalVentas',
            'comisionCalzado', 'comisionMedicamento', 'comisionConsulta', 'comisionGeneral',
            'totalComisiones', 'pctGeneral', 'pctCalzado', 'pctMedicamento', 'pctConsulta'
        ));
    }

    /**
     * 4. Generación de PDF de Comisiones sin Venta Total (Para Imprimir o Enviar por WhatsApp)
     */
    public function commissionsPdf(Request $request)
    {
        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $userId = (int) $request->usuario;
        $sucursal = $request->sucursal;

        $user = User::findOrFail($userId);
        $employeeName = $user->name ?? $user->username ?? ("Empleado #" . $user->id);

        $employeeRecord = DB::table('employees')->where('user_id', $userId)->first();
        $pctConsulta = $employeeRecord && $employeeRecord->commission_rate > 0 
            ? (float) $employeeRecord->commission_rate 
            : 10.0;

        $typeColumn = Schema::hasColumn('products', 'category_id') 
            ? 'products.category_id' 
            : (Schema::hasColumn('products', 'type') ? 'products.type' : DB::raw('4'));

        $salesQuery = DB::table('ventas')
            ->join('venta_producto', 'ventas.venta_id', '=', 'venta_producto.venta_id')
            ->leftJoin('products', 'venta_producto.producto_id', '=', 'products.id')
            ->whereBetween('ventas.venta_fecha', [$fecha1, $fecha2])
            ->where('ventas.venta_idempleado', $userId)
            ->where('ventas.venta_tipo', '!=', 3)
            ->select(
                'venta_producto.producto_id',
                'venta_producto.cantidad',
                'venta_producto.precio',
                DB::raw("{$typeColumn} as producto_categoria"),
                DB::raw("products.type as producto_tipo")
            );

        if ($sucursal && $sucursal !== 'TODOS') {
            $salesQuery->where('ventas.venta_sucursal', $sucursal);
        }

        $items = $salesQuery->get();

        $totalCalzado = 0; $totalMedicamento = 0; $totalConsulta = 0; $totalGeneral = 0;

        foreach ($items as $item) {
            $subtotal = (float)($item->cantidad * $item->precio);
            $productId = (int)($item->producto_id ?? 0);

            if ($productId === 0) {
                $totalConsulta += $subtotal;
            } else {
                $catId = (int)($item->producto_categoria ?? 0);
                $tipoStr = strtoupper($item->producto_tipo ?? '');

                if ($catId === 3 || $tipoStr === 'CALZADO') $totalCalzado += $subtotal;
                elseif ($catId === 2 || $tipoStr === 'MEDICAMENTO') $totalMedicamento += $subtotal;
                else $totalGeneral += $subtotal;
            }
        }

        $pctGeneral = 10; $pctCalzado = 10; $pctMedicamento = 10;

        $comisionGeneral     = ($totalGeneral * $pctGeneral) / 100;
        $comisionCalzado     = ($totalCalzado * $pctCalzado) / 100;
        $comisionMedicamento = ($totalMedicamento * $pctMedicamento) / 100;
        $comisionConsulta    = ($totalConsulta * $pctConsulta) / 100;

        $totalComisiones = $comisionCalzado + $comisionMedicamento + $comisionConsulta + $comisionGeneral;

        $pdf = Pdf::loadView('cash_closing.pdf', compact(
            'employeeName', 'fecha1', 'fecha2', 'sucursal',
            'comisionCalzado', 'comisionMedicamento', 'comisionConsulta', 'comisionGeneral',
            'totalComisiones', 'pctGeneral', 'pctCalzado', 'pctMedicamento', 'pctConsulta'
        ));

        return $pdf->stream("Comisiones_{$employeeName}_{$fecha1}.pdf");
    }
}