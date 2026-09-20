<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING BATCHES WITH ENROLLMENTS & FINAL MARKS ===\n";
foreach (\App\Models\Batch::with(['course.semesters', 'enrollments.student'])->get() as $b) {
    $activeEnr = $b->enrollments->where('status', 'ACTIVE')->count();
    if ($activeEnr > 0) {
        $marksCount = \App\Models\FinalMark::where('batch_id', $b->id)->count();
        $semCount = $b->course?->semesters?->count() ?? 0;
        $cType = $b->course?->type ?? 'N/A';
        echo "Batch {$b->id}: {$b->name} | Course: {$b->course?->name} ({$cType}, {$semCount} sems) | Enr: {$activeEnr} | Final Marks: {$marksCount}\n";
    }
}
