<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_under_test', function (Blueprint $table) {
            // Add columns only if they do not already exist

            if (! Schema::hasColumn('unit_under_test', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('name');
            }

            if (! Schema::hasColumn('unit_under_test', 'model')) {
                $table->string('model')->nullable()->after('manufacturer');
            }

            if (! Schema::hasColumn('unit_under_test', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('model');
            }

            if (! Schema::hasColumn('unit_under_test', 'range')) {
                $table->string('range')->nullable()->after('serial_number');
            }

            if (! Schema::hasColumn('unit_under_test', 'remarks')) {
                $table->text('remarks')->nullable()->after('range');
            }

            if (! Schema::hasColumn('unit_under_test', 'assigned_to')) {
                $table->string('assigned_to')->nullable()->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('unit_under_test', function (Blueprint $table) {
            if (Schema::hasColumn('unit_under_test', 'manufacturer')) {
                $table->dropColumn('manufacturer');
            }
            if (Schema::hasColumn('unit_under_test', 'model')) {
                $table->dropColumn('model');
            }
            if (Schema::hasColumn('unit_under_test', 'serial_number')) {
                $table->dropColumn('serial_number');
            }
            if (Schema::hasColumn('unit_under_test', 'range')) {
                $table->dropColumn('range');
            }
            if (Schema::hasColumn('unit_under_test', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('unit_under_test', 'assigned_to')) {
                $table->dropColumn('assigned_to');
            }
        });
    }
};
