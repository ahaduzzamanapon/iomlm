<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\Result;
use App\Models\FinalMark;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ResultBookController;

class BulkMarksImportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_download_manual_marks_template_generates_exam_and_combined_headers()
    {
        $course = Course::create(['name' => 'Hadith Course', 'code' => 'HAD-01', 'type' => 'SEMESTER_BASED']);
        $batch = Batch::create(['name' => 'Batch 05', 'batch_code' => 'B05', 'course_id' => $course->id, 'start_date' => now()]);
        $subject = Subject::create(['name' => 'Bukhari', 'code' => 'BUK-01']);

        $user = User::create(['name' => 'Abdullah', 'email' => 'abdullah@iom.test', 'password' => bcrypt('password'), 'role' => 'student']);
        $student = Student::create(['user_id' => $user->id, 'name' => 'Abdullah', 'email' => 'abdullah@iom.test', 'student_code' => '801']);

        FinalMark::create([
            'batch_id'    => $batch->id,
            'student_id'  => $student->id,
            'subject_id'  => $subject->id,
        ]);

        $exam = Exam::create([
            'title'            => 'Mid Term Bukhari',
            'type'             => 'MIDTERM',
            'subject_id'       => $subject->id,
            'full_marks'       => 100,
            'pass_marks'       => 40,
            'duration_minutes' => 60,
            'exam_date'        => now()->addDays(2),
        ]);

        $controller = app(ResultBookController::class);

        // 1. Template for specific Exam (Exam-wise mode)
        $reqExam = Request::create('/admin/result-book/manual-marks/template', 'GET', [
            'batch_id'   => $batch->id,
            'subject_id' => $subject->id,
            'exam_id'    => $exam->id,
        ]);
        $respExam = $controller->downloadManualMarksTemplate($reqExam);
        $contentExam = $respExam->getContent();

        $this->assertStringContainsString('student_code', $contentExam);
        $this->assertStringContainsString('tamrin_marks', $contentExam);
        $this->assertStringContainsString('attendance_mark', $contentExam);
        $this->assertStringContainsString('801', $contentExam);

        // 2. Template for Combined Semester mode
        $reqAll = Request::create('/admin/result-book/manual-marks/template', 'GET', [
            'batch_id'   => $batch->id,
            'subject_id' => $subject->id,
        ]);
        $respAll = $controller->downloadManualMarksTemplate($reqAll);
        $contentAll = $respAll->getContent();

        $this->assertStringContainsString('student_code', $contentAll);
        $this->assertStringContainsString('ct_tamrin', $contentAll);
        $this->assertStringContainsString('midterm_tamrin', $contentAll);
        $this->assertStringContainsString('final_tamrin', $contentAll);
        $this->assertStringContainsString('attendance_converted', $contentAll);
    }

    public function test_import_manual_marks_bulk_exam_wise_and_combined_mode()
    {
        $course = Course::create(['name' => 'Tafseer Course', 'code' => 'TAF-01', 'type' => 'SEMESTER_BASED']);
        $batch = Batch::create(['name' => 'Batch 06', 'batch_code' => 'B06', 'course_id' => $course->id, 'start_date' => now()]);
        $subject = Subject::create(['name' => 'Jalalayn', 'code' => 'JAL-01']);

        $user = User::create(['name' => 'Tariq', 'email' => 'tariq@iom.test', 'password' => bcrypt('password'), 'role' => 'student']);
        $student = Student::create(['user_id' => $user->id, 'name' => 'Tariq', 'email' => 'tariq@iom.test', 'student_code' => '901']);

        $exam = Exam::create([
            'title'            => 'Mid Term Jalalayn',
            'type'             => 'MIDTERM',
            'subject_id'       => $subject->id,
            'full_marks'       => 100,
            'pass_marks'       => 40,
            'duration_minutes' => 60,
            'exam_date'        => now()->addDays(2),
        ]);

        $controller = app(ResultBookController::class);

        // 1. Exam-wise bulk import via pasted TSV/CSV data
        $examCsvData = "student_code\tstudent_name\ttamrin_marks\tmcq_marks\twritten_marks\tviva_marks\tattendance_mark\tremarks\n901\tTariq\t18\t25\t35\t10\t9.5\tExcellent";

        $reqExam = Request::create('/admin/result-book/manual-marks/import', 'POST', [
            'batch_id'   => $batch->id,
            'subject_id' => $subject->id,
            'exam_id'    => $exam->id,
            'paste_data' => $examCsvData,
        ]);

        $resp = $controller->importManualMarksBulk($reqExam);

        // Verify Result record
        $result = Result::where('exam_id', $exam->id)->where('student_id', $student->id)->first();
        $this->assertNotNull($result);
        $this->assertEquals(18, $result->tamrin_marks);
        $this->assertEquals(88, $result->marks); // 18 + 25 + 35 + 10
        $this->assertEquals('PASS', $result->status);

        // Verify FinalMark updated
        $fm = FinalMark::where('batch_id', $batch->id)->where('student_id', $student->id)->where('subject_id', $subject->id)->first();
        $this->assertNotNull($fm);
        $this->assertEquals(18, $fm->midterm_tamrin);
        $this->assertEquals(9.5, $fm->attendance_converted);

        // 2. Combined semester bulk import
        $combinedCsvData = "student_code,student_name,ct_tamrin,midterm_tamrin,final_tamrin,attendance_converted,remarks\n901,Tariq,8.5,15,19,9.0,Good";

        $reqCombined = Request::create('/admin/result-book/manual-marks/import', 'POST', [
            'batch_id'   => $batch->id,
            'subject_id' => $subject->id,
            'paste_data' => $combinedCsvData,
        ]);

        $controller->importManualMarksBulk($reqCombined);

        $fm->refresh();
        $this->assertEquals(8.5, $fm->ct_tamrin);
        $this->assertEquals(15, $fm->midterm_tamrin);
        $this->assertEquals(19, $fm->final_tamrin);
        $this->assertEquals(9.0, $fm->attendance_converted);
    }
}
