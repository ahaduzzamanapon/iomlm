<?php

use App\Http\Controllers\Student\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Routes  →  prefix: /student   name: student.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', '/student/dashboard');

    // Timeline
    Route::get('timeline', [\App\Http\Controllers\Student\TimelineController::class, 'index'])->name('timeline');

    // Classes
    Route::get('classes',          [\App\Http\Controllers\Student\ClassController::class, 'index'])->name('classes.index');
    Route::get('classes/{class}',  [\App\Http\Controllers\Student\ClassController::class, 'show'])->name('classes.show');

    // Subjects
    Route::get('subjects',           [\App\Http\Controllers\Student\SubjectController::class, 'index'])->name('subjects.index');
    Route::get('subjects/{subject}', [\App\Http\Controllers\Student\SubjectController::class, 'show'])->name('subjects.show');

    // Resources
    Route::get('resources',          [\App\Http\Controllers\Student\LearningResourceController::class, 'index'])->name('resources.index');
    Route::get('resources/{module}', [\App\Http\Controllers\Student\LearningResourceController::class, 'show'])->name('resources.show');

    // Attendance
    Route::get('attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');

    // Exams
    Route::get('exams',        [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams.index');
    Route::get('exams/{exam}', [\App\Http\Controllers\Student\ExamController::class, 'show'])->name('exams.show');

    // Results
    Route::get('results',         [\App\Http\Controllers\Student\ResultController::class, 'index'])->name('results.index');
    Route::get('results/{result}', [\App\Http\Controllers\Student\ResultController::class, 'show'])->name('results.show');

    // Documents
    Route::get('documents',              [\App\Http\Controllers\Student\DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents/{type}/generate', [\App\Http\Controllers\Student\DocumentController::class, 'generate'])->name('documents.generate');
});
