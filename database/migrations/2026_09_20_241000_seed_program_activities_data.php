<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\ProgramActivity;
use App\Models\Course;
use App\Models\Batch;

return new class extends Migration
{
    public function up(): void
    {
        if (ProgramActivity::count() > 0) {
            return;
        }

        $courses = Course::all()->keyBy('name');

        $sampleData = [
            [
                'semester_name'  => 'Fall 2026 (Jul-Dec)',
                'course_name'    => 'School Maktab Nazera (Bangla)',
                'batch_name'     => null,
                'starting_month' => 'September',
                'start_date'     => '2026-09-01',
                'end_date'       => '2027-02-28',
            ],
            [
                'semester_name'  => 'Fall 2026 (Jul-Dec)',
                'course_name'    => 'Alim Preparatory Course',
                'batch_name'     => null,
                'starting_month' => 'July',
                'start_date'     => '2026-07-01',
                'end_date'       => '2026-12-31',
            ],
            [
                'semester_name'  => 'Fall 2026 (Jul-Dec)',
                'course_name'    => 'আলিম কোর্স',
                'batch_name'     => null,
                'starting_month' => 'October',
                'start_date'     => '2026-10-01',
                'end_date'       => '2027-03-31',
            ],
            [
                'semester_name'  => 'Fall 2026 (Jul-Dec)',
                'course_name'    => 'Course (Semester Based)',
                'batch_name'     => null,
                'starting_month' => 'September',
                'start_date'     => '2026-09-01',
                'end_date'       => '2027-02-28',
            ],
            [
                'semester_name'  => 'Fall 2026 (Jul-Dec)',
                'course_name'    => null, // Any Program
                'batch_name'     => null, // Any Batch
                'starting_month' => 'July',
                'start_date'     => '2026-07-01',
                'end_date'       => '2026-12-31',
            ],
        ];

        foreach ($sampleData as $item) {
            $courseId = null;
            if ($item['course_name'] && isset($courses[$item['course_name']])) {
                $courseId = $courses[$item['course_name']]->id;
            }

            ProgramActivity::create([
                'semester_name'  => $item['semester_name'],
                'course_id'      => $courseId,
                'batch_id'       => null,
                'starting_month' => $item['starting_month'],
                'start_date'     => $item['start_date'],
                'end_date'       => $item['end_date'],
                'is_active'      => true,
            ]);
        }
    }

    public function down(): void
    {
        // Safe to leave
    }
};
