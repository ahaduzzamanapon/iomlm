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

---

## Pending Tasks
- No pending tasks. All requested modules and features are implemented and verified.



