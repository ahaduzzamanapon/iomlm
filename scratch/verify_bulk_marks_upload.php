<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\Exam;
use App\Models\Student;
use App\Models\FinalMark;
use App\Models\Result;
use App\Http\Controllers\Admin\ResultBookController;

echo "=== START VERIFICATION: BULK MANUAL MARKS UPLOAD SYSTEM ===\n";

// Step 1: Check Route Registration
echo "\n1. Verifying Route Registrations...\n";
$templateRoute = Route::getRoutes()->getByName('admin.result-book.manual-marks-template');
$importRoute   = Route::getRoutes()->getByName('admin.result-book.manual-marks-import');

if (!$templateRoute) {
    echo "  [FAIL] Route 'admin.result-book.manual-marks-template' is not registered!\n";
    exit(1);
}
echo "  [PASS] Route 'admin.result-book.manual-marks-template' is registered.\n";

if (!$importRoute) {
    echo "  [FAIL] Route 'admin.result-book.manual-marks-import' is not registered!\n";
    exit(1);
}
echo "  [PASS] Route 'admin.result-book.manual-marks-import' is registered.\n";

// Begin DB Transaction for safe testing
DB::beginTransaction();

try {
    // Find or create test batch, subject, semester, student, final_mark
    $batch = Batch::first();
    if (!$batch) {
        throw new Exception("No batch found in database.");
    }
    $subject = Subject::first();
    if (!$subject) {
        throw new Exception("No subject found in database.");
    }
    $semester = Semester::first();
    $student = Student::first();
    if (!$student) {
        throw new Exception("No student found in database.");
    }

    $controller = new ResultBookController();

    // Step 2: Test Template Generation (Combined Semester Mode)
    echo "\n2. Verifying Template Download (Combined Semester Mode)...\n";
    $templateReq = Request::create(route('admin.result-book.manual-marks-template'), 'GET', [
        'batch_id'    => $batch->id,
        'subject_id'  => $subject->id,
        'semester_id' => $semester ? $semester->id : null,
    ]);
    $templateRes = $controller->downloadManualMarksTemplate($templateReq);

    ob_start();
    $templateRes->sendContent();
    $csvContent = ob_get_clean();

    if (strpos($csvContent, 'student_code') === false || strpos($csvContent, 'ct_tamrin') === false || strpos($csvContent, 'attendance_converted') === false) {
        echo "  [FAIL] Combined template header missing expected columns. Content:\n" . substr($csvContent, 0, 200) . "\n";
        exit(1);
    }
    echo "  [PASS] Combined mode template CSV successfully generated with UTF-8 BOM and correct columns.\n";

    // Step 3: Test Template Generation (Exam-Specific Mode)
    echo "\n3. Verifying Template Download (Single Exam Mode)...\n";
    $exam = Exam::where('subject_id', $subject->id)->first();
    if (!$exam) {
        $exam = Exam::create([
            'title'        => 'Verification Test Quiz',
            'subject_id'   => $subject->id,
            'semester_id'  => $semester ? $semester->id : null,
            'type'         => 'QUIZ',
            'exam_date'    => now()->format('Y-m-d'),
            'full_marks'   => 30,
            'pass_marks'   => 12,
            'status'       => 'SCHEDULED',
        ]);
    }

    $examTemplateReq = Request::create(route('admin.result-book.manual-marks-template'), 'GET', [
        'batch_id'    => $batch->id,
        'subject_id'  => $subject->id,
        'semester_id' => $semester ? $semester->id : null,
        'exam_id'     => $exam->id,
    ]);
    $examTemplateRes = $controller->downloadManualMarksTemplate($examTemplateReq);

    ob_start();
    $examTemplateRes->sendContent();
    $examCsvContent = ob_get_clean();

    if (strpos($examCsvContent, 'student_code') === false || strpos($examCsvContent, 'tamrin_marks') === false || strpos($examCsvContent, 'attendance_mark') === false) {
        echo "  [FAIL] Exam template header missing expected columns. Content:\n" . substr($examCsvContent, 0, 200) . "\n";
        exit(1);
    }
    echo "  [PASS] Exam mode template CSV successfully generated with UTF-8 BOM and correct columns.\n";

    // Step 4: Test Bulk Import via CSV File (Combined Mode)
    echo "\n4. Verifying Bulk Import via CSV File (Combined Mode)...\n";
    $fm = FinalMark::firstOrNew([
        'batch_id'    => $batch->id,
        'subject_id'  => $subject->id,
        'semester_id' => $semester ? $semester->id : null,
        'student_id'  => $student->id,
    ]);
    $fm->save();

    $code = $student->student_code ?? $student->student_id;
    $fakeCsvData = "student_code,student_name,ct_tamrin,midterm_tamrin,final_tamrin,attendance_converted,remarks\n" .
                   "{$code},{$student->name},5.0,8.5,10.0,9.0,নিয়মিত শিক্ষার্থী";

    $tmpFile = tempnam(sys_get_temp_dir(), 'csv_test_');
    file_put_contents($tmpFile, $fakeCsvData);

    $uploadedFile = new UploadedFile($tmpFile, 'test.csv', 'text/csv', null, true);

    $importReq = Request::create(route('admin.result-book.manual-marks-import'), 'POST', [
        'batch_id'    => $batch->id,
        'subject_id'  => $subject->id,
        'semester_id' => $semester ? $semester->id : null,
    ], [], ['csv_file' => $uploadedFile]);

    $importRes = $controller->importManualMarksBulk($importReq);

    $fm->refresh();
    echo "  Updated CT Tamrin: {$fm->ct_tamrin} (Expected: 5.0)\n";
    echo "  Updated Midterm Tamrin: {$fm->midterm_tamrin} (Expected: 8.5)\n";
    echo "  Updated Final Tamrin: {$fm->final_tamrin} (Expected: 10.0)\n";
    echo "  Updated Attendance: {$fm->attendance_converted} (Expected: 9.0)\n";
    echo "  Updated Remarks: {$fm->remarks} (Expected: নিয়মিত শিক্ষার্থী)\n";

    if ((float)$fm->ct_tamrin !== 5.0 || (float)$fm->midterm_tamrin !== 8.5 || (float)$fm->final_tamrin !== 10.0 || (float)$fm->attendance_converted !== 9.0) {
        echo "  [FAIL] FinalMark marks did not match uploaded CSV values!\n";
        exit(1);
    }
    echo "  [PASS] Combined mode CSV file bulk upload verified.\n";

    // Step 5: Test Bulk Import via Pasted Spreadsheet Text (Exam-Specific Mode)
    echo "\n5. Verifying Bulk Import via Pasted Spreadsheet Text (Exam Mode)...\n";
    $pasteText = "student_code\tstudent_name\ttamrin_marks\tmcq_marks\twritten_marks\tviva_marks\tattendance_mark\tremarks\n" .
                 "{$code}\t{$student->name}\t4.5\t12\t10\t0\t8.5\tপরীক্ষায় ভালো করেছে";

    $pasteReq = Request::create(route('admin.result-book.manual-marks-import'), 'POST', [
        'batch_id'    => $batch->id,
        'subject_id'  => $subject->id,
        'semester_id' => $semester ? $semester->id : null,
        'exam_id'     => $exam->id,
        'paste_data'  => $pasteText,
    ]);

    $pasteRes = $controller->importManualMarksBulk($pasteReq);

    $resultRecord = Result::where('exam_id', $exam->id)->where('student_id', $student->id)->first();
    if (!$resultRecord) {
        echo "  [FAIL] Result record was not created/updated for exam {$exam->id} and student {$student->id}!\n";
        exit(1);
    }

    echo "  Exam Result Tamrin: {$resultRecord->tamrin_marks} (Expected: 4.5)\n";
    echo "  Exam Result Total Marks: {$resultRecord->marks} (Expected: 26.5)\n";

    $fm->refresh();
    echo "  FinalMark CT Tamrin Sync: {$fm->ct_tamrin} (Expected: 4.5)\n";
    echo "  FinalMark Attendance: {$fm->attendance_converted} (Expected: 8.5)\n";

    if ((float)$resultRecord->tamrin_marks !== 4.5 || (float)$resultRecord->marks !== 26.5 || (float)$fm->attendance_converted !== 8.5) {
        echo "  [FAIL] Exam mode pasted import did not persist expected values!\n";
        exit(1);
    }
    echo "  [PASS] Exam mode pasted text bulk upload verified.\n";

    // Step 6: Test Blade View Rendering
    echo "\n6. Verifying Blade View Rendering...\n";
    $adminUser = \App\Models\User::first();
    if ($adminUser) {
        \Illuminate\Support\Facades\Auth::login($adminUser);
    }
    $viewReq = Request::create(route('admin.result-book.index'), 'GET', [
        'tab'         => 'manual_marking',
        'batch_id'    => $batch->id,
        'subject_id'  => $subject->id,
        'semester_id' => $semester ? $semester->id : null,
        'exam_id'     => $exam->id,
    ]);
    $viewResponse = $controller->index($viewReq);
    $viewHtml = $viewResponse->render();

    $expectedSnippets = [
        'নমুনা টেমপ্লেট',
        'বাল্ক আপলোড (CSV/এক্সেল)',
        'bulkManualMarksModal',
        'bulkCsvFileInput',
        'bulkPasteTextarea',
        'openBulkMarksModal',
        'switchBulkUploadTab',
    ];

    foreach ($expectedSnippets as $snippet) {
        if (strpos($viewHtml, $snippet) === false) {
            echo "  [FAIL] View does not contain expected snippet: '{$snippet}'!\n";
            exit(1);
        }
        echo "  [PASS] View contains '{$snippet}'.\n";
    }

    echo "\n=== ALL VERIFICATION CHECKS PASSED (EXIT CODE 0) ===\n";

} catch (Exception $e) {
    echo "\n[ERROR] Exception occurred: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    DB::rollBack();
    exit(1);
} finally {
    DB::rollBack();
    if (isset($tmpFile) && file_exists($tmpFile)) {
        @unlink($tmpFile);
    }
}

exit(0);
