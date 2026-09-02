<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix existing SEMESTER invoices title for SUBJECT_BASED courses ===\n";

$invs = App\Models\Invoice::where('category', 'SEMESTER')
    ->where('title', 'like', '%Semester Tuition Fee%')
    ->get();

foreach ($invs as $inv) {
    $enrollment = $inv->enrollment;
    if (!$enrollment) continue;
    $course = $enrollment->course;
    if (!$course || $course->type !== 'SUBJECT_BASED') continue;

    $newTitle = str_replace('Semester Tuition Fee', 'Course Tuition Fee', $inv->title);
    // Also remove " (Current Semester)" suffix for subject-based
    $newTitle = preg_replace('/\s*\(Current Semester\)\s*/i', '', $newTitle);
    $newTitle = trim($newTitle);

    echo "Fixing ID: {$inv->id} | Old: {$inv->title}\n  -> New: {$newTitle}\n";
    $inv->update(['title' => $newTitle]);
}

echo "\nDone.\n";
