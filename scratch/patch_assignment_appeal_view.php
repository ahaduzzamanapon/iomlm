<?php

// 1. Update Student/AssignmentController.php show method
$studCtrlPath = __DIR__ . '/../app/Http/Controllers/Student/AssignmentController.php';
$sc = file_get_contents($studCtrlPath);

$targetShow = 'public function show(Assignment $assignment)
    {
        $student = $this->student();
        $assignment->load([\'subject\', \'batch\', \'teacher\']);

        $submission = AssignmentSubmission::where(\'assignment_id\', $assignment->id)
            ->where(\'student_id\', $student?->id)
            ->first();

        return view(\'student.assignments.show\', compact(\'assignment\', \'submission\', \'student\'));
    }';

$replacementShow = 'public function show(Assignment $assignment)
    {
        $student = $this->student();
        $assignment->load([\'subject\', \'batch\', \'teacher\']);

        $submission = AssignmentSubmission::where(\'assignment_id\', $assignment->id)
            ->where(\'student_id\', $student?->id)
            ->first();

        $appeal = \App\Models\AssignmentAppeal::where(\'assignment_id\', $assignment->id)
            ->where(\'student_id\', $student?->id)
            ->latest()
            ->first();

        return view(\'student.assignments.show\', compact(\'assignment\', \'submission\', \'student\', \'appeal\'));
    }';

$sc = str_replace(
    str_replace("\r\n", "\n", $targetShow),
    str_replace("\r\n", "\n", $replacementShow),
    str_replace("\r\n", "\n", $sc)
);

file_put_contents($studCtrlPath, $sc);
echo "Student AssignmentController show method patched.\n";

// 2. Update resources/views/student/assignments/show.blade.php
$viewPath = __DIR__ . '/../resources/views/student/assignments/show.blade.php';
$vc = file_get_contents($viewPath);

$targetViewBlock = '@if(!$submission || $submission->status !== \'GRADED\')
                        <form method="POST" action="{{ route(\'student.assignments.submit\', $assignment) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>সমাধান ফাইল আপলোড করুন <span class="required">*</span></label>
                                <input type="file" name="submission_file" class="form-control" required accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                                <small style="font-size:11px;color:#64748b">PDF, Word Document, Zip বা ইমেজ (সর্বোচ্চ ৫০MB)</small>
                            </div>

                            <div class="form-group">
                                <label>কোনো নোট বা বক্তব্য (ঐচ্ছিক)</label>
                                <textarea name="student_note" class="form-control" rows="2" placeholder="শিক্ষকের উদ্দেশ্যে কোনো বার্তা থাকলে লিখুন..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;display:flex;align-items:center;justify-content:center;gap:6px">
                                <i class="fa-solid fa-paper-plane"></i> {{ $submission ? \'পুনরায় জমা দিন\' : \'অ্যাসাইনমেন্ট জমা দিন\' }}
                            </button>
                        </form>
                    @endif';

$replacementViewBlock = '@if(!$submission || $submission->status !== \'GRADED\')
                        @php
                            $isExpired = $assignment->isExpired();
                            $hasApprovedAppeal = isset($appeal) && $appeal->isApproved();
                            $hasPendingAppeal = isset($appeal) && $appeal->isPending();
                        @endphp

                        @if($isExpired && !$hasApprovedAppeal)
                            @if($hasPendingAppeal)
                                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:14px;font-family:\'Kalpurush\',sans-serif">
                                    <div style="font-size:13px;font-weight:700;color:#1e40af;display:flex;align-items:center;gap:6px;margin-bottom:6px">
                                        <i class="fa-solid fa-hourglass-half" style="color:#2563eb"></i> দেরিতে জমার আবেদন প্রক্রিয়াধীন
                                    </div>
                                    <p style="font-size:12px;color:#1e3a8a;margin:0;line-height:1.5">
                                        দেরিতে অ্যাসাইনমেন্ট জমা দেওয়ার জন্য আপনার আবেদনটি পর্যালোচনাধীন রয়েছে। শিক্ষক/কর্তৃপক্ষ অনুমোদন দেওয়া মাত্রই আপনি কোনো ফি ছাড়াই খাতা জমা দিতে পারবেন।
                                    </p>
                                </div>
                            @else
                                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px;font-family:\'Kalpurush\',sans-serif">
                                    <div style="font-size:13px;font-weight:700;color:#92400e;display:flex;align-items:center;gap:6px;margin-bottom:6px">
                                        <i class="fa-solid fa-triangle-exclamation" style="color:#d97706"></i> জমার নির্ধারিত ডেডলাইন শেষ হয়েছে
                                    </div>
                                    <p style="font-size:12px;color:#78350f;margin:0 0 12px 0;line-height:1.5">
                                        যৌক্তিক কারণে সময়মতো জমা দিতে না পেরে থাকলে কারণ উল্লেখ করে দেরিতে জমা দেওয়ার আবেদন করুন। কর্তৃপক্ষ অনুমোদন করলে আপনি কোনো ফি ছাড়াই জমা দিতে পারবেন।
                                    </p>
                                    <form method="POST" action="{{ route(\'student.assignments.appeal\', $assignment) }}">
                                        @csrf
                                        <div class="form-group" style="margin-bottom:10px">
                                            <label style="font-size:12px;font-weight:600;color:#78350f">দেরিতে জমার কারণ <span class="required">*</span></label>
                                            <textarea name="reason" class="form-control" rows="3" required placeholder="দেরিতে জমার কারণ বিস্তারিত লিখুন..." style="font-size:12px;font-family:\'Kalpurush\',sans-serif"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-warning btn-sm" style="width:100%;font-weight:700;font-family:\'Kalpurush\',sans-serif">
                                            <i class="fa-solid fa-paper-plane"></i> দেরিতে জমার আবেদন করুন (বিনা ফিতে)
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @else
                            @if($isExpired && $hasApprovedAppeal)
                                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-family:\'Kalpurush\',sans-serif;font-size:12px;color:#065f46">
                                    <i class="fa-solid fa-circle-check" style="color:#059669"></i> আপনার দেরিতে জমার আবেদন অনুমোদিত হয়েছে। কোনো ফি ছাড়াই আপনি এখন জমা দিতে পারেন।
                                </div>
                            @endif

                            <form method="POST" action="{{ route(\'student.assignments.submit\', $assignment) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label>সমাধান ফাইল আপলোড করুন <span class="required">*</span></label>
                                    <input type="file" name="submission_file" class="form-control" required accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                                    <small style="font-size:11px;color:#64748b">PDF, Word Document, Zip বা ইমেজ (সর্বোচ্চ ৫০MB)</small>
                                </div>

                                <div class="form-group">
                                    <label>কোনো নোট বা বক্তব্য (ঐচ্ছিক)</label>
                                    <textarea name="student_note" class="form-control" rows="2" placeholder="শিক্ষকের উদ্দেশ্যে কোনো বার্তা থাকলে লিখুন..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;display:flex;align-items:center;justify-content:center;gap:6px;font-family:\'Kalpurush\',sans-serif">
                                    <i class="fa-solid fa-paper-plane"></i> {{ $submission ? \'পুনরায় জমা দিন\' : \'অ্যাসাইনমেন্ট জমা দিন\' }}
                                </button>
                            </form>
                        @endif
                    @endif';

$vc = str_replace(
    str_replace("\r\n", "\n", $targetViewBlock),
    str_replace("\r\n", "\n", $replacementViewBlock),
    str_replace("\r\n", "\n", $vc)
);

file_put_contents($viewPath, $vc);
echo "Student Assignment show view patched.\n";
