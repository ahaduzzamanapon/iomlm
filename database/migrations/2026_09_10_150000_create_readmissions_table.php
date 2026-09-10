<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add readmission_fee to courses if not exists
        if (!Schema::hasColumn('courses', 'readmission_fee')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->decimal('readmission_fee', 10, 2)->nullable()->after('admission_fee');
            });
        }

        // 2. Expand invoices category enum in MySQL if applicable
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE invoices MODIFY COLUMN category ENUM('ADMISSION', 'SEMESTER', 'RETAKE', 'READMISSION', 'EXAM', 'DOCUMENT', 'FINE', 'MANUAL') NOT NULL DEFAULT 'MANUAL'");
            }
        } catch (\Throwable $e) {
            // Non-critical if driver doesn't support or already altered
        }

        // 3. Create readmissions table
        Schema::create('readmissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('from_batch_id')->nullable()->references('id')->on('batches')->nullOnDelete();
            $table->foreignId('to_batch_id')->nullable()->references('id')->on('batches')->nullOnDelete();
            $table->unsignedInteger('failed_subjects_count')->default(0);
            $table->json('failed_subject_ids')->nullable();
            $table->decimal('readmission_fee', 10, 2)->default(0.00);
            $table->foreignId('invoice_id')->nullable()->references('id')->on('invoices')->nullOnDelete();
            $table->string('status', 40)->default('PENDING'); // PENDING, APPROVED, CONTINUED_WITH_RETAKE, CANCELLED
            $table->string('admin_decision', 50)->nullable(); // READMISSION, CONTINUE_WITH_RETAKE
            $table->text('notes')->nullable();
            $table->foreignId('decided_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readmissions');

        if (Schema::hasColumn('courses', 'readmission_fee')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('readmission_fee');
            });
        }
    }
};
