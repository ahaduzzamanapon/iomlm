<x-student-layout>
    <x-slot name="title">পরীক্ষার ফলাফল — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>📊 পরীক্ষার ফলাফল</h1>
            <p>{{ $exam->title }} &middot; {{ $exam->subject?->name }} ({{ $exam->subject?->code }})</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('student.exams.index') }}" class="btn btn-outline">← পরীক্ষার তালিকায় ফিরুন</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($submission->status === 'AUTO_SUBMITTED_VIOLATION')
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px">
            <strong>⚠️ Anti-Cheating Notice:</strong> This exam was automatically submitted due to tab switching ({{ $submission->tab_switch_count }} times).
        </div>
    @endif

    {{-- Score Summary --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon blue">🏆</div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($submission->total_score, 1) }} / {{ $exam->full_marks }}</div>
                <div class="stat-label">মোট নম্বর</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-info">
                <div class="stat-value">{{ $submission->correct_count }}</div>
                <div class="stat-label">সঠিক উত্তর (MCQ)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">✕</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#e11d48">{{ $submission->wrong_count }}</div>
                <div class="stat-label">ভুল উত্তর (MCQ)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon violet">⚠️</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#e11d48">-{{ $submission->negative_marks_deducted }}</div>
                <div class="stat-label">Negative Mark Penalty</div>
            </div>
        </div>
    </div>

    {{-- Question Breakdown --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">📖 প্রশ্ন বিশ্লেষণ</span>
        </div>
        <div style="padding:20px">

            @foreach($exam->examQuestions as $i => $eq)
            @php
                $q      = $eq->question;
                $answer = $answersMap[$q->id] ?? null;
            @endphp

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:16px">

                {{-- Question Header --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-weight:700;color:#64748b;font-size:13px">{{ $i + 1 }}.</span>
                        @if($q->question_type === 'WRITTEN')
                            <span style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#fce7f3;color:#9d174d">✏️ WRITTEN</span>
                        @else
                            <span style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#e0e7ff;color:#4338ca">🔵 MCQ</span>
                        @endif
                    </div>
                    <div>
                        @if($q->question_type === 'WRITTEN')
                            @if($answer?->teacher_marks !== null)
                                <span class="badge badge-success no-dot">✓ Teacher নম্বর: {{ $answer->teacher_marks }}/{{ $eq->marks }}</span>
                            @else
                                <span class="badge badge-secondary no-dot">⏳ Teacher Grading Pending</span>
                            @endif
                        @else
                            @php
                                $userOpt  = strtolower($answer?->selected_option_id ?? '');
                                $isRight  = $answer?->is_correct;
                            @endphp
                            @if($isRight)
                                <span class="badge badge-success no-dot">✓ সঠিক (+{{ $eq->marks }})</span>
                            @elseif($userOpt)
                                <span class="badge badge-danger no-dot">✕ ভুল (-{{ $exam->negative_marking }})</span>
                            @else
                                <span class="badge badge-secondary no-dot">Skip (0)</span>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Question Text --}}
                <div style="font-weight:600;font-size:14px;color:#1e293b;margin-bottom:12px;line-height:1.5">
                    {!! e($q->question_text) !!}
                </div>

                {{-- MCQ Options --}}
                @if($q->question_type === 'MCQ')
                @php
                    $rightOpt = strtolower($q->correct_option_id ?? '');
                    $userOpt  = strtolower($answer?->selected_option_id ?? '');
                @endphp
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px">
                    @foreach($q->options ?? [] as $opt)
                    @php
                        $optId  = strtolower($opt['id'] ?? '');
                        $isCorr = $optId === $rightOpt;
                        $isUser = $optId === $userOpt && !($answer?->is_correct);
                        $bg     = '#fff'; $border = '#e2e8f0';
                        if ($isCorr)      { $bg = '#dcfce7'; $border = '#86efac'; }
                        elseif ($isUser)  { $bg = '#fee2e2'; $border = '#fca5a5'; }
                    @endphp
                    <div style="background:{{ $bg }};border:1px solid {{ $border }};padding:8px 12px;border-radius:6px">
                        <strong>{{ strtoupper($optId) }}:</strong> {{ $opt['text'] ?? '' }}
                        @if($isCorr) <span style="color:#166534;font-weight:700"> ✓ সঠিক উত্তর</span> @endif
                        @if($isUser) <span style="color:#991b1b;font-weight:700"> (আপনার উত্তর)</span> @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Written Answer Image --}}
                @if($q->question_type === 'WRITTEN')
                    @if($answer?->answer_image_path)
                        <div style="margin-top:10px">
                            <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px">📷 আপনার উত্তর:</div>
                            <img src="{{ Storage::url($answer->answer_image_path) }}"
                                 alt="Your Answer"
                                 style="max-width:100%;max-height:400px;border-radius:8px;border:1px solid #f9a8d4">
                        </div>
                    @else
                        <div style="color:#94a3b8;font-size:13px;font-style:italic;margin-top:8px">⚪ কোনো উত্তর Upload করা হয়নি।</div>
                    @endif
                @endif

            </div>
            @endforeach

        </div>
    </div>
</x-student-layout>
