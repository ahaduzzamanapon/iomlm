<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;

$st = Student::with('user', 'enrollments.course', 'enrollments.batch.academicYear')->find(15);
echo "Student 15: " . json_encode([
    'id' => $st->id,
    'user_id' => $st->user_id,
    'name' => $st->user?->name,
    'code' => $st->student_code,
    'fee_package_id' => $st->fee_package_id,
    'batch_id' => $st->enrollments->first()?->batch_id,
    'batch_name' => $st->enrollments->first()?->batch?->name,
    'batch_start_date' => $st->enrollments->first()?->batch?->start_date,
    'batch_fee_start_month' => $st->enrollments->first()?->batch?->fee_start_month,
    'academic_year' => $st->enrollments->first()?->batch?->academicYear?->name,
    'academic_year_start' => $st->enrollments->first()?->batch?->academicYear?->start_date,
]) . PHP_EOL;

$controller = app()->make(\App\Http\Controllers\Student\FeeController::class);
auth()->loginUsingId($st->user_id);
$req = \Illuminate\Http\Request::create('/student/fees', 'GET', [
    'course_id'   => 15,
    'semester_id' => 54, // Semester 2
]);
app()->instance('request', $req);
$view = $controller->index($req);
$data = $view->getData();

echo "hasPriorSemesterDue: " . ($data['hasPriorSemesterDue'] ? 'true' : 'false') . PHP_EOL;
echo "priorDueAmount: " . $data['priorDueAmount'] . PHP_EOL;
echo "priorDueSemesterName: " . $data['priorDueSemesterName'] . PHP_EOL;
echo "monthlyTuition: " . ($data['monthlyTuition'] ?? 'null') . PHP_EOL;
echo "step1Particulars for Student 15 Semester 2:" . PHP_EOL;
foreach ($data['step1Particulars'] as $p) {
    echo "  SL {$p['sl']}: {$p['name']} | Amount: {$p['amount']} | Due: {$p['due']} | Paid: " . ($p['is_paid'] ? 'Y' : 'N') . PHP_EOL;
}
