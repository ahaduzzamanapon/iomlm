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
