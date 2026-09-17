<x-student-layout>
    <x-slot name="title">পরীক্ষার ফলাফল — {{ $exam->title }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>পরীক্ষার ফলাফল</h1>
            <p>{{ $exam->title }} &middot; {{ $exam->subject?->name }} ({{ $exam->subject?->code }})</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('student.exams.index') }}" class="btn btn-outline">← পরীক্ষার তালিকায় ফিরুন</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    @if($submission->status === 'AUTO_SUBMITTED_VIOLATION')
        <div style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;padding:16px 20px;border-radius:10px;margin-bottom:20px;font-size:13px;display:flex;align-items:flex-start;gap:12px">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:22px;color:#dc2626;flex-shrink:0;margin-top:2px"></i>
            <div>
                <strong style="font-size:14px">Anti-Cheating Notice:</strong>
                <div style="margin-top:2px">পরীক্ষা চলাকালীন অনিচ্ছাকৃত বা নিষিদ্ধ ট্যাব সুইচের ({{ $submission->tab_switch_count }} বার) কারণে এই পরীক্ষাটি স্বয়ংক্রিয়ভাবে জমা (Auto Submitted) হয়েছে।</div>
            </div>
        </div>
    @endif

    {{-- Appeal to Re-Exam Section --}}
    @if(isset($latestAppeal) && $latestAppeal && $latestAppeal->isApproved())
        <div style="background:#ecfdf5;border:1.5px solid #6ee7b7;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 1px 4px rgba(16,185,129,0.1)">
            <div>
                <div style="font-weight:800;color:#065f46;font-size:15px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-circle-check" style="color:#059669;font-size:18px"></i> পুনরায় পরীক্ষার আবেদন অনুমোদিত হয়েছে! (Appeal Approved)
                </div>
                <p style="margin:4px 0 0;font-size:12px;color:#047857">
                    অনুমোদনের তারিখ: {{ $latestAppeal->reviewed_at?->format('d M Y, h:i A') }}
                    @if($latestAppeal->admin_remarks) &middot; মন্তব্য: "{{ $latestAppeal->admin_remarks }}" @endif
                    <br>আপনার পূর্বের খাতা রিসেট করা হয়েছে। আপনি এখন পুনরায় নতুন করে পরীক্ষা দিতে পারবেন।
                </p>
            </div>
            <a href="{{ route('student.exams.take', $exam) }}" class="btn btn-primary" style="background:#059669;border-color:#047857;color:#fff;font-weight:700;padding:10px 20px;display:inline-flex;align-items:center;gap:8px;box-shadow:0 2px 8px rgba(5,150,105,0.3)">
                <i class="fa-solid fa-rotate-right"></i> পুনরায় পরীক্ষা শুরু করুন (Start Re-Exam)
            </a>
        </div>
    @elseif(isset($latestAppeal) && $latestAppeal && $latestAppeal->isPending())
        <div style="background:#fefce8;border:1.5px solid #fef08a;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-weight:700;color:#854d0e;font-size:14px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-hourglass-half" style="color:#ca8a04"></i> পুনরায় পরীক্ষার আবেদন পর্যালোচনায় রয়েছে (Appeal Pending)
                </div>
                <p style="margin:4px 0 0;font-size:12px;color:#a16207">
                    আবেদনের কারণ: "{{ $latestAppeal->reason }}" (জমার সময়: {{ $latestAppeal->created_at->format('d M Y, h:i A') }})
                    <br>এডমিন বা সংশ্লিষ্ট শিক্ষক আবেদনটি অনুমোদন করলে আপনি পুনরায় পরীক্ষা দিতে পারবেন।
                </p>
            </div>
            <span class="badge badge-warning no-dot" style="background:#fde047;color:#713f12;font-size:12px;padding:6px 14px;border-radius:20px;font-weight:700">
                অপেক্ষমাণ (Pending Review)
            </span>
        </div>
    @elseif(isset($latestAppeal) && $latestAppeal && $latestAppeal->isRejected())
        <div style="background:#fff1f2;border:1.5px solid #fecdd3;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-weight:700;color:#9f1239;font-size:14px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-circle-xmark" style="color:#e11d48"></i> পুনরায় পরীক্ষার আবেদনটি গৃহীত হয়নি (Appeal Rejected)
                </div>
                <p style="margin:4px 0 0;font-size:12px;color:#be123c">
                    পর্যালোচনার তারিখ: {{ $latestAppeal->reviewed_at?->format('d M Y, h:i A') }}
                    @if($latestAppeal->admin_remarks) &middot; কারণ: "{{ $latestAppeal->admin_remarks }}" @endif
                </p>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('appealModal').style.display='flex'" style="border-color:#f43f5e;color:#e11d48;font-weight:700">
                <i class="fa-solid fa-rotate-right"></i> নতুন কারণ উল্লেখ করে পুনরায় আপিল করুন
            </button>
        </div>
    @else
        <div style="background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-weight:700;color:#1e293b;font-size:14px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-shield-halved" style="color:#6366f1"></i> কোনো সমস্যার কারণে পরীক্ষা ব্যাহত হয়েছে?
                </div>
                <p style="margin:4px 0 0;font-size:12px;color:#64748b">
                    অনিচ্ছাকৃত ট্যাব সুইচ, বিদ্যুৎ বিভ্রাট, ইন্টারনেট বিচ্ছিন্নতা বা কোনো অনিবার্য কারণে পরীক্ষা ক্ষতিগ্রস্ত হলে পুনরায় পরীক্ষার আবেদন করতে পারেন।
                </p>
            </div>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('appealModal').style.display='flex'" style="border-color:#6366f1;color:#6366f1;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-file-signature"></i> রি-এক্সামের জন্য আপিল করুন (Appeal to Re-Exam)
            </button>
        </div>
    @endif

    {{-- Score Summary --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ number_format($submission->total_score, 1) }} / {{ $exam->full_marks }}</div>
                <div class="stat-label">মোট নম্বর</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $submission->correct_count }}</div>
                <div class="stat-label">সঠিক উত্তর (MCQ)</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value" style="color:#e11d48">{{ $submission->wrong_count }}</div>
                <div class="stat-label">ভুল উত্তর (MCQ)</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value" style="color:#e11d48">-{{ $submission->negative_marks_deducted }}</div>
                <div class="stat-label">Negative Mark Penalty</div>
            </div>
        </div>
    </div>

    {{-- Question Breakdown --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">প্রশ্ন বিশ্লেষণ</span>
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
                            <span style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#fce7f3;color:#9d174d">WRITTEN</span>
                        @else
                            <span style="padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#e0e7ff;color:#4338ca">MCQ</span>
                        @endif
                    </div>
                    <div>
                        @if($q->question_type === 'WRITTEN')
                            @if($answer?->teacher_marks !== null)
                                <span class="badge badge-success no-dot">Teacher নম্বর: {{ $answer->teacher_marks }}/{{ $eq->marks }}</span>
                            @else
                                <span class="badge badge-secondary no-dot">⏳ Teacher Grading Pending</span>
                            @endif
                        @else
                            @php
                                $userOpt  = strtolower($answer?->selected_option_id ?? '');
                                $isRight  = $answer?->is_correct;
                            @endphp
                            @if($isRight)
                                <span class="badge badge-success no-dot">সঠিক (+{{ $eq->marks }})</span>
                            @elseif($userOpt)
                                <span class="badge badge-danger no-dot">ভুল (-{{ $exam->negative_marking }})</span>
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
                        @if($isCorr) <span style="color:#166534;font-weight:700"> (সঠিক উত্তর)</span> @endif
                        @if($isUser) <span style="color:#991b1b;font-weight:700"> (আপনার উত্তর)</span> @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Written Answer Image --}}
                @if($q->question_type === 'WRITTEN')
                    @if($answer?->answer_image_path)
                        @php
                            $imgRel = ltrim(str_replace('public/', '', $answer->answer_image_path), '/');
                            $imgUrl = asset('storage/' . $imgRel);
                        @endphp
                        <div style="margin-top:10px">
                            <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;display:flex;align-items:center;justify-content:space-between">
                                <span>আপনার উত্তর:</span>
                                <a href="{{ $imgUrl }}" target="_blank" style="font-size:11px;color:#2563eb;text-decoration:underline">পূর্ণআকারে দেখুন (Open Image) ↗</a>
                            </div>
                            <img src="{{ $imgUrl }}"
                                 alt="Your Answer"
                                 style="max-width:100%;max-height:400px;border-radius:8px;border:1px solid #f9a8d4;display:block"
                                 onerror="this.onerror=null;this.style.display='none';document.getElementById('ans-fallback-{{ $answer->id }}').style.display='block';">
                            <div id="ans-fallback-{{ $answer->id }}" style="display:none;padding:10px;background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;font-size:12px;color:#9f1239;margin-top:6px">
                                আপনার উত্তরের ছবি পাওয়া গেছে — <a href="{{ $imgUrl }}" target="_blank" style="color:#9f1239;font-weight:700;text-decoration:underline">সরাসরি নতুন ট্যাবে দেখুন ↗</a>
                            </div>
                        </div>
                    @else
                        <div style="color:#94a3b8;font-size:13px;font-style:italic;margin-top:8px">কোনো উত্তর Upload করা হয়নি।</div>
                    @endif
                @endif

            </div>
            @endforeach

        </div>
    </div>

    {{-- Appeal for Re-Exam Modal --}}
    <div id="appealModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.65);z-index:9999;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(2px)">
        <div style="background:#fff;border-radius:12px;max-width:540px;width:100%;box-shadow:0 20px 25px -5px rgba(0,0,0,0.25);overflow:hidden;animation:fadeIn 0.2s ease-out">
            <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
                <h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-file-signature" style="color:#6366f1"></i> পুনরায় পরীক্ষার আবেদন (Appeal for Re-Exam)
                </h3>
                <button type="button" onclick="document.getElementById('appealModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b;line-height:1">&times;</button>
            </div>
            <form method="POST" action="{{ route('student.exams.appeal', $exam) }}" style="padding:22px">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $submission->id }}">

                <div style="background:#f1f5f9;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#334155;display:flex;justify-content:space-between;align-items:center">
                    <div>বিষয়: <strong>{{ $exam->title }}</strong></div>
                    <div>বর্তমান স্কোর: <strong style="color:#4338ca">{{ number_format($submission->total_score, 1) }}/{{ $exam->full_marks }}</strong></div>
                </div>

                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        আবেদনের কারণ (Reason for Re-Exam) <span style="color:#e11d48">*</span>
                    </label>
                    <textarea name="reason" rows="4" required placeholder="কেন পুনরায় পরীক্ষা দিতে চাচ্ছেন তার সুনির্দিষ্ট ও যৌক্তিক কারণ লিখুন (যেমন: অনিচ্ছাকৃত ট্যাব সুইচ, বিদ্যুৎ বিভ্রাট, ডিভাইস ক্র্যাশ ইত্যাদি)..." style="width:100%;border:1.5px solid #cbd5e1;border-radius:8px;padding:10px 12px;font-size:13px;box-sizing:border-box;font-family:inherit;outline:none"></textarea>
                    <small style="color:#64748b;font-size:11px;margin-top:5px;display:block">
                        <i class="fa-solid fa-circle-info" style="color:#6366f1"></i> শিক্ষক বা এডমিন আপনার কারণ পর্যালোচনা করে অনুমোদন দিলে আপনার বর্তমান খাতা রিসেট হবে এবং আপনি নতুন করে পরীক্ষা দিতে পারবেন।
                    </small>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('appealModal').style.display='none'">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#6366f1;display:inline-flex;align-items:center;gap:6px;font-weight:700">
                        <i class="fa-solid fa-paper-plane"></i> আবেদন জমা দিন
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-student-layout>
