<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_pos', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_pos', 'po_number')) {
                $table->string('po_number')->nullable()->after('label');
            }
            if (!Schema::hasColumn('customer_pos', 'po_date')) {
                $table->date('po_date')->nullable()->after('po_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_pos', function (Blueprint $table) {
            if (Schema::hasColumn('customer_pos', 'po_number')) {
                $table->dropColumn('po_number');
            }
            if (Schema::hasColumn('customer_pos', 'po_date')) {
                $table->dropColumn('po_date');
            }
        });
    }
};
