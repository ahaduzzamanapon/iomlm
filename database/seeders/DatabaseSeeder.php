<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AcademicYear;
use App\Models\AdmissionForm;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseSubjectMap;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttendee;
use App\Models\HolidayCalendar;
use App\Models\LearningResource;
use App\Models\RoutineEntry;
use App\Models\RoutineSlot;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectModule;
use App\Models\SubjectTeacherAssignment;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ═══════════════════════════════════════════════════════════
        // 1. USERS
        // ═══════════════════════════════════════════════════════════
        $adminUser = User::create([
            'name' => 'IOM Central Admin', 'email' => 'admin@learningplus.com',
            'password' => $password, 'role' => 'admin',
        ]);

        User::create(['name' => 'Dr. Shaikh Ahmadullah',  'email' => 'teacher@learningplus.com', 'password' => $password, 'role' => 'teacher']);
        User::create(['name' => 'Dr. Manzur-e-Elahi',     'email' => 'ada@learningplus.com',     'password' => $password, 'role' => 'teacher']);
        User::create(['name' => 'Prof. Abu Bakr Muhammad','email' => 'shannon@learningplus.com', 'password' => $password, 'role' => 'teacher']);
        User::create(['name' => 'Abdullah Al Mamun',      'email' => 'student@learningplus.com', 'password' => $password, 'role' => 'student']);
        User::create(['name' => 'Ayesha Siddiqua',        'email' => 'sarah@learningplus.com',   'password' => $password, 'role' => 'student']);
        User::create(['name' => 'Tanvir Hossain',         'email' => 'tanvir@gmail.com',         'password' => $password, 'role' => 'student']);

        // ═══════════════════════════════════════════════════════════
        // 2. TEACHER & STUDENT PROFILES
        // ═══════════════════════════════════════════════════════════
        $t1 = Teacher::create(['employee_id' => 'EMP-IOM-101', 'name' => 'Dr. Shaikh Ahmadullah',   'email' => 'teacher@learningplus.com', 'phone' => '+8801711112233', 'designation' => 'Chief Islamic Scholar',  'is_active' => true]);
        $t2 = Teacher::create(['employee_id' => 'EMP-IOM-102', 'name' => 'Dr. Manzur-e-Elahi',      'email' => 'ada@learningplus.com',     'phone' => '+8801722334455', 'designation' => 'Professor of Fiqh',      'is_active' => true]);
        $t3 = Teacher::create(['employee_id' => 'EMP-IOM-103', 'name' => 'Prof. Abu Bakr Muhammad', 'email' => 'shannon@learningplus.com', 'phone' => '+8801733445566', 'designation' => 'Professor of Tajweed',  'is_active' => true]);

        $s1 = Student::create(['student_code' => 'STD-IOM-2026-001', 'name' => 'Abdullah Al Mamun', 'email' => 'student@learningplus.com', 'phone' => '+8801811998877', 'date_of_birth' => '2002-04-12', 'gender' => 'Male',   'status' => 'ACTIVE']);
        $s2 = Student::create(['student_code' => 'STD-IOM-2026-002', 'name' => 'Ayesha Siddiqua',   'email' => 'sarah@learningplus.com',   'phone' => '+8801822887766', 'date_of_birth' => '2003-09-25', 'gender' => 'Female', 'status' => 'ACTIVE']);
        $s3 = Student::create(['student_code' => 'STD-IOM-2026-003', 'name' => 'Tanvir Hossain',    'email' => 'tanvir@gmail.com',         'phone' => '+8801833776655', 'date_of_birth' => '2004-01-10', 'gender' => 'Male',   'status' => 'ACTIVE']);

        // ═══════════════════════════════════════════════════════════
        // 3. ACADEMIC YEAR & SESSION
        // ═══════════════════════════════════════════════════════════
        $year = AcademicYear::create(['name' => 'IOM Academic Year 2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_active' => true]);
        AcademicSession::create(['academic_year_id' => $year->id, 'name' => 'Spring Session 2026', 'is_active' => true]);

        // ═══════════════════════════════════════════════════════════
        // 4. COURSES & SEMESTERS
        // ═══════════════════════════════════════════════════════════
        $sfcCourse = Course::create(['name' => 'ফরজে আইন (Short Foundation Course - SFC)', 'type' => 'SUBJECT_BASED',  'duration_value' => 6, 'duration_unit' => 'MONTH', 'is_active' => true]);
        $baCourse  = Course::create(['name' => 'B.A. in Islamic Studies (ইসলামিক স্টাডিজে বি.এ.)',  'type' => 'SEMESTER_BASED', 'duration_value' => 4, 'duration_unit' => 'YEAR',  'is_active' => true]);

        $sem1 = Semester::create(['course_id' => $baCourse->id, 'sequence_no' => 1, 'name' => '1st Semester']);
        $sem2 = Semester::create(['course_id' => $baCourse->id, 'sequence_no' => 2, 'name' => '2nd Semester']);

        // ═══════════════════════════════════════════════════════════
        // 5. MASTER SUBJECTS + MODULES (Curriculum Syllabus)
        //    Modules = learning plan only, NOT scheduling
        // ═══════════════════════════════════════════════════════════
        $subDawah   = Subject::create(['name' => 'দাওয়াহ (Dawah Studies)',        'code' => 'DWH-101', 'credit' => 2, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);
        $subAqeedah = Subject::create(['name' => 'আক্বিদা (Islamic Aqeedah)',       'code' => 'AQD-101', 'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);
        $subFiqh    = Subject::create(['name' => 'ফিক্বহ (Islamic Fiqh & Rulings)', 'code' => 'FQH-101', 'credit' => 4, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);
        $subTajweed = Subject::create(['name' => 'তাজবীদ (Tajweed & Recitation)',   'code' => 'TAJ-101', 'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);

        // Curriculum modules (syllabus order)
        $mD1 = SubjectModule::create(['subject_id' => $subDawah->id,   'category' => 'দাওয়াহ',  'sequence_no' => 1, 'title' => 'DWH-1: দাওয়াহর মূলনীতি',          'is_locked_until_previous' => false, 'is_active' => true]);
        $mD2 = SubjectModule::create(['subject_id' => $subDawah->id,   'category' => 'দাওয়াহ',  'sequence_no' => 2, 'title' => 'DWH-2: মুসলিমদের মধ্যে দাওয়াহ',   'is_locked_until_previous' => true,  'is_active' => true]);
        $mA1 = SubjectModule::create(['subject_id' => $subAqeedah->id, 'category' => 'আক্বিদা', 'sequence_no' => 1, 'title' => 'AQD-1: ঈমানের আরকান পরিচয়',        'is_locked_until_previous' => false, 'is_active' => true]);
        $mA2 = SubjectModule::create(['subject_id' => $subAqeedah->id, 'category' => 'আক্বিদা', 'sequence_no' => 2, 'title' => 'AQD-2: তাওহীদের প্রকারভেদ',         'is_locked_until_previous' => true,  'is_active' => true]);
        $mF1 = SubjectModule::create(['subject_id' => $subFiqh->id,    'category' => 'ফিক্বহ',  'sequence_no' => 1, 'title' => 'FQH-1: তাহারাতের পরিচয় ও নাজাসাত', 'is_locked_until_previous' => false, 'is_active' => true]);
        $mF2 = SubjectModule::create(['subject_id' => $subFiqh->id,    'category' => 'ফিক্বহ',  'sequence_no' => 2, 'title' => 'FQH-2: অযু ও গোসলের বিধান',         'is_locked_until_previous' => true,  'is_active' => true]);
        $mT1 = SubjectModule::create(['subject_id' => $subTajweed->id, 'category' => 'তাজবীদ', 'sequence_no' => 1, 'title' => 'TAJ-1: আরবী হরফ ও মাখরাজ',          'is_locked_until_previous' => false, 'is_active' => true]);
        $mT2 = SubjectModule::create(['subject_id' => $subTajweed->id, 'category' => 'তাজবীদ', 'sequence_no' => 2, 'title' => 'TAJ-2: তানবীন ও শাদ্দার নিয়ম',       'is_locked_until_previous' => true,  'is_active' => true]);

        // Course → Subject mappings
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $subDawah->id,   'semester_id' => null,      'sort_order' => 1]);
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $subAqeedah->id, 'semester_id' => null,      'sort_order' => 2]);
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $subFiqh->id,    'semester_id' => null,      'sort_order' => 3]);
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $subTajweed->id, 'semester_id' => null,      'sort_order' => 4]);
        CourseSubjectMap::create(['course_id' => $baCourse->id,  'subject_id' => $subAqeedah->id, 'semester_id' => $sem1->id, 'sort_order' => 1]);
        CourseSubjectMap::create(['course_id' => $baCourse->id,  'subject_id' => $subFiqh->id,    'semester_id' => $sem1->id, 'sort_order' => 2]);
        CourseSubjectMap::create(['course_id' => $baCourse->id,  'subject_id' => $subDawah->id,   'semester_id' => $sem2->id, 'sort_order' => 1]);

        // Teacher → Subject (global)
        SubjectTeacherAssignment::create(['subject_id' => $subDawah->id,   'teacher_id' => $t1->id, 'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $subAqeedah->id, 'teacher_id' => $t1->id, 'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $subFiqh->id,    'teacher_id' => $t2->id, 'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $subTajweed->id, 'teacher_id' => $t3->id, 'batch_id' => null]);

        // ═══════════════════════════════════════════════════════════
        // 6. HOLIDAYS
        // ═══════════════════════════════════════════════════════════
        HolidayCalendar::create(['date' => '2026-02-21', 'name' => 'আন্তর্জাতিক মাতৃভাষা দিবস', 'scope' => 'GLOBAL', 'is_recurring_yearly' => true]);
        HolidayCalendar::create(['date' => '2026-03-26', 'name' => 'স্বাধীনতা দিবস',            'scope' => 'GLOBAL', 'is_recurring_yearly' => true]);
        HolidayCalendar::create(['date' => '2026-04-01', 'name' => 'ঈদুল ফিতর',                'scope' => 'GLOBAL', 'is_recurring_yearly' => false]);
        HolidayCalendar::create(['date' => '2026-04-02', 'name' => 'ঈদুল ফিতর (পরের দিন)',      'scope' => 'GLOBAL', 'is_recurring_yearly' => false]);
        HolidayCalendar::create(['date' => '2026-12-16', 'name' => 'বিজয় দিবস',               'scope' => 'GLOBAL', 'is_recurring_yearly' => true]);

        // ═══════════════════════════════════════════════════════════
        // 7. BATCHES
        // ═══════════════════════════════════════════════════════════
        $sfcBatch = Batch::create([
            'course_id' => $sfcCourse->id, 'academic_year_id' => $year->id,
            'name' => 'SFC Batch 01 — 2026', 'batch_code' => 'SFC-2026-01',
            'start_date' => '2026-01-15', 'status' => 'ACTIVE', 'subject_version_snapshot' => 1,
        ]);
        $baBatch = Batch::create([
            'course_id' => $baCourse->id, 'academic_year_id' => $year->id,
            'name' => 'B.A. Islamic Studies 2026 Batch 01', 'batch_code' => 'BA-IS-2026-01',
            'start_date' => '2026-01-15', 'status' => 'ACTIVE', 'subject_version_snapshot' => 1,
        ]);

        // ═══════════════════════════════════════════════════════════
        // 8. ROUTINE SLOTS
        // ═══════════════════════════════════════════════════════════
        $slotAsr     = RoutineSlot::create(['name' => 'আসরের পর',    'start_time' => '15:30:00', 'end_time' => '16:45:00', 'sort_order' => 1]);
        $slotMaghrib = RoutineSlot::create(['name' => 'মাগরিবের পর', 'start_time' => '18:30:00', 'end_time' => '20:00:00', 'sort_order' => 2]);
        $slotIsha    = RoutineSlot::create(['name' => 'ইশার পর',     'start_time' => '20:30:00', 'end_time' => '22:00:00', 'sort_order' => 3]);
        $slotFajr    = RoutineSlot::create(['name' => 'ফজরের পর',    'start_time' => '06:00:00', 'end_time' => '07:00:00', 'sort_order' => 4]);

        // ═══════════════════════════════════════════════════════════
        // 9. ROUTINE ENTRIES (Weekly Schedule Grid)
        //    SFC: SAT=Dawah(Maghrib), SUN=Aqeedah(Maghrib), MON=Fiqh(Maghrib), TUE=Tajweed(Isha)
        //    BA:  SUN=Aqeedah(Isha — different slot from SFC SUN), MON=Fiqh(Isha)
        //    → No conflict between batches (different slots on same day)
        // ═══════════════════════════════════════════════════════════
        $reSfcDawah   = RoutineEntry::create(['batch_id' => $sfcBatch->id, 'slot_id' => $slotMaghrib->id, 'day_of_week' => 'SAT', 'subject_id' => $subDawah->id,   'teacher_id' => $t1->id, 'title' => 'DWH-101: দাওয়াহ',  'color' => '#3b82f6', 'is_override' => false]);
        $reSfcAqeedah = RoutineEntry::create(['batch_id' => $sfcBatch->id, 'slot_id' => $slotMaghrib->id, 'day_of_week' => 'SUN', 'subject_id' => $subAqeedah->id, 'teacher_id' => $t1->id, 'title' => 'AQD-101: আক্বিদা', 'color' => '#10b981', 'is_override' => false]);
        $reSfcFiqh    = RoutineEntry::create(['batch_id' => $sfcBatch->id, 'slot_id' => $slotMaghrib->id, 'day_of_week' => 'MON', 'subject_id' => $subFiqh->id,    'teacher_id' => $t2->id, 'title' => 'FQH-101: ফিক্বহ',  'color' => '#8b5cf6', 'is_override' => false]);
        $reSfcTajweed = RoutineEntry::create(['batch_id' => $sfcBatch->id, 'slot_id' => $slotIsha->id,    'day_of_week' => 'TUE', 'subject_id' => $subTajweed->id, 'teacher_id' => $t3->id, 'title' => 'TAJ-101: তাজবীদ',  'color' => '#f59e0b', 'is_override' => false]);
        // ✅ Wednesday classes (today = Jul 29, WED)
        $reSfcWedDawah = RoutineEntry::create(['batch_id' => $sfcBatch->id, 'slot_id' => $slotAsr->id,    'day_of_week' => 'WED', 'subject_id' => $subDawah->id,   'teacher_id' => $t1->id, 'title' => 'DWH-101: দাওয়াহ (WED)',  'color' => '#3b82f6', 'is_override' => false]);
        $reBaWedFiqh   = RoutineEntry::create(['batch_id' => $baBatch->id,  'slot_id' => $slotMaghrib->id,'day_of_week' => 'WED', 'subject_id' => $subFiqh->id,    'teacher_id' => $t2->id, 'title' => 'FQH-101: ফিক্বহ (WED, B.A.)', 'color' => '#ec4899', 'is_override' => false]);
        // BA uses Isha slot on SUN & MON to avoid conflict with SFC's Maghrib slot
        $reBaAqeedah  = RoutineEntry::create(['batch_id' => $baBatch->id,  'slot_id' => $slotIsha->id,    'day_of_week' => 'SUN', 'subject_id' => $subAqeedah->id, 'teacher_id' => $t1->id, 'title' => 'AQD-101: আক্বিদা (B.A.)', 'color' => '#06b6d4', 'is_override' => false]);
        $reBaFiqh     = RoutineEntry::create(['batch_id' => $baBatch->id,  'slot_id' => $slotIsha->id,    'day_of_week' => 'MON', 'subject_id' => $subFiqh->id,    'teacher_id' => $t2->id, 'title' => 'FQH-101: ফিক্বহ (B.A.)',  'color' => '#ec4899', 'is_override' => false]);

        // ═══════════════════════════════════════════════════════════
        // 10. ENROLLMENTS & ADMISSION FORMS
        // ═══════════════════════════════════════════════════════════
        foreach ([$s1, $s3] as $st) {
            AdmissionForm::create(['student_id' => $st->id, 'interested_course_id' => $sfcCourse->id, 'attempt_no' => 1, 'status' => 'APPROVED', 'reviewed_by' => $adminUser->id, 'reviewed_at' => now()->subDays(10)]);
            Enrollment::create(['student_id' => $st->id, 'batch_id' => $sfcBatch->id, 'course_id' => $sfcCourse->id, 'semester_id' => null, 'enrolled_at' => '2026-01-15', 'status' => 'ACTIVE']);
        }
        AdmissionForm::create(['student_id' => $s2->id, 'interested_course_id' => $baCourse->id, 'attempt_no' => 1, 'status' => 'APPROVED', 'reviewed_by' => $adminUser->id, 'reviewed_at' => now()->subDays(10)]);
        $baEnroll = Enrollment::create(['student_id' => $s2->id, 'batch_id' => $baBatch->id, 'course_id' => $baCourse->id, 'semester_id' => $sem1->id, 'enrolled_at' => '2026-01-15', 'status' => 'ACTIVE']);

        // ═══════════════════════════════════════════════════════════
        // 11. CLASS SESSIONS (date-based, from routine — 4 weeks from batch start)
        //     Past dates → COMPLETED, future dates → SCHEDULED
        // ═══════════════════════════════════════════════════════════
        $holidays = HolidayCalendar::pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();
        $dayMap   = ['SUN' => 0, 'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6];

        $allEntries = RoutineEntry::with(['slot', 'subject'])->get();

        // ✅ Center session generation on TODAY (2026-07-29)
        // 4 weeks back → today → 3 weeks forward = good data for all panels
        $today     = Carbon::today();           // 2026-07-29
        $baseDate  = $today->copy()->subWeeks(4);
        $endDate   = $today->copy()->addWeeks(3);

        $sessionsByEntry = [];

        foreach ($allEntries as $entry) {
            if (!isset($dayMap[$entry->day_of_week])) continue;
            $targetDow   = $dayMap[$entry->day_of_week];
            $sessionDate = $baseDate->copy();

            // Advance to the first occurrence of this DOW >= baseDate
            while ($sessionDate->dayOfWeek !== $targetDow) { $sessionDate->addDay(); }

            while ($sessionDate <= $endDate) {
                $dateStr = $sessionDate->toDateString();
                if (!in_array($dateStr, $holidays)) {
                    $isPast   = Carbon::parse($dateStr)->lt($today);
                    $isToday  = Carbon::parse($dateStr)->isToday();

                    // Past → COMPLETED, Today → SCHEDULED (teacher will conduct),
                    // Future → SCHEDULED
                    if ($isPast) {
                        $status = 'COMPLETED';
                        $hasLink = true;
                    } elseif ($isToday) {
                        $status = 'SCHEDULED';
                        $hasLink = true; // today already has a link ready
                    } else {
                        $status = 'SCHEDULED';
                        $hasLink = false;
                    }

                    $cs = ClassSession::create([
                        'routine_entry_id' => $entry->id,
                        'batch_id'         => $entry->batch_id,
                        'subject_id'       => $entry->subject_id,
                        'teacher_id'       => $entry->teacher_id,
                        'session_date'     => $dateStr,
                        'start_time'       => $entry->slot?->start_time,
                        'meeting_link'     => $hasLink
                            ? 'https://meet.google.com/iom-' . strtolower(Str::random(3)) . '-' . strtolower(Str::random(4))
                            : null,
                        'teacher_present'  => $status === 'COMPLETED',
                        'class_conducted'  => $status === 'COMPLETED',
                        'ended_at'         => $status === 'COMPLETED' ? Carbon::parse($dateStr)->setTimeFromTimeString($entry->slot?->end_time ?? '20:00:00') : null,
                        'status'           => $status,
                    ]);

                    $sessionsByEntry[$entry->id][] = $cs;
                }
                $sessionDate->addWeek();
            }
        }

        // ═══════════════════════════════════════════════════════════
        // 12. ATTENDANCE (for COMPLETED sessions)
        // ═══════════════════════════════════════════════════════════
        $sfcEnroll1 = Enrollment::where('student_id', $s1->id)->where('batch_id', $sfcBatch->id)->first();
        $sfcEnroll3 = Enrollment::where('student_id', $s3->id)->where('batch_id', $sfcBatch->id)->first();

        // SFC sessions attendance
        foreach ($allEntries->where('batch_id', $sfcBatch->id) as $entry) {
            $completedSessions = collect($sessionsByEntry[$entry->id] ?? [])->where('status', 'COMPLETED');
            foreach ($completedSessions as $cs) {
                Attendance::create(['class_session_id' => $cs->id, 'student_id' => $s1->id, 'enrollment_id' => $sfcEnroll1?->id, 'status' => 'PRESENT']);
                Attendance::create(['class_session_id' => $cs->id, 'student_id' => $s3->id, 'enrollment_id' => $sfcEnroll3?->id, 'status' => fake()->randomElement(['PRESENT', 'ABSENT', 'LATE'])]);
            }
        }

        // BA sessions attendance
        foreach ($allEntries->where('batch_id', $baBatch->id) as $entry) {
            $completedSessions = collect($sessionsByEntry[$entry->id] ?? [])->where('status', 'COMPLETED');
            foreach ($completedSessions as $cs) {
                Attendance::create(['class_session_id' => $cs->id, 'student_id' => $s2->id, 'enrollment_id' => $baEnroll->id, 'status' => 'PRESENT']);
            }
        }

        // Log module covered for first completed session of each entry (optional teacher notes)
        $moduleCoverageMap = [
            $reSfcDawah->id   => $mD1->id,
            $reSfcAqeedah->id => $mA1->id,
            $reSfcFiqh->id    => $mF1->id,
            $reSfcTajweed->id => $mT1->id,
            $reBaAqeedah->id  => $mA1->id,
            $reBaFiqh->id     => $mF1->id,
        ];
        foreach ($moduleCoverageMap as $entryId => $moduleId) {
            $firstCompleted = collect($sessionsByEntry[$entryId] ?? [])->where('status', 'COMPLETED')->first();
            $firstCompleted?->update(['module_covered_id' => $moduleId]);
        }

        // ═══════════════════════════════════════════════════════════
        // 13. LEARNING RESOURCES
        // ═══════════════════════════════════════════════════════════
        LearningResource::create(['module_id' => $mD1->id, 'type' => 'VIDEO', 'title' => 'লেকচার ১: দাওয়াহর মূলনীতি',    'url' => 'https://youtube.com/watch?v=iom_dawah1',  'sort_order' => 1]);
        LearningResource::create(['module_id' => $mA1->id, 'type' => 'PDF',   'title' => 'আকীদাহ মডিউল ১ হ্যান্ডআউট',    'url' => 'https://iom.edu.bd/aqeedah1.pdf',          'sort_order' => 1]);
        LearningResource::create(['module_id' => $mF1->id, 'type' => 'VIDEO', 'title' => 'FQH-1: তাহারাত ভিডিও লেকচার',  'url' => 'https://youtube.com/watch?v=iom_fiqh1',   'sort_order' => 1]);
        LearningResource::create(['module_id' => $mT1->id, 'type' => 'PDF',   'title' => 'তাজবীদ: আরবী হরফ চার্ট',       'url' => 'https://iom.edu.bd/tajweed_chart.pdf',     'sort_order' => 1]);

        // ═══════════════════════════════════════════════════════════
        // 14. EXAMS
        // ═══════════════════════════════════════════════════════════
        $examSFC = Exam::create([
            'subject_id' => $subAqeedah->id, 'semester_id' => null,
            'title' => 'আক্বিদা মিড-টার্ম (SFC)', 'type' => 'MIDTERM',
            'exam_date' => '2026-07-20', 'start_time' => '19:30:00', 'duration_minutes' => 90,
            'full_marks' => 50, 'pass_marks' => 20, 'status' => 'COMPLETED',
        ]);
        $examBA = Exam::create([
            'subject_id' => $subAqeedah->id, 'semester_id' => $sem1->id,
            'title' => 'আক্বিদা চূড়ান্ত পরীক্ষা (B.A. 1st Sem)', 'type' => 'FINAL',
            'exam_date' => '2026-08-10', 'start_time' => '10:00:00', 'duration_minutes' => 120,
            'full_marks' => 100, 'pass_marks' => 40, 'status' => 'SCHEDULED',
        ]);
        foreach ([$s1, $s3] as $idx => $st) {
            $enroll = Enrollment::where('student_id', $st->id)->where('batch_id', $sfcBatch->id)->first();
            ExamAttendee::create(['exam_id' => $examSFC->id, 'student_id' => $st->id, 'batch_id' => $sfcBatch->id, 'enrollment_id' => $enroll?->id, 'is_eligible' => true, 'admit_card_no' => 'ADM-SFC-2026-' . ($idx + 1)]);
        }
        ExamAttendee::create(['exam_id' => $examBA->id, 'student_id' => $s2->id, 'batch_id' => $baBatch->id, 'enrollment_id' => $baEnroll->id, 'is_eligible' => true, 'admit_card_no' => 'ADM-BA-2026-101']);

        // ═══════════════════════════════════════════════════════════
        // 15. SYSTEM SETTINGS
        // ═══════════════════════════════════════════════════════════
        Setting::create(['key' => 'institute_name',          'value' => 'Islamic Online Media (IOM)',  'type' => 'string', 'group' => 'general',  'label' => 'Institute Name']);
        Setting::create(['key' => 'weekend_days',            'value' => 'FRI,SAT',                    'type' => 'string', 'group' => 'academic', 'label' => 'Weekend Days']);
        Setting::create(['key' => 'min_attendance_required', 'value' => '0',                          'type' => 'bool',   'group' => 'academic', 'label' => 'Require Min Attendance for Exam']);
        Setting::create(['key' => 'min_attendance_percent',  'value' => '75',                         'type' => 'int',    'group' => 'academic', 'label' => 'Minimum Attendance %']);
        Setting::create(['key' => 'final_result_policy',     'value' => 'BEST_ATTEMPT',               'type' => 'string', 'group' => 'academic', 'label' => 'Multi-attempt Result Policy']);
        Setting::create(['key' => 'due_enforcement_level',   'value' => 'NONE',                       'type' => 'string', 'group' => 'accounts', 'label' => 'Fee Due Enforcement Level']);
    }
}
