<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Enrollment;
use App\Models\AdmissionForm;

$pendingFormIds = AdmissionForm::whereIn('status', ['PENDING', 'TRASH', 'REJECTED'])->pluck('id');
$enrs = Enrollment::whereIn('admission_form_id', $pendingFormIds)->get();

echo "Enrollments with admission_form_id pointing to PENDING/TRASH/REJECTED forms: " . $enrs->count() . "\n";
foreach ($enrs as $e) {
    $f = AdmissionForm::find($e->admission_form_id);
    echo "Enr ID {$e->id} | Form ID: {$e->admission_form_id} | Form Status: {$f?->status} | Student ID: {$e->student_id} | Enr Status: {$e->status}\n";
}
