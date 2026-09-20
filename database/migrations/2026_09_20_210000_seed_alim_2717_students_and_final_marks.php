<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\CourseSubjectMap;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Result;
use App\Models\ClassSession;
use App\Models\Attendance;
use App\Models\FinalMark;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Identify or ensure Course 15 (Alim Preparatory Course)
        $course = Course::find(15) ?? Course::where('name', 'like', '%Alim%')->first();
        if (!$course) {
            return;
        }

        // 2. Identify Semester 1 for this course
        $semester = Semester::where('course_id', $course->id)->where('sequence_no', 1)->first()
            ?? Semester::where('course_id', $course->id)->first();

        $semesterId = $semester?->id ?? 53;

        // 3. Find target batches (Alim 2717, Alim 2718, Alim 2819)
        $targetBatches = Batch::where('course_id', $course->id)
            ->orWhere('name', 'like', '%Alim 2717%')
            ->get();

        if ($targetBatches->isEmpty()) {
            $batch17 = Batch::create([
                'name'      => 'Alim 2717',
                'course_id' => $course->id,
                'status'    => 'ACTIVE',
            ]);
            $targetBatches = collect([$batch17]);
        }

        // Set Alim 2717 specifically to ACTIVE
        foreach ($targetBatches as $b) {
            if ($b->name === 'Alim 2717' || $b->id == 17) {
                $b->update(['status' => 'ACTIVE']);
            }
        }

        // 4. Ensure active students exist
        $students = Student::where('status', 'ACTIVE')->take(4)->get();
        if ($students->count() < 4) {
            $names = [
                ['name' => 'নোমান আহমদ', 'code' => '26-26-15-1-0001', 'gender' => 'MALE'],
                ['name' => 'মুহাম্মদ আব্দুল্লাহ', 'code' => '26-26-15-1-0002', 'gender' => 'MALE'],
                ['name' => 'মোঃ বনী আমিন খান', 'code' => '26-26-15-1-0004', 'gender' => 'MALE'],
                ['name' => 'জাহিদুল ইসলাম', 'code' => '26-26-15-1-0005', 'gender' => 'MALE'],
            ];
            foreach ($names as $idx => $item) {
                $user = User::firstOrCreate(
                    ['email' => 'alim.student' . ($idx + 1) . '@iom.edu.bd'],
                    [
                        'name'     => $item['name'],
                        'password' => bcrypt('password123'),
                        'role'     => 'student',
                    ]
                );
                Student::firstOrCreate(
                    ['student_code' => $item['code']],
                    [
                        'user_id' => $user->id,
                        'name'    => $item['name'],
                        'gender'  => $item['gender'],
                        'status'  => 'ACTIVE',
                    ]
                );
            }
            $students = Student::where('status', 'ACTIVE')->take(4)->get();
        }

        // 5. Enroll students into Alim 2717 (and target batches)
        $alim2717 = $targetBatches->firstWhere('name', 'Alim 2717') 
            ?? $targetBatches->firstWhere('id', 17) 
            ?? $targetBatches->first();

        $enrolledStudents = [];
        foreach ($students as $student) {
            $enr = Enrollment::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'batch_id'   => $alim2717->id,
                ],
                [
                    'course_id'   => $course->id,
                    'semester_id' => $semesterId,
                    'status'      => 'ACTIVE',
                    'enrolled_at' => now(),
                ]
            );
            if ($enr->status !== 'ACTIVE' || $enr->semester_id != $semesterId) {
                $enr->update(['status' => 'ACTIVE', 'semester_id' => $semesterId]);
            }
            $enrolledStudents[] = ['student' => $student, 'enrollment' => $enr];
        }

        // 6. Identify Subject 36 (Adabu Talibul Ilm - ATI 101) & Semester 1 Subjects
        $atiSubject = Subject::find(36)
            ?? Subject::where('code', 'ATI 101')->first()
            ?? Subject::where('name', 'like', '%Adabu Talibul Ilm%')->first();

        if (!$atiSubject) {
            $atiSubject = Subject::create([
                'name'      => 'Adabu Talibul Ilm',
                'code'      => 'ATI 101',
                'is_active' => true,
            ]);
        }

        // Ensure mapped to course & semester
        CourseSubjectMap::firstOrCreate(
            [
                'course_id'  => $course->id,
                'subject_id' => $atiSubject->id,
            ],
            [
                'semester_id' => $semesterId,
                'sort_order'  => 0,
                'group_mode'  => 'INHERIT',
            ]
        );

        // Also get other Semester 1 subjects
        $sem1SubjectIds = CourseSubjectMap::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->pluck('subject_id')
            ->toArray();

        if (empty($sem1SubjectIds)) {
            $sem1SubjectIds = [$atiSubject->id];
        }

        // 7. Create Sample Exams for ATI 101 so Generate has data
        $quiz = Exam::firstOrCreate(
            ['subject_id' => $atiSubject->id, 'type' => 'QUIZ'],
            [
                'title'          => 'ATI 101 - ক্লাস টেস্ট ১',
                'semester_id'    => $semesterId,
                'full_marks'     => 30,
                'pass_marks'     => 12,
                'duration_minutes' => 30,
                'exam_date'        => now()->subDays(20)->format('Y-m-d'),
                'status'           => 'COMPLETED',
                'start_datetime'   => now()->subDays(20),
                'end_datetime'     => now()->subDays(19),
            ]
        );

        $midterm = Exam::firstOrCreate(
            ['subject_id' => $atiSubject->id, 'type' => 'MIDTERM'],
            [
                'title'            => 'ATI 101 - মিডটার্ম পরীক্ষা',
                'semester_id'      => $semesterId,
                'full_marks'       => 50,
                'pass_marks'       => 20,
                'duration_minutes' => 60,
                'exam_date'        => now()->subDays(15)->format('Y-m-d'),
                'status'           => 'COMPLETED',
                'start_datetime'   => now()->subDays(15),
                'end_datetime'     => now()->subDays(14),
            ]
        );

        $finalExam = Exam::firstOrCreate(
            ['subject_id' => $atiSubject->id, 'type' => 'FINAL'],
            [
                'title'            => 'ATI 101 - ফাইনাল পরীক্ষা',
                'semester_id'      => $semesterId,
                'full_marks'       => 100,
                'pass_marks'       => 40,
                'duration_minutes' => 120,
                'exam_date'        => now()->subDays(5)->format('Y-m-d'),
                'status'           => 'COMPLETED',
                'start_datetime'   => now()->subDays(5),
                'end_datetime'     => now()->subDays(4),
            ]
        );

        // 8. Create sample Class Sessions for attendance
        $teacher = \App\Models\Teacher::first();
        for ($s = 1; $s <= 5; $s++) {
            $cs = ClassSession::firstOrCreate(
                [
                    'batch_id'     => $alim2717->id,
                    'subject_id'   => $atiSubject->id,
                    'session_date' => now()->subDays(20 - $s * 3)->format('Y-m-d'),
                ],
                [
                    'teacher_id'      => $teacher?->id,
                    'start_time'      => '10:00:00',
                    'status'          => 'COMPLETED',
                    'class_conducted' => true,
                ]
            );

            // Mark attendance for enrolled students
            foreach ($enrolledStudents as $idx => $pair) {
                Attendance::firstOrCreate(
                    [
                        'class_session_id' => $cs->id,
                        'student_id'       => $pair['student']->id,
                    ],
                    [
                        'enrollment_id' => $pair['enrollment']->id,
                        'status'        => ($idx === 3 && $s === 1) ? 'ABSENT' : 'PRESENT',
                    ]
                );
            }
        }

        // 9. Presets for Final Marks
        $presets = [
            [
                'ct_ob' => 26.00, 'ct_cv' => 17.33,
                'mid_ob'=> 44.00, 'mid_cv'=> 26.40,
                'fn_ob' => 88.00, 'fn_cv' => 35.20,
                'att_pct' => 100.00, 'att_cv' => 10.00,
                'total' => 88.93, 'grade' => 'A+', 'gpa' => 5.00, 'status' => 'PASS',
            ],
            [
                'ct_ob' => 24.00, 'ct_cv' => 16.00,
                'mid_ob'=> 40.00, 'mid_cv'=> 24.00,
                'fn_ob' => 80.00, 'fn_cv' => 32.00,
                'att_pct' => 90.00, 'att_cv' => 9.00,
                'total' => 81.00, 'grade' => 'A+', 'gpa' => 5.00, 'status' => 'PASS',
            ],
            [
                'ct_ob' => 21.00, 'ct_cv' => 14.00,
                'mid_ob'=> 36.00, 'mid_cv'=> 21.60,
                'fn_ob' => 72.00, 'fn_cv' => 28.80,
                'att_pct' => 85.00, 'att_cv' => 8.50,
                'total' => 72.90, 'grade' => 'A', 'gpa' => 4.00, 'status' => 'PASS',
            ],
            [
                'ct_ob' => 18.00, 'ct_cv' => 12.00,
                'mid_ob'=> 30.00, 'mid_cv'=> 18.00,
                'fn_ob' => 60.00, 'fn_cv' => 24.00,
                'att_pct' => 80.00, 'att_cv' => 8.00,
                'total' => 62.00, 'grade' => 'A-', 'gpa' => 3.50, 'status' => 'PASS',
            ],
        ];

        $adminId = User::whereIn('role', ['admin', 'super_admin'])->value('id') ?? 1;

        // Seed Final Marks across all Semester 1 subjects for Alim 2717
        foreach ($sem1SubjectIds as $subId) {
            foreach ($enrolledStudents as $idx => $pair) {
                $p = $presets[$idx % count($presets)];

                // Also attach Results for QUIZ, MIDTERM, FINAL for ATI 101
                if ($subId == $atiSubject->id) {
                    Result::updateOrCreate(
                        ['exam_id' => $quiz->id, 'student_id' => $pair['student']->id],
                        ['marks' => $p['ct_ob'], 'status' => 'PASS']
                    );
                    Result::updateOrCreate(
                        ['exam_id' => $midterm->id, 'student_id' => $pair['student']->id],
                        ['marks' => $p['mid_ob'], 'status' => 'PASS']
                    );
                    Result::updateOrCreate(
                        ['exam_id' => $finalExam->id, 'student_id' => $pair['student']->id],
                        ['marks' => $p['fn_ob'], 'status' => 'PASS']
                    );
                }

                FinalMark::updateOrCreate(
                    [
                        'student_id' => $pair['student']->id,
                        'subject_id' => $subId,
                        'batch_id'   => $alim2717->id,
                    ],
                    [
                        'enrollment_id'        => $pair['enrollment']->id,
                        'semester_id'          => $semesterId,
                        'class_test_obtained'  => $p['ct_ob'],
                        'class_test_converted' => $p['ct_cv'],
                        'midterm_obtained'     => $p['mid_ob'],
                        'midterm_converted'    => $p['mid_cv'],
                        'final_obtained'       => $p['fn_ob'],
                        'final_converted'      => $p['fn_cv'],
                        'attendance_percent'   => $p['att_pct'],
                        'attendance_converted' => $p['att_cv'],
                        'total_mark'           => $p['total'],
                        'grade'                => $p['grade'],
                        'gpa'                  => $p['gpa'],
                        'status'               => $p['status'],
                        'generated_by'         => $adminId,
                        'generated_at'         => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Safe reversible cleanup
        $batch = Batch::where('name', 'Alim 2717')->first();
        if ($batch) {
            FinalMark::where('batch_id', $batch->id)->delete();
        }
    }
};
