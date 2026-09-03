<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();

            // Class Test  — full marks: 30, convert to: 20
            $table->decimal('class_test_obtained', 5, 2)->nullable()->comment('Out of 30');
            $table->decimal('class_test_converted', 5, 2)->nullable()->comment('Out of 20');

            // Mid Term — full marks: 50, convert to: 30
            $table->decimal('midterm_obtained', 5, 2)->nullable()->comment('Out of 50');
            $table->decimal('midterm_converted', 5, 2)->nullable()->comment('Out of 30');

            // Final Term — full marks: 100, convert to: 40
            $table->decimal('final_obtained', 5, 2)->nullable()->comment('Out of 100');
            $table->decimal('final_converted', 5, 2)->nullable()->comment('Out of 40');

            // Attendance — out of 10 (based on attendance %)
            $table->decimal('attendance_percent', 5, 2)->nullable()->comment('0-100 percent');
            $table->decimal('attendance_converted', 5, 2)->nullable()->comment('Out of 10');

            // Grand total out of 100
            $table->decimal('total_mark', 5, 2)->nullable()->comment('Out of 100');
            $table->string('grade', 10)->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->enum('status', ['PASS', 'FAIL', 'INCOMPLETE'])->default('INCOMPLETE');
            $table->text('remarks')->nullable();

            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'batch_id'], 'unique_student_subject_batch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_marks');
    }
};
