<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assignments Table
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('title', 250);
            $table->text('instructions')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->decimal('total_marks', 5, 2)->default(100.00);
            $table->dateTime('due_datetime');
            $table->enum('status', ['ACTIVE', 'CLOSED'])->default('ACTIVE');
            $table->timestamps();
        });

        // Assignment Submissions Table
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('submission_file', 500);
            $table->text('student_note')->nullable();
            $table->decimal('obtained_marks', 5, 2)->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->enum('status', ['SUBMITTED', 'GRADED', 'LATE_SUBMITTED'])->default('SUBMITTED');
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
