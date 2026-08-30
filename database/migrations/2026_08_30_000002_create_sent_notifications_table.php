<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 250);
            $table->text('message');
            $table->enum('channel', ['PUSH', 'EMAIL', 'BOTH'])->default('BOTH');
            $table->enum('recipient_type', ['ALL_STUDENTS', 'ALL_TEACHERS', 'SPECIFIC_STUDENT', 'BATCH_WISE', 'SEMESTER_WISE'])->default('ALL_STUDENTS');
            $table->string('recipient_filter_id', 100)->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->string('action_url', 1000)->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_notifications');
    }
};
