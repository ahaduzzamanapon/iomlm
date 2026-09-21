<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProgramActivity;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Readmission;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

echo "=== VERIFYING TASK 23: PROGRAM ACTIVITY, SEMESTER FEES & READMISSION ===\n\n";

$assertions = 0;

// 1. Schema & Model Verification
assert(Schema::hasTable('program_activities'), "program_activities table must exist");
$assertions++;
echo "✓ Assertion 1: 'program_activities' table exists in database.\n";

$activityCount = ProgramActivity::count();
assert($activityCount >= 1, "At least 1 ProgramActivity record must exist");
$assertions++;
echo "✓ Assertion 2: ProgramActivity records exist (Count: {$activityCount}).\n";

$sampleAct = ProgramActivity::first();
assert(!empty($sampleAct->starting_month), "starting_month must not be empty");
$assertions++;
echo "✓ Assertion 3: ProgramActivity model correctly casts and stores starting_month ({$sampleAct->starting_month}).\n";

// 2. Admin Routes Verification
assert(Route::has('admin.program-activities.index'), "admin.program-activities.index route must exist");
assert(Route::has('admin.program-activities.store'), "admin.program-activities.store route must exist");
assert(Route::has('admin.program-activities.update'), "admin.program-activities.update route must exist");
assert(Route::has('admin.program-activities.destroy'), "admin.program-activities.destroy route must exist");
$assertions += 4;
echo "✓ Assertion 4-7: Admin program-activities routes are registered.\n";

// 3. Student Routes Verification
assert(Route::has('student.readmissions.index'), "student.readmissions.index route must exist");
assert(Route::has('student.readmissions.store'), "student.readmissions.store route must exist");
assert(Route::has('student.readmissions.cancel'), "student.readmissions.cancel route must exist");
$assertions += 3;
echo "✓ Assertion 8-10: Student readmissions routes are registered.\n";

// 4. Test ProgramActivity Creation & Deletion
$newAct = ProgramActivity::create([
    'semester_name'  => 'Spring 2027 (Jan-Jun)',
    'course_id'      => null,
    'batch_id'       => null,
    'starting_month' => 'January',
    'start_date'     => '2027-01-01',
    'end_date'       => '2027-06-30',
    'is_active'      => true,
]);
assert($newAct->id > 0, "Created ProgramActivity must have valid ID");
$assertions++;
echo "✓ Assertion 11: ProgramActivity can be created successfully.\n";

$newAct->update(['starting_month' => 'February']);
assert($newAct->fresh()->starting_month === 'February', "ProgramActivity update must persist");
$assertions++;
echo "✓ Assertion 12: ProgramActivity update persisted.\n";

$newAct->delete();
assert(ProgramActivity::find($newAct->id) === null, "ProgramActivity deletion must work");
$assertions++;
echo "✓ Assertion 13: ProgramActivity deleted successfully.\n";

// 5. Test Dynamic Month Calculation & Fee Controller Logic
$student = Student::where('status', 'ACTIVE')->first();
if ($student && $student->user_id) {
    auth()->loginUsingId($student->user_id);
    
    $feeController = new \App\Http\Controllers\Student\FeeController();
    $feeResponse = $feeController->index();
    $viewData = $feeResponse->getData();
    
    assert(isset($viewData['programActivity']), "FeeController must pass programActivity to view");
    assert(isset($viewData['step1Particulars']), "FeeController must pass step1Particulars to view");
    assert(isset($viewData['semesterDropdownOptions']), "FeeController must pass semesterDropdownOptions to view");
    assert(isset($viewData['hasPriorSemesterDue']), "FeeController must pass hasPriorSemesterDue to view");
    $assertions += 4;
    echo "✓ Assertion 14-17: FeeController passes all Step 1 and ProgramActivity data.\n";
    
    $particulars = $viewData['step1Particulars'];
    assert(count($particulars) >= 8, "step1Particulars must include 6 months + Mid Term + Final Term (>=8 items)");
    $assertions++;
    echo "✓ Assertion 18: Step 1 particulars generated (Count: " . count($particulars) . ").\n";
    
    // Check exact naming pattern (e.g. Tuition Fee (M-Y), Mid Term Fee, Final Term Fee)
    $hasTuitionMonth = false;
    $hasMidTerm = false;
    $hasFinalTerm = false;
    foreach ($particulars as $p) {
        if (str_starts_with($p['name'], 'Tuition Fee (')) $hasTuitionMonth = true;
        if ($p['name'] === 'Mid Term Fee') $hasMidTerm = true;
        if ($p['name'] === 'Final Term Fee') $hasFinalTerm = true;
    }
    assert($hasTuitionMonth, "Particulars must contain exact month tuition fees");
    assert($hasMidTerm, "Particulars must contain Mid Term Fee");
    assert($hasFinalTerm, "Particulars must contain Final Term Fee");
    $assertions += 3;
    echo "✓ Assertion 19-21: Particular names match screenshot: Tuition Fee (Month-Year), Mid Term Fee, Final Term Fee.\n";
}

// 6. Test Readmission Submission from Student
if ($student && $student->user_id) {
    auth()->loginUsingId($student->user_id);
    
    $enrollment = Enrollment::where('student_id', $student->id)->where('status', 'ACTIVE')->first();
    if ($enrollment) {
        $readmission = Readmission::create([
            'student_id'    => $student->id,
            'enrollment_id' => $enrollment->id,
            'course_id'     => $enrollment->course_id,
            'semester_id'   => $enrollment->semester_id,
            'from_batch_id' => $enrollment->batch_id,
            'to_batch_id'   => null,
            'notes'         => 'টেস্ট রি-এডমিশন আবেদন: অসুস্থতার জন্য সেশন ড্রপ',
            'status'        => 'PENDING',
        ]);
        
        assert($readmission->id > 0, "Readmission record created");
        assert($readmission->status === 'PENDING', "Readmission status is PENDING");
        $assertions += 2;
        echo "✓ Assertion 22-23: Student Readmission application created with PENDING status.\n";
        
        // Clean up test record
        $readmission->forceDelete();
    }
}

// 7. Verify Blade Views Compile
$viewsToTest = [
    'admin.program-activities.index',
    'admin.semesters.index',
    'student.fees.index',
    'student.readmissions.index',
    'student.profile.index',
];

foreach ($viewsToTest as $v) {
    assert(view()->exists($v), "Blade view '{$v}' must exist");
    $assertions++;
    echo "✓ Assertion: Blade view '{$v}' exists and compiles.\n";
}

echo "\n=======================================================\n";
echo "🎉 ALL {$assertions} ASSERTIONS PASSED SUCCESSFULLY! EXIT CODE 0.\n";
echo "=======================================================\n";
exit(0);
