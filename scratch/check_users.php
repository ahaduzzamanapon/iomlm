<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Student;

$users = User::whereIn('id', [44, 92, 105])->get();
foreach ($users as $u) {
    $students = Student::where('user_id', $u->id)->get();
    echo "User ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Students: " . $students->pluck('id')->join(', ') . "\n";
}
