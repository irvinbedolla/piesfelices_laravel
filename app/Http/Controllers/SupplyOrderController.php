<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class SupplyOrderController extends Controller
{
    /**
     * Vista de creación de la Orden de Surtido
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('status', true)->get();
        $matrixBranch = Branch::getMatrixBranch();

        // Determinar la sucursal de trabajo
        if ($user->type == 1 && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        } else {
            $selectedBranchId = $request->get('branch_id', $matrixBranch?->id);
        }

        $selectedBranch = Branch::find($selectedBranchId);

        // Cantidad de productos a mostrar por página
        $perPage = $request->get('per_page', 10);

        // Obtener los productos en la sucursal seleccionada paginados
        $products = Product::whereHas('branches', function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        })->with(['branches' => function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        }])
        ->orderBy('name', 'asc')
        ->paginate($perPage)
        ->withQueryString(); // Mantiene branch_id y demás parámetros al cambiar de página

        return view('supply_orders.pdf', compact(
            'branches', 'selectedBranch', 'selectedBranchId', 'products', 'user'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items'     => 'required|array',
            'notes'     => 'nullable|string|max:1000',
        ]);

        // Filtrar únicamente los productos cuya casilla fue marcada
        $selectedItems = array_filter($request->items, function ($item) {
            return isset($item['selected']) && $item['selected'] == '1';
        });

        if (empty($selectedItems)) {
            return redirect()->back()->with('error', 'Debes seleccionar al menos un producto para generar la orden de surtido.');
        }

        DB::transaction(function () use ($request, $selectedItems, &$order) {
            // 1. Crear el encabezado de la Orden
            $order = SupplyOrder::create([
                'user_id'    => auth()->id(),
                'branch_id'  => $request->branch_id,
                'status'     => 'PENDIENTE',
                'notes'      => $request->notes,
                'created_at' => now(),
            ]);

            // 2. Insertar solo los ítems seleccionados con la cantidad definida por el usuario
            foreach ($selectedItems as $item) {
                $productId = $item['product_id'];
                $quantity  = max(1, (int) $item['quantity']);

                // Obtener el stock actual de la sucursal
                $pivot = DB::table('branch_product')
                    ->where('product_id', $productId)
                    ->where('branch_id', $request->branch_id)
                    ->first();

                $order->items()->create([
                    'product_id'         => $productId,
                    'current_stock'      => $pivot ? $pivot->stock_current : 0,
                    'suggested_quantity' => $quantity,
                ]);
            }
        });

        // Abrir el PDF generado
        return redirect()->route('supply-orders.pdf', $order->id);
    }

    /**
     * Descargar o visualizar el PDF de la Orden de Surtido
     */
    public function pdf(SupplyOrder $order)
    {
        $order->load(['branch', 'user', 'details.product']);

        $pdf = Pdf::loadView('supply_orders.pdf', compact('order'))
            ->setPaper('a4', 'portrait');

        $filename = 'orden_surtido_#' . $order->id . '_' . date('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    public function details()
    {
        return $this->hasMany(SupplyOrderDetail::class, 'supply_order_id');
    }
}