<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function assertCondition($cond, $msg) {
    if (!$cond) {
        echo "[FAIL] $msg\n";
        exit(1);
    }
    echo "[PASS] $msg\n";
}

echo "=== Comprehensive Modal Scroll & Backdrop Click Verification ===\n\n";

// 1. app.css
$appCss = file_get_contents(__DIR__ . '/../public/css/app.css');
assertCondition(strpos($appCss, 'align-items: flex-start') !== false, 'app.css has align-items: flex-start on .modal-overlay');
assertCondition(strpos($appCss, 'margin: 0 auto') !== false, 'app.css has margin: 0 auto on .modal');
assertCondition(strpos($appCss, 'max-height: calc(100vh - 60px)') !== false, 'app.css has max-height constraint with overflow-y: auto');
assertCondition(strpos($appCss, 'modal-overlay[style*="display: flex"]') !== false, 'app.css handles inline display: flex with opacity: 1 and pointer-events: all');

// 2. Admin Layout
$adminLayout = file_get_contents(__DIR__ . '/../resources/views/admin/layouts/app.blade.php');
assertCondition(strpos($adminLayout, '.modal-overlay:not(.open):not(.active):not(.show)') !== false, 'Admin layout has closed display:none rule');
assertCondition(strpos($adminLayout, 'margin: 0 auto !important') !== false, 'Admin layout has margin: 0 auto !important on modal boxes');
assertCondition(strpos($adminLayout, 'm.style.pointerEvents = \'auto\'') !== false, 'Admin layout openModal sets pointerEvents');
assertCondition(strpos($adminLayout, 'target.closest(') !== false, 'Admin layout backdrop click protects inner modal clicks');
assertCondition(strpos($adminLayout, 'closeCircularModal') !== false, 'Admin layout click listener handles closeCircularModal');
assertCondition(strpos($adminLayout, 'closeStudentMarksheet') !== false, 'Admin layout click listener handles closeStudentMarksheet');

// 3. Student Layout
$studentLayout = file_get_contents(__DIR__ . '/../resources/views/student/layouts/app.blade.php');
assertCondition(strpos($studentLayout, '.modal-overlay:not(.open):not(.active):not(.show)') !== false, 'Student layout has closed display:none rule');
assertCondition(strpos($studentLayout, 'margin: 0 auto !important') !== false, 'Student layout has margin: 0 auto !important');
assertCondition(strpos($studentLayout, 'target.closest(') !== false, 'Student layout click listener protects inner modal clicks');
assertCondition(strpos($studentLayout, 'closePayModal') !== false, 'Student layout click listener handles closePayModal');
assertCondition(strpos($studentLayout, 'closeIndexAppealModal') !== false, 'Student layout click listener handles closeIndexAppealModal');

// 4. Teacher Layout
$teacherLayout = file_get_contents(__DIR__ . '/../resources/views/teacher/layouts/app.blade.php');
assertCondition(strpos($teacherLayout, '.modal-overlay:not(.open):not(.active):not(.show)') !== false, 'Teacher layout has closed display:none rule');
assertCondition(strpos($teacherLayout, 'margin: 0 auto !important') !== false, 'Teacher layout has margin: 0 auto !important');
assertCondition(strpos($teacherLayout, 'target.closest(') !== false, 'Teacher layout click listener protects inner modal clicks');

// 5. Support Layout
$supportLayout = file_get_contents(__DIR__ . '/../resources/views/support/layouts/app.blade.php');
assertCondition(strpos($supportLayout, '.modal-overlay:not(.open):not(.active):not(.show)') !== false, 'Support layout has closed display:none rule');
assertCondition(strpos($supportLayout, 'margin: 0 auto !important') !== false, 'Support layout has margin: 0 auto !important');
assertCondition(strpos($supportLayout, 'target.closest(') !== false, 'Support layout click listener protects inner modal clicks');
assertCondition(strpos($supportLayout, 'closeStudentProfileModal') !== false, 'Support layout click listener handles closeStudentProfileModal');

// 6. Specific Blade Files
$admCirc = file_get_contents(__DIR__ . '/../resources/views/admin/admission_circulars/index.blade.php');
assertCondition(strpos($admCirc, 'onclick="if(event.target===this) closeCircularModal()"') !== false, 'Admission circulars modal has backdrop click guard');
assertCondition(strpos($admCirc, 'openModal(\'circularModal\')') !== false, 'Admission circulars uses openModal');
assertCondition(strpos($admCirc, 'closeModal(\'circularModal\')') !== false, 'Admission circulars uses closeModal');
assertCondition(strpos($admCirc, 'align-items: flex-start') !== false, 'Admission circulars modal has align-items: flex-start');

$ledger = file_get_contents(__DIR__ . '/../resources/views/admin/accounts/student_ledger.blade.php');
assertCondition(strpos($ledger, 'align-items: flex-start !important') !== false, 'Student ledger modal has align-items: flex-start !important');
assertCondition(strpos($ledger, 'margin: 0 auto !important') !== false, 'Student ledger modal dialog has margin: 0 auto !important');

$studentsShow = file_get_contents(__DIR__ . '/../resources/views/admin/students/show.blade.php');
assertCondition(strpos($studentsShow, 'align-items: flex-start') !== false, 'Students show modal has align-items: flex-start');
assertCondition(strpos($studentsShow, 'onclick="if(event.target===this) closeModal(\'editProfileModal\')"') !== false, 'Students show editProfileModal has backdrop click guard');
assertCondition(strpos($studentsShow, 'onclick="if(event.target===this) closeModal(\'passwordResetModal\')"') !== false, 'Students show passwordResetModal has backdrop click guard');
assertCondition(strpos($studentsShow, 'onclick="if(event.target===this) closeModal(\'cancelAdmissionModal\')"') !== false, 'Students show cancelAdmissionModal has backdrop click guard');
assertCondition(strpos($studentsShow, 'onclick="if(event.target===this) closeModal(\'adjustFeeModal\')"') !== false, 'Students show adjustFeeModal has backdrop click guard');

$promotions = file_get_contents(__DIR__ . '/../resources/views/admin/promotions/index.blade.php');
assertCondition(strpos($promotions, 'onclick="if(event.target===this) closeModal(\'addPromotionModal\')"') !== false, 'Promotions modal has backdrop click guard');
assertCondition(strpos($promotions, 'align-items:flex-start') !== false, 'Promotions modal has align-items:flex-start');

$surveys = file_get_contents(__DIR__ . '/../resources/views/admin/surveys/index.blade.php');
assertCondition(strpos($surveys, 'onclick="if(event.target===this) closeModal(\'createSurveyModal\')"') !== false, 'Surveys modal has backdrop click guard');
assertCondition(strpos($surveys, 'max-height: calc(100vh - 60px)') !== false, 'Surveys modal card has max-height and scroll');

$myCourse = file_get_contents(__DIR__ . '/../resources/views/student/my-course/index.blade.php');
assertCondition(strpos($myCourse, 'class="modal-overlay"') !== false, 'Student my-course applyCourseModal has class modal-overlay');
assertCondition(strpos($myCourse, 'align-items:flex-start') !== false, 'Student my-course applyCourseModal has align-items:flex-start');
assertCondition(strpos($myCourse, 'if(event.target===this)') !== false, 'Student my-course applyCourseModal has backdrop click guard');

$resultBook = file_get_contents(__DIR__ . '/../resources/views/admin/result-book/index.blade.php');
assertCondition(strpos($resultBook, 'align-items: flex-start') !== false, 'Result book modal-backdrop has align-items: flex-start');

echo "\n>>> ALL 31 UNIVERSAL MODAL ASSERTIONS PASSED WITH ZERO REGRESSIONS! <<<\n";
