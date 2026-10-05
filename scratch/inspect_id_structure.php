<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\Course;
use App\Models\Student;
use App\Models\AdmissionForm;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== ACADEMIC YEARS ===\n";
if (Schema::hasTable('academic_years')) {
    echo "Columns: " . implode(', ', Schema::getColumnListing('academic_years')) . "\n";
    foreach (DB::table('academic_years')->get() as $ay) {
        echo json_encode($ay) . "\n";
    }
} else {
    echo "academic_years table does not exist\n";
}

echo "\n=== BATCH 22 & FORM 26 ===\n";
$b22 = Batch::find(22);
if ($b22) {
    echo "Batch 22: " . json_encode($b22->toArray()) . "\n";
}
$form26 = AdmissionForm::where('application_no', 'APP-2026-0026')->first();
if ($form26) {
    echo "Form 26: " . json_encode($form26->toArray()) . "\n";
    if ($form26->student) {
        echo "Student: " . json_encode($form26->student->toArray()) . "\n";
    }
}
