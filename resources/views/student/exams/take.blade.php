<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam — {{ $exam->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body);"></script>

    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 0; user-select: none; -webkit-user-select: none; }
        .exam-bar { position: fixed; top: 0; left: 0; right: 0; height: 64px; background: #0f172a; color: #fff; display: flex; justify-content: space-between; align-items: center; padding: 0 24px; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .exam-title { font-weight: 700; font-size: 16px; display: flex; align-items: center; gap: 10px; }
        .exam-timer { background: #e11d48; color: #fff; padding: 6px 16px; border-radius: 20px; font-weight: 800; font-size: 16px; letter-spacing: 1px; }

        .exam-container { max-width: 800px; margin: 90px auto 40px; padding: 0 20px; }
        .q-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .q-num { font-size: 12px; font-weight: 700; color: #6366f1; text-transform: uppercase; margin-bottom: 6px; }
        .q-text { font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 16px; line-height: 1.5; }

        .options-list { display: flex; flex-direction: column; gap: 10px; }
        .opt-label { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; transition: all 0.2s; font-size: 14px; background: #f8fafc; }
        .opt-label:hover { border-color: #6366f1; background: #f5f3ff; }
        .opt-label input[type="radio"]:checked + span { font-weight: 700; color: #4f46e5; }

        .btn-submit { background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 14px 28px; font-weight: 700; font-size: 16px; cursor: pointer; width: 100%; margin-top: 20px; }
        .btn-submit:hover { background: #059669; }

        .warning-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; display: none; justify-content: center; align-items: center; }
        .warning-box { background: #fff; padding: 30px; border-radius: 16px; text-align: center; max-width: 450px; border-top: 6px solid #e11d48; }
    </style>
</head>
<body oncontextmenu="return false;" oncopy="return false;" oncut="return false;" onpaste="return false;">

    {{-- Fixed Header Bar --}}
    <div class="exam-bar">
        <div class="exam-title">
            ✍️ {{ $exam->title }} ({{ $exam->subject?->code }})
            @if($exam->is_anti_cheating)
                <span style="font-size:11px;background:#334155;color:#f8fafc;padding:3px 8px;border-radius:4px">🔒 Anti-Cheating ON</span>
            @endif
        </div>
        <div class="exam-timer" id="timer">--:--</div>
    </div>

    {{-- Main Exam Body --}}
    <div class="exam-container">
        <form id="examForm" method="POST" action="{{ route('student.exams.submit', $exam) }}">
            @csrf
            <input type="hidden" name="tab_switch_count" id="tab_switch_count" value="0">
            <input type="hidden" name="is_violation" id="is_violation" value="0">

            @foreach($exam->examQuestions as $i => $eq)
            @php $q = $eq->question; @endphp
            <div class="q-card">
                <div class="q-num">Question {{ $i + 1 }} of {{ $exam->examQuestions->count() }} ({{ $eq->marks }} Marks)</div>
                <div class="q-text">{!! e($q->question_text) !!}</div>

                <div class="options-list">
                    @foreach($q->options ?? [] as $opt)
                    @php $optId = strtolower($opt['id'] ?? ''); @endphp
                    <label class="opt-label">
                        <input type="radio" name="answers[{{ $q->id }}]" value="{{ $optId }}">
                        <span><strong>{{ strtoupper($optId) }}.</strong> {{ $opt['text'] ?? '' }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach

            <button type="submit" class="btn-submit" onclick="return confirm('Are you sure you want to final submit your answers?')">
                ✅ Submit Exam Answers
            </button>
        </form>
    </div>

    {{-- Anti-Cheating Warning Modal --}}
    <div class="warning-overlay" id="warningModal">
        <div class="warning-box">
            <div style="font-size:48px;margin-bottom:10px">⚠️</div>
            <h2 style="color:#e11d48;margin:0 0 10px">Anti-Cheating Violation Warning!</h2>
            <p style="font-size:14px;color:#475569;margin-bottom:20px">
                Switching tabs, leaving full-screen, or opening other windows is strictly prohibited during the exam.
                <br>Warning <strong id="warnCount" style="color:#e11d48">1</strong> of 3. (Exam will auto-submit on 3rd violation).
            </p>
            <button onclick="dismissWarning()" style="background:#0f172a;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-weight:700;cursor:pointer">
                I Understand, Resume Exam
            </button>
        </div>
    </div>

    <script>
    // ── Timer Logic ──────────────────────────────────────────────────────────
    let durationSeconds = {{ ($exam->duration_minutes ?? 30) * 60 }};
    const timerDisplay = document.getElementById('timer');

    const countdown = setInterval(() => {
        const mins = Math.floor(durationSeconds / 60);
        const secs = durationSeconds % 60;

        timerDisplay.innerText = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;

        if (durationSeconds <= 0) {
            clearInterval(countdown);
            alert('Time is UP! Your exam answers are being auto-submitted.');
            document.getElementById('examForm').submit();
        }
        durationSeconds--;
    }, 1000);

    // ── Anti-Cheating Protection Logic ─────────────────────────────────────────
    let tabSwitches = 0;
    const isAntiCheating = {{ $exam->is_anti_cheating ? 'true' : 'false' }};

    if (isAntiCheating) {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                handleViolation();
            }
        });

        window.addEventListener('blur', () => {
            handleViolation();
        });
    }

    function handleViolation() {
        tabSwitches++;
        document.getElementById('tab_switch_count').value = tabSwitches;

        if (tabSwitches >= 3) {
            document.getElementById('is_violation').value = '1';
            alert('CRITICAL VIOLATION: You have switched tabs/windows 3 times. Your exam is being automatically submitted now.');
            document.getElementById('examForm').submit();
        } else {
            document.getElementById('warnCount').innerText = tabSwitches;
            document.getElementById('warningModal').style.display = 'flex';
        }
    }

    function dismissWarning() {
        document.getElementById('warningModal').style.display = 'none';
    }
    </script>
</body>
</html>
