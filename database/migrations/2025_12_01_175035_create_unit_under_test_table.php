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
        Schema::create('unit_under_test', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('job_item_id');

            // Reference like 2025CAL00001 / 2025INS00001
            $table->string('reference_no', 32)->unique();

            // Basic status in Receiving flow
            $table->string('status', 50)->default('for_approval');

            // Short name / UUT description
            $table->string('name')->nullable();

            // --- Fields you’ll need later (all nullable so no errors now) ---
            $table->string('method_procedure')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('identification')->nullable();

            $table->boolean('sub_contracted')->default(false);

            // From your Unit Under Test block:
            $table->string('type', 50)->nullable();              // In-house / On-site / Pull-out
            $table->string('receiving_method', 50)->nullable();  // Walk-in / Pick-up / Courier
            $table->string('tracking_no')->nullable();

            $table->date('received_date')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->date('onsite_date')->nullable();

            $table->integer('service_interval')->nullable();
            $table->string('service_interval_type', 20)->nullable(); // Month / Year

            // Approver assignment
            $table->unsignedBigInteger('assigned_approver_id')->nullable();
            $table->text('approver_remarks')->nullable();

            $table->timestamps();

            $table->foreign('job_item_id')
                ->references('id')->on('job_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_under_test');
    }
};
