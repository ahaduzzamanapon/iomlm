<?php

namespace Database\Seeders;

use App\Models\AdmissionCircular;
use App\Models\AdmissionCircularBatch;
use App\Models\Batch;
use App\Models\Course;
use Illuminate\Database\Seeder;

class AdmissionCircularSeeder extends Seeder
{
    public function run(): void
    {
        if (AdmissionCircular::count() > 0) {
            return;
        }

        $circulars = [
            [
                'name'              => 'Adm Fall 2026 (Jul-Dec)',
                'short_name'        => 'Adm Fall 2026 (Jul-Dec)',
                'semester_name'     => 'Fall 2026 (Jul-Dec)',
                'session_year'      => '2025-2026',
                'student_id_prefix' => '26',
                'program_type'      => 'Any',
                'circular_status'   => 'Current',
                'is_enabled'        => true,
                'is_program_batch_map_enabled' => true,
                'remark'            => 'Active session for Fall 2026 admissions',
            ],
            [
                'name'              => 'Adm Spring 2026 (Jan-Jun)',
                'short_name'        => 'Adm Spring 2026 (Jan-Jun)',
                'semester_name'     => 'Spring 2026 (Jan-Jun)',
                'session_year'      => '2025-2026',
                'student_id_prefix' => '26',
                'program_type'      => 'Any',
                'circular_status'   => 'Expired',
                'is_enabled'        => false,
                'is_program_batch_map_enabled' => true,
            ],
            [
                'name'              => 'Adm Fall 2025 (Jul-Dec)',
                'short_name'        => 'Adm Fall 2025 (Jul-Dec)',
                'semester_name'     => 'Fall 2025 (Jul-Dec)',
                'session_year'      => '2024-2025',
                'student_id_prefix' => '25',
                'program_type'      => 'Any',
                'circular_status'   => 'Expired',
                'is_enabled'        => false,
                'is_program_batch_map_enabled' => true,
            ],
            [
                'name'              => 'Adm Spring 2025 (Jan-Jun)',
                'short_name'        => 'Adm Spring 2025 (Jan-Jun)',
                'semester_name'     => 'Spring 2025 (Jan-Jun)',
                'session_year'      => '2024-2025',
                'student_id_prefix' => '25',
                'program_type'      => 'Any',
                'circular_status'   => 'Expired',
                'is_enabled'        => false,
                'is_program_batch_map_enabled' => true,
            ],
        ];

        foreach ($circulars as $circData) {
            $circ = AdmissionCircular::create($circData);

            if ($circ->circular_status === 'Current') {
                $courses = Course::where('is_active', true)->get();
                foreach ($courses as $c) {
                    $firstBatch = Batch::where('course_id', $c->id)->where('status', 'ACTIVE')->first();
                    AdmissionCircularBatch::create([
                        'admission_circular_id'       => $circ->id,
                        'course_id'                   => $c->id,
                        'batch_id'                    => $firstBatch ? $firstBatch->id : null,
                        'campus'                      => 'Main Campus',
                        'is_online_admission_enabled' => true,
                    ]);
                }
            }
        }
    }
}
