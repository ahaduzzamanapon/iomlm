<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\AdmissionForm;

$st = Student::where('phone', 'like', '%01785347267%')->orWhere('student_code', 'like', '%27015010001%')->with('user')->first();
if ($st) {
    echo "Found Student ID: {$st->id}, Code: {$st->student_code}, Name: {$st->name}, Email: {$st->email}\n";
    echo "User ID: " . ($st->user?->id ?? 'none') . ", User Email: " . ($st->user?->email ?? 'none') . "\n";
} else {
    echo "Student not found by phone 01785347267 or code 27015010001.\n";
}

$af = AdmissionForm::where('phone', 'like', '%01785347267%')->first();
if ($af) {
    echo "Found AF ID: {$af->id}, AppNo: {$af->application_no}, Status: {$af->status}, Student ID: {$af->student_id}\n";
}
