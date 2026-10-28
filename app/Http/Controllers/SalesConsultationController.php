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
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\Log;



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

        $isAdmin = in_array($userRole, [1, 2]);

        if (!$isAdmin) {
            $selectedBranch = $userBranch;
        }

        // --- ACUMULADORES SIN LA CATEGORÍA 'GENERAL' ---
        $summary = [
            'contado_calzado'     => 0,
            'contado_medicamento' => 0,
            'contado_consulta'    => 0,
            'total_contado'       => 0,

            'credito_calzado'     => 0,
            'credito_medicamento' => 0,
            'credito_consulta'    => 0,
            'total_credito'       => 0,

            'total_calzado'       => 0,
            'total_medicamento'   => 0,
            'total_consulta'      => 0,

            // Acumuladores de forma de pago
            'pago_efectivo'       => 0,
            'pago_tarjeta_credito'=> 0,
            'pago_tarjeta_debito' => 0,
            'pago_transferencia'  => 0,
            'pago_otros'          => 0,

            'descuentos'          => 0,
            'gran_total'          => 0,
        ];

        if ($tab !== 'abonos') {
            $query = Sale::with(['details.product', 'payments', 'seller']);
            $query->where('status', "!=", 'Pendiente');
            
            $fecha1 = $request->get('fecha1', date('Y-m-d'));
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
                // "Ventas en General": Excluir canceladas y excluidas las facturadas
                $query->where('venta_tipo', '!=', 3)->Where('venta_factura', 'NO');
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

            // 1. CÁLCULO DEL RESUMEN GLOBAL
            $allFilteredSales = (clone $query)->get();

            foreach ($allFilteredSales as $sale) {
                $isCredito = in_array($sale->venta_tipo, [2, '2', 'CREDITO']) 
                    || str_contains(strtoupper($sale->venta_tipopago ?? ''), 'CREDITO');

                $summary['descuentos'] += (float)($sale->venta_descuento ?? 0);
                $montoVenta = (float)$sale->venta_total;

                $tipoPago = strtoupper(trim($sale->venta_tipopago ?? 'EFECTIVO'));

                if (str_contains($tipoPago, 'DEBITO') || str_contains($tipoPago, 'TD')) {
                    $summary['pago_tarjeta_debito'] += $montoVenta;
                } elseif (str_contains($tipoPago, 'CREDITO') || str_contains($tipoPago, 'TARJETA') || str_contains($tipoPago, 'TC')) {
                    $summary['pago_tarjeta_credito'] += $montoVenta;
                } elseif (str_contains($tipoPago, 'TRANSFERENCIA') || str_contains($tipoPago, 'TRANS')) {
                    $summary['pago_transferencia'] += $montoVenta;
                } elseif (str_contains($tipoPago, 'EFECTIVO')) {
                    $summary['pago_efectivo'] += $montoVenta;
                } else {
                    $summary['pago_otros'] += $montoVenta;
                }

                foreach ($sale->details as $d) {
                    $subtotal = (float)($d->cantidad * $d->precio);
                    $productId = (int)($d->producto_id ?? 0);

                    if ($productId === 0) { // Consulta o servicio
                        if ($isCredito) $summary['credito_consulta'] += $subtotal;
                        else $summary['contado_consulta'] += $subtotal;
                        $summary['total_consulta'] += $subtotal;
                    } else {
                        $categoryId = (int)($d->product?->category_id ?? 0);
                        $productType = strtoupper($d->product?->type ?? '');

                        if ($categoryId === 3 || $productType === 'CALZADO') {
                            if ($isCredito) {
                                $summary['credito_calzado'] += $subtotal;
                            } else {
                                $summary['contado_calzado'] += $subtotal;
                            }
                            $summary['total_calzado'] += $subtotal;
                        } else {
                            // TODO LO DEMÁS PASA A SER MEDICAMENTO
                            if ($isCredito) {
                                $summary['credito_medicamento'] += $subtotal;
                            } else {
                                $summary['contado_medicamento'] += $subtotal;
                            }
                            $summary['total_medicamento'] += $subtotal;
                        }
                    }
                }

                $summary['gran_total'] += $montoVenta;
            }

            $summary['total_contado'] = $summary['contado_calzado'] + $summary['contado_medicamento'] + $summary['contado_consulta'];
            $summary['total_credito'] = $summary['credito_calzado'] + $summary['credito_medicamento'] + $summary['credito_consulta'];

            // 2. CÁLCULO POR FILA DE TABLA
            $records = $query->orderBy('venta_id', 'desc')
                            ->paginate(10)
                            ->appends($request->all());

            foreach ($records as $sale) {
                $rowMed = 0; $rowCon = 0; $rowCal = 0;

                foreach ($sale->details as $d) {
                    $sub = (float)($d->cantidad * $d->precio);
                    $pId = (int)($d->producto_id ?? $d->product_id ?? 0);

                    if ($pId === 0) {
                        $rowCon += $sub; // Consulta
                    } else {
                        $cId = (int)($d->product?->category_id ?? 0);
                        $pType = strtoupper($d->product?->type ?? $d->tipo ?? '');

                        if ($cId === 3 || $pType === 'CALZADO') {
                            $rowCal += $sub;
                        } else {
                            // TODOS los artículos pasan a Medicamentos
                            $rowMed += $sub;
                        }
                    }
                }

                $sale->sum_medicamento = $rowMed;
                $sale->sum_consulta    = $rowCon;
                $sale->sum_calzado     = $rowCal;

                $lastPayment = $sale->payments?->last();
                $sale->ultimo_abono = $lastPayment ? ($lastPayment->abono_cantidad ?? $lastPayment->monto ?? 0) : 0;
            }

        } else {
            $fecha1 = $request->get('fecha1', date('Y-m-d'));
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
        $userRole = (int) ($user->role_id ?? $user->tipo_seccion ?? $user->type ?? 2);

        // 1. Validar permisos de usuario
        if (!in_array($userRole, [1, 2, 3])) {
            return redirect()->back()->with('error', 'No tienes permisos para cancelar o borrar ventas.');
        }

        try {
            DB::transaction(function () use ($id, $user) {
                // Buscar la venta con sus ítems para iterarlos
                $sale = Sale::with('items')->findOrFail($id);

                // Evitar re-cancelar si ya está cancelada
                if ($sale->venta_tipo == 3 || strtoupper($sale->status) === 'CANCELADO') {
                    throw new \Exception("La Venta #{$id} ya se encuentra cancelada.");
                }

                $sellerUser = User::find($sale->venta_idempleado) ?? $user;
                
                // Determinar la sucursal correcta donde descontar o sumar el inventario
                $branchId = $sellerUser->branch_id 
                    ?? $sellerUser->sucursal_id 
                    ?? Branch::where('name', $sale->venta_sucursal)->value('id') 
                    ?? 1;

                // 2. SOLO REINTEGRAR INVENTARIO SI LA VENTA YA ESTABA PAGADA
                if (strtoupper($sale->status) === 'PAGADO') {
                    foreach ($sale->items as $item) {
                        $pId = (int) ($item->producto_id ?? $item->product_id ?? 0);
                        if ($pId <= 0) continue; // Si es un servicio, se omite

                        // Bloquear la fila pivote para consistencia de stock
                        $pivot = DB::table('branch_product')
                            ->where('product_id', $pId)
                            ->where('branch_id', $branchId)
                            ->lockForUpdate()
                            ->first();

                        $prevStock = $pivot ? $pivot->stock_current : 0;
                        $newStock  = $prevStock + $item->cantidad; // Devolver existencias

                        // Reintegrar stock a la sucursal
                        DB::table('branch_product')
                            ->where('product_id', $pId)
                            ->where('branch_id', $branchId)
                            ->update([
                                'stock_current' => $newStock,
                                'updated_at'    => now()
                            ]);

                        // Registrar el movimiento de devolución en la bitácora
                        try {
                            InventoryMovement::create([
                                'product_id'     => $pId,
                                'user_id'        => $user->id ?? auth()->id() ?? 1,
                                'branch_id'      => $branchId,
                                'type'           => 'ENTRADA', // Devuelve inventario
                                'quantity'       => $item->cantidad,
                                'previous_stock' => $prevStock,
                                'new_stock'      => $newStock,
                                'reference'      => "Cancelación Venta #{$sale->venta_id}",
                                'notes'          => "Cancelación desde consulta de ventas",
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Error al registrar movimiento en destroySale Venta #{$sale->venta_id}: " . $e->getMessage());
                        }
                    }
                }

                // 3. ACTUALIZAR TANTO EL VENTA_TIPO COMO EL STATUS
                $updatePayload = [
                    'venta_tipo' => 3,             // 3 = Cancelado
                    'status'     => 'CANCELADO',   // CORRECCIÓN CRÍTICA: Cambiar de 'Pagado' a 'CANCELADO'
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('ventas', 'venta_estado')) {
                    $updatePayload['venta_estado'] = 'CANCELADO';
                }

                // Actualización directa en la BD para evitar bloqueos de asignación masiva
                DB::table('ventas')
                    ->where('venta_id', $sale->venta_id)
                    ->update($updatePayload);
            });

            return redirect()->back()->with('success', "Venta #{$id} cancelada e inventario reincorporado correctamente.");

        } catch (\Exception $e) {
            Log::error("Error al cancelar venta #{$id} en destroySale: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
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