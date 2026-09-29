<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->dateTime('sale_datetime')->nullable();
            $table->string('payment_type', 40)->nullable();
            $table->text('diagnosis')->nullable();
            $table->decimal('deposit', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->enum('sale_type', ['contado', 'credito'])->default('contado');
            $table->integer('type')->default(1); // 1: Venta, 2: Consulta
            $table->string('client_name', 100)->nullable();
            $table->string('client_email', 30)->nullable();
            $table->string('branch_name', 20)->nullable();
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->boolean('has_invoice')->default(false);
            $table->integer('invoice_number')->nullable();
            $table->string('cfdi_code', 80)->nullable();
            $table->string('invoice_type', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->string('product_name', 50)->nullable();
            $table->string('sku', 40)->nullable();
            $table->string('payment_type', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('sale_payments', function (Blueprint $table) { // abonos
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('branch_name', 30)->default('SIN SUCURSAL');
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) { // prestamos
            $table->id();
            $table->string('description', 200);
            $table->date('loan_date');
            $table->decimal('cost', 10, 2);
            $table->decimal('remaining_balance', 10, 2);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('branch_name', 50);
            $table->timestamps();
        });

        Schema::create('loan_payments', function (Blueprint $table) { // pago_prestamo
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('branch_name', 30);
            $table->timestamps();
        });

        Schema::create('cash_withdrawals', function (Blueprint $table) { // retiros
            $table->id();
            $table->string('reason', 300)->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('branch_name', 30);
            $table->integer('type');
            $table->string('status', 20)->default('activo');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_and_finance_tables');
    }
};
