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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 70);
            $table->string('phone', 20)->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('username', 50)->unique();
            $table->string('email')->unique()->nullable(); // Necesario para Breeze
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('user_type')->default(1);
            $table->string('branch_name', 20)->nullable();
            $table->boolean('has_permission')->default(false);
            $table->string('status', 25)->default('activo');
            $table->boolean('is_doctor')->default(false);
            $table->rememberToken(); // Necesario para mantener la sesión iniciada
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('can_sales')->default(false);
            $table->boolean('can_inventory')->default(false);
            $table->boolean('can_clients')->default(false);
            $table->boolean('can_prescriptions')->default(false);
            $table->boolean('can_expenses')->default(false);
            $table->boolean('can_credit')->default(false);
            $table->boolean('can_cash_closing')->default(false);
            $table->boolean('can_patients')->default(false);
            $table->boolean('can_suppliers')->default(false);
            $table->boolean('can_employees')->default(false);
            $table->boolean('can_users')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_and_permissions_tables');
    }
};
