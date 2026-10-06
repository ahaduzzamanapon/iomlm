<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\StudentFeeService;
use Illuminate\Http\Request;

echo "=== STEP 1: TEST STUDENT FEE SERVICE & BREAKDOWN ===\n";

$student = Student::where('student_code', 'like', '%27010110003%')->first()
    ?? Student::first();

if (!$student) {
    echo "ERROR: No student found.\n";
    exit(1);
}

echo "Testing Student: {$student->name} (Code: {$student->student_code})\n";

$service = app(StudentFeeService::class);
$data = $service->getStudentFeeBreakdown($student);

if (empty($data['step1Particulars'])) {
    echo "ERROR: step1Particulars is empty.\n";
    exit(1);
}

echo "SUCCESS: Found " . count($data['step1Particulars']) . " particulars.\n";
foreach ($data['step1Particulars'] as $p) {
    echo " - #{$p['sl']} {$p['name']} | Due: ৳{$p['due']} | Paid: " . ($p['is_paid'] ? 'YES' : 'NO') . "\n";
}

echo "\n=== STEP 2: TEST RENDERING ADMIN STUDENT LEDGER VIEW ===\n";

// Ensure auth user exists for blade view
$adminUser = App\Models\User::where('role', 'ADMIN')->first() ?? App\Models\User::first();
auth()->login($adminUser);

try {
    $renderedHtml = view('admin.accounts.student_ledger', $data)->render();
    echo "View successfully rendered! Length: " . strlen($renderedHtml) . " bytes\n";

    $requiredSnippets = [
        'মাসভিত্তিক ফি ও বেতন হিসাব',
        'Check Due For:',
        'PARTICULAR NAME',
        'adminStep1Filter_all',
        'adminPartEditModal',
        'adminAddFeeModal',
        'adminCollectParticularPaymentModal',
    ];

    foreach ($requiredSnippets as $snippet) {
        if (!str_contains($renderedHtml, $snippet)) {
            echo "ERROR: Rendered HTML does not contain expected snippet: '{$snippet}'\n";
            exit(1);
        }
    }
    echo "SUCCESS: All required UI elements confirmed present in rendered HTML!\n";
} catch (\Throwable $e) {
    echo "ERROR rendering view: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== STEP 3: TEST PARTICULAR EDIT (updateParticular) ===\n";

$inv = $data['selectedSemesterInvoice'] ?? Invoice::where('student_id', $student->id)->first();
if ($inv) {
    $testPartName = 'Tuition Fee (Jan-2027)';
    $originalDue = (float)($inv->custom_particulars[$testPartName]['due'] ?? 100);

    echo "Updating particular '{$testPartName}' on Invoice {$inv->invoice_no} to ৳125...\n";
    $updateRes = $service->updateParticular($inv->id, $testPartName, 125, 'Test Adjustment', $adminUser->id);

    if (!$updateRes['success'] || $updateRes['new_due'] != 125) {
        echo "ERROR: Failed to update particular.\n";
        exit(1);
    }
    echo "SUCCESS: Particular updated: " . $updateRes['message'] . "\n";

    // Verify it reflects in getStudentFeeBreakdown
    $freshData = $service->getStudentFeeBreakdown($student, $data['selectedSemesterId']);
    $foundUpdated = false;
    foreach ($freshData['step1Particulars'] as $item) {
        if ($item['name'] === $testPartName) {
            $foundUpdated = true;
            if ($item['due'] != 125) {
                echo "ERROR: Expected due 125, got {$item['due']}\n";
                exit(1);
            }
            echo "CONFIRMED in breakdown: {$item['name']} due is ৳{$item['due']}!\n";
        }
    }

    if (!$foundUpdated) {
        echo "ERROR: Updated particular not found in fresh breakdown.\n";
        exit(1);
    }

    // Revert back
    $service->updateParticular($inv->id, $testPartName, $originalDue, 'Reverted Test', $adminUser->id);
    echo "Reverted particular back to ৳{$originalDue}.\n";
}

echo "\n=== STEP 4: TEST PARTICULAR PAYMENT COLLECTION (collectParticularPayment) ===\n";

if ($inv && $inv->due_amount > 0) {
    $testPartName = 'Test Monthly Fee';
    // First add a test particular
    $addRes = $service->storeParticular($inv->id, $student->id, $data['selectedSemesterId'], $testPartName, 50, 'Test Particular', $adminUser->id);
    if (!$addRes['success']) {
        echo "ERROR adding test particular: " . json_encode($addRes) . "\n";
        exit(1);
    }
    echo "Added temporary particular: {$testPartName} (৳50)\n";

    // Collect payment for it
    $collectRes = $service->collectParticularPayment(
        $inv->fresh(),
        [$testPartName],
        50,
        'CASH',
        'TRX-TEST-001',
        null,
        'Test Collection Remarks',
        $adminUser->id
    );

    if (!$collectRes['success']) {
        echo "ERROR collecting payment: " . json_encode($collectRes) . "\n";
        exit(1);
    }
    echo "SUCCESS: Payment collected: {$collectRes['message']}\n";
    echo "Payment No: {$collectRes['payment']->payment_no}\n";

    // Check that breakdown now marks it as PAID
    $freshData = $service->getStudentFeeBreakdown($student, $data['selectedSemesterId']);
    $foundPaid = false;
    foreach ($freshData['step1Particulars'] as $item) {
        if ($item['name'] === $testPartName) {
            $foundPaid = true;
            if (!$item['is_paid'] || $item['due'] > 0) {
                echo "ERROR: Expected is_paid=true and due=0, got due={$item['due']}\n";
                exit(1);
            }
            echo "CONFIRMED in breakdown: {$item['name']} is marked PAID (due: ৳{$item['due']})!\n";
        }
    }

    if (!$foundPaid) {
        echo "ERROR: Paid test particular not found in breakdown.\n";
        exit(1);
    }

    // Clean up test data
    $collectRes['payment']->delete();
    $service->deleteParticular($inv->id, $testPartName, $adminUser->id);
    echo "Cleaned up temporary test payment and particular.\n";
}

echo "\n============================================\n";
echo "ALL TESTS PASSED WITH EXIT CODE 0!\n";
echo "============================================\n";
exit(0);
