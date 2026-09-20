<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EXAMS BY SUBJECT AND TYPE ===\n";
$exams = \App\Models\Exam::with(['subject', 'semester'])->get();
echo "Total exams: " . $exams->count() . "\n";
foreach ($exams->take(15) as $ex) {
    echo "Exam: {$ex->title} | Type: {$ex->type} | Sub: {$ex->subject?->name} | Sem: {$ex->semester?->name}\n";
}
