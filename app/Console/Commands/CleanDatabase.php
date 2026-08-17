<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDatabase extends Command
{
    protected $signature = 'db:clean';
    protected $description = 'Delete all data except admin user';

    public function handle()
    {
        if (!$this->confirm('This will DELETE ALL DATA (except admin). Continue?', true)) {
            $this->info('Cancelled.');
            return;
        }

        $tables = [
            'subject_retakes', 'exam_answers', 'exam_submissions',
            'exam_attendees', 'exam_questions', 'exams', 'results',
            'attendances', 'assignment_submissions', 'assignments',
            'learning_resources', 'payments', 'invoices', 'fee_structures',
            'promotion_records', 'enrollments', 'merged_class_groups',
            'class_sessions', 'routine_entries', 'routine_slots',
            'subject_teacher_assignments', 'notices', 'student_documents',
            'students', 'teachers', 'admission_forms', 'public_applications',
            'batches', 'course_subject_maps', 'subject_modules', 'subjects',
            'courses', 'academic_sessions', 'academic_years', 'holiday_calendars',
            'batch_semester_positions', 'timeline',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
                $this->line("  ✓ Cleared: {$table}");
            } catch (\Exception $e) {
                $this->line("  - Skipped: {$table}");
            }
        }

        $deleted = DB::table('users')->where('role', '!=', 'admin')->delete();
        $this->line("  ✓ Users: removed {$deleted} non-admin accounts");

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("\n✅ Database cleaned. Only admin user remains.");
    }
}
