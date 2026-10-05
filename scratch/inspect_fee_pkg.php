<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pkg = \App\Models\CourseFeePackage::where('name', 'like', '%Poor fund%')->first();
if ($pkg) {
    echo "Found package: ID {$pkg->id}, Name: {$pkg->name}, Course ID: {$pkg->course_id}, Total: {$pkg->total_fee}\n";
    foreach ($pkg->items as $it) {
        echo " - Item: {$it->label}, FeeHead: {$it->feeHead?->name} ({$it->feeHead?->slug}), mode: {$it->amount_mode}, unit: {$it->amount_per_unit}, total: {$it->total_amount}, months: {$it->months_count}\n";
    }
}

$student = \App\Models\Student::find(24);
echo "\nStudent attributes:\n";
echo "fee_package_id: " . ($student->fee_package_id ?? 'N/A') . "\n";

$appRecord = \App\Models\AdmissionApplication::where('student_id', 24)->orWhere('email', $student->user?->email)->first();
if ($appRecord) {
    echo "AdmissionApplication: ID {$appRecord->id}, fee_package_id: {$appRecord->fee_package_id}, selected_fee_package_id: {$appRecord->selected_fee_package_id}\n";
}

$enrollment = $student->enrollments()->latest()->first();
echo "Enrollment: ID {$enrollment->id}, fee_package_id: " . ($enrollment->fee_package_id ?? 'N/A') . "\n";

$inv64 = \App\Models\Invoice::find(64);
echo "Invoice 64 attributes:\n" . json_encode($inv64->toArray(), JSON_PRETTY_PRINT) . "\n";
