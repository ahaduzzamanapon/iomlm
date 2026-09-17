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
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                                    <a href="{{ route('student.exams.take', $ex) }}" class="btn btn-success btn-sm" style="background:#059669;border-color:#047857;color:#fff;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 6px rgba(5,150,105,0.3)">
                                        <i class="fa-solid fa-rotate-right"></i> পুনরায় পরীক্ষা দিন (Re-Exam)
                                    </a>
                                    <small style="color:#059669;font-size:11px;font-weight:700">
                                        <i class="fa-solid fa-check-circle"></i> আপিল অনুমোদিত
                                    </small>
                                </div>
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
                                    <span class="badge badge-danger no-dot" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:6px 12px;font-size:12px;display:inline-flex;align-items:center;gap:5px;font-weight:700">
                                        <i class="fa-solid fa-clock-rotate-left"></i> সময় সমাপ্ত (Expired)
                                    </span>
                                    <small style="color:#94a3b8;font-size:11px">নির্ধারিত সময় অতিবাহিত</small>
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
    </script>
</x-student-layout>
