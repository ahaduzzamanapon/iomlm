<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title', 250);
            $table->text('content');
            $table->enum('target_audience', ['ALL', 'STUDENTS', 'TEACHERS'])->default('ALL');
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('priority', ['NORMAL', 'IMPORTANT', 'URGENT'])->default('NORMAL');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
