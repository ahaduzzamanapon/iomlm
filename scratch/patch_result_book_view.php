<?php

$rbPath = __DIR__ . '/../resources/views/admin/result-book/index.blade.php';
$content = file_get_contents($rbPath);

$target = '</form>' . "\n" . '            </div>' . "\n\n" . '            {{-- Bulk Manual Marking Form --}}';

$buttonHtml = '</form>
                <form method="POST" action="{{ route(\'admin.result-book.auto-assignment\') }}" style="margin:0">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    @if($isSemesterBased && $selectedSemester)
                        <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                    @endif
                    @if($selectedExam)
                        <input type="hidden" name="exam_id" value="{{ $selectedExam->id }}">
                    @endif
                    <button type="submit" onclick="return confirm(\'আপনি কি শিক্ষার্থীদের জমাকৃত অ্যাসাইনমেন্ট/তামরিন নম্বর স্বয়ংক্রিয়ভাবে রেজাল্ট শীটে সিঙ্ক করতে চান?\')"
                            style="background:#059669; color:#fff; border:none; padding:8px 16px; border-radius:8px; font-weight:800; font-size:12.5px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-family:\'Kalpurush\',sans-serif">
                        <i class="fa-solid fa-file-signature"></i> অটো অ্যাসাইনমেন্ট মার্ক সিঙ্ক করুন
                    </button>
                </form>
            </div>

            {{-- Bulk Manual Marking Form --}}';

$content = str_replace(
    str_replace("\r\n", "\n", $target),
    str_replace("\r\n", "\n", $buttonHtml),
    str_replace("\r\n", "\n", $content)
);

file_put_contents($rbPath, $content);
echo "result-book index patched.\n";
