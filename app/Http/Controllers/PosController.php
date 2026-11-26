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
use Illuminate\Support\Facades\Cache;

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

            // 1. Cachear sucursales como arreglos puros y convertir a stdClass (100% seguro al deserializar)
            $branchesRaw = Cache::remember('pos_branches_flat_v5', 600, function () {
                return DB::table('branches')->get()->map(fn($item) => (array) $item)->toArray();
            });
            $branches = collect(is_array($branchesRaw) ? $branchesRaw : [])->map(fn($item) => (object) $item);

            $matrixBranch = $branches->first(fn($b) => strtoupper($b->name ?? '') === 'MATRIZ') ?? $branches->first();
            $userBranchId = $user->branch_id ?? ($matrixBranch->id ?? 1);
            $userBranchName = $branches->firstWhere('id', $userBranchId)?->name ?? 'MATRIZ';

            if (!$id) {
                $draftSale = Sale::where('venta_idempleado', $user->id)
                    ->where('venta_total', 0)
                    ->where(function ($q) {
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

                return redirect()->route('pos.index', ['id' => $draftSale->venta_id]);
            }

            $sale = Sale::with(['items', 'customer', 'seller'])->findOrFail($id);

            if ($sale->status === 'Pagado' || $sale->status === 'Eliminar') {
                return redirect()->route('pos.index');
            }

            // Actualización segura de la fecha solo si difiere de hoy
            $today = now()->format('Y-m-d');
            if (!in_array($userRole, [1, 2]) && !$user->isAdmin() && $sale->venta_fecha !== $today) {
                try {
                    DB::table('ventas')
                        ->where('venta_id', $sale->venta_id)
                        ->update(['venta_fecha' => $today]);
                    $sale->venta_fecha = $today;
                } catch (\Exception $exFecha) {
                    Log::warning("No se pudo actualizar la fecha: " . $exFecha->getMessage());
                }
            }

            $sellerUser = $sale->seller ?? $user;
            $activeBranchId = $sellerUser->branch_id
                ?? $branches->firstWhere('name', $sale->venta_sucursal)?->id
                ?? $userBranchId;

            // 2. Cachear vendedores como arreglos puros y convertir a stdClass (clave nueva v5)
            $sellersRaw = Cache::remember("pos_sellers_flat_v5_{$userRole}_{$activeBranchId}_{$user->id}", 600, function () use ($userRole, $user, $activeBranchId) {
                $query = DB::table('users')
                    ->select('id', 'username', 'branch_id')
                    ->whereNull('deleted_at');

                if ($userRole === 1 || (method_exists($user, 'isAdmin') && $user->isAdmin())) {
                    $rows = $query->get();
                } elseif ($userRole === 2) {
                    $rows = (clone $query)->where('branch_id', $activeBranchId)->get();
                    if ($rows->isEmpty()) {
                        $rows = (clone $query)->where('id', $user->id)->get();
                    }
                } else {
                    $rows = $query->where('id', $user->id)->get();
                }

                return $rows->map(fn($u) => (array) $u)->toArray();
            });
            $sellers = collect(is_array($sellersRaw) ? $sellersRaw : [])->map(fn($u) => (object) $u);

            // 3. Cargar SOLO los primeros 30 clientes inicialmente (el resto se busca vía AJAX)
            $customersQuery = DB::table('clientes')
                ->select('cliente_id', 'nombre', 'telefono', 'sucursal');

            if (!($userRole === 1 || (method_exists($user, 'isAdmin') && $user->isAdmin()))) {
                $customersQuery->where(function ($q) use ($sale, $userBranchName) {
                    $q->where('sucursal', $sale->venta_sucursal)
                      ->orWhere('sucursal', $userBranchName)
                      ->orWhere('sucursal', 'MATRIZ');
                });
            }

            $customers = $customersQuery
                ->orderByDesc('cliente_id')
                ->limit(30)
                ->get();

            // 4. Productos con stock en la sucursal activa
            $products = DB::table('products')
                ->join('branch_product', 'products.id', '=', 'branch_product.product_id')
                ->where('branch_product.branch_id', $activeBranchId)
                ->where('branch_product.stock_current', '>', 0)
                ->select(
                    'products.id',
                    'products.name',
                    'products.barcode',
                    'products.sale_price',
                    'branch_product.stock_current as stock_sucursal'
                )
                ->orderBy('products.name', 'asc')
                ->get();

            $maxFolioActual = DB::table('ventas')
                ->where('venta_sucursal', $sale->venta_sucursal)
                ->whereNotNull('folio_sucursal')
                ->max('folio_sucursal');

            $nextFolio = ($maxFolioActual ? (int) $maxFolioActual : 0) + 1;

            return view('pos.index', compact('sale', 'customers', 'sellers', 'products', 'user', 'branches', 'nextFolio'));

        } catch (\Exception $e) {
            Log::error("Error general en POS: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
            return back()->with('error', 'Error al cargar el punto de venta: ' . $e->getMessage());
        }
    }

    /**
     * Búsqueda rápida de clientes bajo demanda (AJAX)
     */
    public function searchCustomers(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $branch = trim((string) $request->get('branch', ''));

        $query = DB::table('clientes')
            ->select('cliente_id', 'nombre', 'telefono', 'sucursal');

        if ($q !== '') {
            // Si hay texto de búsqueda, buscar en TODAS las sucursales
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%");
            });
        } elseif ($branch !== '') {
            // Sin texto, mostrar primero los recientes de la sucursal actual
            $query->where('sucursal', $branch);
        }

        $results = $query->orderBy('nombre', 'asc')
            ->limit(40)
            ->get();

        return response()->json([
            'success'   => true,
            'customers' => $results,
        ]);
    }

    /**
     * Agregar Producto Físico al Carrito
     */
    public function addProduct(Request $request, $saleId)
    {
        try {
            $productId = (int) $request->product_id;

            if ($request->has('is_service') || !$productId) {
                return $this->addService($request, $saleId);
            }

            $sale = Sale::select('venta_id', 'venta_idempleado', 'venta_sucursal', 'venta_descuento')
                ->findOrFail($saleId);

            $product = DB::table('products')
                ->select('id', 'name', 'barcode', 'sale_price')
                ->where('id', $productId)
                ->first();

            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
            }

            $branchId = DB::table('users')->where('id', $sale->venta_idempleado)->value('branch_id')
                ?? DB::table('branches')->where('name', $sale->venta_sucursal)->value('id')
                ?? 1;

            $stockDisponible = (int) (DB::table('branch_product')
                ->where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->value('stock_current') ?? 0);

            $existingItem = SaleItem::where('venta_id', $saleId)
                ->where('producto_id', $productId)
                ->first();

            $cantEnCarrito = $existingItem ? (int) $existingItem->cantidad : 0;

            if (($cantEnCarrito + 1) > $stockDisponible) {
                return response()->json([
                    'success' => false,
                    'message' => "Supera la existencia disponible en inventario ({$stockDisponible})"
                ], 422);
            }

            if ($existingItem) {
                $existingItem->increment('cantidad');
            } else {
                SaleItem::create([
                    'venta_id'    => $saleId,
                    'producto_id' => $product->id,
                    'nombre'      => $product->name,
                    'sku'         => $product->barcode ?? 'S/C',
                    'cantidad'    => 1,
                    'precio'      => $product->sale_price,
                ]);
            }

            return $this->cartJsonResponse($sale);

        } catch (\Exception $e) {
            Log::error("Error en addProduct: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar Servicio al Carrito
     */
    public function addService(Request $request, $saleId)
    {
        try {
            $request->validate([
                'concept' => 'required|string',
                'amount'  => 'required|numeric|min:0.01',
            ]);

            $sale = Sale::select('venta_id', 'venta_descuento')->findOrFail($saleId);

            SaleItem::create([
                'venta_id'    => $saleId,
                'product_id'  => null,
                'producto_id' => 0,
                'nombre'      => '[SERVICIO] ' . $request->concept,
                'sku'         => 'SERV',
                'cantidad'    => 1,
                'precio'      => $request->amount,
            ]);

            return $this->cartJsonResponse($sale);

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
            $productId = (int) ($item->product_id ?? $item->producto_id ?? 0);

            $sale = Sale::select('venta_id', 'venta_idempleado', 'venta_sucursal', 'venta_descuento')
                ->findOrFail($item->venta_id);

            if ($productId > 0) {
                $branchId = DB::table('users')->where('id', $sale->venta_idempleado)->value('branch_id')
                    ?? DB::table('branches')->where('name', $sale->venta_sucursal)->value('id')
                    ?? 1;

                $availableStock = (int) (DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $branchId)
                    ->value('stock_current') ?? 0);

                if ($newQty > $availableStock) {
                    return response()->json([
                        'success' => false,
                        'message' => "Supera la existencia disponible ({$availableStock})"
                    ], 422);
                }
            }

            $item->update(['cantidad' => $newQty]);

            return $this->cartJsonResponse($sale);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar producto del carrito y devolver JSON actualizado
     */
    public function removeItem($itemId)
    {
        try {
            $item = SaleItem::findOrFail($itemId);
            $sale = Sale::select('venta_id', 'venta_descuento')->findOrFail($item->venta_id);
            $item->delete();

            return $this->cartJsonResponse($sale);
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
                'venta_descuento_porcentaje',
                'venta_factura',
                'numero_factura',
                'venta_cfdi',
                'venta_tipo_factura',
            ]);

            if ($request->has('venta_descuento_porcentaje')) {
                $porcentaje = max(0, min(100, floatval($request->venta_descuento_porcentaje)));
                $subtotalItems = $sale->items->sum(fn($item) => $item->cantidad * $item->precio);
                $fields['venta_descuento'] = round($subtotalItems * ($porcentaje / 100), 2);
                unset($fields['venta_descuento_porcentaje']);
            }

            if ($sale->status !== 'Pagado') {
                $fields['status'] = 'Pendiente';
            }

            if ($request->has('venta_idempleado')) {
                $newSellerId = intval($request->venta_idempleado);
                $newSeller   = User::find($newSellerId);
                $canChangeSeller = false;

                if ($user->isAdmin() || $userRole === 1) {
                    $canChangeSeller = true;
                } elseif ($userRole === 2 && $newSeller) {
                    $userBranch   = $user->branch_id ?? $user->branch_name ?? $user->sucursal;
                    $sellerBranch = $newSeller->branch_id ?? $newSeller->branch_name ?? $newSeller->sucursal;
                    if ($userBranch == $sellerBranch) {
                        $canChangeSeller = true;
                    }
                }

                if ($canChangeSeller && $newSeller) {
                    $newBranchId = $newSeller->branch_id
                        ?? Branch::where('name', $newSeller->sucursal)->value('id')
                        ?? 1;

                    $newBranchName = Branch::find($newBranchId)?->name
                        ?? $newSeller->sucursal
                        ?? $sale->venta_sucursal;

                    if ($sale->venta_sucursal !== $newBranchName && $sale->items->count() > 0) {
                        $stockErrors = [];
                        foreach ($sale->items as $item) {
                            $pId = (int) ($item->producto_id ?? $item->product_id ?? 0);
                            if ($pId <= 0) continue;

                            $pivot = DB::table('branch_product')
                                ->where('product_id', $pId)
                                ->where('branch_id', $newBranchId)
                                ->first();

                            $availableStock = $pivot ? (int) $pivot->stock_current : 0;
                            if ($availableStock < $item->cantidad) {
                                $stockErrors[] = "• {$item->nombre}: Solicitado {$item->cantidad}, Disponible: {$availableStock}";
                            }
                        }

                        if (count($stockErrors) > 0) {
                            $msg = "No se puede cambiar al vendedor a la sucursal ({$newBranchName}) por falta de stock:\n" . implode("\n", $stockErrors);
                            return response()->json(['success' => false, 'message' => $msg], 422);
                        }
                    }

                    $fields['venta_idempleado'] = $newSellerId;
                    $fields['venta_sucursal']   = $newBranchName;
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
            $sale->refresh();

            return $this->cartJsonResponse($sale);
        } catch (\Exception $e) {
            Log::error("Error en updateHeader: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function finishSale(Request $request, $saleId)
    {
        try {
            $saleData = DB::transaction(function () use ($request, $saleId) {
                $sale = Sale::with('items')->findOrFail($saleId);

                if (strtoupper($sale->status) === 'PAGADO') {
                    return [
                        'redirect' => route('pos.ticket', $sale->venta_id),
                        'print'    => (bool) $request->get('print_ticket', false)
                    ];
                }

                $receivedAmount = (float) $request->get('abono', 0);
                $totalSale      = (float) $sale->venta_total;
                $changeAmount   = max(0, $receivedAmount - $totalSale);
                $user           = Auth::user();

                $sellerUser = User::find($sale->venta_idempleado) ?? $user;
                $branchId   = $sellerUser->branch_id
                    ?? Branch::where('name', $sale->venta_sucursal)->value('id')
                    ?? 1;

                $branchName = Branch::where('id', $branchId)->value('name') ?? $sellerUser->sucursal ?? $sale->venta_sucursal;

                if ($sale->items->count() === 0) {
                    throw new \Exception("No hay productos ni servicios en el carrito.");
                }

                foreach ($sale->items as $item) {
                    $pId = (int) ($item->producto_id ?? $item->product_id ?? 0);
                    if ($pId <= 0) continue;

                    $pivot = DB::table('branch_product')
                        ->where('product_id', $pId)
                        ->where('branch_id', $branchId)
                        ->lockForUpdate()
                        ->first();

                    $prevStock = $pivot ? (int) $pivot->stock_current : 0;

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

                // Folio de venta consecutivo por sucursal
                $folioSucursal = $sale->folio_sucursal;
                if (empty($folioSucursal) && Schema::hasColumn('ventas', 'folio_sucursal')) {
                    $maxFolio = DB::table('ventas')
                        ->where('venta_sucursal', $branchName)
                        ->whereNotNull('folio_sucursal')
                        ->lockForUpdate()
                        ->max('folio_sucursal');

                    $folioSucursal = ($maxFolio ? (int) $maxFolio : 0) + 1;
                }

                // Folio de factura consecutivo independiente por sucursal
                $numeroFactura = $sale->numero_factura;
                if ($sale->venta_factura === 'SI' && Schema::hasColumn('ventas', 'numero_factura')) {
                    $yaExisteFactura = DB::table('ventas')
                        ->where('venta_sucursal', $branchName)
                        ->where('venta_factura', 'SI')
                        ->where('status', 'Pagado')
                        ->where('numero_factura', $numeroFactura)
                        ->where('venta_id', '!=', $sale->venta_id)
                        ->lockForUpdate()
                        ->exists();

                    if (empty($numeroFactura) || $yaExisteFactura) {
                        $maxFacturaSucursal = DB::table('ventas')
                            ->where('venta_sucursal', $branchName)
                            ->where('venta_factura', 'SI')
                            ->where('status', 'Pagado')
                            ->whereNotNull('numero_factura')
                            ->lockForUpdate()
                            ->max(DB::raw('CAST(numero_factura AS UNSIGNED)'));

                        $numeroFactura = ($maxFacturaSucursal ? (int) $maxFacturaSucursal : 0) + 1;
                    }
                }

                $finalDate = ($user->isAdmin() && !empty($sale->venta_fecha))
                    ? $sale->venta_fecha
                    : now()->format('Y-m-d');

                $updatePayload = [
                    'venta_sucursal' => $branchName,
                    'venta_abono'    => $sale->venta_tipo == '1' ? $totalSale : $receivedAmount,
                    'venta_fecha'    => $finalDate,
                    'venta_hora'     => now()->format('H:i:s'),
                    'status'         => 'Pagado',
                    'updated_at'     => now(),
                ];

                if (Schema::hasColumn('ventas', 'folio_sucursal')) {
                    $updatePayload['folio_sucursal'] = $folioSucursal;
                }
                if (Schema::hasColumn('ventas', 'numero_factura')) {
                    $updatePayload['numero_factura'] = ($sale->venta_factura === 'SI') ? $numeroFactura : null;
                }
                if (Schema::hasColumn('ventas', 'venta_efectivo')) {
                    $updatePayload['venta_efectivo'] = $receivedAmount;
                }
                if (Schema::hasColumn('ventas', 'venta_cambio')) {
                    $updatePayload['venta_cambio'] = $changeAmount;
                }
                if (Schema::hasColumn('ventas', 'venta_estado')) {
                    $updatePayload['venta_estado'] = 'Pagado';
                }

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
     * Cancelar / Eliminar Venta y Revertir Inventario
     */
    public function cancelSale(Request $request, $saleId)
    {
        try {
            DB::transaction(function () use ($saleId) {
                $sale = Sale::with('items')->findOrFail($saleId);

                if (strtoupper($sale->status) === 'PAGADO') {
                    $sellerUser = User::find($sale->venta_idempleado) ?? Auth::user();
                    $branchId = $sellerUser->branch_id
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

                $updatePayload = [
                    'venta_tipo' => 3,
                    'status'     => 'CANCELADO',
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('ventas', 'venta_estado')) {
                    $updatePayload['venta_estado'] = 'CANCELADO';
                }

                DB::table('ventas')
                    ->where('venta_id', $sale->venta_id)
                    ->update($updatePayload);
            });

            return redirect('pos')->with('success', 'Venta cancelada correctamente.');

        } catch (\Exception $e) {
            return redirect('pos')->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
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
     * Obtener el CONSECUTIVO DE FACTURA INDEPENDIENTE POR SUCURSAL
     */
    public function getNextInvoiceNumber(Request $request, $saleId)
    {
        try {
            $sale = Sale::select('venta_id', 'venta_sucursal')->findOrFail($saleId);
            $branch = trim((string) $request->get('branch', $sale->venta_sucursal));

            $lastInvoice = Sale::where('venta_factura', 'SI')
                ->where('status', 'Pagado')
                ->where('venta_sucursal', $branch)
                ->whereNotNull('numero_factura')
                ->max(DB::raw('CAST(numero_factura AS UNSIGNED)'));

            $nextInvoice = $lastInvoice ? ((int) $lastInvoice + 1) : 1;

            return response()->json([
                'success'      => true,
                'branch'       => $branch,
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

    public function checkBranchStock(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'items'       => 'required|array',
        ]);

        $newEmployee = User::find($request->employee_id);

        if (!$newEmployee) {
            return response()->json([
                'success' => false,
                'message' => 'El empleado seleccionado no existe.'
            ], 404);
        }

        $newBranch = $newEmployee->branch?->name ?? $newEmployee->branch_name ?? $newEmployee->sucursal ?? 'MATRIZ';
        $itemsWithErrors = [];

        foreach ($request->items as $item) {
            $productId = (int) ($item['producto_id'] ?? $item['id'] ?? 0);
            $requestedQty = (float) ($item['cantidad'] ?? 1);

            if ($productId === 0) {
                continue;
            }

            $product = Product::find($productId);

            if (!$product) {
                $itemsWithErrors[] = [
                    'id'        => $productId,
                    'nombre'    => $item['nombre'] ?? 'Producto no encontrado',
                    'reason'    => 'El producto no existe en el catálogo general.',
                    'available' => 0
                ];
                continue;
            }

            $stock = (float) ($product->stock ?? 0);

            if ($stock < $requestedQty) {
                $itemsWithErrors[] = [
                    'id'        => $productId,
                    'nombre'    => $product->name ?? $product->nombre ?? $item['nombre'] ?? "Artículo #{$productId}",
                    'requested' => $requestedQty,
                    'available' => $stock,
                    'reason'    => $stock <= 0
                        ? 'No existe en la nueva sucursal'
                        : "Stock insuficiente (Disponible: {$stock}, Solicitado: {$requestedQty})"
                ];
            }
        }

        if (count($itemsWithErrors) > 0) {
            return response()->json([
                'success'    => false,
                'new_branch' => $newBranch,
                'errors'     => $itemsWithErrors,
                'message'    => 'Algunos artículos del carrito no están disponibles en la sucursal del nuevo empleado.'
            ]);
        }

        return response()->json([
            'success'    => true,
            'new_branch' => $newBranch,
            'message'    => 'Todos los artículos están disponibles en la nueva sucursal.'
        ]);
    }

    /**
     * Recalcula totales en 1 sola consulta y devuelve el estado actualizado del carrito
     */
    private function cartJsonResponse(Sale $sale)
    {
        $items = DB::table('venta_producto')
            ->select('id', 'producto_id', 'nombre', 'sku', 'cantidad', 'precio')
            ->where('venta_id', $sale->venta_id)
            ->get();

        $subtotal = $items->sum(fn($i) => $i->cantidad * $i->precio);
        $descuento = (float) ($sale->venta_descuento ?? 0);
        $total = max(0, $subtotal - $descuento);

        DB::table('ventas')
            ->where('venta_id', $sale->venta_id)
            ->update(['venta_total' => $total]);

        $currentPercent = ($subtotal > 0 && $descuento > 0)
            ? round(($descuento / $subtotal) * 100, 2)
            : 0;

        return response()->json([
            'success'         => true,
            'items'           => $items,
            'venta_total'     => number_format($total, 2, '.', ''),
            'venta_total_fmt' => number_format($total, 2),
            'descuento_fmt'   => number_format($descuento, 2),
            'descuento_pct'   => $currentPercent,
        ]);
    }
}