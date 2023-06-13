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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('user_id');
            // $table->binary('file');
            $table->string('file_ext');
            $table->string('file_path');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');    
            $table->timestamps();
              
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
