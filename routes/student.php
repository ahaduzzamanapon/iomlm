<?php

use App\Http\Controllers\Student\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Routes  →  prefix: /student   name: student.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:student,admin,super_admin'])->prefix('student')->name('student.')->group(function () {

    // ── Profile Management (Always accessible to complete profile) ────────
    Route::get('profile', [\App\Http\Controllers\Student\ProfileController::class, 'index'])->name('profile.index');
    Route::post('profile', [\App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');

    // ── Enforce 95% Profile Completion & Active Course Access ────────────
    Route::middleware(['profile.completed', 'course.access'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/', '/student/dashboard');

        // Timeline
        Route::get('timeline', [\App\Http\Controllers\Student\TimelineController::class, 'index'])->name('timeline');

        // My Classes & Calendar
        Route::get('classes',               [\App\Http\Controllers\Student\ClassController::class, 'index'])->name('classes.index');
        Route::get('classes/today',         [\App\Http\Controllers\Student\ClassController::class, 'today'])->name('classes.today');
        Route::get('calendar',              [\App\Http\Controllers\Student\ClassController::class, 'calendar'])->name('calendar');
        Route::get('classes/{class}',       [\App\Http\Controllers\Student\ClassController::class, 'show'])->name('classes.show');
        Route::get('classes/{class}/join',  [\App\Http\Controllers\Student\ClassController::class, 'join'])->name('classes.join');

        // My Course
        Route::get('my-course',       [\App\Http\Controllers\Student\MyCourseController::class, 'index'])->name('my-course.index');
        Route::post('my-course/apply', [\App\Http\Controllers\Student\MyCourseController::class, 'applyStore'])->name('my-course.apply');

        // Course Transfer (কোর্স পরিবর্তন)
        Route::get('course-transfers',                    [\App\Http\Controllers\Student\CourseTransferController::class, 'index'])->name('course-transfers.index');
        Route::post('course-transfers',                   [\App\Http\Controllers\Student\CourseTransferController::class, 'store'])->name('course-transfers.store');
        Route::delete('course-transfers/{courseTransfer}', [\App\Http\Controllers\Student\CourseTransferController::class, 'cancel'])->name('course-transfers.cancel');

        // Readmission (রি-এডমিশন আবেদন)
        Route::get('readmissions',                   [\App\Http\Controllers\Student\ReadmissionController::class, 'index'])->name('readmissions.index');
        Route::post('readmissions',                  [\App\Http\Controllers\Student\ReadmissionController::class, 'store'])->name('readmissions.store');
        Route::delete('readmissions/{readmission}',  [\App\Http\Controllers\Student\ReadmissionController::class, 'cancel'])->name('readmissions.cancel');

        // Subjects
        Route::get('subjects',           [\App\Http\Controllers\Student\SubjectController::class, 'index'])->name('subjects.index');
        Route::get('subjects/{subject}', [\App\Http\Controllers\Student\SubjectController::class, 'show'])->name('subjects.show');

        // Assignments (শিক্ষার্থীদের অ্যাসাইনমেন্ট তালিকা ও জমা)
        Route::get('assignments',                       [\App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/{assignment}',           [\App\Http\Controllers\Student\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{assignment}/submit',   [\App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');

        // Resources
        Route::get('resources', [\App\Http\Controllers\Student\LearningResourceController::class, 'index'])->name('resources.index');

        // Attendance
        Route::get('attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');

        // Fees & Accounts Dues
        Route::get('fees', [\App\Http\Controllers\Student\FeeController::class, 'index'])->name('fees.index');
        Route::get('fees/payments/{payment}/receipt', [\App\Http\Controllers\Student\FeeController::class, 'printReceipt'])->name('fees.receipt');
        Route::post('fees/invoices/{invoice}/pay', [\App\Http\Controllers\Student\FeeController::class, 'payInvoice'])->name('fees.pay');
        Route::post('fees/particulars/update', [\App\Http\Controllers\Student\FeeController::class, 'updateParticular'])->name('fees.particular.update');

        // Exams
        Route::get('exams',                       [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}/take',           [\App\Http\Controllers\Student\ExamController::class, 'take'])->name('exams.take');
        Route::post('exams/{exam}/submit',        [\App\Http\Controllers\Student\ExamController::class, 'submit'])->name('exams.submit');
        Route::post('exams/{exam}/appeal',        [\App\Http\Controllers\Student\ExamController::class, 'appeal'])->name('exams.appeal');
        Route::get('exams/{exam}/result/{submission}', [\App\Http\Controllers\Student\ExamController::class, 'result'])->name('exams.result');

        // Results & Transcript
        Route::get('results',    [\App\Http\Controllers\Student\ResultController::class, 'index'])->name('results.index');
        Route::get('transcript', [\App\Http\Controllers\Student\ResultController::class, 'transcript'])->name('results.transcript');

        // Documents
        Route::get('documents',              [\App\Http\Controllers\Student\DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents/{type}/generate', [\App\Http\Controllers\Student\DocumentController::class, 'generate'])->name('documents.generate');

        // Routine
        Route::get('routine', [\App\Http\Controllers\Student\RoutineController::class, 'index'])->name('routine.index');

        // Online Support
        Route::get('support', [\App\Http\Controllers\Student\StudentSupportController::class, 'index'])->name('support.index');
    });
});
