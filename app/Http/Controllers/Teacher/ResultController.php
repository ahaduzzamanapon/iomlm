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
        $exam->load(['subject', 'attendees.student', 'results']);
        $students = Student::where('status', 'ACTIVE')->get();
        return view('teacher.results.enter', compact('exam', 'students'));
    }

    public function store(Request $request, Exam $exam)
    {
        $request->validate([
            'marks' => 'required|array',
        ]);

        foreach ($request->input('marks', []) as $studentId => $markValue) {
            if ($markValue === null || $markValue === '') continue;

            $mark = (float) $markValue;
            $status = $mark >= $exam->pass_marks ? 'PASS' : 'FAIL';
            $grade = $this->calculateGrade($mark, $exam->full_marks);

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
                    'subject_id'  => $exam->subject_id,
                    'attempt_no'  => $attemptNo,
                    'marks'       => $mark,
                    'grade'       => $grade,
                    'status'      => $status,
                    'recorded_by' => auth()->id(),
                ]
            );
        }

        $exam->update(['status' => 'COMPLETED']);

        return redirect()->route('teacher.results.index')
            ->with('success', 'Exam results entered successfully. Student GPA and history updated!');
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
