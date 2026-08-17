<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══════════════════════════════════════════════
        // ACADEMIC YEAR & SESSION
        // ══════════════════════════════════════════════
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // SUBJECT
        // ══════════════════════════════════════════════
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable();
            $table->string('name', 200);
            $table->string('code', 30)->unique();
            $table->unsignedTinyInteger('credit')->default(3);
            $table->unsignedSmallInteger('full_marks')->default(100);
            $table->unsignedSmallInteger('pass_marks')->default(40);
            $table->unsignedTinyInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // SUBJECT MODULE
        // ══════════════════════════════════════════════
        Schema::create('subject_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('category', 100)->nullable()->comment('Category / Topic Grouping e.g. Fiqh, Aqeedah, Tajweed');
            $table->unsignedSmallInteger('sequence_no');
            $table->string('title', 250);
            $table->text('description')->nullable();
            $table->boolean('is_locked_until_previous')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['subject_id', 'sequence_no']);
        });

        // ══════════════════════════════════════════════
        // COURSE
        // ══════════════════════════════════════════════
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable();
            $table->string('name', 200);
            $table->enum('type', ['SUBJECT_BASED', 'SEMESTER_BASED'])->default('SEMESTER_BASED');
            $table->unsignedSmallInteger('duration_value');
            $table->enum('duration_unit', ['MONTH', 'YEAR'])->default('YEAR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // SEMESTER
        // ══════════════════════════════════════════════
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence_no');
            $table->string('name', 100);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['course_id', 'sequence_no']);
        });

        // ══════════════════════════════════════════════
        // COURSE SUBJECT MAP
        // ══════════════════════════════════════════════
        Schema::create('course_subject_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['course_id', 'subject_id', 'semester_id'], 'unique_course_subject_semester');
        });

        // ══════════════════════════════════════════════
        // TEACHER
        // ══════════════════════════════════════════════
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable();
            $table->string('employee_id', 30)->unique()->nullable();
            $table->string('name', 200);
            $table->string('email', 150)->unique()->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('qualification', 200)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // BATCH
        // ══════════════════════════════════════════════
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('batch_code', 30)->unique()->nullable();
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->enum('status', ['PLANNED', 'ACTIVE', 'COMPLETED', 'CANCELLED'])->default('PLANNED');
            $table->unsignedTinyInteger('subject_version_snapshot')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // BATCH SEMESTER POSITION (for SEMESTER_BASED only)
        // ══════════════════════════════════════════════
        Schema::create('batch_semester_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_semester_id')->constrained('semesters')->restrictOnDelete();
            $table->date('started_at');
            $table->timestamps();
            $table->unique('batch_id'); // one active position per batch
        });

        // ══════════════════════════════════════════════
        // SUBJECT TEACHER ASSIGNMENT
        // ══════════════════════════════════════════════
        Schema::create('subject_teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['subject_id', 'teacher_id', 'batch_id'], 'unique_subject_teacher_batch');
        });

        // ══════════════════════════════════════════════
        // HOLIDAY CALENDAR
        // ══════════════════════════════════════════════
        Schema::create('holiday_calendars', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name', 200);
            $table->enum('scope', ['GLOBAL', 'INSTITUTE'])->default('GLOBAL');
            $table->foreignId('department_id')->nullable();
            $table->boolean('is_recurring_yearly')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // STUDENT
        // ══════════════════════════════════════════════
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_code', 30)->unique()->nullable();
            $table->string('name', 200);
            $table->string('email', 150)->unique()->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('father_name', 200)->nullable();
            $table->string('mother_name', 200)->nullable();
            $table->string('guardian_name', 200)->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->string('guardian_email', 150)->nullable();
            $table->string('guardian_relation', 50)->nullable();
            // Academic records
            $table->string('ssc_roll', 30)->nullable();
            $table->string('ssc_board', 50)->nullable();
            $table->decimal('ssc_gpa', 4, 2)->nullable();
            $table->unsignedSmallInteger('ssc_year')->nullable();
            $table->string('hsc_roll', 30)->nullable();
            $table->string('hsc_board', 50)->nullable();
            $table->decimal('hsc_gpa', 4, 2)->nullable();
            $table->unsignedSmallInteger('hsc_year')->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->enum('status', ['LEAD', 'PENDING', 'APPROVED', 'ACTIVE', 'ABSENT', 'DROPPED', 'CANCELLED', 'TRANSFERRED', 'COMPLETED', 'GRADUATED'])->default('LEAD');
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // ADMISSION FORM
        // ══════════════════════════════════════════════
        Schema::create('admission_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interested_course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->unsignedTinyInteger('attempt_no')->default(1);
            $table->string('lead_source', 50)->nullable(); // Website, Social, Referral, Direct
            $table->json('documents_uploaded')->nullable(); // photo, ssc_cert, hsc_cert
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->text('waiver_notes')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // ENROLLMENT
        // ══════════════════════════════════════════════
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admission_form_id')->nullable()->constrained()->nullOnDelete();
            $table->date('enrolled_at');
            $table->enum('status', ['ACTIVE', 'DROPPED', 'CANCELLED', 'TRANSFERRED', 'COMPLETED'])->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // ROUTINE SLOTS  (must be before timelines)
        // Named time slots: e.g. "মাগরিবের পর" 18:45-20:00
        // ══════════════════════════════════════════════
        Schema::create('routine_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ══════════════════════════════════════════════
        // ROUTINE ENTRIES  (must be before timelines)
        // Weekly grid: batch + slot + day → recurring class
        // ══════════════════════════════════════════════
        Schema::create('routine_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('routine_slots')->cascadeOnDelete();
            $table->enum('day_of_week', ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI']);
            // No FK constraint here — class_sessions doesn't exist yet (circular dep)
            // Eloquent relationship still works; FK added post-class_sessions if needed
            $table->unsignedBigInteger('class_session_id')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_override')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });


        // ══════════════════════════════════════════════
        // TIMELINE
        // ══════════════════════════════════════════════
        Schema::create('timelines', function (Blueprint $table) {

            $table->id();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('module_id')->constrained('subject_modules')->restrictOnDelete();
            // Link to routine entry (weekly pattern) — nullable for manual scheduling
            $table->foreignId('routine_entry_id')->nullable()->constrained('routine_entries')->nullOnDelete();
            $table->date('scheduled_date')->nullable(); // NULL = not yet scheduled (UPCOMING)
            $table->unsignedSmallInteger('class_no')->default(1)->comment('Which class occurrence for this module (1st, 2nd, 3rd...)');
            $table->enum('status', ['UPCOMING', 'SCHEDULED', 'RUNNING', 'COMPLETED', 'CANCELLED', 'RESCHEDULED'])->default('UPCOMING');
            $table->foreignId('parent_timeline_id')->nullable()->constrained('timelines')->nullOnDelete();
            $table->unsignedSmallInteger('reschedule_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // CLASS SESSION (date-based, subject-level)
        // One row = one actual class occurrence (e.g. Sunday 2026-01-19 Maghrib)
        // ══════════════════════════════════════════════
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();

            // Optional link to curriculum plan (timeline)
            $table->foreignId('timeline_id')->nullable()->constrained()->nullOnDelete();

            // The actual calendar date of this class
            $table->date('session_date')->nullable()->comment('Actual class date e.g. 2026-01-19');

            // Link to the recurring routine entry (which subject/slot/day this belongs to)
            $table->foreignId('routine_entry_id')->nullable()->constrained('routine_entries')->nullOnDelete();

            // Direct subject + batch refs (for display without joining timeline)
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->time('start_time')->nullable()->comment('Class scheduled start time e.g. 20:00');
            $table->string('meeting_link', 500)->nullable();
            $table->boolean('teacher_present')->nullable();
            $table->boolean('class_conducted')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['UPCOMING', 'SCHEDULED', 'RUNNING', 'COMPLETED', 'CANCELLED', 'RESCHEDULED'])->default('UPCOMING');

            // Teacher optionally logs which module was covered in this session
            $table->foreignId('module_covered_id')->nullable()->constrained('subject_modules')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // MERGED CLASS GROUP
        // ══════════════════════════════════════════════
        Schema::create('merged_class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['class_session_id', 'batch_id']);
        });

        // ══════════════════════════════════════════════
        // ATTENDANCE
        // ══════════════════════════════════════════════
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['PRESENT', 'ABSENT', 'LATE', 'EXCUSED'])->default('ABSENT');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['class_session_id', 'student_id']);
        });

        // ══════════════════════════════════════════════
        // LEARNING RESOURCE
        // ══════════════════════════════════════════════
        Schema::create('learning_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('subject_modules')->cascadeOnDelete();
            $table->enum('type', ['VIDEO', 'PDF', 'AUDIO', 'NOTES', 'SLIDES', 'LINK'])->default('LINK');
            $table->string('title', 250);
            $table->string('url', 1000);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // EXAM
        // ══════════════════════════════════════════════
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->enum('type', ['MIDTERM', 'FINAL', 'RETAKE', 'QUIZ', 'PRACTICAL'])->default('FINAL');
            $table->date('exam_date');
            $table->time('start_time')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('full_marks');
            $table->unsignedSmallInteger('pass_marks');
            $table->enum('status', ['SCHEDULED', 'RUNNING', 'COMPLETED', 'CANCELLED'])->default('SCHEDULED');
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // EXAM ATTENDEE
        // ══════════════════════════════════════════════
        Schema::create('exam_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_eligible')->default(true);
            $table->string('admit_card_no', 50)->nullable();
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });

        // ══════════════════════════════════════════════
        // RESULT
        // ══════════════════════════════════════════════
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('attempt_no')->default(1);
            $table->decimal('marks', 6, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->enum('status', ['PASS', 'FAIL', 'ABSENT', 'WITHHELD'])->nullable();
            $table->boolean('is_final_counted')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
            // Immutable — no softDeletes
            $table->unique(['exam_id', 'student_id', 'attempt_no']);
        });

        // ══════════════════════════════════════════════
        // SUBJECT RETAKE
        // ══════════════════════════════════════════════
        Schema::create('subject_retakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('retake_type', ['EXAM_ONLY', 'CLASS_EXAM', 'FULL_RESTART']);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED'])->default('PENDING');
            $table->timestamps();
            $table->softDeletes();
        });

        // ══════════════════════════════════════════════
        // PROMOTION RECORD
        // ══════════════════════════════════════════════
        Schema::create('promotion_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('from_semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('to_semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->enum('decision', ['PROMOTED', 'FORCE_PROMOTED', 'HELD_BACK'])->default('PROMOTED');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════
        // DOCUMENTS (Certificate, Transcript, etc.)
        // ══════════════════════════════════════════════
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['CERTIFICATE', 'TRANSCRIPT', 'MARKSHEET', 'COMPLETION_LETTER', 'RECOMMENDATION_LETTER']);
            $table->string('template_id', 50)->nullable();
            $table->string('file_url', 1000)->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->json('snapshot_data')->nullable(); // frozen data at generation time
            $table->timestamps();
        });

        // ══════════════════════════════════════════════
        // SETTINGS
        // ══════════════════════════════════════════════
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string'); // string|bool|int|json
            $table->string('group', 50)->default('general');
            $table->string('label', 200)->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════
        // ROLES & PERMISSIONS
        // ══════════════════════════════════════════════
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('label', 150)->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('label', 200)->nullable();
            $table->string('group', 60)->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('admin_scopes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scope_name', 100);
            $table->primary(['user_id', 'scope_name']);
        });

        // ══════════════════════════════════════════════
        // NOTIFICATION ENGINE
        // ══════════════════════════════════════════════
        Schema::create('notification_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('notification_events')->cascadeOnDelete();
            $table->enum('channel', ['WEB', 'EMAIL', 'SMS', 'PUSH']);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['PENDING', 'SENT', 'FAILED', 'READ'])->default('PENDING');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['WEB', 'EMAIL', 'SMS', 'PUSH']);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'channel']);
        });

        // ══════════════════════════════════════════════
        // AUDIT LOG
        // ══════════════════════════════════════════════
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event', 100); // class_status_changed, result_published, etc.
            $table->string('auditable_type', 100)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notification_events');
        Schema::dropIfExists('admin_scopes');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('promotion_records');
        Schema::dropIfExists('subject_retakes');
        Schema::dropIfExists('results');
        Schema::dropIfExists('exam_attendees');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('learning_resources');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('merged_class_groups');
        Schema::dropIfExists('class_sessions');
        Schema::dropIfExists('routine_entries'); // must drop before timelines (FK)
        Schema::dropIfExists('timelines');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('admission_forms');
        Schema::dropIfExists('students');
        Schema::dropIfExists('holiday_calendars');
        Schema::dropIfExists('batch_semester_positions');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('subject_teacher_assignments');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('course_subject_maps');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('subject_modules');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_sessions');
        Schema::dropIfExists('academic_years');
    }
};
