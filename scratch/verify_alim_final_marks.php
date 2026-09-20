<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\FinalMark;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\FinalMarkController;
use App\Http\Controllers\Admin\PromotionController;

echo "=== STARTING VERIFICATION: ALIM 2717 FINAL MARKS & PROMOTION ===\n\n";

// 1. Authenticate Admin
$admin = User::where('role', 'admin')->first() ?? User::first();
auth()->login($admin);
echo "Step 1: Authenticated as {$admin->name} ({$admin->role})\n";

// 2. Check Batch 17 (Alim 2717)
echo "\nStep 2: Checking Alim 2717 batch & enrollments...\n";
$batch17 = Batch::where('name', 'like', '%Alim 2717%')->orWhere('id', 17)->first();
if (!$batch17) {
    echo "❌ FAIL: Alim 2717 batch not found.\n";
    exit(1);
}
echo "  Batch ID: {$batch17->id} | Name: {$batch17->name} | Status: {$batch17->status}\n";
if ($batch17->status !== 'ACTIVE') {
    echo "❌ FAIL: Expected Alim 2717 status to be ACTIVE, got {$batch17->status}\n";
    exit(1);
}

$enrollmentCount = Enrollment::where('batch_id', $batch17->id)->where('status', 'ACTIVE')->count();
echo "  Active Enrollments: {$enrollmentCount}\n";
if ($enrollmentCount < 4) {
    echo "❌ FAIL: Expected at least 4 active enrollments in Alim 2717, got {$enrollmentCount}\n";
    exit(1);
}
echo "  ✔ Alim 2717 has active enrollments.\n";

// 3. Check FinalMarks for Subject 36 (Adabu Talibul Ilm)
echo "\nStep 3: Checking FinalMark records for Adabu Talibul Ilm (Subject 36)...\n";
$atiSubject = Subject::find(36) ?? Subject::where('code', 'ATI 101')->first();
if (!$atiSubject) {
    echo "❌ FAIL: Subject Adabu Talibul Ilm not found.\n";
    exit(1);
}
echo "  Subject ID: {$atiSubject->id} | Name: {$atiSubject->name} ({$atiSubject->code})\n";

$finalMarks = FinalMark::with('student')
    ->where('batch_id', $batch17->id)
    ->where('subject_id', $atiSubject->id)
    ->get();

echo "  Final Marks count: " . $finalMarks->count() . "\n";
if ($finalMarks->isEmpty()) {
    echo "❌ FAIL: No final marks found for Alim 2717 and Subject 36.\n";
    exit(1);
}

foreach ($finalMarks as $fm) {
    echo "    - Student: {$fm->student->name} | Total: {$fm->total_mark} | Grade: {$fm->grade} | GPA: {$fm->gpa} | Status: {$fm->status}\n";
}
echo "  ✔ Final marks exist and populated.\n";

// 4. Test FinalMarkController::index for Alim 2717
echo "\nStep 4: Testing FinalMarkController::index view rendering...\n";
$finalMarkController = new FinalMarkController();
$requestIndex = Request::create('/admin/final-marks', 'GET', [
    'batch_id'    => $batch17->id,
    'semester_id' => 53,
    'subject_id'  => $atiSubject->id,
]);
$responseIndex = $finalMarkController->index($requestIndex);
$htmlIndex = $responseIndex->render();

if (strpos($htmlIndex, 'Alim 2717') === false) {
    echo "❌ FAIL: 'Alim 2717' not found in rendered HTML.\n";
    exit(1);
}
if (strpos($htmlIndex, 'Adabu Talibul Ilm') === false) {
    echo "❌ FAIL: 'Adabu Talibul Ilm' not found in rendered HTML.\n";
    exit(1);
}
if (strpos($htmlIndex, 'মোট শিক্ষার্থী: 0 জন') !== false) {
    echo "❌ FAIL: Student count shows 0 in HTML.\n";
    exit(1);
}
echo "  ✔ Final marks index view rendered successfully with student records.\n";

// 5. Test FinalMarkController::generate for Alim 2717
echo "\nStep 5: Testing FinalMarkController::generate (Generate / Regenerate Marks)...\n";
$requestGen = Request::create('/admin/final-marks/generate', 'POST', [
    'batch_id'    => $batch17->id,
    'semester_id' => 53,
    'subject_id'  => $atiSubject->id,
]);
$responseGen = $finalMarkController->generate($requestGen);

if ($responseGen->getStatusCode() !== 302) {
    echo "❌ FAIL: Expected 302 redirect from generate(), got {$responseGen->getStatusCode()}\n";
    exit(1);
}
$sessionErrors = session('error');
if ($sessionErrors) {
    echo "❌ FAIL: generate() returned error: {$sessionErrors}\n";
    exit(1);
}
$sessionSuccess = session('success');
echo "  Success message: {$sessionSuccess}\n";
echo "  ✔ generate() successfully ran without error.\n";

// 6. Test PromotionController::index for Alim 2717
echo "\nStep 6: Testing PromotionController::index for Alim 2717...\n";
$promoController = new PromotionController();
$requestPromo = Request::create('/admin/promotions', 'GET', [
    'batch_id'    => $batch17->id,
    'semester_id' => 53,
]);
$responsePromo = $promoController->index($requestPromo);
$promoData = $responsePromo->getData();

echo "  Evaluated students: " . $promoData['studentEvaluations']->count() . "\n";
if ($promoData['studentEvaluations']->isEmpty()) {
    echo "❌ FAIL: No student evaluations found for Alim 2717 in Promotion engine.\n";
    exit(1);
}
foreach ($promoData['studentEvaluations'] as $st) {
    echo "    - {$st['student']->name}: {$st['status_text']} | Standing: {$st['standing']}\n";
}
echo "  ✔ PromotionController successfully evaluated all Alim 2717 students.\n";

echo "\n=======================================================\n";
echo "🎉 ALL VERIFICATION TESTS PASSED SUCCESSFULLY! (EXIT 0)\n";
echo "=======================================================\n";
exit(0);
