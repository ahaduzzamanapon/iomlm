<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes  →  prefix: /admin   name: admin.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', '/admin/dashboard');

    // ── Academic Setup ────────────────────────────────────────────────
    Route::resource('academic-years',   \App\Http\Controllers\Admin\AcademicYearController::class);
    Route::patch('academic-years/{academicYear}/toggle-status', [\App\Http\Controllers\Admin\AcademicYearController::class, 'toggleStatus'])->name('academic-years.toggle-status');
    Route::post('academic-years/{academicYear}/session', [\App\Http\Controllers\Admin\AcademicYearController::class, 'storeSession'])->name('academic-years.session.store');
    Route::delete('academic-years/sessions/{academicSession}', [\App\Http\Controllers\Admin\AcademicYearController::class, 'destroySession'])->name('academic-years.session.destroy');
    Route::resource('subjects',         \App\Http\Controllers\Admin\SubjectController::class);
    Route::resource('subjects.modules', \App\Http\Controllers\Admin\SubjectModuleController::class)->shallow();
    Route::resource('courses',          \App\Http\Controllers\Admin\CourseController::class);
    Route::post('courses/{course}/semesters', [\App\Http\Controllers\Admin\CourseController::class, 'storeSemester'])->name('courses.semesters.store');
    Route::delete('courses/{course}/semesters/{semester}', [\App\Http\Controllers\Admin\CourseController::class, 'destroySemester'])->name('courses.semesters.destroy');
    Route::post('courses/{course}/subjects', [\App\Http\Controllers\Admin\CourseController::class, 'assignSubject'])->name('courses.subjects.assign');
    Route::delete('courses/{course}/subjects/{map}', [\App\Http\Controllers\Admin\CourseController::class, 'removeSubject'])->name('courses.subjects.remove');
    Route::resource('semesters', \App\Http\Controllers\Admin\SemesterController::class)->only(['index', 'store', 'destroy']);
    Route::resource('holiday-calendar', \App\Http\Controllers\Admin\HolidayCalendarController::class)->only(['index', 'store', 'destroy']);

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
    Route::get('waiver-applications',                                     [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'index'])->name('waiver-applications.index');
    Route::get('waiver-applications/{waiverApplication}',                 [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'show'])->name('waiver-applications.show');
    Route::patch('waiver-applications/{waiverApplication}/approve',       [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'approve'])->name('waiver-applications.approve');
    Route::patch('waiver-applications/{waiverApplication}/reject',        [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'reject'])->name('waiver-applications.reject');

    // ── Survey & Dynamic Forms ─────────────────────────────────────────
    Route::get('surveys',                               [\App\Http\Controllers\Admin\SurveyController::class, 'index'])->name('surveys.index');
    Route::post('surveys',                              [\App\Http\Controllers\Admin\SurveyController::class, 'store'])->name('surveys.store');
    Route::get('surveys/{survey}/builder',             [\App\Http\Controllers\Admin\SurveyController::class, 'builder'])->name('surveys.builder');
    Route::put('surveys/{survey}/builder',             [\App\Http\Controllers\Admin\SurveyController::class, 'saveBuilder'])->name('surveys.builder.save');
    Route::patch('surveys/{survey}/toggle-status',      [\App\Http\Controllers\Admin\SurveyController::class, 'toggleStatus'])->name('surveys.toggle-status');
    Route::get('surveys/{survey}/responses',           [\App\Http\Controllers\Admin\SurveyController::class, 'responses'])->name('surveys.responses');
    Route::get('surveys/{survey}/responses/csv',       [\App\Http\Controllers\Admin\SurveyController::class, 'exportCsv'])->name('surveys.responses.csv');
    Route::delete('surveys/{survey}',                   [\App\Http\Controllers\Admin\SurveyController::class, 'destroy'])->name('surveys.destroy');

    // ── Support Setup (Departments & Agents) ──────────────────────────
    Route::get('support-tickets',                        [\App\Http\Controllers\Admin\AdminSupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::patch('support-tickets/{ticket}/reassign',    [\App\Http\Controllers\Admin\AdminSupportTicketController::class, 'reassign'])->name('support-tickets.reassign');
    Route::resource('support-departments',               \App\Http\Controllers\Admin\SupportDepartmentController::class);
    Route::patch('support-departments/{supportDepartment}/toggle', [\App\Http\Controllers\Admin\SupportDepartmentController::class, 'toggleStatus'])->name('support-departments.toggle');
    Route::resource('support-agents',                    \App\Http\Controllers\Admin\SupportUserController::class);

    // ── Hidden Artisan Command Console ──────────────────────────────
    Route::get('command',  [\App\Http\Controllers\Admin\CommandRunnerController::class, 'index'])->name('command.run.index');
    Route::post('command', [\App\Http\Controllers\Admin\CommandRunnerController::class, 'run'])->name('command.run');

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
    Route::get('questions/template-download', [\App\Http\Controllers\Admin\QuestionController::class, 'downloadTemplate'])->name('questions.template-download');
    Route::resource('questions', \App\Http\Controllers\Admin\QuestionController::class)->only(['index', 'store', 'destroy']);
    Route::post('questions/bulk-upload', [\App\Http\Controllers\Admin\QuestionController::class, 'bulkUpload'])->name('questions.bulk-upload');

    // ── Exams, Results, Retakes, Promotions ───────────────────────────
    Route::resource('exams', \App\Http\Controllers\Admin\ExamController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('retakes',  [\App\Http\Controllers\Admin\SubjectRetakeController::class, 'index'])->name('retakes.index');
    Route::post('retakes', [\App\Http\Controllers\Admin\SubjectRetakeController::class, 'store'])->name('retakes.store');
    Route::resource('promotions', \App\Http\Controllers\Admin\PromotionController::class)->only(['index', 'store']);

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
    Route::post('accounts/payments/{payment}/approve', [\App\Http\Controllers\Admin\AccountsController::class, 'approvePayment'])->name('accounts.payments.approve');
    Route::post('accounts/payments/{payment}/reject',  [\App\Http\Controllers\Admin\AccountsController::class, 'rejectPayment'])->name('accounts.payments.reject');

    // ── Notice Board ──────────────────────────────────────────────────
    Route::resource('notices', \App\Http\Controllers\Admin\NoticeController::class)->only(['index', 'store', 'destroy']);

    // ── Reports & Settings ────────────────────────────────────────────
    Route::get('reports',           [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('settings',          [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings',          [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // ── Google Auth Setup ───────────────────────────────────────────────
    Route::get('settings/google-auth',  [\App\Http\Controllers\Admin\GoogleAuthSettingController::class, 'index'])->name('settings.google-auth.index');
    Route::post('settings/google-auth', [\App\Http\Controllers\Admin\GoogleAuthSettingController::class, 'update'])->name('settings.google-auth.update');

    // ── Notification Settings (Firebase & SMTP) ─────────────────────────
    Route::get('settings/notifications',           [\App\Http\Controllers\Admin\NotificationSettingController::class, 'index'])->name('settings.notifications.index');
    Route::post('settings/notifications/firebase', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'updateFirebase'])->name('settings.notifications.firebase');
    Route::post('settings/notifications/smtp',     [\App\Http\Controllers\Admin\NotificationSettingController::class, 'updateSmtp'])->name('settings.notifications.smtp');
    Route::post('settings/notifications/test-mail',[\App\Http\Controllers\Admin\NotificationSettingController::class, 'sendTestMail'])->name('settings.notifications.test-mail');

    // ── Send Notification Broadcast Center ──────────────────────────────
    Route::get('notifications',                    [\App\Http\Controllers\Admin\BroadcastNotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/create',             [\App\Http\Controllers\Admin\BroadcastNotificationController::class, 'create'])->name('notifications.create');
    Route::post('notifications',                   [\App\Http\Controllers\Admin\BroadcastNotificationController::class, 'send'])->name('notifications.send');

    // ── Fee Heads (Settings → Fee Head) ───────────────────────────
    Route::get('fee-heads',                     [\App\Http\Controllers\Admin\FeeHeadController::class, 'index'])->name('fee-heads.index');
    Route::post('fee-heads',                    [\App\Http\Controllers\Admin\FeeHeadController::class, 'store'])->name('fee-heads.store');
    Route::put('fee-heads/{feeHead}',           [\App\Http\Controllers\Admin\FeeHeadController::class, 'update'])->name('fee-heads.update');
    Route::delete('fee-heads/{feeHead}',        [\App\Http\Controllers\Admin\FeeHeadController::class, 'destroy'])->name('fee-heads.destroy');

    // ── Course Fee Packages ────────────────────────────────────────
    Route::post('courses/{course}/packages',                          [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'store'])->name('courses.packages.store');
    Route::put('courses/packages/{package}',                          [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'update'])->name('courses.packages.update');
    Route::delete('courses/packages/{package}',                       [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'destroy'])->name('courses.packages.destroy');
    Route::patch('courses/{course}/packages/{package}/set-default',   [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'setDefault'])->name('courses.packages.set-default');
    Route::post('courses/packages/{package}/items',                   [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'storeItem'])->name('courses.packages.items.store');
    Route::put('courses/packages/items/{item}',                       [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'updateItem'])->name('courses.packages.items.update');
    Route::delete('courses/packages/items/{item}',                    [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'destroyItem'])->name('courses.packages.items.destroy');
    Route::post('courses/{course}/packages/from-template',            [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'fromTemplate'])->name('courses.packages.from-template');

    // ── Retake Approve ────────────────────────────────────────────
    Route::patch('retakes/{retake}/approve', [\App\Http\Controllers\Admin\SubjectRetakeController::class, 'approve'])->name('retakes.approve');


    // ── Public Applications (from /apply form) — now handled via AdmissionForm (source=PUBLIC) ──
    // Admin reviews these from admin/admissions tab=public, no separate controller needed.

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

    // ── Final Mark Generator ───────────────────────────────────────────
    Route::get('final-marks',          [\App\Http\Controllers\Admin\FinalMarkController::class, 'index'])->name('final-marks.index');
    Route::post('final-marks/generate',[\App\Http\Controllers\Admin\FinalMarkController::class, 'generate'])->name('final-marks.generate');
    Route::get('final-marks/export-csv',[\App\Http\Controllers\Admin\FinalMarkController::class, 'exportCsv'])->name('final-marks.export-csv');
});

