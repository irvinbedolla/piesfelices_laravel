<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Supplier;
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

        // Determinar el tipo de usuario (priorizando tipo_seccion, type o rol por defecto 2)
        $userType = (int) ($user->role_id ?? $user->role_id ?? 2);

        // -------------------------------------------------------------
        // 1. DETERMINAR SUCURSAL ACTIVA SEGÚN EL TIPO DE USUARIO
        // -------------------------------------------------------------
        if ($userType === 2) {
            // Tipo 2 (Admin de Centro): Forzado estrictamente a su sucursal asignada
            $selectedBranchId = $user->branch_id ?? $matrixBranch?->id ?? 1;
        } else {
            // Tipo 1 (Admin Global) y Tipo 3 (Inventario): Pueden consultar cualquier sucursal
            $selectedBranchId = $request->get('branch_id', $user->branch_id ?? $matrixBranch?->id ?? 1);
        }

        $selectedType = $request->get('type');
        $onlyLowStock = $request->boolean('low_stock');

        // 2. Parámetros de Paginación y Ordenamiento
        $perPage   = $request->get('per_page', 10);
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        
        // 3. Consulta en MySQL para Bajo Stock Real
        $query = Product::whereHas('branches', function ($q) use ($selectedBranchId, $onlyLowStock) {
            $q->where('branch_id', $selectedBranchId);
            
            if ($onlyLowStock) {
                // Opción A: Productos que están por debajo del stock mínimo Y que sí tienen/tuvieron inventario registrado
                $q->whereColumn('branch_product.stock_current', '<=', 'branch_product.stock_min')
                ->where(function ($sub) use ($selectedBranchId) {
                    // Exige que la sucursal haya tenido al menos 1 movimiento en Kárdex o que el producto no sea 'fantasma'
                    $sub->where('branch_product.stock_current', '>', 0)
                        ->orWhereExists(function ($movQuery) use ($selectedBranchId) {
                            $movQuery->select(DB::raw(1))
                                ->from('inventory_movements')
                                ->whereColumn('inventory_movements.product_id', 'branch_product.product_id')
                                ->where('inventory_movements.branch_id', $selectedBranchId);
                        });
                });
            } else {
                // Por defecto: Muestra SOLO productos con existencias activas en esta sucursal
                $q->where('branch_product.stock_current', '>', 0);
            }
        })->with(['branches' => function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        }])
        ->orderBy('name', 'asc');

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

        // -------------------------------------------------------------
        // 7. MÉTRICAS FINANCIERAS Y STOCK (EXCLUSIVO ADMINISTRADOR)
        // -------------------------------------------------------------
        $totalArticulos   = 0;
        $totalPiezasStock = 0;
        $inversionTotal   = 0;
        $gananciaEstimada = 0;

        // Variables por categoría
        $statsByType = [
            'GENERAL'     => ['pieces' => 0, 'investment' => 0],
            'CALZADO'     => ['pieces' => 0, 'investment' => 0],
            'MEDICAMENTO' => ['pieces' => 0, 'investment' => 0],
        ];

        // Solo el Tipo 1 se considera Administrador Financiero completo
        $isAdmin = method_exists($user, 'isAdmin') 
            ? $user->isAdmin() 
            : ($userType === 1);

        if ($isAdmin) {
            $totalArticulos = Product::whereHas('branches', function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            })->count();

            // Stats Globales de la sucursal activa
            $stats = DB::table('branch_product')
                ->join('products', 'branch_product.product_id', '=', 'products.id')
                ->where('branch_product.branch_id', $selectedBranchId)
                ->select(
                    DB::raw('SUM(branch_product.stock_current) as total_stock'),
                    DB::raw('SUM(branch_product.stock_current * COALESCE(products.cost_price, 0)) as costo_total'),
                    DB::raw('SUM(branch_product.stock_current * COALESCE(products.sale_price, 0)) as venta_total')
                )
                ->first();

            $totalPiezasStock = $stats->total_stock ?? 0;
            $inversionTotal   = $stats->costo_total ?? 0;
            $valorVentaTotal  = $stats->venta_total ?? 0;
            $gananciaEstimada = max(0, $valorVentaTotal - $inversionTotal);

            // Stats Desglosadas por Tipo de Producto (GENERAL, CALZADO, MEDICAMENTO)
            $byTypeQuery = DB::table('branch_product')
                ->join('products', 'branch_product.product_id', '=', 'products.id')
                ->where('branch_product.branch_id', $selectedBranchId)
                ->select(
                    'products.type',
                    DB::raw('SUM(branch_product.stock_current) as pieces'),
                    DB::raw('SUM(branch_product.stock_current * COALESCE(products.cost_price, 0)) as investment')
                )
                ->groupBy('products.type')
                ->get();

            foreach ($byTypeQuery as $row) {
                $typeKey = strtoupper($row->type);
                if (isset($statsByType[$typeKey])) {
                    $statsByType[$typeKey]['pieces']     = (int) $row->pieces;
                    $statsByType[$typeKey]['investment'] = (float) $row->investment;
                }
            }

        }
        $suppliers = Supplier::all();

        return view('inventory.index', compact(
            'products', 'branches', 'matrixBranch', 'selectedBranchId', 
            'selectedType', 'onlyLowStock', 'productsJson', 'user', 'userType', 
            'perPage', 'sortBy', 'sortOrder', 'totalArticulos', 'totalPiezasStock', 
            'inversionTotal', 'gananciaEstimada', 'isAdmin', 'statsByType'
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
            'barcode'       => 'nullable|string|max:100',
            //'category_id'   => 'nullable|exists:categories,id', // Opcional si agregas select
            'supplier_id'   => 'nullable|exists:suppliers,id',  // Opcional si agregas select
            'model'         => 'nullable|string|max:100',
            'color'         => 'nullable|string|max:100',
            'page_number'   => 'nullable|integer',
            'substance'     => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $matrixBranch = Branch::getMatrixBranch();

                // Determinar la categoría en el backend como respaldo si no viniera en el request
                $categoryId = match(strtoupper($request->type)) {
                    'CALZADO'     => 3,
                    'MEDICAMENTO' => 2,
                    default       => 4,
                };

                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('products', 'public');
                }

                $productType = strtoupper(trim($request->type));
                $isFootwear = ($productType === 'CALZADO');
                $isMedicine = ($productType === 'MEDICAMENTO');

                $product = Product::create([
                    'name'        => trim($request->name),
                    'type'        => $productType,
                    'barcode'     => $request->filled('barcode') ? trim($request->barcode) : null,
                    'cost_price'  => $request->cost_price,
                    'sale_price'  => $request->sale_price,
                    
                    // ASIGNACIÓN DE LOS 3 CAMPOS DE LA IMAGEN:
                    'category_id' => $request->get('category_id', $categoryId),
                    'supplier_id' => $request->get('supplier_id', null),
                    'branch_id'   => $request->get('branch_id', $matrixBranch?->id ?? 1),

                   'model'       => $request->type === 'CALZADO' ? $request->model : null,
                    'color'       => $request->type === 'CALZADO' ? $request->color : null,
                    'page_number' => $request->type === 'CALZADO' ? $request->page_number : null,
                    'substance'   => $request->type === 'MEDICAMENTO' ? $request->substance : null,
                    
                    'image'       => $imagePath,
                    'status'      => true,
                ]);

                // Asignar stock pivote en Matriz Central
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

        if ($request->type === 'ENTRADA' && !in_array($userType, [1, 3])) {
            return redirect()->back()->with('error', 'No tienes permisos para agregar artículos al inventario general.');
        }

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