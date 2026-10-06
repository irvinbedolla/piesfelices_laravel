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
    /**
     * MÓDULO 1: CONTROL DE GASTOS CON PERMISOS POR SEDE
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $fecha1 = $request->get('fecha1', date('Y-m-01'));
        $fecha2 = $request->get('fecha2', date('Y-m-d'));
        $sucursalSel = $request->get('sucursal');

        if (in_array($userRole, [0, 3])) {
            $branches = Branch::all();
            $selectedBranch = $sucursalSel ?? 'TODOS';
        } else {
            $branches = Branch::where('name', $userBranch)->get();
            $selectedBranch = $userBranch;
        }

        $query = Expense::where('status', 'Activo')
            ->whereBetween('fecha', [$fecha1, $fecha2]);

        if (in_array($userRole, [0, 3])) {
            if ($selectedBranch && $selectedBranch !== 'TODOS') {
                $query->where('sucursal', $selectedBranch);
            }
        } else {
            $query->where('sucursal', $userBranch);
        }

        $expenses = $query->latest('fecha')->get();

        $totalConsultorio = $expenses->where('tipo', 2)->sum('cantida');
        $totalProveedor   = $expenses->where('tipo', 1)->sum('cantida');
        $totalSueldos     = $expenses->where('tipo', 3)->sum('cantida');
        $totalGeneral     = $expenses->sum('cantida');

        return view('expenses.index', compact(
            'expenses', 'branches', 'fecha1', 'fecha2', 'selectedBranch',
            'totalConsultorio', 'totalProveedor', 'totalSueldos', 'totalGeneral',
            'userRole', 'user', 'userBranch'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'cantida'  => 'required|numeric|min:0.01',
            'fecha'    => 'required|date',
            'tipo'     => 'required|integer',
        ]);

        $user = Auth::user();
        $userRole = $user->tipo_seccion ?? 2;
        $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';

        $branchName = in_array($userRole, [0, 3]) 
            ? ($request->sucursal ?? 'MATRIZ') 
            : $userBranch;

        Expense::create([
            'concepto' => $request->concepto,
            'cantida'  => $request->cantida,
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
        $userRole = $user->role_id ?? $user->role_id ?? 0; // Administrador por defecto
        $branches = Branch::all();
        $users = User::all();

        $viewMode = $request->get('mode', 'activos'); // 'activos', 'historial', 'mis_prestamos'

        $query = Loan::with('payments');

        // Si se selecciona "Mis Préstamos" o el usuario no es Admin
        if ($viewMode === 'mis_prestamos') {
            $query->where('user_id', $user->id);
        } else {
            // Modo "Activos": préstamos con saldo pendiente por abonar
            if ($viewMode === 'activos') {
                $query->where('pres_restante', '>', 0);
            }
            
            // Modo "Historial": muestra todos los préstamos (activos y liquidados)

            // Filtro por sucursal si es Administrador de Centro (Rol 1)
            if ($userRole == 2) {
                $userBranch = $user->branch?->name ?? $user->branch_name ?? 'MATRIZ';
                $query->where('sucursal', $userBranch);
            }
        }

        $loans = $query->latest('pres_fecha')->get();

        return view('loans.index', compact('loans', 'branches', 'users', 'viewMode', 'userRole', 'user'));
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
}