<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_files', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->string('label')->nullable();
    $table->string('file_path')->nullable(); // <--- important
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('customer_files');
    }
};
