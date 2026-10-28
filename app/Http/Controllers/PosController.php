<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\User;
use App\Models\Branch;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;

class PosController extends Controller
{
    /**
     * Cargar venta en borrador activa o redirigir a una nueva
     */
    public function index(Request $request, $id = null)
    {
        try {
            $user = Auth::user();
            $userRole = (int) ($user->role_id ?? $user->type ?? 2);
            $matrixBranch = Branch::getMatrixBranch();
            
            $userBranchId = $user->branch_id ?? ($matrixBranch->id ?? 1);
            $userBranchName = Branch::find($userBranchId)?->name ?? 'MATRIZ';
            $branches = Branch::all();

            if (!$id) {
                // Reutilizar cualquier borrador activo del usuario que no haya sido finalizado (status 'Pendiente' y sin cobro)
                $draftSale = Sale::where('venta_idempleado', $user->id)
                    ->where('venta_total', 0)
                    ->where(function($q) {
                        $q->where('status', 'Pendiente')
                          ->orWhereNull('status');
                    })
                    ->latest('venta_id')
                    ->first();

                if (!$draftSale) {
                    $draftSale = Sale::create([
                        'venta_idempleado' => $user->id,
                        'venta_idcliente'  => 0,
                        'nombre_cliente'   => 'Cliente General',
                        'venta_total'      => 0,
                        'venta_sucursal'   => $userBranchName,
                        'venta_fecha'      => now()->format('Y-m-d'),
                        'venta_hora'       => now()->format('H:i:s'),
                        'venta_tipopago'   => 'Efectivo',
                        'venta_tipo'       => '1',
                        'venta_descuento'  => 0,
                        'venta_abono'      => 0,
                        'venta_factura'    => 'NO',
                        'status'           => 'Pendiente',
                    ]);
                }

                return redirect()->route('pos.index', $draftSale->venta_id);
            }

            $sale = Sale::with(['items', 'customer', 'seller'])->findOrFail($id);

            // Si la venta seleccionada ya está cobrada o eliminada, redirigir a la vista limpia de POS
            if ($sale->status === 'Pagado' || $sale->status === 'Eliminar') {
                return redirect()->route('pos.index');
            }

            // Si no es Admin (rol 1 ni rol 2), restablece a la fecha actual
            if (!in_array($userRole, [1, 2]) && !$user->isAdmin()) {
                $sale->update(['venta_fecha' => now()->format('Y-m-d')]);
                $sale->venta_fecha = now()->format('Y-m-d');
            }

            $sellerUser = User::find($sale->venta_idempleado) ?? $user;
            $activeBranchId = $sellerUser->branch_id 
                ?? $sellerUser->sucursal_id 
                ?? Branch::where('name', $sale->venta_sucursal)->value('id') 
                ?? $userBranchId;

            // Cargar únicamente clientes pertenecientes a la sucursal activa de la venta (o todos si es Admin)
            if ($user->isAdmin() || $userRole === 1) {
                $customers = Customer::all();
            } else {
                $customers = Customer::where('sucursal', $sale->venta_sucursal)
                    ->orWhere('sucursal', $userBranchName)
                    ->orWhere('sucursal', 'MATRIZ')
                    ->get();
            }

            // Filtrar vendedores según permisos de sucursal
            if ($user->isAdmin() || $userRole === 1) {
                $sellers = User::all();
            } else {
                $hasBranchId   = Schema::hasColumn('users', 'branch_id');
                $hasBranchName = Schema::hasColumn('users', 'branch_name');
                $hasSucursal   = Schema::hasColumn('users', 'sucursal');
                $hasSucursalId = Schema::hasColumn('users', 'sucursal_id');

                $sellers = User::where(function ($q) use ($userBranchId, $userBranchName, $hasBranchId, $hasBranchName, $hasSucursal, $hasSucursalId) {
                    if ($hasBranchId) $q->orWhere('branch_id', $userBranchId);
                    if ($hasSucursalId) $q->orWhere('sucursal_id', $userBranchId);
                    if ($hasBranchName) $q->orWhere('branch_name', $userBranchName);
                    if ($hasSucursal) $q->orWhere('sucursal', $userBranchName);
                })->get();
            }

            // Productos con existencia > 0 en la sucursal del vendedor
            $products = Product::whereHas('branches', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)
                  ->where('branch_product.stock_current', '>', 0);
            })->with(['branches' => function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            }])->get();

            return view('pos.index', compact('sale', 'customers', 'sellers', 'products', 'user', 'branches'));

        } catch (\Exception $e) {
            Log::error("Error en PosController@index: " . $e->getMessage());
            return redirect()->route('pos.index')->with('error', 'Ocurrió un problema al cargar el punto de venta.');
        }
    }

    /**
     * Agregar Producto Físico al Carrito
     */
    public function addProduct(Request $request, $saleId)
    {
        try {
            $sale = Sale::findOrFail($saleId);
            $productId = $request->product_id;

            if ($request->has('is_service') || !$productId) {
                return $this->addService($request, $saleId);
            }

            $product = Product::findOrFail($productId);
            
            $sellerUser = User::find($sale->venta_idempleado);
            $branchId = $sellerUser?->branch_id 
                ?? $sellerUser?->sucursal_id 
                ?? Branch::where('name', $sale->venta_sucursal)->value('id') 
                ?? 1;

            $pivot = DB::table('branch_product')
                ->where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->first();

            $stockDisponible = $pivot ? $pivot->stock_current : 0;

            $existingItem = $sale->items()
                ->where('producto_id', $productId)
                ->first();

            $cantEnCarrito = $existingItem ? $existingItem->cantidad : 0;

            if (($cantEnCarrito + 1) > $stockDisponible) {
                return response()->json([
                    'success' => false,
                    'message' => "Supera la existencia disponible en inventario ({$stockDisponible})"
                ], 422);
            }

            if ($existingItem) {
                $existingItem->increment('cantidad');
            } else {
                $sale->items()->create([
                    'producto_id' => $product->id,
                    'nombre'      => $product->name,
                    'sku'         => $product->barcode ?? 'S/C',
                    'cantidad'    => 1,
                    'precio'      => $product->sale_price,
                ]);
            }

            $this->recalculateTotal($saleId);
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error("Error en addProduct: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error al agregar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar Servicio
     */
    public function addService(Request $request, $saleId)
    {
        try {
            $request->validate([
                'concept' => 'required|string',
                'amount'  => 'required|numeric|min:0.01',
            ]);

            $sale = Sale::findOrFail($saleId);

            $sale->items()->create([
                'product_id'  => null,
                'producto_id' => 0,
                'nombre'      => '[SERVICIO] ' . $request->concept,
                'sku'         => 'SERV',
                'cantidad'    => 1,
                'precio'      => $request->amount,
            ]);

            $this->recalculateTotal($saleId);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error("Error en addService: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar cantidad de un renglón
     */
    public function updateQuantity(Request $request, $itemId)
    {
        try {
            $item = SaleItem::findOrFail($itemId);
            $newQty = max(1, (int) $request->cantidad);
            $productId = $item->product_id ?? $item->producto_id;

            if ($productId && $productId > 0) {
                $sale = Sale::findOrFail($item->venta_id);
                $sellerUser = User::find($sale->venta_idempleado);
                
                $branchId = $sellerUser?->branch_id 
                    ?? $sellerUser?->sucursal_id 
                    ?? Branch::where('name', $sale->venta_sucursal)->value('id') 
                    ?? 1;

                $pivot = DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $branchId)
                    ->first();

                $availableStock = $pivot ? $pivot->stock_current : 0;

                if ($newQty > $availableStock) {
                    return response()->json([
                        'success' => false, 
                        'message' => "Supera la existencia disponible ({$availableStock})"
                    ], 422);
                }
            }

            $item->update(['cantidad' => $newQty]);
            $this->recalculateTotal($item->venta_id);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar producto del carrito
     */
    public function removeItem($itemId)
    {
        try {
            $item = SaleItem::findOrFail($itemId);
            $saleId = $item->venta_id;
            $item->delete();

            $this->recalculateTotal($saleId);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateHeader(Request $request, $saleId)
    {
        try {
            $sale = Sale::findOrFail($saleId);
            $user = Auth::user();
            $userRole = (int) ($user->role_id ?? $user->type ?? 2);

            $fields = $request->only([
                'venta_idcliente',
                'nombre_cliente',
                'venta_tipopago',
                'venta_tipo',
                'venta_descuento',
                'venta_factura',
                'numero_factura',
                'venta_cfdi',
                'venta_tipo_factura',
            ]);

            // Si la venta aún no ha sido pagada, asegurar que su estatus permanezca en 'Pendiente'
            if ($sale->status !== 'Pagado') {
                $fields['status'] = 'Pendiente';
            }

            // RESTRICCIÓN DE REASIGNACIÓN DE EMPLEADO/VENDEDOR
            if ($request->has('venta_idempleado')) {
                $newSellerId = intval($request->venta_idempleado);
                $newSeller   = User::find($newSellerId);

                $canChangeSeller = false;

                if ($user->isAdmin() || $userRole === 1) {
                    $canChangeSeller = true;
                } elseif ($userRole === 2 && $newSeller) {
                    $userBranch   = $user->branch_id ?? $user->sucursal_id ?? $user->branch_name ?? $user->sucursal;
                    $sellerBranch = $newSeller->branch_id ?? $newSeller->sucursal_id ?? $newSeller->branch_name ?? $newSeller->sucursal;

                    if ($userBranch == $sellerBranch) {
                        $canChangeSeller = true;
                    }
                }

                if ($canChangeSeller && $newSeller) {
                    $fields['venta_idempleado'] = $newSellerId;
                    $branchName = Branch::find($newSeller->branch_id ?? $newSeller->sucursal_id)?->name 
                        ?? $newSeller->sucursal 
                        ?? $sale->venta_sucursal;
                        
                    $fields['venta_sucursal'] = $branchName;
                }
            }

            if ($request->has('venta_fecha')) {
                if ($user->isAdmin() || in_array($userRole, [1, 2])) {
                    $fields['venta_fecha'] = $request->venta_fecha;
                } else {
                    $fields['venta_fecha'] = now()->format('Y-m-d');
                }
            }

            $sale->update($fields);
            $this->recalculateTotal($saleId);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error en updateHeader: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Finalizar Venta y Confirmar en Base de Datos
     */
    public function finishSale(Request $request, $saleId)
    {
        try {
            $saleData = DB::transaction(function () use ($request, $saleId) {
                $sale = Sale::with('items')->findOrFail($saleId);

                // Si ya está pagada, retornar respuesta
                if (strtoupper($sale->status) === 'Pagado') {
                    return [
                        'redirect' => route('pos.ticket', $sale->venta_id),
                        'print' => (bool) $request->get('print_ticket', false)
                    ];
                }

                $receivedAmount = (float) $request->get('abono', 0);
                $totalSale = (float) $sale->venta_total;
                $changeAmount = max(0, $receivedAmount - $totalSale);
                $user = Auth::user();

                $sellerUser = User::find($sale->venta_idempleado) ?? $user;
                $branchId = $sellerUser->branch_id 
                    ?? $sellerUser->sucursal_id 
                    ?? Branch::where('name', $sale->venta_sucursal)->value('id') 
                    ?? 1;

                $branchName = Branch::where('id', $branchId)->value('name') ?? $sellerUser->sucursal ?? $sale->venta_sucursal;

                if ($sale->items->count() === 0) {
                    throw new \Exception("No hay productos ni servicios en el carrito.");
                }

                // Descontar inventario de productos
                foreach ($sale->items as $item) {
                    $pId = (int) ($item->producto_id ?? $item->product_id ?? 0);
                    if ($pId <= 0) continue;

                    $pivot = DB::table('branch_product')
                        ->where('product_id', $pId)
                        ->where('branch_id', $branchId)
                        ->lockForUpdate()
                        ->first();

                    $prevStock = $pivot ? $pivot->stock_current : 0;

                    if ($prevStock < $item->cantidad) {
                        throw new \Exception("Stock insuficiente para '{$item->nombre}'. Disponible: {$prevStock}");
                    }

                    $newStock = max(0, $prevStock - $item->cantidad);

                    DB::table('branch_product')
                        ->where('product_id', $pId)
                        ->where('branch_id', $branchId)
                        ->update([
                            'stock_current' => $newStock, 
                            'updated_at'    => now()
                        ]);

                    try {
                        InventoryMovement::create([
                            'product_id'     => $pId,
                            'user_id'        => $user->id ?? auth()->id() ?? 1,
                            'branch_id'      => $branchId,
                            'type'           => 'SALIDA',
                            'quantity'       => $item->cantidad,
                            'previous_stock' => $prevStock,
                            'new_stock'      => $newStock,
                            'reference'      => "Venta #{$sale->venta_id}",
                            'notes'          => "Venta procesada en POS",
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error guardando movimiento de inventario Venta #{$sale->venta_id}: " . $e->getMessage());
                    }
                }

                $finalDate = ($user->isAdmin() && !empty($sale->venta_fecha)) 
                    ? $sale->venta_fecha 
                    : now()->format('Y-m-d');

                // 1. Preparar campos para actualizar mediante Query Builder
                $updatePayload = [
                    'venta_sucursal' => $branchName,
                    'venta_abono'    => $sale->venta_tipo == '1' ? $totalSale : $receivedAmount,
                    'venta_fecha'    => $finalDate,
                    'venta_hora'     => now()->format('H:i:s'),
                    'status'         => 'Pagado',
                    'updated_at'     => now(),
                ];

                if (Schema::hasColumn('ventas', 'venta_efectivo')) {
                    $updatePayload['venta_efectivo'] = $receivedAmount;
                }
                if (Schema::hasColumn('ventas', 'venta_cambio')) {
                    $updatePayload['venta_cambio'] = $changeAmount;
                }
                if (Schema::hasColumn('ventas', 'venta_estado')) {
                    $updatePayload['venta_estado'] = 'Pagado';
                }

                // Actualización directa en la tabla 'ventas' sin pasar por asignación masiva Eloquent
                DB::table('ventas')
                    ->where('venta_id', $sale->venta_id)
                    ->update($updatePayload);

                return [
                    'redirect' => route('pos.ticket', $sale->venta_id),
                    'print'    => (bool) $request->get('print_ticket', false)
                ];
            });

            return response()->json([
                'success'  => true, 
                'redirect' => $saleData['redirect'],
                'print'    => $saleData['print']
            ]);

        } catch (\Exception $e) {
            Log::error("Error al finalizar venta #{$saleId}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancelar / Eliminar Venta, Revertir Inventario y Liberar Folio de Factura
     */
    public function cancelSale(Request $request, $saleId)
    {
        try {
            DB::transaction(function () use ($saleId) {
                $sale = Sale::with('items')->findOrFail($saleId);

                // Reintegrar inventario solo si estaba pagada previamente
                if (strtoupper($sale->status) === 'Pagado') {
                    $sellerUser = User::find($sale->venta_idempleado) ?? Auth::user();
                    $branchId = $sellerUser->branch_id 
                        ?? $sellerUser->sucursal_id 
                        ?? Branch::where('name', $sale->venta_sucursal)->value('id') 
                        ?? 1;

                    foreach ($sale->items as $item) {
                        $pId = (int) ($item->producto_id ?? $item->product_id ?? 0);
                        if ($pId <= 0) continue;

                        $pivot = DB::table('branch_product')
                            ->where('product_id', $pId)
                            ->where('branch_id', $branchId)
                            ->lockForUpdate()
                            ->first();

                        $prevStock = $pivot ? $pivot->stock_current : 0;
                        $newStock  = $prevStock + $item->cantidad;

                        DB::table('branch_product')
                            ->where('product_id', $pId)
                            ->where('branch_id', $branchId)
                            ->update([
                                'stock_current' => $newStock,
                                'updated_at'    => now()
                            ]);
                    }
                }

                // ACTUALIZACIÓN DE ESTATUS Y TIPO EN BASE DE DATOS
                $updatePayload = [
                    'venta_tipo' => 3,             // 3 = Cancelado
                    'status'     => 'Cancelado',    // CAMBIAR STATUS DE 'Pagado' A 'CANCELADO'
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('ventas', 'venta_estado')) {
                    $updatePayload['venta_estado'] = 'Cancelado';
                }

                DB::table('ventas')
                    ->where('venta_id', $sale->venta_id)
                    ->update($updatePayload);
            });

            return response()->json([
                'success' => true,
                'message' => 'Venta cancelada correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Imprimir Ticket PDF
     */
    public function ticket($saleId)
    {
        $sale = Sale::with(['items', 'customer', 'seller'])->findOrFail($saleId);

        $pdf = Pdf::loadView('pos.ticket', compact('sale'))
            ->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->stream("ticket_venta_#{$saleId}.pdf");
    }

    /**
     * Recalcular total de la venta
     */
    private function recalculateTotal($saleId)
    {
        $sale = Sale::with('items')->findOrFail($saleId);
        $subtotal = $sale->items->sum(function($i) {
            return $i->cantidad * $i->precio;
        });
        
        $total = max(0, $subtotal - ($sale->venta_descuento ?? 0));

        $sale->update(['venta_total' => $total]);
    }

    /**
     * Obtener el CONSECUTIVO GLOBAL ÚNICO de factura (Todas las Sedes)
     */
    public function getNextInvoiceNumber(Request $request, $saleId)
    {
        try {
            // Obtener el folio más alto GLOBAL de todas las sucursales omitiendo eliminadas
            $lastInvoice = Sale::where('venta_factura', 'SI')
                ->where('status', '=', 'Pagado')
                ->whereNotNull('numero_factura')
                ->max('numero_factura');

            $nextInvoice = $lastInvoice ? ((int) $lastInvoice + 1) : 1;

            return response()->json([
                'success'      => true,
                'next_invoice' => $nextInvoice
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar Cliente Rápido o Completo
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre'   => 'required|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'sucursal' => 'nullable|string|max:255',
                'correo'   => 'nullable|email|max:255',
            ]);

            $nextId = (DB::table('clientes')->max('cliente_id') ?? 0) + 1;
            
            $clienteId = DB::table('clientes')->insertGetId([
                'cliente_id' => $nextId,
                'nombre'     => $validated['nombre'],
                'telefono'   => $validated['telefono'] ?? null,
                'correo'     => $validated['correo'] ?? null,
                'sucursal'   => $validated['sucursal'] ?? 'MATRIZ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success'    => true,
                'id'         => $clienteId,
                'cliente_id' => $nextId,
                'nombre'     => $validated['nombre'],
                'message'    => 'Cliente registrado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}