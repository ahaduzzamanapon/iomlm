<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

$students = Student::where('name', 'like', '%Mazhar%')
    ->orWhere('phone', 'like', '%01785347267%')
    ->orWhere('name', 'like', '%Umar%')
    ->get();

foreach ($students as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Code: {$s->student_code}, Phone: {$s->phone}, Email: {$s->email}, UserID: {$s->user_id}\n";
    if ($s->user) {
        echo "  User: ID={$s->user->id}, Email={$s->user->email}\n";
    }
}
