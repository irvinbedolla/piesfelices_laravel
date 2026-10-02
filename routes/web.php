<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InventoryController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas del Módulo de Usuarios
    Route::resource('users', UserController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('employees', EmployeeController::class);
    Route::get('suppliers/pdf', [SupplierController::class, 'generatePdf'])->name('suppliers.pdf');
    Route::resource('suppliers', SupplierController::class);
    // MÓDULO DE INVENTARIO MULTISUCURSAL Y KÁRDEX
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/store-product', [InventoryController::class, 'storeProduct'])->name('inventory.store-product');
    Route::post('inventory/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::post('inventory/transfer', [InventoryController::class, 'transferStock'])->name('inventory.transfer');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    
    // API Lector Código de Barras
    Route::get('api/inventory/scan', [InventoryController::class, 'searchByBarcode'])->name('api.inventory.scan');});
    Route::post('inventory/return-matrix', [InventoryController::class, 'returnToMatrix'])->name('inventory.return-matrix');
    Route::patch('branches/{branch}/set-matrix', [BranchController::class, 'setMatrix'])->name('branches.set-matrix');
    Route::put('inventory/products/{product}', [InventoryController::class, 'updateProduct'])->name('inventory.update-product');
    Route::post('/inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::post('/inventory/return-matrix', [InventoryController::class, 'returnToMatrix'])->name('inventory.return-matrix');
    Route::get('/inventory/pdf', [InventoryController::class, 'exportPdf'])->name('inventory.pdf');

    
require __DIR__.'/auth.php';
