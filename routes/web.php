<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplyOrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CashClosingController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesConsultationController;
use App\Http\Controllers\DashboardController;

// Redirigir la raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    
    // Módulo de Usuarios, Sucursales y Roles
    Route::resource('users', UserController::class);
    
    //Rutas para el módulo de Sucursales
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    Route::resource('roles', RoleController::class);
    Route::resource('employees', EmployeeController::class);
    
    // Proveedores
    Route::get('suppliers/pdf', [SupplierController::class, 'generatePdf'])->name('suppliers.pdf');
    Route::resource('suppliers', SupplierController::class);
    
    // Inventarios y Movimientos
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/store-product', [InventoryController::class, 'storeProduct'])->name('inventory.store-product');
    Route::post('inventory/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::post('inventory/transfer', [InventoryController::class, 'transferStock'])->name('inventory.transfer');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('api/inventory/scan', [InventoryController::class, 'searchByBarcode'])->name('api.inventory.scan');
    Route::post('inventory/return-matrix', [InventoryController::class, 'returnToMatrix'])->name('inventory.return-matrix');
    Route::patch('branches/{branch}/set-matrix', [BranchController::class, 'setMatrix'])->name('branches.set-matrix');
    Route::put('inventory/products/{product}', [InventoryController::class, 'updateProduct'])->name('inventory.update-product');
    Route::get('/inventory/pdf', [InventoryController::class, 'exportPdf'])->name('inventory.pdf');

    // Órdenes de Suministro
    Route::get('/supply-orders/create', [SupplyOrderController::class, 'create'])->name('supply-orders.create');
    Route::post('/supply-orders', [SupplyOrderController::class, 'store'])->name('supply-orders.store');
    Route::get('/supply-orders/{order}/pdf', [SupplyOrderController::class, 'pdf'])->name('supply-orders.pdf');

    // Directorio, Clientes y Pacientes
    Route::get('/customers/directory', [CustomerController::class, 'directory'])->name('customers.directory');
    Route::get('/customers/list', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('/customers/bulk-delete', [CustomerController::class, 'destroyBulk'])->name('customers.bulk-delete');
    Route::get('/customers/pdf', [CustomerController::class, 'exportPdf'])->name('customers.pdf');

    Route::get('/patients/list', function () {
        return view('patients.index');
    })->name('patients.index');

    // Punto de Venta (POS) - Soporta métodos POST y PUT para actualizar header
    Route::get('/pos/{id?}', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/{id}/add-product', [PosController::class, 'addProduct'])->name('pos.add-product');
    Route::put('/pos/item/{itemId}', [PosController::class, 'updateQuantity'])->name('pos.update-qty');
    Route::delete('/pos/item/{itemId}', [PosController::class, 'removeItem'])->name('pos.remove-item');
    Route::match(['post', 'put'], '/pos/{id}/header', [PosController::class, 'updateHeader'])->name('pos.update-header');
    Route::post('/pos/{id}/finish', [PosController::class, 'finishSale'])->name('pos.finish');
    Route::get('/pos/{id}/cancel', [PosController::class, 'cancelSale'])->name('pos.cancel');
    Route::get('/pos/{id}/ticket', [PosController::class, 'ticket'])->name('pos.ticket');
    Route::post('/pos/{id}/add-service', [PosController::class, 'addService'])->name('pos.add-service');
    Route::get('/pos/{id}/next-invoice', [PosController::class, 'getNextInvoiceNumber'])->name('pos.next-invoice');

    // Cierre de Caja
    Route::get('/cash-closing', [CashClosingController::class, 'index'])->name('cash-closing.index');
    Route::get('/cash-closing/daily', [CashClosingController::class, 'dailyReport'])->name('cash-closing.daily');
    Route::get('/cash-closing/loans', [CashClosingController::class, 'loansReport'])->name('cash-closing.loans');
    Route::get('/cash-closing/commissions', [CashClosingController::class, 'commissionsReport'])->name('cash-closing.commissions');

    // Recetas Médicas
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('prescriptions/{id}/pdf', [PrescriptionController::class, 'pdf'])->name('prescriptions.pdf');

    // Citas Podológicas
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/events', [AppointmentController::class, 'getEvents'])->name('appointments.events');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{id}/dates', [AppointmentController::class, 'updateDates'])->name('appointments.updateDates');
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::get('/customer-appointments-history/{customerId}', [AppointmentController::class, 'getCustomerAppointments'])->name('appointments.customer');

    // Gastos y Préstamos
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/loans', [ExpenseController::class, 'loansIndex'])->name('loans.index');
    Route::post('/loans', [ExpenseController::class, 'storeLoan'])->name('loans.store');
    Route::post('/loans/{id}/payment', [ExpenseController::class, 'storePayment'])->name('loans.payment');
    Route::delete('/loans/{id}', [ExpenseController::class, 'destroyLoan'])->name('loans.destroy');
    Route::get('/loans/{id}/payments', [ExpenseController::class, 'getPayments'])->name('loans.payments');

    // Créditos y Cobranza
    Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
    Route::post('/credits/{id}/payment', [CreditController::class, 'storePayment'])->name('credits.payment');
    Route::delete('/credits/{id}', [CreditController::class, 'destroy'])->name('credits.destroy');

    // Reportes y Estadísticas
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/invoices/excel', [ReportController::class, 'exportInvoicesExcel'])->name('reports.invoices.excel');
    Route::get('/reports/appointments/pdf', [ReportController::class, 'exportAppointmentsPdf'])->name('reports.appointments.pdf');
    Route::get('/reports/appointments', [ReportController::class, 'exportAppointments'])->name('reports.appointments');
    Route::get('/reports/sales/items', [ReportController::class, 'exportSalesReport'])->name('reports.sales.items');

    // Consulta de Ventas
    Route::get('/sales-consultation', [SalesConsultationController::class, 'index'])->name('sales.consultation.index');
    Route::delete('/sales-consultation/sale/{id}', [SalesConsultationController::class, 'destroySale'])->name('sales.consultation.destroy-sale');
    Route::delete('/sales-consultation/abono/{id}', [SalesConsultationController::class, 'destroyAbono'])->name('sales.consultation.destroy-abono');

    Route::post('/clientes', [PosController::class, 'store'])->name('clientes.store');
    
    // API Stats Dashboard
    Route::get('/api/dashboard-stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');
});

require __DIR__.'/auth.php';