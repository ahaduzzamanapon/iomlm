<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Batch;
use App\Models\Course;
use App\Models\CourseSubjectMap;
use App\Models\Enrollment;
use App\Models\FinalMark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations to seed sample student and final mark data.
     */
    public function up(): void
    {
        // 1. Get or create student(s)
        $students = Student::take(3)->get();

        if ($students->isEmpty()) {
            $user = User::firstOrCreate(
                ['email' => 'student.demo@iom.edu.bd'],
                [
                    'name'     => 'মুহাম্মদ আব্দুল্লাহ',
                    'password' => bcrypt('password123'),
                    'role'     => 'student',
                ]
            );

            $newStudent = Student::create([
                'user_id'      => $user->id,
                'student_code' => 'STD-2026-001',
                'name'         => 'মুহাম্মদ আব্দুল্লাহ',
                'gender'       => 'MALE',
                'status'       => 'ACTIVE',
            ]);
            $students = collect([$newStudent]);
        }

        // 2. Identify target subject (Subject 4 or ARG 301 or first subject)
        $subject = Subject::find(4) 
            ?? Subject::where('code', 'ARG 301')->first() 
            ?? Subject::where('name', 'like', '%Arabic Grammer%')->first()
            ?? Subject::first();

        if (!$subject) {
            $subject = Subject::create([
                'name'      => 'Arabic Grammer - 2',
                'code'      => 'ARG 301',
                'is_active' => true,
            ]);
        }

        // 3. Identify target batches
        // Look for Batch ID 6, Batch 'RNC 2601', and active batches
        $targetBatches = Batch::where('id', 6)
            ->orWhere('name', 'like', '%RNC 2601%')
            ->get();

        if ($targetBatches->isEmpty()) {
            $firstBatch = Batch::first();
            $targetBatches = $firstBatch ? collect([$firstBatch]) : collect();
        }

        $adminId = User::whereIn('role', ['admin', 'super_admin'])->value('id') ?? 1;

        // Sample mark presets for multiple students
        $markPresets = [
            [
                'ct_ob' => 25.00, 'ct_cv' => 16.67,
                'mid_ob'=> 42.00, 'mid_cv'=> 25.20,
                'fn_ob' => 85.00, 'fn_cv' => 34.00,
                'att_pct' => 90.00, 'att_cv' => 9.00,
                'total' => 84.87, 'grade' => 'A+', 'gpa' => 5.00, 'status' => 'PASS',
            ],
            [
                'ct_ob' => 22.00, 'ct_cv' => 14.67,
                'mid_ob'=> 38.00, 'mid_cv'=> 22.80,
                'fn_ob' => 76.00, 'fn_cv' => 30.40,
                'att_pct' => 85.00, 'att_cv' => 8.50,
                'total' => 76.37, 'grade' => 'A', 'gpa' => 4.00, 'status' => 'PASS',
            ],
            [
                'ct_ob' => 18.00, 'ct_cv' => 12.00,
                'mid_ob'=> 30.00, 'mid_cv'=> 18.00,
                'fn_ob' => 60.00, 'fn_cv' => 24.00,
                'att_pct' => 70.00, 'att_cv' => 7.00,
                'total' => 61.00, 'grade' => 'A-', 'gpa' => 3.50, 'status' => 'PASS',
            ],
        ];

        foreach ($targetBatches as $batch) {
            // Ensure the course of this batch has the subject mapped
            if ($batch->course_id && $subject) {
                CourseSubjectMap::firstOrCreate([
                    'course_id'  => $batch->course_id,
                    'subject_id' => $subject->id,
                ], [
                    'semester_id' => null,
                    'sort_order'  => 1,
                ]);
            }

            // Ensure enrollment and final marks for each student
            foreach ($students as $idx => $student) {
                $enrollment = Enrollment::firstOrCreate([
                    'student_id' => $student->id,
                    'batch_id'   => $batch->id,
                ], [
                    'course_id'   => $batch->course_id,
                    'status'      => 'ACTIVE',
                    'enrolled_at' => now()->toDateString(),
                ]);

                if ($enrollment->status !== 'ACTIVE') {
                    $enrollment->update(['status' => 'ACTIVE']);
                }

                $preset = $markPresets[$idx % count($markPresets)];

                FinalMark::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'batch_id'   => $batch->id,
                    ],
                    [
                        'enrollment_id'        => $enrollment->id,
                        'semester_id'          => $enrollment->semester_id,
                        'class_test_obtained'  => $preset['ct_ob'],
                        'class_test_converted' => $preset['ct_cv'],
                        'midterm_obtained'     => $preset['mid_ob'],
                        'midterm_converted'    => $preset['mid_cv'],
                        'final_obtained'       => $preset['fn_ob'],
                        'final_converted'      => $preset['fn_cv'],
                        'attendance_percent'   => $preset['att_pct'],
                        'attendance_converted' => $preset['att_cv'],
                        'total_mark'           => $preset['total'],
                        'grade'                => $preset['grade'],
                        'gpa'                  => $preset['gpa'],
                        'status'               => $preset['status'],
                        'generated_by'         => $adminId,
                        'generated_at'         => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback: delete seeded demo final marks
        $targetBatchIds = Batch::where('id', 6)->orWhere('name', 'like', '%RNC 2601%')->pluck('id');
        $subjectId = Subject::where('id', 4)->orWhere('code', 'ARG 301')->value('id');

        if ($targetBatchIds->isNotEmpty() && $subjectId) {
            FinalMark::whereIn('batch_id', $targetBatchIds)->where('subject_id', $subjectId)->delete();
        }
    }
};
