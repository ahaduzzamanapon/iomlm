<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\FinalMark;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class ResultBookController extends Controller
{
    /**
     * Display the Result Book with multi-criteria filters, student search & marks table
     */
    public function index(Request $request)
    {
        $batches = Batch::with('course.semesters', 'course.subjects')->orderByDesc('id')->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();
        
        $selectedBatch = null;
        $selectedSubject = null;
        $selectedSemester = null;
        $subjects = collect();
        $semesters = collect();

        if ($request->filled('batch_id')) {
            $selectedBatch = Batch::with('course.semesters', 'course.subjects')->find($request->batch_id);
            if ($selectedBatch && $selectedBatch->course) {
                $semesters = $selectedBatch->course->semesters;
                $subjects = $selectedBatch->course->subjects;
            }
        } else {
            $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        }

        if ($request->filled('semester_id')) {
            $selectedSemester = Semester::find($request->semester_id);
        }

        if ($request->filled('subject_id')) {
            $selectedSubject = Subject::find($request->subject_id);
        }

        // Query final marks
        $query = FinalMark::with(['student.enrollments.batch', 'subject', 'batch', 'semester']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('semester_id')) {
            $semId = $request->semester_id;
            $query->where(function ($q) use ($semId) {
                $q->where('semester_id', $semId)->orWhereNull('semester_id');
            });
        }

        $finalMarks = $query->orderBy('merit_position')
            ->orderByDesc('total_mark')
            ->get();

        // Get exams for selected subject/semester to allow toggling CT/Mid/Final publish
        $exams = collect();
        if ($selectedSubject) {
            $exams = Exam::where('subject_id', $selectedSubject->id)
                ->when($selectedSemester, fn($q) => $q->where(function ($q2) use ($selectedSemester) {
                    $q2->where('semester_id', $selectedSemester->id)->orWhereNull('semester_id');
                }))
                ->get();
        }

        // Summary Statistics
        $totalStudents = $finalMarks->count();
        $passedCount   = $finalMarks->where('status', 'PASS')->count();
        $failedCount   = $finalMarks->where('status', 'FAIL')->count();
        $publishedCount = $finalMarks->where('is_published', true)->count();
        $avgScore      = $totalStudents > 0 ? round($finalMarks->avg('total_mark'), 2) : 0;

        return view('admin.result-book.index', compact(
            'batches', 'courses', 'subjects', 'semesters',
            'selectedBatch', 'selectedSubject', 'selectedSemester',
            'finalMarks', 'exams',
            'totalStudents', 'passedCount', 'failedCount', 'publishedCount', 'avgScore'
        ));
    }

    /**
     * Manual Mark Override from Result Book Modal
     */
    public function override(Request $request, FinalMark $finalMark)
    {
        $validated = $request->validate([
            'class_test_converted' => 'nullable|numeric|min:0|max:100',
            'midterm_converted'    => 'nullable|numeric|min:0|max:100',
            'final_converted'      => 'nullable|numeric|min:0|max:100',
            'attendance_converted' => 'nullable|numeric|min:0|max:100',
            'remarks'              => 'nullable|string|max:500',
        ]);

        if ($request->has('remarks')) {
            $finalMark->remarks = $validated['remarks'] ?? null;
        }

        $finalMark->recalculate($validated);

        // Re-evaluate batch merit ranks
        FinalMark::recalculateMeritRanks($finalMark->batch_id, $finalMark->subject_id, $finalMark->semester_id);

        $studentName = $finalMark->student->name ?? 'শিক্ষার্থী';
        return back()->with('success', "✅ শিক্ষার্থী '{$studentName}'-এর ফলাফল ম্যানুয়ালি সংশোধন করা হয়েছে। নতুন মোট: {$finalMark->total_mark} (গ্রেড: {$finalMark->grade}, মেধাক্রম: {$finalMark->merit_rank_bengali})");
    }

    /**
     * Toggle Result Publication for a Specific Exam (Class Test, Mid Term, Final Term)
     */
    public function publishExam(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        $exam = Exam::findOrFail($request->exam_id);

        if ($exam->is_result_published) {
            $exam->unpublishResults();
            $msg = "⚠️ '{$exam->title}' পরীক্ষার ফলাফল প্রকাশ প্রত্যাহার (Unpublished) করা হয়েছে।";
        } else {
            $exam->publishResults();
            $msg = "✅ '{$exam->title}' পরীক্ষার ফলাফল সফলভাবে প্রকাশিত হয়েছে। শিক্ষার্থীরা পোর্টালে রেজাল্ট দেখতে পারবে।";
        }

        return back()->with('success', $msg);
    }

    /**
     * 6-Semester Consolidated Academic Transcript with CGPA & Qawmi Grade
     */
    public function transcript(Student $student)
    {
        $student->load(['enrollments.batch.course.semesters', 'enrollments.course.semesters']);

        $primaryEnrollment = $student->enrollments->whereIn('status', ['ACTIVE', 'COMPLETED', 'active', 'completed'])->first()
            ?? $student->enrollments->first();

        $course = $primaryEnrollment?->course ?? $primaryEnrollment?->batch?->course;

        // Fetch all final marks across all semesters for this student
        $finalMarks = FinalMark::with(['subject', 'semester', 'batch'])
            ->where('student_id', $student->id)
            ->get();

        // Organize marks by semester
        $semestersData = [];
        $totalCreditsAttempted = 0;
        $totalCreditsEarned = 0;
        $totalWeightedGpaPoints = 0;

        if ($course && $course->semesters->isNotEmpty()) {
            foreach ($course->semesters->sortBy('sequence_no') as $sem) {
                $semMarks = $finalMarks->where('semester_id', $sem->id);

                $semCredit = 0;
                $semEarnedCredit = 0;
                $semPoints = 0;

                $subjectsList = [];
                foreach ($semMarks as $fm) {
                    $credit = $fm->subject->credit ?? 3;
                    $semCredit += $credit;
                    if ($fm->status === 'PASS') {
                        $semEarnedCredit += $credit;
                    }
                    $semPoints += ($fm->gpa * $credit);

                    $subjectsList[] = [
                        'code'        => $fm->subject->code ?? '—',
                        'name'        => $fm->subject->name ?? '—',
                        'credit'      => $credit,
                        'total_mark'  => $fm->total_mark,
                        'grade'       => $fm->grade,
                        'gpa'         => $fm->gpa,
                        'qawmi_grade' => $fm->qawmi_grade,
                        'status'      => $fm->status,
                        'merit_rank'  => $fm->merit_rank_bengali,
                    ];
                }

                $sgpa = $semCredit > 0 ? round($semPoints / $semCredit, 2) : 0.00;

                $semestersData[] = [
                    'semester'      => $sem,
                    'sequence_no'   => $sem->sequence_no,
                    'name'          => $sem->name,
                    'subjects'      => $subjectsList,
                    'total_credit'  => $semCredit,
                    'earned_credit' => $semEarnedCredit,
                    'sgpa'          => $sgpa,
                    'qawmi_grade'   => FinalMark::calculateQawmiGrade(0, $sgpa),
                ];

                $totalCreditsAttempted += $semCredit;
                $totalCreditsEarned += $semEarnedCredit;
                $totalWeightedGpaPoints += $semPoints;
            }
        } else {
            // Non-semester or subject-based course fallback
            $subjectsList = [];
            $semCredit = 0;
            $semEarnedCredit = 0;
            $semPoints = 0;

            foreach ($finalMarks as $fm) {
                $credit = $fm->subject->credit ?? 3;
                $semCredit += $credit;
                if ($fm->status === 'PASS') {
                    $semEarnedCredit += $credit;
                }
                $semPoints += ($fm->gpa * $credit);

                $subjectsList[] = [
                    'code'        => $fm->subject->code ?? '—',
                    'name'        => $fm->subject->name ?? '—',
                    'credit'      => $credit,
                    'total_mark'  => $fm->total_mark,
                    'grade'       => $fm->grade,
                    'gpa'         => $fm->gpa,
                    'qawmi_grade' => $fm->qawmi_grade,
                    'status'      => $fm->status,
                    'merit_rank'  => $fm->merit_rank_bengali,
                ];
            }

            $sgpa = $semCredit > 0 ? round($semPoints / $semCredit, 2) : 0.00;
            $semestersData[] = [
                'semester'      => null,
                'sequence_no'   => 1,
                'name'          => 'সামগ্রিক কোর্স সিলেবাস',
                'subjects'      => $subjectsList,
                'total_credit'  => $semCredit,
                'earned_credit' => $semEarnedCredit,
                'sgpa'          => $sgpa,
                'qawmi_grade'   => FinalMark::calculateQawmiGrade(0, $sgpa),
            ];

            $totalCreditsAttempted = $semCredit;
            $totalCreditsEarned = $semEarnedCredit;
            $totalWeightedGpaPoints = $semPoints;
        }

        // Cumulative GPA (CGPA)
        $cgpa = $totalCreditsAttempted > 0 ? round($totalWeightedGpaPoints / $totalCreditsAttempted, 2) : 0.00;
        $overallQawmiGrade = FinalMark::calculateQawmiGrade(0, $cgpa);
        $overallStatus = ($cgpa >= 2.00 && $totalCreditsEarned >= ($totalCreditsAttempted * 0.75)) ? 'PASSED' : 'IN_PROGRESS';

        return view('admin.students.transcript', compact(
            'student', 'course', 'primaryEnrollment',
            'semestersData', 'totalCreditsAttempted', 'totalCreditsEarned',
            'cgpa', 'overallQawmiGrade', 'overallStatus'
        ));
    }
}
