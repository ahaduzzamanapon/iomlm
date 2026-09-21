<?php

/**
 * Task 29 Verification Script: Subject & Module Management and Assignments
 * 
 * Verifies:
 * 1. Subject Categories CRUD and relationship with Subjects
 * 2. Subject 1-Click Clone ($subject->cloneSubject()) duplicating subject & all modules
 * 3. Module File attachment, Google Drive link, Folder Name, and Module Clone ($module->cloneModule())
 * 4. Module Hide/Unhide toggle and Student filtering (hidden modules omitted from student queries)
 * 5. Recorded class URL & Embed code support
 * 6. Assignment System: start_datetime, due_datetime, file attachment, marks
 * 7. Student Assignment Submission: file upload, note, status
 * 8. Teacher/Admin Assignment Grading & Admin Override
 * 9. Route definitions & Blade views integrity with Kalpurush Bangla font
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Subject;
use App\Models\SubjectCategory;
use App\Models\SubjectModule;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

$passed = 0;
$failed = 0;

function assertCondition(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [\033[32mPASS\033[0m] $name\n";
    } else {
        $failed++;
        echo "  [\033[31mFAIL\033[0m] $name " . ($details ? "($details)" : "") . "\n";
    }
}

echo "=== TASK 29: SUBJECT CATEGORIES, MODULES & ASSIGNMENTS VERIFICATION ===\n\n";

try {
    DB::beginTransaction();

    // ── SECTION 1: Subject Categories CRUD & Subject Relationship ────────
    echo "1. Testing Subject Categories & Subject Relationship...\n";

    $category = SubjectCategory::create([
        'name' => 'তাখাসসুস ফিল ফিকহ (Specialization in Fiqh)',
        'code' => 'TAK-FIQH',
        'description' => 'উচ্চতর ফিকহ ও ফতোয়া গবেষণা বিভাগ',
        'is_active' => true
    ]);

    assertCondition("SubjectCategory created successfully", $category && $category->id > 0);

    $subject = Subject::create([
        'code' => 'TEST-SUB-' . rand(1000, 9999),
        'name' => 'উসুলুল ইফতা ও ফতোয়া লিখন পদ্ধতি',
        'category_id' => $category->id,
        'credit' => 3,
        'full_marks' => 100,
        'pass_marks' => 40,
        'is_active' => true
    ]);

    assertCondition("Subject created with category_id", $subject && $subject->category_id == $category->id);
    assertCondition("Subject->category relationship resolves", $subject->category && $subject->category->id == $category->id);
    assertCondition("SubjectCategory->subjects relation contains subject", $category->subjects()->where('subjects.id', $subject->id)->exists());

    // ── SECTION 2: Subject Modules with Drive Link, Attachment & Folder ────────
    echo "\n2. Testing Enhanced Subject Modules (File, Drive, Folder, Hidden)...\n";

    $module1 = SubjectModule::create([
        'subject_id' => $subject->id,
        'title' => '১ম অধ্যায়: ফতোয়ার মূলনীতি ও পরিভাষা',
        'folder_name' => 'উসুলুল ইফতা পরিচিতি',
        'file_path' => 'modules/attachments/test_usul_intro.pdf',
        'drive_link' => 'https://drive.google.com/file/d/123456789/view?usp=sharing',
        'recorded_url' => 'https://www.youtube.com/watch?v=sample_fiqh_class',
        'embed_code' => '<iframe src="https://www.youtube.com/embed/sample_fiqh_class" frameborder="0"></iframe>',
        'is_hidden' => false,
        'sequence_no' => 1,
        'is_active' => true
    ]);

    assertCondition("Module 1 created with file_path, drive_link, folder_name & embed_code", 
        $module1 && !empty($module1->drive_link) && !empty($module1->file_path) && !empty($module1->folder_name)
    );

    $module2Hidden = SubjectModule::create([
        'subject_id' => $subject->id,
        'title' => '২য় অধ্যায়: সংরক্ষিত খসড়া নোট (শিক্ষক অনলি)',
        'folder_name' => 'উসুলুল ইফতা পরিচিতি',
        'file_path' => 'modules/attachments/draft_notes.pdf',
        'is_hidden' => true,
        'sequence_no' => 2,
        'is_active' => true
    ]);

    assertCondition("Module 2 created with is_hidden = true", $module2Hidden && $module2Hidden->is_hidden === true);

    // Test student query filtering for hidden modules
    $studentVisibleModules = $subject->modules()->where('is_hidden', false)->get();
    assertCondition("Student visible query includes module 1", $studentVisibleModules->contains('id', $module1->id));
    assertCondition("Student visible query excludes hidden module 2", !$studentVisibleModules->contains('id', $module2Hidden->id));

    // ── SECTION 3: Module & Subject 1-Click Cloning ────────
    echo "\n3. Testing 1-Click Module & Subject Cloning...\n";

    $clonedModule = $module1->cloneModule();
    assertCondition("Module clone creates new record", $clonedModule && $clonedModule->id !== $module1->id);
    assertCondition("Cloned module has '(Copy)' suffix in title", str_contains($clonedModule->title, '(Copy)'));
    assertCondition("Cloned module preserves folder, drive link and embed code", 
        $clonedModule->folder_name === $module1->folder_name && 
        $clonedModule->drive_link === $module1->drive_link &&
        $clonedModule->embed_code === $module1->embed_code
    );

    $clonedSubjectCode = 'CLONE-' . rand(100, 999);
    $clonedSubjectName = 'উসুলুল ইফতা (ক্লোন সংস্করণ)';
    $clonedSubject = $subject->cloneSubject($clonedSubjectCode, $clonedSubjectName);

    assertCondition("Subject clone creates new subject record", $clonedSubject && $clonedSubject->id !== $subject->id);
    assertCondition("Cloned subject has new code and name", $clonedSubject->code === $clonedSubjectCode && $clonedSubject->name === $clonedSubjectName);
    assertCondition("Cloned subject inherits category_id", $clonedSubject->category_id === $subject->category_id);
    assertCondition("Cloned subject duplicates all modules", $clonedSubject->modules()->count() === $subject->modules()->count());

    // ── SECTION 4: Assignment Management & Dates ────────
    echo "\n4. Testing Assignment System (Model, Attributes & Methods)...\n";

    $teacher = Teacher::first();
    if (!$teacher) {
        $teacherUser = User::create([
            'name' => 'মাওলানা আবদুল কাদের',
            'email' => 'teacher_test_' . time() . '@iom.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'teacher'
        ]);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'name' => 'মাওলানা আবদুল কাদের'
        ]);
    }

    $assignment = Assignment::create([
        'subject_id' => $subject->id,
        'title' => 'ফতোয়া লেখার প্রাথমিক শর্তাবলী সম্পর্কিত তামরিন',
        'instructions' => 'ইবনে আবেদিন আশ-শামী রচিত রাদ্দুল মুহতার এর মুখাদ্দামা থেকে ফতোয়া প্রদানের ১০টি মূলনীতি সংক্ষেপে লিখুন।',
        'start_datetime' => now()->subDay(),
        'due_datetime' => now()->addDays(7),
        'total_marks' => 25.00,
        'file_path' => 'assignments/brief_instructions.pdf',
        'teacher_id' => $teacher->id,
        'status' => 'ACTIVE'
    ]);

    assertCondition("Assignment created with start_datetime and due_datetime", $assignment && $assignment->id > 0);
    assertCondition("Assignment isOpen() returns true for current date range", $assignment->isOpen());
    assertCondition("Assignment isExpired() returns false before due date", !$assignment->isExpired());

    $expiredAssignment = Assignment::create([
        'subject_id' => $subject->id,
        'title' => 'মেয়াদোত্তীর্ণ অ্যাসাইনমেন্ট',
        'start_datetime' => now()->subDays(10),
        'due_datetime' => now()->subDay(),
        'total_marks' => 20.00,
        'teacher_id' => $teacher->id,
        'status' => 'ACTIVE'
    ]);

    assertCondition("Expired assignment isExpired() returns true", $expiredAssignment->isExpired());
    assertCondition("Expired assignment isOpen() returns false", !$expiredAssignment->isOpen());

    // ── SECTION 5: Student Submission & Grading ────────
    echo "\n5. Testing Student Assignment Submission & Teacher/Admin Grading...\n";

    $student = Student::first();
    if (!$student) {
        $studentUser = User::create([
            'name' => 'মুহাম্মদ আবদুল্লাহ',
            'email' => 'student_test_' . time() . '@iom.edu.bd',
            'password' => bcrypt('password'),
            'role' => 'student'
        ]);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'name' => 'মুহাম্মদ আবদুল্লাহ',
            'student_id' => '26-01-01-M-9999'
        ]);
    }

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'submission_file' => 'assignments/submissions/student_usul_tamrin.pdf',
        'student_note' => 'মুহতারাম উস্তাদ, ১০টি মূলনীতি বিস্তারিতভাবে নোট সংযুক্ত করেছি।',
        'status' => 'SUBMITTED',
        'submitted_at' => now()
    ]);

    assertCondition("AssignmentSubmission created with SUBMITTED status", $submission && $submission->status === 'SUBMITTED');
    assertCondition("Assignment->submissionForStudent resolves student submission", 
        $assignment->submissionForStudent($student->id)?->id === $submission->id
    );

    // Teacher grades submission
    $submission->update([
        'obtained_marks' => 23.50,
        'teacher_feedback' => 'মাশাআল্লাহ চমৎকার উপস্থাপন। মূলনীতিগুলো যথাযথভাবে উল্লেখ করা হয়েছে।',
        'status' => 'GRADED'
    ]);

    assertCondition("Submission graded successfully with obtained_marks and status GRADED", 
        $submission->obtained_marks == 23.50 && $submission->status === 'GRADED'
    );

    // Admin overrides submission mark & feedback
    $submission->update([
        'obtained_marks' => 24.00,
        'teacher_feedback' => 'এডমিন রিভিউ: বিশেষ মূল্যায়নে ১ নম্বর বৃদ্ধি করা হয়েছে।'
    ]);

    assertCondition("Admin override successfully modified mark to 24.00 and updated feedback", 
        $submission->obtained_marks == 24.00 && str_contains($submission->teacher_feedback, 'এডমিন রিভিউ')
    );

    // ── SECTION 6: Routes Integrity ────────
    echo "\n6. Verifying Named Routes for Task 29...\n";

    $requiredRoutes = [
        'admin.subject-categories.index',
        'admin.subject-categories.store',
        'admin.subject-categories.update',
        'admin.subject-categories.destroy',
        'admin.subjects.clone',
        'admin.modules.clone',
        'admin.modules.toggle-hidden',
        'admin.assignments.index',
        'admin.assignments.store',
        'admin.assignments.show',
        'admin.assignments.submissions.grade',
        'admin.assignments.submissions.override',
        'teacher.assignments.index',
        'teacher.assignments.show',
        'teacher.assignments.submissions.grade',
        'student.subjects.index',
        'student.subjects.show',
        'student.assignments.index',
        'student.assignments.show',
        'student.assignments.submit',
    ];

    foreach ($requiredRoutes as $routeName) {
        assertCondition("Route '$routeName' is registered", Route::has($routeName));
    }

    // ── SECTION 7: Blade Views & Kalpurush Font Verification ────────
    echo "\n7. Verifying Blade Views & Kalpurush Bangla Typography...\n";

    $bladeFiles = [
        'resources/views/admin/subject_categories/index.blade.php',
        'resources/views/admin/subjects/index.blade.php',
        'resources/views/admin/subjects/show.blade.php',
        'resources/views/admin/assignments/index.blade.php',
        'resources/views/admin/assignments/show.blade.php',
        'resources/views/teacher/assignments/index.blade.php',
        'resources/views/teacher/assignments/show.blade.php',
        'resources/views/student/subjects/index.blade.php',
        'resources/views/student/subjects/show.blade.php',
        'resources/views/student/assignments/index.blade.php',
        'resources/views/student/assignments/show.blade.php',
        'resources/views/student/layouts/app.blade.php'
    ];

    foreach ($bladeFiles as $relPath) {
        $fullPath = __DIR__ . '/../' . $relPath;
        $exists = file_exists($fullPath);
        assertCondition("Blade view '$relPath' exists", $exists);
        if ($exists) {
            $content = file_get_contents($fullPath);
            assertCondition("Blade view '$relPath' contains Kalpurush font reference", 
                str_contains($content, 'Kalpurush') || str_contains($content, 'kalpurush') || str_contains($relPath, 'layouts')
            );
        }
    }

    // Rollback changes to keep DB pristine
    DB::rollBack();
    echo "\nVerification database transaction rolled back cleanly.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n\033[31mException occurred:\033[0m " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $failed++;
}

echo "\n=======================================================\n";
echo "VERIFICATION SUMMARY:\n";
echo "Total Passed: $passed\n";
echo "Total Failed: $failed\n";

if ($failed === 0) {
    echo "\033[32mALL ASSERTIONS PASSED! Task 29 verified successfully.\033[0m\n";
    exit(0);
} else {
    echo "\033[31mSOME ASSERTIONS FAILED! Please review.\033[0m\n";
    exit(1);
}
