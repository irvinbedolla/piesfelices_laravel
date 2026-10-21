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

        $fecha1 = $request->get('fecha1', date('Y-m-d'));
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
        $authUser = auth()->user();
        $roleId   = (int) ($authUser->role_id ?? $authUser->user_type ?? $authUser->type ?? 2);

        // 1. Validar que el usuario tenga rol autorizado (Rol 1 o Rol 2)
        if (!in_array($roleId, [1, 2])) {
            return redirect()->back()->with('error', 'No tienes permisos para eliminar préstamos.');
        }

        $loan = Loan::findOrFail($id);

        // 2. Si es Admin de Centro (Rol 2), validar estricta pertenencia de sucursal
        if ($roleId === 2) {
            $userBranch = $authUser->branch?->name ?? $authUser->sucursal;
            
            if ($loan->sucursal !== $userBranch) {
                return redirect()->back()->with('error', 'No puedes eliminar préstamos de otra sucursal.');
            }
        }

        // 3. Eliminar el registro
        $loan->delete();

        return redirect()->back()->with('success', 'Préstamo eliminado correctamente.');
    }

    /**
     * MÓDULO 2: CONTROL DE PRÉSTAMOS loansIndex
    */
    public function loansIndex(Request $request)
    {
        $authUser = auth()->user();
        
        // 1. Identificar el tipo de rol y permisos
        $roleId = (int) ($authUser->role_id ?? $authUser->user_type ?? 2);
        $isAdmin = in_array($roleId, [1, 2]);

        $viewMode = $request->get('mode', 'activos');

        // 2. Obtener la lista general de sucursales activas
        $branches = Branch::where('status', true)->get();

        // 3. DETERMINAR LA SUCURSAL SELECCIONADA/ACTIVA ($selectedBranch y $selectedBranchId)
        if ($roleId === 1) {
            // SuperAdmin (Rol 1): Puede filtrar por la sucursal enviada en la URL o ver la de su perfil por defecto
            $selectedBranchId = $request->get('branch_id', $authUser->branch_id ?? $branches->first()?->id ?? 1);
        } else {
            // Admin de Centro (Rol 2) y Empleados: Forzados estrictamente a su sucursal asignada
            $selectedBranchId = $authUser->branch_id ?? 1;
        }

        $selectedBranch = Branch::find($selectedBranchId);

        // 4. Filtrar los usuarios para el modal según el alcance del rol
        if ($roleId === 1) {
            // SuperAdmin: Todos los usuarios activos
            $users = User::where('status', 'Activo')->orderBy('username', 'asc')->get();
        } elseif ($roleId === 2) {
            // Admin de Centro: Únicamente los usuarios de su sucursal
            $users = User::where('status', 'Activo')
                ->where('branch_id', $authUser->branch_id)
                ->orderBy('username', 'asc')
                ->get();
        } else {
            $users = collect([$authUser]);
        }

        // 5. Consulta Base de Préstamos
        $query = Loan::with('user');

        if ($roleId === 1) {
            if ($viewMode === 'mis_prestamos') {
                $query->where('user_id', $authUser->id);
            } elseif ($viewMode === 'historial') {
                $query->where('status', 'Pagado')->orWhere('pres_restante', 0);
            } else {
                $query->where('status', 'Activo');
            }
        } elseif ($roleId === 2) {
            $branchName = $authUser->branch?->name ?? $authUser->sucursal;

            if ($viewMode === 'mis_prestamos') {
                $query->where('user_id', $authUser->id);
            } elseif ($viewMode === 'historial') {
                $query->where('sucursal', $branchName)->where('status', 'Pagado')->orWhere('pres_restante', 0);
            } else {
                $query->where('sucursal', $branchName)->where('status', 'Activo');
            }
        } else {
            $viewMode = 'mis_prestamos';
            $query->where('user_id', $authUser->id);
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(10);

        // 6. INCLUIR $selectedBranch Y $selectedBranchId EN EL COMPACT
        return view('loans.index', compact(
            'loans', 
            'isAdmin', 
            'viewMode', 
            'roleId', 
            'users', 
            'branches', 
            'selectedBranch', 
            'selectedBranchId'
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

        $authUser = auth()->user();
        $targetUser = User::with('branch')->findOrFail($request->user_id);
        // Si la request no envía sucursal, toma la del usuario destino o la del admin autenticado
        $userBranch = $request->sucursal 
        ?? ($targetUser->branch?->name ?? $authUser->branch?->name ?? 'MATRIZ');


        //$targetUser = User::findOrFail($request->user_id);
        //$userBranch = $request->sucursal ?? ($targetUser->branch?->name ?? 'MATRIZ');

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