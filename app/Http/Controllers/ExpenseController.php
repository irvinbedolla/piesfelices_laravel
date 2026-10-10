<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role_id ?? $user->tipo_seccion ?? $user->type ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $fecha1 = $request->get('fecha1', date('Y-m-01'));
        $fecha2 = $request->get('fecha2', date('Y-m-d'));
        $sucursalSel = $request->get('sucursal');
        $search = trim($request->get('search')); // <--- Capturar texto a buscar

        $isAdmin = in_array($userRole, [1]);

        if ($isAdmin) {
            $branches = Branch::all();
            $selectedBranch = $sucursalSel ?? 'TODOS';
        } else {
            $branches = Branch::where('name', $userBranch)->get();
            $selectedBranch = $userBranch;
        }

        // 1. Consulta base
        $query = Expense::where('status', 'Activo')
            ->whereBetween('fecha', [$fecha1, $fecha2]);

        // 2. Filtro por sucursal
        if ($isAdmin) {
            if (!empty($selectedBranch) && $selectedBranch !== 'TODOS') {
                $query->where('sucursal', $selectedBranch);
            }
        } else {
            $query->where('sucursal', $userBranch);
        }

        // 3. Filtro por texto de búsqueda (Concepto / Cantidad)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('concepto', 'LIKE', "%{$search}%")
                ->orWhere('cantidad', 'LIKE', "%{$search}%");
            });
        }

        // 4. Calcular los totales sobre los registros filtrados
        $allFilteredExpenses = (clone $query)->get();

        $totalConsultorio = $allFilteredExpenses->where('tipo', 2)->sum('cantidad');
        $totalProveedor   = $allFilteredExpenses->where('tipo', 1)->sum('cantidad');
        $totalSueldos     = $allFilteredExpenses->where('tipo', 3)->sum('cantidad');
        $totalGeneral     = $allFilteredExpenses->sum('cantidad');

        // 5. Paginar resultados manteniendo query strings
        $expenses = $query->latest('fecha')
                        ->latest('id')
                        ->paginate(10)
                        ->appends($request->all());

        return view('expenses.index', compact(
            'expenses', 'branches', 'fecha1', 'fecha2', 'selectedBranch', 'search',
            'totalConsultorio', 'totalProveedor', 'totalSueldos', 'totalGeneral',
            'userRole', 'user', 'userBranch', 'isAdmin'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'cantidad'  => 'required|numeric|min:0.01',
            'fecha'    => 'required|date',
            'tipo'     => 'required|integer',
        ]);

        $user = Auth::user();
        $userRole = $user->role_id ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $branchName = in_array($userRole, [1]) 
            ? ($request->sucursal ?? 'MATRIZ') 
            : $userBranch;

        Expense::create([
            'concepto' => $request->concepto,
            'cantidad' => $request->cantidad,
            'fecha'    => $request->fecha,
            'sucursal' => $branchName,
            'tipo'     => $request->tipo,
            'status'   => 'Activo',
            'user_id'  => $user->id,
        ]);

        return redirect()->back()->with('success', 'Gasto registrado correctamente.');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->update(['status' => 'Inactivo']);

        return redirect()->back()->with('success', 'Gasto eliminado.');
    }

    /**
     * MÓDULO 2: CONTROL DE PRÉSTAMOS
     */
    public function loansIndex(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role_id ?? $user->tipo_seccion ?? $user->type ?? 0;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $branches = Branch::all();
        $users = User::all();

        $viewMode = $request->get('mode', 'activos'); // 'activos', 'historial', 'mis_prestamos'
        $selectedBranch = $request->get('sucursal', $request->get('branch'));
        $search = trim($request->get('search'));

        $isAdmin = in_array($userRole, [0, 1]);

        if (!$isAdmin) {
            $selectedBranch = $userBranch;
        }

        // Cargar la relación 'user' para obtener el nombre real del empleado
        $query = Loan::with(['user', 'payments']);

        // 1. Filtrado por Pestaña / Modo
        if ($viewMode === 'mis_prestamos') {
            $query->where('user_id', $user->id);
        } elseif ($viewMode === 'historial') {
            // Historial: Muestra los préstamos liquidados/pagados (restante = 0)
            $query->where('pres_restante', '<=', 0);
        } else {
            // Activos (por defecto): Préstamos con saldo pendiente (restante > 0)
            $query->where('pres_restante', '>', 0);
        }

        // 2. Filtro por Sucursal
        if ($isAdmin && $viewMode !== 'mis_prestamos') {
            if (!empty($selectedBranch) && $selectedBranch !== 'TODOS') {
                $query->where('sucursal', $selectedBranch);
            }
        } else {
            $query->where('sucursal', $userBranch);
        }

        // 3. Buscador
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('pres_descripcion', 'LIKE', "%{$search}%")
                ->orWhere('pres_id', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function($uQuery) use ($search) {
                    $uQuery->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        $loans = $query->latest('pres_fecha')
                    ->latest('pres_id')
                    ->paginate(10)
                    ->appends($request->all());

        return view('loans.index', compact(
            'loans', 'branches', 'users', 'viewMode', 'userRole', 
            'user', 'userBranch', 'selectedBranch', 'isAdmin', 'search'
        )); 
    }

    public function storeLoan(Request $request)
    {
        $request->validate([
            'pres_descripcion' => 'required|string|max:255',
            'pres_costo'       => 'required|numeric|min:1',
            'pres_fecha'       => 'required|date',
            'user_id'          => 'required|integer',
        ]);

        $targetUser = User::findOrFail($request->user_id);
        $userBranch = $request->sucursal ?? ($targetUser->branch?->name ?? 'MATRIZ');

        Loan::create([
            'pres_descripcion' => $request->pres_descripcion,
            'pres_costo'       => $request->pres_costo,
            'pres_restante'    => $request->pres_costo,
            'pres_fecha'       => $request->pres_fecha,
            'usuario'          => $targetUser->name ?? $targetUser->username,
            'user_id'          => $targetUser->id,
            'sucursal'         => $userBranch,
            'status'           => 'Activo',
        ]);

        return redirect()->back()->with('success', 'Préstamo registrado exitosamente.');
    }

    public function storePayment(Request $request, $loanId)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $loanId) {
            $loan = Loan::findOrFail($loanId);
            $montoAbono = (float) $request->monto;

            if ($montoAbono > $loan->pres_restante) {
                throw new \Exception("El abono no puede ser mayor al saldo restante.");
            }

            LoanPayment::create([
                'idPrestamo'   => $loan->pres_id,
                'monto'        => $montoAbono,
                'fechaPretamo' => $request->fecha,
                'sucursal'     => $loan->sucursal,
            ]);

            $nuevoRestante = $loan->pres_restante - $montoAbono;
            $loan->update(['pres_restante' => $nuevoRestante]);
        });

        return redirect()->back()->with('success', 'Abono registrado con éxito.');
    }

    public function destroyLoan($id)
    {
        $loan = Loan::findOrFail($id);
        $loan->delete();

        return redirect()->back()->with('success', 'Préstamo eliminado.');
    }

    public function getPayments($id)
    {
        // Carga los abonos asociados al préstamo
        $payments = LoanPayment::where('idPrestamo', $id) // O la columna foránea que use tu tabla abonos
                    ->orderBy('id', 'desc')
                    ->get();

        return response()->json($payments);
    }
}