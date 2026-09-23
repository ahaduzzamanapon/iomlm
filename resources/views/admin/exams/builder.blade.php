<x-admin-layout>
    <x-slot name="title">Exam Paper Builder — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.exams.index') }}">← Back to Exams</a> &middot;
                <a href="{{ route('admin.exams.show', $exam) }}">Inspect Exam</a>
            </div>
            <h1>Exam Question Paper Builder</h1>
            <p>
                {{ $exam->title }} &middot;
                Subject: <strong>{{ $exam->subject?->name }} ({{ $exam->subject?->code }})</strong> &middot;
                Type: <span class="badge badge-info no-dot">{{ $exam->type }}</span>
            </p>
        </div>
        <div class="page-header-actions" style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('admin.exams.test-exam', $exam) }}" class="btn btn-outline" style="background:#fef3c7;border-color:#fde68a;color:#92400e;display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-vial"></i> 🧪 টেস্ট এক্সাম (Test Exam)
            </a>
            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline">
                <i class="fa-solid fa-eye"></i> View Exam Details
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Exam Overview Banner --}}
    {{-- Exam Overview Banner (4 Independent Assessment Components) --}}
    @php
        $poolMarks = $exam->examQuestions->sum('marks');
        $mcqPoolMarks = $exam->getPoolMcqMarks();
        $writtenPoolMarks = $exam->getPoolWrittenMarks();
        $isMcqTargetMet = $exam->isMcqTargetMet();
        $isWrittenTargetMet = $exam->isWrittenTargetMet();
        $isPoolMode = $poolMarks > $exam->full_marks;
        $mcqQuestions = $exam->examQuestions->filter(fn($eq) => ($eq->question?->question_type ?? 'MCQ') === 'MCQ');
        $writtenQuestions = $exam->examQuestions->filter(fn($eq) => ($eq->question?->question_type ?? '') === 'WRITTEN');
    @endphp
    <div class="card" style="margin-bottom:20px; background:#fff; border:1px solid #cbd5e1; border-radius:12px; overflow:hidden; font-family:'Kalpurush',sans-serif">
        {{-- General Info Strip --}}
        <div style="background:#f1f5f9; padding:12px 18px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; font-size:13px">
            <div>
                <span style="color:#64748b">নির্ধারিত পূর্ণমান:</span>
                <strong style="font-size:16px; color:#0f172a; margin-left:4px">{{ $exam->full_marks }} নম্বর</strong>
            </div>
            <div>
                <span style="color:#64748b">সময়সীমা:</span>
                <strong style="color:#0f172a; margin-left:4px">⏱️ {{ $exam->duration_minutes }} মিনিট</strong>
            </div>
            <div>
                <span style="color:#64748b">নেগেটিভ মার্ক:</span>
                <strong style="color:#e11d48; margin-left:4px">-{{ $exam->negative_marking }} (MCQ)</strong>
            </div>
            <div>
                <span style="color:#64748b">প্রশ্নপুলে মোট:</span>
                <strong style="color:#4338ca; margin-left:4px">{{ $exam->examQuestions->count() }}টি প্রশ্ন ({{ $poolMarks }} নম্বর)</strong>
            </div>
        </div>

        {{-- 4 Component Target & Progress Grid --}}
        <div style="padding:14px 18px; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px">
            {{-- 1. MCQ --}}
            @if($exam->has_mcq)
            <div style="background:#f8fafc; border:1.5px solid {{ $isMcqTargetMet ? '#86efac' : '#cbd5e1' }}; border-radius:8px; padding:10px 14px">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                    <span style="font-weight:700; color:#1e293b; font-size:13px">
                        <i class="fa-solid fa-list-check" style="color:#4f46e5; margin-right:4px"></i> MCQ লক্ষ্য
                    </span>
                    @if($isMcqTargetMet)
                        <span style="background:#dcfce7; color:#15803d; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:12px">✅ পূর্ণ</span>
                    @else
                        <span style="background:#fef3c7; color:#92400e; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:12px">⏳ বাকি ({{ max(0, $exam->mcq_marks - $mcqPoolMarks) }})</span>
                    @endif
                </div>
                <div style="font-size:15px; font-weight:800; color:#0f172a">
                    {{ (int)$exam->mcq_marks }} নম্বর
                </div>
                <div style="font-size:11.5px; color:#64748b; margin-top:2px">
                    বর্তমানে পুলে যুক্ত: <strong>{{ $mcqPoolMarks }} নম্বর</strong> ({{ $mcqQuestions->count() }}টি)
                </div>
            </div>
            @endif

            {{-- 2. Written --}}
            @if($exam->has_written)
            <div style="background:#f8fafc; border:1.5px solid {{ $isWrittenTargetMet ? '#86efac' : '#cbd5e1' }}; border-radius:8px; padding:10px 14px">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                    <span style="font-weight:700; color:#1e293b; font-size:13px">
                        <i class="fa-solid fa-pen-nib" style="color:#db2777; margin-right:4px"></i> লিখিত লক্ষ্য
                    </span>
                    @if($isWrittenTargetMet)
                        <span style="background:#dcfce7; color:#15803d; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:12px">✅ পূর্ণ</span>
                    @else
                        <span style="background:#fef3c7; color:#92400e; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:12px">⏳ বাকি ({{ max(0, $exam->written_marks - $writtenPoolMarks) }})</span>
                    @endif
                </div>
                <div style="font-size:15px; font-weight:800; color:#0f172a">
                    {{ (int)$exam->written_marks }} নম্বর
                </div>
                <div style="font-size:11.5px; color:#64748b; margin-top:2px">
                    বর্তমানে পুলে যুক্ত: <strong>{{ $writtenPoolMarks }} নম্বর</strong> ({{ $writtenQuestions->count() }}টি)
                </div>
            </div>
            @endif

            {{-- 3. Tamrin --}}
            @if($exam->has_tamrin)
            <div style="background:#fffbeb; border:1.5px solid #fde68a; border-radius:8px; padding:10px 14px">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                    <span style="font-weight:700; color:#92400e; font-size:13px">
                        <i class="fa-solid fa-hand-holding-hand" style="color:#d97706; margin-right:4px"></i> তামরিন লক্ষ্য
                    </span>
                    <span style="background:#fef3c7; color:#92400e; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:12px">📌 নির্ধারিত</span>
                </div>
                <div style="font-size:15px; font-weight:800; color:#92400e">
                    {{ (int)$exam->tamrin_marks }} নম্বর
                </div>
                <div style="font-size:11.5px; color:#b45309; margin-top:2px">
                    হাতের কাজ / অ্যাসাইনমেন্ট মূল্যায়ন
                </div>
            </div>
            @endif

            {{-- 4. Viva --}}
            @if($exam->has_viva)
            <div style="background:#ecfdf5; border:1.5px solid #a7f3d0; border-radius:8px; padding:10px 14px">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                    <span style="font-weight:700; color:#065f46; font-size:13px">
                        <i class="fa-solid fa-microphone" style="color:#059669; margin-right:4px"></i> ভাইভা লক্ষ্য
                    </span>
                    <span style="background:#dcfce7; color:#065f46; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:12px">🎙️ নির্ধারিত</span>
                </div>
                <div style="font-size:15px; font-weight:800; color:#065f46">
                    {{ (int)$exam->viva_marks }} নম্বর
                </div>
                <div style="font-size:11.5px; color:#047857; margin-top:2px">
                    মৌখিক মূল্যায়ন / সরাসরি ইন্টারভিউ
                </div>
            </div>
            @endif
        </div>

        @if($isPoolMode)
            <div style="padding:10px 18px; background:#eef2ff; border-top:1px solid #e0e7ff; font-size:12px; color:#3730a3; display:flex; align-items:center; gap:10px">
                <i class="fa-solid fa-shuffle" style="font-size:15px; color:#4f46e5"></i>
                <span>
                    <strong>র‍্যান্ডম প্রশ্ন পুল সক্রিয়:</strong> পুলে {{ $poolMarks }} নম্বরের মোট {{ $exam->examQuestions->count() }}টি প্রশ্ন যুক্ত আছে। শিক্ষার্থীদের জন্য নির্ধারিত <strong>{{ $exam->full_marks }} নম্বরের</strong> প্রশ্ন সাফল হয়ে প্রদর্শিত হবে।
                </span>
            </div>
        @endif
    </div>

    <div style="display:grid; grid-template-columns:1fr 420px; gap:20px; font-family:'Kalpurush',sans-serif">

        {{-- Left Panel: Configured Exam Paper (Categorized Sections) --}}
        <div style="display:flex; flex-direction:column; gap:20px">

            {{-- বিভাগ-ক: বহুনির্বাচনী প্রশ্ন (MCQ Section) --}}
            @if($exam->has_mcq)
            <div class="card">
                <div class="card-header" style="background:#f8fafc">
                    <div style="display:flex; align-items:center; gap:8px">
                        <span class="card-title" style="font-size:15px; color:#1e293b">
                            <i class="fa-solid fa-list-check" style="color:#4f46e5"></i> বিভাগ-ক: বহুনির্বাচনী প্রশ্ন (MCQ Section)
                        </span>
                        <span class="badge badge-primary no-dot">{{ $mcqQuestions->count() }}টি প্রশ্ন</span>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#4f46e5">
                        যুক্ত: {{ $mcqPoolMarks }} / লক্ষ্য: {{ (int)$exam->mcq_marks }} নম্বর
                    </span>
                </div>
                <div style="padding:0">
                    @forelse($mcqQuestions as $i => $eq)
                    @php $q = $eq->question; @endphp
                    <div style="padding:14px 18px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:flex-start; gap:12px">
                        <div style="flex:1">
                            <div style="margin-bottom:4px; display:flex; align-items:center; gap:8px">
                                <span style="font-weight:700; color:#64748b; font-size:12px">ক-{{ $loop->iteration }}.</span>
                                <span style="padding:2px 7px; border-radius:12px; font-size:10px; font-weight:700; background:#e0e7ff; color:#4338ca">MCQ</span>
                                @if($q?->subject)
                                    <span style="font-size:10px; background:#f1f5f9; color:#334155; padding:2px 6px; border-radius:4px">{{ $q->subject->code }}</span>
                                @endif
                                @if($q?->source_tag)
                                    <span style="font-size:10px; background:#f8fafc; color:#64748b; padding:2px 6px; border-radius:4px; border:1px solid #e2e8f0">#{{ $q->source_tag }}</span>
                                @endif
                            </div>
                            <div style="font-weight:600; font-size:13.5px; color:#0f172a; margin-bottom:4px">
                                {!! e($q?->question_text) !!}
                            </div>
                            <div style="font-size:12px; color:#64748b">
                                সঠিক উত্তর: <strong style="color:#10b981">{{ strtoupper($q?->correct_option_id ?? '—') }}</strong> &middot;
                                নম্বর: <strong>{{ $eq->marks }}</strong>
                            </div>
                        </div>
                        <div>
                            <form method="POST" action="{{ route('admin.exams.questions.detach', [$exam, $eq]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444" title="Remove question" onsubmit="return confirm('Remove question?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div style="padding:30px; text-align:center; color:#94a3b8; font-size:13px">
                        <i class="fa-solid fa-circle-info" style="margin-right:4px"></i> এখনো কোনো MCQ প্রশ্ন যুক্ত করা হয়নি। ডানপাশের পুল থেকে যুক্ত করুন →
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- বিভাগ-খ: লিখিত প্রশ্ন (Written Section) --}}
            @if($exam->has_written)
            <div class="card">
                <div class="card-header" style="background:#fdf2f8">
                    <div style="display:flex; align-items:center; gap:8px">
                        <span class="card-title" style="font-size:15px; color:#9d174d">
                            <i class="fa-solid fa-pen-nib" style="color:#db2777"></i> বিভাগ-খ: লিখিত প্রশ্ন (Written Section)
                        </span>
                        <span class="badge badge-danger no-dot">{{ $writtenQuestions->count() }}টি প্রশ্ন</span>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#db2777">
                        যুক্ত: {{ $writtenPoolMarks }} / লক্ষ্য: {{ (int)$exam->written_marks }} নম্বর
                    </span>
                </div>
                <div style="padding:0">
                    @forelse($writtenQuestions as $i => $eq)
                    @php $q = $eq->question; @endphp
                    <div style="padding:14px 18px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:flex-start; gap:12px">
                        <div style="flex:1">
                            <div style="margin-bottom:4px; display:flex; align-items:center; gap:8px">
                                <span style="font-weight:700; color:#64748b; font-size:12px">খ-{{ $loop->iteration }}.</span>
                                <span style="padding:2px 7px; border-radius:12px; font-size:10px; font-weight:700; background:#fce7f3; color:#9d174d">WRITTEN</span>
                                @if($q?->subject)
                                    <span style="font-size:10px; background:#f1f5f9; color:#334155; padding:2px 6px; border-radius:4px">{{ $q->subject->code }}</span>
                                @endif
                            </div>
                            <div style="font-weight:600; font-size:13.5px; color:#0f172a; margin-bottom:4px">
                                {!! e($q?->question_text) !!}
                            </div>
                            <div style="font-size:12px; color:#64748b">
                                মূল্যায়ন: <em>শিক্ষক কর্তৃক মূল্যায়নকৃত</em> &middot;
                                পূর্ণমান: <strong>{{ $eq->marks }}</strong>
                            </div>
                        </div>
                        <div>
                            <form method="POST" action="{{ route('admin.exams.questions.detach', [$exam, $eq]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444" title="Remove question" onsubmit="return confirm('Remove question?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div style="padding:30px; text-align:center; color:#94a3b8; font-size:13px">
                        <i class="fa-solid fa-circle-info" style="margin-right:4px"></i> এখনো কোনো লিখিত প্রশ্ন যুক্ত করা হয়নি। ডানপাশের পুল থেকে ফিল্টার করে যুক্ত করুন →
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- বিভাগ-গ: তামরিন ও ভাইভা সংক্রান্ত নির্দেশনা --}}
            @if($exam->has_tamrin || $exam->has_viva)
            <div class="card" style="border:1.5px solid #fed7aa; background:#fffbf5">
                <div class="card-header" style="background:#ffedd5">
                    <span class="card-title" style="font-size:15px; color:#9a3412">
                        <i class="fa-solid fa-comments" style="color:#ea580c"></i> বিভাগ-গ: তামরিন ও ভাইভা মূল্যায়ন রূপরেখা
                    </span>
                    <span style="font-size:12px; font-weight:700; color:#ea580c">
                        মোট: {{ (int)(($exam->tamrin_marks ?? 0) + ($exam->viva_marks ?? 0)) }} নম্বর
                    </span>
                </div>
                <div style="padding:16px 20px; font-size:13px; color:#431407">
                    <p style="margin-bottom:10px">
                        এই পরীক্ষার জন্য সরাসরি ব্যবহারিক/মৌখিক মূল্যায়ন সক্রিয় রয়েছে। পরীক্ষার পর সংশ্লিষ্ট শিক্ষক মার্ক এন্ট্রি শিটে শিক্ষার্থীদের প্রাপ্ত নম্বর সরাসরি ইনপুট করবেন:
                    </p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px">
                        @if($exam->has_tamrin)
                        <div style="background:#fff; border:1px solid #fde68a; border-radius:8px; padding:12px">
                            <strong style="color:#b45309; display:block; margin-bottom:4px">
                                <i class="fa-solid fa-hand-holding-hand"></i> তামরিন (পূর্ণমান: {{ (int)$exam->tamrin_marks }} নম্বর)
                            </strong>
                            <span style="font-size:12px; color:#64748b">শিক্ষার্থীদের জমা দেওয়া খাতার হাতের কাজ, বাড়ির কাজ বা অ্যাসাইনমেন্টের ওপর ভিত্তি করে নম্বর প্রদান।</span>
                        </div>
                        @endif
                        @if($exam->has_viva)
                        <div style="background:#fff; border:1px solid #a7f3d0; border-radius:8px; padding:12px">
                            <strong style="color:#047857; display:block; margin-bottom:4px">
                                <i class="fa-solid fa-microphone"></i> ভাইভা (পূর্ণমান: {{ (int)$exam->viva_marks }} নম্বর)
                            </strong>
                            <span style="font-size:12px; color:#64748b">শিক্ষার্থীদের মৌখিক সাক্ষাৎকার, সঠিক তেলাওয়াত ও উচ্চারণের দক্ষতা যাচাই করে নম্বর প্রদান।</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- Available Question Bank Selector --}}
        <div class="card">
            <div class="card-header" style="flex-direction:column;align-items:flex-start;gap:8px">
                <span class="card-title"><i class="fa-solid fa-layer-group" style="color:#6366f1"></i> Question Bank Pool</span>
                <span style="font-size:12px;color:var(--text-muted)">যেকোনো বিষয়, ব্যাচ, সেমিস্টার বা ট্যাগ থেকে প্রশ্ন সিলেক্ট করে যুক্ত করুন</span>
            </div>
            <div style="padding:14px">

                {{-- Notice for Pure Viva / Tamrin Exams --}}
                @if(!$exam->has_mcq && !$exam->has_written)
                <div style="background:#fffbeb; border:1.5px solid #fde68a; border-radius:10px; padding:16px; margin-bottom:14px; text-align:center">
                    <i class="fa-solid fa-microphone-lines" style="font-size:26px; color:#d97706; margin-bottom:8px; display:block"></i>
                    <strong style="color:#92400e; font-size:13.5px; display:block; margin-bottom:4px">
                        এই পরীক্ষাটি সম্পূর্ণ {{ $exam->has_viva && $exam->has_tamrin ? 'ভাইভা ও তামরিন' : ($exam->has_viva ? 'ভাইভা (মৌখিক)' : 'তামরিন (হাতের কাজ)') }} মূল্যায়নের জন্য নির্ধারিত
                    </strong>
                    <p style="font-size:11.5px; color:#78350f; margin:0">
                        এই পরীক্ষার জন্য অনলাইন প্রশ্নপত্রে প্রশ্ন সংযুক্তির আবশ্যকতা নেই। সংশ্লিষ্ট শিক্ষক পরীক্ষা সম্পন্ন হওয়ার পর সরাসরি মার্ক এন্ট্রি শিটে নম্বর প্রদান করবেন।
                    </p>
                </div>
                @endif

                {{-- Pull Random Questions Card --}}
                @if($exam->has_mcq || $exam->has_written)
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:14px">
                    <div style="font-weight:700;font-size:12px;color:#166534;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-dice" style="color:#16a34a"></i> র‍্যান্ডম প্রশ্ন যোগ করুন (Pull Random Questions)
                    </div>
                    <form method="POST" action="{{ route('admin.exams.questions.random', $exam) }}" style="display:flex;flex-direction:column;gap:8px">
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
                @endif

                {{-- Filter Bar --}}
                <form method="GET" action="{{ route('admin.exams.builder', $exam) }}" style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:8px">
                    <input type="text" name="search" class="form-control" placeholder="প্রশ্ন অনুসন্ধান করুন..." value="{{ $search }}" style="height:32px;font-size:12px">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        <select name="pool_subject_id" class="form-control" style="height:32px;font-size:11px">
                            <option value="all" {{ ($subjectId ?? '') === 'all' ? 'selected' : '' }}>সকল বিষয় (All Subjects)</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ ($subjectId ?? $exam->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                            @endforeach
                        </select>

                        <select name="question_type" class="form-control" style="height:32px;font-size:11px">
                            <option value="">সকল প্রশ্ন ধরন</option>
                            <option value="MCQ" {{ ($selectedQType ?? '') === 'MCQ' ? 'selected' : '' }}>MCQ (বহুনির্বাচনী)</option>
                            <option value="WRITTEN" {{ ($selectedQType ?? '') === 'WRITTEN' ? 'selected' : '' }}>WRITTEN (লিখিত)</option>
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        <select name="batch_id" id="pool_batch_select" class="form-control" style="height:32px;font-size:11px">
                            <option value="" data-course-id="">সকল ব্যাচ (All Batches)</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}" {{ ($batchId ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>

                        <select name="semester_id" id="pool_semester_select" class="form-control" style="height:32px;font-size:11px">
                            <option value="" data-course-id="">সকল সেমিস্টার (All Semesters)</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}" 
                                        data-course-id="{{ $sem->course_id }}" 
                                        data-course-name="{{ $sem->course?->name ?? 'অন্যান্য কোর্স' }}"
                                        data-name="{{ $sem->name }}"
                                        {{ ($semesterId ?? '') == $sem->id ? 'selected' : '' }}>
                                    {{ $sem->name }} ({{ $sem->course?->name ?? 'Course' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        <select name="exam_type" class="form-control" style="height:32px;font-size:11px">
                            <option value="">সকল পরীক্ষার ধরন</option>
                            @foreach($examTypes as $et)
                                <option value="{{ $et }}" {{ ($examType ?? '') === $et ? 'selected' : '' }}>{{ $et }}</option>
                            @endforeach
                        </select>

                        <select name="difficulty" class="form-control" style="height:32px;font-size:11px">
                            <option value="">সকল কঠিনতা</option>
                            <option value="easy" {{ ($difficulty ?? '') === 'easy' ? 'selected' : '' }}>Easy (সহজ)</option>
                            <option value="medium" {{ ($difficulty ?? '') === 'medium' ? 'selected' : '' }}>Medium (মধ্যম)</option>
                            <option value="hard" {{ ($difficulty ?? '') === 'hard' ? 'selected' : '' }}>Hard (কঠিন)</option>
                        </select>
                    </div>

                    <div style="display:flex;gap:6px">
                        <button type="submit" class="btn btn-primary btn-sm" style="flex:1;height:32px;padding:0;font-size:11px">
                            <i class="fa-solid fa-filter"></i> ফিল্টার
                        </button>
                        @if($search || $difficulty || $examType || $batchId || $semesterId || ($subjectId && $subjectId !== $exam->subject_id))
                            <a href="{{ route('admin.exams.builder', $exam) }}" class="btn btn-outline btn-sm" style="height:32px;padding:4px 8px;font-size:11px" title="Reset">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
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
                        <form method="POST" action="{{ route('admin.exams.questions.attach', $exam) }}" style="display:flex;gap:8px;align-items:center">
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

    <script>
        function setupBatchSemesterCascading(batchSelectId, semesterSelectId, defaultSemText) {
            const batchSelect = typeof batchSelectId === 'string' ? document.getElementById(batchSelectId) : batchSelectId;
            const semSelect = typeof semesterSelectId === 'string' ? document.getElementById(semesterSelectId) : semesterSelectId;
            if (!batchSelect || !semSelect) return () => {};

            const allSemesterData = [];
            Array.from(semSelect.options).forEach(opt => {
                if (!opt.value) return;
                allSemesterData.push({
                    value: opt.value,
                    courseId: String(opt.getAttribute('data-course-id') || ''),
                    courseName: opt.getAttribute('data-course-name') || '',
                    name: opt.getAttribute('data-name') || opt.text
                });
            });

            function update() {
                const selectedOption = batchSelect.options[batchSelect.selectedIndex];
                const selectedCourseId = selectedOption ? String(selectedOption.getAttribute('data-course-id') || '') : '';
                const currentSemVal = String(semSelect.value || '');

                semSelect.innerHTML = '';

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = defaultSemText || 'সকল সেমিস্টার (All Semesters)';
                semSelect.appendChild(defaultOpt);

                if (selectedCourseId) {
                    const filtered = allSemesterData.filter(s => s.courseId === selectedCourseId);
                    filtered.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.value;
                        opt.textContent = s.name;
                        opt.setAttribute('data-course-id', s.courseId);
                        opt.setAttribute('data-name', s.name);
                        if (currentSemVal === String(s.value)) {
                            opt.selected = true;
                        }
                        semSelect.appendChild(opt);
                    });
                } else {
                    const groups = {};
                    allSemesterData.forEach(s => {
                        const cName = s.courseName || 'অন্যান্য কোর্স';
                        if (!groups[cName]) groups[cName] = [];
                        groups[cName].push(s);
                    });

                    Object.keys(groups).forEach(cName => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = cName;
                        groups[cName].forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.value;
                            opt.textContent = `${s.name} (${cName})`;
                            opt.setAttribute('data-course-id', s.courseId);
                            opt.setAttribute('data-name', s.name);
                            if (currentSemVal === String(s.value)) {
                                opt.selected = true;
                            }
                            optgroup.appendChild(opt);
                        });
                        semSelect.appendChild(optgroup);
                    });
                }
            }

            batchSelect.addEventListener('change', update);
            update();
            return update;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setupBatchSemesterCascading('pool_batch_select', 'pool_semester_select', 'সকল সেমিস্টার (All Semesters)');
            });
        } else {
            setupBatchSemesterCascading('pool_batch_select', 'pool_semester_select', 'সকল সেমিস্টার (All Semesters)');
        }
    </script>
</x-admin-layout>
