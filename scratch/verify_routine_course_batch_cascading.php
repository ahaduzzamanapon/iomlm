<?php

require 'e:/My Soft/iom/learning-plus/vendor/autoload.php';
$app = require_once 'e:/My Soft/iom/learning-plus/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\RoutineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

$user = User::first();
if ($user) {
    Auth::login($user);
}

$passed = 0;
$total = 0;

function assertCondition($desc, $cond) {
    global $passed, $total;
    $total++;
    if ($cond) {
        $passed++;
        echo " [PASS] $desc\n";
    } else {
        echo " [FAIL] $desc\n";
    }
}

echo "=== Routine Modal Course -> Batch Cascading Verification ===\n\n";

// 1. Check Blade File Content
$viewPath = 'e:/My Soft/iom/learning-plus/resources/views/admin/routine/index.blade.php';
assertCondition("View file routine/index.blade.php exists", file_exists($viewPath));
$content = file_get_contents($viewPath);

assertCondition("Add modal contains Course dropdown #add_course_id", str_contains($content, 'id="add_course_id"'));
assertCondition("Add modal contains Batch dropdown #add_batch_id", str_contains($content, 'id="add_batch_id"'));
assertCondition("Edit modal contains Course dropdown #edit_course_id", str_contains($content, 'id="edit_course_id"'));
assertCondition("Edit modal contains Batch dropdown #edit_batch_id", str_contains($content, 'id="edit_batch_id"'));
assertCondition("Batch options in Add modal include data-course-id", str_contains($content, 'data-course-id="{{ $b->course_id }}"'));
assertCondition("JS defines onModalCourseChange function", str_contains($content, 'function onModalCourseChange('));
assertCondition("openAddModal handles course filtering", str_contains($content, 'onModalCourseChange(mainCourseFilter, \'add\''));
assertCondition("openEditModal handles course filtering", str_contains($content, 'onModalCourseChange(b.course_id, \'edit\''));

// 2. Test Controller Execution & View Data
$controller = new RoutineController();
$req = new Request();
$response = $controller->index($req);

assertCondition("RoutineController::index returns view", $response instanceof \Illuminate\View\View);
$data = $response->getData();
assertCondition("View data has 'courses'", isset($data['courses']) && $data['courses']->count() > 0);
assertCondition("View data has 'batches'", isset($data['batches']));
assertCondition("View data has 'batchData'", isset($data['batchData']));

// 3. Verify batchData contains course_id
$batchData = $data['batchData'];
$hasCourseId = false;
foreach ($batchData as $bId => $bInfo) {
    if (isset($bInfo['course_id'])) {
        $hasCourseId = true;
        break;
    }
}
assertCondition("batchData array elements contain 'course_id'", $hasCourseId);

// 4. Test View Compilation and Rendering
try {
    $rendered = $response->render();
    assertCondition("Routine index Blade view renders without errors", strlen($rendered) > 5000);
    assertCondition("Rendered HTML contains add_course_id select element", str_contains($rendered, 'id="add_course_id"'));
    assertCondition("Rendered HTML contains add_batch_id select element", str_contains($rendered, 'id="add_batch_id"'));
    assertCondition("Rendered HTML contains edit_course_id select element", str_contains($rendered, 'id="edit_course_id"'));
    assertCondition("Rendered HTML contains edit_batch_id select element", str_contains($rendered, 'id="edit_batch_id"'));
} catch (\Throwable $e) {
    assertCondition("Routine index Blade view renders without errors: " . $e->getMessage(), false);
}

echo "\nSummary: $passed / $total assertions passed.\n";

if ($passed === $total) {
    echo "ALL TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED! (Exit Code 1)\n";
    exit(1);
}
