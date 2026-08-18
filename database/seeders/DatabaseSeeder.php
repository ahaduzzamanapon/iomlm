<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectModule;
use App\Models\AcademicYear;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\BatchSemesterPosition;
use App\Models\Enrollment;
use App\Models\AdmissionForm;
use App\Models\SubjectTeacherAssignment;
use App\Models\RoutineSlot;
use App\Models\RoutineEntry;
use App\Models\ClassSession;
use App\Models\Attendance;
use App\Models\Question;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamSubmission;
use App\Models\ExamAnswer;
use App\Models\Result;
use App\Models\LearningResource;
use App\Models\HolidayCalendar;
use App\Models\Notice;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\FeeHead;
use App\Models\CourseFeePackage;
use App\Models\CourseFeePackageItem;
use App\Models\BloodGroup;
use App\Models\Religion;
use App\Models\Division;
use App\Models\District;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $pass = Hash::make('password');

        // ══════════════════════════════════════════════════════════════
        // 0. SETTINGS
        // settings: key, value, type, group, label
        // ══════════════════════════════════════════════════════════════
        foreach ([
            ['key' => 'institute_name',         'value' => 'Islamic Online Madrasah (IOM)', 'type' => 'string', 'group' => 'general',  'label' => 'Institute Name'],
            ['key' => 'institute_phone',         'value' => '09638-113322',                  'type' => 'string', 'group' => 'general',  'label' => 'Phone'],
            ['key' => 'institute_email',         'value' => 'info@iom.edu.bd',               'type' => 'string', 'group' => 'general',  'label' => 'Email'],
            ['key' => 'weekend_days',            'value' => 'FRI',                           'type' => 'string', 'group' => 'academic', 'label' => 'Weekend Days'],
            ['key' => 'min_attendance_required', 'value' => '0',                             'type' => 'bool',   'group' => 'academic', 'label' => 'Require Min Attendance'],
            ['key' => 'min_attendance_percent',  'value' => '75',                            'type' => 'int',    'group' => 'academic', 'label' => 'Min Attendance %'],
            ['key' => 'final_result_policy',     'value' => 'BEST_ATTEMPT',                  'type' => 'string', 'group' => 'academic', 'label' => 'Result Policy'],
            ['key' => 'due_enforcement_level',   'value' => 'NONE',                          'type' => 'string', 'group' => 'accounts', 'label' => 'Fee Due Enforcement'],
        ] as $s) {
            Setting::create($s);
        }

        // ══════════════════════════════════════════════════════════════
        // 0b. LOOKUPS
        // ══════════════════════════════════════════════════════════════
        foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg) {
            BloodGroup::create(['name' => $bg, 'is_active' => true]);
        }
        foreach (['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Other'] as $rel) {
            Religion::create(['name' => $rel, 'is_active' => true]);
        }
        foreach (['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh'] as $divName) {
            $div = Division::create(['name' => $divName]);
            District::create(['name' => $divName . ' Sadar', 'division_id' => $div->id]);
        }

        // ══════════════════════════════════════════════════════════════
        // 1. USERS
        // ══════════════════════════════════════════════════════════════
        $adminUser = User::create(['name' => 'IOM Admin', 'email' => 'admin@iom.edu.bd', 'password' => $pass, 'role' => 'admin']);

        // Teachers
        $teacherDefs = [
            ['name' => 'Ustaz Abdullah Al-Mamun', 'email' => 'teacher.tajweed@iom.edu.bd', 'phone' => '01711000001', 'dept' => 'তাজবীদ',           'desig' => 'Senior Ustaz'],
            ['name' => 'Ustaz Abdur Rahman Khan',  'email' => 'teacher.fiqh@iom.edu.bd',   'phone' => '01711000002', 'dept' => 'ফিকহ',             'desig' => 'Ustaz'],
            ['name' => 'Ustaz Muhammad Ibrahim',   'email' => 'teacher.quran@iom.edu.bd',  'phone' => '01711000003', 'dept' => 'কুরআন',            'desig' => 'Ustaz'],
            ['name' => 'Ustaza Fatima Begum',      'email' => 'teacher.hadith@iom.edu.bd', 'phone' => '01711000004', 'dept' => 'হাদিস ও সুন্নাহ', 'desig' => 'Ustaza'],
            ['name' => 'Ustaz Zakariya Ahmed',     'email' => 'teacher.arabic@iom.edu.bd', 'phone' => '01711000005', 'dept' => 'আরবী ভাষা',       'desig' => 'Ustaz'],
        ];
        $teachers = [];
        foreach ($teacherDefs as $td) {
            $u = User::create(['name' => $td['name'], 'email' => $td['email'], 'password' => $pass, 'role' => 'teacher']);
            $teachers[] = Teacher::create([
                'user_id'     => $u->id,
                'name'        => $td['name'],
                'email'       => $td['email'],
                'phone'       => $td['phone'],
                'department'  => $td['dept'],
                'designation' => $td['desig'],
                'is_active'   => true,
                'gender'      => 'Male',
                'nationality' => 'Bangladeshi',
            ]);
        }

        // Students
        $studentDefs = [
            ['name' => 'Muhammad Rafiqul Islam', 'email' => 'student1@iom.edu.bd', 'phone' => '01812000001', 'gender' => 'Male',   'code' => 'IOM-2026-001'],
            ['name' => 'Abdullah Al-Noman',       'email' => 'student2@iom.edu.bd', 'phone' => '01812000002', 'gender' => 'Male',   'code' => 'IOM-2026-002'],
            ['name' => 'Fatima Tuz Zuhra',        'email' => 'student3@iom.edu.bd', 'phone' => '01812000003', 'gender' => 'Female', 'code' => 'IOM-2026-003'],
            ['name' => 'Khadija Akter',           'email' => 'student4@iom.edu.bd', 'phone' => '01812000004', 'gender' => 'Female', 'code' => 'IOM-2026-004'],
            ['name' => 'Omar Faruk Siddiqui',     'email' => 'student5@iom.edu.bd', 'phone' => '01812000005', 'gender' => 'Male',   'code' => 'IOM-2026-005'],
            ['name' => 'Aisha Siddiqua',          'email' => 'student6@iom.edu.bd', 'phone' => '01812000006', 'gender' => 'Female', 'code' => 'IOM-2026-006'],
            ['name' => 'Ibrahim Khalilullah',     'email' => 'student7@iom.edu.bd', 'phone' => '01812000007', 'gender' => 'Male',   'code' => 'IOM-2026-007'],
            ['name' => 'Maryam Salam',            'email' => 'student8@iom.edu.bd', 'phone' => '01812000008', 'gender' => 'Female', 'code' => 'IOM-2026-008'],
        ];
        $students = [];
        foreach ($studentDefs as $i => $sd) {
            $u = User::create(['name' => $sd['name'], 'email' => $sd['email'], 'password' => $pass, 'role' => 'student']);
            $students[] = Student::create([
                'user_id'       => $u->id,
                'name'          => $sd['name'],
                'email'         => $sd['email'],
                'phone'         => $sd['phone'],
                'gender'        => $sd['gender'],
                'student_code'  => $sd['code'],
                'status'        => 'ACTIVE',
                'date_of_birth' => Carbon::now()->subYears(20 + $i)->format('Y-m-d'),
                'address'       => 'Dhaka, Bangladesh',
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // 2. COURSES
        // courses.type ENUM: ['SUBJECT_BASED', 'SEMESTER_BASED']
        // courses.duration_unit ENUM: ['MONTH', 'YEAR']
        // ══════════════════════════════════════════════════════════════
        $alimCourse = Course::create([
            'name'           => 'আলিম কোর্স (Alim Course)',
            'type'           => 'SEMESTER_BASED',
            'duration_value' => 36,
            'duration_unit'  => 'MONTH',
            'is_active'      => true,
        ]);
        $nazeraCourse = Course::create([
            'name'           => 'নাজেরা কোর্স (Nazera Course)',
            'type'           => 'SUBJECT_BASED',
            'duration_value' => 6,
            'duration_unit'  => 'MONTH',
            'is_active'      => true,
        ]);
        $hifzCourse = Course::create([
            'name'           => 'হিফজুল কুরআন কোর্স',
            'type'           => 'SUBJECT_BASED',
            'duration_value' => 6,
            'duration_unit'  => 'MONTH',
            'is_active'      => true,
        ]);

        // ══════════════════════════════════════════════════════════════
        // 3. SEMESTERS
        // ══════════════════════════════════════════════════════════════
        $semesters = [];
        foreach ([
            1 => '১ম বর্ষ — ১ম সেমিস্টার',
            2 => '১ম বর্ষ — ২য় সেমিস্টার',
            3 => '২য় বর্ষ — ১ম সেমিস্টার',
            4 => '২য় বর্ষ — ২য় সেমিস্টার',
            5 => '৩য় বর্ষ — ১ম সেমিস্টার',
            6 => '৩য় বর্ষ — ২য় সেমিস্টার',
        ] as $seq => $sname) {
            $semesters[$seq] = Semester::create(['course_id' => $alimCourse->id, 'sequence_no' => $seq, 'name' => $sname]);
        }

        // ══════════════════════════════════════════════════════════════
        // 4. SUBJECTS
        // subjects: name, code(unique), credit(tinyInt), full_marks, pass_marks, version(tinyInt), is_active
        // ══════════════════════════════════════════════════════════════
        $subjectDefs = [
            ['code' => 'TAJ-101', 'name' => 'তাজবীদ (Tajweed)',                 'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 0],
            ['code' => 'QUR-101', 'name' => 'কুরআন তিলাওয়াত ও ট্রান্সলেশন',   'credit' => 4, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 2],
            ['code' => 'HAD-101', 'name' => 'হাদিস ও সুন্নাহ (Hadith)',         'credit' => 4, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 3],
            ['code' => 'FIQ-101', 'name' => 'ফিকহ (Fiqh)',                      'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 1],
            ['code' => 'ARB-101', 'name' => 'আরবী ভাষা ও ব্যাকরণ (Arabic)',    'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 4],
            ['code' => 'SIR-101', 'name' => 'সীরাতুন নবী (ﷺ)',                 'credit' => 2, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 1],
            ['code' => 'AQD-101', 'name' => 'আক্বীদাহ (Aqeedah)',              'credit' => 3, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 0],
            ['code' => 'ISH-101', 'name' => 'ইসলামের ইতিহাস (Islamic History)', 'credit' => 2, 'full_marks' => 100, 'pass_marks' => 40, 'ti' => 2],
        ];
        $subjects = [];
        foreach ($subjectDefs as $sd) {
            $subj = Subject::create([
                'code'       => $sd['code'],
                'name'       => $sd['name'],
                'credit'     => $sd['credit'],
                'full_marks' => $sd['full_marks'],
                'pass_marks' => $sd['pass_marks'],
                'version'    => 1,
                'is_active'  => true,
            ]);
            $subjects[] = $subj;

            // Subject modules (category + sequence_no + subject_id must be unique)
            foreach ([
                ['অধ্যায় ১: পরিচিতি ও মূলনীতি', 1],
                ['অধ্যায় ২: মৌলিক বিষয়াবলী',    2],
                ['অধ্যায় ৩: প্রায়োগিক দিক',      3],
            ] as [$mname, $seq]) {
                SubjectModule::create([
                    'subject_id'  => $subj->id,
                    'title'       => $mname,
                    'description' => $mname . ' — ' . $sd['name'],
                    'sequence_no' => $seq,
                    'is_active'   => true,
                ]);
            }

            // Teacher assignment
            SubjectTeacherAssignment::create([
                'subject_id' => $subj->id,
                'teacher_id' => $teachers[$sd['ti']]->id,
                'is_active'  => true,
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // 5. ACADEMIC YEAR & SESSION
        // academic_years: name, start_date, end_date, is_active
        // academic_sessions: academic_year_id, name, is_active
        // ══════════════════════════════════════════════════════════════
        $academicYear = AcademicYear::create([
            'name'       => 'শিক্ষাবর্ষ ২০২৬',
            'start_date' => '2026-01-01',
            'end_date'   => '2026-12-31',
            'is_active'  => true,
        ]);
        $session = AcademicSession::create([
            'academic_year_id' => $academicYear->id,
            'name'             => '২০২৬ — ১৫তম ব্যাচ',
            'is_active'        => true,
        ]);

        // ══════════════════════════════════════════════════════════════
        // 6. BATCHES
        // batches: course_id, academic_year_id, name, batch_code, start_date, expected_end_date, status
        // status ENUM: ['PLANNED','ACTIVE','COMPLETED','CANCELLED']
        // ══════════════════════════════════════════════════════════════
        $alimBatch = Batch::create([
            'course_id'         => $alimCourse->id,
            'academic_year_id'  => $academicYear->id,
            'name'              => 'আলিম ব্যাচ ১৫ (২০২৬)',
            'batch_code'        => 'ALIM-B15',
            'start_date'        => '2026-01-01',
            'expected_end_date' => '2028-12-31',
            'status'            => 'ACTIVE',
            'monthly_fee'       => 500.00,
            'admission_fee'     => 1500.00,
        ]);
        // batch_semester_positions: batch_id, current_semester_id, started_at (date required)
        BatchSemesterPosition::create([
            'batch_id'            => $alimBatch->id,
            'current_semester_id' => $semesters[1]->id,
            'started_at'          => '2026-01-01',
        ]);

        $nazeraBatch = Batch::create([
            'course_id'         => $nazeraCourse->id,
            'academic_year_id'  => $academicYear->id,
            'name'              => 'নাজেরা ব্যাচ ১ (২০২৬)',
            'batch_code'        => 'NC-B01',
            'start_date'        => '2026-02-01',
            'expected_end_date' => '2026-07-31',
            'status'            => 'ACTIVE',
            'monthly_fee'       => 500.00,
            'admission_fee'     => 1000.00,
        ]);

        // ══════════════════════════════════════════════════════════════
        // 7. FEE HEADS & PACKAGES
        // fee_heads: name, slug, is_static, is_active, sort_order
        // course_fee_package_items: package_id, fee_head_id, label, quantity, amount_per_unit, total_amount, sort_order
        // ══════════════════════════════════════════════════════════════
        $fhAdmission = FeeHead::create(['name' => 'ভর্তি ফি (Admission Fee)', 'slug' => 'admission-fee', 'is_static' => true,  'is_active' => true, 'sort_order' => 1]);
        $fhMonthly   = FeeHead::create(['name' => 'মাসিক বেতন (Monthly Fee)', 'slug' => 'monthly-fee',   'is_static' => false, 'is_active' => true, 'sort_order' => 2]);
        $fhExam      = FeeHead::create(['name' => 'পরীক্ষার ফি (Exam Fee)',    'slug' => 'exam-fee',      'is_static' => false, 'is_active' => true, 'sort_order' => 3]);

        $alimPkg = CourseFeePackage::create(['course_id' => $alimCourse->id,  'name' => 'আলিম স্ট্যান্ডার্ড', 'is_default' => true, 'is_active' => true]);
        CourseFeePackageItem::create(['package_id' => $alimPkg->id, 'fee_head_id' => $fhAdmission->id, 'label' => 'ভর্তি ফি', 'quantity' => 1, 'amount_per_unit' => 1500, 'total_amount' => 1500, 'sort_order' => 1]);
        CourseFeePackageItem::create(['package_id' => $alimPkg->id, 'fee_head_id' => $fhMonthly->id,   'label' => 'মাসিক ফি', 'quantity' => 1, 'amount_per_unit' => 500,  'total_amount' => 500,  'sort_order' => 2]);

        $nazeraPkg = CourseFeePackage::create(['course_id' => $nazeraCourse->id, 'name' => 'নাজেরা স্ট্যান্ডার্ড', 'is_default' => true, 'is_active' => true]);
        CourseFeePackageItem::create(['package_id' => $nazeraPkg->id, 'fee_head_id' => $fhAdmission->id, 'label' => 'ভর্তি ফি', 'quantity' => 1, 'amount_per_unit' => 1000, 'total_amount' => 1000, 'sort_order' => 1]);
        CourseFeePackageItem::create(['package_id' => $nazeraPkg->id, 'fee_head_id' => $fhMonthly->id,   'label' => 'মাসিক ফি', 'quantity' => 1, 'amount_per_unit' => 500,  'total_amount' => 500,  'sort_order' => 2]);

        // ══════════════════════════════════════════════════════════════
        // 8. ENROLLMENTS & ADMISSION FORMS
        // ══════════════════════════════════════════════════════════════
        $enrollments = [];
        foreach ($students as $i => $student) {
            $batch   = $i < 6 ? $alimBatch  : $nazeraBatch;
            $course  = $i < 6 ? $alimCourse : $nazeraCourse;
            $semId   = $i < 6 ? $semesters[1]->id : null;
            $admFee  = $i < 6 ? 1500 : 1000;

            $form = AdmissionForm::create([
                'source'               => 'ADMIN',
                'application_no'       => 'APP-2026-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'student_id'           => $student->id,
                'interested_course_id' => $course->id,
                'batch_id'             => $batch->id,
                'academic_session_id'  => $session->id,
                'attempt_no'           => 1,
                'lead_source'          => 'Direct',
                'discount_percent'     => 0,
                'status'               => 'APPROVED',
                'reviewed_by'          => $adminUser->id,
                'reviewed_at'          => now(),
                'nationality'          => 'Bangladeshi',
                'present_house'        => 'Dhaka, Bangladesh',
            ]);

            $enroll = Enrollment::create([
                'student_id'        => $student->id,
                'batch_id'          => $batch->id,
                'course_id'         => $course->id,
                'semester_id'       => $semId,
                'admission_form_id' => $form->id,
                'enrolled_at'       => '2026-01-01',
                'status'            => 'ACTIVE',
            ]);
            $enrollments[$i] = $enroll;

            Invoice::create([
                'invoice_no'     => 'INV-ADM-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'student_id'     => $student->id,
                'enrollment_id'  => $enroll->id,
                'category'       => 'ADMISSION',
                'title'          => 'ভর্তি ফি — ' . $course->name,
                'amount'         => $admFee,
                'discount'       => 0,
                'payable_amount' => $admFee,
                'paid_amount'    => $admFee,
                'due_amount'     => 0,
                'status'         => 'PAID',
                'due_date'       => '2026-01-31',
                'created_by'     => $adminUser->id,
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // 9. ROUTINE SLOTS
        // routine_slots: name, start_time, end_time, sort_order
        // ══════════════════════════════════════════════════════════════
        $slots = [];
        foreach ([
            ['সকাল ক্লাস', '09:00', '10:00', 1],
            ['দুপুর ক্লাস', '14:00', '15:00', 2],
            ['বিকাল ক্লাস', '16:00', '17:00', 3],
            ['রাত ক্লাস',   '20:00', '21:00', 4],
        ] as [$sname, $start, $end, $order]) {
            $slots[] = RoutineSlot::create(['name' => $sname, 'start_time' => $start, 'end_time' => $end, 'sort_order' => $order]);
        }

        // ══════════════════════════════════════════════════════════════
        // 10. ROUTINE ENTRIES
        // day_of_week ENUM: ['SAT','SUN','MON','TUE','WED','THU','FRI']
        // ══════════════════════════════════════════════════════════════
        $dayMap = [0 => 'SUN', 1 => 'MON', 2 => 'TUE', 3 => 'WED', 4 => 'THU', 5 => 'FRI', 6 => 'SAT'];
        // [subjectIdx, slotIdx, dayOfWeekInt]
        $routineMap = [
            [0, 0, 0], // Tajweed   — Sunday   Morning
            [1, 3, 1], // Quran     — Monday   Night
            [2, 1, 2], // Hadith    — Tuesday  Afternoon
            [3, 2, 3], // Fiqh      — Wednesday Evening
            [4, 3, 4], // Arabic    — Thursday Night
            [5, 0, 6], // Seerah    — Saturday Morning
        ];
        $routineEntries = [];
        foreach ($routineMap as [$sIdx, $slotIdx, $dow]) {
            $routineEntries[] = RoutineEntry::create([
                'batch_id'    => $alimBatch->id,
                'subject_id'  => $subjects[$sIdx]->id,
                'slot_id'     => $slots[$slotIdx]->id,
                'teacher_id'  => $teachers[$subjectDefs[$sIdx]['ti']]->id,
                'day_of_week' => $dayMap[$dow],
                'title'       => $subjects[$sIdx]->name . ' ক্লাস',
                'is_override' => false,
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // 11. CLASS SESSIONS + ATTENDANCE (past 4 weeks + 2 future)
        // class_sessions.status ENUM: ['UPCOMING','SCHEDULED','RUNNING','COMPLETED','CANCELLED','RESCHEDULED']
        // attendances.status ENUM: ['PRESENT','ABSENT','LATE','EXCUSED']
        // ══════════════════════════════════════════════════════════════
        $alimStudents = array_slice($students, 0, 6);
        $carbonDayMap = ['SUN' => 0, 'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6];
        $sessionNum   = 0;

        for ($week = -4; $week <= 2; $week++) {
            foreach ($routineEntries as $ri => $re) {
                $dayInt      = $carbonDayMap[$re->day_of_week];
                $sessionDate = Carbon::now()->startOfWeek(Carbon::SUNDAY)->addWeeks($week)->addDays($dayInt);
                $isPast      = $sessionDate->isPast();

                $classSess = ClassSession::create([
                    'batch_id'        => $alimBatch->id,
                    'subject_id'      => $re->subject_id,
                    'routine_entry_id'=> $re->id,
                    'teacher_id'      => $re->teacher_id,
                    'session_date'    => $sessionDate->format('Y-m-d'),
                    'start_time'      => $slots[$routineMap[$ri][1]]->start_time,
                    'meeting_link'    => 'https://zoom.us/j/iom-class-' . ($sessionNum + 1),
                    'status'          => $isPast ? 'COMPLETED' : 'SCHEDULED',
                    'class_conducted' => $isPast ? 1 : 0,
                    'teacher_present' => $isPast ? 1 : 0,
                ]);

                if ($isPast) {
                    foreach ($alimStudents as $ai => $stu) {
                        Attendance::create([
                            'class_session_id' => $classSess->id,
                            'student_id'       => $stu->id,
                            'enrollment_id'    => $enrollments[$ai]->id,
                            'status'           => $ai < 5 ? 'PRESENT' : 'ABSENT',
                        ]);
                    }
                }
                $sessionNum++;
            }
        }

        // ══════════════════════════════════════════════════════════════
        // 12. QUESTION BANK
        // questions: subject_id, question_type, question_text, options(json), correct_option_id, difficulty, is_active
        // ══════════════════════════════════════════════════════════════
        $opt = fn($a, $b, $c, $d) => [['id'=>'a','text'=>$a],['id'=>'b','text'=>$b],['id'=>'c','text'=>$c],['id'=>'d','text'=>$d]];
        $mcq = fn($subjId, $q, $a, $b, $c, $d, $correct, $diff) => Question::create([
            'subject_id' => $subjId, 'question_type' => 'MCQ', 'question_text' => $q,
            'options' => $opt($a, $b, $c, $d), 'correct_option_id' => $correct, 'difficulty' => $diff, 'is_active' => true,
        ]);
        $written = fn($subjId, $q, $diff) => Question::create([
            'subject_id' => $subjId, 'question_type' => 'WRITTEN', 'question_text' => $q,
            'options' => [], 'correct_option_id' => '', 'difficulty' => $diff, 'is_active' => true,
        ]);

        // Tajweed (subj idx 0)
        $tajMcqQs = [
            $mcq($subjects[0]->id, 'ইখফা কাকে বলে?', 'নুন সাকিন বা তানভিনকে লুকিয়ে পড়া', 'নুন সাকিনকে স্পষ্টভাবে পড়া', 'নুন সাকিনকে মিলিয়ে পড়া', 'নুন সাকিনকে পরিবর্তন করা', 'a', 'easy'),
            $mcq($subjects[0]->id, 'ইদগাম কয় প্রকার?', 'দুই প্রকার', 'তিন প্রকার', 'চার প্রকার', 'পাঁচ প্রকার', 'a', 'medium'),
            $mcq($subjects[0]->id, 'ইক্বলাব কোন হরফে হয়?', 'বা (ب)', 'মিম (م)', 'নুন (ن)', 'ওয়াও (و)', 'a', 'easy'),
            $mcq($subjects[0]->id, 'মদ্দে তাবিয়ি কতটুকু লম্বা করতে হয়?', '২ হরকত', '৪ হরকত', '৬ হরকত', '১ হরকত', 'a', 'medium'),
            $mcq($subjects[0]->id, 'নুন সাকিনের পর "ر" আসলে কোন বিধান?', 'ইদগাম বিগুন্নাহ', 'ইখফা', 'ইযহার', 'ইক্বলাব', 'a', 'hard'),
        ];
        $tajWrittenQs = [
            $written($subjects[0]->id, 'ইযহার হালক্বি কাকে বলে? এর হরফগুলো উল্লেখ করুন।', 'medium'),
            $written($subjects[0]->id, 'তাজবীদের গুরুত্ব ও ফজিলত সম্পর্কে আলোচনা করুন।', 'easy'),
        ];

        // Fiqh (subj idx 3)
        $fiqhMcqQs = [
            $mcq($subjects[3]->id, 'নামাজের ফরজ কয়টি?', '১৩টি', '১৪টি', '১২টি', '১০টি', 'a', 'easy'),
            $mcq($subjects[3]->id, 'ওযুর ফরজ কয়টি?', '৪টি', '৫টি', '৬টি', '৭টি', 'a', 'easy'),
            $mcq($subjects[3]->id, 'সালাতুল জুমআ কার উপর ফরজ?', 'প্রাপ্তবয়স্ক মুসলিম পুরুষ', 'সকল মুসলিম', 'শুধু পুরুষ', 'শুধু ইমাম', 'a', 'medium'),
            $mcq($subjects[3]->id, 'যাকাত কখন ফরজ হয়?', 'নিসাব পরিমাণ সম্পদ এক বছর থাকলে', 'রমাদান মাসে', 'প্রতি মাসে', 'শুধু ব্যবসায়ীদের জন্য', 'a', 'medium'),
            $mcq($subjects[3]->id, 'তায়াম্মুমের ফরজ কয়টি?', '৩টি', '২টি', '৪টি', '৫টি', 'b', 'hard'),
        ];
        $fiqhWrittenQs = [
            $written($subjects[3]->id, 'নামাজ ভঙ্গের কারণসমূহ বিস্তারিত আলোচনা করুন।', 'medium'),
            $written($subjects[3]->id, 'ওযু ও গোসলের পার্থক্য উদাহরণসহ বর্ণনা করুন।', 'easy'),
        ];

        // Arabic (subj idx 4)
        $arabMcqQs = [
            $mcq($subjects[4]->id, 'আরবী ভাষায় ক্রিয়ার মূল রূপকে কী বলে?', 'মাসদার', 'ফেল', 'ইসম', 'হরফ', 'a', 'easy'),
            $mcq($subjects[4]->id, '"كَتَبَ" এর অর্থ কী?', 'সে লিখেছে', 'সে পড়েছে', 'সে গেছে', 'সে বলেছে', 'a', 'easy'),
            $mcq($subjects[4]->id, 'আরবী ব্যাকরণে বাক্যের কয়টি প্রকার?', '২টি', '৩টি', '৪টি', '৫টি', 'a', 'medium'),
        ];
        $arabWrittenQs = [
            $written($subjects[4]->id, 'আরবী ইসম ও ফেলের পার্থক্য উদাহরণসহ বর্ণনা করুন।', 'medium'),
        ];

        // ══════════════════════════════════════════════════════════════
        // 13. EXAMS
        // exams.type ENUM: ['MIDTERM','FINAL','RETAKE','QUIZ','PRACTICAL']
        // exams.status ENUM: ['SCHEDULED','RUNNING','COMPLETED','CANCELLED']
        // NOTE: start_datetime, negative_marking, is_anti_cheating are added by later migration
        // ══════════════════════════════════════════════════════════════

        // EXAM 1: Tajweed QUIZ — COMPLETED
        $examTajweed = Exam::create([
            'subject_id'       => $subjects[0]->id,
            'title'            => 'তাজবীদ প্রথম কুইজ পরীক্ষা',
            'type'             => 'QUIZ',
            'exam_date'        => Carbon::now()->subDays(7)->format('Y-m-d'),
            'start_datetime'   => Carbon::now()->subDays(7)->setTime(20, 0),
            'duration_minutes' => 30,
            'full_marks'       => 20,
            'pass_marks'       => 10,
            'negative_marking' => 0.25,
            'is_anti_cheating' => true,
            'status'           => 'COMPLETED',
        ]);
        foreach ($tajMcqQs as $qi => $q) {
            ExamQuestion::create(['exam_id' => $examTajweed->id, 'question_id' => $q->id, 'marks' => 4.00, 'sort_order' => $qi + 1]);
        }
        foreach ($tajWrittenQs as $qi => $q) {
            ExamQuestion::create(['exam_id' => $examTajweed->id, 'question_id' => $q->id, 'marks' => 0.00, 'sort_order' => 10 + $qi]);
        }

        // Submissions for first 4 alim students
        foreach (array_slice($alimStudents, 0, 4) as $si => $stu) {
            $correctCount = 5 - $si;
            $wrongCount   = $si;
            $negDeducted  = $wrongCount * 0.25;
            $totalScore   = max(0, $correctCount * 4 - $negDeducted);

            $submission = ExamSubmission::create([
                'exam_id'                => $examTajweed->id,
                'student_id'             => $stu->id,
                'status'                 => 'SUBMITTED',
                'started_at'             => Carbon::now()->subDays(7)->setTime(20, 0),
                'submitted_at'           => Carbon::now()->subDays(7)->setTime(20, 28),
                'correct_count'          => $correctCount,
                'wrong_count'            => $wrongCount,
                'negative_marks_deducted'=> $negDeducted,
                'total_score'            => $totalScore,
                'tab_switch_count'       => 0,
            ]);

            foreach ($tajMcqQs as $qi => $q) {
                $isCorrect = $qi < $correctCount;
                ExamAnswer::create([
                    'submission_id'      => $submission->id,
                    'question_id'        => $q->id,
                    'selected_option_id' => $isCorrect ? $q->correct_option_id : ($q->correct_option_id === 'a' ? 'b' : 'a'),
                    'is_correct'         => $isCorrect,
                    'marks_awarded'      => $isCorrect ? 4.00 : 0.00,
                ]);
            }
            foreach ($tajWrittenQs as $q) {
                ExamAnswer::create([
                    'submission_id' => $submission->id,
                    'question_id'   => $q->id,
                    'teacher_marks' => $si === 0 ? 8 : null,
                    'marks_awarded' => 0,
                    'is_correct'    => 0,  // Written — graded manually, not auto-correct
                ]);
            }

            $pct   = ($totalScore / 20) * 100;
            $grade = $pct >= 80 ? 'A+' : ($pct >= 70 ? 'A' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C' : ($pct >= 40 ? 'D' : 'F'))));
            Result::create([
                'exam_id'    => $examTajweed->id,
                'student_id' => $stu->id,
                'attempt_no' => 1,
                'marks'      => $totalScore,
                'grade'      => $grade,
                'status'     => $totalScore >= 10 ? 'PASS' : 'FAIL',
            ]);
        }

        // EXAM 2: Fiqh MIDTERM — SCHEDULED (upcoming, student দিতে পারবে)
        $examFiqh = Exam::create([
            'subject_id'       => $subjects[3]->id,
            'title'            => 'ফিকহ মিডটার্ম — ১ম সেমিস্টার',
            'type'             => 'MIDTERM',
            'exam_date'        => Carbon::now()->addDays(3)->format('Y-m-d'),
            'start_datetime'   => Carbon::now()->addDays(3)->setTime(20, 0),
            'duration_minutes' => 45,
            'full_marks'       => 35,
            'pass_marks'       => 18,
            'negative_marking' => 0.00,
            'is_anti_cheating' => false,
            'status'           => 'SCHEDULED',
        ]);
        foreach ($fiqhMcqQs as $qi => $q) {
            ExamQuestion::create(['exam_id' => $examFiqh->id, 'question_id' => $q->id, 'marks' => 5.00, 'sort_order' => $qi + 1]);
        }
        foreach ($fiqhWrittenQs as $qi => $q) {
            ExamQuestion::create(['exam_id' => $examFiqh->id, 'question_id' => $q->id, 'marks' => 5.00, 'sort_order' => 10 + $qi]);
        }

        // EXAM 3: Arabic QUIZ — ONGOING (এখনই student দিতে পারবে!)
        $examArabic = Exam::create([
            'subject_id'       => $subjects[4]->id,
            'title'            => 'আরবী ভাষা লাইভ কুইজ — এখনই দিন!',
            'type'             => 'QUIZ',
            'exam_date'        => Carbon::now()->format('Y-m-d'),
            'start_datetime'   => Carbon::now()->subMinutes(5),
            'duration_minutes' => 20,
            'full_marks'       => 15,
            'pass_marks'       => 8,
            'negative_marking' => 0.00,
            'is_anti_cheating' => false,
            'status'           => 'RUNNING',
        ]);
        foreach ($arabMcqQs as $qi => $q) {
            ExamQuestion::create(['exam_id' => $examArabic->id, 'question_id' => $q->id, 'marks' => 5.00, 'sort_order' => $qi + 1]);
        }
        foreach ($arabWrittenQs as $qi => $q) {
            ExamQuestion::create(['exam_id' => $examArabic->id, 'question_id' => $q->id, 'marks' => 0.00, 'sort_order' => 10 + $qi]);
        }

        // ══════════════════════════════════════════════════════════════
        // 14. LEARNING RESOURCES
        // learning_resources: module_id, type, title, url, sort_order
        // type ENUM: ['VIDEO','PDF','AUDIO','NOTES','SLIDES','LINK']
        // ══════════════════════════════════════════════════════════════
        $allModules = SubjectModule::all();
        foreach ([
            ['title' => 'তাজবীদ পরিচিতি — লেকচার নোট',          'type' => 'NOTES', 'url' => 'https://drive.google.com/tajweed-intro'],
            ['title' => 'ইখফার বিস্তারিত ব্যাখ্যা — ভিডিও ক্লাস', 'type' => 'VIDEO', 'url' => 'https://youtu.be/ikhfa-class'],
            ['title' => 'ফিকহ অধ্যায় ১ — PDF',                    'type' => 'PDF',   'url' => 'https://drive.google.com/fiqh-pdf'],
            ['title' => 'আরবী ব্যাকরণ — ভিডিও লেকচার',            'type' => 'VIDEO', 'url' => 'https://youtu.be/arabic-grammar'],
        ] as $ri => $rd) {
            LearningResource::create([
                'module_id'  => $allModules[$ri % $allModules->count()]->id,
                'title'      => $rd['title'],
                'type'       => $rd['type'],
                'url'        => $rd['url'],
                'sort_order' => $ri + 1,
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // 15. HOLIDAYS
        // holiday_calendars.scope ENUM: ['GLOBAL','INSTITUTE']
        // ══════════════════════════════════════════════════════════════
        foreach ([
            ['2026-03-26', 'স্বাধীনতা দিবস'],
            ['2026-04-14', 'পহেলা বৈশাখ'],
            ['2026-12-16', 'বিজয় দিবস'],
        ] as [$date, $name]) {
            HolidayCalendar::create(['date' => $date, 'name' => $name, 'scope' => 'GLOBAL', 'is_recurring_yearly' => true]);
        }

        // ══════════════════════════════════════════════════════════════
        // 16. NOTICES
        // ══════════════════════════════════════════════════════════════
        foreach ([
            ['title' => 'আলিম কোর্সের ক্লাস শুরু: ১লা জানুয়ারি, ২০২৬',  'content' => '১৫তম ব্যাচের আলিম কোর্সের ক্লাস ১লা জানুয়ারি থেকে শুরু। সকলকে স্বাগতম।',                       'priority' => 'IMPORTANT', 'audience' => 'ALL'],
            ['title' => '১৫তম ব্যাচের ভর্তি চলছে',                         'content' => 'আসন্ন ১৫তম ব্যাচে ভর্তি হতে আবেদন করুন। আসন সীমিত।',                                            'priority' => 'NORMAL',    'audience' => 'ALL'],
            ['title' => 'পরীক্ষার সময়সূচি প্রকাশিত',                       'content' => 'তাজবীদ ও ফিকহ পরীক্ষার সময়সূচি প্রকাশিত। সকল ছাত্র-ছাত্রী প্রস্তুতি নিন।',                   'priority' => 'URGENT',    'audience' => 'STUDENTS'],
            ['title' => 'শিক্ষকদের জন্য বিশেষ নির্দেশনা',                  'content' => 'সকল উস্তায/উস্তাযাকে ক্লাস সেশন আপডেট করতে ও প্রশ্নব্যাংকে প্রশ্ন যোগ করতে অনুরোধ করা হচ্ছে।', 'priority' => 'NORMAL',    'audience' => 'TEACHERS'],
        ] as $n) {
            Notice::create([
                'title'           => $n['title'],
                'content'         => $n['content'],
                'target_audience' => $n['audience'],
                'priority'        => $n['priority'],
                'created_by'      => $adminUser->id,
                'is_published'    => true,
            ]);
        }
    }
}
