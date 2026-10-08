<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdmissionForm;
use App\Models\Student;

$af = AdmissionForm::where('application_no', 'like', '%0062%')->first();
if ($af) {
    echo "Found AF: {$af->id}, App: {$af->application_no}, Student: {$af->student_id}\n";
    $st = $af->student;
    if ($st) {
        echo "Student: {$st->name}, Code: {$st->student_code}, Email: {$st->email}, UserID: {$st->user_id}, UserEmail: " . ($st->user?->email ?? 'none') . "\n";
    }
} else {
    echo "No AF matching 0062. All latest AFs:\n";
    foreach (AdmissionForm::withTrashed()->latest()->take(10)->get() as $f) {
        echo "ID {$f->id}: {$f->application_no}, student {$f->student_id}\n";
    }
}

$st2 = Student::where('student_code', 'like', '%0001%')->orWhere('student_code', 'like', '%0002%')->get();
echo "\nStudents with 0001 or 0002:\n";
foreach ($st2 as $s) {
    echo "Student ID {$s->id}, Code: {$s->student_code}, Name: {$s->name}, Email: {$s->email}, UserEmail: " . ($s->user?->email ?? 'none') . "\n";
}
