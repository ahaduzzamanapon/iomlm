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
use App\Models\Result;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectModule;
use App\Models\SubjectTeacherAssignment;
use App\Models\Teacher;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ═════════════════════════════════════════════════════════════════
        // 1. ADMIN USER
        // ═════════════════════════════════════════════════════════════════
        $adminUser = User::create([
            'name'     => 'IOM Central Admin',
            'email'    => 'admin@learningplus.com',
            'password' => $password,
            'role'     => 'admin',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 2. TEACHERS (USERS & PROFILES)
        // ═════════════════════════════════════════════════════════════════
        $uTeacher1 = User::create(['name' => 'Dr. Shaikh Ahmadullah', 'email' => 'teacher@learningplus.com', 'password' => $password, 'role' => 'teacher']);
        $uTeacher2 = User::create(['name' => 'Dr. Manzur-e-Elahi',    'email' => 'ada@learningplus.com',     'password' => $password, 'role' => 'teacher']);
        $uTeacher3 = User::create(['name' => 'Prof. Abu Bakr Muhammad','email' => 'shannon@learningplus.com', 'password' => $password, 'role' => 'teacher']);

        $teacherProf1 = Teacher::create([
            'employee_id' => 'EMP-IOM-101', 'name' => 'Dr. Shaikh Ahmadullah', 'email' => 'teacher@learningplus.com',
            'phone' => '+8801711112233', 'designation' => 'Chief Islamic Scholar', 'is_active' => true,
        ]);
        $teacherProf2 = Teacher::create([
            'employee_id' => 'EMP-IOM-102', 'name' => 'Dr. Manzur-e-Elahi', 'email' => 'ada@learningplus.com',
            'phone' => '+8801722334455', 'designation' => 'Professor of Fiqh', 'is_active' => true,
        ]);
        $teacherProf3 = Teacher::create([
            'employee_id' => 'EMP-IOM-103', 'name' => 'Prof. Abu Bakr Muhammad', 'email' => 'shannon@learningplus.com',
            'phone' => '+8801733445566', 'designation' => 'Professor of Tajweed', 'is_active' => true,
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 3. STUDENTS (USERS & PROFILES)
        // ═════════════════════════════════════════════════════════════════
        User::create(['name' => 'Abdullah Al Mamun', 'email' => 'student@learningplus.com', 'password' => $password, 'role' => 'student']);
        User::create(['name' => 'Ayesha Siddiqua',  'email' => 'sarah@learningplus.com',   'password' => $password, 'role' => 'student']);
        User::create(['name' => 'Tanvir Hossain',   'email' => 'tanvir@gmail.com',         'password' => $password, 'role' => 'student']);

        $studentProf1 = Student::create([
            'student_code' => 'STD-IOM-2026-001', 'name' => 'Abdullah Al Mamun', 'email' => 'student@learningplus.com',
            'phone' => '+8801811998877', 'date_of_birth' => '2002-04-12', 'gender' => 'Male', 'status' => 'ACTIVE',
        ]);
        $studentProf2 = Student::create([
            'student_code' => 'STD-IOM-2026-002', 'name' => 'Ayesha Siddiqua', 'email' => 'sarah@learningplus.com',
            'phone' => '+8801822887766', 'date_of_birth' => '2003-09-25', 'gender' => 'Female', 'status' => 'ACTIVE',
        ]);
        $studentProf3 = Student::create([
            'student_code' => 'STD-IOM-2026-003', 'name' => 'Tanvir Hossain', 'email' => 'tanvir@gmail.com',
            'phone' => '+8801833776655', 'date_of_birth' => '2004-01-10', 'gender' => 'Male', 'status' => 'ACTIVE',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 4. ACADEMIC SETUP & COURSES
        // ═════════════════════════════════════════════════════════════════
        $year2026 = AcademicYear::create([
            'name'       => 'IOM Academic Year 2026',
            'start_date' => '2026-01-01',
            'end_date'   => '2026-12-31',
            'is_active'  => true,
        ]);

        AcademicSession::create([
            'academic_year_id' => $year2026->id,
            'name'             => 'Spring Session 2026',
            'is_active'        => true,
        ]);

        // Course 1: Subject-Based (SFC)
        $sfcCourse = Course::create([
            'name'           => 'SSC ফরজে আইন (Short Foundation Course - SFC)',
            'type'           => 'SUBJECT_BASED',
            'duration_value' => 6,
            'duration_unit'  => 'MONTH',
            'is_active'      => true,
        ]);

        // Course 2: Semester-Based (BA Degree)
        $baCourse = Course::create([
            'name'           => 'B.A. in Islamic Studies (ইসলামিক স্টাডিজে বি.এ. ডিগ্রি)',
            'type'           => 'SEMESTER_BASED',
            'duration_value' => 4,
            'duration_unit'  => 'YEAR',
            'is_active'      => true,
        ]);

        $baSem1 = Semester::create(['course_id' => $baCourse->id, 'sequence_no' => 1, 'name' => '1st Semester (প্রথম সেমিস্টার)']);
        $baSem2 = Semester::create(['course_id' => $baCourse->id, 'sequence_no' => 2, 'name' => '2nd Semester (দ্বিতীয় সেমিস্টার)']);

        // Subjects & Modules
        $dawahSubj   = Subject::create(['name' => 'দাওয়াহ (Dawah Studies)',        'code' => 'DWH-101', 'credit' => 2, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);
        $aqeedahSubj = Subject::create(['name' => 'আক্বিদা (Islamic Aqeedah)',       'code' => 'AQD-101', 'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);
        $fiqhSubj    = Subject::create(['name' => 'ফিক্বহ (Islamic Fiqh & Rulings)', 'code' => 'FQH-101', 'credit' => 4, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);
        $tajweedSubj = Subject::create(['name' => 'তাজবীদ (Tajweed & Recitation)',   'code' => 'TAJ-101', 'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'is_active' => true]);

        $modD1 = SubjectModule::create(['subject_id' => $dawahSubj->id,   'category' => 'দাওয়াহ',  'sequence_no' => 1, 'title' => 'DWH-1: Dawah to Muslim',              'is_locked_until_previous' => false]);
        $modA1 = SubjectModule::create(['subject_id' => $aqeedahSubj->id, 'category' => 'আক্বিদা', 'sequence_no' => 1, 'title' => 'AQD-1: ঈমানের পরিচয় ও আরকানুল ঈমান',    'is_locked_until_previous' => false]);
        $modF1 = SubjectModule::create(['subject_id' => $fiqhSubj->id,    'category' => 'ফিক্বহ',  'sequence_no' => 1, 'title' => 'FQH-1: তাহারাতের পরিচয় ও নাজাসাত',      'is_locked_until_previous' => false]);
        $modT1 = SubjectModule::create(['subject_id' => $tajweedSubj->id, 'category' => 'তাজবীদ', 'sequence_no' => 1, 'title' => 'TAJ-1: কুরআন শিক্ষার গুরুত্ব ও আরবী হরফ', 'is_locked_until_previous' => false]);

        // SFC Subject Mappings
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $dawahSubj->id,   'semester_id' => null, 'sort_order' => 1]);
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $aqeedahSubj->id, 'semester_id' => null, 'sort_order' => 2]);
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $fiqhSubj->id,    'semester_id' => null, 'sort_order' => 3]);
        CourseSubjectMap::create(['course_id' => $sfcCourse->id, 'subject_id' => $tajweedSubj->id, 'semester_id' => null, 'sort_order' => 4]);

        // BA Degree Semester Mappings
        CourseSubjectMap::create(['course_id' => $baCourse->id, 'subject_id' => $aqeedahSubj->id, 'semester_id' => $baSem1->id, 'sort_order' => 1]);
        CourseSubjectMap::create(['course_id' => $baCourse->id, 'subject_id' => $fiqhSubj->id,    'semester_id' => $baSem1->id, 'sort_order' => 2]);
        CourseSubjectMap::create(['course_id' => $baCourse->id, 'subject_id' => $dawahSubj->id,   'semester_id' => $baSem2->id, 'sort_order' => 1]);

        // Teacher Assignments
        SubjectTeacherAssignment::create(['subject_id' => $dawahSubj->id,   'teacher_id' => $teacherProf1->id, 'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $aqeedahSubj->id, 'teacher_id' => $teacherProf1->id, 'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $fiqhSubj->id,    'teacher_id' => $teacherProf2->id, 'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $tajweedSubj->id, 'teacher_id' => $teacherProf3->id, 'batch_id' => null]);

        // ═════════════════════════════════════════════════════════════════
        // 5. BATCHES & ENROLLMENTS
        // ═════════════════════════════════════════════════════════════════
        // Batch 1: Subject-Based SFC
        $sfcBatch = Batch::create([
            'course_id'                => $sfcCourse->id,
            'academic_year_id'         => $year2026->id,
            'name'                     => 'SSC ফরজে আইন 2026 Batch 01',
            'batch_code'               => 'SFC-2026-01',
            'start_date'               => '2026-01-15',
            'expected_end_date'        => '2026-07-15',
            'status'                   => 'ACTIVE',
            'subject_version_snapshot' => 1,
        ]);

        // Batch 2: Semester-Based BA Degree
        $baBatch = Batch::create([
            'course_id'                => $baCourse->id,
            'academic_year_id'         => $year2026->id,
            'name'                     => 'B.A. Islamic Studies 2026 Batch 01',
            'batch_code'               => 'BA-IS-2026-01',
            'start_date'               => '2026-01-15',
            'expected_end_date'        => '2029-12-31',
            'status'                   => 'ACTIVE',
            'subject_version_snapshot' => 1,
        ]);

        // Enroll Student 1 (Abdullah) & Student 3 (Tanvir) in Subject-Based SFC
        foreach ([$studentProf1, $studentProf3] as $st) {
            AdmissionForm::create([
                'student_id'           => $st->id,
                'interested_course_id' => $sfcCourse->id,
                'attempt_no'           => 1,
                'status'               => 'APPROVED',
                'reviewed_by'          => $adminUser->id,
                'reviewed_at'          => now()->subDays(10),
                'notes'                => 'Approved for SFC Course',
            ]);

            Enrollment::create([
                'student_id'  => $st->id,
                'batch_id'    => $sfcBatch->id,
                'course_id'   => $sfcCourse->id,
                'semester_id' => null,
                'enrolled_at' => '2026-01-15',
                'status'      => 'ACTIVE',
            ]);
        }

        // Enroll Student 2 (Ayesha Siddiqua) in SEMESTER_BASED B.A. Course (1st Semester)!
        AdmissionForm::create([
            'student_id'           => $studentProf2->id,
            'interested_course_id' => $baCourse->id,
            'attempt_no'           => 1,
            'status'               => 'APPROVED',
            'reviewed_by'          => $adminUser->id,
            'reviewed_at'          => now()->subDays(10),
            'notes'                => 'Approved for B.A. Degree Course',
        ]);

        $baEnrollment = Enrollment::create([
            'student_id'  => $studentProf2->id,
            'batch_id'    => $baBatch->id,
            'course_id'   => $baCourse->id,
            'semester_id' => $baSem1->id, // Semester 1!
            'enrolled_at' => '2026-01-15',
            'status'      => 'ACTIVE',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 6. TIMELINES & CLASS SESSIONS WITH MEET LINKS
        // ═════════════════════════════════════════════════════════════════
        // Timelines for SFC Batch
        $tl1 = Timeline::create(['batch_id' => $sfcBatch->id, 'subject_id' => $dawahSubj->id,   'module_id' => $modD1->id, 'scheduled_date' => '2026-01-20', 'status' => 'COMPLETED']);
        $tl2 = Timeline::create(['batch_id' => $sfcBatch->id, 'subject_id' => $aqeedahSubj->id, 'module_id' => $modA1->id, 'scheduled_date' => '2026-01-27', 'status' => 'SCHEDULED']);
        $tl3 = Timeline::create(['batch_id' => $sfcBatch->id, 'subject_id' => $fiqhSubj->id,    'module_id' => $modF1->id, 'scheduled_date' => '2026-02-03', 'status' => 'SCHEDULED']);
        $tl4 = Timeline::create(['batch_id' => $sfcBatch->id, 'subject_id' => $tajweedSubj->id, 'module_id' => $modT1->id, 'scheduled_date' => '2026-02-10', 'status' => 'SCHEDULED']);

        // Timelines for BA Semester 1 Batch
        $tlBA1 = Timeline::create(['batch_id' => $baBatch->id, 'subject_id' => $aqeedahSubj->id, 'module_id' => $modA1->id, 'scheduled_date' => '2026-01-28', 'status' => 'SCHEDULED']);
        $tlBA2 = Timeline::create(['batch_id' => $baBatch->id, 'subject_id' => $fiqhSubj->id,    'module_id' => $modF1->id, 'scheduled_date' => '2026-02-04', 'status' => 'SCHEDULED']);

        // Class Sessions
        $cs1 = ClassSession::create(['timeline_id' => $tl1->id,   'teacher_id' => $teacherProf1->id, 'start_time' => '20:00:00', 'meeting_link' => 'https://meet.google.com/iom-dwh1-live', 'teacher_present' => true, 'class_conducted' => true, 'started_at' => '2026-01-20 20:00:00', 'ended_at' => '2026-01-20 21:30:00', 'status' => 'COMPLETED']);
        $cs2 = ClassSession::create(['timeline_id' => $tl2->id,   'teacher_id' => $teacherProf1->id, 'start_time' => '20:00:00', 'meeting_link' => 'https://meet.google.com/iom-aqd1-live', 'status' => 'SCHEDULED']);
        $cs3 = ClassSession::create(['timeline_id' => $tl3->id,   'teacher_id' => $teacherProf2->id, 'start_time' => '20:30:00', 'meeting_link' => 'https://meet.google.com/iom-fqh1-live', 'status' => 'SCHEDULED']);
        $cs4 = ClassSession::create(['timeline_id' => $tl4->id,   'teacher_id' => $teacherProf3->id, 'start_time' => '21:00:00', 'meeting_link' => 'https://meet.google.com/iom-taj1-live', 'status' => 'SCHEDULED']);

        $csBA1 = ClassSession::create(['timeline_id' => $tlBA1->id, 'teacher_id' => $teacherProf1->id, 'start_time' => '20:00:00', 'meeting_link' => 'https://meet.google.com/iom-ba-aqd1', 'status' => 'SCHEDULED']);
        $csBA2 = ClassSession::create(['timeline_id' => $tlBA2->id, 'teacher_id' => $teacherProf2->id, 'start_time' => '20:30:00', 'meeting_link' => 'https://meet.google.com/iom-ba-fqh1', 'status' => 'SCHEDULED']);

        // Attendance
        foreach ([$studentProf1, $studentProf3] as $st) {
            Attendance::create(['class_session_id' => $cs1->id, 'student_id' => $st->id, 'enrollment_id' => $st->enrollments->first()?->id, 'status' => 'PRESENT']);
        }

        // Learning Resources
        LearningResource::create(['module_id' => $modD1->id, 'type' => 'VIDEO', 'title' => 'লেকচার ১: দাওয়াহ টু মুসলিম (Dr. Shaikh Ahmadullah)', 'url' => 'https://youtube.com/watch?v=iom_demo_dawah', 'sort_order' => 1]);
        LearningResource::create(['module_id' => $modA1->id, 'type' => 'PDF',   'title' => 'আকীদাহ মডিউল ১ হ্যান্ডআউট (PDF)',                'url' => 'https://iom.edu.bd/resources/aqeedah1.pdf',       'sort_order' => 1]);

        // Exams for SFC and BA Semester 1
        $midtermExamSFC = Exam::create([
            'subject_id' => $aqeedahSubj->id, 'semester_id' => null, 'title' => 'ইসলামিক আকীদাহ মিডটার্ম (SFC)',
            'type' => 'MIDTERM', 'exam_date' => '2026-02-15', 'start_time' => '19:30:00', 'duration_minutes' => 90,
            'full_marks' => 50, 'pass_marks' => 20, 'status' => 'SCHEDULED',
        ]);

        $midtermExamBA = Exam::create([
            'subject_id' => $aqeedahSubj->id, 'semester_id' => $baSem1->id, 'title' => 'আকীদাহ ১ম সেমিস্টার পরীক্ষা (B.A. Degree)',
            'type' => 'FINAL', 'exam_date' => '2026-03-10', 'start_time' => '10:00:00', 'duration_minutes' => 120,
            'full_marks' => 100, 'pass_marks' => 40, 'status' => 'SCHEDULED',
        ]);

        foreach ([$studentProf1, $studentProf3] as $idx => $st) {
            ExamAttendee::create([
                'exam_id'       => $midtermExamSFC->id,
                'student_id'    => $st->id,
                'batch_id'      => $sfcBatch->id,
                'enrollment_id' => $st->enrollments->first()?->id,
                'is_eligible'   => true,
                'admit_card_no' => 'ADM-SFC-2026-10' . ($idx + 1),
            ]);
        }

        ExamAttendee::create([
            'exam_id'       => $midtermExamBA->id,
            'student_id'    => $studentProf2->id,
            'batch_id'      => $baBatch->id,
            'enrollment_id' => $baEnrollment->id,
            'is_eligible'   => true,
            'admit_card_no' => 'ADM-BA-2026-101',
        ]);

        // System Settings
        Setting::create(['key' => 'institute_name',         'value' => 'Islamic Online Media (IOM)',       'type' => 'string', 'group' => 'general', 'label' => 'Institute Name']);
        Setting::create(['key' => 'min_attendance_required', 'value' => '0',                                'type' => 'bool',   'group' => 'academic', 'label' => 'Require Minimum Attendance for Exam']);
        Setting::create(['key' => 'min_attendance_percent',  'value' => '75',                               'type' => 'int',    'group' => 'academic', 'label' => 'Minimum Attendance %']);
        Setting::create(['key' => 'final_result_policy',     'value' => 'BEST_ATTEMPT',                     'type' => 'string', 'group' => 'academic', 'label' => 'Multi-attempt Result Policy']);
        Setting::create(['key' => 'due_enforcement_level',   'value' => 'NONE',                             'type' => 'string', 'group' => 'accounts', 'label' => 'Fee Due Enforcement Level']);
    }
}
