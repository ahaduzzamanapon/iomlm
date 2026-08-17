<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

// Tables to truncate completely (no dependency on users)
$truncateTables = [
    'subject_retakes',
    'exam_answers',
    'exam_submissions',
    'exam_attendees',
    'exam_questions',
    'exams',
    'results',
    'attendances',
    'assignment_submissions',
    'assignments',
    'learning_resources',
    'payments',
    'invoices',
    'fee_structures',
    'promotion_records',
    'enrollments',
    'merged_class_groups',
    'class_sessions',
    'routine_entries',
    'routine_slots',
    'subject_teacher_assignments',
    'notices',
    'timeline',
    'student_documents',
    'students',
    'teachers',
    'admission_forms',
    'public_applications',
    'batches',
    'course_subject_maps',
    'subject_modules',
    'subjects',
    'courses',
    'academic_sessions',
    'academic_years',
    'holiday_calendars',
    'batch_semester_positions',
];

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

foreach ($truncateTables as $table) {
    try {
        DB::table($table)->truncate();
        echo "Cleared: $table\n";
    } catch (\Exception $e) {
        echo "Skipped: $table ({$e->getMessage()})\n";
    }
}

// Keep only admin user
DB::table('users')->where('role', '!=', 'admin')->delete();
echo "Users: kept admin only\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo "\nDone! Database is clean.\n";
