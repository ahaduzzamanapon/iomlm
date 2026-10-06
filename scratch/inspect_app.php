<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$st = App\Models\Student::find(8);
echo json_encode($st->toArray(), JSON_PRETTY_PRINT);

$forms = App\Models\AdmissionForm::where('student_id', 8)->get();
echo "\nForms for student 8:\n";
echo json_encode($forms->map(fn($f) => $f->only(['id', 'application_no', 'status', 'batch_id', 'created_at']))->toArray(), JSON_PRETTY_PRINT);
