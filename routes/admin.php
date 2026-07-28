<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes  →  prefix: /admin   name: admin.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', '/admin/dashboard');

    // ── Academic Setup ────────────────────────────────────────────────
    Route::resource('academic-years',   \App\Http\Controllers\Admin\AcademicYearController::class);
    Route::post('academic-years/{academicYear}/session', [\App\Http\Controllers\Admin\AcademicYearController::class, 'storeSession'])->name('academic-years.session.store');
    Route::resource('subjects',         \App\Http\Controllers\Admin\SubjectController::class);
    Route::resource('subjects.modules', \App\Http\Controllers\Admin\SubjectModuleController::class)->shallow();
    Route::resource('courses',          \App\Http\Controllers\Admin\CourseController::class);
    Route::post('courses/{course}/semesters', [\App\Http\Controllers\Admin\CourseController::class, 'storeSemester'])->name('courses.semesters.store');
    Route::delete('courses/{course}/semesters/{semester}', [\App\Http\Controllers\Admin\CourseController::class, 'destroySemester'])->name('courses.semesters.destroy');
    Route::post('courses/{course}/subjects', [\App\Http\Controllers\Admin\CourseController::class, 'assignSubject'])->name('courses.subjects.assign');
    Route::delete('courses/{course}/subjects/{map}', [\App\Http\Controllers\Admin\CourseController::class, 'removeSubject'])->name('courses.subjects.remove');
    Route::resource('semesters',        \App\Http\Controllers\Admin\SemesterController::class);
    Route::resource('holiday-calendar', \App\Http\Controllers\Admin\HolidayCalendarController::class);

    // ── Teachers ──────────────────────────────────────────────────────
    Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);
    Route::post('teachers/{teacher}/subjects', [\App\Http\Controllers\Admin\TeacherController::class, 'assignSubject'])->name('teachers.subjects.assign');
    Route::delete('teachers/{teacher}/subjects/{assignment}', [\App\Http\Controllers\Admin\TeacherController::class, 'removeSubject'])->name('teachers.subjects.remove');

    // ── Admissions & Students ─────────────────────────────────────────
    Route::resource('admissions', \App\Http\Controllers\Admin\AdmissionController::class);
    Route::patch('admissions/{admission}/approve', [\App\Http\Controllers\Admin\AdmissionController::class, 'approve'])->name('admissions.approve');
    Route::patch('admissions/{admission}/reject',  [\App\Http\Controllers\Admin\AdmissionController::class, 'reject'])->name('admissions.reject');
    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);

    // ── Batches & Classes ─────────────────────────────────────────────
    Route::resource('batches', \App\Http\Controllers\Admin\BatchController::class);
    Route::post('batches/{batch}/generate-timeline', [\App\Http\Controllers\Admin\BatchController::class, 'generateTimeline'])->name('batches.generate-timeline');
    Route::resource('classes', \App\Http\Controllers\Admin\ClassSessionController::class);

    // ── Exams, Results, Retakes, Promotions ───────────────────────────
    Route::resource('exams',      \App\Http\Controllers\Admin\ExamController::class);
    Route::resource('retakes',    \App\Http\Controllers\Admin\SubjectRetakeController::class);
    Route::resource('promotions', \App\Http\Controllers\Admin\PromotionController::class);

    // ── Reports & Settings ────────────────────────────────────────────
    Route::get('reports',           [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('settings',          [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings',          [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
});
