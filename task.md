# Client Feedback Implementation Tasks (15-Page Document)
*Note: Item 1 (Coupon Code) is skipped per user directive ("1 bade sob koro").*

## Task Overview & Progress Summary

| # | Task | Category | Status | Verification Method |
|---|------|----------|--------|---------------------|
| 32 | Retake Student Selection via Live Roll/ID Search & Searchable Dropdowns | Exams / Retakes | COMPLETED | `scratch/verify_retake_student_search.php` |
| 33 | Admin Manual Course Transfer Action | Courses / Transfers | COMPLETED | `scratch/verify_admin_manual_course_transfer.php` |
| 34 | Notice Board & Broadcast Notification Enhancements (Edit Notice, Full View & Schedule) | Communications | COMPLETED | `scratch/verify_notice_and_notification_enhancements.php` |
| 35 | Teacher Dashboard Weekly Routine Calendar & Admin Routine Slot Copy/Edit | Routine / Schedule | COMPLETED | `scratch/verify_teacher_routine_and_admin_routine_actions.php` |
| 36 | Special Courses Common User Account (Read-only Access) | Accounts / Special Courses | COMPLETED | `scratch/verify_special_courses_common_account.php` |
| 37 | Gender Simplification (Male/Female only) & Religion Removal Cleanup | Admission / Student Profile | COMPLETED | `scratch/verify_gender_and_religion_cleanup.php` |
| 38 | Re-Exam Appeals Post-Deadline Fee & Payment Gate | Exams / Appeals & Fees | COMPLETED | `scratch/verify_reexam_appeal_fee_system.php` |
| 39 | Multiple Recorded Class Videos with Manual Titles, Edit/Upload & In-Portal Player | Classroom / Recorded Lectures | COMPLETED | `scratch/verify_recorded_videos.php` |
| 40 | Fix StudentController::create() Undefined Method & Admission Link | Students / Admissions | COMPLETED | `scratch/verify_student_create_route.php` |
| 41 | Extra Class System Outside Routine (Schedule, Filter, Badges & Portals) | Routine / Classes | COMPLETED | `scratch/verify_extra_class_system.php` |
| 42 | Student Class Records Section, "আমার বিষয়" Removal & Admin/Teacher Session Upload | Classroom / Recordings | COMPLETED | `scratch/verify_class_recording_system.php` |
| 48 | Exam-wise Tamrin Marks & Semester-wise Attendance Across Result System | Exams / Results | COMPLETED | `scratch/verify_exam_wise_tamrin_system.php` |
| 49 | Universal Modal Scrolling & Backdrop/Escape Dismiss Across All Portals | UI / Modals | COMPLETED | `scratch/verify_result_book_and_modals_e2e.php` |
| 50 | Email Template Selector, Quick Dropdown, Library Modal & Auto-Insertion in Message Body | Communications / Notifications | COMPLETED | `scratch/verify_email_template_system.php` |
| 51 | Fix Table Horizontal Scroll & Card Overflow Clipping on Batches, Teachers, and Tickets | UI / Tables | COMPLETED | `scratch/verify_table_scroll_fix.php` |
| 52 | Promotion Menu Fatal Crash Fix (`admin.final-marks.batch-subjects`) | Results / Promotions | COMPLETED | `scratch/test_promotion_all.php` |
| 53 | Result Mark Edit / Override Crash Fix (`ResultBookController@overrideSave`) | Results / Mark Entry | COMPLETED | `scratch/test_result_override.php` |
| 54 | Assignment Publish Constraint & Status Fix (Nullable Teacher, Status Enum) | Assignments | COMPLETED | `scratch/test_assignment_store.php` |
| 55 | New Admission Store Error & Profile Deduplication / FK Sanitization | Admissions | COMPLETED | `scratch/test_admission_full.php` |
| 56 | Question Bank Random Subset Selection & Sequence Shuffling Per Student | Exams / Question Bank | COMPLETED | `scratch/test_exam_shuffling.php` |
| 57 | Teacher Panel Menu Cleanup (Remove Exams/Appeals, Show Assigned Subjects & Routine) | Teacher Portal | COMPLETED | `scratch/test_teacher_panel.php` |
| 58 | Course Duration & Tuition Fee Cycle Definition for Courses and Batches | Courses / Batches | COMPLETED | `scratch/test_course_batch_cycles.php` |
| 59 | Remove "Other Subjects" Section & Prevent Duplicate Course Subject Mappings | Courses / Subjects | COMPLETED | `scratch/verify_no_other_subjects.php` |
| 60 | Batch Unique Code Collision Fix & Same Date/Month Duplicate Validation | Batches | COMPLETED | `scratch/test_batch_validation.php` |
| 61 | Search Bar for Subject Mapping & Teacher Assignment Dropdowns | Subjects / Course Mapping | COMPLETED | `scratch/test_subject_mapping_search.php` |
| 62 | Student ID Architecture Correction (YY-BB-CC-G-RRRR) & Semester Subject Modal Multi-Select | Students / Course Mapping | COMPLETED | `scratch/verify_student_id_and_semester_subject_modal.php` |
| 63 | Poor Fund Application Success Screen Preservation Notice & Copy Button | Poor Fund / Admissions | COMPLETED | `scratch/verify_poor_fund_success_copy.php` |

---

### Task 32: Retake Student Selection via Live Roll/ID Search & Searchable Dropdowns
- **Objective**: Improve retake registration modal in `admin/retakes/index.blade.php` so admins can search students in real-time by student roll/ID (without hyphen/dash sensitivity), name, phone, or email, displaying student batch, course, gender, and failed subjects.
- **Definition of Done (DoD)**:
  - `students/search-api` route registered in `routes/admin.php` and functional.
  - `StudentController::searchApi` returns student details with failed subjects.
  - Modal provides instant search input with dynamic dropdown suggestions and selection chip.
  - Verification script executes and returns Exit Code 0.
- **Status**: COMPLETED (16/16 assertions passed, Exit Code 0)

---

### Task 33: Admin Manual Course Transfer Action
- **Objective**: Allow admins to manually transfer a student between courses/batches directly from the course transfer interface, without waiting for a student-submitted request.
- **Definition of Done (DoD)**:
  - Admin modal/action in `admin/course-transfers/index.blade.php` with manual transfer form.
  - Controller handles transferring enrollment from old course/batch to new course/batch, recording the transfer history and ledger updates.
  - Route `admin.course-transfers.manual-store` registered and secured.
  - Automated verification returns Exit Code 0.
- **Status**: COMPLETED (16/16 assertions passed, Exit Code 0)

---

### Task 34: Notice Board & Broadcast Notification Enhancements (Edit Notice, Full View & Schedule)
- **Objective**: Enable editing existing notices, viewing full broadcast notification contents in a modal, and scheduling broadcast notifications.
- **Definition of Done (DoD)**:
  - `edit` and `update` methods in `NoticeController` with edit modal in notice board.
  - Broadcast notification table includes full-view modal for long messages and scheduled dispatch support.
  - Automated verification returns Exit Code 0.
- **Status**: COMPLETED (19/19 assertions passed, Exit Code 0)

---

### Task 35: Teacher Dashboard Weekly Routine Calendar & Admin Routine Slot Copy/Edit
- **Objective**: Display a weekly timetable calendar on the teacher dashboard (`teacher/dashboard.blade.php`) and provide quick duplicate/copy and edit actions on routine slots in admin view.
- **Definition of Done (DoD)**:
  - Weekly routine calendar grid on teacher dashboard showing day-wise slots with subject and batch.
  - Admin routine index offers copy slot action and inline/modal edit action.
  - Automated verification returns Exit Code 0.
- **Status**: COMPLETED (14/14 assertions passed, Exit Code 0)

---

### Task 36: Special Courses Common User Account (Read-only Access)
- **Objective**: Create a common student account for special courses that allows multiple users to log in with shared credentials to view lectures while preventing profile/password tampering.
- **Definition of Done (DoD)**:
  - Flag `is_common_account` on student model/user.
  - When logged in as common account, student cannot edit profile, change email/password, or cancel enrollment.
  - Admin UI indicates common account status.
  - Automated verification returns Exit Code 0.
- **Status**: COMPLETED (16/16 assertions passed, Exit Code 0)

---

### Task 37: Gender Simplification (Male/Female only) & Religion Removal Cleanup
- **Objective**: Clean up forms across student admission, teacher profiles, and student edits to only support Male/Female (removing "Other" or non-standard options) and remove Religion fields as per Bangladeshi cultural and institutional rules.
- **Definition of Done (DoD)**:
  - Gender select fields only offer "পুরুষ (Male)" and "মহিলা (Female)".
  - Religion fields removed from admission forms, student create/edit forms, and public registration.
  - Automated verification returns Exit Code 0.
- **Status**: COMPLETED (17/17 assertions passed, Exit Code 0)

---

### Task 38: Re-Exam Appeals Post-Deadline Fee & Payment Gate
- **Objective**: If a student appeals for an exam after the exam's end date (or missed/expired exam), allow admin to set a custom re-exam fee upon approving the appeal. An invoice is automatically generated, and the student must pay the fee before they are permitted to take the exam.
- **Definition of Done (DoD)**:
  - Migration added to store `fee_amount`, `payment_status`, and `invoice_id` in `exam_appeals`.
  - Admin appeals view highlights expired exams and provides an approval modal with fee input.
  - `AccountingService::createReExamAppealInvoice` creates an invoice with category `RE_EXAM` and due amount.
  - Paying the invoice automatically updates the appeal's `payment_status` to `PAID`.
  - Student `take()` route blocks exam attempt if the approved appeal has an unpaid fee.
  - Student exams index view shows fee status, direct "Pay Fee" button, and allows submitting appeals on expired exams.
  - Automated verification script returns Exit Code 0.
- **Status**: COMPLETED (25/25 assertions passed, Exit Code 0)
 
---

### Task 39: Multiple Recorded Class Videos with Manual Titles, Edit/Upload & In-Portal Player
- **Objective**: Enable adding multiple recorded class videos per subject module with individual custom titles, support editing and replacing videos/files, and provide an in-portal iframe/HTML5 player on the student classroom portal with playlist switcher.
- **Definition of Done (DoD)**:
  - Migration added to store `recorded_videos` JSON array in `subject_modules`.
  - `SubjectModule` model has accessors `videos`, `has_recorded_videos`, `video_count`, and `$appends` with full backward compatibility for legacy single-video columns.
  - `SubjectModuleController` processes dynamic multiple videos, direct file uploads, YouTube/Vimeo/Drive URLs, iframe embed codes, and cleans up deleted files on update/delete.
  - Admin modal allows dynamically adding/removing video rows with title, URL, file, or embed code. Existing videos are loaded and editable in edit modal.
  - Student view lists all class videos under each module with individual titles, and renders an in-portal responsive player with playlist selector bar for seamless switching.
  - Automated verification script returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0, all assertions passed)

---

### Task 40: Fix StudentController::create() Undefined Method & Admission Link
- **Objective**: Fix the HTTP 500 error `Call to undefined method App\Http\Controllers\Admin\StudentController::create()` triggered when clicking "নতুন শিক্ষার্থী ভর্তি" on `admin/students` or directly accessing `/admin/students/create`.
- **Definition of Done (DoD)**:
  - Implement `create()` in `StudentController` that redirects to `admin.admissions.create`.
  - Implement `store()` in `StudentController` delegating to `AdmissionController@store`.
  - Update the "নতুন শিক্ষার্থী ভর্তি" button in `resources/views/admin/students/index.blade.php` to link directly to `route('admin.admissions.create')`.
  - Automated verification returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0, all assertions passed)

---

### Task 41: Extra Class System Outside Routine (Schedule, Filter, Badges & Portals)
- **Objective**: Enable scheduling extra/makeup classes outside recurring weekly routines. Both Admins and Teachers can schedule extra classes for specific batches and groups (Brother/Sister/All) with title, reason, date/time, and live meeting links. Extra classes appear highlighted with badges in Class Sessions, Admin & Teacher Routine views, and Student Routine & Class portals.
- **Definition of Done (DoD)**:
  - Migration adds `is_extra_class` (boolean default false), `title`, `end_time`, and `reason` to `class_sessions`.
  - `ClassSession` model updated with casts, `is_extra` accessor, `class_type_label` attribute, and scopes `scopeExtra()` and `scopeRegular()`.
  - Admin `ClassSessionController` updated with extra class scheduling modal, type filter (`all`, `extra`, `regular`), and deletion support.
  - Admin `RoutineController` queries upcoming extra classes and shows a quick-schedule modal and notice ribbon.
  - Teacher `ClassController` and `RoutineController` allow teachers to schedule extra classes via `teacher.classes.extra.store`, view upcoming extra classes with direct conduct links, and display an extra class badge.
  - Student `RoutineController` and `ClassController` show upcoming extra classes prominently on student routine view and class lists with `[এক্সট্রা ক্লাস]` badges and direct Join Class links.
  - Automated end-to-end verification script `scratch/verify_extra_class_system.php` executes and returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0, all tests passed)

---

### Task 42: Student Class Records Section, "আমার বিষয়" Removal & Admin/Teacher Session Upload
- **Objective**: In the student panel, remove "আমার বিষয়" (Subjects) from sidebar navigation and replace it with a dedicated "ক্লাস রেকর্ড (Class Records)" section. Allow Admin and Teachers to upload/edit recorded class videos for every class session (supporting YouTube, Google Drive, Vimeo, MP4 file uploads, and iframe embed codes, with multi-part support). Provide an in-portal video player on the student portal to browse and play recorded class videos.
- **Definition of Done (DoD)**:
  - Migration adds `recording_url`, `recording_file`, `recording_embed`, `recorded_videos`, and `has_recording` to `class_sessions`.
  - `ClassSession` model updated with casts, `videos` accessor with automatic embed conversion for YouTube, Google Drive, Vimeo, and files, `has_recorded_videos`, `video_count`, and `scopeWithRecording()`.
  - Admin `ClassSessionController@updateRecording` and view `admin/classes/show` updated with "ক্লাস রেকর্ড" card and dynamic multi-part video upload modal.
  - Teacher `ClassController@updateRecording` and `markComplete` updated with recording upload support; `teacher/classes/index` has a "রেকর্ড আপলোড / এডিট" modal and button.
  - Student sidebar navigation updated: "আমার বিষয়" removed, replaced with "ক্লাস রেকর্ড (Class Records)" (`student.recordings.index`).
  - Student `ClassRecordingController` handles `index` (filtered by subject/batch with search) and `show` (in-portal responsive video player with multi-part playlist switcher).
  - Student `student/classes/show` view displays recording availability prompt and direct watch button.
  - Automated verification script `scratch/verify_class_recording_system.php` executes and returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0, all 8 assertions passed)---

### Task 43: Question Bank Bulk & Aiken Upload Categorization Fields (Subject, Difficulty, Exam Type, Tag, Batch, Semester)
- **Objective**: Ensure Question Bank bulk upload modals (`bulkUploadModal` and `aikenUploadModal`) have all 6 standard categorization fields:
  1. বিষয় (Subject) — `-- বিষয় নির্বাচন করুন --`
  2. কঠিনতা (Difficulty) — `Easy (সহজ) / Medium (মধ্যম) / Hard (কঠিন)`
  3. পরীক্ষার ধরন (Exam Type) — `-- ধরন নির্বাচন করুন --` (CT, MID, FINAL, QUIZ, PRACTICE)
  4. প্রশ্ন সেট / সোর্স ট্যাগ (Source Tag) — `যেমন: সেট ক, ২০২৪ ফাইনাল`
  5. ব্যাচ (Batch) (ঐচ্ছিক) — `-- সকল ব্যাচের জন্য প্রযোজ্য --`
  6. সেমিস্টার (Semester) (ঐচ্ছিক) — `-- সকল সেমিস্টারের জন্য প্রযোজ্য --`
- **Definition of Done (DoD)**:
  - `bulkUploadModal` has all 6 fields in a clean 2x3 grid with `font-family:'Kalpurush',sans-serif`.
  - `aikenUploadModal` has all 6 fields in a clean 2x3 grid with `font-family:'Kalpurush',sans-serif`.
  - Batch-to-Semester cascading dynamically filters semester options based on selected batch for both upload modals.
  - `QuestionController@bulkUpload` validates and stores `subject_id`, `batch_id`, `semester_id`, `difficulty`, `exam_type`, and `source_tag` for both MCQ and Written questions.
  - `QuestionController@importAiken` validates and stores `subject_id`, `batch_id`, `semester_id`, `difficulty`, `exam_type`, and `source_tag`.
  - Automated verification test script `scratch/verify_question_upload_fields.php` executes and exits with code 0.
- **Status**: COMPLETED (Exit Code 0, all assertions passed)

---

### Task 44: MCQ Option Shuffling with Persistent Mapping, Accurate Grading & Result Synchronization
- **Objective**: Randomize/shuffle the options of each MCQ question when presented to students during an exam, ensuring:
  1. The options are shuffled into a clean A, B, C, D order per student per question.
  2. The arrangement is persistent on `ExamSubmission` (`shuffled_options` JSON column) so reloads/reconnects don't reshuffle midway.
  3. The student's chosen option letter is graded against the new letter location of the correct answer for that student's shuffled arrangement.
  4. The result page (`result.blade.php`) renders options in the exact same shuffled sequence shown during the exam with accurate tags: `(আপনার উত্তর - সঠিক ✓)`, `(আপনার উত্তর - ভুল ✗)`, and `(সঠিক উত্তর)`.
  5. Negative marking, test exam mode, and admin/question regrade operations properly map against `shuffled_options`.
- **Definition of Done (DoD)**:
  - Database migration adds `shuffled_options` JSON column to `exam_submissions` table.
  - `ExamSubmission` model casts `shuffled_options` as `array`.
  - `Student\ExamController@take` initializes and persists shuffled options per question for the student submission.
  - `Student\ExamController@submit` evaluates MCQ answers against the student's shuffled `correct_option_id`.
  - `take.blade.php` renders options using the student's persistent shuffled list.
  - `result.blade.php` renders options using the student's persistent shuffled list and highlights user answers and correct answers accurately.
  - `Admin\ExamController@regradeAll`, `Admin\QuestionController@performRegrade`, and test exam modes support `shuffled_options`.
  - Automated verification test script `scratch/verify_shuffled_options_system.php` executes and exits with code 0.
- **Status**: COMPLETED (Exit Code 0, all assertions passed)

---

### Task 45: Batch Management List Course-wise and Session-wise Filtering
- **Objective**: Implement Course-wise (`course_id`) and Session-wise (`academic_year_id`) filtering on the Admin Batch Management page (`admin/batches`), along with status filtering and text search, while displaying the Session/Academic Year column in the batches table with 'Kalpurush' font.
- **Definition of Done (DoD)**:
  - `Admin\BatchController@index` accepts `course_id`, `academic_year_id`, `status`, and `search` query parameters and filters batches accordingly.
  - `AcademicYear` model has `batches()` relationship.
  - `admin/batches/index.blade.php` includes a clean filter card with Course dropdown (`-- সকল কোর্স (All Courses) --`), Session dropdown (`-- সকল সেশন (All Sessions) --`), Status dropdown, search input, Filter button, and Reset button.
  - Batches table displays Session column (`{{ $batch->academicYear->name ?? '—' }}`) and updated badges.
  - Automated verification test script `scratch/verify_batch_filtering.php` executes and exits with code 0.
- **Status**: COMPLETED (Exit Code 0, all assertions passed)

### Task 46: Add Fee Option in Student Fees Module for Admin
- **Objective**: Implement an "Add Fee" (+ নতুন ফি যোগ করুন) option on the student fees view (`student/fees`) for administrators, allowing admins to add custom fee particulars (e.g. Late Fee, Retake Fee, Certificate Fee, ID Card Fee, Fine, Custom Fee) with amount and remarks, persisting to the invoice and rendering dynamically in Step 1 particulars table with 'Kalpurush' font.
- **Definition of Done (DoD)**:
  - Add "➕ নতুন ফি যোগ করুন (Add Fee)" buttons in the Admin banner and alongside the status filters in `resources/views/student/fees/index.blade.php`.
  - Create the `adminAddFeeModal` with presets and custom particular input, amount input, and remarks input using 'Kalpurush' font.
  - Implement `FeeController::storeParticular` and `FeeController::deleteParticular` routes and controller methods with admin authorization, invoice updating, and AuditLog recording.
  - Update `FeeController::index` to display custom added fee particulars in `$step1Particulars` table with custom badges, inline edit buttons, and pay checkboxes.
  - Verification Method: Automated test script `scratch/verify_add_fee_option.php` verifying authorization, fee addition, amount persistence, deletion, and blade view rendering. Exit code 0 required.
- **Status**: COMPLETED (Exit Code 0, all assertions passed)

---

### Task 47: Fix Modal Scrolling and Click-Outside to Close in Student Fees Page
- **Objective**: Fix scrolling issues in the payment modal (`payInvoiceModal`) and admin fee modals (`adminParticularEditModal`, `adminAddFeeModal`) on `student/fees` so the full content and submit button are accessible on all viewport heights, and enable clicking outside the modal dialog (on the backdrop) and pressing ESC to close the modal.
- **Definition of Done (DoD)**:
  - Add `overflow-y: auto` and proper max-height / flex scrolling to `payInvoiceModal`, `adminParticularEditModal`, and `adminAddFeeModal` containers and dialog cards so content never gets cut off.
  - Implement click-outside backdrop event handlers on all three modals to close them when clicked outside.
  - Implement `Escape` key listener to close active modals.
  - Verification Method: Automated verification test script `scratch/verify_modal_scroll_and_backdrop_close.php` checking CSS scrolling rules, backdrop click handlers, and escape key listener. Exit code 0 required.
- **Status**: COMPLETED (Exit Code 0, all assertions passed)

---

### Task 48: Make Tamrin Marks Exam-wise and Keep Attendance Mark Semester-wise Across Entire Result System
- **Objective**: Refactor the Tamrin and Attendance marking architecture so that Tamrin (তামরিন) marks are strictly exam-wise (linked to Class Test, Midterm, Final exams or specific exams in `results` and `final_marks`), while Attendance (উপস্থিতি) marks remain strictly semester-wise (10 marks per semester), propagating through all result views (Manual Marking Tab 2, Tabulation Tab 1, Edit Modal, Student Portal, Teacher Portal, and FinalMark calculations).
- **Definition of Done (DoD)**:
  - Add migration for `ct_tamrin`, `midterm_tamrin`, `final_tamrin` in `final_marks` table.
  - Update `FinalMark` model to cast and calculate `total_mark = round(class_test_converted + midterm_converted + final_converted + attendance_converted, 2)` (out of 100), where Tamrin marks are components of the respective exams, and attendance is purely semester-level.
  - In `ResultBookController` Tab 2 (`manual_marking`):
    - Enhance filter to include Exam selector (`-- সকল পরীক্ষা (পরীক্ষাভিত্তিক তামরিন ও সেমিস্টার উপস্থিতি) --` or specific exam).
    - In All-Exams view: render exam-wise Tamrin columns (সিটি তামরিন, মিডটার্ম তামরিন, ফাইনাল তামরিন) alongside single semester attendance column (`উপস্থিতি নম্বর (/১০)`) and semester total (`/১০০`).
    - In Specific Exam view: render exam evaluation sheet (MCQ, Written, Tamrin, Viva, Total).
    - In `saveManualMarksBulk`: save exam-wise Tamrin into `results` table (`tamrin_marks`), sync exam totals into `FinalMark` (`class_test_obtained`, `midterm_obtained`, `final_obtained`), save semester `attendance_converted`, and recalculate final marks and merit ranks.
  - Update `Teacher\ResultController@store` to sync exam results (including Tamrin) into `FinalMark`.
  - Update `openEditMarkModal` and student result views to reflect exam-wise Tamrin and semester-wise attendance.
  - Verification Method: Automated verification test script `scratch/verify_exam_wise_tamrin_system.php` verifying schema, exam-wise Tamrin persistence, semester-wise attendance, total calculation, Tab 2 submission, and blade rendering. Exit code 0 required.
- **Status**: COMPLETED (Exit Code 0, all tests passed)

---

### Task 49: Universal Modal Scrolling and Backdrop/Escape Click-Outside Dismiss Across All System Portals
- **Objective**: Safeguard all modals across Admin, Teacher, Student, and Support portals against unscrollable viewports (flexbox clipping) and ensure clicking outside on the backdrop or pressing the Escape key reliably closes the modal.
- **Definition of Done (DoD)**:
  - Universal CSS in `app.css`, `admin/layouts/app.blade.php`, `student/layouts/app.blade.php`, `teacher/layouts/app.blade.php`, and `support/layouts/app.blade.php` applying:
    - Overlay/Backdrop: `position: fixed !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; align-items: flex-start !important; justify-content: center !important; padding: 30px 15px !important; z-index: 99999 !important;`.
    - Inner Modal Box: `margin: auto !important; max-height: calc(100vh - 60px) !important; overflow-y: auto !important;`.
  - Universal `openModal` and `closeModal` functions handling classes (`open`, `active`, `show`) and inline `display` (`flex` / `none`) simultaneously so modals never get stuck.
  - Universal Backdrop Click Listener intercepting all backdrop clicks (`modal-overlay`, `modal-backdrop`, `modal-wrapper`, `admin-modal-overlay`, or fixed modal elements) and closing them.
  - Universal `Escape` key listener closing all open modals and restoring body overflow.
  - Verification Method: Automated verification test scripts `scratch/verify_exam_wise_tamrin_system.php` and `scratch/verify_result_book_and_modals_e2e.php` returning Exit Code 0.
- **Status**: COMPLETED (Exit Code 0, all tests passed)

### Task 50: Email Template Selector, Quick Dropdown, Library Modal & Auto-Insertion in Message Body
- **Objective**: Add an email template selector and management system to Broadcast Notification composer (`admin/notifications/create`), enabling admins to quickly select from predefined standard academic templates or custom templates, auto-populating into `Message Body / Content *` (and optionally title/subject), with live preview updates, variable tag insertion (`{name}`, `{roll}`, `{course}`, `{batch}`, `{date}`), and a save-as-template feature.
- **Definition of Done (DoD)**:
  - Migration created `2026_09_28_181000_create_email_templates_table.php` and migrated.
  - `EmailTemplate` model created with Bengali category labels and default Islamic education seeds (Exam, Fees, Class/Routine, Holiday, Admission, Assignments, Results, General).
  - Routes `admin.email-templates.store`, `admin.email-templates.destroy`, `admin.email-templates.list-json` registered in `routes/admin.php`.
  - `EmailTemplateController` handles AJAX storing, deleting with system protection, and listing.
  - `BroadcastNotificationController@create` passes templates to view; `send()` handles recipient personalization for `{name}`, `{roll}`, `{course}`, `{batch}`, `{date}`.
  - `resources/views/admin/notifications/create.blade.php` features:
    - Quick select dropdown above `Message Body / Content *` with immediate auto-fill.
    - "ইমেইল টেমপ্লেট লাইব্রেরি" button opening full template gallery modal with category filters and search.
    - "টেমপ্লেট সেভ" button to save current message as a reusable template.
    - Variable tag helper badges for one-click insertion.
  - Automated verification test script `scratch/verify_email_template_system.php` passes with Exit Code 0.
### Task 51: Fix Table Horizontal Scroll & Card Overflow Clipping on Batches, Teachers, and Tickets
- **Objective**: Fix table overflow and missing horizontal scroll on `admin/batches` (and related admin tables `admin/teachers`, `admin/support/tickets`) where hardcoded inline `style="overflow:visible"` prevented horizontal scrolling, causing the rightmost columns (Actions) to be clipped outside the viewport on laptop and zoom displays.
- **Definition of Done (DoD)**:
  - Remove all hardcoded `style="overflow:visible"` on cards and `.table-wrapper` across `batches/index.blade.php`, `teachers/index.blade.php`, and `support/tickets.blade.php`.
  - Ensure `.table-wrapper` has `overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100% !important;` with sleek custom scrollbar styling.
  - Set explicit `min-width: 1120px` and column width distributions on `admin/batches/index.blade.php` so content never crumples or bleeds.
  - Add auto-flip positioning for dropdown menus on the last table rows so dropdowns never get clipped vertically.
  - Add universal `.table-wrapper` horizontal scroll rule to `public/css/app.css` and `resources/views/admin/layouts/app.blade.php`.
  - Verification Method: Automated verification test script `scratch/verify_table_scroll_fix.php` passes with Exit Code 0.
- **Status**: COMPLETED (Exit Code 0, all 15 assertions passed)

---

### Task 52: Promotion Menu Fatal Crash Fix (`admin.final-marks.batch-subjects`)
- **Objective**: Fix the fatal route missing error `Route [admin.final-marks.batch-subjects] not defined` when clicking the Promotion menu or accessing `/admin/promotions`.
- **Definition of Done (DoD)**:
  - Register route `admin.final-marks.batch-subjects` pointing to `ResultBookController@getBatchSubjects`.
  - Implement `getBatchSubjects` returning JSON list of subjects assigned to a batch.
  - Verification: Automated script `scratch/test_promotion_all.php` returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0)

---

### Task 53: Result Management Mark Edit / Override Crash Fix (`ResultBookController@overrideSave`)
- **Objective**: Fix 404/500 crash when clicking "মার্ক এডিট" from Result Book Tabulation view to override student final marks.
- **Definition of Done (DoD)**:
  - Register route `admin.result-book.override-save` in `routes/admin.php`.
  - Implement `overrideSave()` in `ResultBookController` handling upsert on `FinalMark` table (updating marks, total, grade, remarks).
  - Update `editMarkForm` in `resources/views/admin/result-book/index.blade.php` to pass `student_id`, `batch_id`, `subject_id`, and existing `final_mark_id`.
  - Verification: Script `scratch/test_result_override.php` executes upsert without error (Exit Code 0).
- **Status**: COMPLETED (Exit Code 0)

---

### Task 54: Assignment Publish Constraint & Status Fix (Nullable Teacher, Status Enum)
- **Objective**: Fix SQL error when publishing assignments without a teacher assigned or with an empty batch, and fix status enum mismatch.
- **Definition of Done (DoD)**:
  - Migration making `teacher_id` nullable in `assignments` table.
  - Migration updating `status` column from enum `['ACTIVE', 'CLOSED']` to varchar default `'PUBLISHED'`.
  - Update `AssignmentController` (admin & teacher) to sanitize empty string `batch_id` to `null`.
  - Update `Student/SubjectController` to query assignments with status `PUBLISHED` or `ACTIVE`.
  - Verification: Script `scratch/test_assignment_store.php` returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0)

---

### Task 55: New Admission Store Error & Profile Deduplication / FK Sanitization
- **Objective**: Fix errors when creating new admissions from admin panel: avoid duplicate email crashes on `students` table, sanitize empty string dropdown values for foreign keys (`batch_id`, `academic_session_id`, `blood_group_id`, etc.), and allow approving admissions without duplicating existing users.
- **Definition of Done (DoD)**:
  - `AdmissionController@store` finds or creates student profile, updating existing record rather than crashing on duplicate key.
  - Empty string values on foreign key IDs are cast to `null`.
  - `AdmissionController@approve` safely checks for existing user account before creating.
  - `admin/admissions/create.blade.php` safely checks `isset($errors) && $errors->any()`.
  - Verification: Script `scratch/test_admission_full.php` returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0)

---

### Task 56: Question Bank Random Subset Selection & Sequence Shuffling Per Student
- **Objective**: When a question pool contains more questions than target marks, automatically pick a randomized subset matching the required marks (for MCQ and Written separately), and shuffle question order for each student.
- **Definition of Done (DoD)**:
  - In `Student/ExamController@take`:
    - If `has_mcq && mcq_marks > 0`: randomly pick MCQ questions totaling `mcq_marks`.
    - If `has_written && written_marks > 0`: randomly pick Written questions totaling `written_marks`.
    - Shuffle the combined question set and save to `ExamSubmission::assigned_question_ids`.
  - Verification: Script `scratch/test_exam_shuffling.php` verifies two students get randomized subsets and different question order (Exit Code 0).
- **Status**: COMPLETED (Exit Code 0)

---

### Task 57: Teacher Panel Menu Cleanup (Remove Exams/Appeals, Show Assigned Subjects & Routine)
- **Objective**: Remove Exam Management, Appeals, and Result Submission links from the Teacher Portal sidebar and dashboard; display weekly routine slots and assigned subjects instead.
- **Definition of Done (DoD)**:
  - In `resources/views/teacher/layouts/app.blade.php`, remove Exams, Appeals, and Submit Results links.
  - In `Teacher/DashboardController.php`, remove `pending_results` stat and provide `routine_slots` and `assignedSubjects`.
  - In `resources/views/teacher/dashboard.blade.php`, replace exam cards with "My Assigned Subjects" and "Weekly Routine Slots".
  - Verification: Script `scratch/test_teacher_panel.php` returns Exit Code 0.
- **Status**: COMPLETED (Exit Code 0)

---

### Task 58: Course Duration & Tuition Fee Cycle Definition for Courses and Batches
- **Objective**: Allow configuring Course Duration cycle (start month and end month) and Tuition Fee cycle (fee start month and fee end month) on Courses and Batches, with Bengali labels and auto-formatted cycle texts.
- **Definition of Done (DoD)**:
  - Migration adding `start_month`, `end_month`, `fee_start_month`, `fee_end_month` to `courses` and `batches` tables.
  - Models `Course` and `Batch` provide `monthsList()`, `duration_cycle_text`, and `fee_cycle_text` accessors.
  - Controllers `CourseController` and `BatchController` validate and store cycle months.
  - Views `admin/courses/index.blade.php` and `admin/batches/index.blade.php` include month selectors in Create/Edit modals and display cycle badges in table.
  - Verification: Script `scratch/test_course_batch_cycles.php` and `scratch/run_all_feedback_tests.php` return Exit Code 0.
- **Status**: COMPLETED (Exit Code 0)

### Task 59: Remove "Other Subjects" Section & Prevent Duplicate Course Subject Mappings
- **Objective**: Remove the unintended "Other Subjects" group from Student Portal My Course page (`/student/my-course`), clean up unassigned duplicate subject mappings for `Alim Preparatory Course`, prevent future duplicates by matching on `[course_id, subject_id]` in `CourseController@assignSubject`, and require `semester_id` for semester-based courses.
- **Definition of Done (DoD)**:
  - Remove duplicate unassigned mappings (`semester_id IS NULL`) for Alim Preparatory Course (IDs 83, 84, 85, 86) leaving accurate 28 subjects across 6 semesters.
  - In `app/Http/Controllers/Admin/CourseController.php`: require `semester_id` when course is `SEMESTER_BASED`, and make `updateOrCreate` match on `[course_id, subject_id]` so assigning existing subjects updates their semester instead of creating duplicates.
  - In `resources/views/student/my-course/index.blade.php`: only query `whereNotNull('semester_id')` for `SEMESTER_BASED` courses and remove `'Other Subjects'` fallback text.
  - In `app/Http/Controllers/Student/MyCourseController.php`: calculate `totalSubjects` accurately based on valid semester subjects for semester-based courses.
  - Verification: Automated script `scratch/verify_no_other_subjects.php` verifies 28 subjects, 0 nulls, and clean render without "Other Subjects" (Exit Code 0).
- **Status**: COMPLETED (Exit Code 0)

### Task 60: Batch Unique Code Collision Fix & Same Date/Month Duplicate Validation
- **Objective**: Fix the SQL 1062 duplicate key crash on `batches.batches_batch_code_unique` (e.g. `Duplicate entry 'ALI-2026-11'`) by making batch code generation collision-free, and implement form validation preventing multiple batches for the same course on the same date or in the same month.
- **Definition of Done (DoD)**:
  - In `app/Http/Controllers/Admin/BatchController.php`:
    - Add collision-free `batch_code` generator checking maximum sequence and verifying against existing codes with fallback while-loop.
    - Add validation in `store()` and `update()` ensuring a course cannot have two batches on the exact same date or in the same month/year.
    - Throw `ValidationException::withMessages(['start_date' => ...])` on duplicate date/month conflict.
  - In `resources/views/admin/batches/index.blade.php`:
    - Add error alert banners in main view and inside `addBatchModal`.
    - Retain `old(...)` form inputs on validation failure.
    - Auto-reopen `addBatchModal` on validation error via DOM script.
  - Verification: Automated test script `scratch/test_batch_validation.php` verifies unique code generation without collision, rejects same date and same month, allows different month and different course, and tests blade rendering (Exit Code 0).
- **Status**: COMPLETED (Exit Code 0)

---

### Task 61: Search Bar for Subject Mapping & Teacher Assignment Dropdowns
- **Objective**: Add instant search bars to all dropdowns and selection lists in Course Subject Mapping (`admin/courses/{course}`) and Teacher Subject Assignment (`admin/teachers`), replacing cumbersome multi-selects with searchable, filterable interfaces and "Select All / Deselect All" helpers.
- **Definition of Done (DoD)**:
  - In `resources/views/admin/courses/show.blade.php`:
    - Add real-time live search filter `#map_semester_search_input` for Semester selection.
    - Replace Ctrl+click native `<select multiple>` in `mapSubjectModal` with searchable checkbox list `#map_subject_search_input`, filterable by code or subject title.
    - Include quick action buttons: "সব নির্বাচন (Select All Filtered)", "বাতিল (Deselect All)", and dynamic selected item & total credit counter badge.
    - Form submit validation prevents submitting empty selections.
    - Add real-time live search filter `#single_subject_search_input` for single subject dropdown in `addSingleSubjectModal`.
  - In `resources/views/admin/teachers/index.blade.php`:
    - Add real-time search filter `#teacher_subject_search_input` for Subject dropdown in `assignSubjectModal`.
  - Design & Localization:
    - Styled with Bengali cultural context and font `'Kalpurush'`.
  - Verification:
    - Test script `scratch/test_subject_mapping_search.php` verifies DOM presence of all search bars, options, buttons, and successful persistence in DB returning Exit Code 0.
- **Status**: COMPLETED (Exit Code 0)

---

### Task 62: Student ID Architecture Correction (YY-BB-CC-G-RRRR) & Semester Subject Modal Multi-Select Overhaul
- **Objective**: 
  1. Fix Student ID generation formula:
     - Digits 1-2: Academic Year (e.g., 27, 28) derived from batch academic year / start date.
     - Digits 3-4: Batch Number (e.g., 01, 02) extracted from batch name/code, with Bengali numeral support.
     - Digits 5-6: Course Code (e.g., 01, 04) from course formatted code.
     - Digit 7: Gender code (1 = Male, 2 = Female).
     - Digits 8-11: Sequential 4-digit unique roll number.
  2. Correct all existing database students' IDs, updating linked user accounts and support tickets.
  3. Overhaul `addSingleSubjectModal` ("+ Semester X এ নতুন বিষয় যোগ করুন") into the exact same rich checkbox multi-select UI as `mapSubjectModal` with live search, Select All / Deselect All, and credit counters.
- **Definition of Done (DoD)**:
  - `Student` model implements `resolveAcademicYearCode`, `resolveBatchNumberCode`, `resolveCourseCode`, `resolveGenderCode`, `generateStudentCode`, and updated `updateCodeForTransferOrReadmission`.
  - `PaymentGatewayService` and `AdmissionController` use `Student::generateStudentCode(...)`.
  - All 18 existing database student records corrected (including Mazharul Islam Hridoy `APP-2026-0026` updated to `27010110003`).
  - `resources/views/admin/courses/show.blade.php` overhauls `addSingleSubjectModal` into a searchable checkbox multi-selector with action buttons and badge counters.
  - Verification: `scratch/verify_student_id_and_semester_subject_modal.php` returns Exit Code 0 (71 assertions passed).
- **Status**: COMPLETED (Exit Code 0)

---

### Task 63: Poor Fund Application Success Screen Preservation Notice & Copy Button
- **Objective**: 
  1. Add an informative alert box instructing the applicant to carefully preserve the Application Reference Number (e.g., `PF-2026-0015`) for use during admission form waiver submission and status inquiries.
  2. Add an interactive Copy Button (`#copyBtn`) next to the reference number with instant clipboard copying, fallback support, and animated feedback ("কপি হয়েছে!").
- **Definition of Done (DoD)**:
  - In `resources/views/public/poor_fund_success.blade.php`:
    - Responsive reference number display with `.app-no` and `.btn-copy`.
    - Alert notice box with Bengali text and icon advising user to save the reference number.
    - JavaScript `copyRefNumber` supporting modern Clipboard API and fallback textarea copy with visual state transition.
    - Adheres to `'Kalpurush'` font.
  - Verification: `scratch/verify_poor_fund_success_copy.php` returns Exit Code 0 (10 assertions passed).
- **Status**: COMPLETED (Exit Code 0)

---

## Pending Tasks
*All tasks (Tasks 32 through 63) are COMPLETED and verified with Exit Code 0. Zero pending tasks remain.*


