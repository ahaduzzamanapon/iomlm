<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পরীক্ষা — {{ $exam->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body);"></script>

    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, 'Noto Sans Bengali', sans-serif; background: #f1f5f9; color: #0f172a; margin: 0; padding: 0; }

        /* Top Fixed Bar */
        .exam-bar {
            position: fixed; top: 0; left: 0; right: 0; height: 60px;
            background: #0f172a; color: #fff;
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 24px; z-index: 1000; box-shadow: 0 2px 12px rgba(0,0,0,.3);
        }
        .exam-bar-left  { display: flex; flex-direction: column; gap: 2px; }
        .exam-bar-title { font-weight: 700; font-size: 15px; }
        .exam-bar-sub   { font-size: 11px; color: #94a3b8; }
        .exam-bar-right { display: flex; align-items: center; gap: 14px; }

        .timer-pill {
            background: #1e293b; border: 2px solid #475569;
            color: #f8fafc; padding: 6px 16px; border-radius: 20px;
            font-weight: 800; font-size: 15px; letter-spacing: 1px;
            font-variant-numeric: tabular-nums;
            transition: background .3s;
        }
        .timer-pill.urgent { background: #e11d48; border-color: #be123c; }

        /* Question Paper */
        .paper-wrap { max-width: 860px; margin: 80px auto 60px; padding: 0 16px; }

        /* Paper Header */
        .paper-header {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 20px 24px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
        }
        .paper-meta span { font-size: 12px; color: #64748b; display: block; }
        .paper-meta strong { font-size: 14px; color: #0f172a; }
        .stat-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600;
        }

        /* Section Labels */
        .section-label {
            font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            color: #475569; margin: 24px 0 10px; padding-left: 4px;
            border-left: 3px solid #6366f1; padding-left: 10px;
        }
        .section-label.written-section { border-left-color: #db2777; }

        /* Question Card */
        .q-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 20px 22px; margin-bottom: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            transition: border-color .2s;
        }
        .q-card.answered { border-color: #86efac; }
        .q-card.written-card { border-left: 4px solid #db2777; }

        .q-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .q-num    { font-size: 12px; font-weight: 700; color: #fff; background: #6366f1; width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .q-num.w  { background: #db2777; }
        .q-marks  { font-size: 11px; color: #64748b; margin-left: auto; background: #f8fafc; padding: 2px 8px; border-radius: 4px; border: 1px solid #e2e8f0; }
        .q-type-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
        .q-type-mcq     { background: #e0e7ff; color: #4338ca; }
        .q-type-written { background: #fce7f3; color: #9d174d; }

        .q-text { font-size: 15px; font-weight: 600; color: #1e293b; line-height: 1.6; margin-bottom: 14px; }

        /* MCQ Options */
        .options-list { display: flex; flex-direction: column; gap: 8px; }
        .opt-label {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; border: 1.5px solid #e2e8f0;
            border-radius: 8px; cursor: pointer; transition: all .18s;
            font-size: 14px; background: #fafafa;
        }
        .opt-label:hover { border-color: #6366f1; background: #f5f3ff; }
        .opt-label input[type="radio"] { accent-color: #6366f1; width: 16px; height: 16px; cursor: pointer; }
        .opt-label input[type="radio"]:checked ~ .opt-text { font-weight: 700; color: #4f46e5; }
        .opt-circle {
            width: 28px; height: 28px; border-radius: 50%; border: 2px solid #cbd5e1;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #64748b; flex-shrink: 0;
        }

        /* Written Upload */
        .written-upload-zone {
            border: 2px dashed #f9a8d4; border-radius: 10px;
            padding: 20px; text-align: center; background: #fdf2f8;
            cursor: pointer; transition: all .2s; position: relative;
        }
        .written-upload-zone:hover { border-color: #db2777; background: #fce7f3; }
        .written-upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
        .upload-preview { margin-top: 12px; display: none; }
        .upload-preview img { max-height: 200px; border-radius: 8px; border: 1px solid #f9a8d4; }

        /* Progress Tracker */
        .progress-bar-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; }
        .progress-bar-track { background: #f1f5f9; border-radius: 4px; height: 6px; margin-top: 8px; }
        .progress-bar-fill  { background: #10b981; border-radius: 4px; height: 6px; transition: width .3s; }

        /* Submit Button */
        .submit-zone { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: center; margin-top: 24px; }
        .btn-submit {
            background: #10b981; color: #fff; border: none; border-radius: 10px;
            padding: 14px 40px; font-weight: 700; font-size: 16px; cursor: pointer;
            transition: background .2s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-submit:hover { background: #059669; }

        /* Anti-Cheat Warning */
        .warning-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.75); z-index: 9999; display: none; justify-content: center; align-items: center; }
        .warning-box { background: #fff; padding: 32px; border-radius: 16px; text-align: center; max-width: 440px; border-top: 6px solid #e11d48; }
    </style>
</head>
<body>

    {{-- Fixed Top Bar --}}
    <div class="exam-bar">
        <div class="exam-bar-left">
            <div class="exam-bar-title">{{ $exam->title }}</div>
            <div class="exam-bar-sub">{{ $exam->subject?->name }} ({{ $exam->subject?->code }}) &middot; {{ $exam->type }}</div>
        </div>
        <div class="exam-bar-right">
            @if($exam->is_anti_cheating)
                <span style="font-size:11px;background:#334155;color:#cbd5e1;padding:4px 10px;border-radius:6px">Anti-Cheating</span>
            @endif
            <div class="timer-pill" id="timer">{{ $exam->duration_minutes }}:00</div>
        </div>
    </div>

    {{-- Paper Content --}}
    <div class="paper-wrap">

        {{-- Paper Header Card --}}
        <div class="paper-header">
            <div class="paper-meta">
                <span>পরীক্ষার শিরোনাম</span>
                <strong>{{ $exam->title }}</strong>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <div class="stat-pill">{{ $exam->examQuestions->count() }} প্রশ্ন</div>
                <div class="stat-pill">{{ $exam->duration_minutes }} মিনিট</div>
                <div class="stat-pill">{{ $exam->full_marks }} নম্বর</div>
                @php $writtenCount = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'WRITTEN')->count(); @endphp
                @if($writtenCount > 0)
                    <div class="stat-pill" style="background:#fdf2f8;border-color:#f9a8d4;color:#9d174d">{{ $writtenCount }} Written</div>
                @endif
            </div>
        </div>

        {{-- Progress Tracker --}}
        @php $totalQ = $exam->examQuestions->count(); @endphp
        <div class="progress-bar-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;font-weight:600">
                <span>উত্তর করা হয়েছে: <span id="answeredCount">0</span> / {{ $totalQ }}</span>
                <span style="color:#64748b;font-size:12px">সব প্রশ্ন এক পেজে দেখানো হচ্ছে</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
            </div>
        </div>

        {{-- The Exam Form --}}
        <form id="examForm" method="POST" action="{{ route('student.exams.submit', $exam) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tab_switch_count" id="tab_switch_count" value="0">
            <input type="hidden" name="is_violation" id="is_violation" value="0">

            @php
                $mcqQuestions     = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'MCQ');
                $writtenQuestions = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'WRITTEN');
                $qSerial = 0;
            @endphp

            {{-- MCQ Section --}}
            @if($mcqQuestions->count() > 0)
                <div class="section-label">বহুনির্বাচনী প্রশ্ন (MCQ) — {{ $mcqQuestions->count() }}টি</div>

                @foreach($mcqQuestions as $eq)
                @php $q = $eq->question; $qSerial++; $savedAns = $savedAnswers[$q->id] ?? null; @endphp
                <div class="q-card" id="qcard-{{ $q->id }}" data-type="mcq">
                    <div class="q-header">
                        <span class="q-num">{{ $qSerial }}</span>
                        <span class="q-type-badge q-type-mcq">MCQ</span>
                        <span class="q-marks">{{ $eq->marks }} নম্বর</span>
                    </div>
                    <div class="q-text">{!! e($q->question_text) !!}</div>
                    <div class="options-list">
                        @foreach($q->options ?? [] as $opt)
                        @php $optId = strtolower($opt['id'] ?? ''); @endphp
                        <label class="opt-label" onclick="markAnswered('{{ $q->id }}')">
                            <input type="radio" name="answers[{{ $q->id }}]" value="{{ $optId }}"
                                   {{ $savedAns?->selected_option_id === $optId ? 'checked' : '' }}>
                            <span class="opt-circle">{{ strtoupper($optId) }}</span>
                            <span class="opt-text">{{ $opt['text'] ?? '' }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            @endif

            {{-- Written Section --}}
            @if($writtenQuestions->count() > 0)
                <div class="section-label written-section">রচনামূলক প্রশ্ন (Written) — {{ $writtenQuestions->count() }}টি</div>
                <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:8px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#9d174d">
                    <strong>নির্দেশনা:</strong> নিচের প্রশ্নগুলোর উত্তর কাগজে লিখুন এবং ছবি তুলে প্রতিটি প্রশ্নের নিচে Upload করুন।
                </div>

                @foreach($writtenQuestions as $eq)
                @php $q = $eq->question; $qSerial++; $savedAns = $savedAnswers[$q->id] ?? null; @endphp
                <div class="q-card written-card" id="qcard-{{ $q->id }}" data-type="written">
                    <div class="q-header">
                        <span class="q-num w">{{ $qSerial }}</span>
                        <span class="q-type-badge q-type-written">Written</span>
                        <span class="q-marks">{{ $eq->marks }} নম্বর</span>
                    </div>
                    <div class="q-text">{!! e($q->question_text) !!}</div>

                    <div class="written-upload-zone" id="zone-{{ $q->id }}"
                         onclick="isPickingFile=true; document.getElementById('file-{{ $q->id }}').click()">
                        <input type="file" id="file-{{ $q->id }}"
                               name="answer_image_{{ $q->id }}"
                               accept="image/*"
                               onchange="previewImage({{ $q->id }}, this)"
                               style="display:none">
                        <div id="zone-placeholder-{{ $q->id }}">
                            <div style="font-size:28px;margin-bottom:6px"></div>
                            <div style="font-weight:600;font-size:14px;color:#9d174d">উত্তরের ছবি Upload করুন</div>
                            <div style="font-size:12px;color:#be185d;margin-top:4px">Click করুন বা ছবি Drag করুন</div>
                        </div>
                        <div class="upload-preview" id="preview-{{ $q->id }}">
                            <img id="preview-img-{{ $q->id }}" src="" alt="Answer">
                            <div style="font-size:12px;color:#9d174d;margin-top:6px" id="preview-name-{{ $q->id }}"></div>
                        </div>
                    </div>

                    @if($savedAns?->answer_image_path)
                        @php
                            $savedImgUrl = asset('storage/' . ltrim(str_replace('public/', '', $savedAns->answer_image_path), '/'));
                        @endphp
                        <div style="margin-top:10px;font-size:12px;color:#10b981;font-weight:600">
                            আগে Upload করা ছবি আছে (<a href="{{ $savedImgUrl }}" target="_blank" style="color:#10b981;text-decoration:underline">ছবিটি দেখুন ↗</a>)। নতুন ছবি Upload করলে replace হবে।
                        </div>
                    @endif
                </div>
                @endforeach
            @endif

            {{-- Submit Zone --}}
            <div class="submit-zone">
                <div style="font-size:14px;color:#64748b;margin-bottom:16px">
                    সব উত্তর দেওয়া হয়ে গেলে নিচের বাটনে ক্লিক করুন।<br>
                    <strong>একবার Submit করলে আর পরিবর্তন করা যাবে না।</strong>
                </div>
                <button type="button" class="btn-submit" onclick="confirmSubmit()">
                    প্রশ্নপত্র জমা দিন (Submit Paper)
                </button>
            </div>
        </form>
    </div>

    {{-- Anti-Cheat Warning Modal --}}
    <div class="warning-overlay" id="warningModal">
        <div class="warning-box">
            <div style="font-size:48px;margin-bottom:10px"></div>
            <h2 style="color:#e11d48;margin:0 0 10px">সতর্কতা!</h2>
            <p style="font-size:14px;color:#475569;margin-bottom:20px">
                পরীক্ষা চলাকালীন অন্য tab বা window-এ যাওয়া নিষিদ্ধ।<br>
                সতর্কতা <strong id="warnCount" style="color:#e11d48">1</strong>/3
            </p>
            <button onclick="dismissWarning()" style="background:#0f172a;color:#fff;border:none;padding:10px 24px;border-radius:6px;font-weight:700;cursor:pointer">
                বুঝলাম, পরীক্ষায় ফিরে যাই
            </button>
        </div>
    </div>

    <script>
    // ── Timer (display only — does NOT force submit) ──────────────────────────
    let durationSeconds = {{ ($exam->duration_minutes ?? 30) * 60 }};
    const timerEl = document.getElementById('timer');

    const countdown = setInterval(() => {
        const mins = Math.floor(durationSeconds / 60);
        const secs = durationSeconds % 60;
        const display = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        timerEl.innerText = display;

        if (durationSeconds <= 300) {  // Last 5 minutes: urgent color
            timerEl.classList.add('urgent');
        }
        if (durationSeconds <= 0) {
            clearInterval(countdown);
            timerEl.innerText = '00:00';
            // Timer expired but we don't force-submit; just show reminder
            timerEl.innerText = 'সময় শেষ';
        }
        durationSeconds--;
    }, 1000);

    // ── Progress Tracker ──────────────────────────────────────────────────────
    const renderedCards = document.querySelectorAll('.q-card');
    const totalQ = renderedCards.length > 0 ? renderedCards.length : {{ $totalQ }};
    const answeredSet = new Set();

    // Pre-populate answered set from saved answers (both MCQ and Written)
    @foreach($savedAnswers as $qId => $ans)
        @if($ans->selected_option_id || $ans->answer_image_path || $ans->answer_text)
            answeredSet.add({{ $qId }});
            const c{{ $qId }} = document.getElementById('qcard-{{ $qId }}');
            if (c{{ $qId }}) c{{ $qId }}.classList.add('answered');
        @endif
    @endforeach

    // Check rendered inputs in DOM to ensure no answered question is missed
    renderedCards.forEach(card => {
        const hasChecked = card.querySelector('input[type="radio"]:checked');
        const hasExistingImg = card.querySelector('a[href*="storage"]') || card.querySelector('.upload-preview[style*="display: block"]');
        if (hasChecked || hasExistingImg) {
            const qid = parseInt(card.id.replace('qcard-', ''));
            if (!isNaN(qid)) {
                answeredSet.add(qid);
                card.classList.add('answered');
            }
        }
    });

    function markAnswered(qId) {
        answeredSet.add(parseInt(qId));
        const card = document.getElementById('qcard-' + qId);
        if (card) card.classList.add('answered');
        updateProgress();
    }

    function updateProgress() {
        const count = Math.min(answeredSet.size, totalQ);
        document.getElementById('answeredCount').innerText = count;
        document.getElementById('progressFill').style.width = (totalQ > 0 ? (count / totalQ * 100) : 0) + '%';
    }
    updateProgress();

    // Written image upload preview
    function previewImage(qId, input) {
        isPickingFile = false;
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-img-' + qId).src = e.target.result;
            document.getElementById('preview-name-' + qId).innerText = '' + file.name;
            document.getElementById('zone-placeholder-' + qId).style.display = 'none';
            document.getElementById('preview-' + qId).style.display = 'block';
            // Mark written as answered too
            answeredSet.add(parseInt(qId));
            const card = document.getElementById('qcard-' + qId);
            if (card) card.classList.add('answered');
            updateProgress();
        };
        reader.readAsDataURL(file);
    }

    function confirmSubmit() {
        const answeredCount = Math.min(answeredSet.size, totalQ);
        const unanswered = Math.max(0, totalQ - answeredCount);
        let msg = 'প্রশ্নপত্র জমা দিতে চান?';
        if (unanswered > 0) {
            msg = unanswered + 'টি প্রশ্নের উত্তর এখনো দেওয়া হয়নি।\n' + msg;
        }
        if (confirm(msg)) {
            document.getElementById('examForm').submit();
        }
    }

    // ── Anti-Cheating with File Picker Protection ─────────────────────────────
    let tabSwitches = 0;
    const isAntiCheating = {{ $exam->is_anti_cheating ? 'true' : 'false' }};
    let isPickingFile = false;

    // Detect when student opens file chooser dialog
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('click', () => {
            isPickingFile = true;
        });
    });

    window.addEventListener('focus', () => {
        setTimeout(() => { isPickingFile = false; }, 1500);
    });

    if (isAntiCheating) {
        document.addEventListener('visibilitychange', () => {
            if (isPickingFile) return;
            if (document.hidden) handleViolation();
        });
        window.addEventListener('blur', () => {
            if (isPickingFile) return;
            handleViolation();
        });
    }

    function handleViolation() {
        if (isPickingFile) return;
        tabSwitches++;
        document.getElementById('tab_switch_count').value = tabSwitches;
        if (tabSwitches >= 3) {
            document.getElementById('is_violation').value = '1';
            alert('গুরুতর লঙ্ঘন: তিনবার Tab/Window পরিবর্তন হয়েছে। স্বয়ংক্রিয়ভাবে Submit হচ্ছে।');
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
