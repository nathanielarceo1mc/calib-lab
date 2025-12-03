<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'nature_of_business')) {
                $table->string('nature_of_business')->nullable();
            }

            if (!Schema::hasColumn('customers', 'region')) {
                $table->string('region', 100)->nullable();
            }

            if (!Schema::hasColumn('customers', 'province')) {
                $table->string('province', 100)->nullable();
            }

            if (!Schema::hasColumn('customers', 'tin')) {
                $table->string('tin')->nullable();
            }

            if (!Schema::hasColumn('customers', 'payment_terms')) {
                $table->integer('payment_terms')->nullable();
            }

            if (!Schema::hasColumn('customers', 'vat_type')) {
                $table->string('vat_type', 50)->nullable();
            }

            if (!Schema::hasColumn('customers', 'certificate_expiration_days')) {
                $table->integer('certificate_expiration_days')->nullable();
            }

            if (!Schema::hasColumn('customers', 'company_type')) {
                $table->string('company_type', 50)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Only drop if they exist, to avoid errors when rolling back
            $drops = [];

            foreach ([
                'nature_of_business',
                'region',
                'province',
                'tin',
                'payment_terms',
                'vat_type',
                'certificate_expiration_days',
                'company_type',
            ] as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    $drops[] = $col;
                }
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
