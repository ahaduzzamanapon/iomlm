<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Support Departments
        Schema::create('support_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Support Department Users (Pivot: Agents assigned to Departments)
        Schema::create('support_department_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('support_department_id')->constrained('support_departments')->cascadeOnDelete();
            $table->unique(['user_id', 'support_department_id'], 'dept_user_unique');
            $table->timestamps();
        });

        // 3. Support Tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no', 30)->unique();
            $table->uuid('uuid')->unique();
            $table->foreignId('department_id')->constrained('support_departments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Student if logged in
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('gender', ['MALE', 'FEMALE'])->default('MALE');
            $table->string('student_id', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('subject');
            $table->text('problem_details');
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'CLOSED'])->default('PENDING');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable(); // 1 to 5 stars
            $table->text('feedback')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 4. Support Messages (Live Chat)
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->enum('sender_type', ['USER', 'AGENT', 'SYSTEM'])->default('USER');
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_department_user');
        Schema::dropIfExists('support_departments');
    }
};
