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
        Schema::create('equipment', function (Blueprint $table) {
        $table->id();
        $table->string('description');
        $table->string('manufacturer')->nullable();
        $table->string('model')->nullable();
        $table->string('serial_number')->nullable();
        $table->string('owner')->nullable(); // lab or customer
        $table->string('range')->nullable();
        $table->date('last_cal_date')->nullable();
        $table->date('next_cal_date')->nullable();
        $table->string('status')->default('active'); // active, retired, etc.
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
