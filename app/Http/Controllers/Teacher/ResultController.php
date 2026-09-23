<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $exams = Exam::with(['subject', 'results'])->latest()->get();
        return view('teacher.results.index', compact('exams'));
    }

    public function enter(Exam $exam)
    {
        $exam->load(['subject', 'attendees.student', 'results', 'submissions.student']);
        
        // If attendees exist, prioritize them, otherwise get students belonging to the batch/course or active
        if ($exam->attendees->isNotEmpty()) {
            $studentIds = $exam->attendees->pluck('student_id');
            $students = Student::whereIn('id', $studentIds)->where('status', 'ACTIVE')->get();
        } elseif ($exam->batch_id) {
            $students = Student::whereHas('enrollments', function ($q) use ($exam) {
                $q->where('batch_id', $exam->batch_id)->whereIn('status', ['ACTIVE', 'active', 'ENROLLED', 'enrolled']);
            })->where('status', 'ACTIVE')->get();
            if ($students->isEmpty()) {
                $students = Student::where('status', 'ACTIVE')->get();
            }
        } else {
            $students = Student::where('status', 'ACTIVE')->get();
        }

        return view('teacher.results.enter', compact('exam', 'students'));
    }

    public function store(Request $request, Exam $exam)
    {
        $hasComponents = $exam->has_mcq || $exam->has_written || $exam->has_tamrin || $exam->has_viva;

        $studentIds = collect(array_merge(
            array_keys($request->input('marks', [])),
            array_keys($request->input('mcq_marks', [])),
            array_keys($request->input('written_marks', [])),
            array_keys($request->input('tamrin_marks', [])),
            array_keys($request->input('viva_marks', []))
        ))->unique();

        foreach ($studentIds as $studentId) {
            $mcq = $request->input("mcq_marks.{$studentId}");
            $written = $request->input("written_marks.{$studentId}");
            $tamrin = $request->input("tamrin_marks.{$studentId}");
            $viva = $request->input("viva_marks.{$studentId}");
            $manualTotal = $request->input("marks.{$studentId}");

            // If completely empty for this student, continue
            if ($mcq === null && $written === null && $tamrin === null && $viva === null && ($manualTotal === null || $manualTotal === '')) {
                continue;
            }

            $mcqVal = ($mcq !== null && $mcq !== '') ? (float) $mcq : null;
            $writtenVal = ($written !== null && $written !== '') ? (float) $written : null;
            $tamrinVal = ($tamrin !== null && $tamrin !== '') ? (float) $tamrin : null;
            $vivaVal = ($viva !== null && $viva !== '') ? (float) $viva : null;

            if ($hasComponents) {
                $totalMark = ($mcqVal ?? 0) + ($writtenVal ?? 0) + ($tamrinVal ?? 0) + ($vivaVal ?? 0);
            } else {
                $totalMark = (float) $manualTotal;
            }

            $status = $totalMark >= $exam->pass_marks ? 'PASS' : 'FAIL';
            $grade = $this->calculateGrade($totalMark, $exam->full_marks ?: 100);

            // Fetch existing result attempt number or increment per §9.3
            $prevResult = Result::where('student_id', $studentId)
                ->where('subject_id', $exam->subject_id)
                ->latest('attempt_no')
                ->first();

            $attemptNo = $prevResult ? $prevResult->attempt_no + 1 : 1;

            Result::updateOrCreate(
                [
                    'exam_id'    => $exam->id,
                    'student_id' => $studentId,
                ],
                [
                    'attempt_no'    => $attemptNo,
                    'marks'         => $totalMark,
                    'mcq_marks'     => $mcqVal,
                    'written_marks' => $writtenVal,
                    'tamrin_marks'  => $tamrinVal,
                    'viva_marks'    => $vivaVal,
                    'grade'         => $grade,
                    'status'        => $status,
                ]
            );

            // If an ExamSubmission exists for this student, also keep it in sync
            $submission = \App\Models\ExamSubmission::where('exam_id', $exam->id)->where('student_id', $studentId)->first();
            if ($submission) {
                $submission->update([
                    'mcq_score'     => $mcqVal ?? $submission->mcq_score,
                    'written_score' => $writtenVal ?? $submission->written_score,
                    'tamrin_score'  => $tamrinVal ?? $submission->tamrin_score,
                    'viva_score'    => $vivaVal ?? $submission->viva_score,
                    'total_score'   => $totalMark,
                ]);
            }
        }

        $exam->update(['status' => 'COMPLETED']);

        return redirect()->route('teacher.results.index')
            ->with('success', 'মূল্যায়ন ও নম্বর সফলভাবে সংরক্ষিত হয়েছে।');
    }

    private function calculateGrade($marks, $fullMarks): string
    {
        $pct = ($marks / $fullMarks) * 100;
        if ($pct >= 80) return 'A+';
        if ($pct >= 70) return 'A';
        if ($pct >= 60) return 'B';
        if ($pct >= 50) return 'C';
        if ($pct >= 40) return 'D';
        return 'F';
    }
}
