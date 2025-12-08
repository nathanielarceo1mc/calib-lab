<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->string('uut_status')->nullable()->after('status');
            $table->string('reporting_status')->nullable()->after('uut_status');
            $table->date('date_performed')->nullable()->after('onsite_date');
            $table->json('certificate_path')->nullable()->after('date_performed');
            $table->json('datasheet_path')->nullable()->after('certificate_path');
            $table->json('tsrf_path')->nullable()->after('datasheet_path');
        });
    }

    public function down(): void
    {
        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->dropColumn([
                'uut_status',
                'reporting_status',
                'date_performed',
                'certificate_path',
                'datasheet_path',
                'tsrf_path',
            ]);
        });
    }
};
