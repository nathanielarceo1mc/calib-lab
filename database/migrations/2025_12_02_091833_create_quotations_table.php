<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            $table->string('quote_no')->unique();
            $table->string('customer_name');
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            $table->text('scope')->nullable();
            $table->decimal('amount', 12, 2)->nullable();

            // draft, sent, approved, rejected, canceled
            $table->string('status')->default('draft');

            // PO info, filled when approved
            $table->string('po_number')->nullable();
            $table->date('po_date')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
