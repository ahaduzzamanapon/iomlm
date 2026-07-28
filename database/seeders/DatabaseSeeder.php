<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AcademicYear;
use App\Models\AdmissionForm;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\BatchSemesterPosition;
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
        // 1. SYSTEM USERS & IOM FACULTY / STUDENTS
        // ═════════════════════════════════════════════════════════════════
        $adminUser = User::create([
            'name'     => 'IOM Central Admin',
            'email'    => 'admin@learningplus.com',
            'password' => $password,
            'role'     => 'admin',
        ]);

        // Teachers (IOM Islamic Scholars & Professors)
        $turing = User::create([
            'name'     => 'Dr. Shaikh Ahmadullah',
            'email'    => 'teacher@learningplus.com',
            'password' => $password,
            'role'     => 'teacher',
        ]);

        $lovelace = User::create([
            'name'     => 'Dr. Manzur-e-Elahi',
            'email'    => 'ada@learningplus.com',
            'password' => $password,
            'role'     => 'teacher',
        ]);

        $shannon = User::create([
            'name'     => 'Prof. Abu Bakr Muhammad',
            'email'    => 'shannon@learningplus.com',
            'password' => $password,
            'role'     => 'teacher',
        ]);

        // Students (IOM Enrolled Students)
        $student1 = User::create([
            'name'     => 'Abdullah Al Mamun',
            'email'    => 'student@learningplus.com',
            'password' => $password,
            'role'     => 'student',
        ]);

        $student2 = User::create([
            'name'     => 'Ayesha Siddiqua',
            'email'    => 'sarah@learningplus.com',
            'password' => $password,
            'role'     => 'student',
        ]);

        $student3 = User::create([
            'name'     => 'Tanvir Hossain',
            'email'    => 'tanvir@gmail.com',
            'password' => $password,
            'role'     => 'student',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 2. ACADEMIC SETUP (IOM CALENDAR & SESSIONS)
        // ═════════════════════════════════════════════════════════════════
        $year2026 = AcademicYear::create([
            'name'       => 'IOM Academic Year 2026',
            'start_date' => '2026-01-01',
            'end_date'   => '2026-12-31',
            'is_active'  => true,
        ]);

        AcademicSession::create([
            'academic_year_id' => $year2026->id,
            'name'             => 'Spring Semester 2026',
            'is_active'        => true,
        ]);

        AcademicSession::create([
            'academic_year_id' => $year2026->id,
            'name'             => 'Autumn Semester 2026',
            'is_active'        => false,
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 3. IOM SUBJECTS & MODULES (Module is King!)
        // ═════════════════════════════════════════════════════════════════

        // Subject 1: Islamic Aqeedah & Theology
        $aqeedah = Subject::create([
            'name'       => 'Islamic Aqeedah & Theology (আকীদাহ ও বিশ্বাসগত মূলনীতি)',
            'code'       => 'AQD-101',
            'credit'     => 3,
            'full_marks' => 100,
            'pass_marks' => 40,
            'version'    => 1,
            'is_active'  => true,
        ]);

        $aqMod1 = SubjectModule::create([
            'subject_id'               => $aqeedah->id,
            'sequence_no'              => 1,
            'title'                    => 'Introduction to Tawheed (তাওহীদের পরিচয় ও প্রকারভেদ)',
            'description'              => 'Tawheed ar-Rububiyyah, Uluhiyyah, and Asma was-Sifat',
            'is_locked_until_previous' => false,
            'is_active'                => true,
        ]);

        $aqMod2 = SubjectModule::create([
            'subject_id'               => $aqeedah->id,
            'sequence_no'              => 2,
            'title'                    => 'Nullifiers of Islam (ঈমান ভঙ্গের কারণসমূহ)',
            'description'              => 'Shirk, Kufr, Nifaq, and fundamental nullifiers of faith',
            'is_locked_until_previous' => true,
            'is_active'                => true,
        ]);

        $aqMod3 = SubjectModule::create([
            'subject_id'               => $aqeedah->id,
            'sequence_no'              => 3,
            'title'                    => 'Belief in Destiny & Angels (তাকদীর ও ফেরেশতাগণের উপর ঈমান)',
            'description'              => 'Pillars of Iman and divine decree',
            'is_locked_until_previous' => true,
            'is_active'                => true,
        ]);

        // Subject 2: Quranic Exegesis & Tafseer
        $tafseer = Subject::create([
            'name'       => 'Tafseer & Quranic Sciences (তাফসীর ও উলূমুল কুরআন)',
            'code'       => 'TAF-102',
            'credit'     => 4,
            'full_marks' => 100,
            'pass_marks' => 40,
            'version'    => 1,
            'is_active'  => true,
        ]);

        $tafMod1 = SubjectModule::create([
            'subject_id'               => $tafseer->id,
            'sequence_no'              => 1,
            'title'                    => 'Principles of Tafseer (তাফসীরের মূলনীতি ও ইতিহাস)',
            'description'              => 'Usool at-Tafseer and revelation historical context',
            'is_locked_until_previous' => false,
            'is_active'                => true,
        ]);

        $tafMod2 = SubjectModule::create([
            'subject_id'               => $tafseer->id,
            'sequence_no'              => 2,
            'title'                    => 'Tafseer of Surah Al-Fatiha & Juz Amma (সূরা ফাতিহা ও জুজ আম্মার তাফসীর)',
            'description'              => 'Detailed word-by-word explanation and lessons',
            'is_locked_until_previous' => true,
            'is_active'                => true,
        ]);

        // Subject 3: Fiqh of Worship & Daily Life
        $fiqh = Subject::create([
            'name'       => 'Fiqh of Worship (ফিকহুস সুন্নাহ ও আল-ইবাদাত)',
            'code'       => 'FQH-201',
            'credit'     => 3,
            'full_marks' => 100,
            'pass_marks' => 40,
            'version'    => 1,
            'is_active'  => true,
        ]);

        $fqMod1 = SubjectModule::create([
            'subject_id'               => $fiqh->id,
            'sequence_no'              => 1,
            'title'                    => 'Purification & Taharah (পবিত্রতা ও তাহায়্যুরের মাসায়েল)',
            'description'              => 'Wudu, Ghusl, Tayammum, and rulings of cleanliness',
            'is_locked_until_previous' => false,
            'is_active'                => true,
        ]);

        $fqMod2 = SubjectModule::create([
            'subject_id'               => $fiqh->id,
            'sequence_no'              => 2,
            'title'                    => 'Rulings of Salah & Congregation (সালাত ও জামায়াতের বিধান)',
            'description'              => 'Conditions, pillars, and Sunnah practices of prayer',
            'is_locked_until_previous' => true,
            'is_active'                => true,
        ]);

        // Subject 4: Quranic Arabic Language
        $arabic = Subject::create([
            'name'       => 'Quranic Arabic Language (কুরআনিক আরবী ভাষা ও ব্যাকরণ)',
            'code'       => 'ARB-101',
            'credit'     => 3,
            'full_marks' => 100,
            'pass_marks' => 40,
            'version'    => 1,
            'is_active'  => true,
        ]);

        $arbMod1 = SubjectModule::create([
            'subject_id'               => $arabic->id,
            'sequence_no'              => 1,
            'title'                    => 'Arabic Grammar & Nouns (নাহু ও এ’রাবের প্রাথমিক জ্ঞান)',
            'description'              => 'Parts of speech, Ism, Fi\'l, Harf',
            'is_locked_until_previous' => false,
            'is_active'                => true,
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 4. IOM COURSES & SEMESTERS
        // ═════════════════════════════════════════════════════════════════
        $baIslamicStudies = Course::create([
            'name'           => 'B.A. in Islamic Studies (ইসলামিক স্টাডিজে বি.এ. ডিগ্রি)',
            'type'           => 'SEMESTER_BASED',
            'duration_value' => 4,
            'duration_unit'  => 'YEAR',
            'is_active'      => true,
        ]);

        $diplomaIslamic = Course::create([
            'name'           => 'Higher Diploma in Islamic Sciences (ইসলামিক সায়েন্সে ডিপ্লোমা)',
            'type'           => 'SUBJECT_BASED',
            'duration_value' => 1,
            'duration_unit'  => 'YEAR',
            'is_active'      => true,
        ]);

        $shortQuranCourse = Course::create([
            'name'           => 'Quranic Arabic & Tajweed Certificate (কুরআনিক এ্যারাবিক সার্টিফিকেট)',
            'type'           => 'SUBJECT_BASED',
            'duration_value' => 3,
            'duration_unit'  => 'MONTH',
            'is_active'      => true,
        ]);

        // Semesters for B.A. Course
        $sem1 = Semester::create(['course_id' => $baIslamicStudies->id, 'sequence_no' => 1, 'name' => '1st Semester (প্রথম সেমিস্টার)']);
        $sem2 = Semester::create(['course_id' => $baIslamicStudies->id, 'sequence_no' => 2, 'name' => '2nd Semester (দ্বিতীয় সেমিস্টার)']);
        $sem3 = Semester::create(['course_id' => $baIslamicStudies->id, 'sequence_no' => 3, 'name' => '3rd Semester (তৃতীয় সেমিস্টার)']);
        $sem4 = Semester::create(['course_id' => $baIslamicStudies->id, 'sequence_no' => 4, 'name' => '4th Semester (চতুর্থ সেমিস্টার)']);

        // Course-Subject Maps
        CourseSubjectMap::create(['course_id' => $baIslamicStudies->id, 'subject_id' => $aqeedah->id, 'semester_id' => $sem1->id, 'sort_order' => 1]);
        CourseSubjectMap::create(['course_id' => $baIslamicStudies->id, 'subject_id' => $tafseer->id, 'semester_id' => $sem1->id, 'sort_order' => 2]);
        CourseSubjectMap::create(['course_id' => $baIslamicStudies->id, 'subject_id' => $fiqh->id,    'semester_id' => $sem2->id, 'sort_order' => 3]);
        CourseSubjectMap::create(['course_id' => $baIslamicStudies->id, 'subject_id' => $arabic->id,  'semester_id' => $sem2->id, 'sort_order' => 4]);

        // Diploma Course Direct Subject Maps (semester_id = NULL)
        CourseSubjectMap::create(['course_id' => $diplomaIslamic->id, 'subject_id' => $aqeedah->id, 'semester_id' => null, 'sort_order' => 1]);
        CourseSubjectMap::create(['course_id' => $diplomaIslamic->id, 'subject_id' => $fiqh->id,    'semester_id' => null, 'sort_order' => 2]);

        // Short Course Map
        CourseSubjectMap::create(['course_id' => $shortQuranCourse->id, 'subject_id' => $arabic->id, 'semester_id' => null, 'sort_order' => 1]);

        // Holidays
        HolidayCalendar::create(['date' => '2026-03-20', 'name' => 'Eid-ul-Fitr Vacation',     'scope' => 'GLOBAL', 'is_recurring_yearly' => false]);
        HolidayCalendar::create(['date' => '2026-05-27', 'name' => 'Eid-ul-Adha Vacation',    'scope' => 'GLOBAL', 'is_recurring_yearly' => false]);
        HolidayCalendar::create(['date' => '2026-02-21', 'name' => 'International Mother Language Day', 'scope' => 'GLOBAL', 'is_recurring_yearly' => true]);

        // ═════════════════════════════════════════════════════════════════
        // 5. FACULTY MEMBERS & ASSIGNMENTS
        // ═════════════════════════════════════════════════════════════════
        $turingTeacher = Teacher::create([
            'employee_id'   => 'EMP-IOM-101',
            'name'          => 'Dr. Shaikh Ahmadullah',
            'email'         => 'teacher@learningplus.com',
            'phone'         => '+8801711112233',
            'designation'   => 'Chief Islamic Scholar & Head of Aqeedah',
            'qualification' => 'Ph.D. in Islamic Theology',
            'joining_date'  => '2020-01-10',
            'blood_group'   => 'B+',
            'is_active'     => true,
        ]);

        $lovelaceTeacher = Teacher::create([
            'employee_id'   => 'EMP-IOM-102',
            'name'          => 'Dr. Manzur-e-Elahi',
            'email'         => 'ada@learningplus.com',
            'phone'         => '+8801722334455',
            'designation'   => 'Senior Professor of Fiqh & Usool',
            'qualification' => 'Ph.D. in Islamic Jurisprudence, Madinah University',
            'joining_date'  => '2021-03-15',
            'blood_group'   => 'O+',
            'is_active'     => true,
        ]);

        $shannonTeacher = Teacher::create([
            'employee_id'   => 'EMP-IOM-103',
            'name'          => 'Prof. Abu Bakr Muhammad',
            'email'         => 'shannon@learningplus.com',
            'phone'         => '+8801733445566',
            'designation'   => 'Assistant Professor of Arabic & Tafseer',
            'qualification' => 'M.A. in Arabic Literature & Tafseer',
            'joining_date'  => '2022-06-01',
            'blood_group'   => 'A+',
            'is_active'     => true,
        ]);

        // Subject Teacher Assignments
        SubjectTeacherAssignment::create(['subject_id' => $aqeedah->id, 'teacher_id' => $turingTeacher->id,   'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $tafseer->id, 'teacher_id' => $shannonTeacher->id,  'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $fiqh->id,    'teacher_id' => $lovelaceTeacher->id, 'batch_id' => null]);
        SubjectTeacherAssignment::create(['subject_id' => $arabic->id,  'teacher_id' => $shannonTeacher->id,  'batch_id' => null]);

        // ═════════════════════════════════════════════════════════════════
        // 6. BATCHES & BATCH POSITIONS
        // ═════════════════════════════════════════════════════════════════
        $baBatch2026 = Batch::create([
            'course_id'                => $baIslamicStudies->id,
            'academic_year_id'         => $year2026->id,
            'name'                     => 'B.A. Islamic Studies 2026 Batch A',
            'batch_code'               => 'BA-ISL-2026-A',
            'start_date'               => '2026-01-15',
            'expected_end_date'        => '2029-12-31',
            'status'                   => 'ACTIVE',
            'subject_version_snapshot' => 1,
        ]);

        $diplomaBatch01 = Batch::create([
            'course_id'                => $diplomaIslamic->id,
            'academic_year_id'         => $year2026->id,
            'name'                     => 'Diploma in Islamic Sciences Jan 2026',
            'batch_code'               => 'DIP-ISL-2026-01',
            'start_date'               => '2026-01-20',
            'expected_end_date'        => '2027-01-20',
            'status'                   => 'ACTIVE',
            'subject_version_snapshot' => 1,
        ]);

        BatchSemesterPosition::create([
            'batch_id'            => $baBatch2026->id,
            'current_semester_id' => $sem1->id,
            'started_at'          => '2026-01-15',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 7. STUDENTS & ADMISSION FORMS
        // ═════════════════════════════════════════════════════════════════
        $abdullah = Student::create([
            'student_code'   => 'STD-IOM-2026-001',
            'name'           => 'Abdullah Al Mamun',
            'email'          => 'student@learningplus.com',
            'phone'          => '+8801811998877',
            'date_of_birth'  => '2002-04-12',
            'gender'         => 'Male',
            'blood_group'    => 'B+',
            'national_id'    => '19982691234567890',
            'address'        => 'Mirpur-10, Dhaka, Bangladesh',
            'father_name'    => 'Abdur Rahman',
            'mother_name'    => 'Fatema Begum',
            'guardian_name'  => 'Abdur Rahman',
            'guardian_phone' => '+8801811998876',
            'ssc_gpa'        => 5.00,
            'hsc_gpa'        => 4.90,
            'status'         => 'ACTIVE',
        ]);

        $ayesha = Student::create([
            'student_code'   => 'STD-IOM-2026-002',
            'name'           => 'Ayesha Siddiqua',
            'email'          => 'sarah@learningplus.com',
            'phone'          => '+8801822887766',
            'date_of_birth'  => '2003-09-25',
            'gender'         => 'Female',
            'blood_group'    => 'O+',
            'national_id'    => '20012698765432109',
            'address'        => 'Halishahar, Chittagong, Bangladesh',
            'father_name'    => 'Maulana Ibrahim',
            'mother_name'    => 'Mariam Khatun',
            'guardian_name'  => 'Maulana Ibrahim',
            'guardian_phone' => '+8801822887765',
            'ssc_gpa'        => 5.00,
            'hsc_gpa'        => 5.00,
            'status'         => 'ACTIVE',
        ]);

        $tanvir = Student::create([
            'student_code'   => null,
            'name'           => 'Tanvir Hossain',
            'email'          => 'tanvir@gmail.com',
            'phone'          => '+8801833776655',
            'date_of_birth'  => '2004-01-10',
            'gender'         => 'Male',
            'blood_group'    => 'A+',
            'national_id'    => '20041234567890123',
            'address'        => 'Zindabazar, Sylhet, Bangladesh',
            'father_name'    => 'Kawsar Ahmed',
            'mother_name'    => 'Nazma Parvin',
            'guardian_name'  => 'Kawsar Ahmed',
            'guardian_phone' => '+8801833776654',
            'ssc_gpa'        => 4.80,
            'hsc_gpa'        => 4.75,
            'status'         => 'PENDING',
        ]);

        // Admission Applications
        AdmissionForm::create([
            'student_id'           => $abdullah->id,
            'interested_course_id' => $baIslamicStudies->id,
            'attempt_no'           => 1,
            'lead_source'          => 'Website',
            'discount_percent'     => 10,
            'status'               => 'APPROVED',
            'reviewed_by'          => $adminUser->id,
            'reviewed_at'          => now()->subDays(12),
            'notes'                => 'Verified HSC Alim certificate and Hafiz Quran credential',
        ]);

        AdmissionForm::create([
            'student_id'           => $ayesha->id,
            'interested_course_id' => $diplomaIslamic->id,
            'attempt_no'           => 1,
            'lead_source'          => 'Social Media',
            'discount_percent'     => 0,
            'status'               => 'APPROVED',
            'reviewed_by'          => $adminUser->id,
            'reviewed_at'          => me_date('-10 days'),
            'notes'                => 'Approved for Higher Diploma in Islamic Sciences',
        ]);

        AdmissionForm::create([
            'student_id'           => $tanvir->id,
            'interested_course_id' => $baIslamicStudies->id,
            'attempt_no'           => 1,
            'lead_source'          => 'Referral',
            'discount_percent'     => 0,
            'status'               => 'PENDING',
            'notes'                => 'Awaiting verification of NID copy',
        ]);

        // Enrollments
        $enr1 = Enrollment::create([
            'student_id'  => $abdullah->id,
            'batch_id'    => $baBatch2026->id,
            'course_id'   => $baIslamicStudies->id,
            'semester_id' => $sem1->id,
            'enrolled_at' => '2026-01-15',
            'status'      => 'ACTIVE',
        ]);

        $enr2 = Enrollment::create([
            'student_id'  => $ayesha->id,
            'batch_id'    => $diplomaBatch01->id,
            'course_id'   => $diplomaIslamic->id,
            'semester_id' => null,
            'enrolled_at' => '2026-01-20',
            'status'      => 'ACTIVE',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 8. MODULE TIMELINES & LIVE CLASS SESSIONS
        // ═════════════════════════════════════════════════════════════════
        $tl1 = Timeline::firstOrCreate([
            'batch_id'   => $baBatch2026->id,
            'subject_id' => $aqeedah->id,
            'module_id'  => $aqMod1->id,
        ], [
            'scheduled_date' => '2026-01-22',
            'status'         => 'COMPLETED',
        ]);
        $tl1->update(['status' => 'COMPLETED', 'scheduled_date' => '2026-01-22']);

        $tl2 = Timeline::firstOrCreate([
            'batch_id'   => $baBatch2026->id,
            'subject_id' => $aqeedah->id,
            'module_id'  => $aqMod2->id,
        ], [
            'scheduled_date' => '2026-01-29',
            'status'         => 'SCHEDULED',
        ]);

        $tl3 = Timeline::firstOrCreate([
            'batch_id'   => $baBatch2026->id,
            'subject_id' => $tafseer->id,
            'module_id'  => $tafMod1->id,
        ], [
            'scheduled_date' => '2026-02-05',
            'status'         => 'UPCOMING',
        ]);

        // Class Session 1 — Completed Class
        $cs1 = ClassSession::create([
            'timeline_id'     => $tl1->id,
            'teacher_id'      => $turingTeacher->id,
            'meeting_link'    => 'https://meet.google.com/iom-aqd1-live',
            'teacher_present' => true,
            'class_conducted' => true,
            'started_at'      => '2026-01-22 20:00:00',
            'ended_at'        => '2026-01-22 21:30:00',
            'status'          => 'COMPLETED',
            'notes'           => 'Discussed Tawheed ar-Rububiyyah & Uluhiyyah with references from Surah Al-Ikhlas.',
        ]);

        // Class Session 2 — Scheduled for Today
        $cs2 = ClassSession::create([
            'timeline_id'     => $tl2->id,
            'teacher_id'      => $turingTeacher->id,
            'meeting_link'    => 'https://meet.google.com/iom-aqd2-live',
            'teacher_present' => null,
            'class_conducted' => null,
            'status'          => 'SCHEDULED',
        ]);

        // Attendance
        Attendance::create([
            'class_session_id' => $cs1->id,
            'student_id'       => $abdullah->id,
            'enrollment_id'    => $enr1->id,
            'status'           => 'PRESENT',
            'notes'            => 'Joined online live session on time',
        ]);

        // Learning Resources
        LearningResource::create([
            'module_id'  => $aqMod1->id,
            'type'       => 'VIDEO',
            'title'      => 'লেকচার ১: তাওহীদের পরিচয় ও গুরুত্ব (Dr. Shaikh Ahmadullah)',
            'url'        => 'https://youtube.com/watch?v=iom_aqeedah_1',
            'sort_order' => 1,
        ]);

        LearningResource::create([
            'module_id'  => $aqMod1->id,
            'type'       => 'PDF',
            'title'      => 'আকীদাহ মডিউল ১ হ্যান্ডআউট ও নোট (PDF)',
            'url'        => 'https://iom.edu.bd/resources/aqeedah_mod1.pdf',
            'sort_order' => 2,
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 9. EXAMS & INITIAL RESULTS
        // ═════════════════════════════════════════════════════════════════
        $midtermExam = Exam::create([
            'subject_id'       => $aqeedah->id,
            'semester_id'      => $sem1->id,
            'title'            => 'ইসলামিক আকীদাহ মিডটার্ম পরীক্ষা ২০২৬',
            'type'             => 'MIDTERM',
            'exam_date'        => '2026-02-15',
            'start_time'       => '19:30:00',
            'duration_minutes' => 90,
            'full_marks'       => 50,
            'pass_marks'       => 20,
            'status'           => 'SCHEDULED',
        ]);

        ExamAttendee::create([
            'exam_id'       => $midtermExam->id,
            'student_id'    => $abdullah->id,
            'batch_id'      => $baBatch2026->id,
            'enrollment_id' => $enr1->id,
            'is_eligible'   => true,
            'admit_card_no' => 'ADM-IOM-2026-101',
        ]);

        // ═════════════════════════════════════════════════════════════════
        // 10. SYSTEM SETTINGS (CONFIGURABLE BUSINESS RULES)
        // ═════════════════════════════════════════════════════════════════
        Setting::create(['key' => 'institute_name',         'value' => 'Islamic Online Media (IOM)',       'type' => 'string', 'group' => 'general', 'label' => 'Institute Name']);
        Setting::create(['key' => 'min_attendance_required', 'value' => '0',                                'type' => 'bool',   'group' => 'academic', 'label' => 'Require Minimum Attendance for Exam']);
        Setting::create(['key' => 'min_attendance_percent',  'value' => '75',                               'type' => 'int',    'group' => 'academic', 'label' => 'Minimum Attendance %']);
        Setting::create(['key' => 'final_result_policy',     'value' => 'BEST_ATTEMPT',                     'type' => 'string', 'group' => 'academic', 'label' => 'Multi-attempt Result Policy']);
        Setting::create(['key' => 'due_enforcement_level',   'value' => 'NONE',                             'type' => 'string', 'group' => 'accounts', 'label' => 'Fee Due Enforcement Level']);
    }
}

function me_date($str) {
    return date('Y-m-d H:i:s', strtotime($str));
}
