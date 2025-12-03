<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Add quotation_id column (nullable because not all jobs come from quotations)
            $table->foreignId('quotation_id')
                  ->nullable()
                  ->constrained('quotations')
                  ->nullOnDelete(); // if quotation deleted, set to null
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Remove the foreign key and the column on rollback
            $table->dropForeign(['quotation_id']);
            $table->dropColumn('quotation_id');
        });
    }
};
