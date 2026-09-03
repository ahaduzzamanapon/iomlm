<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

echo "=== USER ACCOUNT CONNECTION AUDIT ===\n\n";

// 1. Audit Teachers
$teachers = Teacher::all();
echo "Total Teachers: " . $teachers->count() . "\n";
$unlinkedTeachers = 0;

foreach ($teachers as $teacher) {
    if (empty($teacher->user_id) || !User::find($teacher->user_id)) {
        $unlinkedTeachers++;
        $nextEmpId = $teacher->employee_id ?? ('EMP-' . str_pad($teacher->id, 3, '0', STR_PAD_LEFT));
        $email = $teacher->email ?: ($nextEmpId . '@iom.teacher');
        $phone = $teacher->phone ?: 'iom@1234';

        if (User::where('email', $email)->exists()) {
            $email = 'teacher.' . $teacher->id . '@iom.teacher';
        }

        $user = User::create([
            'name'     => $teacher->name,
            'email'    => $email,
            'password' => Hash::make($phone),
            'role'     => 'teacher',
        ]);

        $teacher->update(['user_id' => $user->id]);
        echo " -> Linked Teacher ID #{$teacher->id} ({$teacher->name}) to User ID #{$user->id} ({$email})\n";
    }
}

echo "Unlinked Teachers Fixed: {$unlinkedTeachers}\n\n";

// 2. Audit Active Students
$students = Student::all();
echo "Total Students: " . $students->count() . "\n";
$unlinkedStudents = 0;

foreach ($students as $student) {
    if ($student->status === 'ACTIVE' && (empty($student->user_id) || !User::find($student->user_id))) {
        $unlinkedStudents++;
        $code = $student->student_code ?? ('STD-' . date('Y') . '-' . str_pad($student->id, 3, '0', STR_PAD_LEFT));
        $email = $student->email ?: ($code . '@iom.student');
        $phone = $student->phone ?: 'iom@1234';

        if (User::where('email', $email)->exists()) {
            $email = 'student.' . $student->id . '@iom.student';
        }

        $user = User::create([
            'name'     => $student->name,
            'email'    => $email,
            'password' => Hash::make($phone),
            'role'     => 'student',
        ]);

        $student->update(['user_id' => $user->id, 'student_code' => $code]);
        echo " -> Linked Student ID #{$student->id} ({$student->name}) to User ID #{$user->id} ({$email})\n";
    }
}

echo "Unlinked Active Students Fixed: {$unlinkedStudents}\n\n";

echo "=== AUDIT COMPLETED SUCCESSFULLY ===\n";
