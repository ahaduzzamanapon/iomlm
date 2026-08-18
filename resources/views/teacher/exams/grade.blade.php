<x-teacher-layout>
    <x-slot name="title">Written Answer Grading — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.exams.show', $exam) }}">← Back to Exam Builder</a>
            </div>
            <h1>✏️ Written Answer Grading</h1>
            <p>
                {{ $exam->title }} &middot;
                Subject: <strong>{{ $exam->subject?->name }}</strong> &middot;
                <span class="badge badge-info no-dot">{{ $submissions->count() }} Submissions</span>
            </p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Written Questions List --}}
    @php
        $writtenEqs = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'WRITTEN');
    @endphp

    @if($writtenEqs->isEmpty())
        <div class="card" style="text-align:center;padding:40px;color:#94a3b8">
            <p style="font-size:15px;font-weight:600">এই Exam-এ কোনো Written প্রশ্ন নেই।</p>
        </div>
    @elseif($submissions->isEmpty())
        <div class="card" style="text-align:center;padding:40px;color:#94a3b8">
            <p style="font-size:15px;font-weight:600">এখনো কোনো Submission আসেনি।</p>
        </div>
    @else
        @foreach($submissions as $submission)
        <div class="card" style="margin-bottom:24px">
            {{-- Student Header --}}
            <div class="card-header" style="background:#f8fafc;border-radius:12px 12px 0 0">
                <div>
                    <span class="card-title">👤 {{ $submission->student?->name }}</span>
                    <div style="font-size:12px;color:#64748b;margin-top:2px">
                        ID: {{ $submission->student?->student_id ?? '—' }} &middot;
                        Submitted: {{ $submission->submitted_at?->format('d M Y, h:i A') ?? '—' }} &middot;
                        Status: <span style="color:#10b981;font-weight:700">{{ $submission->status }}</span>
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:11px;color:#64748b">MCQ Score</div>
                    <div style="font-size:18px;font-weight:800;color:#4338ca">{{ number_format($submission->total_score, 1) }}</div>
                </div>
            </div>

            {{-- Written Answers for this student --}}
            <div style="padding:16px;display:flex;flex-direction:column;gap:16px">
                @foreach($writtenEqs as $eq)
                @php
                    $q = $eq->question;
                    $answer = $submission->answers->firstWhere('question_id', $q->id);
                @endphp
                <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;padding:16px">
                    <div style="font-weight:700;font-size:14px;color:#0f172a;margin-bottom:4px">
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#fce7f3;color:#9d174d;margin-right:8px">✏️ Written</span>
                        {!! e($q->question_text) !!}
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-bottom:12px">সর্বোচ্চ নম্বর: <strong>{{ $eq->marks }}</strong></div>

                    @if($answer && $answer->answer_image_path)
                        <div style="margin-bottom:12px">
                            <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px">📷 ছাত্রের উত্তর:</div>
                            <img src="{{ Storage::url($answer->answer_image_path) }}"
                                 alt="Student Answer"
                                 style="max-width:100%;max-height:500px;border-radius:8px;border:1px solid #f9a8d4;cursor:pointer"
                                 onclick="this.style.maxHeight = this.style.maxHeight === 'none' ? '500px' : 'none'"
                                 title="Click to expand/collapse">
                        </div>

                        {{-- Grading Form --}}
                        <form method="POST" action="{{ route('teacher.exam-answers.grade', $answer) }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                            @csrf @method('PATCH')
                            <div style="display:flex;align-items:center;gap:8px">
                                <label style="font-size:13px;font-weight:600;color:#475569">নম্বর দিন:</label>
                                <input type="number" name="teacher_marks"
                                       value="{{ $answer->teacher_marks ?? '' }}"
                                       min="0" max="{{ $eq->marks }}" step="0.5"
                                       class="form-control" style="width:100px;height:36px;font-size:14px;font-weight:700"
                                       placeholder="0">
                                <span style="font-size:13px;color:#64748b">/ {{ $eq->marks }}</span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                💾 নম্বর সেভ করুন
                            </button>
                            @if($answer->teacher_marks !== null)
                                <span style="color:#10b981;font-weight:700;font-size:13px">✓ দেওয়া নম্বর: {{ $answer->teacher_marks }}</span>
                            @endif
                        </form>
                    @else
                        <div style="color:#94a3b8;font-size:13px;padding:12px;background:#fff;border-radius:6px;border:1px solid #f1f5f9">
                            ⚪ এই ছাত্র এই প্রশ্নের উত্তর Upload করেনি।
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @endif
</x-teacher-layout>
