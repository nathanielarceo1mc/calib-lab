<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('order_number')->unique()->after('id');
            $table->date('order_date')->nullable()->after('order_number');

            // if you ALREADY have customer_name in jobs, comment this next line
            $table->string('customer_name')->nullable()->after('order_date');

            $table->string('address')->nullable()->after('customer_name');
            $table->string('telephone')->nullable()->after('address');
            $table->string('email')->nullable()->after('telephone');
            $table->string('contact_person')->nullable()->after('email');
            $table->string('designation')->nullable()->after('contact_person');

            $table->string('po_number')->nullable()->after('designation');
            $table->string('po_file_path')->nullable()->after('po_number');
            $table->decimal('discount_percent', 5, 2)->default(0)->after('po_file_path');

            $table->string('quotation_number')->nullable()->after('discount_percent');
            $table->string('end_user')->nullable()->after('quotation_number');

            $table->unsignedBigInteger('receiver_user_id')->nullable()->after('end_user');

            // if you ALREADY have status in jobs, comment this next line
            $table->string('status')->default('receiving')->after('receiver_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'order_date',
                'address',
                'telephone',
                'email',
                'contact_person',
                'designation',
                'po_number',
                'po_file_path',
                'discount_percent',
                'quotation_number',
                'end_user',
                'receiver_user_id',
                'status',
            ]);
        });
    }
};
