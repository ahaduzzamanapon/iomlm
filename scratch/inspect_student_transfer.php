<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\CourseTransfer;

$students = Student::all();
echo "TOTAL STUDENTS: " . $students->count() . "\n";
foreach ($students as $s) {
    echo "ID: {$s->id} | Name: [{$s->name}] | Code: [{$s->student_code}] | Phone: [{$s->phone}] | Email: [{$s->email}]\n";
}
