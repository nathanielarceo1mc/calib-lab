<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_pos', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_pos', 'file_path')) {
                $table->string('file_path')->nullable()->after('label');
            }
        });

        Schema::table('customer_files', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_files', 'file_path')) {
                $table->string('file_path')->nullable()->after('label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_pos', function (Blueprint $table) {
            if (Schema::hasColumn('customer_pos', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });

        Schema::table('customer_files', function (Blueprint $table) {
            if (Schema::hasColumn('customer_files', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
};
