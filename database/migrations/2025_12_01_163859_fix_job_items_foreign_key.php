<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_items', function (Blueprint $table) {
            // drop old FK if it exists
            try {
                $table->dropForeign(['job_id']);
            } catch (\Throwable $e) {
                // ignore if it didn't exist yet
            }

            // create new FK to work_orders
            $table->foreign('job_id')
                  ->references('id')
                  ->on('work_orders')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('job_items', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            // optional: add back FK to jobs if you really want
        });
    }
};
