<x-teacher-layout>
    <x-slot name="title">Exam Builder — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.exams.index') }}">← Back to Exams</a>
            </div>
            <h1>📝 Exam Question Paper Builder</h1>
            <p>
                {{ $exam->title }} &middot;
                Subject: <strong>{{ $exam->subject?->name }} ({{ $exam->subject?->code }})</strong> &middot;
                Type: <span class="badge badge-info no-dot">{{ $exam->type }}</span>
            </p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Exam Overview Banner --}}
    <div class="card" style="margin-bottom:20px;background:#f8fafc">
        <div style="padding:16px;display:grid;grid-template-columns:repeat(4, 1fr);gap:12px;font-size:13px">
            <div><span style="color:#64748b;font-size:11px">Full Marks:</span><br><strong>{{ $exam->full_marks }} Marks</strong></div>
            <div><span style="color:#64748b;font-size:11px">Duration:</span><br><strong>⏱️ {{ $exam->duration_minutes }} Mins</strong></div>
            <div><span style="color:#64748b;font-size:11px">Negative Mark Rate:</span><br><strong style="color:#e11d48">-{{ $exam->negative_marking }}</strong></div>
            <div><span style="color:#64748b;font-size:11px">Anti-Cheating:</span><br><strong style="color:#10b981">{{ $exam->is_anti_cheating ? 'ON 🔒' : 'OFF' }}</strong></div>
        </div>
    </div>

    <div class="grid-2" style="grid-template-columns: 1fr 380px;">

        {{-- Attached Question Paper --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">📜 Configured Exam Paper Questions</span>
                <span class="badge badge-primary no-dot">{{ $exam->examQuestions->count() }} Questions Attached</span>
            </div>
            <div style="padding:0">
                @forelse($exam->examQuestions as $i => $eq)
                @php $q = $eq->question; @endphp
                <div style="padding:16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                    <div>
                        <div style="font-weight:700;font-size:14px;color:#0f172a;margin-bottom:6px">
                            {{ $i + 1 }}. {!! e($q->question_text) !!}
                        </div>
                        <div style="font-size:12px;color:#64748b">
                            Correct Option: <strong style="color:#10b981">{{ strtoupper($q->correct_option_id) }}</strong> &middot;
                            Marks: <strong>{{ $eq->marks }}</strong>
                        </div>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('teacher.exams.questions.detach', [$exam, $eq]) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444" title="Remove question">
                                ✕ Remove
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div style="padding:40px;text-align:center;color:#94a3b8">
                    No questions attached yet. Select questions from the right panel to build this paper.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Available Question Bank Selector --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">📚 Question Bank Pool</span>
            </div>
            <div style="padding:16px">
                <p style="font-size:12px;color:#64748b;margin-bottom:12px">
                    Questions for subject <strong>{{ $exam->subject?->name }}</strong>:
                </p>
                <div style="display:flex;flex-direction:column;gap:12px;max-height:500px;overflow-y:auto">
                    @forelse($availableQuestions as $q)
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
                        <div style="font-weight:600;font-size:13px;color:#1e293b;margin-bottom:8px">
                            {!! e($q->question_text) !!}
                        </div>
                        <form method="POST" action="{{ route('teacher.exams.questions.attach', $exam) }}" style="display:flex;gap:8px;align-items:center">
                            @csrf
                            <input type="hidden" name="question_id" value="{{ $q->id }}">
                            <input type="number" step="0.5" name="marks" value="1.0" class="form-control" style="width:80px;height:32px;font-size:12px" title="Marks">
                            <button type="submit" class="btn btn-primary btn-sm" style="padding:4px 12px;font-size:12px">
                                + Attach
                            </button>
                        </form>
                    </div>
                    @empty
                    <div style="padding:20px;text-align:center;color:#94a3b8;font-size:12px">
                        No additional questions available in Question Bank for this subject.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-teacher-layout>
