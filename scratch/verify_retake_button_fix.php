<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectRetake;
use App\Models\Invoice;
use App\Http\Controllers\Admin\SubjectRetakeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== TEST 1: Blade View Rendering & Attribute Syntax Check ===\n";

$adminUser = User::where('role', 'SUPER_ADMIN')->orWhere('role', 'ADMIN')->first();
if (!$adminUser) {
    throw new Exception("No admin user found.");
}
Auth::login($adminUser);

$mockStudent = new Student([
    'name' => 'মো: বনী আমিন খান',
    'student_code' => '26810510001'
]);
$mockStudent->id = 8881;

$mockSubject = new Subject([
    'name' => 'Arabic Language - 1',
    'code' => 'ARB101'
]);
$mockSubject->id = 8882;

$mockRetake = new SubjectRetake([
    'retake_type' => 'FULL_RESTART',
    'status'      => 'PENDING',
]);
$mockRetake->id = 8883;
$mockRetake->setRelation('student', $mockStudent);
$mockRetake->setRelation('subject', $mockSubject);
$mockRetake->created_at = now();

$html = view('admin.retakes.index', [
    'retakes'  => collect([$mockRetake]),
    'students' => collect([]),
    'subjects' => collect([$mockSubject]),
])->render();

// Check 1: Button HTML should not have broken quotes inside onclick
if (!preg_match('/<button[^>]*class="[^"]*btn-approve-retake[^"]*"[^>]*>/', $html, $matches)) {
    echo "FAIL: .btn-approve-retake button not found in rendered HTML!\n";
    exit(1);
}
$buttonHtml = $matches[0];
echo "Rendered Button HTML: " . $buttonHtml . "\n";

if (strpos($buttonHtml, 'data-id="8883"') === false) {
    echo "FAIL: data-id attribute missing on button!\n";
    exit(1);
}
if (strpos($buttonHtml, 'data-student="মো: বনী আমিন খান"') === false) {
    echo "FAIL: data-student attribute missing on button!\n";
    exit(1);
}
if (strpos($buttonHtml, 'data-subject="Arabic Language - 1"') === false) {
    echo "FAIL: data-subject attribute missing on button!\n";
    exit(1);
}
if (strpos($buttonHtml, 'openApproveRetakeFromBtn(this)') === false) {
    echo "FAIL: openApproveRetakeFromBtn(this) onclick missing!\n";
    exit(1);
}

// Check 2: Verify JavaScript functions are present in output
$requiredFunctions = ['openApproveRetakeFromBtn', 'openApproveRetake', 'openModal', 'closeModal'];
foreach ($requiredFunctions as $fn) {
    if (strpos($html, $fn) === false) {
        echo "FAIL: Required JavaScript function {$fn} missing from page!\n";
        exit(1);
    }
}
echo "PASS: View rendered cleanly and all JS handlers and data attributes are valid.\n\n";

echo "=== TEST 2: Controller Approval & Invoice Generation Execution ===\n";
DB::beginTransaction();
try {
    $student = Student::first();
    $subject = Subject::first();
    if (!$student || !$subject) {
        throw new Exception("Student or Subject record required in DB for approval test.");
    }

    $retake = SubjectRetake::create([
        'student_id'  => $student->id,
        'subject_id'  => $subject->id,
        'retake_type' => 'CLASS_EXAM',
        'status'      => 'PENDING',
        'reason'      => 'Test Retake Request',
    ]);

    $controller = new SubjectRetakeController();
    $request = Request::create("/admin/retakes/{$retake->id}/approve", 'PATCH', [
        'retake_fee' => 1500,
        'notes'      => 'Test Approval Notes',
    ]);

    $response = $controller->approve($request, $retake);
    
    // Refresh retake from DB
    $retake->refresh();

    if ($retake->status !== 'IN_PROGRESS') {
        echo "FAIL: Expected retake status IN_PROGRESS, got: " . $retake->status . "\n";
        exit(1);
    }
    if ((float)$retake->retake_fee !== 1500.0) {
        echo "FAIL: Expected retake_fee 1500, got: " . $retake->retake_fee . "\n";
        exit(1);
    }

    // Verify invoice created
    $invoice = Invoice::where('source_type', SubjectRetake::class)
        ->where('source_id', $retake->id)
        ->first();

    if (!$invoice) {
        echo "FAIL: Invoice was not created for approved retake!\n";
        exit(1);
    }

    if ((float)$invoice->amount !== 1500.0) {
        echo "FAIL: Invoice amount mismatch, expected 1500, got: " . $invoice->amount . "\n";
        exit(1);
    }

    echo "PASS: Retake approval and fee setting executed perfectly. Invoice {$invoice->invoice_no} created with amount ৳{$invoice->amount}.\n";
} finally {
    DB::rollBack();
    echo "DB Transaction rolled back cleanly.\n";
}

echo "\n=== ALL TESTS PASSED SUCCESSFULLY (Exit Code 0) ===\n";
exit(0);
