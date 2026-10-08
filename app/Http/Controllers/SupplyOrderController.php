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

    public function store(Request $request)
    {
        // 1. Validar la solicitud
        $request->validate([
            'branch_id' => 'required',
            'products'  => 'required|array',
        ]);

        // 2. Crear el registro principal de la orden
        $order = SupplyOrder::create([
            'branch_id' => $request->branch_id,
            'notes'     => $request->notes,
            'user_id'   => auth()->id(),
            'status'    => 'PENDIENTE',
        ]);

        // 3. Adjuntar/Guardar los productos seleccionados y sus cantidades
        foreach ($request->products as $productId) {
            $qty = $request->quantity[$productId] ?? 1;
            $stockActual = $request->stock_actual[$productId] ?? 0;

            $order->items()->create([
                'product_id'         => $productId,
                'quantity_requested' => $qty,         // <--- Nombre exacto de la columna en BD
                'stock_at_request'   => $stockActual,
            ]);
        }

        // 4. Cargar relaciones para la vista PDF
        $order->load(['branch', 'user', 'items.product']);

        // 5. Generar y retornar el PDF directamente (esto es lo que abre el documento en la nueva pestaña)
        $pdf = Pdf::loadView('supply_orders.pdf', compact('order'));
        return $pdf->setPaper('letter', 'portrait')->stream("Orden_Surtido_#{$order->id}.pdf");
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