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
        Schema::create('product_images', function (Blueprint $table) { // imagen_articulo
            $table->id();
            $table->string('name', 50);
            $table->string('type', 10);
            $table->text('document_path');
            $table->timestamps();
        });

        Schema::create('clinical_history_images', function (Blueprint $table) { // imagen_historial
            $table->id();
            $table->foreignId('clinical_history_id')->constrained('clinical_histories')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('file_type', 20);
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('media_files', function (Blueprint $table) { // multimedia
            $table->id();
            $table->string('name', 100);
            $table->string('file_type', 20);
            $table->string('document_path', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_tables');
    }
};
