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
                                    'tajweed'       => $fm->tajweed_mark,
                                    'dns'           => $fm->dns_mark,
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
                                    'tajweed'       => null,
                                    'dns'           => null,
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

                        $studentsData[] = [
                            'student_id'     => $student->id,
                            'student_name'   => $student->name ?? '—',
                            'student_roll'   => $student->student_code ?? $student->student_id ?? '—',
                            'first_mark_id'  => $firstFinalMarkId,
                            'subject_marks'  => $subjectMarks,
                            'total_obtained' => $totalObtained,
                            'total_full'     => $totalFull,
                            'percentage'     => $overallPercent,
                            'sgpa'           => number_format($sgpa, 2),
                            'grade'          => $overallGrade,
                            'status'         => $overallStatus,
                            'qawmi'          => $qawmi['name_bn'] ?? '—',
                        ];
                    }

                    // Sort descending: SGPA first, then total obtained marks
                    usort($studentsData, function ($a, $b) {
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

                // ─── TAB 2: MANUAL MARKING (তামরিন, তাজবীদ, DNS, এটেন্ডেন্স) ─
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
            'isBatchPublished', 'summary'
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
            'tamrin_mark'          => 'nullable|numeric|min:0|max:100',
            'tajweed_mark'         => 'nullable|numeric|min:0|max:100',
            'dns_mark'             => 'nullable|numeric|min:0|max:100',
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
            'tamrin_mark'          => $request->filled('tamrin_mark') ? (float) $request->tamrin_mark : null,
            'tajweed_mark'         => $request->filled('tajweed_mark') ? (float) $request->tajweed_mark : null,
            'dns_mark'             => $request->filled('dns_mark') ? (float) $request->dns_mark : null,
        ];

        if ($request->has('remarks')) {
            $finalMark->remarks = $validated['remarks'] ?? null;
        }

        $finalMark->recalculate($updates);
        FinalMark::recalculateMeritRanks($finalMark->batch_id, $finalMark->subject_id, $finalMark->semester_id);

        return back()->with('success', '✅ নম্বর সফলভাবে সংশোধন ও মেধা পুনঃনির্ধারণ করা হয়েছে।');
    }

    /**
     * Save Bulk Manual Marks (তামরিন, তাজবীদ, DNS, এটেন্ডেন্স)
     */
    public function saveManualMarksBulk(Request $request)
    {
        $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks'      => 'required|array',
        ]);

        $batchId   = $request->batch_id;
        $subjectId = $request->subject_id;
        $semesterId = $request->input('semester_id');
        $updatedCount = 0;

        foreach ($request->marks as $finalMarkId => $data) {
            $finalMark = FinalMark::find($finalMarkId);
            if (!$finalMark) continue;

            $updates = [];
            if (isset($data['tamrin_mark'])) {
                $updates['tamrin_mark'] = is_numeric($data['tamrin_mark']) ? (float) $data['tamrin_mark'] : null;
            }
            if (isset($data['tajweed_mark'])) {
                $updates['tajweed_mark'] = is_numeric($data['tajweed_mark']) ? (float) $data['tajweed_mark'] : null;
            }
            if (isset($data['dns_mark'])) {
                $updates['dns_mark'] = is_numeric($data['dns_mark']) ? (float) $data['dns_mark'] : null;
            }
            if (isset($data['attendance_converted'])) {
                $updates['attendance_converted'] = is_numeric($data['attendance_converted']) ? (float) $data['attendance_converted'] : null;
            }
            if (isset($data['remarks'])) {
                $finalMark->remarks = $data['remarks'];
            }

            $finalMark->recalculate($updates);
            $updatedCount++;
        }

        FinalMark::recalculateMeritRanks($batchId, $subjectId, $semesterId);

        return back()->with('success', "✅ মোট {$updatedCount} জন শিক্ষার্থীর ম্যানুয়াল মার্ক (তামরিন, তাজবীদ, DNS ও এটেন্ডেন্স) সফলভাবে সংরক্ষিত হয়েছে।");
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
}
