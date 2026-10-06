<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\CreditPayment;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * Consola de Créditos Pendientes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $branches = Branch::all();
        $selectedBranch = $request->get('branch');
        $search = trim($request->get('search'));

        // Query base de ventas a crédito
        $query = Sale::with(['details', 'payments'])
            ->where(function($q) {
                $q->where('venta_tipo', 2)
                  ->orWhere('venta_tipo', '2')
                  ->orWhere('venta_tipo', 'CREDITO')
                  ->orWhere('venta_tipopago', 'LIKE', '%Credito%')
                  ->orWhere('venta_tipopago', 'LIKE', '%CREDITO%');
            })
            ->where('venta_tipo', '!=', 3) // Excluir cancelados
            ->where('venta_tipo', '!=', 'CANCELADO')
            ->where('venta_tipo', '!=', 1) // Excluir liquidados (tipo 1)
            ->where('venta_tipo', '!=', 'PAGADO')
            // VALIDACIÓN MATEMÁTICA: El restante debe ser mayor a cero
            ->whereRaw('(venta_total - venta_abono) > 0');

        // Filtro por Buscador
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('venta_id', 'LIKE', "%{$search}%")
                  ->orWhere('nombre_cliente', 'LIKE', "%{$search}%")
                  ->orWhere('venta_fecha', 'LIKE', "%{$search}%");
            });
        }

        // Control por rol y sucursal
        if (in_array($userRole, [0, 3])) {
            if ($selectedBranch && $selectedBranch !== 'TODOS') {
                $query->where('venta_sucursal', $selectedBranch);
            }
        } else {
            $query->where('venta_sucursal', $userBranch);
            $selectedBranch = $userBranch;
        }

        $credits = $query->latest('venta_fecha')
                         ->paginate(10)
                         ->appends($request->all());

        return view('credits.index', compact('credits', 'branches', 'selectedBranch', 'search', 'userRole', 'userBranch'));
    }

    /**
     * Registrar Abono
     */
    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $id) {
            $sale = Sale::findOrFail($id);
            $monto = (float) $request->monto;

            $restanteActual = $sale->venta_restante > 0 
                ? $sale->venta_restante 
                : ($sale->venta_total - $sale->venta_abono);

            if ($monto > $restanteActual) {
                throw new \Exception("El monto abonado es mayor al saldo pendiente.");
            }

            // 1. Guardar el abono
            CreditPayment::create([
                'venta_id'       => $sale->venta_id,
                'abono_cantidad' => $monto,
                'abono_fecha'    => date('Y-m-d H:i:s'),
                'abono_sucursal' => $sale->venta_sucursal,
            ]);

            // 2. Recalcular
            $nuevoAbonoAcumulado = $sale->venta_abono + $monto;
            $nuevoRestante = $sale->venta_total - $nuevoAbonoAcumulado;

            // Si el restante llega a 0 o menos, actualizamos a 1 (PAGADO/CONTADO)
            $nuevoEstatus = ($nuevoRestante <= 0) ? 1 : 2;

            $sale->update([
                'venta_abono'    => $nuevoAbonoAcumulado,
                'venta_restante' => max(0, $nuevoRestante),
                'venta_tipo'     => $nuevoEstatus,
            ]);
        });

        return redirect()->back()->with('success', 'Abono registrado con éxito.');
    }

    /**
     * Borrado Lógico
     */
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->update(['venta_tipo' => 3]); // 3 = Cancelado

        return redirect()->back()->with('success', 'Venta a crédito cancelada.');
    }
}