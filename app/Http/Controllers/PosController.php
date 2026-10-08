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

class PosController extends Controller
{
    /**
     * Cargar venta en borrador activa o redirigir a una nueva
     */
    public function index(Request $request, $id = null)
    {
        $user = Auth::user();
        $matrixBranch = Branch::getMatrixBranch();
        
        $userBranchId = $user->branch_id ?? ($matrixBranch->id ?? 1);
        $userBranchName = Branch::find($userBranchId)?->name ?? 'MATRIZ';

        if (!$id) {
            $draftSale = Sale::where('venta_idempleado', $user->id)
                ->where('venta_total', 0)
                ->whereDoesntHave('items')
                ->latest('venta_id')
                ->first();

            if (!$draftSale) {
                $draftSale = Sale::create([
                    'venta_idempleado' => $user->id,
                    'venta_idcliente'  => 0,
                    'nombre_cliente'   => 'Cliente General',
                    'venta_total'      => 0,
                    'venta_sucursal'   => $userBranchName,
                    'venta_fecha'      => date('Y-m-d'),
                    'venta_hora'       => date('H:i:s'),
                    'venta_tipopago'   => 'Efectivo',
                    'venta_tipo'       => '1',
                    'venta_descuento'  => 0,
                    'venta_abono'      => 0,
                    'venta_factura'    => 'NO',
                ]);
            }

            return redirect()->route('pos.index', $draftSale->venta_id);
        }

        $sale = Sale::with(['items', 'customer', 'seller'])->findOrFail($id);

        $currentBranch = Branch::where('name', $sale->venta_sucursal)->first();
        $activeBranchId = $currentBranch ? $currentBranch->id : $userBranchId;

        $customers = Customer::where('sucursal', $sale->venta_sucursal)->get();
        $sellers   = User::all();

        // Cargar productos disponibles (> 0) en la sucursal activa
        $products = Product::whereHas('branches', function ($q) use ($activeBranchId) {
            $q->where('branch_id', $activeBranchId)
              ->where('branch_product.stock_current', '>', 0);
        })->with(['branches' => function ($q) use ($activeBranchId) {
            $q->where('branch_id', $activeBranchId);
        }])->get();

        return view('pos.index', compact('sale', 'customers', 'sellers', 'products', 'user'));
    }

    /**
     * Agregar Producto Físico al Carrito
     */
    public function addProduct(Request $request, $saleId)
    {
        try {
            $sale = Sale::findOrFail($saleId);
            $productId = $request->product_id;

            // SI ES UN SERVICIO O NO TIENE PRODUCT_ID -> DERIVAR A SERVICIO
            if ($request->has('is_service') || !$productId) {
                return $this->addService($request, $saleId);
            }

            // SI ES UN PRODUCTO FÍSICO -> CONSULTAR STOCK ESPECÍFICO DE LA SUCURSAL
            $product = Product::findOrFail($productId);
            
            // Obtener el ID de la sucursal asociada al nombre guardado en la venta
            $branch = Branch::where('name', $sale->venta_sucursal)->first();
            $branchId = $branch ? $branch->id : (Auth::user()->branch_id ?? 1);

            // Consultar stock en la tabla pivote branch_product
            $pivot = DB::table('branch_product')
                ->where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->first();

            $stockDisponible = $pivot ? $pivot->stock_current : 0;

            // Cantidad actual agregada en el carrito (usando solo producto_id)
            $existingItem = $sale->items()
                ->where('producto_id', $productId)
                ->first();

            $cantEnCarrito = $existingItem ? $existingItem->cantidad : 0;

            if (($cantEnCarrito + 1) > $stockDisponible) {
                return response()->json([
                    'success' => false,
                    'message' => "Supera la existencia disponible ({$stockDisponible})"
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
     * Agregar Servicio sin validación de stock
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

            // Si es un producto físico, validamos stock
            if ($productId && $productId > 0) {
                $sale = Sale::findOrFail($item->venta_id);
                $branch = Branch::where('name', $sale->venta_sucursal)->first();
                $branchId = $branch ? $branch->id : (Auth::user()->branch_id ?? 1);

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
        $item = SaleItem::findOrFail($itemId);
        $saleId = $item->venta_id;
        $item->delete();

        $this->recalculateTotal($saleId);

        return response()->json(['success' => true]);
    }

    /**
     * Actualizar Encabezado (Cliente, Descuento, Tipo de Pago, etc.)
     */
    public function updateHeader(Request $request, $saleId)
    {
        $sale = Sale::findOrFail($saleId);

        $sale->update($request->only([
            'venta_idcliente',
            'nombre_cliente',
            'venta_idempleado',
            'venta_tipopago',
            'venta_tipo',
            'venta_descuento',
            'venta_factura',
            'numero_factura',
            'venta_cfdi',
            'venta_tipo_factura',
        ]));

        $this->recalculateTotal($saleId);

        return response()->json(['success' => true]);
    }

    /**
     * Finalizar Venta y Confirmar en Base de Datos
     */
    public function finishSale(Request $request, $saleId)
    {
        try {
            $redirectUrl = DB::transaction(function () use ($request, $saleId) {
                $sale = Sale::with('items')->findOrFail($saleId);
                $receivedAmount = (float) $request->get('abono', 0);
                
                $branch = Branch::where('name', $sale->venta_sucursal)->first();
                $branchId = $branch ? $branch->id : (Auth::user()->branch_id ?? 1);

                if ($sale->items->count() === 0) {
                    throw new \Exception("No hay productos ni servicios en el carrito.");
                }

                // Descontar inventario solo de productos físicos
                foreach ($sale->items as $item) {
                    $pId = $item->product_id ?? $item->producto_id;
                    if (!$pId || $pId == 0) {
                        continue; // Omitir servicios
                    }

                    $pivot = DB::table('branch_product')
                        ->where('product_id', $pId)
                        ->where('branch_id', $branchId)
                        ->lockForUpdate()
                        ->first();

                    $prevStock = $pivot ? $pivot->stock_current : 0;

                    if ($prevStock < $item->cantidad) {
                        throw new \Exception("Stock insuficiente para '{$item->nombre}'. Disponible: {$prevStock}");
                    }

                    $newStock = $prevStock - $item->cantidad;

                    DB::table('branch_product')
                        ->where('product_id', $pId)
                        ->where('branch_id', $branchId)
                        ->update(['stock_current' => $newStock, 'updated_at' => now()]);

                    InventoryMovement::create([
                        'product_id'     => $pId,
                        'user_id'        => Auth::id(),
                        'branch_id'      => $branchId,
                        'type'           => 'SALIDA',
                        'quantity'       => $item->cantidad,
                        'previous_stock' => $prevStock,
                        'new_stock'      => $newStock,
                        'reference'      => "Venta #{$sale->venta_id}",
                        'notes'          => "Venta procesada en POS",
                    ]);
                }

                $sale->update([
                    'venta_abono' => $sale->venta_tipo == '1' ? $sale->venta_total : $receivedAmount,
                    'venta_fecha' => date('Y-m-d'),
                    'venta_hora'  => date('H:i:s'),
                ]);

                return route('pos.ticket', $sale->venta_id);
            });

            return response()->json(['success' => true, 'redirect' => $redirectUrl]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancelar borrador actual
     */
    public function cancelSale($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        $sale->items()->delete();
        
        $sale->update([
            'venta_total'     => 0,
            'venta_descuento' => 0,
            'venta_abono'     => 0,
            'nombre_cliente'  => 'Cliente General',
            'venta_idcliente' => 0,
        ]);

        return redirect()->route('pos.index', $saleId);
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
     * Obtener el siguiente número consecutivo de factura para la sucursal activa
     */
    public function getNextInvoiceNumber(Request $request, $saleId)
    {
        $sale = Sale::findOrFail($saleId);

        $lastInvoice = Sale::where('venta_sucursal', $sale->venta_sucursal)
            ->where('venta_factura', 'SI')
            ->whereNotNull('numero_factura')
            ->max('numero_factura');

        $nextInvoice = $lastInvoice ? ((int) $lastInvoice + 1) : 1;

        return response()->json([
            'success'      => true,
            'next_invoice' => $nextInvoice
        ]);
    }
}