<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\CreditPayment;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesConsultationController extends Controller
{
    /**
     * Consola Principal de Consulta de Ventas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $branches = Branch::all();
        $tab = $request->get('tab', 'general');
        $search = trim($request->get('search'));
        $selectedBranch = $request->get('branch');
        $fecha1 = $request->get('fecha1', date('Y-m-01'));
        $fecha2 = $request->get('fecha2', date('Y-m-d'));

        if (!in_array($userRole, [0, 3])) {
            $selectedBranch = $userBranch;
        }

        $summary = [
            'medicamento_credito' => 0,
            'consulta_credito'    => 0,
            'total_medicamentos'  => 0,
            'total_consultas'     => 0,
            'descuentos'          => 0,
            'gran_total'          => 0,
        ];

        if ($tab !== 'abonos') {
            $query = Sale::with(['details', 'payments']);

            if (!empty($fecha1) && !empty($fecha2)) {
                $query->whereBetween('venta_fecha', [$fecha1, $fecha2]);
            }

            if ($tab === 'facturadas') {
                $query->where(function($q) {
                    $q->where('venta_factura', 'SI')
                      ->orWhere('venta_factura', 'si')
                      ->orWhere('venta_factura', '1')
                      ->orWhere('venta_factura', 1);
                })->where('venta_tipo', '!=', 3);
            } elseif ($tab === 'canceladas') {
                $query->where(function($q) {
                    $q->where('venta_tipo', 3)
                      ->orWhere('venta_tipo', 'CANCELADO')
                      ->orWhere('venta_tipo', '3');
                });
            } else {
                $query->where('venta_tipo', '!=', 3);
            }

            if (!empty($selectedBranch) && $selectedBranch !== 'TODOS') {
                $query->where('venta_sucursal', $selectedBranch);
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('venta_id', 'LIKE', "%{$search}%")
                      ->orWhere('nombre_cliente', 'LIKE', "%{$search}%")
                      ->orWhere('venta_tipopago', 'LIKE', "%{$search}%");
                });
            }

            $allFilteredSales = (clone $query)->get();

            foreach ($allFilteredSales as $sale) {
                $isCredito = in_array($sale->venta_tipo, [2, '2', 'CREDITO']) || str_contains(strtoupper($sale->venta_tipopago ?? ''), 'CREDITO');
                $descuentoVenta = (float)($sale->venta_descuento ?? 0);
                $summary['descuentos'] += $descuentoVenta;

                foreach ($sale->details as $d) {
                    $subtotal = $d->cantidad * $d->precio;
                    $esConsulta = str_contains(strtoupper($d->nombre ?? ''), 'CONSULTA') || str_contains(strtoupper($d->nombre ?? ''), 'SERVICIO');

                    if ($esConsulta) {
                        $summary['total_consultas'] += $subtotal;
                        if ($isCredito) $summary['consulta_credito'] += $subtotal;
                    } else {
                        $summary['total_medicamentos'] += $subtotal;
                        if ($isCredito) $summary['medicamento_credito'] += $subtotal;
                    }
                }

                $summary['gran_total'] += (float)$sale->venta_total;
            }

            $records = $query->orderBy('venta_id', 'desc')
                             ->paginate(10)
                             ->appends($request->all());

        } else {
            $dateColAbono = Schema::hasColumn('abonos', 'abono_fecha') ? 'abono_fecha' : 'created_at';
            $query = CreditPayment::query();

            if (!empty($fecha1) && !empty($fecha2)) {
                $query->whereBetween($dateColAbono, [$fecha1 . ' 00:00:00', $fecha2 . ' 23:59:59']);
            }

            if (!empty($selectedBranch) && $selectedBranch !== 'TODOS') {
                if (Schema::hasColumn('abonos', 'abono_sucursal')) {
                    $query->where('abono_sucursal', $selectedBranch);
                }
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('venta_id', 'LIKE', "%{$search}%")
                      ->orWhere('abono_cantidad', 'LIKE', "%{$search}%");
                });
            }

            $summary['gran_total'] = (float)(clone $query)->sum('abono_cantidad');

            $records = $query->orderBy('abono_id', 'desc')
                             ->paginate(10)
                             ->appends($request->all());
        }

        return view('sales_consultation.index', compact(
            'records', 'tab', 'branches', 'selectedBranch', 'search', 
            'fecha1', 'fecha2', 'userRole', 'userBranch', 'summary'
        ));
    }

    /**
     * Cancelar/Borrar Venta (Borrado Lógico: venta_tipo = 3)
     */
    public function destroySale($id)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;

        if (!in_array($userRole, [0, 3])) {
            return redirect()->back()->with('error', 'No tienes permisos para cancelar o borrar ventas.');
        }

        $sale = Sale::findOrFail($id);
        $sale->update(['venta_tipo' => 3]); // 3 = Cancelado

        return redirect()->back()->with('success', "Venta #{$id} cancelada correctamente.");
    }

    /**
     * Borrar Abono y Revertir Saldo de la Venta a Crédito
     */
    public function destroyAbono($id)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;

        if (!in_array($userRole, [0, 3])) {
            return redirect()->back()->with('error', 'No tienes permisos para eliminar abonos.');
        }

        DB::transaction(function () use ($id) {
            $abono = CreditPayment::findOrFail($id);
            $sale = Sale::find($abono->venta_id);

            if ($sale) {
                $montoAbono = (float) $abono->abono_cantidad;
                $nuevoAbonoAcumulado = max(0, $sale->venta_abono - $montoAbono);
                $nuevoRestante = $sale->venta_total - $nuevoAbonoAcumulado;

                // Si aún se debe algo, el tipo regresa a 2 (CRÉDITO PENDIENTE)
                $nuevoEstatus = ($nuevoRestante > 0) ? 2 : $sale->venta_tipo;

                $sale->update([
                    'venta_abono'    => $nuevoAbonoAcumulado,
                    'venta_restante' => max(0, $nuevoRestante),
                    'venta_tipo'     => $nuevoEstatus,
                ]);
            }

            $abono->delete();
        });

        return redirect()->back()->with('success', "Abono #{$id} eliminado y saldo recalculado.");
    }
}