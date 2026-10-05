<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::find(24);
auth()->loginUsingId($student->user_id);

$course = \App\Models\Course::find(15);
echo "=== Course 15 Semesters ===" . PHP_EOL;
foreach ($course->semesters as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Seq: {$s->sequence_no}" . PHP_EOL;
}

$controller = app()->make(\App\Http\Controllers\Student\FeeController::class);

foreach ($course->semesters as $s) {
    echo PHP_EOL . "==========================================" . PHP_EOL;
    echo "TESTING SEMESTER: ID {$s->id} ({$s->name})" . PHP_EOL;
    echo "==========================================" . PHP_EOL;
    $req = \Illuminate\Http\Request::create('/student/fees', 'GET', [
        'course_id'   => 15,
        'semester_id' => $s->id,
    ]);
    app()->instance('request', $req);
    $view = $controller->index($req);
    $data = $view->getData();
    echo "selectedSemesterId: " . json_encode($data['selectedSemesterId']) . PHP_EOL;
    echo "selectedSemester: " . ($data['selectedSemester']?->name ?? 'null') . PHP_EOL;
    echo "hasPriorSemesterDue: " . ($data['hasPriorSemesterDue'] ? 'true' : 'false') . PHP_EOL;
    echo "priorDueAmount: " . $data['priorDueAmount'] . PHP_EOL;
    echo "priorDueSemesterName: " . $data['priorDueSemesterName'] . PHP_EOL;
    echo "monthlyTuition: " . ($data['monthlyTuition'] ?? 'null') . PHP_EOL;
    echo "step1Particulars:" . PHP_EOL;
    foreach ($data['step1Particulars'] as $p) {
        echo "  SL {$p['sl']}: {$p['name']} | Amount: {$p['amount']} | Due: {$p['due']} | Paid: " . ($p['is_paid'] ? 'Y' : 'N') . PHP_EOL;
    }
}
