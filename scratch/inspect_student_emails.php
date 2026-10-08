<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Models\AdmissionForm;

echo "--- Students with @iom.student in users ---\n";
$users = User::where('email', 'like', '%@iom.student%')->get();
foreach ($users as $u) {
    $st = Student::where('user_id', $u->id)->first();
    $form = $st ? AdmissionForm::where('student_id', $st->id)->first() : null;
    echo "User ID: {$u->id}, User Email: {$u->email}, Student Name: " . ($st ? $st->name : 'N/A') . 
         ", Student Email: " . ($st ? $st->email : 'N/A') . 
         ", Form Email: " . ($form ? $form->email : 'N/A') . "\n";
}

echo "\n--- Check Student 27015010001 ---\n";
$st = Student::with('user', 'admissionForms')->where('student_code', 'like', '%27015010001%')->first();
if ($st) {
    echo "Found student: ID={$st->id}, Name={$st->name}, Code={$st->student_code}, Email={$st->email}, UserID={$st->user_id}\n";
    if ($st->user) {
        echo "User: ID={$st->user->id}, Email={$st->user->email}\n";
    }
    foreach ($st->admissionForms as $af) {
        echo "Form: ID={$af->id}, Email={$af->email}, Status={$af->status}\n";
    }
}
