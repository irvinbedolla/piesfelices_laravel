<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;


class InventoryController extends Controller
{
    /**
     * Vista principal de Inventario con Filtros (Bajo Stock / Tipo) e Imagen
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('status', true)->get();
        $matrixBranch = Branch::getMatrixBranch();

        // 1. Determinar sucursal activa
        if ($user->type == 1 && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        } else {
            $selectedBranchId = $request->get('branch_id', $matrixBranch?->id);
        }

        $selectedType = $request->get('type');
        $onlyLowStock = $request->boolean('low_stock');

        // 2. Parámetros de Paginación y Ordenamiento
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // 3. Consulta optimizada en MySQL (JOIN / WhereHas)
        $query = Product::whereHas('branches', function ($q) use ($selectedBranchId, $onlyLowStock) {
            $q->where('branch_id', $selectedBranchId);
            
            // Filtro conducido directamente por MySQL para Bajo Stock
            if ($onlyLowStock) {
                $q->whereColumn('branch_product.stock_current', '<=', 'branch_product.stock_min');
            }else{
                // Por defecto, mostrar únicamente artículos que tengan stock (stock_current > 0)
                $q->where('branch_product.stock_current', '>', 0);
            }

        })->with(['branches' => function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        }]);

        // 4. Filtro por Tipo de Producto (GENERAL, CALZADO, MEDICAMENTO)
        if ($selectedType && in_array($selectedType, ['GENERAL', 'CALZADO', 'MEDICAMENTO'])) {
            $query->where('type', $selectedType);
        }

        // 5. Buscador Global ejecutado en MySQL
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('color', 'like', "%{$search}%")
                ->orWhere('substance', 'like', "%{$search}%");
            });
        }

        // 6. Ordenamiento directo
        $allowedSorts = ['name', 'barcode', 'sale_price', 'type', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Paginación nativa manteniendo los parámetros de la URL
        $products = $query->paginate($perPage)->withQueryString();

        // Arreglo para el buscador de traspasos
        $productsJson = $products->getCollection()->map(function ($p) use ($matrixBranch) {
            return [
                'id'      => $p->id,
                'name'    => $p->name ?? '',
                'barcode' => $p->barcode ?? '',
                'type'    => $p->type ?? 'GENERAL',
                'model'   => $p->model ?? '',
                'stock'   => $p->getStockInBranch($matrixBranch->id ?? 1)
            ];
        });

        return view('inventory.index', compact(
            'products', 'branches', 'matrixBranch', 'selectedBranchId', 
            'selectedType', 'onlyLowStock', 'productsJson', 'user', 'perPage', 'sortBy', 'sortOrder'
        ));
    }

    /**
     * Guardar un nuevo producto con Imagen opcional
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:GENERAL,CALZADO,MEDICAMENTO',
            'cost_price'    => 'required|numeric|min:0',
            'sale_price'    => 'required|numeric|min:0',
            'initial_stock' => 'required|integer|min:0',
            'stock_min'     => 'required|integer|min:1',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $matrixBranch = Branch::getMatrixBranch();

                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('products', 'public');
                }

                $product = Product::create([
                    'name'        => $request->name,
                    'type'        => $request->type,
                    'barcode'     => $request->barcode,
                    'cost_price'  => $request->cost_price,
                    'sale_price'  => $request->sale_price,
                    'model'       => $request->type === 'CALZADO' ? $request->model : null,
                    'color'       => $request->type === 'CALZADO' ? $request->color : null,
                    'page_number' => $request->type === 'CALZADO' ? $request->page_number : null,
                    'substance'   => $request->type === 'MEDICAMENTO' ? $request->substance : null,
                    'image'       => $imagePath,
                ]);

                // Asignar stock en Matriz Central
                DB::table('branch_product')->insert([
                    'product_id'    => $product->id,
                    'branch_id'     => $matrixBranch->id,
                    'stock_current' => $request->initial_stock,
                    'stock_min'     => $request->stock_min,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // Movimiento inicial Kárdex
                InventoryMovement::create([
                    'product_id'     => $product->id,
                    'user_id'        => Auth::id(),
                    'branch_id'      => $matrixBranch->id,
                    'type'           => 'ENTRADA',
                    'quantity'       => $request->initial_stock,
                    'previous_stock' => 0,
                    'new_stock'      => $request->initial_stock,
                    'reference'      => 'Alta de Producto (Ingreso Inicial)',
                    'notes'          => 'Ingreso inicial a inventario matriz',
                ]);
            });

            return redirect()->back()->with('success', 'Producto registrado correctamente en la Matriz.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear producto: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar producto incluyendo reemplazo de imagen
     */
    public function updateProduct(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:GENERAL,CALZADO,MEDICAMENTO',
            'sale_price'  => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'barcode'     => 'nullable|string|max:100',
            'model'       => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:100',
            'page_number' => 'nullable|integer',
            'substance'   => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Validación de imagen (máx 2MB)
        ]);

        try {
            $data = [
                'name'        => $request->name,
                'type'        => $request->type,
                'barcode'     => $request->barcode,
                'sale_price'  => $request->sale_price,
                'cost_price'  => $request->cost_price ?? $product->cost_price,
                'model'       => $request->type === 'CALZADO' ? $request->model : null,
                'color'       => $request->type === 'CALZADO' ? $request->color : null,
                'page_number' => $request->type === 'CALZADO' ? $request->page_number : null,
                'substance'   => $request->type === 'MEDICAMENTO' ? $request->substance : null,
            ];

            // Manejo de la subida / reemplazo de la imagen en MySQL y Storage
            if ($request->hasFile('image')) {
                // Eliminar la imagen anterior si existe
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                // Guardar la nueva imagen en storage/app/public/products
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            return redirect()->back()->with('success', 'Producto e imagen actualizados correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Historial Kárdex de Movimientos con Paginación Dinámica
     */
    public function movements(Request $request)
    {
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = InventoryMovement::with(['product', 'user', 'branch', 'destinationBranch']);

        // Buscador global en Kárdex
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhereHas('product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            });
        }

        $movements = $query->orderBy($sortBy, $sortOrder)
                        ->paginate($perPage)
                        ->withQueryString();

        return view('inventory.movements', compact('movements', 'perPage', 'sortBy', 'sortOrder'));
    }

    /**
     * Realizar un traspaso de inventario entre la Sucursal Matriz y una Sucursal Destino
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_id'             => 'required|exists:products,id',
            'destination_branch_id' => 'required|exists:branches,id',
            'quantity'              => 'required|integer|min:1',
            'notes'                 => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $matrixBranch = Branch::getMatrixBranch();
                $productId    = $request->product_id;
                $destBranchId = $request->destination_branch_id;
                $quantity     = (int) $request->quantity;

                if ($matrixBranch->id == $destBranchId) {
                    throw new \Exception("La sucursal destino no puede ser la misma Sucursal Matriz.");
                }

                // 1. Verificar stock disponible en Matriz
                $matrixPivot = DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $matrixBranch->id)
                    ->lockForUpdate()
                    ->first();

                $matrixPreviousStock = $matrixPivot ? $matrixPivot->stock_current : 0;

                if ($matrixPreviousStock < $quantity) {
                    throw new \Exception("Stock insuficiente en Matriz Central. Disponible: {$matrixPreviousStock}");
                }

                // 2. Descontar de Matriz
                $matrixNewStock = $matrixPreviousStock - $quantity;
                DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $matrixBranch->id)
                    ->update(['stock_current' => $matrixNewStock, 'updated_at' => now()]);

                // 3. Aumentar stock en la Sucursal Destino
                $destPivot = DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $destBranchId)
                    ->lockForUpdate()
                    ->first();

                $destPreviousStock = $destPivot ? $destPivot->stock_current : 0;
                $destNewStock      = $destPreviousStock + $quantity;

                if ($destPivot) {
                    DB::table('branch_product')
                        ->where('product_id', $productId)
                        ->where('branch_id', $destBranchId)
                        ->update(['stock_current' => $destNewStock, 'updated_at' => now()]);
                } else {
                    DB::table('branch_product')->insert([
                        'product_id'    => $productId,
                        'branch_id'     => $destBranchId,
                        'stock_current' => $destNewStock,
                        'stock_min'     => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // 4. Registrar Movimiento Inmutable en Kárdex
                $destBranchName = Branch::find($destBranchId)->name ?? 'Sucursal Destino';

                InventoryMovement::create([
                    'product_id'            => $productId,
                    'user_id'               => Auth::id(),
                    'branch_id'             => $matrixBranch->id,
                    'destination_branch_id' => $destBranchId,
                    'type'                  => 'TRASPASO',
                    'quantity'              => $quantity,
                    'previous_stock'        => $matrixPreviousStock,
                    'new_stock'             => $matrixNewStock,
                    'reference'             => "Traspaso a {$destBranchName}",
                    'notes'                 => $request->notes,
                ]);
            });

            return redirect()->back()->with('success', 'Traspaso de inventario realizado correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en traspaso: ' . $e->getMessage());
        }
    }

    /**
     * Alias por si la ruta o llamada invoca transferStock
     */
    public function transferStock(Request $request)
    {
        return $this->transfer($request);
    }

    /**
     * Registrar Ajuste/Entrada/Salida/Devolución de stock en la sucursal seleccionada
     */
    public function adjustStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id'  => 'required|exists:branches,id',
            'type'       => 'required|in:ENTRADA,SALIDA,AJUSTE,DEVOLUCION',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $productId = $request->product_id;
                $branchId  = $request->branch_id;
                $quantity  = (int) $request->quantity;

                // 1. Consultar registro de inventario en la sucursal
                $pivot = DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->first();

                $previousStock = $pivot ? $pivot->stock_current : 0;

                if (in_array($request->type, ['SALIDA', 'AJUSTE']) && $quantity > $previousStock) {
                    throw new \Exception("Stock insuficiente en la sucursal. Disponible: {$previousStock}");
                }

                $newStock = in_array($request->type, ['ENTRADA', 'DEVOLUCION'])
                    ? $previousStock + $quantity
                    : $previousStock - $quantity;

                // 2. Actualizar o crear fila en la tabla pivote branch_product
                if ($pivot) {
                    DB::table('branch_product')
                        ->where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->update(['stock_current' => $newStock, 'updated_at' => now()]);
                } else {
                    DB::table('branch_product')->insert([
                        'product_id'    => $productId,
                        'branch_id'     => $branchId,
                        'stock_current' => $newStock,
                        'stock_min'     => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // 3. Registrar Movimiento en Kárdex
                InventoryMovement::create([
                    'product_id'     => $productId,
                    'user_id'        => Auth::id(),
                    'branch_id'      => $branchId,
                    'type'           => $request->type,
                    'quantity'       => $quantity,
                    'previous_stock' => $previousStock,
                    'new_stock'      => $newStock,
                    'reference'      => $request->reference ?? 'Ajuste manual de inventario',
                    'notes'          => $request->notes,
                ]);
            });

            return redirect()->back()->with('success', 'Movimiento de inventario registrado correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en ajuste de stock: ' . $e->getMessage());
        }
    }

    /**
     * Método para devolución a matriz (por admin de sucursal)
     */
    public function returnToMatrix(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = Auth::user();
                $matrixBranch = Branch::getMatrixBranch();
                $productId = $request->product_id;
                $quantity = (int) $request->quantity;
                $userBranchId = $user->branch_id;

                if (!$userBranchId) {
                    throw new \Exception("El usuario no tiene una sucursal asignada.");
                }

                // Descontar de la sucursal del usuario
                $userPivot = DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $userBranchId)
                    ->lockForUpdate()
                    ->first();

                $userPrevStock = $userPivot ? $userPivot->stock_current : 0;

                if ($userPrevStock < $quantity) {
                    throw new \Exception("Stock insuficiente para devolver. Disponible: {$userPrevStock}");
                }

                $userNewStock = $userPrevStock - $quantity;
                DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $userBranchId)
                    ->update(['stock_current' => $userNewStock, 'updated_at' => now()]);

                // Sumar a la Matriz Central
                $matrixPivot = DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $matrixBranch->id)
                    ->lockForUpdate()
                    ->first();

                $matrixPrevStock = $matrixPivot ? $matrixPivot->stock_current : 0;
                $matrixNewStock = $matrixPrevStock + $quantity;

                if ($matrixPivot) {
                    DB::table('branch_product')
                        ->where('product_id', $productId)
                        ->where('branch_id', $matrixBranch->id)
                        ->update(['stock_current' => $matrixNewStock, 'updated_at' => now()]);
                } else {
                    DB::table('branch_product')->insert([
                        'product_id'    => $productId,
                        'branch_id'     => $matrixBranch->id,
                        'stock_current' => $matrixNewStock,
                        'stock_min'     => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // Registrar movimiento en Kárdex
                InventoryMovement::create([
                    'product_id'            => $productId,
                    'user_id'               => Auth::id(),
                    'branch_id'             => $userBranchId,
                    'destination_branch_id' => $matrixBranch->id,
                    'type'                  => 'DEVOLUCION',
                    'quantity'              => $quantity,
                    'previous_stock'        => $userPrevStock,
                    'new_stock'             => $userNewStock,
                    'reference'             => 'Devolución a Matriz Central',
                    'notes'                 => $request->notes,
                ]);
            });

            return redirect()->back()->with('success', 'Devolución a Matriz realizada con éxito.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en devolución: ' . $e->getMessage());
        }
    }

    /**
     * Generar Reporte PDF del Inventario según Filtros Activos
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $matrixBranch = Branch::getMatrixBranch();

        // 1. Determinar sucursal seleccionada
        if ($user->type == 1 && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        } else {
            $selectedBranchId = $request->get('branch_id', $matrixBranch?->id);
        }

        $branch = Branch::find($selectedBranchId);
        $selectedType = $request->get('type');
        $onlyLowStock = $request->boolean('low_stock');

        // 2. Consulta idéntica con filtros aplicados en MySQL
        $query = Product::whereHas('branches', function ($q) use ($selectedBranchId, $onlyLowStock) {
            $q->where('branch_id', $selectedBranchId);

            if ($onlyLowStock) {
                $q->whereColumn('branch_product.stock_current', '<=', 'branch_product.stock_min');
            } else {
                $q->where('branch_product.stock_current', '>', 0);
            }
        })->with(['branches' => function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        }]);

        // 3. Filtro por Tipo
        if ($selectedType && in_array($selectedType, ['GENERAL', 'CALZADO', 'MEDICAMENTO'])) {
            $query->where('type', $selectedType);
        }

        // 4. Buscador Global
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('color', 'like', "%{$search}%")
                ->orWhere('substance', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name', 'asc')->get();

        // 5. Cargar vista para el PDF
        $pdf = Pdf::loadView('inventory.pdf', compact(
            'products', 'branch', 'selectedType', 'onlyLowStock', 'user'
        ))->setPaper('a4', 'portrait');

        $filename = 'inventario_' . strtolower(str_replace(' ', '_', $branch->name ?? 'sucursal')) . '_' . date('Y-m-d_Hi') . '.pdf';

        return $pdf->stream($filename);
    }

}