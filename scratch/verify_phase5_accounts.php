<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== STARTING PHASE 5 & RECENT REQUESTS VERIFICATION ===\n\n";

$testsPassed = 0;
$totalTests = 6;

// -------------------------------------------------------------
// TEST 1: Student Impersonation & Leave
// -------------------------------------------------------------
echo "[Test 1] Testing Student Impersonation and Return to Admin...\n";
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    $admin = User::create([
        'name' => 'Test Admin',
        'email' => 'testadmin_' . uniqid() . '@iom.test',
        'password' => bcrypt('secret123'),
        'role' => 'admin',
    ]);
}

$student = Student::first();
if (!$student) {
    $student = Student::create([
        'name' => 'Test Student User',
        'phone' => '01700000000',
        'student_code' => '26-01-01-1-9999',
        'status' => 'ACTIVE',
    ]);
}

Auth::login($admin);
assert(Auth::id() === $admin->id, "Admin must be logged in");

// Call impersonate via controller
$studentController = new \App\Http\Controllers\Admin\StudentController();
$response = $studentController->impersonate($student);

assert(session()->has('admin_impersonator_id'), "Session must store admin_impersonator_id");
assert(session()->get('admin_impersonator_id') === $admin->id, "Stored admin ID must match admin");
assert(Auth::id() === $student->user_id, "Current authenticated user must be the student user");

// Now test leave impersonation
$leaveResponse = $studentController->leaveImpersonation();
assert(!session()->has('admin_impersonator_id'), "Session admin_impersonator_id must be cleared");
assert(Auth::id() === $admin->id, "Admin must be logged back in");

echo "  -> PASS: Impersonation to student and return to admin verified!\n\n";
$testsPassed++;

// -------------------------------------------------------------
// TEST 2: Weekend Days Empty Configuration
// -------------------------------------------------------------
echo "[Test 2] Testing Weekend Days empty setting (no fallback to Friday)...\n";
$settingController = new \App\Http\Controllers\Admin\SettingController();

// Simulate request with weekend_days = ""
$request = new \Illuminate\Http\Request();
$request->merge(['weekend_days' => '']);
$settingController->update($request);

$dbSetting = Setting::where('key', 'weekend_days')->first();
assert($dbSetting !== null, "Setting must exist");
assert($dbSetting->value === '', "Setting value in DB must be exactly empty string ''");

// Test RoutineController weekends() method reflection
$routineController = new \App\Http\Controllers\Admin\RoutineController();
$reflection = new \ReflectionClass($routineController);
$method = $reflection->getMethod('weekends');
$method->setAccessible(true);
$weekends = $method->invoke($routineController);

assert(is_array($weekends) && count($weekends) === 0, "Weekends must be empty array [] when setting is empty, not ['FRI'] or ['FRI', 'SAT']");

echo "  -> PASS: Weekend days empty setting verified (does not auto-revert to Friday)!\n\n";
$testsPassed++;

// -------------------------------------------------------------
// TEST 3: Readmission and Course Transfer Filters
// -------------------------------------------------------------
echo "[Test 3] Testing Readmission & Course Transfer Controller filters...\n";
$readmissionController = new \App\Http\Controllers\Admin\ReadmissionController();
$rRequest = new \Illuminate\Http\Request(['status' => 'PENDING', 'course_id' => 1, 'batch_id' => 1]);
$rView = $readmissionController->index($rRequest);
assert($rView->getName() === 'admin.readmissions.index', "Readmission index view must load");

$transferController = new \App\Http\Controllers\Admin\CourseTransferController();
$tRequest = new \Illuminate\Http\Request(['status' => 'PENDING', 'from_course_id' => 1, 'to_course_id' => 2, 'batch_id' => 1]);
$tView = $transferController->index($tRequest);
assert($tView->getName() === 'admin.course-transfers.index', "Course transfer index view must load");

echo "  -> PASS: Readmission and Course Transfer filters verified!\n\n";
$testsPassed++;

// -------------------------------------------------------------
// TEST 4: Student Accounts Ledger CRUD (Add, Edit, Delete Invoice)
// -------------------------------------------------------------
echo "[Test 4] Testing Student Accounts Ledger CRUD...\n";
$accountsController = new \App\Http\Controllers\Admin\AccountsController();

// A. Create Manual Invoice
$invRequest = new \Illuminate\Http\Request([
    'student_id' => $student->id,
    'category'   => 'MANUAL',
    'title'      => 'Test Custom Lab Fee',
    'amount'     => 1500.00,
    'discount'   => 200.00,
    'due_date'   => now()->addDays(7)->toDateString(),
]);
$accountsController->storeInvoice($invRequest);

$testInvoice = Invoice::where('student_id', $student->id)->where('title', 'Test Custom Lab Fee')->latest()->first();
assert($testInvoice !== null, "Invoice must be created in DB");
assert((float)$testInvoice->amount === 1500.00, "Amount must be 1500");
assert((float)$testInvoice->payable_amount === 1300.00, "Payable amount must be 1300 (1500 - 200)");
assert((float)$testInvoice->due_amount === 1300.00, "Due amount must be 1300");

// B. Update Invoice
$updateRequest = new \Illuminate\Http\Request([
    'title'      => 'Updated Lab Fee',
    'category'   => 'MANUAL',
    'amount'     => 1200.00,
    'discount'   => 100.00,
    'due_date'   => now()->addDays(14)->toDateString(),
]);
$accountsController->updateInvoice($updateRequest, $testInvoice);
$testInvoice->refresh();
assert($testInvoice->title === 'Updated Lab Fee', "Title must be updated");
assert((float)$testInvoice->payable_amount === 1100.00, "Payable amount must be 1100");

// C. Delete Invoice (Safe Deletion)
$delResponse = $accountsController->destroyInvoice($testInvoice);
assert(!Invoice::where('id', $testInvoice->id)->exists(), "Unpaid invoice must be deleted");

echo "  -> PASS: Student accounts ledger CRUD verified!\n\n";
$testsPassed++;

// -------------------------------------------------------------
// TEST 5: Monthly ৳100 Course Activation Fee
// -------------------------------------------------------------
echo "[Test 5] Testing Monthly ৳100 Course Activation Fee past 10th...\n";
// Create an invoice with due for student to trigger activation fee
$unpaidInvoice = Invoice::create([
    'invoice_no'     => 'INV-DUE-' . uniqid(),
    'student_id'     => $student->id,
    'category'       => 'SEMESTER',
    'title'          => 'Semester Tuition Fee',
    'amount'         => 1000.00,
    'payable_amount' => 1000.00,
    'paid_amount'    => 0.00,
    'due_amount'     => 1000.00,
    'status'         => 'UNPAID',
    'due_date'       => Carbon::now()->subDays(5),
]);

// Apply activation fee with force=true
$actResult = AccountingService::applyCourseActivationFees(Carbon::now(), true);
assert($actResult['status'] === 'success', "Activation fee process must succeed");

$activationInv = Invoice::where('student_id', $student->id)
    ->where('category', 'FINE')
    ->where('amount', 100.00)
    ->first();

assert($activationInv !== null, "৳100 Course Activation Fee invoice must be generated");
assert((float)$activationInv->amount === 100.00, "Activation fee amount must be 100");
assert($activationInv->status === 'UNPAID', "Status must be UNPAID");

// Test idempotent (running again should skip duplicate for same month)
$actResult2 = AccountingService::applyCourseActivationFees(Carbon::now(), true);
$duplicateCount = Invoice::where('student_id', $student->id)
    ->where('category', 'FINE')
    ->where('amount', 100.00)
    ->count();
assert($duplicateCount === 1, "Duplicate activation fee must NOT be created in the same month");

// Clean up test invoices
$activationInv->delete();
$unpaidInvoice->delete();

echo "  -> PASS: ৳100 Course Activation Fee application and idempotency verified!\n\n";
$testsPassed++;

// -------------------------------------------------------------
// TEST 6: Accounts Revenue Report with Course, Month & Date Range
// -------------------------------------------------------------
echo "[Test 6] Testing Accounts Revenue Report with Course & Month filtering...\n";
$reportRequest = new \Illuminate\Http\Request([
    'course_id' => 1,
    'month'     => date('Y-m'),
]);
$reportView = $accountsController->reports($reportRequest);
assert($reportView->getName() === 'admin.accounts.reports', "Accounts report view must load");
assert(array_key_exists('courseSummary', $reportView->getData()), "View must contain courseSummary");
assert(array_key_exists('totalCollected', $reportView->getData()), "View must contain totalCollected");

echo "  -> PASS: Accounts Revenue Report filtering verified!\n\n";
$testsPassed++;

echo "=========================================================\n";
echo "ALL {$testsPassed}/{$totalTests} VERIFICATION TESTS PASSED SUCCESSFULLY! (EXIT CODE 0)\n";
echo "=========================================================\n";

exit(0);
