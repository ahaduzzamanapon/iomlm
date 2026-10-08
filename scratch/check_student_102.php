<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

$s = Student::with('user', 'enrollments.course', 'enrollments.batch')->find(102);
if ($s) {
    print_r($s->toArray());
}
