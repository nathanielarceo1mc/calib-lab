<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->json('report_payload')->nullable()->after('tsrf_path');
        });
    }

    public function down(): void
    {
        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->dropColumn('report_payload');
        });
    }
};
