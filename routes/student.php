<?php

use App\Http\Controllers\Student\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Routes  →  prefix: /student   name: student.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:student,admin,super_admin'])->prefix('student')->name('student.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', '/student/dashboard');

    // Timeline
    Route::get('timeline', [\App\Http\Controllers\Student\TimelineController::class, 'index'])->name('timeline');

    // My Classes & Calendar
    Route::get('classes',          [\App\Http\Controllers\Student\ClassController::class, 'index'])->name('classes.index');
    Route::get('classes/today',    [\App\Http\Controllers\Student\ClassController::class, 'today'])->name('classes.today');
    Route::get('calendar',     [\App\Http\Controllers\Student\ClassController::class, 'calendar'])->name('calendar');
    Route::get('classes/{class}',  [\App\Http\Controllers\Student\ClassController::class, 'show'])->name('classes.show');

    // My Course
    Route::get('my-course',       [\App\Http\Controllers\Student\MyCourseController::class, 'index'])->name('my-course.index');
    Route::post('my-course/apply', [\App\Http\Controllers\Student\MyCourseController::class, 'applyStore'])->name('my-course.apply');

    // Subjects
    Route::get('subjects',           [\App\Http\Controllers\Student\SubjectController::class, 'index'])->name('subjects.index');
    Route::get('subjects/{subject}', [\App\Http\Controllers\Student\SubjectController::class, 'show'])->name('subjects.show');

    // Resources
    Route::get('resources', [\App\Http\Controllers\Student\LearningResourceController::class, 'index'])->name('resources.index');

    // Attendance
    Route::get('attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');

    // Fees & Accounts Dues
    Route::get('fees', [\App\Http\Controllers\Student\FeeController::class, 'index'])->name('fees.index');
    Route::get('fees/payments/{payment}/receipt', [\App\Http\Controllers\Student\FeeController::class, 'printReceipt'])->name('fees.receipt');
    Route::post('fees/invoices/{invoice}/pay', [\App\Http\Controllers\Student\FeeController::class, 'payInvoice'])->name('fees.pay');

    // Exams
    Route::get('exams',                       [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams.index');
    Route::get('exams/{exam}/take',           [\App\Http\Controllers\Student\ExamController::class, 'take'])->name('exams.take');
    Route::post('exams/{exam}/submit',        [\App\Http\Controllers\Student\ExamController::class, 'submit'])->name('exams.submit');
    Route::get('exams/{exam}/result/{submission}', [\App\Http\Controllers\Student\ExamController::class, 'result'])->name('exams.result');

    // Results
    Route::get('results', [\App\Http\Controllers\Student\ResultController::class, 'index'])->name('results.index');

    // Documents
    Route::get('documents',              [\App\Http\Controllers\Student\DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents/{type}/generate', [\App\Http\Controllers\Student\DocumentController::class, 'generate'])->name('documents.generate');

    // Routine
    Route::get('routine', [\App\Http\Controllers\Student\RoutineController::class, 'index'])->name('routine.index');

    // Online Support
    Route::get('support', [\App\Http\Controllers\Student\StudentSupportController::class, 'index'])->name('support.index');
});
