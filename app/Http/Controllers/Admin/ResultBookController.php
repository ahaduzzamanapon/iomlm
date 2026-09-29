<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\CourseSubjectMap;
use App\Models\FinalMark;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class ResultBookController extends Controller
{
    /**
     * Display Result Management
     * ট্যাবস: ১. সেমিস্টার টেবুলেশন ও মেধা তালিকা ২. ম্যানুয়াল মার্কিং (তামরিন, তাজবীদ, DNS) ৩. ৬-সেমিস্টার সামগ্রিক মেধা তালিকা
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'tabulation'); // tabulation, manual_marking, batch_merit

        $batches = Batch::with(['course.semesters' => function ($q) {
            $q->orderBy('sequence_no');
        }])->orderByDesc('id')->get();

        $selectedBatch = null;
        $selectedSemester = null;
        $selectedSubject = null;
        $examType = $request->input('exam_type', 'FINAL'); // FINAL, MIDTERM, QUIZ, ALL
        $search = trim((string) $request->input('search', ''));
        $subjects = collect();
        $semesters = collect();
        $rankedStudents = collect();
        $manualMarkingList = collect();
        $batchMeritList = collect();
        $isSemesterBased = false;
        $isBatchPublished = false;
        $exams = collect();
        $examLookup = [];
        $subjectExams = collect();
        $selectedExam = null;
        $allExamResults = collect();

        // Auto-select requested batch or default to the latest batch
        $batchId = $request->filled('batch_id') ? $request->batch_id : ($batches->first()?->id ?? null);

        if ($batchId) {
            $selectedBatch = Batch::with(['course.semesters' => function ($q) {
                $q->orderBy('sequence_no');
            }])->find($batchId);

            if ($selectedBatch && $selectedBatch->course) {
                $course = $selectedBatch->course;
                $isSemesterBased = ($course->type === 'SEMESTER_BASED');

                if ($isSemesterBased) {
                    $semesters = $course->semesters->sortBy('sequence_no');
                    if ($request->filled('semester_id')) {
                        $selectedSemester = $semesters->firstWhere('id', $request->semester_id);
                    }
                    if (!$selectedSemester) {
                        $selectedSemester = $semesters->first();
                    }

                    // Retrieve all subjects for this semester
                    if ($selectedSemester) {
                        $subjectMaps = CourseSubjectMap::where('course_id', $course->id)
                            ->where('semester_id', $selectedSemester->id)
                            ->orderBy('sort_order')
                            ->with('subject')
                            ->get();
                        $subjects = $subjectMaps->pluck('subject')->filter();

                        if ($subjects->isEmpty()) {
                            $subIds = FinalMark::where('batch_id', $selectedBatch->id)
                                ->where('semester_id', $selectedSemester->id)
                                ->pluck('subject_id')->unique();
                            $subjects = Subject::whereIn('id', $subIds)->orderBy('code')->get();
                        }
                    }
                } else {
                    // Subject-based course: subjects assigned directly to the course
                    $subIds = CourseSubjectMap::where('course_id', $course->id)->pluck('subject_id');
                    if ($subIds->isEmpty()) {
                        $subIds = FinalMark::where('batch_id', $selectedBatch->id)->pluck('subject_id')->unique();
                    }
                    $subjects = Subject::whereIn('id', $subIds)->orderBy('code')->get();
                }

                // Optional Subject Filter in Tabulation / Single Subject view
                if ($request->filled('subject_id')) {
                    $selectedSubject = $subjects->firstWhere('id', $request->subject_id);
                }

                // Retrieve all exams for this batch & semester for direct linking to Merit List & Marksheets
                $subjectIds = $subjects->pluck('id')->filter()->values();
                $examsQuery = \App\Models\Exam::with(['subject', 'semester'])
                    ->where(function ($q) use ($selectedBatch, $subjectIds, $selectedSemester, $isSemesterBased) {
                        $q->whereHas('attendees', function ($aq) use ($selectedBatch) {
                            $aq->where('batch_id', $selectedBatch->id);
                        });
                        if ($subjectIds->isNotEmpty()) {
                            $q->orWhereIn('subject_id', $subjectIds);
                        }
                        if ($isSemesterBased && $selectedSemester) {
                            $q->orWhere('semester_id', $selectedSemester->id);
                        }
                    });

                if ($isSemesterBased && $selectedSemester) {
                    $examsQuery->where(function($q) use ($selectedSemester) {
                        $q->where('semester_id', $selectedSemester->id)
                          ->orWhereNull('semester_id');
                    });
                }
                $exams = $examsQuery->latest('id')->get();

                $examLookup = [];
                foreach ($exams as $ex) {
                    $examLookup[$ex->subject_id . '_' . $ex->type] = $ex;
                    if ($ex->semester_id) {
                        $examLookup[$ex->subject_id . '_' . $ex->semester_id . '_' . $ex->type] = $ex;
                    }
                    $examLookup[$ex->id] = $ex;
                }

                // Check if current batch + semester has published results
                $publishQuery = FinalMark::where('batch_id', $selectedBatch->id);
                if ($isSemesterBased && $selectedSemester) {
                    $publishQuery->where('semester_id', $selectedSemester->id);
                }
                $isBatchPublished = (clone $publishQuery)->where('is_published', true)->exists();

                // ─── TAB 1: TABULATION & MERIT LIST ─────────────────────────
                if ($tab === 'tabulation') {
                    $finalMarksQuery = FinalMark::with(['student', 'subject'])
                        ->where('batch_id', $selectedBatch->id);

                    if ($isSemesterBased && $selectedSemester) {
                        $finalMarksQuery->where('semester_id', $selectedSemester->id);
                    }

                    $allFinalMarks = $finalMarksQuery->get();
                    $studentsGrouped = $allFinalMarks->groupBy('student_id');
                    $studentsData = [];

                    foreach ($studentsGrouped as $studentId => $marks) {
                        $student = $marks->first()->student;
                        if (!$student) continue;

                        // Filter by search query if provided
                        if (!empty($search)) {
                            $sName = strtolower($student->name ?? '');
                            $sRoll = strtolower((string) ($student->student_code ?? $student->student_id ?? ''));
                            $sQuery = strtolower($search);
                            if (!str_contains($sName, $sQuery) && !str_contains($sRoll, $sQuery)) {
                                continue;
                            }
                        }

                        $subjectMarks = [];
                        $totalObtained = 0;
                        $totalFull = 0;
                        $totalGpaPoints = 0;
                        $totalCredits = 0;
                        $hasFail = false;
                        $firstFinalMarkId = $marks->first()?->id;

                        foreach ($subjects as $sub) {
                            $fm = $marks->firstWhere('subject_id', $sub->id);
                            $credit = $sub->credit ?? 3;
                            $totalCredits += $credit;

                            if ($fm) {
                                if ($examType === 'FINAL') {
                                    $obtained = (float) ($fm->raw_final ?? 0);
                                    $full = 100;
                                    $conv = $fm->final_converted ?? round(($obtained / 100) * 40, 2);
                                } elseif ($examType === 'MIDTERM') {
                                    $obtained = (float) ($fm->raw_midterm ?? 0);
                                    $full = 50;
                                    $conv = $fm->midterm_converted ?? round(($obtained / 50) * 30, 2);
                                } elseif ($examType === 'QUIZ') {
                                    $obtained = (float) ($fm->raw_class_test ?? 0);
                                    $full = 30;
                                    $conv = $fm->class_test_converted ?? round(($obtained / 30) * 20, 2);
                                } else {
                                    // ALL (Full 100% aggregate)
                                    $obtained = (float) ($fm->total_mark ?? 0);
                                    $full = 100;
                                    $conv = $obtained;
                                }

                                $grade = $fm->grade ?? 'F';
                                $gpa = (float) ($fm->gpa ?? 0);
                                $status = $fm->status ?? 'FAIL';
                                if ($status === 'FAIL') {
                                    $hasFail = true;
                                }

                                $totalObtained += $obtained;
                                $totalFull += $full;
                                $totalGpaPoints += ($gpa * $credit);

                                $subjectMarks[$sub->id] = [
                                    'final_mark_id' => $fm->id,
                                    'subject_id'    => $sub->id,
                                    'code'          => $sub->code ?? '—',
                                    'name'          => $sub->name ?? '—',
                                    'credit'        => $credit,
                                    'obtained'      => $obtained,
                                    'full'          => $full,
                                    'converted'     => $conv,
                                    'grade'         => $grade,
                                    'gpa'           => number_format($gpa, 2),
                                    'status'        => $status,
                                    'qawmi'         => $fm->qawmi_grade['name_bn'] ?? '',
                                    'raw_ct'        => $fm->raw_class_test,
                                    'raw_mid'       => $fm->raw_midterm,
                                    'raw_final'     => $fm->raw_final,
                                    'att_conv'      => $fm->attendance_converted,
                                    'tamrin'        => $fm->tamrin_mark,
                                    'ct_tamrin'     => $fm->ct_tamrin,
                                    'mid_tamrin'    => $fm->midterm_tamrin,
                                    'fin_tamrin'    => $fm->final_tamrin,
                                ];
                            } else {
                                $hasFail = true;
                                $fMarks = ($examType === 'MIDTERM' ? 50 : ($examType === 'QUIZ' ? 30 : 100));
                                $totalFull += $fMarks;
                                $subjectMarks[$sub->id] = [
                                    'final_mark_id' => null,
                                    'subject_id'    => $sub->id,
                                    'code'          => $sub->code ?? '—',
                                    'name'          => $sub->name ?? '—',
                                    'credit'        => $credit,
                                    'obtained'      => 0,
                                    'full'          => $fMarks,
                                    'converted'     => 0,
                                    'grade'         => 'F',
                                    'gpa'           => '0.00',
                                    'status'        => 'ABSENT',
                                    'qawmi'         => 'রাসিব (অনুত্তীর্ণ)',
                                    'raw_ct'        => null,
                                    'raw_mid'       => null,
                                    'raw_final'     => null,
                                    'att_conv'      => null,
                                    'tamrin'        => null,
                                    'ct_tamrin'     => null,
                                    'mid_tamrin'    => null,
                                    'fin_tamrin'    => null,
                                ];
                            }
                        }

                        $sgpa = $totalCredits > 0 ? round($totalGpaPoints / $totalCredits, 2) : 0.00;
                        $overallPercent = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 1) : 0;

                        $overallGrade = match(true) {
                            $hasFail       => 'F',
                            $sgpa >= 5.00 => 'A+',
                            $sgpa >= 4.00 => 'A',
                            $sgpa >= 3.50 => 'A-',
                            $sgpa >= 3.00 => 'B',
                            $sgpa >= 2.00 => 'C',
                            default        => 'F',
                        };
                        $overallStatus = ($overallGrade !== 'F' && !$hasFail) ? 'PASS' : 'FAIL';
                        $qawmi = FinalMark::calculateQawmiGrade(0, $sgpa);

                        // If a specific subject is selected, extract detailed raw breakdown and criteria conversion
                        $selectedSubMark = $selectedSubject ? ($subjectMarks[$selectedSubject->id] ?? null) : null;
                        $rawCt = $selectedSubMark['raw_ct'] ?? null;
                        $rawMid = $selectedSubMark['raw_mid'] ?? null;
                        $rawFinal = $selectedSubMark['raw_final'] ?? null;
                        $rawAtt = $selectedSubMark['att_conv'] ?? null;
                        $rawTotal = round(($rawCt ?? 0) + ($rawMid ?? 0) + ($rawFinal ?? 0) + ($rawAtt ?? 0), 2);
                        $criteriaTotal = $selectedSubMark ? (float) ($selectedSubMark['converted'] ?? 0) : $totalObtained;

                        $studentsData[] = [
                            'student_id'     => $student->id,
                            'student_name'   => $student->name ?? '—',
                            'student_roll'   => $student->student_code ?? $student->student_id ?? '—',
                            'first_mark_id'  => $selectedSubMark ? ($selectedSubMark['final_mark_id'] ?? $firstFinalMarkId) : $firstFinalMarkId,
                            'subject_marks'  => $subjectMarks,
                            'raw_ct'         => $rawCt,
                            'raw_mid'        => $rawMid,
                            'raw_final'      => $rawFinal,
                            'raw_att'        => $rawAtt,
                            'raw_total'      => $rawTotal,
                            'criteria_total' => $criteriaTotal,
                            'selected_mark'  => $selectedSubMark,
                            'total_obtained' => $totalObtained,
                            'total_full'     => $totalFull,
                            'percentage'     => $overallPercent,
                            'sgpa'           => number_format($sgpa, 2),
                            'grade'          => $overallGrade,
                            'status'         => $overallStatus,
                            'qawmi'          => $qawmi['name_bn'] ?? '—',
                        ];
                    }

                    // Sort descending: If subject selected, by subject criteria total; else by SGPA, then total obtained marks
                    usort($studentsData, function ($a, $b) use ($selectedSubject) {
                        if ($selectedSubject) {
                            if ((float) ($b['criteria_total'] ?? 0) != (float) ($a['criteria_total'] ?? 0)) {
                                return (float) ($b['criteria_total'] ?? 0) <=> (float) ($a['criteria_total'] ?? 0);
                            }
                        }
                        if ((float) $b['sgpa'] != (float) $a['sgpa']) {
                            return (float) $b['sgpa'] <=> (float) $a['sgpa'];
                        }
                        return $b['total_obtained'] <=> $a['total_obtained'];
                    });

                    $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
                    foreach ($studentsData as $idx => &$item) {
                        $pos = $idx + 1;
                        $bnNum = str_replace(range(0, 9), $bnDigits, (string) $pos);
                        $suffix = match($pos) {
                            1 => 'ম',
                            2, 3 => 'য়',
                            4 => 'র্থ',
                            default => 'ম'
                        };
                        $item['merit_rank_bengali'] = $bnNum . $suffix;
                        $item['merit_position'] = $pos;
                    }
                    unset($item);

                    $rankedStudents = collect($studentsData);
                }

                // ─── TAB 2: MANUAL MARKING (পরীক্ষাভিত্তিক তামরিন ও সেমিস্টার উপস্থিতি) ─
                elseif ($tab === 'manual_marking') {
                    if ($request->filled('subject_id')) {
                        $selectedSubject = $subjects->firstWhere('id', $request->subject_id);
                    }
                    if (!$selectedSubject) {
                        $selectedSubject = $subjects->first();
                    }

                    if ($selectedSubject) {
                        $manualMarkingList = FinalMark::with(['student'])
                            ->where('batch_id', $selectedBatch->id)
                            ->where('subject_id', $selectedSubject->id)
                            ->when($isSemesterBased && $selectedSemester, function ($q) use ($selectedSemester) {
                                $q->where('semester_id', $selectedSemester->id);
                            })
                            ->get();

                        // Get all exams for this subject in the selected batch/semester
                        $subjectExams = $exams->where('subject_id', $selectedSubject->id)->values();

                        if ($request->filled('exam_id')) {
                            $selectedExam = $subjectExams->firstWhere('id', $request->exam_id);
                        }

                        // Preload results for these exams keyed by exam_id -> student_id
                        $examIds = $subjectExams->pluck('id');
                        $allExamResults = \App\Models\Result::whereIn('exam_id', $examIds)
                            ->get()
                            ->groupBy('exam_id')
                            ->map(fn($group) => $group->keyBy('student_id'));
                    }
                }

                // ─── TAB 3: 6-SEMESTER COMBINED BATCH MERIT ─────────────────
                elseif ($tab === 'batch_merit') {
                    $allBatchMarks = FinalMark::with(['student', 'semester', 'subject'])
                        ->where('batch_id', $selectedBatch->id)
                        ->get();

                    $studentsGrouped = $allBatchMarks->groupBy('student_id');
                    $batchStudents = [];

                    foreach ($studentsGrouped as $studentId => $marks) {
                        $student = $marks->first()->student;
                        if (!$student) continue;

                        $semestersSummary = [];
                        $totalCredits = 0;
                        $totalWeightedPoints = 0;
                        $totalObtainedAll = 0;

                        foreach ($semesters as $sem) {
                            $semMarks = $marks->where('semester_id', $sem->id);
                            $semCredit = 0;
                            $semPoints = 0;
                            $semObtained = 0;

                            foreach ($semMarks as $fm) {
                                $c = $fm->subject->credit ?? 3;
                                $semCredit += $c;
                                $semPoints += ($fm->gpa * $c);
                                $semObtained += (float) ($fm->total_mark ?? 0);
                            }

                            $semSgpa = $semCredit > 0 ? round($semPoints / $semCredit, 2) : 0.00;
                            $semestersSummary[$sem->id] = [
                                'sgpa'     => $semSgpa,
                                'credit'   => $semCredit,
                                'obtained' => $semObtained,
                            ];

                            $totalCredits += $semCredit;
                            $totalWeightedPoints += $semPoints;
                            $totalObtainedAll += $semObtained;
                        }

                        $cgpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.00;
                        $overallQawmi = FinalMark::calculateQawmiGrade(0, $cgpa);

                        $overallGrade = match(true) {
                            $cgpa >= 5.00 => 'A+',
                            $cgpa >= 4.00 => 'A',
                            $cgpa >= 3.50 => 'A-',
                            $cgpa >= 3.00 => 'B',
                            $cgpa >= 2.00 => 'C',
                            default       => 'F',
                        };

                        $batchStudents[] = [
                            'student_id'        => $student->id,
                            'student_name'      => $student->name ?? '—',
                            'student_roll'      => $student->student_code ?? $student->student_id ?? '—',
                            'semesters_summary' => $semestersSummary,
                            'total_credits'     => $totalCredits,
                            'total_obtained'    => $totalObtainedAll,
                            'cgpa'              => number_format($cgpa, 2),
                            'grade'             => $overallGrade,
                            'qawmi'             => $overallQawmi['name_bn'] ?? '—',
                        ];
                    }

                    // Sort descending by CGPA, then total marks
                    usort($batchStudents, function ($a, $b) {
                        if ((float) $b['cgpa'] != (float) $a['cgpa']) {
                            return (float) $b['cgpa'] <=> (float) $a['cgpa'];
                        }
                        return $b['total_obtained'] <=> $a['total_obtained'];
                    });

                    $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
                    foreach ($batchStudents as $idx => &$item) {
                        $pos = $idx + 1;
                        $bnNum = str_replace(range(0, 9), $bnDigits, (string) $pos);
                        $suffix = match($pos) {
                            1 => 'ম',
                            2, 3 => 'য়',
                            4 => 'র্থ',
                            default => 'ম'
                        };
                        $item['merit_rank_bengali'] = $bnNum . $suffix;
                        $item['merit_position'] = $pos;
                    }
                    unset($item);

                    $batchMeritList = collect($batchStudents);
                }
            }
        }

        // Summary KPI statistics
        $totalStudents = $rankedStudents->count();
        $passedCount   = $rankedStudents->where('status', 'PASS')->count();
        $failedCount   = $rankedStudents->where('status', 'FAIL')->count();
        $passRate      = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100, 1) : 0;
        $avgGpa        = $totalStudents > 0 ? round($rankedStudents->avg(fn($s) => (float) $s['sgpa']), 2) : 0.00;
        $highestScore  = $totalStudents > 0 ? $rankedStudents->max('total_obtained') : 0;

        $summary = [
            'total'         => $totalStudents,
            'passed'        => $passedCount,
            'failed'        => $failedCount,
            'pass_rate'     => $passRate,
            'avg_gpa'       => number_format($avgGpa, 2),
            'highest_score' => $highestScore,
        ];

        return view('admin.result-book.index', compact(
            'tab', 'batches', 'selectedBatch', 'semesters', 'selectedSemester',
            'selectedSubject', 'examType', 'search', 'subjects', 'rankedStudents',
            'manualMarkingList', 'batchMeritList', 'isSemesterBased',
            'isBatchPublished', 'summary', 'exams', 'examLookup',
            'subjectExams', 'selectedExam', 'allExamResults'
        ));
    }

    /**
     * Manual Mark Override for Auditing / Grade Adjustments
     */
    public function override(Request $request, FinalMark $finalMark)
    {
        $validated = $request->validate([
            'class_test_obtained'  => 'nullable|numeric|min:0|max:100',
            'midterm_obtained'     => 'nullable|numeric|min:0|max:100',
            'final_obtained'       => 'nullable|numeric|min:0|max:100',
            'attendance_converted' => 'nullable|numeric|min:0|max:100',
            'ct_tamrin'            => 'nullable|numeric|min:0|max:100',
            'midterm_tamrin'       => 'nullable|numeric|min:0|max:100',
            'final_tamrin'         => 'nullable|numeric|min:0|max:100',
            'tamrin_mark'          => 'nullable|numeric|min:0|max:100',
            'remarks'              => 'nullable|string|max:500',
        ]);

        $ctOb  = $request->filled('class_test_obtained') ? (float) $request->class_test_obtained : null;
        $midOb = $request->filled('midterm_obtained') ? (float) $request->midterm_obtained : null;
        $finOb = $request->filled('final_obtained') ? (float) $request->final_obtained : null;
        $attConv = $request->filled('attendance_converted') ? (float) $request->attendance_converted : null;

        $updates = [
            'class_test_obtained'  => $ctOb,
            'class_test_converted' => $ctOb !== null ? round(($ctOb / FinalMark::CLASS_TEST_FULL) * FinalMark::CLASS_TEST_CONVERT, 2) : null,
            'midterm_obtained'     => $midOb,
            'midterm_converted'    => $midOb !== null ? round(($midOb / FinalMark::MIDTERM_FULL) * FinalMark::MIDTERM_CONVERT, 2) : null,
            'final_obtained'       => $finOb,
            'final_converted'      => $finOb !== null ? round(($finOb / FinalMark::FINAL_FULL) * FinalMark::FINAL_CONVERT, 2) : null,
            'attendance_converted' => $attConv,
            'ct_tamrin'            => $request->filled('ct_tamrin') ? (float) $request->ct_tamrin : null,
            'midterm_tamrin'       => $request->filled('midterm_tamrin') ? (float) $request->midterm_tamrin : null,
            'final_tamrin'         => $request->filled('final_tamrin') ? (float) $request->final_tamrin : null,
            'tamrin_mark'          => $request->filled('tamrin_mark') ? (float) $request->tamrin_mark : null,
        ];

        if ($request->has('remarks')) {
            $finalMark->remarks = $validated['remarks'] ?? null;
        }

        $finalMark->recalculate($updates);
        FinalMark::recalculateMeritRanks($finalMark->batch_id, $finalMark->subject_id, $finalMark->semester_id);

        return back()->with('success', '✅ নম্বর সফলভাবে সংশোধন ও মেধা পুনঃনির্ধারণ করা হয়েছে।');
    }

    /**
     * Unified Override Save (Creates FinalMark if absent, then applies override)
     */
    public function overrideSave(Request $request)
    {
        $validated = $request->validate([
            'final_mark_id'        => 'nullable|integer',
            'student_id'           => 'required_without:final_mark_id|integer',
            'batch_id'             => 'required|integer',
            'subject_id'           => 'required|integer',
            'semester_id'          => 'nullable|integer',
            'class_test_obtained'  => 'nullable|numeric|min:0|max:100',
            'midterm_obtained'     => 'nullable|numeric|min:0|max:100',
            'final_obtained'       => 'nullable|numeric|min:0|max:100',
            'attendance_converted' => 'nullable|numeric|min:0|max:100',
            'ct_tamrin'            => 'nullable|numeric|min:0|max:100',
            'midterm_tamrin'       => 'nullable|numeric|min:0|max:100',
            'final_tamrin'         => 'nullable|numeric|min:0|max:100',
            'tamrin_mark'          => 'nullable|numeric|min:0|max:100',
            'remarks'              => 'nullable|string|max:500',
        ]);

        $finalMark = null;
        if (!empty($validated['final_mark_id'])) {
            $finalMark = FinalMark::find($validated['final_mark_id']);
        }

        if (!$finalMark) {
            $semesterId = $validated['semester_id'] ?? null;
            if (!$semesterId && !empty($validated['batch_id'])) {
                $batch = Batch::find($validated['batch_id']);
                $semesterId = $batch?->current_semester_id;
            }
            $finalMark = FinalMark::firstOrCreate(
                [
                    'batch_id'    => $validated['batch_id'],
                    'student_id'  => $validated['student_id'],
                    'subject_id'  => $validated['subject_id'],
                    'semester_id' => $semesterId,
                ],
                [
                    'total_full'   => 100,
                    'credit'       => 3,
                    'generated_at' => now(),
                ]
            );
        }

        return $this->override($request, $finalMark);
    }

    /**
     * Save Bulk Manual Marks (পরীক্ষাভিত্তিক তামরিন ও সেমিস্টার উপস্থিতি)
     */
    public function saveManualMarksBulk(Request $request)
    {
        $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks'      => 'required|array',
        ]);

        $batchId    = $request->batch_id;
        $subjectId  = $request->subject_id;
        $semesterId = $request->input('semester_id');
        $examId     = $request->input('exam_id');
        $exam       = $examId ? \App\Models\Exam::find($examId) : null;
        $updatedCount = 0;

        foreach ($request->marks as $finalMarkId => $data) {
            $finalMark = FinalMark::find($finalMarkId);
            if (!$finalMark) continue;
            $studentId = $finalMark->student_id;

            if ($exam) {
                // Single Exam Mode: Tamrin and assessment marks belong to this exam
                $tamrinVal  = (isset($data['tamrin_mark']) && $data['tamrin_mark'] !== '') ? (float) $data['tamrin_mark'] : null;
                $mcqVal     = (isset($data['mcq_marks']) && $data['mcq_marks'] !== '') ? (float) $data['mcq_marks'] : null;
                $writtenVal = (isset($data['written_marks']) && $data['written_marks'] !== '') ? (float) $data['written_marks'] : null;
                $vivaVal    = (isset($data['viva_marks']) && $data['viva_marks'] !== '') ? (float) $data['viva_marks'] : null;

                $prevResult = \App\Models\Result::where('student_id', $studentId)
                    ->where('subject_id', $exam->subject_id)
                    ->latest('attempt_no')
                    ->first();
                $attemptNo = $prevResult ? $prevResult->attempt_no + 1 : 1;

                $res = \App\Models\Result::firstOrNew([
                    'exam_id'    => $exam->id,
                    'student_id' => $studentId,
                ]);
                if (!$res->exists) {
                    $res->attempt_no = $attemptNo;
                }

                $resMcq = $mcqVal ?? $res->mcq_marks;
                $resWritten = $writtenVal ?? $res->written_marks;
                $resViva = $vivaVal ?? $res->viva_marks;
                $totalExamMark = ($resMcq ?? 0) + ($resWritten ?? 0) + ($tamrinVal ?? 0) + ($resViva ?? 0);

                $res->tamrin_marks  = $tamrinVal;
                $res->mcq_marks     = $resMcq;
                $res->written_marks = $resWritten;
                $res->viva_marks    = $resViva;
                $res->marks         = $totalExamMark;
                $res->status        = $totalExamMark >= $exam->pass_marks ? 'PASS' : 'FAIL';
                $pct = $exam->full_marks > 0 ? (($totalExamMark / $exam->full_marks) * 100) : 0;
                $gInfo = FinalMark::calculateGrade($pct);
                $res->grade = $gInfo['grade'];
                $res->save();

                // Sync ExamSubmission if exists
                $sub = \App\Models\ExamSubmission::where('exam_id', $exam->id)->where('student_id', $studentId)->first();
                if ($sub) {
                    $sub->update([
                        'tamrin_score'  => $tamrinVal ?? $sub->tamrin_score,
                        'total_score'   => $totalExamMark,
                    ]);
                }

                // Sync to FinalMark
                $updates = [];
                if ($exam->type === 'QUIZ') {
                    $updates['ct_tamrin'] = $tamrinVal;
                    $updates['class_test_obtained'] = $totalExamMark;
                    $updates['class_test_converted'] = round(($totalExamMark / FinalMark::CLASS_TEST_FULL) * FinalMark::CLASS_TEST_CONVERT, 2);
                } elseif ($exam->type === 'MIDTERM') {
                    $updates['midterm_tamrin'] = $tamrinVal;
                    $updates['midterm_obtained'] = $totalExamMark;
                    $updates['midterm_converted'] = round(($totalExamMark / FinalMark::MIDTERM_FULL) * FinalMark::MIDTERM_CONVERT, 2);
                } elseif ($exam->type === 'FINAL') {
                    $updates['final_tamrin'] = $tamrinVal;
                    $updates['final_obtained'] = $totalExamMark;
                    $updates['final_converted'] = round(($totalExamMark / FinalMark::FINAL_FULL) * FinalMark::FINAL_CONVERT, 2);
                }

                if (isset($data['remarks'])) {
                    $finalMark->remarks = $data['remarks'];
                }
                $finalMark->recalculate($updates);
            } else {
                // All-Exams / Semester Mode
                $updates = [];
                // Attendance is strictly semester-level
                if (isset($data['attendance_converted'])) {
                    $updates['attendance_converted'] = is_numeric($data['attendance_converted']) ? (float) $data['attendance_converted'] : null;
                }

                // Exam-wise Tamrins
                if (isset($data['ct_tamrin'])) {
                    $ctT = is_numeric($data['ct_tamrin']) ? (float) $data['ct_tamrin'] : null;
                    $updates['ct_tamrin'] = $ctT;
                    if ($finalMark->class_test_obtained !== null && $ctT !== null) {
                        $diff = $ctT - ($finalMark->ct_tamrin ?? 0);
                        $newCtOb = max(0, min(FinalMark::CLASS_TEST_FULL, $finalMark->class_test_obtained + $diff));
                        $updates['class_test_obtained'] = $newCtOb;
                        $updates['class_test_converted'] = round(($newCtOb / FinalMark::CLASS_TEST_FULL) * FinalMark::CLASS_TEST_CONVERT, 2);
                    }
                }
                if (isset($data['midterm_tamrin'])) {
                    $midT = is_numeric($data['midterm_tamrin']) ? (float) $data['midterm_tamrin'] : null;
                    $updates['midterm_tamrin'] = $midT;
                    if ($finalMark->midterm_obtained !== null && $midT !== null) {
                        $diff = $midT - ($finalMark->midterm_tamrin ?? 0);
                        $newMidOb = max(0, min(FinalMark::MIDTERM_FULL, $finalMark->midterm_obtained + $diff));
                        $updates['midterm_obtained'] = $newMidOb;
                        $updates['midterm_converted'] = round(($newMidOb / FinalMark::MIDTERM_FULL) * FinalMark::MIDTERM_CONVERT, 2);
                    }
                }
                if (isset($data['final_tamrin'])) {
                    $finT = is_numeric($data['final_tamrin']) ? (float) $data['final_tamrin'] : null;
                    $updates['final_tamrin'] = $finT;
                    if ($finalMark->final_obtained !== null && $finT !== null) {
                        $diff = $finT - ($finalMark->final_tamrin ?? 0);
                        $newFinOb = max(0, min(FinalMark::FINAL_FULL, $finalMark->final_obtained + $diff));
                        $updates['final_obtained'] = $newFinOb;
                        $updates['final_converted'] = round(($newFinOb / FinalMark::FINAL_FULL) * FinalMark::FINAL_CONVERT, 2);
                    }
                }

                // If individual exams' tamrin passed in exam_tamrins
                if (isset($data['exam_tamrins']) && is_array($data['exam_tamrins'])) {
                    foreach ($data['exam_tamrins'] as $eId => $tScore) {
                        $targetExam = \App\Models\Exam::find($eId);
                        if (!$targetExam) continue;
                        $tVal = is_numeric($tScore) ? (float) $tScore : null;

                        $res = \App\Models\Result::firstOrNew([
                            'exam_id'    => $targetExam->id,
                            'student_id' => $studentId,
                        ]);
                        $res->tamrin_marks = $tVal;
                        $res->marks = ($res->mcq_marks ?? 0) + ($res->written_marks ?? 0) + ($tVal ?? 0) + ($res->viva_marks ?? 0);
                        $res->status = $res->marks >= $targetExam->pass_marks ? 'PASS' : 'FAIL';
                        $res->save();

                        if ($targetExam->type === 'QUIZ') {
                            $updates['ct_tamrin'] = $tVal;
                            $updates['class_test_obtained'] = $res->marks;
                            $updates['class_test_converted'] = round(($res->marks / FinalMark::CLASS_TEST_FULL) * FinalMark::CLASS_TEST_CONVERT, 2);
                        } elseif ($targetExam->type === 'MIDTERM') {
                            $updates['midterm_tamrin'] = $tVal;
                            $updates['midterm_obtained'] = $res->marks;
                            $updates['midterm_converted'] = round(($res->marks / FinalMark::MIDTERM_FULL) * FinalMark::MIDTERM_CONVERT, 2);
                        } elseif ($targetExam->type === 'FINAL') {
                            $updates['final_tamrin'] = $tVal;
                            $updates['final_obtained'] = $res->marks;
                            $updates['final_converted'] = round(($res->marks / FinalMark::FINAL_FULL) * FinalMark::FINAL_CONVERT, 2);
                        }
                    }
                }

                if (isset($data['remarks'])) {
                    $finalMark->remarks = $data['remarks'];
                }
                $finalMark->recalculate($updates);
            }
            $updatedCount++;
        }

        FinalMark::recalculateMeritRanks($batchId, $subjectId, $semesterId);

        $msg = $exam 
            ? "✅ মোট {$updatedCount} জন শিক্ষার্থীর '{$exam->title}' পরীক্ষার মূল্যায়ন ও তামরিন নম্বর সফলভাবে সংরক্ষিত হয়েছে।"
            : "✅ মোট {$updatedCount} জন শিক্ষার্থীর পরীক্ষাভিত্তিক তামরিন ও সেমিস্টার উপস্থিতি নম্বর সফলভাবে সংরক্ষিত হয়েছে।";

        return back()->with('success', $msg);
    }

    /**
     * Toggle Publish / Unpublish final marks for batch + semester
     */
    public function publishToggle(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
        ]);

        $batchId    = $request->batch_id;
        $semesterId = $request->input('semester_id');

        $query = FinalMark::where('batch_id', $batchId);
        if ($semesterId) {
            $query->where(function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)->orWhereNull('semester_id');
            });
        }

        $anyPublished = (clone $query)->where('is_published', true)->exists();
        $newStatus = !$anyPublished;

        // Recalculate merit ranks for all subjects in this semester before publishing
        $subjectIds = (clone $query)->pluck('subject_id')->unique();
        foreach ($subjectIds as $sId) {
            FinalMark::recalculateMeritRanks($batchId, $sId, $semesterId);
        }

        $query->update([
            'is_published' => $newStatus,
            'published_at' => $newStatus ? now() : null,
        ]);

        $msg = $newStatus
            ? '📢 সফলভাবে ব্যাচের সেমিস্টার ফলাফল প্রকাশ করা হয়েছে। শিক্ষার্থীরা এখন তাদের ড্যাশবোর্ড থেকে নম্বর ও মেধা স্থান দেখতে পারবে।'
            : '⚠️ ফলাফল প্রকাশ প্রত্যাহার (Unpublished) করা হয়েছে।';

        return back()->with('success', $msg);
    }

    /**
     * Automatically compute attendance marks from completed class sessions
     */
    public function autoAttendance(Request $request)
    {
        $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $batchId   = $request->batch_id;
        $subjectId = $request->subject_id;
        $criteria  = FinalMark::getCriteria();

        $classSessions = ClassSession::where('batch_id', $batchId)
            ->where('subject_id', $subjectId)
            ->where('status', 'COMPLETED')
            ->pluck('id');

        $totalSessions = $classSessions->count();
        if ($totalSessions === 0) {
            return back()->with('error', 'এই বিষয় ও ব্যাচের জন্য কোনো সমাপ্ত ক্লাস সেশন পাওয়া যায়নি (No completed class sessions found)।');
        }

        $finalMarks = FinalMark::where('batch_id', $batchId)->where('subject_id', $subjectId)->get();
        $updated = 0;

        foreach ($finalMarks as $fm) {
            $presentCount = Attendance::whereIn('class_session_id', $classSessions)
                ->where('student_id', $fm->student_id)
                ->whereIn('status', ['PRESENT', 'LATE'])
                ->count();

            $attendancePercent   = round(($presentCount / $totalSessions) * 100, 2);
            $attendanceConverted = round(($attendancePercent / 100) * $criteria['attendance_convert'], 2);

            $fm->attendance_percent = $attendancePercent;
            $fm->recalculate(['attendance_converted' => $attendanceConverted]);
            $updated++;
        }

        FinalMark::recalculateMeritRanks($batchId, $subjectId, $request->input('semester_id'));

        return back()->with('success', "✅ মোট {$updated} জন শিক্ষার্থীর ক্লাসে উপস্থিতির হার অনুযায়ী নম্বর অটো-ক্যালকুলেট করা হয়েছে।");
    }

    /**
     * 6-Semester Academic Transcript
     */
    public function transcript(Student $student)
    {
        $student->load(['enrollments.batch.course.semesters', 'enrollments.course.semesters']);

        $primaryEnrollment = $student->enrollments->whereIn('status', ['ACTIVE', 'COMPLETED', 'active', 'completed'])->first()
            ?? $student->enrollments->first();

        $course = $primaryEnrollment?->course ?? $primaryEnrollment?->batch?->course;

        $finalMarks = FinalMark::with(['subject', 'semester', 'batch'])
            ->where('student_id', $student->id)
            ->get();

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
        }

        $cgpa = $totalCreditsAttempted > 0 ? round($totalWeightedGpaPoints / $totalCreditsAttempted, 2) : 0.00;
        $overallQawmiGrade = FinalMark::calculateQawmiGrade(0, $cgpa);
        $overallStatus = ($cgpa >= 2.00 && $totalCreditsEarned >= ($totalCreditsAttempted * 0.75)) ? 'PASSED' : 'IN_PROGRESS';

        return view('admin.students.transcript', compact(
            'student', 'course', 'primaryEnrollment',
            'semestersData', 'totalCreditsAttempted', 'totalCreditsEarned',
            'cgpa', 'overallQawmiGrade', 'overallStatus'
        ));
    }

    /**
     * AJAX endpoint: Get course semesters for a batch (Used in Promotion & ResultBook)
     */
    public function getBatchSubjects(Request $request)
    {
        $batchId = $request->input('batch_id');
        $batch = Batch::with(['course.semesters' => function ($q) {
            $q->orderBy('sequence_no');
        }, 'semesterPosition'])->find($batchId);

        if (!$batch || !$batch->course) {
            return response()->json([
                'has_semesters' => false,
                'semesters'     => [],
            ]);
        }

        $course = $batch->course;
        $hasSemesters = ($course->type === 'SEMESTER_BASED' && $course->semesters->isNotEmpty());
        $runningSemesterId = $batch->semesterPosition?->current_semester_id
            ?? $batch->enrollments()->whereNotNull('semester_id')->latest()->value('semester_id')
            ?? $course->semesters->first()?->id;

        return response()->json([
            'has_semesters' => $hasSemesters,
            'semesters'     => $course->semesters->map(fn($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'sequence_no' => $s->sequence_no,
                'is_running'  => ($s->id == $runningSemesterId),
            ])->values(),
            'running_semester_id' => $runningSemesterId,
        ]);
    }
}
