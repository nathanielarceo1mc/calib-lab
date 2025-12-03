<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add only if they don't exist yet
            $table->string('nature_of_business')->nullable()->after('business_name');
            $table->string('region', 100)->nullable()->after('address');
            $table->string('province', 100)->nullable()->after('region');
            $table->string('tin')->nullable()->after('company_phone');
            $table->integer('payment_terms')->nullable()->after('tin');
            $table->string('vat_type', 50)->nullable()->after('payment_terms');
            $table->integer('certificate_expiration_days')->nullable()->after('vat_type');
            $table->string('company_type', 50)->nullable()->after('certificate_expiration_days');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'nature_of_business',
                'region',
                'province',
                'tin',
                'payment_terms',
                'vat_type',
                'certificate_expiration_days',
                'company_type',
            ]);
        });
    }
};
