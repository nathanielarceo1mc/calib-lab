<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');

            $table->string('accreditation')->nullable();   // PAB ISO/IEC 17025:2017, Traceable, etc.
            $table->string('service_type')->nullable();    // Calibration / Inspection
            $table->string('capability')->nullable();
            $table->text('description')->nullable();       // Description / Specification / Calibration
            $table->string('type')->nullable();            // Instrument type
            $table->string('receiving_method')->nullable();
            $table->integer('qty')->default(1);
            $table->string('unit')->nullable();
            $table->integer('lead_time_days')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_items');
    }
};
