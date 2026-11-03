<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Assignment;
use App\Models\AssignmentAppeal;
use App\Models\AssignmentSubmission;
use App\Models\FinalMark;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AssignmentAppealAndResultSyncTest extends TestCase
{
    use DatabaseTransactions;

    public function test_assignment_appeal_allows_late_submission_without_fee()
    {
        $course = Course::create(['name' => 'Arabic Grammar', 'code' => 'ARB-01', 'type' => 'SEMESTER_BASED']);
        $batch = Batch::create(['name' => 'Batch 01', 'batch_code' => 'B01', 'course_id' => $course->id, 'start_date' => now()]);
        $subject = Subject::create(['name' => 'Nahw', 'code' => 'NAH-01']);

        $user = User::create(['name' => 'Student One', 'email' => 'student_app@iom.test', 'password' => bcrypt('password'), 'role' => 'student']);
        $student = Student::create(['user_id' => $user->id, 'name' => 'Student One', 'email' => 'student_app@iom.test', 'student_code' => '701']);

        // Past deadline assignment
        $assignment = Assignment::create([
            'subject_id'   => $subject->id,
            'batch_id'     => $batch->id,
            'title'        => 'Nahw Homework 1',
            'total_marks'  => 20,
            'due_datetime' => now()->subDays(2), // expired!
            'status'       => 'PUBLISHED',
        ]);

        $this->assertTrue($assignment->isExpired());

        // Student applies for late submission grace
        $appeal = AssignmentAppeal::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'reason'        => 'Illness and hospital stay',
            'status'        => 'PENDING',
        ]);

        $this->assertTrue($appeal->isPending());

        $admin = User::create(['name' => 'Admin User', 'email' => 'admin_app@iom.test', 'password' => bcrypt('password'), 'role' => 'admin']);

        // Admin approves appeal without fee
        $this->actingAs($admin)->post(route('admin.assignments.appeals.approve', $appeal));

        $appeal->refresh();
        $this->assertTrue($appeal->isApproved());
        $this->assertEquals($admin->id, $appeal->reviewed_by);
    }

    public function test_auto_assignment_sync_to_final_marks()
    {
        $course = Course::create(['name' => 'Hadith', 'code' => 'HAD-02', 'type' => 'SEMESTER_BASED']);
        $batch = Batch::create(['name' => 'Batch 02', 'batch_code' => 'B02', 'course_id' => $course->id, 'start_date' => now()]);
        $subject = Subject::create(['name' => 'Bukhari', 'code' => 'BUK-01']);

        $student = Student::create(['name' => 'Student Two', 'email' => 'student_sync@iom.test', 'student_code' => '702']);

        $assignment = Assignment::create([
            'subject_id'   => $subject->id,
            'batch_id'     => $batch->id,
            'title'        => 'Bukhari Research',
            'total_marks'  => 50,
            'due_datetime' => now()->addDays(5),
            'status'       => 'PUBLISHED',
        ]);

        // Graded submission with 45 out of 50 (90%)
        AssignmentSubmission::create([
            'assignment_id'   => $assignment->id,
            'student_id'      => $student->id,
            'submission_file' => 'assignments/test.pdf',
            'status'          => 'GRADED',
            'obtained_marks'  => 45,
            'submitted_at'    => now(),
        ]);

        // Create FinalMark record
        $fm = FinalMark::create([
            'student_id' => $student->id,
            'batch_id'   => $batch->id,
            'subject_id' => $subject->id,
        ]);

        $admin = User::create(['name' => 'Admin Sync', 'email' => 'admin_sync@iom.test', 'password' => bcrypt('password'), 'role' => 'admin']);

        // Auto assignment sync call
        $this->actingAs($admin)->post(route('admin.result-book.auto-assignment'), [
            'batch_id'   => $batch->id,
            'subject_id' => $subject->id,
        ]);

        $fm->refresh();
        // 45 / 50 * 10 = 9.0 marks for Tamrin
        $this->assertEquals(9.0, (float)$fm->tamrin_mark);
    }
}
