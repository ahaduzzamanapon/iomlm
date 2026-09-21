<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FinalMark;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Show published examination results & final semester marks
     */
    public function index()
    {
        $student = Student::with(['enrollments.batch.course.semesters', 'enrollments.course.semesters'])
            ->where('user_id', auth()->id())
            ->first();

        if (!$student) {
            return view('student.results.index', [
                'results'    => collect(),
                'finalMarks' => collect(),
                'student'    => null,
                'course'     => null,
            ]);
        }

        // 1. Published Individual Exam Results (Quiz, Mid, Final)
        $results = Result::with(['subject', 'exam'])
            ->where('student_id', $student->id)
            ->where(function ($q) {
                $q->where('is_published', true)
                  ->orWhereHas('exam', function ($e) {
                      $e->where('is_result_published', true);
                  });
            })
            ->latest('id')
            ->get();

        // 2. Published Final Marks with Merit Position & Qawmi Grade
        $finalMarks = FinalMark::with(['subject', 'semester', 'batch'])
            ->where('student_id', $student->id)
            ->where('is_published', true)
            ->get();

        $primaryEnrollment = $student->enrollments->whereIn('status', ['ACTIVE', 'COMPLETED', 'active', 'completed'])->first()
            ?? $student->enrollments->first();

        $course = $primaryEnrollment?->course ?? $primaryEnrollment?->batch?->course;

        return view('student.results.index', compact('results', 'finalMarks', 'student', 'course'));
    }

    /**
     * 6-Semester Consolidated Transcript for Student
     */
    public function transcript()
    {
        $student = Student::with(['enrollments.batch.course.semesters', 'enrollments.course.semesters'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $primaryEnrollment = $student->enrollments->whereIn('status', ['ACTIVE', 'COMPLETED', 'active', 'completed'])->first()
            ?? $student->enrollments->first();

        $course = $primaryEnrollment?->course ?? $primaryEnrollment?->batch?->course;

        // Fetch all published final marks across all semesters
        $finalMarks = FinalMark::with(['subject', 'semester', 'batch'])
            ->where('student_id', $student->id)
            ->where('is_published', true)
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

        $cgpa = $totalCreditsAttempted > 0 ? round($totalWeightedGpaPoints / $totalCreditsAttempted, 2) : 0.00;
        $overallQawmiGrade = FinalMark::calculateQawmiGrade(0, $cgpa);
        $overallStatus = ($cgpa >= 2.00 && $totalCreditsEarned >= ($totalCreditsAttempted * 0.75)) ? 'PASSED' : 'IN_PROGRESS';

        return view('student.results.transcript', compact(
            'student', 'course', 'primaryEnrollment',
            'semestersData', 'totalCreditsAttempted', 'totalCreditsEarned',
            'cgpa', 'overallQawmiGrade', 'overallStatus'
        ));
    }
}
