<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== VERIFYING BULK UPLOAD BUTTON FIXES ===\n";

// 1. Check layout global openModal & closeModal
$layoutHtml = file_get_contents(__DIR__ . '/../resources/views/admin/layouts/app.blade.php');
if (strpos($layoutHtml, 'window.openModal = function') === false || strpos($layoutHtml, 'window.closeModal = function') === false) {
    echo "  [FAIL] window.openModal or window.closeModal is missing from admin layout!\n";
    exit(1);
}
echo "  [PASS] window.openModal and window.closeModal are defined globally in admin layout.\n";

// 2. Check Question Bank Bulk Upload Modal and Button
$questionsView = file_get_contents(__DIR__ . '/../resources/views/admin/questions/index.blade.php');
if (strpos($questionsView, "openModal('bulkUploadModal')") === false) {
    echo "  [FAIL] Question Bank does not have openModal('bulkUploadModal') trigger!\n";
    exit(1);
}
if (strpos($questionsView, 'id="bulkUploadModal"') === false) {
    echo "  [FAIL] bulkUploadModal is missing in questions index!\n";
    exit(1);
}
if (strpos($questionsView, 'function openModal(') === false || strpos($questionsView, 'function closeModal(') === false) {
    echo "  [FAIL] Local openModal/closeModal fallback is missing in questions index!\n";
    exit(1);
}
echo "  [PASS] Question Bank Bulk CSV Upload button and modal are correctly linked and supported.\n";

// 3. Check Result Book Bulk Upload Modal and Button
$resultBookView = file_get_contents(__DIR__ . '/../resources/views/admin/result-book/index.blade.php');
if (strpos($resultBookView, 'openBulkMarksModal()') === false) {
    echo "  [FAIL] Result Book does not have openBulkMarksModal() trigger!\n";
    exit(1);
}
if (strpos($resultBookView, 'id="bulkManualMarksModal"') === false) {
    echo "  [FAIL] bulkManualMarksModal is missing in result-book index!\n";
    exit(1);
}
if (strpos($resultBookView, 'window.openBulkMarksModal = function') === false) {
    echo "  [FAIL] window.openBulkMarksModal definition missing in result-book index!\n";
    exit(1);
}
echo "  [PASS] Result Book Bulk Marks Upload button and modal are correctly linked and supported.\n";

echo "\n>>> ALL BULK UPLOAD BUTTON TESTS PASSED (Exit Code 0) <<<\n";
exit(0);
