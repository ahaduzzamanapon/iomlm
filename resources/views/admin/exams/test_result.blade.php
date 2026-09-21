<x-admin-layout>
    <x-slot name="title">Test Exam Result — {{ $exam->title }}</x-slot>

    <div class="page-header" style="margin-bottom:20px">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ $backUrl }}">← ফিরে যান (Back to Exam)</a>
            </div>
            <h1 style="font-family:'Kalpurush', sans-serif">🧪 টেস্ট পরীক্ষার তাৎক্ষণিক ফলাফল (Test Mode Result)</h1>
            <p style="font-size:13px;color:#64748b">
                {{ $exam->title }} &middot; বিষয়: <strong>{{ $exam->subject?->name }} ({{ $exam->subject?->code }})</strong> &middot; ধরন: {{ $exam->type }}
            </p>
        </div>
        <div class="page-header-actions">
            <a href="{{ $backUrl }}" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> পরীক্ষায় ফিরে যান
            </a>
            <a href="{{ route('admin.exams.test-exam', $exam) }}" class="btn btn-primary" style="background:#f59e0b;border-color:#f59e0b">
                <i class="fa-solid fa-rotate-right"></i> পুনরায় টেস্ট দিন
            </a>
        </div>
    </div>

    {{-- Zero Pollution Notice --}}
    <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:14px 20px;border-radius:10px;margin-bottom:24px;display:flex;align-items:center;gap:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
        <i class="fa-solid fa-shield-halved" style="font-size:24px;color:#10b981"></i>
        <div>
            <strong style="font-size:14px">জিরো ডাটাবেস পলিউশন (Zero Database Pollution):</strong>
            <div style="font-size:12px;margin-top:2px">
                এটি সম্পূর্ণ ইন-মেমোরি টেস্ট এক্সাম ছিল। ডাটাবেসের <code>exam_submissions</code>, <code>exam_answers</code> বা <code>results</code> টেবিলে কোনো পরীক্ষার্থী বা ফলাফলের রেকর্ড যুক্ত হয়নি।
            </div>
        </div>
    </div>

    {{-- Score Cards Grid --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:24px">
        <div class="card" style="padding:20px;text-align:center;border-top:4px solid #4f46e5">
            <span style="font-size:12px;color:#64748b;font-weight:600">প্রাপ্ত টেস্ট স্কোর</span>
            <div style="font-size:32px;font-weight:800;color:#4f46e5;margin:8px 0">
                {{ number_format($totalScore, 2) }}
            </div>
            <span style="font-size:12px;color:#64748b">পূর্ণমান: {{ $exam->full_marks }}</span>
        </div>

        <div class="card" style="padding:20px;text-align:center;border-top:4px solid #10b981">
            <span style="font-size:12px;color:#64748b;font-weight:600">সঠিক উত্তর</span>
            <div style="font-size:32px;font-weight:800;color:#10b981;margin:8px 0">
                {{ $correctCount }}
            </div>
            <span style="font-size:12px;color:#059669">+{{ number_format($earnedMarks, 2) }} অর্জিত মার্কস</span>
        </div>

        <div class="card" style="padding:20px;text-align:center;border-top:4px solid #ef4444">
            <span style="font-size:12px;color:#64748b;font-weight:600">ভুল উত্তর</span>
            <div style="font-size:32px;font-weight:800;color:#ef4444;margin:8px 0">
                {{ $wrongCount }}
            </div>
            <span style="font-size:12px;color:#dc2626">-{{ number_format($negativeDeducted, 2) }} নেগেটিভ কর্তন</span>
        </div>

        <div class="card" style="padding:20px;text-align:center;border-top:4px solid #94a3b8">
            <span style="font-size:12px;color:#64748b;font-weight:600">উত্তরহীন (Unanswered)</span>
            <div style="font-size:32px;font-weight:800;color:#64748b;margin:8px 0">
                {{ $unansweredCount }}
            </div>
            <span style="font-size:12px;color:#64748b">মোট MCQ: {{ $totalMcq }}টি</span>
        </div>
    </div>

    @if($writtenCount > 0)
        <div class="card" style="padding:16px 20px;margin-bottom:24px;background:#fdf2f8;border:1px solid #fbcfe8;display:flex;align-items:center;gap:12px">
            <i class="fa-solid fa-pen-nib" style="font-size:20px;color:#9d174d"></i>
            <div>
                <strong style="color:#9d174d">রচনামূলক প্রশ্ন (Written Questions): {{ $writtenCount }}টি</strong>
                <p style="margin:2px 0 0;font-size:12px;color:#be185d">
                    রচনামূলক প্রশ্নের উত্তর সাধারণত পরীক্ষক/শিক্ষক ম্যানুয়ালি যাচাই করে খাতা মূল্যায়নের মাধ্যমে নম্বর প্রদান করেন।
                </p>
            </div>
        </div>
    @endif

    {{-- Question Breakdown --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">MCQ উত্তরের বিশ্লেষণ (Test Answer Sheet)</span>
            <span class="badge badge-info no-dot">{{ $totalMcq }} Questions</span>
        </div>
        <div style="padding:0">
            @php $mcqSerial = 0; @endphp
            @foreach($exam->examQuestions as $eq)
                @php
                    $q = $eq->question;
                    if (!$q || $q->question_type !== 'MCQ') continue;
                    $mcqSerial++;
                    $userAns = isset($answersInput[$q->id]) ? strtolower(trim($answersInput[$q->id])) : null;
                    $correctAns = strtolower(trim($q->correct_option_id ?? ''));
                    $isCorrect = ($userAns !== null && $userAns === $correctAns);
                    $isWrong = ($userAns !== null && $userAns !== '' && $userAns !== $correctAns);
                    $isUnanswered = ($userAns === null || $userAns === '');
                @endphp
                <div style="padding:16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:flex-start;gap:14px">
                    <div style="flex:1">
                        <div style="margin-bottom:6px;display:flex;align-items:center;gap:8px">
                            <span style="font-weight:700;color:#64748b;font-size:13px">{{ $mcqSerial }}.</span>
                            <span class="badge badge-primary no-dot" style="font-size:10px">MCQ</span>
                            <span style="font-size:11px;color:#64748b">({{ $eq->marks }} নম্বর)</span>

                            @if($isCorrect)
                                <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#dcfce7;color:#166534;font-weight:700">✓ সঠিক (Correct)</span>
                            @elseif($isWrong)
                                <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#fee2e2;color:#991b1b;font-weight:700">✗ ভুল (Wrong)</span>
                            @else
                                <span style="font-size:11px;padding:2px 8px;border-radius:20px;background:#f1f5f9;color:#64748b;font-weight:700">উত্তর দেওয়া হয়নি</span>
                            @endif
                        </div>

                        <div style="font-weight:600;font-size:14px;color:#0f172a;margin-bottom:8px">
                            {!! e($q->question_text) !!}
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:8px;font-size:12px">
                            @foreach($q->options ?? [] as $opt)
                                @php
                                    $optId = strtolower($opt['id'] ?? '');
                                    $optSelected = ($userAns === $optId);
                                    $optIsRight = ($correctAns === $optId);
                                    $border = '#e2e8f0';
                                    $bg = '#fff';
                                    if ($optIsRight) {
                                        $border = '#86efac';
                                        $bg = '#f0fdf4';
                                    } elseif ($optSelected && !$optIsRight) {
                                        $border = '#fca5a5';
                                        $bg = '#fef2f2';
                                    }
                                @endphp
                                <div style="padding:6px 10px;border-radius:6px;border:1px solid {{ $border }};background:{{ $bg }}">
                                    <strong>{{ strtoupper($optId) }}.</strong> {{ $opt['text'] ?? '' }}
                                    @if($optIsRight)
                                        <span style="color:#166534;font-weight:700;margin-left:4px">✓ সঠিক</span>
                                    @endif
                                    @if($optSelected && !$optIsRight)
                                        <span style="color:#991b1b;font-weight:700;margin-left:4px">✗ আপনার উত্তর</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($q->explanation)
                            <div style="margin-top:8px;font-size:12px;background:#f8fafc;padding:6px 10px;border-radius:6px;color:#475569">
                                <strong>ব্যাখ্যা:</strong> {{ $q->explanation }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
