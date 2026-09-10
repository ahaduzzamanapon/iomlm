<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand invoices category enum in MySQL if applicable
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE invoices MODIFY COLUMN category ENUM('ADMISSION', 'SEMESTER', 'RETAKE', 'READMISSION', 'COURSE_TRANSFER', 'EXAM', 'DOCUMENT', 'FINE', 'MANUAL') NOT NULL DEFAULT 'MANUAL'");
            }
        } catch (\Throwable $e) {
            // Non-critical if driver doesn't support or already altered
        }

        // 2. Create course_transfers table
        Schema::create('course_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('from_batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('from_enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->foreignId('to_course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('to_batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('to_semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->decimal('transfer_fee', 10, 2)->default(0.00);
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('status', 40)->default('PENDING'); // PENDING, APPROVED_PENDING_PAYMENT, COMPLETED, REJECTED, CANCELLED
            $table->foreignId('new_enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_transfers');
    }
};
