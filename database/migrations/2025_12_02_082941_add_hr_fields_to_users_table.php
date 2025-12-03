<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('middle_initial')->nullable();
            $table->string('last_name')->nullable();

            $table->string('employee_id')->nullable();
            $table->string('position')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('home_address')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('tin')->nullable();

            $table->string('roles')->nullable();
            $table->string('groups')->nullable();
            $table->string('service_types')->nullable();

            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'middle_initial',
                'last_name',
                'employee_id',
                'position',
                'birth_date',
                'gender',
                'marital_status',
                'home_address',
                'home_phone',
                'mobile',
                'tin',
                'roles',
                'groups',
                'service_types',
                'is_active',
            ]);
        });
    }
};
