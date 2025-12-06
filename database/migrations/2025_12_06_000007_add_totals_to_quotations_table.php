<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'po_amount')) {
                $table->decimal('po_amount', 14, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('quotations', 'prepared_by')) {
                $table->string('prepared_by')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'po_amount')) {
                $table->dropColumn('po_amount');
            }
            if (Schema::hasColumn('quotations', 'prepared_by')) {
                $table->dropColumn('prepared_by');
            }
        });
    }
};
