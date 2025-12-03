<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'region')) {
                $table->string('region', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'province')) {
                $table->string('province', 100)->nullable()->after('region');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'region')) {
                $table->dropColumn('region');
            }
            if (Schema::hasColumn('customers', 'province')) {
                $table->dropColumn('province');
            }
        });
    }
};
