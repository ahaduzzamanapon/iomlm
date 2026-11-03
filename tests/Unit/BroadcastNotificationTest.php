<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BroadcastNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_batch_relationship_resolves_without_exception()
    {
        $user = User::create([
            'name' => 'Test Student User',
            'email' => 'student_broadcast@iom.test',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $course = Course::create(['name' => 'Fiqh', 'code' => 'FIQ-01', 'type' => 'SEMESTER_BASED']);
        $batch = Batch::create(['name' => 'Fiqh Batch 1', 'batch_code' => 'FB01', 'course_id' => $course->id, 'start_date' => now()]);

        $student = Student::create([
            'user_id' => $user->id,
            'name' => 'Test Student User',
            'email' => 'student_broadcast@iom.test',
            'student_code' => '9901',
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'batch_id' => $batch->id,
            'course_id' => $course->id,
            'status' => 'ACTIVE',
            'enrolled_at' => now(),
        ]);

        // Query exactly like BroadcastNotificationController does:
        $resolvedUser = User::with('student.batch.course')->where('id', $user->id)->first();

        $this->assertNotNull($resolvedUser);
        $this->assertNotNull($resolvedUser->student);
        $this->assertNotNull($resolvedUser->student->batch);
        $this->assertEquals('Fiqh Batch 1', $resolvedUser->student->batch->name);
        $this->assertNotNull($resolvedUser->student->batch->course);
        $this->assertEquals('Fiqh', $resolvedUser->student->batch->course->name);
    }
}
