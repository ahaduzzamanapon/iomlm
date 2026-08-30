<?php

use App\Http\Controllers\Teacher\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Routes  →  prefix: /teacher   name: teacher.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:teacher,admin,super_admin'])->prefix('teacher')->name('teacher.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', '/teacher/dashboard');

    // Schedule / Classes
    Route::get('classes',                         [\App\Http\Controllers\Teacher\ClassController::class, 'index'])->name('classes.index');
    Route::get('classes/today',                   [\App\Http\Controllers\Teacher\ClassController::class, 'today'])->name('classes.today');
    Route::get('classes/{class}/conduct',         [\App\Http\Controllers\Teacher\ClassController::class, 'conduct'])->name('classes.conduct');
    Route::post('classes/{class}/set-link',       [\App\Http\Controllers\Teacher\ClassController::class, 'setLink'])->name('classes.setLink');
    Route::post('classes/{class}/sync-zoom-attendance', [\App\Http\Controllers\Teacher\ClassController::class, 'syncZoomAttendance'])->name('classes.syncZoomAttendance');
    Route::post('classes/{class}/complete',       [\App\Http\Controllers\Teacher\ClassController::class, 'markComplete'])->name('classes.complete');
    Route::post('classes/{class}/cancel',         [\App\Http\Controllers\Teacher\ClassController::class, 'markCancelled'])->name('classes.cancel');
    Route::get('calendar',                        [\App\Http\Controllers\Teacher\ClassController::class, 'calendar'])->name('calendar');
    Route::get('schedule',                        [\App\Http\Controllers\Teacher\ClassController::class, 'schedule'])->name('schedule');

    // Subjects
    Route::get('subjects',          [\App\Http\Controllers\Teacher\SubjectController::class, 'index'])->name('subjects.index');
    Route::get('subjects/{subject}', [\App\Http\Controllers\Teacher\SubjectController::class, 'show'])->name('subjects.show');

    // Attendance
    Route::get('attendance',                          [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/{class}/mark',             [\App\Http\Controllers\Teacher\AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('attendance/{class}/save',            [\App\Http\Controllers\Teacher\AttendanceController::class, 'save'])->name('attendance.save');

    // Students
    Route::get('students',           [\App\Http\Controllers\Teacher\StudentController::class, 'index'])->name('students.index');
    Route::get('students/{student}', [\App\Http\Controllers\Teacher\StudentController::class, 'show'])->name('students.show');

    // Exams
    Route::get('exams',                            [\App\Http\Controllers\Teacher\ExamController::class, 'index'])->name('exams.index');
    Route::post('exams',                           [\App\Http\Controllers\Teacher\ExamController::class, 'store'])->name('exams.store');
    Route::get('exams/{exam}',                     [\App\Http\Controllers\Teacher\ExamController::class, 'show'])->name('exams.show');
    Route::post('exams/{exam}/questions',          [\App\Http\Controllers\Teacher\ExamController::class, 'attachQuestion'])->name('exams.questions.attach');
    Route::delete('exams/{exam}/questions/{examQuestion}', [\App\Http\Controllers\Teacher\ExamController::class, 'detachQuestion'])->name('exams.questions.detach');

    // Exam Grading (Written Questions)
    Route::get('exams/{exam}/grade',               [\App\Http\Controllers\Teacher\ExamGradingController::class, 'index'])->name('exams.grade');
    Route::patch('exam-answers/{answer}/grade',    [\App\Http\Controllers\Teacher\ExamGradingController::class, 'grade'])->name('exam-answers.grade');

    // Results
    Route::get('results',                      [\App\Http\Controllers\Teacher\ResultController::class, 'index'])->name('results.index');
    Route::get('results/{exam}/enter',         [\App\Http\Controllers\Teacher\ResultController::class, 'enter'])->name('results.enter');
    Route::post('results/{exam}/store',        [\App\Http\Controllers\Teacher\ResultController::class, 'store'])->name('results.store');

    // Learning Resources
    Route::resource('resources', \App\Http\Controllers\Teacher\LearningResourceController::class)->only(['index', 'store', 'destroy']);

    // Routine
    Route::get('routine', [\App\Http\Controllers\Teacher\RoutineController::class, 'index'])->name('routine.index');
});
