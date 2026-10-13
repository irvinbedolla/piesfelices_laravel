<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\CreditPayment;
use App\Models\Branch;
use App\Models\User;
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
        $userRole = $user->role_id ?? $user->type ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $branches = Branch::all();
        $users = User::orderBy('username', 'asc')->get();

        $tab = $request->get('tab', 'general');
        $search = trim($request->get('search'));
        
        $selectedBranch = $request->get('branch', $request->get('sucursal'));
        $selectedUser   = $request->get('user_id', $request->get('empleado'));

        $isAdmin = in_array($userRole, [0, 1, 3]);

        if (!$isAdmin) {
            $selectedBranch = $userBranch;
        }

        $summary = [
            'contado_calzado'     => 0,
            'contado_medicamento' => 0,
            'contado_general'     => 0,
            'contado_consulta'    => 0,
            'total_contado'       => 0,

            'credito_calzado'     => 0,
            'credito_medicamento' => 0,
            'credito_general'     => 0,
            'credito_consulta'    => 0,
            'total_credito'       => 0,

            'total_calzado'       => 0,
            'total_medicamento'   => 0,
            'total_general'       => 0,
            'total_consulta'      => 0,

            'descuentos'          => 0,
            'gran_total'          => 0,
        ];

        if ($tab !== 'abonos') {
            // Cargar relaciones de detalles, producto, pagos/abonos y vendedor
            $query = Sale::with(['details.product', 'payments', 'seller']);

            $fecha1 = $request->get('fecha1', date('Y-m-01'));
            $fecha2 = $request->get('fecha2', date('Y-m-d'));

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

            if (!empty($selectedUser) && $selectedUser !== 'TODOS') {
                $query->where('venta_idempleado', $selectedUser);
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('venta_id', 'LIKE', "%{$search}%")
                    ->orWhere('nombre_cliente', 'LIKE', "%{$search}%")
                    ->orWhere('venta_tipopago', 'LIKE', "%{$search}%");
                });
            }

            // 1. CALCULAR MÓDULO DE RESUMEN GLOBAL (Sobre todos los registros filtrados)
            $allFilteredSales = (clone $query)->get();

            foreach ($allFilteredSales as $sale) {
                $isCredito = in_array($sale->venta_tipo, [2, '2', 'CREDITO']) 
                    || str_contains(strtoupper($sale->venta_tipopago ?? ''), 'CREDITO');

                $summary['descuentos'] += (float)($sale->venta_descuento ?? 0);

                foreach ($sale->details as $d) {
                    $subtotal = (float)($d->cantidad * $d->precio);
                    $productId = (int)($d->producto_id ?? 0);

                    if ($productId === 0) {
                        if ($isCredito) $summary['credito_consulta'] += $subtotal;
                        else $summary['contado_consulta'] += $subtotal;
                        $summary['total_consulta'] += $subtotal;
                    } else {
                        $categoryId = (int)($d->product?->category_id ?? 0);
                        $productType = strtoupper($d->product?->type ?? '');

                        if ($categoryId === 3 || $productType === 'CALZADO') {
                            if ($isCredito) $summary['credito_calzado'] += $subtotal;
                            else $summary['contado_calzado'] += $subtotal;
                            $summary['total_calzado'] += $subtotal;
                        } elseif ($categoryId === 2 || $productType === 'MEDICAMENTO') {
                            if ($isCredito) $summary['credito_medicamento'] += $subtotal;
                            else $summary['contado_medicamento'] += $subtotal;
                            $summary['total_medicamento'] += $subtotal;
                        } else {
                            if ($isCredito) $summary['credito_general'] += $subtotal;
                            else $summary['contado_general'] += $subtotal;
                            $summary['total_general'] += $subtotal;
                        }
                    }
                }

                $summary['gran_total'] += (float)$sale->venta_total;
            }

            $summary['total_contado'] = $summary['contado_calzado'] + $summary['contado_medicamento'] + $summary['contado_general'] + $summary['contado_consulta'];
            $summary['total_credito'] = $summary['credito_calzado'] + $summary['credito_medicamento'] + $summary['credito_general'] + $summary['credito_consulta'];

            // 2. PAGINACIÓN Y CÁLCULO DE CAMPOS POR FILA PARA LA TABLA
            $records = $query->orderBy('venta_id', 'desc')
                            ->paginate(10)
                            ->appends($request->all());

            // Asignar los desgloses de subtotales a cada registro paginado
            foreach ($records as $sale) {
                $rowMed = 0;
                $rowCon = 0;
                $rowCal = 0;
                $rowGen = 0;

                foreach ($sale->details as $d) {
                    $sub = (float)($d->cantidad * $d->precio);
                    $pId = (int)($d->producto_id ?? $d->product_id ?? 0);

                    // REGLA 1: Si producto_id == 0 -> CONSULTA
                    if ($pId === 0) {
                        $rowCon += $sub;
                    } 
                    // REGLA 2: Si es producto físico -> Evaluar por Categoría o Tipo
                    else {
                        // Intentar obtener la categoría o el tipo del producto cargado
                        $cId = (int)($d->product?->category_id ?? 0);
                        $pType = strtoupper($d->product?->type ?? $d->tipo ?? '');

                        if ($cId === 3 || $pType === 'CALZADO') {
                            $rowCal += $sub;
                        } elseif ($cId === 2 || $pType === 'MEDICAMENTO') {
                            $rowMed += $sub;
                        } else {
                            // Categoría 4 o GENERAL
                            $rowGen += $sub;
                        }
                    }
                }

                // Asignar variables directamente al objeto de la venta
                $sale->sum_medicamento = $rowMed;
                $sale->sum_consulta    = $rowCon;
                $sale->sum_calzado     = $rowCal;
                $sale->sum_general     = $rowGen;

                // Obtener el último abono
                $lastPayment = $sale->payments?->last();
                $sale->ultimo_abono = $lastPayment ? ($lastPayment->abono_cantidad ?? $lastPayment->monto ?? 0) : 0;
            }

        } else {
            $fecha1 = $request->get('fecha1', date('Y-m-01'));
            $fecha2 = $request->get('fecha2', date('Y-m-d'));

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

            if (!empty($selectedUser) && $selectedUser !== 'TODOS') {
                if (Schema::hasColumn('abonos', 'abono_usuario') || Schema::hasColumn('abonos', 'user_id')) {
                    $userCol = Schema::hasColumn('abonos', 'abono_usuario') ? 'abono_usuario' : 'user_id';
                    $query->where($userCol, $selectedUser);
                }
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('abono_venta', 'LIKE', "%{$search}%")
                    ->orWhere('abono_cantidad', 'LIKE', "%{$search}%");
                });
            }

            $summary['gran_total'] = (float)(clone $query)->sum('abono_cantidad');

            $records = $query->orderBy('abono_id', 'desc')
                            ->paginate(10)
                            ->appends($request->all());
        }

        return view('sales_consultation.index', compact(
            'records', 'tab', 'branches', 'users', 'selectedBranch', 'selectedUser', 
            'search', 'fecha1', 'fecha2', 'userRole', 'userBranch', 'summary', 'isAdmin'
        ));
    }

    /**
     * Cancelar/Borrar Venta
     */
    public function destroySale($id)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? $user->type ?? 2;

        if (!in_array($userRole, [0, 1, 3])) {
            return redirect()->back()->with('error', 'No tienes permisos para cancelar o borrar ventas.');
        }

        $sale = Sale::findOrFail($id);
        $sale->update(['venta_tipo' => 3]);

        return redirect()->back()->with('success', "Venta #{$id} cancelada correctamente.");
    }

    /**
     * Borrar Abono y Revertir Saldo
     */
    public function destroyAbono($id)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? $user->type ?? 2;

        if (!in_array($userRole, [0, 1, 3])) {
            return redirect()->back()->with('error', 'No tienes permisos para eliminar abonos.');
        }

        DB::transaction(function () use ($id) {
            $abono = CreditPayment::findOrFail($id);
            $saleId = $abono->abono_venta ?? $abono->venta_id;
            $sale = Sale::find($saleId);

            if ($sale) {
                $montoAbono = (float) $abono->abono_cantidad;
                $nuevoAbonoAcumulado = max(0, $sale->venta_abono - $montoAbono);
                $nuevoRestante = $sale->venta_total - $nuevoAbonoAcumulado;

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