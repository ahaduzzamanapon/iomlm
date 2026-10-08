<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\CourseFeePackage;
use App\Models\CourseFeePackageItem;
use App\Models\FeeHead;
use App\Models\CourseTransfer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\CourseTransferService;
use App\Services\StudentFeeService;
use App\Services\AccountingService;
use App\Http\Controllers\Student\MyCourseController;
use Illuminate\Support\Str;

echo "--- VERIFYING TASK 69: COURSE TRANSFER ACTIVE DISPLAY & FEE STRUCTURE ---\n\n";

$passCount = 0;
function assertTask(bool $cond, string $msg) {
    global $passCount;
    if (!$cond) {
        echo "❌ [FAIL] {$msg}\n";
        exit(1);
    }
    echo "✅ [PASS] {$msg}\n";
    $passCount++;
}

// 1. Setup Test Data
$suffix = Str::random(5);
$user = User::create([
    'name'     => "Test Transfer Student {$suffix}",
    'email'    => "transfer_{$suffix}@test.com",
    'password' => bcrypt('password'),
    'role'     => 'STUDENT',
]);

$courseA = Course::create([
    'name'           => "Course Alpha {$suffix}",
    'code'           => "CA" . rand(10, 99),
    'type'           => 'SEMESTER_BASED',
    'is_active'      => true,
    'admission_fee'  => 1000,
]);
$batchA = Batch::create([
    'course_id'  => $courseA->id,
    'name'       => "Batch A {$suffix}",
    'status'     => 'ACTIVE',
    'start_date' => now()->subMonths(6)->toDateString(),
]);
$semA1 = Semester::create([
    'course_id'   => $courseA->id,
    'name'        => "Semester 1",
    'sequence_no' => 1,
]);

$courseB = Course::create([
    'name'           => "Course Beta {$suffix}",
    'code'           => "CB" . rand(10, 99),
    'type'           => 'SEMESTER_BASED',
    'is_active'      => true,
    'admission_fee'  => 1500,
]);
for ($s = 1; $s <= 6; $s++) {
    Semester::create([
        'course_id'   => $courseB->id,
        'name'        => "Semester {$s}",
        'sequence_no' => $s,
    ]);
}
$batchB = Batch::create([
    'course_id'  => $courseB->id,
    'name'       => "Batch B {$suffix}",
    'status'     => 'ACTIVE',
    'start_date' => now()->toDateString(),
]);

// Setup Fee Heads
$fhTuition = FeeHead::firstOrCreate(['slug' => 'tuition_fee'], ['name' => 'Tuition Fee']);
$fhMid     = FeeHead::firstOrCreate(['slug' => 'mid_term_fee'], ['name' => 'Mid Term Fee']);
$fhFinal   = FeeHead::firstOrCreate(['slug' => 'final_term_fee'], ['name' => 'Final Term Fee']);

// Create Fee Package for Course B
$pkgB = CourseFeePackage::create([
    'course_id'  => $courseB->id,
    'name'       => "Package B {$suffix}",
    'is_default' => true,
    'is_active'  => true,
]);

// Tuition: 500/mo -> 18000 total across 36 months (3000/sem)
CourseFeePackageItem::create([
    'package_id'      => $pkgB->id,
    'fee_head_id'     => $fhTuition->id,
    'label'           => 'Monthly Tuition',
    'quantity'        => 36,
    'amount_per_unit' => 500,
    'total_amount'    => 18000,
]);
// Mid Term: 300 per semester (amount_per_unit = 300, quantity = 1, total_amount = 300)
CourseFeePackageItem::create([
    'package_id'      => $pkgB->id,
    'fee_head_id'     => $fhMid->id,
    'label'           => 'Mid Term Fee',
    'quantity'        => 1,
    'amount_per_unit' => 300,
    'total_amount'    => 300,
]);
// Final Term: 500 per semester (amount_per_unit = 500, quantity = 1, total_amount = 500)
CourseFeePackageItem::create([
    'package_id'      => $pkgB->id,
    'fee_head_id'     => $fhFinal->id,
    'label'           => 'Final Term Fee',
    'quantity'        => 1,
    'amount_per_unit' => 500,
    'total_amount'    => 500,
]);

// Student initially enrolled in Course A
$student = Student::create([
    'user_id'      => $user->id,
    'name'         => $user->name,
    'email'        => "student_{$suffix}@realmail.com",
    'phone'        => '01711' . rand(100000, 999999),
    'student_code' => "2701011" . rand(1000, 9999),
    'status'       => 'ACTIVE',
]);

$enrollmentA = Enrollment::create([
    'student_id'  => $student->id,
    'course_id'   => $courseA->id,
    'batch_id'    => $batchA->id,
    'semester_id' => $semA1->id,
    'enrolled_at' => now()->subMonths(6),
    'status'      => 'ACTIVE',
]);

echo "1. Testing My Course Controller before transfer:\n";
auth()->login($user);
$controller = new MyCourseController();
$viewData = $controller->index()->getData();
assertTask($viewData['enrollments']->count() === 1, "Initial enrollment count is 1");
assertTask($viewData['enrollments']->first()->course_id === $courseA->id, "Active course before transfer is Course A");

echo "\n2. Executing Course Transfer from Course A to Course B:\n";
$transfer = CourseTransfer::create([
    'student_id'         => $student->id,
    'from_course_id'     => $courseA->id,
    'from_batch_id'      => $batchA->id,
    'from_enrollment_id' => $enrollmentA->id,
    'to_course_id'       => $courseB->id,
    'to_batch_id'        => $batchB->id,
    'to_semester_id'     => $courseB->semesters()->first()->id,
    'reason'             => 'Testing course transfer fee and display',
    'status'             => 'PENDING',
]);

CourseTransferService::executeTransfer($transfer);

$enrollmentA->refresh();
assertTask($enrollmentA->status === 'TRANSFERRED', "Old Course A enrollment is updated to TRANSFERRED");

$newEnrollment = Enrollment::where('student_id', $student->id)->where('course_id', $courseB->id)->first();
assertTask($newEnrollment !== null, "New Course B enrollment created");
assertTask($newEnrollment->status === 'ACTIVE', "New Course B enrollment status is ACTIVE");

$student->refresh();
assertTask($student->fee_package_id === $pkgB->id, "Student fee package updated to target course package B");

echo "\n3. Testing My Course Display after transfer:\n";
$viewDataAfter = $controller->index()->getData();
$ordered = $viewDataAfter['enrollments'];
assertTask($ordered->first()->status === 'ACTIVE', "First enrollment in My Course list is ACTIVE");
assertTask($ordered->first()->course_id === $courseB->id, "First enrollment is the NEW Course B!");
assertTask($ordered->last()->status === 'TRANSFERRED', "Transferred course is sorted after active course");

// Verify view renders with Bengali partition headers
$renderedHtml = view('student.my-course.index', $viewDataAfter)->render();
assertTask(str_contains($renderedHtml, 'বর্তমান সক্রিয় কোর্স'), "Rendered view contains active course section title");
assertTask(str_contains($renderedHtml, 'স্থানান্তরিত ও পূর্ববর্তী কোর্সসমূহ'), "Rendered view contains transferred course section title");
assertTask(str_contains($renderedHtml, 'Course Beta ' . $suffix), "Rendered view displays new course title");

echo "\n4. Verifying Auto-Generated Semester Invoice Fee Structure:\n";
$newInvoice = Invoice::where('student_id', $student->id)
    ->where('enrollment_id', $newEnrollment->id)
    ->where('category', 'SEMESTER')
    ->first();

assertTask($newInvoice !== null, "Semester invoice generated for new course enrollment");
// Expected: (500 * 6 = 3000 tuition) + 300 (mid) + 500 (final) = 3800.00
$expectedFee = 3800.00;
assertTask((float)$newInvoice->payable_amount === $expectedFee, "Invoice payable amount ({$newInvoice->payable_amount}) matches expected structure fee (3800.00)");

echo "\n5. Verifying Student Fee Breakdown (Mid Term & Final Term Fee):\n";
$feeService = app(StudentFeeService::class);
$breakdown = $feeService->getStudentFeeBreakdown($student);

$particulars = collect($breakdown['step1Particulars']);
$midItem = $particulars->firstWhere('name', 'Mid Term Fee');
$finalItem = $particulars->firstWhere('name', 'Final Term Fee');

assertTask($midItem !== null, "Mid Term Fee item present in Step 1 particulars");
assertTask((float)$midItem['amount'] === 300.00, "Mid Term Fee amount is exactly ৳300.00 (from amount_per_unit), NOT divided by semesters!");

assertTask($finalItem !== null, "Final Term Fee item present in Step 1 particulars");
assertTask((float)$finalItem['amount'] === 500.00, "Final Term Fee amount is exactly ৳500.00 (from amount_per_unit), NOT divided by semesters!");

$semesterTuitionAndExams = $particulars->filter(function($p) {
    return !str_contains($p['name'], 'Admission Fee') && empty($p['is_added']);
})->sum('amount');
// 6 months * 500 = 3000 + 300 (mid) + 500 (final) = 3800.00
assertTask((float)$semesterTuitionAndExams === $expectedFee, "Step 1 semester tuition & exam particulars sum ({$semesterTuitionAndExams}) matches invoice payable amount ({$expectedFee})!");

echo "\n🎉 ALL TASK 69 CHECKS PASSED ({$passCount}/{$passCount} assertions)!\n";
exit(0);
