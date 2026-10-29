<x-student-layout>
    <x-slot name="title">Online Exams</x-slot>

    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px">
        <div class="page-header-left">
            <h1 style="margin:0 0 4px 0">Online Examinations</h1>
            <p style="margin:0;color:#64748b">Class Quizzes, Class Tests, Half-Term, and Final Examinations</p>
        </div>
        @php
            $hasRunningSemesters = false;
            $semNamesText = '';
            if (isset($runningSemesterNames)) {
                if ($runningSemesterNames instanceof \Illuminate\Support\Collection) {
                    $hasRunningSemesters = $runningSemesterNames->isNotEmpty();
                    $semNamesText = $runningSemesterNames->join(', ');
                } elseif (is_array($runningSemesterNames)) {
                    $hasRunningSemesters = !empty($runningSemesterNames);
                    $semNamesText = implode(', ', $runningSemesterNames);
                } elseif (is_string($runningSemesterNames) && trim($runningSemesterNames) !== '') {
                    $hasRunningSemesters = true;
                    $semNamesText = $runningSemesterNames;
                }
            }
        @endphp
        @if($hasRunningSemesters)
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:6px 14px;border-radius:20px;display:flex;align-items:center;gap:6px">
                <i class="fa-solid fa-graduation-cap" style="color:#059669"></i>
                <span style="font-size:12px;color:#166534;font-weight:700">রানিং সেমিস্টার:</span>
                <strong style="font-size:12.5px;color:#047857">{{ $semNamesText }}</strong>
            </div>
        @endif
    </div>

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
        </div>
    @endif

    {{-- Filter Bar: Running Semester and Subject Selector --}}
    @if(!empty($subjects) && $subjects->isNotEmpty())
        <div class="card" style="margin-bottom:18px;padding:12px 18px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;font-family:'Kalpurush',sans-serif">
            <form method="GET" action="{{ route('student.exams.index') }}" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <label style="font-size:13px;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-book-open" style="color:#059669"></i> বিষয় নির্বাচন করুন:
                    </label>
                    <select id="filterSubject" name="subject_id" onchange="this.form.submit()" class="form-control" style="height:38px;border-radius:8px;font-family:'Kalpurush',sans-serif;font-weight:700;min-width:240px">
                        <option value="">-- সকল বিষয় (All Running Subjects) --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ ($selectedSubjectId == $sub->id) ? 'selected' : '' }}>
                                {{ $sub->name }} ({{ $sub->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedSubjectId)
                    <a href="{{ route('student.exams.index') }}" class="btn btn-sm btn-outline" style="font-size:12px;font-weight:700;color:#64748b;border-color:#cbd5e1">
                        <i class="fa-solid fa-filter-circle-xmark"></i> ফিল্টার মুছুন
                    </a>
                @endif
            </form>
        </div>
    @endif

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Exam Title &amp; Subject</th>
                        <th>Type</th>
                        <th>Full Marks / Pass</th>
                        <th>Duration / Negative Mark</th>
                        <th>Schedule</th>
                        <th style="text-align:center;min-width:180px">Action / Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $ex)
                    @php
                        $submission = $ex->submissions->first();
                        $isSubmitted = $submission && in_array($submission->status, ['SUBMITTED', 'AUTO_SUBMITTED_VIOLATION', 'AUTO_SUBMITTED_TIMEOUT', 'AUTO_SUBMITTED']);
                        $isInProgress = $submission && $submission->status === 'IN_PROGRESS';
                        $latestAppeal = $ex->appeals->sortByDesc('id')->first();
                        $hasApprovedAppeal = $latestAppeal && $latestAppeal->isApproved();
                        $timingStatus = $ex->timing_status; // 'UPCOMING', 'ACTIVE', 'EXPIRED'
                        $startDt = $ex->getEffectiveStartDatetime();
                        $endDt = $ex->getEffectiveEndDatetime();
                        $canReset = $ex->canApplyForReset();
                        $resetDeadline = $ex->reset_deadline ? \Carbon\Carbon::parse($ex->reset_deadline) : null;

                        $typeBadge = match($ex->type) {
                            'QUIZ' => 'badge-secondary',
                            'CLASS_TEST' => 'badge-info',
                            'HALF_TERM' => 'badge-warning',
                            'FINAL' => 'badge-danger',
                            default => 'badge-primary'
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $ex->title }}</strong><br>
                            <span style="color:#64748b;font-size:12px">{{ $ex->subject?->name ?? '—' }} ({{ $ex->subject?->code }})</span>
                        </td>
                        <td><span class="badge {{ $typeBadge }} no-dot">{{ $ex->type }}</span></td>
                        <td>
                            <strong>{{ $ex->full_marks }} Marks</strong><br>
                            <small style="color:#64748b">Pass: {{ $ex->pass_marks }}</small>
                        </td>
                        <td>
                            ⏱️ {{ $ex->duration_minutes }} mins<br>
                            @if($ex->negative_marking > 0)
                                <small style="color:#e11d48">Negative: -{{ $ex->negative_marking }} per wrong</small>
                            @else
                                <small style="color:#10b981">No Negative Mark</small>
                            @endif
                        </td>
                        <td style="font-size:12px;line-height:1.5">
                            <div style="color:#1e293b;font-weight:700">
                                <i class="fa-regular fa-calendar-check" style="color:#6366f1"></i>
                                {{ $startDt->format('d M Y') }}
                            </div>
                            <div style="color:#0284c7;font-size:11px;font-weight:600;margin-top:2px">
                                <i class="fa-regular fa-clock"></i> শুরু: {{ $startDt->format('h:i A') }}
                            </div>
                            <div style="color:#e11d48;font-size:11px;font-weight:600">
                                <i class="fa-solid fa-hourglass-end"></i> শেষ: {{ $endDt->format('d M Y, h:i A') }}
                            </div>
                        </td>
                        <td style="text-align:center;min-width:180px">
                            @if($hasApprovedAppeal && !$isSubmitted)
                                {{-- Approved for Re-exam --}}
                                @if($latestAppeal->requiresPayment())
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                        <span class="badge no-dot" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a;font-size:11px;font-weight:700">
                                            <i class="fa-solid fa-triangle-exclamation"></i> আপিল অনুমোদিত (ফি বাকি)
                                        </span>
                                        <span style="font-size:12px;font-weight:800;color:#e11d48">
                                            বকেয়া ফি: ৳{{ number_format($latestAppeal->fee_amount, 0) }}
                                        </span>
                                        <a href="{{ route('student.fees.index') }}" class="btn btn-sm" style="background:#0284c7;color:#fff;font-weight:700;padding:5px 14px;border-radius:6px;font-size:12px;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 4px rgba(2,132,199,0.3)">
                                            <i class="fa-solid fa-credit-card"></i> ফি পরিশোধ করুন
                                        </a>
                                        <small style="color:#64748b;font-size:10.5px">ফি পরিশোধের পর পরীক্ষা উন্মুক্ত হবে</small>
                                    </div>
                                @else
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                        <a href="{{ route('student.exams.take', $ex) }}" class="btn btn-success btn-sm" style="background:#059669;border-color:#047857;color:#fff;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 6px rgba(5,150,105,0.3)">
                                            <i class="fa-solid fa-rotate-right"></i> পুনরায় পরীক্ষা দিন (Re-Exam)
                                        </a>
                                        <small style="color:#059669;font-size:11px;font-weight:700">
                                            <i class="fa-solid fa-check-circle"></i> আপিল অনুমোদিত (ফি পরিশোধিত)
                                        </small>
                                    </div>
                                @endif
                            @elseif($isSubmitted)
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                    <span class="badge badge-active no-dot" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11px">
                                        <i class="fa-solid fa-check-circle"></i> জমা সম্পন্ন
                                    </span>
                                    <a href="{{ route('student.exams.result', [$ex, $submission]) }}" class="btn btn-outline btn-sm" style="color:#10b981;font-weight:600">
                                        View Result ({{ number_format($submission->total_score, 1) }})
                                    </a>
                                    @if($latestAppeal && $latestAppeal->isPending())
                                        <small style="color:#b45309;font-size:11px;font-weight:700;background:#fef3c7;border:1px solid #fde68a;padding:2px 8px;border-radius:10px">
                                            <i class="fa-regular fa-clock"></i> আপিল অপেক্ষমাণ
                                        </small>
                                    @elseif($latestAppeal && $latestAppeal->requiresPayment())
                                        <small style="color:#b45309;font-size:11px;font-weight:700;background:#fffbeb;border:1px solid #fde68a;padding:2px 8px;border-radius:10px">
                                            <i class="fa-solid fa-triangle-exclamation"></i> ফি বাকি: ৳{{ number_format($latestAppeal->fee_amount, 0) }}
                                        </small>
                                    @elseif($latestAppeal && $latestAppeal->isApproved())
                                        <small style="color:#047857;font-size:11px;font-weight:700;background:#ecfdf5;border:1px solid #a7f3d0;padding:2px 8px;border-radius:10px">
                                            <i class="fa-solid fa-check-double"></i> আপিল অনুমোদিত
                                        </small>
                                    @endif
                                </div>
                            @elseif($timingStatus === 'UPCOMING')
                                {{-- Upcoming Exam: Timer displayed, cannot start before start date/time --}}
                                <div class="exam-countdown-container" data-start="{{ $startDt->toISOString() }}" data-exam-url="{{ route('student.exams.take', $ex) }}" style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                    <span style="font-size:11px;font-weight:700;color:#0284c7;background:#f0f9ff;border:1px solid #bae6fd;padding:2px 8px;border-radius:12px">
                                        <i class="fa-regular fa-clock"></i> শুরু হতে বাকি
                                    </span>
                                    <div class="countdown-display" style="font-size:13px;font-weight:800;color:#0369a1;font-family:monospace;letter-spacing:0.5px">
                                        হিসাব হচ্ছে...
                                    </div>
                                    <button type="button" class="btn btn-sm btn-disabled" disabled style="background:#f1f5f9;color:#94a3b8;border:1px solid #cbd5e1;cursor:not-allowed;font-size:12px;opacity:0.85" title="পরীক্ষার নির্ধারিত সময়ের পূর্বে শুরু করা সম্ভব নয়">
                                        <i class="fa-solid fa-lock"></i> আসন্ন (Locked)
                                    </button>
                                </div>
                            @elseif($timingStatus === 'EXPIRED')
                                {{-- Expired Exam: Cannot start after end date/time unless appeal/reset approved --}}
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                    <span class="badge badge-danger no-dot" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:4px 9px;font-size:11px;display:inline-flex;align-items:center;gap:4px;font-weight:700">
                                        <i class="fa-solid fa-clock-rotate-left"></i> সময় সমাপ্ত (Expired)
                                    </span>
                                    @if($latestAppeal && $latestAppeal->isPending())
                                        <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:11px;padding:3px 8px;font-weight:700">
                                            <i class="fa-regular fa-clock"></i> রিসেট পর্যালোচনায়
                                        </span>
                                    @elseif($latestAppeal && $latestAppeal->requiresPayment())
                                        <span style="font-size:11.5px;font-weight:800;color:#e11d48">
                                            বকেয়া ফি: ৳{{ number_format($latestAppeal->fee_amount, 0) }}
                                        </span>
                                        <a href="{{ route('student.fees.index') }}" class="btn btn-sm" style="background:#0284c7;color:#fff;font-weight:700;padding:4px 10px;border-radius:6px;font-size:11.5px;display:inline-flex;align-items:center;gap:4px">
                                            <i class="fa-solid fa-credit-card"></i> ফি দিন
                                        </a>
                                    @elseif(!$hasApprovedAppeal)
                                        @if($canReset)
                                            <button type="button"
                                                    class="btn btn-outline btn-sm"
                                                    onclick="openIndexAppealModal({{ $ex->id }}, '{{ addslashes($ex->title) }}', '{{ addslashes($ex->subject?->name ?? '—') }}')"
                                                    style="border-color:#4f46e5;color:#4f46e5;font-weight:700;font-size:11.5px;padding:4px 12px;display:inline-flex;align-items:center;gap:5px;border-radius:6px;box-shadow:0 1px 3px rgba(79,70,229,0.15)">
                                                <i class="fa-solid fa-rotate-left"></i> এক্সাম রিসেট
                                            </button>
                                            @if($resetDeadline)
                                                <small style="color:#059669;font-size:10px;font-weight:700" title="রিসেট আবেদনের শেষ সময়">
                                                    <i class="fa-regular fa-clock"></i> রিসেট শেষ: {{ $resetDeadline->format('d M, h:i A') }}
                                                </small>
                                            @endif
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm btn-disabled"
                                                    disabled
                                                    style="background:#f1f5f9;color:#94a3b8;border:1px solid #cbd5e1;cursor:not-allowed;font-size:11px;padding:4px 10px;opacity:0.75;display:inline-flex;align-items:center;gap:4px;border-radius:6px"
                                                    title="রিসেটের আবেদনের নির্ধারিত সময়সীমা শেষ হয়ে গেছে">
                                                <i class="fa-solid fa-ban"></i> এক্সাম রিসেট
                                            </button>
                                            <small style="color:#e11d48;font-size:10px;font-weight:700">
                                                <i class="fa-solid fa-clock"></i> রিসেটের সময় শেষ
                                                @if($resetDeadline)
                                                    <br>({{ $resetDeadline->format('d M, h:i A') }})
                                                @endif
                                            </small>
                                        @endif
                                    @endif
                                    <small style="color:#94a3b8;font-size:10px">নির্ধারিত সময় অতিবাহিত</small>
                                </div>
                            @else
                                {{-- Active Exam: Within Start and End Window --}}
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                    @if($isInProgress)
                                        <a href="{{ route('student.exams.take', $ex) }}" class="btn btn-warning btn-sm" style="background:#f59e0b;color:#fff;font-weight:700;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 4px rgba(245,158,11,0.3)">
                                            <i class="fa-solid fa-play"></i> চালিয়ে যান
                                        </a>
                                        <small style="color:#b45309;font-size:11px;font-weight:600">পরীক্ষা চলমান</small>
                                    @else
                                        <a href="{{ route('student.exams.take', $ex) }}" class="btn btn-primary btn-sm" style="font-weight:700;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 6px rgba(99,102,241,0.3)">
                                            <i class="fa-solid fa-pen-to-square"></i> Start Exam
                                        </a>
                                        <small style="color:#16a34a;font-size:11px;font-weight:600"><i class="fa-solid fa-circle" style="font-size:7px"></i> উন্মুক্ত আছে</small>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">
                            No scheduled online exams found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdownContainers = document.querySelectorAll('.exam-countdown-container');

        function updateCountdowns() {
            const now = new Date().getTime();

            countdownContainers.forEach(container => {
                const startIso = container.getAttribute('data-start');
                const examUrl = container.getAttribute('data-exam-url');
                if (!startIso) return;

                const targetTime = new Date(startIso).getTime();
                const diff = targetTime - now;

                const displayEl = container.querySelector('.countdown-display');

                if (diff <= 0) {
                    // Time has arrived! Automatically unlock into Start Exam button
                    container.innerHTML = `
                        <a href="${examUrl}" class="btn btn-primary btn-sm" style="font-weight:700;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 6px rgba(99,102,241,0.3)">
                            <i class="fa-solid fa-pen-to-square"></i> Start Exam
                        </a>
                        <small style="color:#16a34a;font-size:11px;font-weight:600"><i class="fa-solid fa-circle" style="font-size:7px"></i> উন্মুক্ত হয়েছে</small>
                    `;
                } else {
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    let timeStr = '';
                    if (days > 0) {
                        timeStr += days + 'd ';
                    }
                    timeStr += String(hours).padStart(2, '0') + 'h ' +
                               String(minutes).padStart(2, '0') + 'm ' +
                               String(seconds).padStart(2, '0') + 's';

                    if (displayEl) {
                        displayEl.textContent = timeStr;
                    }
                }
            });
        }

        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    });

    function openIndexAppealModal(examId, examTitle, subjectName) {
        document.getElementById('indexAppealForm').action = "{{ url('student/exams') }}/" + examId + "/appeal";
        document.getElementById('indexModalExamTitle').textContent = examTitle;
        document.getElementById('indexModalSubject').textContent = subjectName;
        document.getElementById('indexAppealModal').style.display = 'flex';
    }

    function closeIndexAppealModal() {
        document.getElementById('indexAppealModal').style.display = 'none';
    }
    </script>

    {{-- Reset Modal for Expired Exams --}}
    <div id="indexAppealModal" class="modal-overlay" onclick="if(event.target===this) closeIndexAppealModal()" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.65);z-index:9999;backdrop-filter:blur(3px);font-family:'Kalpurush',sans-serif">
        <div class="modal" style="background:#fff;border-radius:14px;max-width:480px;width:100%;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden">
            <div style="background:linear-gradient(135deg, #4338ca 0%, #6366f1 100%);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:16px;font-weight:700;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-rotate-left"></i> এক্সাম রিসেটের আবেদন (Exam Reset Request)
                </div>
                <button type="button" onclick="closeIndexAppealModal()" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;line-height:1">&times;</button>
            </div>
            <form id="indexAppealForm" method="POST" action="" style="padding:20px">
                @csrf
                <div style="margin-bottom:14px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;line-height:1.6">
                    <div><strong>পরীক্ষা:</strong> <span id="indexModalExamTitle">—</span></div>
                    <div><strong>বিষয়:</strong> <span id="indexModalSubject">—</span></div>
                </div>

                <div style="background:#fffbeb;border:1px solid #fde68a;padding:10px 14px;border-radius:8px;font-size:12px;color:#92400e;margin-bottom:14px;line-height:1.5">
                    <i class="fa-solid fa-circle-info"></i>
                    এই পরীক্ষার নির্ধারিত সময় ইতিমধ্যে শেষ হয়েছে। অনিবার্য কারণে পরীক্ষায় অংশ নিতে না পারলে বা রিসেটের প্রয়োজন হলে আবেদন করুন। কর্তৃপক্ষ অনুমোদন করলে পরীক্ষা পুনরায় দেওয়ার সুযোগ দেওয়া হবে।
                </div>

                <div style="margin-bottom:18px">
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        রিসেটের সুনির্দিষ্ট কারণ লিখুন <span style="color:#ef4444">*</span>
                    </label>
                    <textarea name="reason"
                              rows="3"
                              class="form-control"
                              required
                              placeholder="অনিবার্য কারণ বিস্তারিত লিখুন (যেমন: অসুস্থতা, বিদ্যুৎ/ইন্টারনেট বিপর্যয় ইত্যাদি)..."
                              style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px"></textarea>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" class="btn btn-outline" onclick="closeIndexAppealModal()" style="font-size:13px">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#4f46e5;border-color:#4338ca;color:#fff;font-weight:700;font-size:13px">
                        <i class="fa-solid fa-paper-plane"></i> রিসেটের আবেদন জমা দিন
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-student-layout>
