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
        Schema::create('events', function (Blueprint $table) { // citas / agenda
            $table->id();
            $table->string('title', 300);
            $table->dateTime('start');
            $table->dateTime('end');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('color', 20)->nullable();
            $table->string('branch_name', 20)->nullable();
            $table->string('status', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) { // recetas
            $table->id();
            $table->text('prescription');
            $table->string('field1', 140)->nullable();
            $table->date('prescription_date');
            $table->string('client_name', 50)->nullable();
            $table->date('consultation_date');
            $table->string('treatment', 200)->nullable();
            $table->string('branch_name', 15)->nullable();
            $table->timestamps();
        });

        Schema::create('clinical_histories', function (Blueprint $table) { // historial_clinico
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('reason_1', 115)->nullable();
            $table->string('reason_2', 115)->nullable();
            $table->string('allergies', 115)->nullable();
            $table->string('weight', 50)->nullable();
            $table->string('height', 50)->nullable();
            $table->string('blood_pressure', 50)->nullable();
            $table->string('foot_type', 115)->nullable();
            $table->string('pedigraphy_image', 200)->nullable();
            $table->string('diagnosis_1', 115)->nullable();
            $table->string('diagnosis_2', 115)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('return_notes', function (Blueprint $table) { // nota_devolucion
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->string('disease', 115)->nullable();
            $table->string('treatment', 100)->nullable();
            $table->string('diagnosis_1', 115)->nullable();
            $table->string('diagnosis_2', 115)->nullable();
            $table->timestamps();
        });

        Schema::create('letterhead_sequences', function (Blueprint $table) { // consecutivo_hojas_membretadas
            $table->id();
            $table->integer('folio');
            $table->integer('year');
            $table->string('full_folio', 50);
            $table->string('subject', 255)->default('Sin asunto especificado');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('branch_id', 50)->nullable();
            $table->dateTime('generated_at');
            $table->string('status', 20)->default('Generado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_tables');
    }
};
