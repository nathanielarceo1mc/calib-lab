<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            // your job/work order fields
            $table->string('job_number')->nullable();          // optional internal code
            $table->string('order_number')->unique();          // WO-2025-0001
            $table->date('order_date')->nullable();

            $table->string('customer_name');
            $table->text('address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('designation')->nullable();

            $table->string('po_number')->nullable();
            $table->string('po_file_path')->nullable();

            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('quotation_number')->nullable();
            $table->string('end_user')->nullable();

            $table->unsignedBigInteger('receiver_user_id')->nullable();
            $table->string('status')->default('receiving');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
