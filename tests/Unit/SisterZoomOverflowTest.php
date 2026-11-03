<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\ClassSession;
use App\Models\RoutineEntry;
use App\Models\RoutineSlot;
use App\Models\Subject;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SisterZoomOverflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_class_session_normalizes_meeting_link_protocol()
    {
        $course = Course::create(['name' => 'Tafseer', 'code' => 'TAF-01', 'type' => 'SEMESTER_BASED']);
        $batch = Batch::create(['name' => 'Batch 01', 'batch_code' => 'B01', 'course_id' => $course->id, 'start_date' => now()]);

        $session = ClassSession::create([
            'batch_id' => $batch->id,
            'notes' => 'Intro Class',
            'session_date' => today(),
            'start_time' => '10:00:00',
            'meeting_link' => 'zoom.us/j/9988776655', // no http or https
        ]);

        $this->assertEquals('https://zoom.us/j/9988776655', $session->meeting_link);
    }

    public function test_bulk_allocate_sisters_to_brothers_zoom_room()
    {
        $course = Course::create(['name' => 'Hadith', 'code' => 'HAD-01', 'type' => 'SEMESTER_BASED']);
        $batch = Batch::create(['name' => 'Batch 02', 'batch_code' => 'B02', 'course_id' => $course->id, 'start_date' => now()]);

        // Create 3 sisters
        $s1 = Student::create(['name' => 'Sister 1', 'email' => 's1@iom.test', 'student_code' => '101', 'gender' => 'Female']);
        $s2 = Student::create(['name' => 'Sister 2', 'email' => 's2@iom.test', 'student_code' => '102', 'gender' => 'Female']);
        $s3 = Student::create(['name' => 'Sister 3', 'email' => 's3@iom.test', 'student_code' => '103', 'gender' => 'Female']);

        $e1 = Enrollment::create(['student_id' => $s1->id, 'batch_id' => $batch->id, 'course_id' => $course->id, 'status' => 'ACTIVE', 'enrolled_at' => now()]);
        $e2 = Enrollment::create(['student_id' => $s2->id, 'batch_id' => $batch->id, 'course_id' => $course->id, 'status' => 'ACTIVE', 'enrolled_at' => now()]);
        $e3 = Enrollment::create(['student_id' => $s3->id, 'batch_id' => $batch->id, 'course_id' => $course->id, 'status' => 'ACTIVE', 'enrolled_at' => now()]);

        $admin = User::create(['name' => 'Admin User', 'email' => 'admin_alloc@iom.test', 'password' => bcrypt('password'), 'role' => 'admin']);

        // Allocate roll range 102 to 103 to MALE (Brother Room)
        $this->actingAs($admin)->post(route('admin.batches.bulk-allocate-group', $batch), [
            'gender' => 'FEMALE',
            'target_group' => 'MALE',
            'mode' => 'roll_range',
            'roll_from' => '102',
            'roll_to' => '103',
        ]);

        $e1->refresh();
        $e2->refresh();
        $e3->refresh();

        $this->assertNull($e1->group_tag); // Roll 101 unchanged
        $this->assertEquals('MALE', $e2->group_tag); // Roll 102 allocated to MALE
        $this->assertEquals('MALE', $e3->group_tag); // Roll 103 allocated to MALE
    }
}
