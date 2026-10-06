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
        $branches = Branch::all();
        $users = User::all();

        return view('cash_closing.index', compact('branches', 'users', 'user'));
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

        // Detectar si la columna en 'products' se llama 'type' o 'tipo'
        $typeColumn = Schema::hasColumn('products', 'type') ? 'products.type' : (Schema::hasColumn('products', 'tipo') ? 'products.tipo' : DB::raw('1'));

        $querySales = DB::table('ventas')
            ->join('venta_producto', 'ventas.venta_id', '=', 'venta_producto.venta_id')
            ->leftJoin('products', 'venta_producto.producto_id', '=', 'products.id')
            ->whereBetween('ventas.venta_fecha', [$fecha1, $fecha2])
            ->select(
                'ventas.venta_tipo',
                'ventas.venta_descuento',
                'ventas.venta_sucursal',
                'venta_producto.producto_id',
                'venta_producto.cantidad',
                'venta_producto.precio',
                DB::raw("{$typeColumn} as producto_tipo")
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

        // Totales desglosados
        $credito = ['medicamento' => 0, 'calzado' => 0, 'consulta' => 0, 'descuento' => 0];
        $contado = ['medicamento' => 0, 'calzado' => 0, 'consulta' => 0, 'descuento' => 0];
        $liquidado = ['medicamento' => 0, 'calzado' => 0, 'consulta' => 0, 'descuento' => 0];

        foreach ($salesData as $item) {
            $monto = $item->cantidad * $item->precio;
            $tipoProd = $item->producto_tipo; // 3: Calzado, 4: Medicamento, 0/null: Consulta

            // Categoría del Producto/Servicio
            $cat = 'consulta';
            if ($item->producto_id != 0) {
                if ($tipoProd == 3) $cat = 'calzado';
                elseif ($tipoProd == 4) $cat = 'medicamento';
            }

            // Tipo de Venta (1: Contado, 2: Crédito, 3: Liquidada)
            if ($item->venta_tipo == 2) {
                $credito[$cat] += $monto;
            } elseif ($item->venta_tipo == 1) {
                $contado[$cat] += $monto;
            } elseif ($item->venta_tipo == 3) {
                $liquidado[$cat] += $monto;
            }
        }

        // Consultar Abonos en el Periodo
        $queryAbonos = DB::table('abonos')->whereBetween('abono_fecha', [$fecha1, $fecha2]);
        if (!empty($branchesSelected)) {
            $queryAbonos->whereIn('abono_sucursal', $branchesSelected);
        } elseif ($sucursalSingle && $sucursalSingle !== 'TODOS') {
            $queryAbonos->where('abono_sucursal', $sucursalSingle);
        }
        $totalAbonos = $queryAbonos->sum('abono_cantidad') ?? 0;

        // Consultar Retiros / Gastos por Tipo (1: Proveedor, 2: Consultorio, 3: Sueldos)
        $queryRetiros = DB::table('retiros')
            ->whereBetween('fecha', [$fecha1, $fecha2])
            ->where('status', 'Activo');

        if (!empty($branchesSelected)) {
            $queryRetiros->whereIn('sucursal', $branchesSelected);
        } elseif ($sucursalSingle && $sucursalSingle !== 'TODOS') {
            $queryRetiros->where('sucursal', $sucursalSingle);
        }

        $retiros = $queryRetiros->get();
        $gastosProveedor = $retiros->where('tipo', 1)->sum('cantida');
        $gastosConsultorio = $retiros->where('tipo', 2)->sum('cantida');
        $gastosSueldos = $retiros->where('tipo', 3)->sum('cantida');
        $totalGastos = $retiros->sum('cantida');

        // Totales Generales
        $totalCredito = array_sum($credito);
        $totalContado = array_sum($contado);
        $totalLiquidado = array_sum($liquidado);

        $totalEfectivoNeto = ($totalContado + $totalAbonos) - $totalGastos;
        $totalEfectivoMasCredito = $totalEfectivoNeto + $totalCredito;

        $pdf = Pdf::loadView('cash_closing.daily', compact(
            'fecha1', 'fecha2', 'sucursalTitle',
            'credito', 'contado', 'liquidado',
            'totalCredito', 'totalContado', 'totalLiquidado',
            'totalAbonos', 'gastosProveedor', 'gastosConsultorio', 'gastosSueldos', 'totalGastos',
            'totalEfectivoNeto', 'totalEfectivoMasCredito'
        ))->setPaper([0, 0, 226.77, 650], 'portrait');

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
     * 3. Reporte de Comisiones por Empleado (Filtro Estricto por venta_idempleado + JOIN con venta_producto)
     */
    public function commissionsReport(Request $request)
    {
        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;
        $userId = (int) $request->usuario;
        $sucursal = $request->sucursal;

        // Obtener datos del empleado desde la tabla de usuarios
        $user = User::findOrFail($userId);
        $employeeName = $user->name ?? $user->username ?? ("Empleado #" . $user->id);
        $typeColumn = Schema::hasColumn('products', 'type') ? 'products.type' : (Schema::hasColumn('products', 'tipo') ? 'products.tipo' : DB::raw('1'));

        // Detectar columna de categoría/tipo en la tabla 'products'
        $totalCalzado = Schema::hasColumn('products', 'type') 
            ? 'products.type' 
            : (Schema::hasColumn('products', 'tipo') ? 'products.tipo' : DB::raw('1'));

        // Consulta SQL con JOIN estricto entre ventas, venta_producto y products
        $salesQuery = DB::table('ventas')
            ->join('venta_producto', 'ventas.venta_id', '=', 'venta_producto.venta_id')
            ->leftJoin('products', 'venta_producto.producto_id', '=', 'products.id')
            ->whereBetween('ventas.venta_fecha', [$fecha1, $fecha2])
            ->where('ventas.venta_idempleado', $userId) // Filtro por el empleado solicitado
            ->select(
                'ventas.venta_id',
                'ventas.venta_idempleado',
                'venta_producto.producto_id',
                'venta_producto.cantidad',
                'venta_producto.precio',
                'venta_producto.nombre as producto_nombre',
                DB::raw("{$typeColumn} as producto_tipo")
            );

        // Filtrar por sucursal si no se eligió "TODOS"
        if ($sucursal && $sucursal !== 'TODOS') {
            $salesQuery->where('ventas.venta_sucursal', $sucursal);
        }

        $items = $salesQuery->get();

        // Acumuladores de montos por rubro
        $totalCalzado     = 0;
        $totalMedicamento = 0;
        $totalConsulta    = 0;
        $totalGeneral     = 0;

        foreach ($items as $item) {
            $subtotal = $item->cantidad * $item->precio;

            // Regla 1: Si producto_id es 0, es un Servicio / Consulta podológica
            if ($item->producto_id == 0) {
                $totalConsulta += $subtotal;
            } 
            // Regla 2: Si producto_id > 0, evaluar la categoría del producto
            else {
                if ($item->producto_tipo == 3) {
                    $totalCalzado += $subtotal;      // Calzado
                } elseif ($item->producto_tipo == 4) {
                    $totalMedicamento += $subtotal;  // Medicamento
                } else {
                    $totalGeneral += $subtotal;      // Productos Generales (Accesorios, Cotonetes, etc.)
                }
            }
        }

        // Porcentaje uniforme del 10%
        $pct = 10;

        $comisionCalzado     = ($totalCalzado * $pct) / 100;
        $comisionMedicamento = ($totalMedicamento * $pct) / 100;
        $comisionConsulta    = ($totalConsulta * $pct) / 100;
        $comisionGeneral     = ($totalGeneral * $pct) / 100;

        $totalVentas     = $totalCalzado + $totalMedicamento + $totalConsulta + $totalGeneral;
        $totalComisiones = $comisionCalzado + $comisionMedicamento + $comisionConsulta + $comisionGeneral;

        return view('cash_closing.commissions_view', compact(
            'employeeName', 'fecha1', 'fecha2', 'sucursal',
            'totalCalzado', 'totalMedicamento', 'totalConsulta', 'totalGeneral', 'totalVentas',
            'comisionCalzado', 'comisionMedicamento', 'comisionConsulta', 'comisionGeneral',
            'totalComisiones', 'pct'
        ));
    }
}