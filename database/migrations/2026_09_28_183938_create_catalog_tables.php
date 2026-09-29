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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('company', 50)->nullable();
            $table->string('contact_name', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('sku', 50)->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0.00);
            $table->decimal('sale_price', 10, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('branch_name', 50)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('model', 50)->nullable();
            $table->string('serie', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->decimal('credit_price', 10, 2)->nullable();
            $table->text('observations')->nullable();
            $table->integer('product_type')->default(1); // 1: Producto, 3: Calzado, 4: Medicamento
            $table->integer('sequence')->default(0);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('address', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('locality', 40)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('branch_name', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('gender', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('age', 30)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('address', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('occupation', 50)->nullable();
            $table->string('religion', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_tables');
    }
};
