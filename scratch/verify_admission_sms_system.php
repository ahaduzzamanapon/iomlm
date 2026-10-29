<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailTemplate;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Student;
use App\Models\AdmissionForm;

echo "=== VERIFICATION: ADMISSION SMS TEMPLATE SYSTEM (TASK 21) ===\n\n";

$passCount = 0;
function assertTest($condition, $message) {
    global $passCount;
    if ($condition) {
        echo "  [PASS] {$message}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$message}\n";
        exit(1);
    }
}

// 1. Verify SMS Template in EmailTemplate Model & Seeder
echo "1. Checking EmailTemplate Model & SMS Seeding...\n";
EmailTemplate::seedDefaultTemplates();

$smsTpl = EmailTemplate::where('type', 'SMS')
    ->where('category', 'ADMISSION')
    ->whereNull('course_id')
    ->whereNull('batch_id')
    ->first();

assertTest($smsTpl !== null, "Default admission SMS template exists in email_templates table");
assertTest($smsTpl->type === 'SMS', "Template type is 'SMS'");
assertTest(str_contains($smsTpl->type_label, 'এসএমএস'), "Template type_label contains 'এসএমএস'");

$expectedContent = "Salam {name}, IOM admission confirmed. ID: {student_id} Pass: {password} Login: {login_url}. Check email for details.\n-IOM";
assertTest(
    trim(str_replace("\r\n", "\n", $smsTpl->content)) === trim(str_replace("\r\n", "\n", $expectedContent)),
    "Template content matches user-specified SMS text exactly"
);

// 2. Checking Template Resolution
echo "\n2. Checking SMS Template Resolution...\n";
$resolvedSms = EmailTemplate::resolveAdmissionSmsTemplate(null, null);
assertTest($resolvedSms !== null && $resolvedSms->type === 'SMS', "resolveAdmissionSmsTemplate returns active SMS template");

$resolvedEmail = EmailTemplate::resolveAdmissionTemplate(null, null);
assertTest($resolvedEmail !== null && ($resolvedEmail->type === 'EMAIL' || $resolvedEmail->type === null), "resolveAdmissionTemplate only returns EMAIL template");

// 3. Checking Batch::getEffectiveSmsTemplate()
echo "\n3. Checking Batch::getEffectiveSmsTemplate()...\n";
$dummyBatch = new Batch();
$effectiveSms = $dummyBatch->getEffectiveSmsTemplate();
assertTest(!preg_match('/[\x{0980}-\x{09FF}]/u', $effectiveSms), "Batch getEffectiveSmsTemplate has NO legacy Bangla characters");
assertTest(str_contains($effectiveSms, 'Salam {name}, IOM admission confirmed.'), "Batch getEffectiveSmsTemplate uses new English SMS default");

// 4. Checking Variable Substitution
echo "\n4. Checking Placeholder Substitution...\n";
$replaceVars = [
    '{name}'       => 'মুহাম্মদ আব্দুল্লাহ',
    '{student_id}' => '26010110001',
    '{roll}'       => '26010110001',
    '{password}'   => '987654',
    '{login_url}'  => url('/login'),
];
$compiledSms = str_replace(array_keys($replaceVars), array_values($replaceVars), $resolvedSms->content);
assertTest(!str_contains($compiledSms, '{name}'), "Placeholder {name} is replaced");
assertTest(!str_contains($compiledSms, '{student_id}'), "Placeholder {student_id} is replaced");
assertTest(!str_contains($compiledSms, '{password}'), "Placeholder {password} is replaced");
assertTest(!str_contains($compiledSms, '{login_url}'), "Placeholder {login_url} is replaced");
assertTest(str_contains($compiledSms, 'মুহাম্মদ আব্দুল্লাহ'), "Student name is present in compiled SMS");
assertTest(str_contains($compiledSms, '26010110001'), "Student ID is present in compiled SMS");
assertTest(str_contains($compiledSms, '987654'), "Password is present in compiled SMS");

// 5. Checking Auto-Approval SMS in PaymentGatewayService
echo "\n5. Checking PaymentGatewayService Auto-Approval SMS...\n";
$pgFile = file_get_contents(__DIR__ . '/../app/Services/PaymentGatewayService.php');
assertTest(
    !str_contains($pgFile, 'আলহামদুলিল্লাহ! IOM-এ আপনার ভর্তি নিশ্চিত হয়েছে'),
    "Legacy Bangla admission SMS completely removed from PaymentGatewayService"
);
assertTest(
    str_contains($pgFile, 'resolveAdmissionSmsTemplate'),
    "PaymentGatewayService calls resolveAdmissionSmsTemplate for dynamic SMS template"
);

// 6. Checking Blade Views
echo "\n6. Checking Admission Modal Blade View (show.blade.php)...\n";
$showBlade = file_get_contents(__DIR__ . '/../resources/views/admin/admissions/show.blade.php');
assertTest(str_contains($showBlade, 'approve_sms_template_id'), "show.blade.php has #approve_sms_template_id dropdown");
assertTest(str_contains($showBlade, 'onSmsTemplateSelect'), "show.blade.php has onSmsTemplateSelect() handler");
assertTest(str_contains($showBlade, 'ADMISSION_SMS_TEMPLATES'), "show.blade.php passes ADMISSION_SMS_TEMPLATES to JS");
assertTest(str_contains($showBlade, 'DEFAULT_SMS_TEMPLATE_CONTENT'), "show.blade.php passes DEFAULT_SMS_TEMPLATE_CONTENT to JS");
assertTest(str_contains($showBlade, 'updateSmsCharCounter'), "show.blade.php updates SMS character counter");

echo "\n7. Checking Email Template Views (index, create, edit)...\n";
$indexBlade = file_get_contents(__DIR__ . '/../resources/views/admin/email_templates/index.blade.php');
assertTest(str_contains($indexBlade, "name=\"type\"") || str_contains($indexBlade, "type="), "index.blade.php has type filter");
assertTest(str_contains($indexBlade, "এসএমএস"), "index.blade.php shows SMS label");

$createBlade = file_get_contents(__DIR__ . '/../resources/views/admin/email_templates/create.blade.php');
assertTest(str_contains($createBlade, "type_sms"), "create.blade.php has SMS type radio");
assertTest(str_contains($createBlade, "updateSmsCharCounter"), "create.blade.php has SMS character counter");

$editBlade = file_get_contents(__DIR__ . '/../resources/views/admin/email_templates/edit.blade.php');
assertTest(str_contains($editBlade, "type_sms"), "edit.blade.php has SMS type radio");
assertTest(str_contains($editBlade, "updateSmsCharCounter"), "edit.blade.php has SMS character counter");

echo "\n8. Checking AdmissionController approve() handling...\n";
$ctrlFile = file_get_contents(__DIR__ . '/../app/Http/Controllers/Admin/AdmissionController.php');
assertTest(str_contains($ctrlFile, "'sms_template_id'"), "AdmissionController validates sms_template_id");
assertTest(str_contains($ctrlFile, "resolveAdmissionSmsTemplate"), "AdmissionController resolves SMS template");

echo "\n=======================================================\n";
echo "SUCCESS: ALL {$passCount} ADMISSION SMS TEMPLATE TESTS PASSED!\n";
echo "=======================================================\n";
exit(0);
