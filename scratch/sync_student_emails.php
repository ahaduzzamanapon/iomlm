<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Student;

echo "--- Synchronizing Existing Student Emails (Safe Mode) ---\n";

$iomUsers = User::where('email', 'like', '%@iom.student%')->get();
$updated = 0;
$skipped = 0;

foreach ($iomUsers as $user) {
    $student = Student::where('user_id', $user->id)->first();
    if (!$student) {
        $skipped++;
        continue;
    }

    $realEmail = trim((string)$student->email);

    if (!empty($realEmail) && filter_var($realEmail, FILTER_VALIDATE_EMAIL)) {
        // Check if another user already has this exact email
        $existingUser = User::where('email', $realEmail)->where('id', '!=', $user->id)->first();
        if (!$existingUser) {
            $oldEmail = $user->email;
            $user->email = $realEmail;
            $user->save();
            echo "Updated User #{$user->id} ({$student->name}): {$oldEmail} -> {$realEmail}\n";
            $updated++;
        } else {
            echo "Skipped User #{$user->id} ({$student->name}): Email {$realEmail} already in use by User #{$existingUser->id}\n";
            $skipped++;
        }
    } else {
        $skipped++;
    }
}

echo "\nSummary: Updated={$updated}, Skipped={$skipped}\n";
