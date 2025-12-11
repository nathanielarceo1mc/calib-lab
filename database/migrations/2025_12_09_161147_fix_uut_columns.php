<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert full table to utf8mb4
        DB::statement('ALTER TABLE unit_under_tests CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->longText('report_payload')->nullable()->change();
            $table->json('certificate_path')->nullable()->change();
            $table->json('datasheet_path')->nullable()->change();
            $table->json('tsrf_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        //
    }
};
