<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\FeeController;

echo "=== Verifying Add Fee Option in Student Fees Module ===\n";

// 1. Verify routes exist
$storeRoute = Route::getRoutes()->getByName('student.fees.particular.store');
assert($storeRoute !== null, "Route 'student.fees.particular.store' must be registered");
assert(in_array('POST', $storeRoute->methods()), "Route must accept POST requests");

$deleteRoute = Route::getRoutes()->getByName('student.fees.particular.delete');
assert($deleteRoute !== null, "Route 'student.fees.particular.delete' must be registered");
assert(in_array('POST', $deleteRoute->methods()), "Route must accept POST requests");
echo "✓ Routes verified: student.fees.particular.store & student.fees.particular.delete\n";

// 2. Setup Test Data (Student, Invoice, Admin)
$adminUser = User::where('role', 'ADMIN')->first();
if (!$adminUser) {
    $adminUser = User::create([
        'name'     => 'Test Admin User',
        'email'    => 'admin_test_fees_' . time() . '@example.com',
        'password' => bcrypt('password'),
        'role'     => 'ADMIN',
    ]);
}

$student = Student::first();
if (!$student) {
    $user = User::create([
        'name'     => 'Test Student User',
        'email'    => 'student_test_fees_' . time() . '@example.com',
        'password' => bcrypt('password'),
        'role'     => 'STUDENT',
    ]);
    $student = Student::create([
        'user_id' => $user->id,
        'name'    => 'Test Student',
        'phone'   => '01700000000',
    ]);
}

$course = Course::first();
$semester = Semester::first();

// Create a test invoice
$testInvoice = Invoice::create([
    'invoice_no'     => 'INV-TEST-ADD-' . time(),
    'student_id'     => $student->id,
    'category'       => 'SEMESTER',
    'title'          => 'Test Semester Tuition Fee',
    'amount'         => 3000.00,
    'discount'       => 0.00,
    'payable_amount' => 3000.00,
    'paid_amount'    => 0.00,
    'due_amount'     => 3000.00,
    'status'         => 'UNPAID',
    'due_date'       => now()->addDays(15),
    'created_by'     => $adminUser->id,
    'custom_particulars' => [],
]);

$controller = new FeeController();

// 3. Test Unauthorized Access (Non-Admin)
auth()->logout();
session()->flush();

$unauthReq = Request::create(route('student.fees.particular.store'), 'POST', [
    'invoice_id'      => $testInvoice->id,
    'particular_name' => 'লেট ফি (Late Fee)',
    'amount'          => 200,
]);
$unauthRes = $controller->storeParticular($unauthReq);
assert($unauthRes->getStatusCode() === 403, "Non-admin request must be rejected with 403");
echo "✓ Non-admin unauthorized request correctly blocked with 403\n";

// 4. Test Authorized Fee Addition as Admin
auth()->login($adminUser);

$feeName = 'লেট ফি (Late Fee)';
$feeAmount = 250.00;
$feeRemarks = 'দেরিতে ফি পরিশোধের জন্য জরিমানা';

$addReq = Request::create(route('student.fees.particular.store'), 'POST', [
    'invoice_id'      => $testInvoice->id,
    'particular_name' => $feeName,
    'amount'          => $feeAmount,
    'remarks'         => $feeRemarks,
]);

$addRes = $controller->storeParticular($addReq);
$data = json_decode($addRes->getContent(), true);

assert($addRes->getStatusCode() === 200, "Store request must return 200 OK");
assert(!empty($data['success']), "Response success flag must be true");
assert($data['particular']['name'] === $feeName, "Particular name must match input");
assert((float)$data['particular']['due'] === $feeAmount, "Particular due must match fee amount");

$testInvoice->refresh();
assert(isset($testInvoice->custom_particulars[$feeName]), "Invoice custom_particulars must have new fee item");
assert((float)$testInvoice->custom_particulars[$feeName]['due'] === $feeAmount, "Custom particular due must match");
assert(!empty($testInvoice->custom_particulars[$feeName]['is_added']), "is_added flag must be true");
assert((float)$testInvoice->payable_amount === 3250.00, "Invoice payable_amount must increase by 250 (3000 -> 3250)");
assert((float)$testInvoice->due_amount === 3250.00, "Invoice due_amount must increase by 250 (3000 -> 3250)");
assert((float)$testInvoice->amount === 3250.00, "Invoice total amount must increase by 250 (3000 -> 3250)");

echo "✓ Fee successfully added and persisted to invoice (Amounts: 3000 -> 3250)\n";

// 5. Verify Blade Template contains required elements
$viewContent = file_get_contents(__DIR__ . '/../resources/views/student/fees/index.blade.php');
assert(str_contains($viewContent, 'openAdminAddFeeModal()'), "Blade must have openAdminAddFeeModal trigger");
assert(str_contains($viewContent, 'adminAddFeeModal'), "Blade must contain adminAddFeeModal dialog");
assert(str_contains($viewContent, 'aaf_particular_name'), "Modal must contain particular name input");
assert(str_contains($viewContent, 'aaf_amount'), "Modal must contain amount input");
assert(str_contains($viewContent, 'student.fees.particular.store'), "Script must call student.fees.particular.store");
assert(str_contains($viewContent, 'student.fees.particular.delete'), "Script must call student.fees.particular.delete");
assert(str_contains($viewContent, 'নতুন যুক্ত'), "Blade must display 'নতুন যুক্ত' badge for added particulars");
assert(str_contains($viewContent, 'Kalpurush'), "Blade must use 'Kalpurush' font");
echo "✓ Blade view template contains Add Fee buttons, modal, preset pills, and delete actions\n";

// 6. Test Deleting the Custom Added Fee
$delReq = Request::create(route('student.fees.particular.delete'), 'POST', [
    'invoice_id'      => $testInvoice->id,
    'particular_name' => $feeName,
]);
$delRes = $controller->deleteParticular($delReq);
$delData = json_decode($delRes->getContent(), true);

assert($delRes->getStatusCode() === 200, "Delete request must return 200 OK");
assert(!empty($delData['success']), "Delete response success flag must be true");

$testInvoice->refresh();
assert(!isset($testInvoice->custom_particulars[$feeName]), "Deleted particular must be removed from custom_particulars");
assert((float)$testInvoice->payable_amount === 3000.00, "Invoice payable_amount must revert back to 3000");
assert((float)$testInvoice->due_amount === 3000.00, "Invoice due_amount must revert back to 3000");
assert((float)$testInvoice->amount === 3000.00, "Invoice amount must revert back to 3000");

echo "✓ Added fee successfully deleted and invoice amounts reverted back\n";

// 7. Clean up test invoice
$testInvoice->forceDelete();
echo "✓ Test invoice cleaned up\n";

echo "\nALL ASSERTIONS PASSED! Add Fee feature is fully verified.\n";
exit(0);
