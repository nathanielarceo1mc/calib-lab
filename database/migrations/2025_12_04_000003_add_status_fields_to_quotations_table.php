<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('quote_no')->constrained('customers')->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'address')) {
                $table->text('address')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('quotations', 'telephone')) {
                $table->string('telephone')->nullable()->after('address');
            }
            if (!Schema::hasColumn('quotations', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->change();
            }
            if (!Schema::hasColumn('quotations', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('quotations', 'tin')) {
                $table->string('tin')->nullable()->after('contact_email');
            }
            if (!Schema::hasColumn('quotations', 'payment_terms')) {
                $table->string('payment_terms')->nullable()->after('tin');
            }
            if (!Schema::hasColumn('quotations', 'vat_type')) {
                $table->string('vat_type')->nullable()->after('payment_terms');
            }
            if (!Schema::hasColumn('quotations', 'validity_days')) {
                $table->integer('validity_days')->nullable()->after('vat_type');
            }
            if (!Schema::hasColumn('quotations', 'reference')) {
                $table->string('reference')->nullable()->after('payment_terms');
            }
            if (!Schema::hasColumn('quotations', 'quotation_request_no')) {
                $table->string('quotation_request_no')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('quotations', 'discount_percent')) {
                $table->decimal('discount_percent', 8, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('quotations', 'status')) {
                $table->string('status')->default('draft')->after('amount');
            } else {
                $table->string('status')->default('draft')->change();
            }
            if (!Schema::hasColumn('quotations', 'po_number')) {
                $table->string('po_number')->nullable()->after('status');
            }
            if (!Schema::hasColumn('quotations', 'po_date')) {
                $table->date('po_date')->nullable()->after('po_number');
            }
            if (!Schema::hasColumn('quotations', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('quotations', 'proceed_to_receiving')) {
                $table->boolean('proceed_to_receiving')->default(false)->after('approved_by');
            }
            if (!Schema::hasColumn('quotations', 'date')) {
                $table->date('date')->nullable()->after('quote_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            foreach ([
                'customer_id','address','telephone','contact_email','tin','payment_terms','vat_type','reference',
                'validity_days','quotation_request_no','discount_percent','po_number','po_date','proceed_to_receiving','date'
            ] as $col) {
                if (Schema::hasColumn('quotations', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('quotations', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
        });
    }
};
