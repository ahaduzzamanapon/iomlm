<?php

$takePath = __DIR__ . '/../resources/views/student/exams/take.blade.php';
$tc = file_get_contents($takePath);

$targetSections = <<<'EOD'
@php
                $mcqQuestions     = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'MCQ');
                $writtenQuestions = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'WRITTEN');
                $qSerial = 0;
            @endphp
EOD;

$replacementSections = <<<'EOD'
@php
                $mcqQuestions     = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'MCQ');
                $writtenQuestions = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'WRITTEN');
                $hasBoth = $mcqQuestions->count() > 0 && ($writtenQuestions->count() > 0 || !empty($exam->written_question_pdf));
                $qSerial = 0;
            @endphp

            {{-- Section Navigation Tabs (When exam has both MCQ & Written) --}}
            @if($hasBoth)
            <div style="display:flex;gap:12px;margin-bottom:20px;background:#f8fafc;padding:8px 12px;border-radius:12px;border:1px solid #e2e8f0;font-family:'Kalpurush',sans-serif">
                <button type="button" id="tabBtnMcq" onclick="switchExamTab('mcq')"
                        style="flex:1;padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;gap:8px;background:#2563eb;color:#fff;transition:all 0.2s">
                    <i class="fa-solid fa-list-check"></i> ১. বহুনির্বাচনী প্রশ্ন (MCQ) — {{ $mcqQuestions->count() }}টি
                </button>
                <button type="button" id="tabBtnWritten" onclick="switchExamTab('written')"
                        style="flex:1;padding:10px 16px;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:#64748b;border:1px solid #cbd5e1;transition:all 0.2s">
                    <i class="fa-solid fa-pen-nib" style="color:#db2777"></i> ২. লিখিত পরীক্ষা (Written) {{ $writtenQuestions->count() > 0 ? "— {$writtenQuestions->count()}টি" : '' }}
                </button>
            </div>
            @endif
EOD;

$tc = str_replace(
    str_replace("\r\n", "\n", $targetSections),
    str_replace("\r\n", "\n", $replacementSections),
    str_replace("\r\n", "\n", $tc)
);

// Wrap MCQ section
$targetMcqOpen = '{{-- MCQ Section --}}' . "\n" . '            @if($mcqQuestions->count() > 0)';
$replacementMcqOpen = '{{-- MCQ Section --}}' . "\n" . '            @if($mcqQuestions->count() > 0)' . "\n" . '            <div id="mcqSectionWrapper">';

$tc = str_replace(
    str_replace("\r\n", "\n", $targetMcqOpen),
    str_replace("\r\n", "\n", $replacementMcqOpen),
    str_replace("\r\n", "\n", $tc)
);

$targetMcqClose = '@endif' . "\n\n" . '            {{-- Written Section --}}';
$replacementMcqClose = <<<'EOD'
                @if($hasBoth)
                <div style="text-align:right;margin-top:16px;margin-bottom:20px">
                    <button type="button" class="btn btn-outline" onclick="switchExamTab('written')" style="font-family:'Kalpurush',sans-serif;color:#db2777;border-color:#f472b6;background:#fff">
                        লিখিত অংশে যান (Go to Written) <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
                @endif
            </div>
            @endif

            {{-- Written Section --}}
EOD;

$tc = str_replace(
    str_replace("\r\n", "\n", $targetMcqClose),
    str_replace("\r\n", "\n", $replacementMcqClose),
    str_replace("\r\n", "\n", $tc)
);

// Wrap Written section
$targetWrittenOpen = '{{-- Written Section --}}' . "\n" . '            @if($writtenQuestions->count() > 0)';
$replacementWrittenOpen = <<<'EOD'
{{-- Written Section --}}
            @if($writtenQuestions->count() > 0 || !empty($exam->written_question_pdf))
            <div id="writtenSectionWrapper" style="{{ ($hasBoth && $mcqQuestions->count() > 0) ? 'display:none' : '' }}">
                {{-- Prominent Written Question PDF Banner --}}
                @if(!empty($exam->written_question_pdf))
                <div style="background:#fdf2f8;border:2px dashed #f472b6;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;font-family:'Kalpurush',sans-serif">
                    <div>
                        <div style="font-size:15px;font-weight:700;color:#9d174d;display:flex;align-items:center;gap:8px">
                            <i class="fa-solid fa-file-pdf" style="font-size:24px;color:#ef4444"></i> লিখিত পরীক্ষার মূল প্রশ্নপত্র (Question Paper PDF)
                        </div>
                        <div style="font-size:12px;color:#64748b;margin-top:4px">
                            প্রশ্নপত্রটি দেখে বা ডাউনলোড করে খাতায় উত্তর লিখুন এবং পাতার ছবিগুলো নিচে আপলোড করুন।
                        </div>
                    </div>
                    <div style="display:flex;gap:10px">
                        <a href="{{ asset('storage/' . $exam->written_question_pdf) }}" target="_blank" class="btn btn-outline btn-sm" style="background:#fff;color:#be185d;border-color:#f472b6;font-size:12px">
                            <i class="fa-solid fa-eye"></i> প্রশ্নপত্র দেখুন
                        </a>
                        <a href="{{ asset('storage/' . $exam->written_question_pdf) }}" download class="btn btn-primary btn-sm" style="background:#db2777;border-color:#db2777;font-size:12px">
                            <i class="fa-solid fa-download"></i> PDF ডাউনলোড করুন
                        </a>
                    </div>
                </div>
                @endif
EOD;

$tc = str_replace(
    str_replace("\r\n", "\n", $targetWrittenOpen),
    str_replace("\r\n", "\n", $replacementWrittenOpen),
    str_replace("\r\n", "\n", $tc)
);

// Close Written wrapper
$targetWrittenClose = '@endforeach' . "\n" . '            @endif';
$replacementWrittenClose = <<<'EOD'
@endforeach
                @if($hasBoth)
                <div style="text-align:left;margin-top:16px;margin-bottom:20px">
                    <button type="button" class="btn btn-outline" onclick="switchExamTab('mcq')" style="font-family:'Kalpurush',sans-serif;color:#2563eb;border-color:#93c5fd;background:#fff">
                        <i class="fa-solid fa-arrow-left"></i> বহুনির্বাচনী (MCQ) অংশে ফিরে যান
                    </button>
                </div>
                @endif
            </div>
            @endif
EOD;

$tc = str_replace(
    str_replace("\r\n", "\n", $targetWrittenClose),
    str_replace("\r\n", "\n", $replacementWrittenClose),
    str_replace("\r\n", "\n", $tc)
);

// Add switchExamTab JS helper
$tabScript = <<<'EOD'

    window.switchExamTab = function(tab) {
        const mcqWrapper = document.getElementById("mcqSectionWrapper");
        const writtenWrapper = document.getElementById("writtenSectionWrapper");
        const btnMcq = document.getElementById("tabBtnMcq");
        const btnWritten = document.getElementById("tabBtnWritten");

        if (tab === "mcq") {
            if (mcqWrapper) mcqWrapper.style.display = "block";
            if (writtenWrapper) writtenWrapper.style.display = "none";
            if (btnMcq) {
                btnMcq.style.background = "#2563eb";
                btnMcq.style.color = "#fff";
                btnMcq.style.border = "none";
            }
            if (btnWritten) {
                btnWritten.style.background = "#fff";
                btnWritten.style.color = "#64748b";
                btnWritten.style.border = "1px solid #cbd5e1";
            }
        } else {
            if (mcqWrapper) mcqWrapper.style.display = "none";
            if (writtenWrapper) writtenWrapper.style.display = "block";
            if (btnMcq) {
                btnMcq.style.background = "#fff";
                btnMcq.style.color = "#64748b";
                btnMcq.style.border = "1px solid #cbd5e1";
            }
            if (btnWritten) {
                btnWritten.style.background = "#db2777";
                btnWritten.style.color = "#fff";
                btnWritten.style.border = "none";
            }
        }
    };
EOD;

$tc = str_replace('</script>', $tabScript . "\n</script>", $tc);

file_put_contents($takePath, $tc);
echo "take.blade.php updated.\n";
