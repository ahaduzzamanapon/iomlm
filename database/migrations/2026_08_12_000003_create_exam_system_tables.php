<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new configuration columns to exams table if not existing
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'negative_marking')) {
                $table->decimal('negative_marking', 4, 2)->default(0.00)->after('pass_marks');
            }
            if (!Schema::hasColumn('exams', 'start_datetime')) {
                $table->dateTime('start_datetime')->nullable()->after('exam_date');
            }
            if (!Schema::hasColumn('exams', 'end_datetime')) {
                $table->dateTime('end_datetime')->nullable()->after('start_datetime');
            }
            if (!Schema::hasColumn('exams', 'is_anti_cheating')) {
                $table->boolean('is_anti_cheating')->default(true)->after('status');
            }
        });

        // ══════════════════════════════════════════════
        // EXAM QUESTIONS (Question Paper Building)
        // ══════════════════════════════════════════════
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->decimal('marks', 5, 2)->default(1.00);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['exam_id', 'question_id']);
        });

        // ══════════════════════════════════════════════
        // EXAM SUBMISSIONS (Student Attempts)
        // ══════════════════════════════════════════════
        Schema::create('exam_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_score', 6, 2)->default(0.00);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->unsignedSmallInteger('wrong_count')->default(0);
            $table->decimal('negative_marks_deducted', 6, 2)->default(0.00);
            $table->unsignedTinyInteger('tab_switch_count')->default(0);
            $table->enum('status', ['IN_PROGRESS', 'SUBMITTED', 'AUTO_SUBMITTED_VIOLATION'])->default('SUBMITTED');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });

        // ══════════════════════════════════════════════
        // EXAM ANSWERS (Detailed Answers)
        // ══════════════════════════════════════════════
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('exam_submissions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('selected_option_id', 10)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->decimal('marks_awarded', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_submissions');
        Schema::dropIfExists('exam_questions');

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['negative_marking', 'start_datetime', 'end_datetime', 'is_anti_cheating']);
        });
    }
};
