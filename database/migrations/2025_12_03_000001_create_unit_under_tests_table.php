<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_under_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->string('accreditation')->nullable();
            $table->string('service_type')->nullable();
            $table->text('capability')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('receiving_method')->nullable();
            $table->string('qty')->nullable();
            $table->string('unit')->nullable();
            $table->string('lead_time')->nullable();
            $table->decimal('rush_fee', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_under_tests');
    }
};
