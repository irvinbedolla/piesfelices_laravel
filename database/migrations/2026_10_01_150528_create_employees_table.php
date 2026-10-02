<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Datos del empleado
            $table->string('name', 150);
            $table->string('phone', 20)->nullable();
            $table->string('position', 100)->nullable();
            $table->decimal('salary', 10, 2)->default(0.00);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->date('hired_at')->nullable();

            // Borrado lógico y timestamps
            $table->softDeletes(); // <-- IMPORTANTE: Esta columna genera deleted_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};