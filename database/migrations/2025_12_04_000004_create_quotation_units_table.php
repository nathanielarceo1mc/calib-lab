<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->string('accreditation')->nullable();
            $table->string('service_type')->nullable();
            $table->string('scope')->nullable();
            $table->text('description')->nullable();
            $table->string('range')->nullable();
            $table->string('cmc')->nullable();
            $table->string('method')->nullable();
            $table->string('environment')->nullable();
            $table->string('type')->nullable();
            $table->integer('qty')->nullable();
            $table->string('uom')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('rush_fee', 12, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->string('lead_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_units');
    }
};
