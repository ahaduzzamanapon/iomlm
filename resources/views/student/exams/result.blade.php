<x-student-layout>
    <x-slot name="title">Exam Result — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>📊 Exam Result &amp; Feedback</h1>
            <p>{{ $exam->title }} &middot; {{ $exam->subject?->name }} ({{ $exam->subject?->code }})</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('student.exams.index') }}" class="btn btn-outline">← Back to Exams</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Score Cards --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon blue">🏆</div>
            <div class="stat-info">
                <div class="stat-value">
                    {{ $submission->total_score }} / {{ $exam->full_marks }}
                </div>
                <div class="stat-label">Total Score</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-info">
                <div class="stat-value">{{ $submission->correct_count }}</div>
                <div class="stat-label">Correct Answers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">✕</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#e11d48">{{ $submission->wrong_count }}</div>
                <div class="stat-label">Wrong Answers</div>
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

    @if($submission->status === 'AUTO_SUBMITTED_VIOLATION')
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px">
            <strong>⚠️ Anti-Cheating Violation Notice:</strong> This exam was automatically submitted because you switched tabs/windows {{ $submission->tab_switch_count }} times during the test.
        </div>
    @endif

    {{-- Question Breakdown & Explanations --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">📖 Question Solutions &amp; Explanations</span>
        </div>
        <div style="padding:20px">
            @foreach($exam->examQuestions as $i => $eq)
            @php
                $q      = $eq->question;
                $answer = $answersMap[$q->id] ?? null;
                $userOpt = strtolower($answer?->selected_option_id ?? '');
                $rightOpt = strtolower($q->correct_option_id);
                $isRight = $answer?->is_correct;
            @endphp
            <div style="background:#f8fafc;border:1px solid {{ $isRight ? '#bbf7d0' : ($userOpt ? '#fecaca' : '#cbd5e1') }};border-radius:10px;padding:16px;margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                    <strong style="font-size:14px;color:#1e293b">Question {{ $i + 1 }}: {!! e($q->question_text) !!}</strong>
                    @if($isRight)
                        <span class="badge badge-success no-dot">✓ Correct (+{{ $eq->marks }})</span>
                    @elseif($userOpt)
                        <span class="badge badge-danger no-dot">✕ Wrong (-{{ $exam->negative_marking }})</span>
                    @else
                        <span class="badge badge-secondary no-dot">Skipped (0)</span>
                    @endif
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px;font-size:13px">
                    @foreach($q->options ?? [] as $opt)
                    @php
                        $optId = strtolower($opt['id'] ?? '');
                        $bg = '#fff';
                        $border = '#e2e8f0';
                        if ($optId === $rightOpt) {
                            $bg = '#dcfce7'; $border = '#86efac';
                        } elseif ($optId === $userOpt && !$isRight) {
                            $bg = '#fee2e2'; $border = '#fca5a5';
                        }
                    @endphp
                    <div style="background:{{ $bg }};border:1px solid {{ $border }};padding:8px 12px;border-radius:6px">
                        <strong>{{ strtoupper($optId) }}:</strong> {{ $opt['text'] ?? '' }}
                        @if($optId === $rightOpt) <span style="color:#166534;font-weight:700">✓ Correct Answer</span> @endif
                        @if($optId === $userOpt && !$isRight) <span style="color:#991b1b;font-weight:700">(Your Choice)</span> @endif
                    </div>
                    @endforeach
                </div>

                @if($q->explanation)
                <div style="background:#fffbeb;border:1px solid #fef3c7;border-radius:6px;padding:10px 12px;margin-top:12px;font-size:12px;color:#92400e">
                    <strong>💡 Explanation:</strong> {!! e($q->explanation) !!}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</x-student-layout>
