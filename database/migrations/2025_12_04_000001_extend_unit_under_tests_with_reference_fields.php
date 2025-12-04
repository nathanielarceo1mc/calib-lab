<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->unique()->after('id');
            $table->string('status')->default('for_approval')->after('reference_no');
            $table->unsignedBigInteger('assigned_approver_id')->nullable()->after('status');
            $table->unsignedBigInteger('assigned_personnel_id')->nullable()->after('assigned_approver_id');
            $table->string('batch_no')->nullable()->after('assigned_personnel_id');

            $table->string('name')->nullable()->after('description');
            $table->string('method_procedure')->nullable()->after('name');
            $table->string('manufacturer')->nullable()->after('method_procedure');
            $table->string('model')->nullable()->after('manufacturer');
            $table->string('serial_number')->nullable()->after('model');
            $table->string('identification')->nullable()->after('serial_number');
            $table->boolean('sub_contracted')->default(false)->after('identification');

            $table->date('received_date')->nullable()->after('sub_contracted');
            $table->date('estimated_completion_date')->nullable()->after('received_date');
            $table->date('onsite_date')->nullable()->after('estimated_completion_date');

            $table->integer('service_interval')->nullable()->after('onsite_date');
            $table->string('service_interval_type', 20)->nullable()->after('service_interval');

            $table->string('tracking_no')->nullable()->after('service_interval_type');
            $table->string('accessories')->nullable()->after('tracking_no');
            $table->string('receiving_condition')->nullable()->after('accessories');

            $table->boolean('require_conformance')->default(false)->after('receiving_condition');
            $table->boolean('require_opinion')->default(false)->after('require_conformance');
            $table->boolean('deviate_methods')->default(false)->after('require_opinion');
            $table->boolean('request_new_method')->default(false)->after('deviate_methods');
            $table->text('request_details')->nullable()->after('request_new_method');

            $table->string('delivery_method')->nullable()->after('request_details');
            $table->string('delivery_contact')->nullable()->after('delivery_method');
            $table->text('special_instructions')->nullable()->after('delivery_contact');

            $table->text('test_points')->nullable()->after('special_instructions');
            $table->text('approver_remarks')->nullable()->after('test_points');

            $table->index('status');
            $table->index('assigned_approver_id');
            $table->index('assigned_personnel_id');
        });

        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->foreign('assigned_approver_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_personnel_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unit_under_tests', function (Blueprint $table) {
            $table->dropForeign(['assigned_approver_id']);
            $table->dropForeign(['assigned_personnel_id']);

            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_approver_id']);
            $table->dropIndex(['assigned_personnel_id']);

            $table->dropColumn([
                'reference_no',
                'status',
                'assigned_approver_id',
                'assigned_personnel_id',
                'batch_no',
                'name',
                'method_procedure',
                'manufacturer',
                'model',
                'serial_number',
                'identification',
                'sub_contracted',
                'received_date',
                'estimated_completion_date',
                'onsite_date',
                'service_interval',
                'service_interval_type',
                'tracking_no',
                'accessories',
                'receiving_condition',
                'require_conformance',
                'require_opinion',
                'deviate_methods',
                'request_new_method',
                'request_details',
                'delivery_method',
                'delivery_contact',
                'special_instructions',
                'test_points',
                'approver_remarks',
            ]);
        });
    }
};
