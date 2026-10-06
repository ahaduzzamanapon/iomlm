<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$st8 = \App\Models\Student::find(8);
echo "St8: id=" . $st8->id . ", code=" . $st8->student_code . ", name=" . $st8->name . "\n";
foreach ($st8->admissionForms as $f) {
    echo "  -> Form ID: {$f->id}, AppNo: {$f->application_no}, Status: {$f->status}, Batch: {$f->batch_id}, Course: {$f->interested_course_id}\n";
}
foreach ($st8->enrollments as $e) {
    echo "  -> Enr ID: {$e->id}, Batch: {$e->batch_id}, Course: {$e->course_id}, Status: {$e->status}, FormID: {$e->admission_form_id}\n";
}
