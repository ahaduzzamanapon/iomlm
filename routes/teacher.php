<?php

use App\Http\Controllers\Teacher\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Routes  →  prefix: /teacher   name: teacher.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('teacher')->name('teacher.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', '/teacher/dashboard');

    // Schedule
    Route::get('classes',                     [\App\Http\Controllers\Teacher\ClassController::class, 'index'])->name('classes.index');
    Route::get('classes/{class}/conduct',     [\App\Http\Controllers\Teacher\ClassController::class, 'conduct'])->name('classes.conduct');
    Route::patch('classes/{class}/complete',  [\App\Http\Controllers\Teacher\ClassController::class, 'markComplete'])->name('classes.complete');
    Route::patch('classes/{class}/cancel',    [\App\Http\Controllers\Teacher\ClassController::class, 'markCancelled'])->name('classes.cancel');
    Route::get('schedule',                    [\App\Http\Controllers\Teacher\ClassController::class, 'schedule'])->name('schedule');

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
    Route::get('exams',           [\App\Http\Controllers\Teacher\ExamController::class, 'index'])->name('exams.index');
    Route::get('exams/{exam}',    [\App\Http\Controllers\Teacher\ExamController::class, 'show'])->name('exams.show');

    // Results
    Route::get('results',                      [\App\Http\Controllers\Teacher\ResultController::class, 'index'])->name('results.index');
    Route::get('results/{exam}/enter',         [\App\Http\Controllers\Teacher\ResultController::class, 'enter'])->name('results.enter');
    Route::post('results/{exam}/store',        [\App\Http\Controllers\Teacher\ResultController::class, 'store'])->name('results.store');

    // Learning Resources
    Route::resource('resources', \App\Http\Controllers\Teacher\LearningResourceController::class);
});
