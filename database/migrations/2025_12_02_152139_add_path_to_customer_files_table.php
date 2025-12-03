<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_files', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_files', 'path')) {
                $table->string('path')->nullable()->after('label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_files', function (Blueprint $table) {
            if (Schema::hasColumn('customer_files', 'path')) {
                $table->dropColumn('path');
            }
        });
    }
};
