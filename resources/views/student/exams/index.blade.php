<x-student-layout>
    <x-slot name="title">Online Exams</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Online Examinations</h1>
            <p>Class Quizzes, Class Tests, Half-Term, and Final Examinations</p>
        </div>
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
                        $isSubmitted = $submission && in_array($submission->status, ['SUBMITTED', 'AUTO_SUBMITTED_VIOLATION']);
                        $isInProgress = $submission && $submission->status === 'IN_PROGRESS';
                        $latestAppeal = $ex->appeals->sortByDesc('id')->first();
                        $hasApprovedAppeal = $latestAppeal && $latestAppeal->isApproved();
                        $timingStatus = $ex->timing_status; // 'UPCOMING', 'ACTIVE', 'EXPIRED'
                        $startDt = $ex->getEffectiveStartDatetime();
                        $endDt = $ex->getEffectiveEndDatetime();

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
                                {{-- Expired Exam: Cannot start after end date/time unless appeal approved --}}
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                    <span class="badge badge-danger no-dot" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:5px 10px;font-size:11.5px;display:inline-flex;align-items:center;gap:5px;font-weight:700">
                                        <i class="fa-solid fa-clock-rotate-left"></i> সময় সমাপ্ত (Expired)
                                    </span>
                                    @if($latestAppeal && $latestAppeal->isPending())
                                        <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:11px;padding:3px 8px;font-weight:700">
                                            <i class="fa-regular fa-clock"></i> আপিল পর্যালোচনায়
                                        </span>
                                    @elseif($latestAppeal && $latestAppeal->requiresPayment())
                                        <span style="font-size:11.5px;font-weight:800;color:#e11d48">
                                            বকেয়া ফি: ৳{{ number_format($latestAppeal->fee_amount, 0) }}
                                        </span>
                                        <a href="{{ route('student.fees.index') }}" class="btn btn-sm" style="background:#0284c7;color:#fff;font-weight:700;padding:4px 10px;border-radius:6px;font-size:11.5px;display:inline-flex;align-items:center;gap:4px">
                                            <i class="fa-solid fa-credit-card"></i> ফি দিন
                                        </a>
                                    @elseif(!$hasApprovedAppeal)
                                        <button type="button"
                                                class="btn btn-outline btn-sm"
                                                onclick="openIndexAppealModal({{ $ex->id }}, '{{ addslashes($ex->title) }}', '{{ addslashes($ex->subject?->name ?? '—') }}')"
                                                style="border-color:#6366f1;color:#6366f1;font-weight:700;font-size:11px;padding:4px 10px;display:inline-flex;align-items:center;gap:4px">
                                            <i class="fa-solid fa-file-signature"></i> আপিল করুন
                                        </button>
                                    @endif
                                    <small style="color:#94a3b8;font-size:10.5px">নির্ধারিত সময় অতিবাহিত</small>
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

    {{-- Appeal Modal for Expired Exams --}}
    <div id="indexAppealModal" class="modal-overlay" onclick="if(event.target===this) closeIndexAppealModal()" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.65);z-index:9999;backdrop-filter:blur(3px);font-family:'Kalpurush',sans-serif">
        <div class="modal" style="background:#fff;border-radius:14px;max-width:480px;width:100%;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden">
            <div style="background:linear-gradient(135deg, #4338ca 0%, #6366f1 100%);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:16px;font-weight:700;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-file-signature"></i> পুনরায় পরীক্ষার আবেদন (Appeal to Re-Exam)
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
                    এই পরীক্ষার নির্ধারিত সময় ইতিমধ্যে শেষ হয়েছে। অনিবার্য কারণে পরীক্ষায় অংশ নিতে না পারলে আবেদন করুন। আবেদন গৃহীত হলে নির্ধারিত ফি (যদি প্রযোজ্য হয়) পরিশোধের মাধ্যমে পরীক্ষা দেওয়া যাবে।
                </div>

                <div style="margin-bottom:18px">
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        আবেদনের সুনির্দিষ্ট কারণ লিখুন <span style="color:#ef4444">*</span>
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
                        <i class="fa-solid fa-paper-plane"></i> আবেদন জমা দিন
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-student-layout>
