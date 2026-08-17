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
    Route::post('teachers/{teacher}/subjects',                     [\App\Http\Controllers\Admin\TeacherController::class, 'assignSubject'])->name('teachers.subjects.assign');
    Route::delete('teachers/{teacher}/subjects/{assignment}',      [\App\Http\Controllers\Admin\TeacherController::class, 'removeSubject'])->name('teachers.subjects.remove');
    Route::get('teachers/{teacher}/id-card',                       [\App\Http\Controllers\Admin\TeacherController::class, 'printIdCard'])->name('teachers.id-card');

    // ── Admissions & Students ─────────────────────────────────────────
    Route::resource('admissions', \App\Http\Controllers\Admin\AdmissionController::class);
    Route::patch('admissions/{admission}/approve', [\App\Http\Controllers\Admin\AdmissionController::class, 'approve'])->name('admissions.approve');
    Route::patch('admissions/{admission}/reject',  [\App\Http\Controllers\Admin\AdmissionController::class, 'reject'])->name('admissions.reject');

    // ── Poor Fund & Waiver Applications ──────────────────────────────
    Route::resource('waiver-applications', \App\Http\Controllers\Admin\WaiverApplicationController::class);
    Route::patch('waiver-applications/{waiverApplication}/approve', [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'approve'])->name('waiver-applications.approve');
    Route::patch('waiver-applications/{waiverApplication}/reject',  [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'reject'])->name('waiver-applications.reject');

    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
    Route::get('students/{student}/grade-sheet', [\App\Http\Controllers\Admin\StudentController::class, 'printGradeSheet'])->name('students.grade-sheet');
    Route::get('students/{student}/certificate', [\App\Http\Controllers\Admin\StudentController::class, 'printCertificate'])->name('students.certificate');
    Route::get('students/{student}/id-card',     [\App\Http\Controllers\Admin\StudentController::class, 'printIdCard'])->name('students.id-card');

    // ── Batches & Classes ─────────────────────────────────────────────
    Route::resource('batches', \App\Http\Controllers\Admin\BatchController::class);
    Route::post('batches/{batch}/generate-sessions', [\App\Http\Controllers\Admin\BatchController::class, 'generateTimeline'])->name('batches.generateTimeline');
    Route::resource('classes', \App\Http\Controllers\Admin\ClassSessionController::class);
    Route::put('classes/{class}/schedule',   [\App\Http\Controllers\Admin\ClassSessionController::class, 'updateSchedule'])->name('classes.updateSchedule');
    Route::post('classes/{class}/generate-zoom', [\App\Http\Controllers\Admin\ClassSessionController::class, 'generateZoomLink'])->name('classes.generateZoom');
    Route::post('classes/{class}/complete',  [\App\Http\Controllers\Admin\ClassSessionController::class, 'markComplete'])->name('classes.complete');
    Route::post('classes/{class}/cancel',    [\App\Http\Controllers\Admin\ClassSessionController::class, 'markCancelled'])->name('classes.cancel');

    // ── Question Bank ────────────────────────────────────────────────
    Route::resource('questions',   \App\Http\Controllers\Admin\QuestionController::class);
    Route::post('questions/bulk-upload', [\App\Http\Controllers\Admin\QuestionController::class, 'bulkUpload'])->name('questions.bulk-upload');

    // ── Exams, Results, Retakes, Promotions ───────────────────────────
    Route::resource('exams',      \App\Http\Controllers\Admin\ExamController::class);
    Route::resource('retakes',    \App\Http\Controllers\Admin\SubjectRetakeController::class);
    Route::resource('promotions', \App\Http\Controllers\Admin\PromotionController::class);

    // ── Routine ────────────────────────────────────────────────────────────
    Route::get('routine',                           [\App\Http\Controllers\Admin\RoutineController::class, 'index'])->name('routine.index');
    Route::post('routine/entries',                  [\App\Http\Controllers\Admin\RoutineController::class, 'store'])->name('routine.entries.store');
    Route::put('routine/entries/{entry}',           [\App\Http\Controllers\Admin\RoutineController::class, 'update'])->name('routine.entries.update');
    Route::delete('routine/entries/{entry}',        [\App\Http\Controllers\Admin\RoutineController::class, 'destroy'])->name('routine.entries.destroy');
    Route::post('routine/auto-generate/{batch}',    [\App\Http\Controllers\Admin\RoutineController::class, 'autoGenerate'])->name('routine.auto-generate');
    Route::get('routine/unassigned',                [\App\Http\Controllers\Admin\RoutineController::class, 'unassigned'])->name('routine.unassigned');
    Route::post('routine/slots',                    [\App\Http\Controllers\Admin\RoutineController::class, 'storeSlot'])->name('routine.slots.store');
    Route::put('routine/slots/{slot}',              [\App\Http\Controllers\Admin\RoutineController::class, 'updateSlot'])->name('routine.slots.update');
    Route::delete('routine/slots/{slot}',           [\App\Http\Controllers\Admin\RoutineController::class, 'destroySlot'])->name('routine.slots.destroy');

    // ── Accounts & Accounts Management ──────────────────────────────────
    Route::get('accounts',                            [\App\Http\Controllers\Admin\AccountsController::class, 'dashboard'])->name('accounts.dashboard');
    Route::get('accounts/invoices',                   [\App\Http\Controllers\Admin\AccountsController::class, 'invoices'])->name('accounts.invoices');
    Route::post('accounts/invoices',                  [\App\Http\Controllers\Admin\AccountsController::class, 'storeInvoice'])->name('accounts.invoices.store');
    Route::post('accounts/invoices/{invoice}/collect',[\App\Http\Controllers\Admin\AccountsController::class, 'collectPayment'])->name('accounts.invoices.collect');
    Route::get('accounts/fee-structures',             [\App\Http\Controllers\Admin\AccountsController::class, 'feeStructures'])->name('accounts.fee-structures');
    Route::post('accounts/fee-structures',            [\App\Http\Controllers\Admin\AccountsController::class, 'storeFeeStructure'])->name('accounts.fee-structures.store');
    Route::get('accounts/reports',                    [\App\Http\Controllers\Admin\AccountsController::class, 'reports'])->name('accounts.reports');
    Route::get('accounts/payments/{payment}/receipt', [\App\Http\Controllers\Admin\AccountsController::class, 'printReceipt'])->name('accounts.payments.receipt');

    // ── Notice Board ──────────────────────────────────────────────────
    Route::resource('notices', \App\Http\Controllers\Admin\NoticeController::class);

    // ── Reports & Settings ────────────────────────────────────────────
    Route::get('reports',           [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('settings',          [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings',          [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // ── Public Applications (from /apply form) ────────────────────────
    Route::get('public-applications',                       [\App\Http\Controllers\Admin\PublicApplicationController::class, 'index'])->name('public-applications.index');
    Route::get('public-applications/{publicApplication}',   [\App\Http\Controllers\Admin\PublicApplicationController::class, 'show'])->name('public-applications.show');
    Route::patch('public-applications/{publicApplication}/status', [\App\Http\Controllers\Admin\PublicApplicationController::class, 'updateStatus'])->name('public-applications.status');

    // ── App Settings (Blood Groups, Religions, Divisions, Districts) ──
    Route::get('app-settings',                          [\App\Http\Controllers\Admin\AppSettingController::class, 'index'])->name('app-settings.index');
    Route::put('app-settings/global',                   [\App\Http\Controllers\Admin\AppSettingController::class, 'updateSettings'])->name('app-settings.update');
    Route::post('app-settings/blood-groups',            [\App\Http\Controllers\Admin\AppSettingController::class, 'storeBloodGroup'])->name('app-settings.blood-groups.store');
    Route::delete('app-settings/blood-groups/{bloodGroup}', [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyBloodGroup'])->name('app-settings.blood-groups.destroy');
    Route::post('app-settings/religions',               [\App\Http\Controllers\Admin\AppSettingController::class, 'storeReligion'])->name('app-settings.religions.store');
    Route::delete('app-settings/religions/{religion}',  [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyReligion'])->name('app-settings.religions.destroy');
    Route::post('app-settings/divisions',               [\App\Http\Controllers\Admin\AppSettingController::class, 'storeDivision'])->name('app-settings.divisions.store');
    Route::delete('app-settings/divisions/{division}',  [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyDivision'])->name('app-settings.divisions.destroy');
    Route::post('app-settings/districts',               [\App\Http\Controllers\Admin\AppSettingController::class, 'storeDistrict'])->name('app-settings.districts.store');
    Route::delete('app-settings/districts/{district}',  [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyDistrict'])->name('app-settings.districts.destroy');
});

