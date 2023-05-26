<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctor_patient', function (Blueprint $table) {
            $table->uuid('doctor_id');
            $table->uuid('patient_id');
            $table->timestamps();
    
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->onDelete('cascade');
            $table->foreign('patient_id')->references('patient_id')->on('patients')->onDelete('cascade');
            
            $table->primary(['doctor_id', 'patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_patient');
    }
};
