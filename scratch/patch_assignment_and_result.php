<?php

// 1. Patch routes/admin.php
$adminRoutesPath = __DIR__ . '/../routes/admin.php';
$arc = file_get_contents($adminRoutesPath);

$targetAR1 = "Route::post('assignment-submissions/{submission}/override', [\App\Http\Controllers\Admin\AssignmentController::class, 'overrideSubmission'])->name('assignments.submissions.override');";
$replaceAR1 = "Route::post('assignment-submissions/{submission}/override', [\App\Http\Controllers\Admin\AssignmentController::class, 'overrideSubmission'])->name('assignments.submissions.override');
        Route::post('assignment-appeals/{appeal}/approve', [\App\Http\Controllers\Admin\AssignmentController::class, 'approveAppeal'])->name('assignments.appeals.approve');
        Route::post('assignment-appeals/{appeal}/reject', [\App\Http\Controllers\Admin\AssignmentController::class, 'rejectAppeal'])->name('assignments.appeals.reject');";

$arc = str_replace($targetAR1, $replaceAR1, $arc);

$targetAR2 = "Route::post('result-book/auto-attendance', [\App\Http\Controllers\Admin\ResultBookController::class, 'autoAttendance'])->name('result-book.auto-attendance');";
$replaceAR2 = "Route::post('result-book/auto-attendance', [\App\Http\Controllers\Admin\ResultBookController::class, 'autoAttendance'])->name('result-book.auto-attendance');
        Route::post('result-book/auto-assignment', [\App\Http\Controllers\Admin\ResultBookController::class, 'autoAssignment'])->name('result-book.auto-assignment');";

$arc = str_replace($targetAR2, $replaceAR2, $arc);
file_put_contents($adminRoutesPath, $arc);
echo "routes/admin.php patched.\n";

// 2. Patch routes/student.php
$studentRoutesPath = __DIR__ . '/../routes/student.php';
$src = file_get_contents($studentRoutesPath);

$targetSR = "Route::post('assignments/{assignment}/submit',   [\App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');";
$replaceSR = "Route::post('assignments/{assignment}/submit',   [\App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');
        Route::post('assignments/{assignment}/appeal',   [\App\Http\Controllers\Student\AssignmentController::class, 'appeal'])->name('assignments.appeal');";

$src = str_replace($targetSR, $replaceSR, $src);
file_put_contents($studentRoutesPath, $src);
echo "routes/student.php patched.\n";

// 3. Patch app/Http/Controllers/Admin/AssignmentController.php
$adminAssignCtrlPath = __DIR__ . '/../app/Http/Controllers/Admin/AssignmentController.php';
$aac = file_get_contents($adminAssignCtrlPath);

$aacMethods = '
    public function approveAppeal(\App\Models\AssignmentAppeal $appeal)
    {
        $appeal->update([
            ' . "'status'      => 'APPROVED',\n" .
            '            ' . "'reviewed_by' => auth()->id(),\n" .
            '            ' . "'reviewed_at' => now(),\n" .
            '        ]);

        return back()->with(' . "'success', 'দেরিতে অ্যাসাইনমেন্ট জমা দেওয়ার আবেদন সফলভাবে অনুমোদন করা হয়েছে (কোনো ফি ছাড়া)।'" . ');
    }

    public function rejectAppeal(\App\Models\AssignmentAppeal $appeal)
    {
        $appeal->update([
            ' . "'status'      => 'REJECTED',\n" .
            '            ' . "'reviewed_by' => auth()->id(),\n" .
            '            ' . "'reviewed_at' => now(),\n" .
            '        ]);

        return back()->with(' . "'info', 'দেরিতে অ্যাসাইনমেন্ট জমা দেওয়ার আবেদন বাতিল করা হয়েছে।'" . ');
    }
}';

$aac = preg_replace('/\}\s*$/', $aacMethods, trim($aac));
file_put_contents($adminAssignCtrlPath, $aac);
echo "Admin/AssignmentController.php patched.\n";

// 4. Patch app/Http/Controllers/Student/AssignmentController.php
$studAssignCtrlPath = __DIR__ . '/../app/Http/Controllers/Student/AssignmentController.php';
$sac = file_get_contents($studAssignCtrlPath);

$sacMethods = '
    public function appeal(Request $request, Assignment $assignment)
    {
        $student = $this->student();
        if (!$student) {
            return back()->with(' . "'error', 'শিক্ষার্থী প্রোফাইল পাওয়া যায়নি।'" . ');
        }

        $validated = $request->validate([
            ' . "'reason' => 'required|string|min:5|max:1000',\n" .
            '        ]);

        $existing = \App\Models\AssignmentAppeal::where(' . "'assignment_id', \$assignment->id)\n" .
            '            ->where(' . "'student_id', \$student->id)\n" .
            '            ->where(' . "'status', 'PENDING')\n" .
            '            ->first();

        if ($existing) {
            return back()->with(' . "'info', 'আপনার একটি আবেদন ইতিমধ্যে প্রক্রিয়াধীন রয়েছে। অনুগ্রহ করে অপেক্ষা করুন।'" . ');
        }

        \App\Models\AssignmentAppeal::create([
            ' . "'assignment_id' => \$assignment->id,\n" .
            '            ' . "'student_id'    => \$student->id,\n" .
            '            ' . "'reason'        => \$validated['reason'],\n" .
            '            ' . "'status'        => 'PENDING',\n" .
            '        ]);

        return back()->with(' . "'success', 'দেরিতে জমা দেওয়ার আবেদন সফলভাবে পাঠানো হয়েছে। কর্তৃপক্ষ অনুমোদন করলে আপনি কোনো ফি ছাড়াই জমা দিতে পারবেন।'" . ');
    }
}';

$sac = preg_replace('/\}\s*$/', $sacMethods, trim($sac));
file_put_contents($studAssignCtrlPath, $sac);
echo "Student/AssignmentController.php patched.\n";

// 5. Patch app/Http/Controllers/Admin/ResultBookController.php
$rbPath = __DIR__ . '/../app/Http/Controllers/Admin/ResultBookController.php';
$rbc = file_get_contents($rbPath);

$autoAssignMethod = '
    /**
     * Automatically compute and sync assignment marks to FinalMark result sheet
     */
    public function autoAssignment(Request $request)
    {
        $request->validate([
            \'batch_id\'   => \'required|exists:batches,id\',
            \'subject_id\' => \'required|exists:subjects,id\',
        ]);

        $batchId   = $request->batch_id;
        $subjectId = $request->subject_id;
        $examId    = $request->input(\'exam_id\');

        $assignments = \App\Models\Assignment::where(\'subject_id\', $subjectId)
            ->where(function ($q) use ($batchId) {
                $q->whereNull(\'batch_id\')->orWhere(\'batch_id\', $batchId);
            })
            ->get();

        if ($assignments->isEmpty()) {
            return back()->with(\'error\', \'এই বিষয় ও ব্যাচের জন্য কোনো অ্যাসাইনমেন্ট পাওয়া যায়নি।\');
        }

        $assignmentIds = $assignments->pluck(\'id\');
        $finalMarks = FinalMark::where(\'batch_id\', $batchId)->where(\'subject_id\', $subjectId)->get();
        $updated = 0;

        foreach ($finalMarks as $fm) {
            $submissions = \App\Models\AssignmentSubmission::whereIn(\'assignment_id\', $assignmentIds)
                ->where(\'student_id\', $fm->student_id)
                ->where(\'status\', \'GRADED\')
                ->whereNotNull(\'obtained_marks\')
                ->get();

            if ($submissions->isEmpty()) {
                continue;
            }

            $totalPossible = 0.0;
            $totalObtained = 0.0;
            foreach ($submissions as $sub) {
                $assign = $assignments->firstWhere(\'id\', $sub->assignment_id);
                $fullM = (float)($assign?->total_marks ?? 100);
                if ($fullM > 0) {
                    $totalPossible += $fullM;
                    $totalObtained += (float)$sub->obtained_marks;
                }
            }

            if ($totalPossible > 0) {
                $selectedExam = $examId ? \App\Models\Exam::find($examId) : null;
                $targetMarks = 10.0;
                if ($selectedExam && $selectedExam->has_tamrin && $selectedExam->tamrin_marks > 0) {
                    $targetMarks = (float)$selectedExam->tamrin_marks;
                }

                $calculatedMark = round(($totalObtained / $totalPossible) * $targetMarks, 2);

                if ($selectedExam) {
                    if ($selectedExam->type === \'MIDTERM\') {
                        $fm->recalculate([\'midterm_tamrin\' => $calculatedMark]);
                    } elseif ($selectedExam->type === \'FINAL\') {
                        $fm->recalculate([\'final_tamrin\' => $calculatedMark]);
                    } else {
                        $fm->recalculate([\'ct_tamrin\' => $calculatedMark]);
                    }
                } else {
                    $fm->recalculate([\'tamrin_mark\' => $calculatedMark]);
                }
                $updated++;
            }
        }

        FinalMark::recalculateMeritRanks($batchId, $subjectId, $request->input(\'semester_id\'));

        return back()->with(\'success\', "মোট {$updated} জন শিক্ষার্থীর অ্যাসাইনমেন্ট/তামরিন মার্ক সফলভাবে স্বয়ংক্রিয়ভাবে রেজাল্ট শীটে আপডেট করা হয়েছে।");
    }
}';

$rbc = preg_replace('/\}\s*$/', $autoAssignMethod, trim($rbc));
file_put_contents($rbPath, $rbc);
echo "Admin/ResultBookController.php patched.\n";
