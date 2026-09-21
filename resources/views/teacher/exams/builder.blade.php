<x-teacher-layout>
    <x-slot name="title">Exam Builder — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.exams.index') }}">← Back to Exams</a>
            </div>
            <h1>Exam Question Paper Builder</h1>
            <p>
                {{ $exam->title }} &middot;
                Subject: <strong>{{ $exam->subject?->name }} ({{ $exam->subject?->code }})</strong> &middot;
                Type: <span class="badge badge-info no-dot">{{ $exam->type }}</span>
            </p>
        </div>
        <div class="page-header-actions" style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('teacher.exams.test-exam', $exam) }}" class="btn btn-outline" style="background:#fef3c7;border-color:#fde68a;color:#92400e;display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-vial"></i> 🧪 টেস্ট এক্সাম (Test Exam)
            </a>
            @if($exam->examQuestions->where('question.question_type', 'WRITTEN')->count() > 0)
                <a href="{{ route('teacher.exams.grade', $exam) }}" class="btn btn-outline" style="color:#9d174d;border-color:#f9a8d4">
                    Grade Written Answers
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Exam Overview Banner --}}
    @php
        $poolMarks = $exam->examQuestions->sum('marks');
        $isPoolMode = $poolMarks > $exam->full_marks;
    @endphp
    <div class="card" style="margin-bottom:20px;background:#f8fafc">
        <div style="padding:16px;display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:12px;font-size:13px">
            <div><span style="color:#64748b;font-size:11px">নির্ধারিত পূর্ণমান:</span><br><strong style="font-size:15px;color:#0f172a">{{ $exam->full_marks }} নম্বর</strong></div>
            <div><span style="color:#64748b;font-size:11px">সময়সীমা:</span><br><strong>⏱️ {{ $exam->duration_minutes }} মিনিট</strong></div>
            <div><span style="color:#64748b;font-size:11px">Negative Marking:</span><br><strong style="color:#e11d48">-{{ $exam->negative_marking }} (MCQ)</strong></div>
            <div><span style="color:#64748b;font-size:11px">সংযুক্ত প্রশ্ন পুল:</span><br><strong style="color:#4338ca">{{ $exam->examQuestions->count() }}টি প্রশ্ন</strong></div>
            <div><span style="color:#64748b;font-size:11px">পুলের মোট নম্বর:</span><br><strong style="color:{{ $isPoolMode ? '#6366f1' : '#059669' }};font-size:15px">{{ $poolMarks }} নম্বর</strong></div>
        </div>
        @if($isPoolMode)
            <div style="padding:10px 16px;background:#eef2ff;border-top:1px solid #e0e7ff;font-size:12px;color:#3730a3;display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-shuffle" style="font-size:15px;color:#4f46e5"></i>
                <span>
                    <strong>র‍্যান্ডম প্রশ্ন পুল সক্রিয়:</strong> আপনি পুলে {{ $poolMarks }} নম্বরের মোট {{ $exam->examQuestions->count() }}টি প্রশ্ন যুক্ত করেছেন। শিক্ষার্থীরা পরীক্ষায় প্রবেশ করলে তাদের প্রত্যেকের জন্য প্রশ্নগুলো স্বয়ংক্রিয়ভাবে এলোমেলো (Shuffle) হয়ে নির্ধারিত <strong>{{ $exam->full_marks }} নম্বরের</strong> প্রশ্নপত্র তৈরি হবে।
                </span>
            </div>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:1fr 400px;gap:20px">

        {{-- Attached Question Paper --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Configured Exam Paper</span>
                <span class="badge badge-primary no-dot">{{ $exam->examQuestions->count() }} Questions Attached</span>
            </div>
            <div style="padding:0">
                @forelse($exam->examQuestions as $i => $eq)
                @php $q = $eq->question; @endphp
                <div style="padding:16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                    <div style="flex:1">
                        <div style="margin-bottom:6px;display:flex;align-items:center;gap:8px">
                            <span style="font-weight:700;color:#64748b;font-size:13px">{{ $i + 1 }}.</span>
                            @if($q?->question_type === 'WRITTEN')
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#fce7f3;color:#9d174d">WRITTEN</span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#e0e7ff;color:#4338ca">MCQ</span>
                            @endif
                            @if($q?->subject)
                                <span style="font-size:10px;background:#f1f5f9;color:#334155;padding:2px 6px;border-radius:4px">
                                    {{ $q->subject->code }}
                                </span>
                            @endif
                            @if($q?->source_tag)
                                <span style="font-size:10px;background:#f8fafc;color:#64748b;padding:2px 6px;border-radius:4px;border:1px solid #e2e8f0">
                                    #{{ $q->source_tag }}
                                </span>
                            @endif
                        </div>
                        <div style="font-weight:600;font-size:14px;color:#0f172a;margin-bottom:6px">
                            {!! e($q?->question_text) !!}
                        </div>
                        <div style="font-size:12px;color:#64748b">
                            @if($q?->question_type === 'MCQ')
                                Correct: <strong style="color:#10b981">{{ strtoupper($q->correct_option_id) }}</strong> &middot;
                            @else
                                <em>Subjective / Teacher graded</em> &middot;
                            @endif
                            Marks: <strong>{{ $eq->marks }}</strong>
                        </div>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('teacher.exams.questions.detach', [$exam, $eq]) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444" title="Remove question" onsubmit="return confirm('Remove question?')">
                                <i class="fa-solid fa-trash"></i> Remove
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div style="padding:40px;text-align:center;color:#94a3b8">
                    No questions attached yet. Select from the right panel →
                </div>
                @endforelse
            </div>
        </div>

        {{-- Available Question Bank Selector --}}
        <div class="card">
            <div class="card-header" style="flex-direction:column;align-items:flex-start;gap:8px">
                <span class="card-title"><i class="fa-solid fa-layer-group" style="color:#6366f1"></i> Question Bank Pool</span>
                <span style="font-size:12px;color:var(--text-muted)">যেকোনো বিষয়, ক্যাটাগরি বা ট্যাগ থেকে প্রশ্ন সিলেক্ট করে যুক্ত করুন</span>
            </div>
            <div style="padding:14px">

                {{-- Pull Random Questions Card --}}
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:14px">
                    <div style="font-weight:700;font-size:12px;color:#166534;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-dice" style="color:#16a34a"></i> র‍্যান্ডম প্রশ্ন যোগ করুন (Pull Random Questions)
                    </div>
                    <form method="POST" action="{{ route('teacher.exams.questions.random', $exam) }}" style="display:flex;flex-direction:column;gap:8px">
                        @csrf
                        <input type="hidden" name="pool_subject_id" value="{{ $subjectId }}">
                        <input type="hidden" name="exam_type" value="{{ $examType }}">
                        <input type="hidden" name="batch_id" value="{{ $batchId }}">
                        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                        <input type="hidden" name="difficulty" value="{{ $difficulty }}">
                        <input type="hidden" name="search" value="{{ $search }}">

                        <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:6px">
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#374151">Number of random questions:</label>
                                <input type="number" name="count" min="1" max="100" value="10" required class="form-control" style="height:32px;font-size:12px">
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:600;color:#374151">Marks per question:</label>
                                <input type="number" step="0.5" name="marks_per_question" min="0.5" value="1" class="form-control" style="height:32px;font-size:12px">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm" style="background:#16a34a;color:#fff;height:32px;display:flex;align-items:center;justify-content:center;gap:6px;font-size:12px;font-weight:600">
                            <i class="fa-solid fa-plus"></i> + Add random questions
                        </button>
                    </form>
                </div>

                {{-- Filter Bar --}}
                <form method="GET" action="{{ route('teacher.exams.show', $exam) }}" style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:8px">
                    <input type="text" name="search" class="form-control" placeholder="প্রশ্ন অনুসন্ধান করুন..." value="{{ $search }}" style="height:32px;font-size:12px">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        <select name="pool_subject_id" class="form-control" style="height:32px;font-size:11px">
                            <option value="all" {{ ($subjectId ?? '') === 'all' ? 'selected' : '' }}>সকল বিষয় (All Subjects)</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ ($subjectId ?? $exam->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                            @endforeach
                        </select>

                        <select name="exam_type" class="form-control" style="height:32px;font-size:11px">
                            <option value="">সকল পরীক্ষার ধরন</option>
                            @foreach($examTypes as $et)
                                <option value="{{ $et }}" {{ ($examType ?? '') === $et ? 'selected' : '' }}>{{ $et }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        <select name="batch_id" class="form-control" style="height:32px;font-size:11px">
                            <option value="">সকল ব্যাচ (All Batches)</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ ($batchId ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>

                        <select name="semester_id" class="form-control" style="height:32px;font-size:11px">
                            <option value="">সকল সেমিস্টার (All Semesters)</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}" {{ ($semesterId ?? '') == $sem->id ? 'selected' : '' }}>{{ $sem->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        <select name="difficulty" class="form-control" style="height:32px;font-size:11px">
                            <option value="">সকল কঠিনতা</option>
                            <option value="easy" {{ ($difficulty ?? '') === 'easy' ? 'selected' : '' }}>Easy (সহজ)</option>
                            <option value="medium" {{ ($difficulty ?? '') === 'medium' ? 'selected' : '' }}>Medium (মধ্যম)</option>
                            <option value="hard" {{ ($difficulty ?? '') === 'hard' ? 'selected' : '' }}>Hard (কঠিন)</option>
                        </select>

                        <div style="display:flex;gap:4px">
                            <button type="submit" class="btn btn-primary btn-sm" style="flex:1;height:32px;padding:0;font-size:11px">
                                <i class="fa-solid fa-filter"></i> ফিল্টার
                            </button>
                            @if($search || $difficulty || $examType || $batchId || $semesterId || ($subjectId && $subjectId !== $exam->subject_id))
                                <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline btn-sm" style="height:32px;padding:4px 8px;font-size:11px" title="Reset">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <div style="display:flex;flex-direction:column;gap:10px;max-height:550px;overflow-y:auto">
                    @forelse($availableQuestions as $q)
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px;box-shadow:0 1px 2px rgba(0,0,0,0.03)">
                        <div style="margin-bottom:6px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                            @if($q->question_type === 'WRITTEN')
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#fce7f3;color:#9d174d">WRITTEN</span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#e0e7ff;color:#4338ca">MCQ</span>
                            @endif

                            @if($q->subject)
                                <span style="font-size:10px;background:#f1f5f9;color:#334155;padding:2px 6px;border-radius:4px;font-weight:600">
                                    {{ $q->subject->code }}
                                </span>
                            @endif

                            @if($q->exam_type)
                                <span style="font-size:10px;background:#ede9fe;color:#6d28d9;padding:2px 6px;border-radius:4px;font-weight:700">
                                    {{ $q->exam_type }}
                                </span>
                            @endif

                            @if($q->source_tag)
                                <span style="font-size:10px;background:#f8fafc;color:#64748b;padding:2px 6px;border-radius:4px;border:1px solid #e2e8f0">
                                    #{{ $q->source_tag }}
                                </span>
                            @endif
                        </div>
                        <div style="font-weight:600;font-size:13px;color:#1e293b;margin-bottom:8px;line-height:1.4">
                            {!! e($q->question_text) !!}
                        </div>
                        <form method="POST" action="{{ route('teacher.exams.questions.attach', $exam) }}" style="display:flex;gap:8px;align-items:center">
                            @csrf
                            <input type="hidden" name="question_id" value="{{ $q->id }}">
                            <input type="number" step="0.5" name="marks" value="{{ $q->question_type === 'WRITTEN' ? 5 : 1 }}"
                                   class="form-control" style="width:75px;height:32px;font-size:12px" title="Marks">
                            <button type="submit" class="btn btn-primary btn-sm" style="padding:4px 12px;font-size:12px">
                                + Attach
                            </button>
                        </form>
                    </div>
                    @empty
                    <div style="padding:20px;text-align:center;color:#94a3b8;font-size:12px">
                        কোনো প্রশ্ন পাওয়া যায়নি। ফিল্টার পরিবর্তন করে দেখুন।
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-teacher-layout>
