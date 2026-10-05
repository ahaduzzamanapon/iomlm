<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::find(24);
$admForm = \App\Models\AdmissionForm::where('student_id', 24)->first();
echo "AdmissionForm ID: {$admForm?->id}\n";
echo "waiver_code: {$admForm?->waiver_code}\n";
echo "discount_type: {$admForm?->discount_type}\n";
echo "discount_amount: {$admForm?->discount_amount}\n";
echo "discount_percent: {$admForm?->discount_percent}\n";

$inv63 = \App\Models\Invoice::find(63);
echo "\nInvoice 63:\n";
echo json_encode($inv63->toArray(), JSON_PRETTY_PRINT) . "\n";

if ($admForm?->waiver_code) {
    $w = \App\Models\WaiverApplication::where('application_no', $admForm->waiver_code)->first();
    echo "\nWaiver Application:\n";
    echo json_encode($w?->toArray(), JSON_PRETTY_PRINT) . "\n";
}
