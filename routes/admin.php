<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes  →  prefix: /admin   name: admin.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard (accessible to all authenticated admins)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/', '/admin/dashboard');

    // ── 1. Academic Setup ───────────────────────────────────────────────
    Route::middleware('admin.module:academic')->group(function () {
        Route::resource('academic-years', \App\Http\Controllers\Admin\AcademicYearController::class);
        Route::patch('academic-years/{academicYear}/toggle-status', [\App\Http\Controllers\Admin\AcademicYearController::class, 'toggleStatus'])->name('academic-years.toggle-status');
        Route::post('academic-years/{academicYear}/session', [\App\Http\Controllers\Admin\AcademicYearController::class, 'storeSession'])->name('academic-years.session.store');
        Route::delete('academic-years/sessions/{academicSession}', [\App\Http\Controllers\Admin\AcademicYearController::class, 'destroySession'])->name('academic-years.session.destroy');
        Route::resource('subject-categories', \App\Http\Controllers\Admin\SubjectCategoryController::class);
        Route::post('subjects/{subject}/clone', [\App\Http\Controllers\Admin\SubjectController::class, 'clone'])->name('subjects.clone');
        Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);
        Route::post('modules/{module}/clone', [\App\Http\Controllers\Admin\SubjectModuleController::class, 'clone'])->name('modules.clone');
        Route::post('modules/{module}/toggle-hidden', [\App\Http\Controllers\Admin\SubjectModuleController::class, 'toggleHidden'])->name('modules.toggle-hidden');
        Route::resource('subjects.modules', \App\Http\Controllers\Admin\SubjectModuleController::class)->shallow();
        Route::resource('assignments', \App\Http\Controllers\Admin\AssignmentController::class);
        Route::post('assignment-submissions/{submission}/grade', [\App\Http\Controllers\Admin\AssignmentController::class, 'gradeSubmission'])->name('assignments.submissions.grade');
        Route::post('assignment-submissions/{submission}/override', [\App\Http\Controllers\Admin\AssignmentController::class, 'overrideSubmission'])->name('assignments.submissions.override');
        Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);
        Route::post('courses/{course}/semesters', [\App\Http\Controllers\Admin\CourseController::class, 'storeSemester'])->name('courses.semesters.store');
        Route::delete('courses/{course}/semesters/{semester}', [\App\Http\Controllers\Admin\CourseController::class, 'destroySemester'])->name('courses.semesters.destroy');
        Route::post('courses/{course}/subjects', [\App\Http\Controllers\Admin\CourseController::class, 'assignSubject'])->name('courses.subjects.assign');
        Route::delete('courses/{course}/subjects/{map}', [\App\Http\Controllers\Admin\CourseController::class, 'removeSubject'])->name('courses.subjects.remove');
        Route::resource('semesters', \App\Http\Controllers\Admin\SemesterController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('program-activities', \App\Http\Controllers\Admin\ProgramActivityController::class);
        Route::resource('holiday-calendar', \App\Http\Controllers\Admin\HolidayCalendarController::class)->only(['index', 'store', 'destroy']);
    });

    // ── 2. Teachers ─────────────────────────────────────────────────────
    Route::middleware('admin.module:teachers')->group(function () {
        Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);
        Route::post('teachers/{teacher}/subjects', [\App\Http\Controllers\Admin\TeacherController::class, 'assignSubject'])->name('teachers.subjects.assign');
        Route::delete('teachers/{teacher}/subjects/{assignment}', [\App\Http\Controllers\Admin\TeacherController::class, 'removeSubject'])->name('teachers.subjects.remove');
        Route::get('teachers/{teacher}/id-card', [\App\Http\Controllers\Admin\TeacherController::class, 'printIdCard'])->name('teachers.id-card');
    });

    // ── 3. Admissions & Waivers ─────────────────────────────────────────
    Route::middleware('admin.module:admissions')->group(function () {
        Route::resource('admissions', \App\Http\Controllers\Admin\AdmissionController::class);
        Route::patch('admissions/{admission}/approve', [\App\Http\Controllers\Admin\AdmissionController::class, 'approve'])->name('admissions.approve');
        Route::patch('admissions/{admission}/reject', [\App\Http\Controllers\Admin\AdmissionController::class, 'reject'])->name('admissions.reject');
        Route::post('admissions/{admission}/send-repayment-email', [\App\Http\Controllers\Admin\AdmissionController::class, 'sendRepaymentEmail'])->name('admissions.send-repayment-email');

        // Admission Circulars (ভর্তি সার্কুলার)
        Route::post('admission-circulars/{admissionCircular}/clone', [\App\Http\Controllers\Admin\AdmissionCircularController::class, 'clone'])->name('admission-circulars.clone');
        Route::patch('admission-circulars/{admissionCircular}/toggle', [\App\Http\Controllers\Admin\AdmissionCircularController::class, 'toggle'])->name('admission-circulars.toggle');
        Route::resource('admission-circulars', \App\Http\Controllers\Admin\AdmissionCircularController::class);

        // Poor Fund & Waiver Applications
        Route::get('waiver-applications', [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'index'])->name('waiver-applications.index');
        Route::get('waiver-applications/{waiverApplication}', [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'show'])->name('waiver-applications.show');
        Route::patch('waiver-applications/{waiverApplication}/approve', [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'approve'])->name('waiver-applications.approve');
        Route::patch('waiver-applications/{waiverApplication}/reject', [\App\Http\Controllers\Admin\WaiverApplicationController::class, 'reject'])->name('waiver-applications.reject');
    });

    // ── 4. Students ─────────────────────────────────────────────────────
    Route::middleware('admin.module:students')->group(function () {
        Route::get('students/export-csv', [\App\Http\Controllers\Admin\StudentController::class, 'exportCsv'])->name('students.export-csv');
        Route::get('students/search-api', [\App\Http\Controllers\Admin\StudentController::class, 'searchApi'])->name('students.search-api');
        Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
        Route::get('students/{student}/impersonate', [\App\Http\Controllers\Admin\StudentController::class, 'impersonate'])->name('students.impersonate');
        Route::get('students/{student}/accounts', [\App\Http\Controllers\Admin\AccountsController::class, 'studentLedger'])->name('students.accounts');
        Route::get('students/{student}/grade-sheet', [\App\Http\Controllers\Admin\StudentController::class, 'printGradeSheet'])->name('students.grade-sheet');
        Route::get('students/{student}/certificate', [\App\Http\Controllers\Admin\StudentController::class, 'printCertificate'])->name('students.certificate');
        Route::get('students/{student}/id-card', [\App\Http\Controllers\Admin\StudentController::class, 'printIdCard'])->name('students.id-card');
        Route::post('students/{student}/toggle-course-access', [\App\Http\Controllers\Admin\StudentController::class, 'toggleCourseAccess'])->name('students.toggle-course-access');
        Route::post('students/{student}/cancel-admission', [\App\Http\Controllers\Admin\StudentController::class, 'cancelAdmission'])->name('students.cancel-admission');
        Route::post('students/{student}/reset-password', [\App\Http\Controllers\Admin\StudentController::class, 'resetPassword'])->name('students.reset-password');
        Route::post('students/{student}/adjust-fee-structure', [\App\Http\Controllers\Admin\StudentController::class, 'adjustFeeStructure'])->name('students.adjust-fee-structure');
    });

    // ── 5. Batches, Classes & Routine ───────────────────────────────────
    Route::middleware('admin.module:classes_batches')->group(function () {
        Route::resource('batches', \App\Http\Controllers\Admin\BatchController::class);
        Route::post('batches/{batch}/generate-sessions', [\App\Http\Controllers\Admin\BatchController::class, 'generateTimeline'])->name('batches.generateTimeline');
        Route::post('batches/{batch}/split-students', [\App\Http\Controllers\Admin\BatchController::class, 'autoSplitStudents'])->name('batches.split-students');
        Route::post('enrollments/{enrollment}/set-group', [\App\Http\Controllers\Admin\BatchController::class, 'setStudentGroup'])->name('enrollments.set-group');
        Route::resource('classes', \App\Http\Controllers\Admin\ClassSessionController::class);
        Route::put('classes/{class}/schedule', [\App\Http\Controllers\Admin\ClassSessionController::class, 'updateSchedule'])->name('classes.updateSchedule');
        Route::post('classes/{class}/generate-zoom', [\App\Http\Controllers\Admin\ClassSessionController::class, 'generateZoomLink'])->name('classes.generateZoom');
        Route::post('classes/{class}/complete', [\App\Http\Controllers\Admin\ClassSessionController::class, 'markComplete'])->name('classes.complete');
        Route::post('classes/{class}/cancel', [\App\Http\Controllers\Admin\ClassSessionController::class, 'markCancelled'])->name('classes.cancel');

        // Routine
        Route::get('routine', [\App\Http\Controllers\Admin\RoutineController::class, 'index'])->name('routine.index');
        Route::post('routine/entries', [\App\Http\Controllers\Admin\RoutineController::class, 'store'])->name('routine.entries.store');
        Route::put('routine/entries/{entry}', [\App\Http\Controllers\Admin\RoutineController::class, 'update'])->name('routine.entries.update');
        Route::post('routine/entries/{entry}/copy', [\App\Http\Controllers\Admin\RoutineController::class, 'copyEntry'])->name('routine.entries.copy');
        Route::delete('routine/entries/{entry}', [\App\Http\Controllers\Admin\RoutineController::class, 'destroy'])->name('routine.entries.destroy');
        Route::post('routine/auto-generate/{batch}', [\App\Http\Controllers\Admin\RoutineController::class, 'autoGenerate'])->name('routine.auto-generate');
        Route::get('routine/unassigned', [\App\Http\Controllers\Admin\RoutineController::class, 'unassigned'])->name('routine.unassigned');
        Route::post('routine/slots', [\App\Http\Controllers\Admin\RoutineController::class, 'storeSlot'])->name('routine.slots.store');
        Route::put('routine/slots/{slot}', [\App\Http\Controllers\Admin\RoutineController::class, 'updateSlot'])->name('routine.slots.update');
        Route::delete('routine/slots/{slot}', [\App\Http\Controllers\Admin\RoutineController::class, 'destroySlot'])->name('routine.slots.destroy');
    });

    // ── 6. Exams, Results, Retakes, Re-admissions & Promotions ──────────
    Route::middleware('admin.module:exams')->group(function () {
        Route::get('questions/template-download', [\App\Http\Controllers\Admin\QuestionController::class, 'downloadTemplate'])->name('questions.template-download');
        Route::get('questions/aiken-template-download', [\App\Http\Controllers\Admin\QuestionController::class, 'downloadAikenTemplate'])->name('questions.aiken-template-download');
        Route::resource('questions', \App\Http\Controllers\Admin\QuestionController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('questions/{question}/regrade', [\App\Http\Controllers\Admin\QuestionController::class, 'regrade'])->name('questions.regrade');
        Route::post('questions/bulk-upload', [\App\Http\Controllers\Admin\QuestionController::class, 'bulkUpload'])->name('questions.bulk-upload');
        Route::post('questions/aiken-upload', [\App\Http\Controllers\Admin\QuestionController::class, 'importAiken'])->name('questions.aiken-upload');

        Route::resource('exams', \App\Http\Controllers\Admin\ExamController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('exams/{exam}/regrade', [\App\Http\Controllers\Admin\ExamController::class, 'regradeAll'])->name('exams.regrade');
        Route::get('exams/{exam}/builder', [\App\Http\Controllers\Admin\ExamController::class, 'builder'])->name('exams.builder');
        Route::post('exams/{exam}/questions', [\App\Http\Controllers\Admin\ExamController::class, 'attachQuestion'])->name('exams.questions.attach');
        Route::post('exams/{exam}/questions/random', [\App\Http\Controllers\Admin\ExamController::class, 'attachRandomQuestions'])->name('exams.questions.random');
        Route::get('exams/{exam}/test-exam', [\App\Http\Controllers\Admin\ExamController::class, 'testExam'])->name('exams.test-exam');
        Route::post('exams/{exam}/test-exam/submit', [\App\Http\Controllers\Admin\ExamController::class, 'submitTestExam'])->name('exams.test-exam.submit');
        Route::delete('exams/{exam}/questions/{examQuestion}', [\App\Http\Controllers\Admin\ExamController::class, 'detachQuestion'])->name('exams.questions.detach');
        Route::delete('exams/{exam}/submissions/{submission}', [\App\Http\Controllers\Admin\ExamController::class, 'resetSubmission'])->name('exams.submissions.reset');
        Route::get('exam-appeals', [\App\Http\Controllers\Admin\ExamController::class, 'allAppeals'])->name('exams.appeals.index');
        Route::post('exam-appeals/{appeal}/approve', [\App\Http\Controllers\Admin\ExamController::class, 'approveAppeal'])->name('exams.appeals.approve');
        Route::post('exam-appeals/{appeal}/reject', [\App\Http\Controllers\Admin\ExamController::class, 'rejectAppeal'])->name('exams.appeals.reject');
        Route::get('retakes', [\App\Http\Controllers\Admin\SubjectRetakeController::class, 'index'])->name('retakes.index');
        Route::post('retakes', [\App\Http\Controllers\Admin\SubjectRetakeController::class, 'store'])->name('retakes.store');
        Route::patch('retakes/{retake}/approve', [\App\Http\Controllers\Admin\SubjectRetakeController::class, 'approve'])->name('retakes.approve');

        // Re-admissions
        Route::resource('readmissions', \App\Http\Controllers\Admin\ReadmissionController::class)->only(['index', 'store', 'destroy']);
        Route::post('readmissions/{readmission}/approve', [\App\Http\Controllers\Admin\ReadmissionController::class, 'approve'])->name('readmissions.approve');
        Route::post('readmissions/{readmission}/continue-retake', [\App\Http\Controllers\Admin\ReadmissionController::class, 'continueWithRetake'])->name('readmissions.continue-retake');
        Route::post('readmissions/auto-detect', [\App\Http\Controllers\Admin\ReadmissionController::class, 'autoDetect'])->name('readmissions.auto-detect');

        // Course Transfers
        Route::post('course-transfers/manual', [\App\Http\Controllers\Admin\CourseTransferController::class, 'manualTransfer'])->name('course-transfers.manual');
        Route::resource('course-transfers', \App\Http\Controllers\Admin\CourseTransferController::class)->only(['index']);
        Route::post('course-transfers/{courseTransfer}/approve', [\App\Http\Controllers\Admin\CourseTransferController::class, 'approve'])->name('course-transfers.approve');
        Route::post('course-transfers/{courseTransfer}/mark-paid', [\App\Http\Controllers\Admin\CourseTransferController::class, 'markPaid'])->name('course-transfers.mark-paid');
        Route::post('course-transfers/{courseTransfer}/reject', [\App\Http\Controllers\Admin\CourseTransferController::class, 'reject'])->name('course-transfers.reject');

        Route::resource('promotions', \App\Http\Controllers\Admin\PromotionController::class)->only(['index', 'store']);
        Route::post('promotions/bulk-promote', [\App\Http\Controllers\Admin\PromotionController::class, 'bulkPromote'])->name('promotions.bulk-promote');
        Route::post('promotions/send-readmission', [\App\Http\Controllers\Admin\PromotionController::class, 'sendReadmission'])->name('promotions.send-readmission');

        // Final Mark Generator & Manual Marking
        Route::get('final-marks', [\App\Http\Controllers\Admin\FinalMarkController::class, 'index'])->name('final-marks.index');
        Route::post('final-marks/generate', [\App\Http\Controllers\Admin\FinalMarkController::class, 'generate'])->name('final-marks.generate');
        Route::get('final-marks/export-csv', [\App\Http\Controllers\Admin\FinalMarkController::class, 'exportCsv'])->name('final-marks.export-csv');
        Route::post('final-marks/update-criteria', [\App\Http\Controllers\Admin\FinalMarkController::class, 'updateCriteria'])->name('final-marks.update-criteria');
        Route::patch('final-marks/{finalMark}/update-attendance', [\App\Http\Controllers\Admin\FinalMarkController::class, 'updateAttendance'])->name('final-marks.update-attendance');
        Route::get('final-marks/batch-subjects', [\App\Http\Controllers\Admin\FinalMarkController::class, 'getBatchSubjects'])->name('final-marks.batch-subjects');
        Route::post('final-marks/publish-toggle', [\App\Http\Controllers\Admin\FinalMarkController::class, 'publishToggle'])->name('final-marks.publish-toggle');
        Route::post('final-marks/auto-attendance', [\App\Http\Controllers\Admin\FinalMarkController::class, 'autoAttendance'])->name('final-marks.auto-attendance');
        Route::post('final-marks/{finalMark}/manual-mark', [\App\Http\Controllers\Admin\FinalMarkController::class, 'updateManualMark'])->name('final-marks.update-manual');

        // Result Book & 6-Semester Consolidated Transcript
        Route::get('result-book', [\App\Http\Controllers\Admin\ResultBookController::class, 'index'])->name('result-book.index');
        Route::post('result-book/{finalMark}/override', [\App\Http\Controllers\Admin\ResultBookController::class, 'override'])->name('result-book.override');
        Route::post('result-book/publish-exam', [\App\Http\Controllers\Admin\ResultBookController::class, 'publishExam'])->name('result-book.publish-exam');
        Route::get('students/{student}/transcript', [\App\Http\Controllers\Admin\ResultBookController::class, 'transcript'])->name('students.transcript');
    });

    // ── 7. Accounts & Financials ────────────────────────────────────────
    Route::middleware('admin.module:accounts')->group(function () {
        Route::get('accounts', [\App\Http\Controllers\Admin\AccountsController::class, 'dashboard'])->name('accounts.dashboard');
        Route::get('accounts/invoices', [\App\Http\Controllers\Admin\AccountsController::class, 'invoices'])->name('accounts.invoices');
        Route::post('accounts/invoices', [\App\Http\Controllers\Admin\AccountsController::class, 'storeInvoice'])->name('accounts.invoices.store');
        Route::put('accounts/invoices/{invoice}', [\App\Http\Controllers\Admin\AccountsController::class, 'updateInvoice'])->name('accounts.invoices.update');
        Route::patch('accounts/invoices/{invoice}/status', [\App\Http\Controllers\Admin\AccountsController::class, 'updateInvoiceStatus'])->name('accounts.invoices.status');
        Route::delete('accounts/invoices/{invoice}', [\App\Http\Controllers\Admin\AccountsController::class, 'destroyInvoice'])->name('accounts.invoices.destroy');
        Route::post('accounts/invoices/{invoice}/collect', [\App\Http\Controllers\Admin\AccountsController::class, 'collectPayment'])->name('accounts.invoices.collect');
        Route::post('accounts/apply-activation-fees', [\App\Http\Controllers\Admin\AccountsController::class, 'applyActivationFees'])->name('accounts.apply-activation-fees');
        Route::get('accounts/fee-structures', [\App\Http\Controllers\Admin\AccountsController::class, 'feeStructures'])->name('accounts.fee-structures');
        Route::post('accounts/fee-structures', [\App\Http\Controllers\Admin\AccountsController::class, 'storeFeeStructure'])->name('accounts.fee-structures.store');
        Route::get('accounts/reports', [\App\Http\Controllers\Admin\AccountsController::class, 'reports'])->name('accounts.reports');
        Route::get('accounts/payments/{payment}/receipt', [\App\Http\Controllers\Admin\AccountsController::class, 'printReceipt'])->name('accounts.payments.receipt');
        Route::post('accounts/payments/{payment}/approve', [\App\Http\Controllers\Admin\AccountsController::class, 'approvePayment'])->name('accounts.payments.approve');
        Route::post('accounts/payments/{payment}/reject', [\App\Http\Controllers\Admin\AccountsController::class, 'rejectPayment'])->name('accounts.payments.reject');
    });

    // ── 8. Communication, Surveys & Notices ─────────────────────────────
    Route::middleware('admin.module:communication')->group(function () {
        Route::resource('notices', \App\Http\Controllers\Admin\NoticeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('notifications', [\App\Http\Controllers\Admin\BroadcastNotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/create', [\App\Http\Controllers\Admin\BroadcastNotificationController::class, 'create'])->name('notifications.create');
        Route::get('notifications/{notification}/json', [\App\Http\Controllers\Admin\BroadcastNotificationController::class, 'showJson'])->name('notifications.show-json');
        Route::post('notifications', [\App\Http\Controllers\Admin\BroadcastNotificationController::class, 'send'])->name('notifications.send');

        // Surveys & Dynamic Forms
        Route::get('surveys', [\App\Http\Controllers\Admin\SurveyController::class, 'index'])->name('surveys.index');
        Route::post('surveys', [\App\Http\Controllers\Admin\SurveyController::class, 'store'])->name('surveys.store');
        Route::get('surveys/{survey}/builder', [\App\Http\Controllers\Admin\SurveyController::class, 'builder'])->name('surveys.builder');
        Route::put('surveys/{survey}/builder', [\App\Http\Controllers\Admin\SurveyController::class, 'saveBuilder'])->name('surveys.builder.save');
        Route::match(['PATCH', 'POST', 'GET'], 'surveys/{survey}/toggle-status', [\App\Http\Controllers\Admin\SurveyController::class, 'toggleStatus'])->name('surveys.toggle-status');
        Route::get('surveys/{survey}/responses', [\App\Http\Controllers\Admin\SurveyController::class, 'responses'])->name('surveys.responses');
        Route::get('surveys/{survey}/responses/csv', [\App\Http\Controllers\Admin\SurveyController::class, 'exportCsv'])->name('surveys.responses.csv');
        Route::delete('surveys/{survey}', [\App\Http\Controllers\Admin\SurveyController::class, 'destroy'])->name('surveys.destroy');
    });

    // ── 9. Support Desk & Departments ───────────────────────────────────
    Route::middleware('admin.module:support')->group(function () {
        Route::get('support-tickets', [\App\Http\Controllers\Admin\AdminSupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::get('support-tickets/export', [\App\Http\Controllers\Admin\AdminSupportTicketController::class, 'exportCsv'])->name('support-tickets.export');
        Route::get('support-tickets/{ticket}', [\App\Http\Controllers\Admin\AdminSupportTicketController::class, 'show'])->name('support-tickets.show');
        Route::match(['PATCH', 'POST'], 'support-tickets/{ticket}/reassign', [\App\Http\Controllers\Admin\AdminSupportTicketController::class, 'reassign'])->name('support-tickets.reassign');
        Route::resource('support-departments', \App\Http\Controllers\Admin\SupportDepartmentController::class);
        Route::patch('support-departments/{supportDepartment}/toggle', [\App\Http\Controllers\Admin\SupportDepartmentController::class, 'toggleStatus'])->name('support-departments.toggle');
        Route::resource('support-agents', \App\Http\Controllers\Admin\SupportUserController::class);
    });

    // ── 10. Settings, Reports & System Config ───────────────────────────
    Route::middleware('admin.module:settings')->group(function () {
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // Google Auth Setup
        Route::get('settings/google-auth', [\App\Http\Controllers\Admin\GoogleAuthSettingController::class, 'index'])->name('settings.google-auth.index');
        Route::post('settings/google-auth', [\App\Http\Controllers\Admin\GoogleAuthSettingController::class, 'update'])->name('settings.google-auth.update');

        // Notification Settings
        Route::get('settings/notifications', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'index'])->name('settings.notifications.index');
        Route::post('settings/notifications/firebase', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'updateFirebase'])->name('settings.notifications.firebase');
        Route::post('settings/notifications/smtp', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'updateSmtp'])->name('settings.notifications.smtp');
        Route::post('settings/notifications/test-mail', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'sendTestMail'])->name('settings.notifications.test-mail');

        // Payment Gateway Settings
        Route::get('settings/payment-gateways', [\App\Http\Controllers\Admin\PaymentGatewaySettingController::class, 'index'])->name('settings.payment-gateways.index');
        Route::post('settings/payment-gateways/sslcommerz', [\App\Http\Controllers\Admin\PaymentGatewaySettingController::class, 'updateSslcommerz'])->name('settings.payment-gateways.sslcommerz');
        Route::post('settings/payment-gateways/bkash', [\App\Http\Controllers\Admin\PaymentGatewaySettingController::class, 'updateBkash'])->name('settings.payment-gateways.bkash');

        // Fee Heads
        Route::get('fee-heads', [\App\Http\Controllers\Admin\FeeHeadController::class, 'index'])->name('fee-heads.index');
        Route::post('fee-heads', [\App\Http\Controllers\Admin\FeeHeadController::class, 'store'])->name('fee-heads.store');
        Route::put('fee-heads/{feeHead}', [\App\Http\Controllers\Admin\FeeHeadController::class, 'update'])->name('fee-heads.update');
        Route::delete('fee-heads/{feeHead}', [\App\Http\Controllers\Admin\FeeHeadController::class, 'destroy'])->name('fee-heads.destroy');

        // Course Fee Packages
        Route::post('courses/{course}/packages', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'store'])->name('courses.packages.store');
        Route::put('courses/packages/{package}', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'update'])->name('courses.packages.update');
        Route::delete('courses/packages/{package}', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'destroy'])->name('courses.packages.destroy');
        Route::patch('courses/{course}/packages/{package}/set-default', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'setDefault'])->name('courses.packages.set-default');
        Route::post('courses/packages/{package}/items', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'storeItem'])->name('courses.packages.items.store');
        Route::put('courses/packages/items/{item}', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'updateItem'])->name('courses.packages.items.update');
        Route::delete('courses/packages/items/{item}', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'destroyItem'])->name('courses.packages.items.destroy');
        Route::post('courses/{course}/packages/from-template', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'fromTemplate'])->name('courses.packages.from-template');
        Route::post('courses/{course}/packages/{package}/clone', [\App\Http\Controllers\Admin\CourseFeePackageController::class, 'clonePackage'])->name('courses.packages.clone');

        // App Settings
        Route::get('app-settings', [\App\Http\Controllers\Admin\AppSettingController::class, 'index'])->name('app-settings.index');
        Route::put('app-settings/global', [\App\Http\Controllers\Admin\AppSettingController::class, 'updateSettings'])->name('app-settings.update');
        Route::post('app-settings/blood-groups', [\App\Http\Controllers\Admin\AppSettingController::class, 'storeBloodGroup'])->name('app-settings.blood-groups.store');
        Route::delete('app-settings/blood-groups/{bloodGroup}', [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyBloodGroup'])->name('app-settings.blood-groups.destroy');
        Route::post('app-settings/religions', [\App\Http\Controllers\Admin\AppSettingController::class, 'storeReligion'])->name('app-settings.religions.store');
        Route::delete('app-settings/religions/{religion}', [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyReligion'])->name('app-settings.religions.destroy');
        Route::post('app-settings/divisions', [\App\Http\Controllers\Admin\AppSettingController::class, 'storeDivision'])->name('app-settings.divisions.store');
        Route::delete('app-settings/divisions/{division}', [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyDivision'])->name('app-settings.divisions.destroy');
        Route::post('app-settings/districts', [\App\Http\Controllers\Admin\AppSettingController::class, 'storeDistrict'])->name('app-settings.districts.store');
        Route::delete('app-settings/districts/{district}', [\App\Http\Controllers\Admin\AppSettingController::class, 'destroyDistrict'])->name('app-settings.districts.destroy');
    });

    // ── 11. Admin User & Role Management ────────────────────────────────
    Route::middleware('admin.module:user_management')->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserManagementController::class);
        Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // ── Command Runner ──────────────────────────────────────────────────
    Route::get('command', [\App\Http\Controllers\Admin\CommandRunnerController::class, 'index'])->name('command.run.index');
    Route::post('command', [\App\Http\Controllers\Admin\CommandRunnerController::class, 'run'])->name('command.run');
});
