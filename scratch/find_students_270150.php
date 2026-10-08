<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\AdmissionForm;

$stCodes = Student::where('student_code', 'like', '%270150%')->get();
echo "Found by 270150: " . $stCodes->count() . "\n";
foreach ($stCodes as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Code: {$s->student_code}, Phone: {$s->phone}, Email: {$s->email}, UserID: {$s->user_id}\n";
}

$allCodes = Student::orderBy('id', 'desc')->take(10)->get();
echo "\nLatest 10 students:\n";
foreach ($allCodes as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Code: {$s->student_code}, Email: {$s->email}\n";
}
