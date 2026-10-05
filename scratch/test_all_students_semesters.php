<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\Course;
use App\Http\Controllers\Student\FeeController;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "TEST SUITE: Semester Months, Package Rates & Annual Fees\n";
echo "========================================================\n";

$studentsToTest = [24, 15];

foreach ($studentsToTest as $stId) {
    $student = Student::find($stId);
    echo "\n>>> Testing Student ID: {$stId} (Code: {$student->student_code}) <<<\n";
    auth()->loginUsingId($student->user_id);

    $controller = app()->make(FeeController::class);

    // 1. Check Semester 1 (Admission Fee + Jan-2027 to Jun-2027 + 100 Tk rate)
    $req1 = Request::create('/student/fees', 'GET', ['course_id' => 15, 'semester_id' => 53]);
    app()->instance('request', $req1);
    $view1 = $controller->index($req1);
    $data1 = $view1->getData();
    $parts1 = collect($data1['step1Particulars']);

    $adm1 = $parts1->first(fn($p) => str_contains($p['name'], 'Admission Fee'));
    if (!$adm1) {
        echo "FAILED [Student {$stId}]: Admission fee missing in Semester 1.\n";
        exit(1);
    }
    echo "✓ [Student {$stId}] Semester 1: Admission fee present ({$adm1['name']}, Amount: {$adm1['amount']})\n";

    $t1Months = [];
    foreach ($parts1 as $p) {
        if (preg_match('/Tuition Fee \((.+)\)/', $p['name'], $m)) {
            $t1Months[] = $m[1];
            if ($p['amount'] != 100) {
                echo "FAILED [Student {$stId}]: Tuition fee rate is {$p['amount']} instead of 100.\n";
                exit(1);
            }
        }
    }
    if ($t1Months !== ['Jan-2027', 'Feb-2027', 'Mar-2027', 'Apr-2027', 'May-2027', 'Jun-2027']) {
        echo "FAILED [Student {$stId}]: Semester 1 months mismatch: " . implode(', ', $t1Months) . "\n";
        exit(1);
    }
    echo "✓ [Student {$stId}] Semester 1: Months Jan-2027 to Jun-2027, all @ 100 Tk\n";

    // 2. Check Semester 2 (Jul-2027 to Dec-2027 + 100 Tk rate, no 500 Tk!)
    $req2 = Request::create('/student/fees', 'GET', ['course_id' => 15, 'semester_id' => 54]);
    app()->instance('request', $req2);
    $view2 = $controller->index($req2);
    $data2 = $view2->getData();
    $parts2 = collect($data2['step1Particulars']);

    $t2Months = [];
    foreach ($parts2 as $p) {
        if (preg_match('/Tuition Fee \((.+)\)/', $p['name'], $m)) {
            $t2Months[] = $m[1];
            if ($p['amount'] != 100) {
                echo "FAILED [Student {$stId}]: Semester 2 tuition rate is {$p['amount']} instead of 100.\n";
                exit(1);
            }
        }
    }
    if ($t2Months !== ['Jul-2027', 'Aug-2027', 'Sep-2027', 'Oct-2027', 'Nov-2027', 'Dec-2027']) {
        echo "FAILED [Student {$stId}]: Semester 2 months mismatch: " . implode(', ', $t2Months) . "\n";
        exit(1);
    }
    echo "✓ [Student {$stId}] Semester 2: Months Jul-2027 to Dec-2027, all @ 100 Tk (No 500 Tk)\n";

    // 3. Check Semester 3 (1st Annual Fee + Jan-2028 to Jun-2028)
    $req3 = Request::create('/student/fees', 'GET', ['course_id' => 15, 'semester_id' => 55]);
    app()->instance('request', $req3);
    $view3 = $controller->index($req3);
    $data3 = $view3->getData();
    $parts3 = collect($data3['step1Particulars']);

    $ann1 = $parts3->first(fn($p) => str_contains($p['name'], 'Annual Fee') || str_contains($p['name'], 'বার্ষিক'));
    if (!$ann1 || $ann1['amount'] != 200) {
        echo "FAILED [Student {$stId}]: Semester 3 Annual Fee missing or wrong amount (" . ($ann1['amount'] ?? 'null') . ").\n";
        exit(1);
    }
    echo "✓ [Student {$stId}] Semester 3: 1st Annual Fee present ('{$ann1['name']}', Amount: ৳{$ann1['amount']})\n";

    $t3Months = [];
    foreach ($parts3 as $p) {
        if (preg_match('/Tuition Fee \((.+)\)/', $p['name'], $m)) {
            $t3Months[] = $m[1];
        }
    }
    if ($t3Months !== ['Jan-2028', 'Feb-2028', 'Mar-2028', 'Apr-2028', 'May-2028', 'Jun-2028']) {
        echo "FAILED [Student {$stId}]: Semester 3 months mismatch: " . implode(', ', $t3Months) . "\n";
        exit(1);
    }
    echo "✓ [Student {$stId}] Semester 3: Months Jan-2028 to Jun-2028, all @ 100 Tk\n";

    // 4. Check Semester 5 (2nd Annual Fee + Jan-2029 to Jun-2029)
    $req5 = Request::create('/student/fees', 'GET', ['course_id' => 15, 'semester_id' => 57]);
    app()->instance('request', $req5);
    $view5 = $controller->index($req5);
    $data5 = $view5->getData();
    $parts5 = collect($data5['step1Particulars']);

    $ann2 = $parts5->first(fn($p) => str_contains($p['name'], 'Annual Fee') || str_contains($p['name'], 'বার্ষিক'));
    if (!$ann2 || $ann2['amount'] != 200) {
        echo "FAILED [Student {$stId}]: Semester 5 Annual Fee missing or wrong amount (" . ($ann2['amount'] ?? 'null') . ").\n";
        exit(1);
    }
    echo "✓ [Student {$stId}] Semester 5: 2nd Annual Fee present ('{$ann2['name']}', Amount: ৳{$ann2['amount']})\n";

    $t5Months = [];
    foreach ($parts5 as $p) {
        if (preg_match('/Tuition Fee \((.+)\)/', $p['name'], $m)) {
            $t5Months[] = $m[1];
        }
    }
    if ($t5Months !== ['Jan-2029', 'Feb-2029', 'Mar-2029', 'Apr-2029', 'May-2029', 'Jun-2029']) {
        echo "FAILED [Student {$stId}]: Semester 5 months mismatch: " . implode(', ', $t5Months) . "\n";
        exit(1);
    }
    echo "✓ [Student {$stId}] Semester 5: Months Jan-2029 to Jun-2029, all @ 100 Tk\n";
}

// 5. Test AccountingService createSemesterInvoice includes Annual Fee for Sem 3 & 5
echo "\n>>> Testing AccountingService Semester Invoice with Annual Fee <<<\n";
$st24 = Student::find(24);
$enr24 = $st24->enrollments->first();
$sem3 = \App\Models\Semester::find(55); // Semester 3
$sem5 = \App\Models\Semester::find(57); // Semester 5
$sem2 = \App\Models\Semester::find(54); // Semester 2

// Delete any test invoices for sem 2, 3, 5 if created
\App\Models\Invoice::where('student_id', 24)->whereIn('source_id', [54, 55, 57])->delete();

$invSem2 = \App\Services\AccountingService::createSemesterInvoice($st24, $enr24, $sem2);
echo "Semester 2 Invoice Amount: {$invSem2->amount} (Expected ~627)\n";
if (round($invSem2->amount) != 627) {
    echo "FAILED: Expected 627 for Sem 2 invoice, got {$invSem2->amount}\n";
    exit(1);
}
echo "✓ Semester 2 Invoice: ~627 Tk (recurring semester tuition only)\n";

$invSem3 = \App\Services\AccountingService::createSemesterInvoice($st24, $enr24, $sem3);
echo "Semester 3 Invoice Amount: {$invSem3->amount} (Expected ~827)\n";
if (round($invSem3->amount) != 827) {
    echo "FAILED: Expected 827 for Sem 3 invoice, got {$invSem3->amount}\n";
    exit(1);
}
echo "✓ Semester 3 Invoice: ~827 Tk (includes 1st Annual Fee ৳200)\n";

$invSem5 = \App\Services\AccountingService::createSemesterInvoice($st24, $enr24, $sem5);
echo "Semester 5 Invoice Amount: {$invSem5->amount} (Expected ~827)\n";
if (round($invSem5->amount) != 827) {
    echo "FAILED: Expected 827 for Sem 5 invoice, got {$invSem5->amount}\n";
    exit(1);
}
echo "✓ Semester 5 Invoice: ~827 Tk (includes 2nd Annual Fee ৳200)\n";

// Cleanup test invoices
$invSem2->delete();
$invSem3->delete();
$invSem5->delete();

echo "\nALL SEMESTER TESTS PASSED SUCCESSFULLY WITH EXIT CODE 0!\n";
exit(0);
