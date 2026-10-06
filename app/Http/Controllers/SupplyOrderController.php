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

        // Obtener los productos en la sucursal seleccionada
        $products = Product::whereHas('branches', function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        })->with(['branches' => function ($q) use ($selectedBranchId) {
            $q->where('branch_id', $selectedBranchId);
        }])->orderBy('name', 'asc')->get();

        return view('supply_orders.create', compact(
            'branches', 'selectedBranch', 'selectedBranchId', 'products', 'user'
        ));
    }

    /**
     * Guardar la Orden de Surtido en la Base de Datos
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'products'  => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'quantity'  => 'required|array',
            'notes'     => 'nullable|string|max:500',
        ], [
            'products.required' => 'Debes marcar al menos un producto para generar la orden.'
        ]);

        try {
            $orderId = DB::transaction(function () use ($request) {
                // 1. Crear cabecera de la orden
                $order = SupplyOrder::create([
                    'branch_id' => $request->branch_id,
                    'user_id'   => Auth::id(),
                    'status'    => 'PENDIENTE',
                    'notes'     => $request->notes,
                ]);

                // 2. Insertar detalles
                foreach ($request->products as $productId) {
                    $requestedQty = (int) ($request->quantity[$productId] ?? 5);
                    $stockCurrent = (int) ($request->stock_actual[$productId] ?? 0);

                    SupplyOrderDetail::create([
                        'supply_order_id'   => $order->id,
                        'product_id'        => $productId,
                        'quantity_requested' => $requestedQty,
                        'stock_at_request'   => $stockCurrent,
                    ]);
                }

                return $order->id;
            });

            return redirect()->route('supply-orders.pdf', $orderId);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al procesar la orden: ' . $e->getMessage());
        }
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