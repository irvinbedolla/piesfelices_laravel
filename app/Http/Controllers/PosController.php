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

        // 1. Si no viene ID en la URL, reutilizar un borrador existente del usuario o crear uno nuevo
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

        // 2. Cargar únicamente productos disponibles (> 0) en la sucursal activa
        $products = Product::whereHas('branches', function ($q) use ($activeBranchId) {
            $q->where('branch_id', $activeBranchId)
              ->where('branch_product.stock_current', '>', 0);
        })->with(['branches' => function ($q) use ($activeBranchId) {
            $q->where('branch_id', $activeBranchId);
        }])->get();

        return view('pos.index', compact('sale', 'customers', 'sellers', 'products', 'user'));
    }

    /**
     * Agregar Producto por lector CB o Clic
     */
    public function addProduct(Request $request, $saleId)
    {
        $sale = Sale::findOrFail($saleId);
        $productId = $request->product_id;
        $barcode   = $request->barcode;

        if ($barcode) {
            $product = Product::where('barcode', $barcode)->first();
        } else {
            $product = Product::findOrFail($productId);
        }

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        // Consultar stock en sucursal
        $pivot = DB::table('branch_product')
            ->where('product_id', $product->id)
            ->where('branch_id', Auth::user()->branch_id ?? 1)
            ->first();

        $availableStock = $pivot ? $pivot->stock_current : 0;

        $existingItem = SaleItem::where('venta_id', $saleId)
            ->where('producto_id', $product->id)
            ->first();

        $currentQty = $existingItem ? $existingItem->cantidad : 0;

        if (($currentQty + 1) > $availableStock) {
            return response()->json([
                'success' => false, 
                'message' => "Stock insuficiente en esta sucursal. Disponible: {$availableStock}"
            ], 422);
        }

        if ($existingItem) {
            $existingItem->increment('cantidad');
        } else {
            SaleItem::create([
                'venta_id'    => $saleId,
                'producto_id' => $product->id,
                'cantidad'    => 1,
                'precio'      => $product->sale_price,
                'nombre'      => $product->name,
                'sku'         => $product->barcode,
            ]);
        }

        $this->recalculateTotal($saleId);

        return response()->json(['success' => true]);
    }

    /**
     * Cambiar cantidad de un renglón
     */
    public function updateQuantity(Request $request, $itemId)
    {
        $item = SaleItem::findOrFail($itemId);
        $newQty = max(1, (int) $request->cantidad);

        // Validar Stock
        $pivot = DB::table('branch_product')
            ->where('product_id', $item->producto_id)
            ->where('branch_id', Auth::user()->branch_id ?? 1)
            ->first();

        $availableStock = $pivot ? $pivot->stock_current : 0;

        if ($newQty > $availableStock) {
            return response()->json([
                'success' => false, 
                'message' => "Supera la existencia disponible ({$availableStock})"
            ], 422);
        }

        $item->update(['cantidad' => $newQty]);
        $this->recalculateTotal($item->venta_id);

        return response()->json(['success' => true]);
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
                $branchId = Auth::user()->branch_id ?? 1;

                if ($sale->items->count() === 0) {
                    throw new \Exception("No hay productos ni servicios en el carrito.");
                }

                // Descontar inventario solo de productos físicos (producto_id > 0)
                foreach ($sale->items as $item) {
                    if ($item->producto_id == 0) {
                        continue; // Omitir servicios
                    }

                    $pivot = DB::table('branch_product')
                        ->where('product_id', $item->producto_id)
                        ->where('branch_id', $branchId)
                        ->lockForUpdate()
                        ->first();

                    $prevStock = $pivot ? $pivot->stock_current : 0;

                    if ($prevStock < $item->cantidad) {
                        throw new \Exception("Stock insuficiente para '{$item->nombre}'. Disponible: {$prevStock}");
                    }

                    $newStock = $prevStock - $item->cantidad;

                    DB::table('branch_product')
                        ->where('product_id', $item->producto_id)
                        ->where('branch_id', $branchId)
                        ->update(['stock_current' => $newStock, 'updated_at' => now()]);

                    InventoryMovement::create([
                        'product_id'     => $item->producto_id,
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

                // Marcar venta como finalizada
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
        
        // Mantener la estructura limpia reiniciando valores en lugar de eliminar la fila de la BD
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

    private function recalculateTotal($saleId)
    {
        $sale = Sale::with('items')->findOrFail($saleId);
        $subtotal = $sale->items->sum(fn($i) => $i->cantidad * $i->precio);
        $total = max(0, $subtotal - $sale->venta_descuento);

        $sale->update(['venta_total' => $total]);
    }

    /**
     * Agregar Servicio Personalizado a la Venta
     */
    public function addService(Request $request, $saleId)
    {
        $request->validate([
            'concept' => 'required|string|max:200',
            'amount'  => 'required|numeric|min:0.01',
        ]);

        $sale = Sale::findOrFail($saleId);

        // Insertar el servicio como un ítem de concepto libre
        SaleItem::create([
            'venta_id'    => $sale->venta_id,
            'producto_id' => 0, // ID 0 indica que es un Servicio
            'cantidad'    => 1,
            'precio'      => $request->amount,
            'nombre'      => '[SERVICIO] ' . trim($request->concept),
            'sku'         => 'SERV',
        ]);

        $this->recalculateTotal($saleId);

        return response()->json(['success' => true]);
    }

    /**
     * Obtener el siguiente número consecutivo de factura para la sucursal activa
     */
    public function getNextInvoiceNumber(Request $request, $saleId)
    {
        $sale = Sale::findOrFail($saleId);

        // Obtener la última factura de la sucursal actual
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