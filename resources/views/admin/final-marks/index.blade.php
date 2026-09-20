<x-admin-layout>
    <x-slot name="title">Final Mark Generator (ফাইনাল মার্ক জেনারেটর)</x-slot>

    <style>
        .fm-page-wrapper {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .fm-header {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 60%, #047857 100%);
            border-radius: 16px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.25);
        }
        .criteria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .criteria-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: transform .15s, box-shadow .15s;
        }
        .criteria-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }
        .criteria-card .label  { font-size: 13px; color: #64748b; font-weight: 700; margin-bottom: 6px; }
        .criteria-card .marks  { font-size: 24px; font-weight: 800; color: #1e293b; }
        .criteria-card .arrow  { font-size: 11px; color: #94a3b8; margin: 3px 0; }
        .criteria-card .convert{ font-size: 15px; font-weight: 800; color: #047857; }
        .grade-badge {
            display: inline-block;
            padding: 4px 11px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 12px;
        }
        .grade-ap  { background:#dcfce7; color:#16a34a; }
        .grade-a   { background:#d1fae5; color:#059669; }
        .grade-am  { background:#e0f2fe; color:#0284c7; }
        .grade-b   { background:#fef9c3; color:#ca8a04; }
        .grade-c   { background:#ffedd5; color:#ea580c; }
        .grade-f   { background:#fee2e2; color:#dc2626; }
        .status-pass { background:#dcfce7; color:#16a34a; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:800; }
        .status-fail { background:#fee2e2; color:#dc2626; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:800; }
        .status-inc  { background:#f1f5f9; color:#64748b; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:800; }

        /* Modal Backdrop and Box */
        .modal-overlay {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            z-index: 99999 !important;
            display: none;
            align-items: center !important;
            justify-content: center !important;
            padding: 16px !important;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .modal-overlay.open,
        .modal-overlay.is-active,
        .modal-overlay.show {
            display: flex !important;
            opacity: 1 !important;
            pointer-events: all !important;
        }
        .modal-card {
            background: #ffffff;
            border-radius: 16px;
            max-width: 580px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn 0.2s ease-out;
            position: relative;
            z-index: 100000;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <div class="fm-page-wrapper">

        {{-- Header --}}
        <div class="fm-header">
            <div>
                <h1 style="margin:0 0 6px; font-size:23px; font-weight:800; display:flex; align-items:center; gap:10px">
                    <i class="fa-solid fa-graduation-cap"></i> ফাইনাল মার্ক জেনারেটর (Final Mark Generator)
                </h1>
                <p style="margin:0; font-size:13.5px; color:#d1fae5">
                    ক্লাস টেস্ট + মিডটার্ম + ফাইনাল পরীক্ষা + উপস্থিতি মার্কস কনভার্সন কাঠামো ও ফলাফল
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap">
                <button type="button" id="btnOpenCriteriaModal" onclick="openCriteriaModal(event)"
                   style="background:#ffffff; color:#065f46; border:none; padding:10px 18px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:7px; box-shadow:0 4px 10px rgba(0,0,0,0.1)">
                    <i class="fa-solid fa-sliders"></i> কনভার্সন ক্রাইটেরিয়া পরিবর্তন
                </button>
                @if(request('batch_id') && request('subject_id') && $finalMarks->isNotEmpty())
                    <a href="{{ route('admin.final-marks.export-csv', ['batch_id' => request('batch_id'), 'subject_id' => request('subject_id'), 'semester_id' => request('semester_id', $selectedSemesterId ?? '')]) }}"
                       style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:#fff; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:7px">
                        <i class="fa-solid fa-file-csv"></i> Export CSV
                    </a>
                @endif
            </div>
        </div>

        {{-- IOM Conversion Criteria Display Cards --}}
        @php
            $totalFull = $criteria['class_test_full'] + $criteria['midterm_full'] + $criteria['final_full'];
            $totalConvert = $criteria['class_test_convert'] + $criteria['midterm_convert'] + $criteria['final_convert'] + $criteria['attendance_convert'];
        @endphp
        <div class="criteria-grid">
            <div class="criteria-card">
                <div class="label">ক্লাস টেস্ট (Class Test)</div>
                <div class="marks">{{ $criteria['class_test_full'] }}</div>
                <div class="arrow">↓ রূপান্তর হবে</div>
                <div class="convert">{{ $criteria['class_test_convert'] }} নম্বরে</div>
            </div>
            <div class="criteria-card">
                <div class="label">মিডটার্ম (Mid Term)</div>
                <div class="marks">{{ $criteria['midterm_full'] }}</div>
                <div class="arrow">↓ রূপান্তর হবে</div>
                <div class="convert">{{ $criteria['midterm_convert'] }} নম্বরে</div>
            </div>
            <div class="criteria-card">
                <div class="label">ফাইনাল পরীক্ষা (Final Term)</div>
                <div class="marks">{{ $criteria['final_full'] }}</div>
                <div class="arrow">↓ রূপান্তর হবে</div>
                <div class="convert">{{ $criteria['final_convert'] }} নম্বরে</div>
            </div>
            <div class="criteria-card">
                <div class="label">উপস্থিতি (Attendance)</div>
                <div class="marks">১০০%</div>
                <div class="arrow">↓ রূপান্তর হবে</div>
                <div class="convert">{{ $criteria['attendance_convert'] }} নম্বরে</div>
            </div>
            <div class="criteria-card" style="border-color:#059669; background:#ecfdf5">
                <div class="label" style="color:#065f46">মোট ফলাফল (TOTAL)</div>
                <div class="marks" style="color:#065f46">{{ $totalFull }} + %</div>
                <div class="arrow" style="color:#059669">→ চূড়ান্ত রূপান্তর</div>
                <div class="convert" style="font-size:20px; color:#065f46">{{ $totalConvert }} নম্বরে</div>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:20px; border-radius:12px; font-weight:700">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom:20px; border-radius:12px; font-weight:700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter & Generate Action Area --}}
        <div class="card" style="margin-bottom:24px; padding:22px 24px; border-radius:16px; background:#fff; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.03)">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px">
                
                {{-- GET Filter Form (Never contains nested forms) --}}
                <form method="GET" action="{{ route('admin.final-marks.index') }}" id="filterForm" style="display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end; flex:1; min-width:320px">
                    <div style="flex:1; min-width:210px">
                        <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                            ১. ব্যাচ নির্বাচন করুন (Select Batch) *
                        </label>
                        <select name="batch_id" id="batchSelect" class="form-control" style="height:44px; border-radius:10px" required onchange="handleBatchChange(this.value)">
                            <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                    {{ $batch->name }} ({{ $batch->course->title ?? $batch->course->name ?? 'কোর্স' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester Selection (Hidden if SUBJECT_BASED, Shown if SEMESTER_BASED) --}}
                    <div id="semesterSelectContainer" style="flex:1; min-width:190px; display: {{ ($isSemesterBased ?? false) ? 'block' : 'none' }};">
                        <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                            ২. সেমিস্টার (Semester)
                        </label>
                        <select name="semester_id" id="semesterSelect" class="form-control" style="height:44px; border-radius:10px" onchange="handleSemesterChange(this.value)">
                            <option value="">-- সকল সেমিস্টার --</option>
                            @if(isset($courseSemesters) && $courseSemesters->isNotEmpty())
                                @foreach($courseSemesters as $sem)
                                    <option value="{{ $sem->id }}" {{ (request('semester_id', $selectedSemesterId ?? '') == $sem->id) ? 'selected' : '' }}>
                                        {{ $sem->name }} {{ ($sem->id == ($runningSemesterId ?? null)) ? '(রানিং / Running)' : '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div style="flex:1; min-width:220px">
                        <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                            <span id="subjectLabelNumber">{{ ($isSemesterBased ?? false) ? '৩' : '২' }}</span>. বিষয় নির্বাচন করুন (Select Subject) *
                        </label>
                        <select name="subject_id" id="subjectSelect" class="form-control" style="height:44px; border-radius:10px" required>
                            <option value="">-- বিষয় নির্বাচন করুন --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} {{ $subject->code ? '('.$subject->code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="height:44px; padding:0 22px; border-radius:10px; font-weight:800; font-size:13.5px; display:inline-flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-magnifying-glass"></i> ফলাফল দেখুন (View Results)
                    </button>
                </form>

                {{-- Standalone POST Generate Form --}}
                @if(request('batch_id') && request('subject_id'))
                    <form method="POST" action="{{ route('admin.final-marks.generate') }}" id="generateForm" style="margin:0">
                        @csrf
                        <input type="hidden" name="batch_id"   value="{{ request('batch_id') }}">
                        <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                        @if(request('semester_id') || !empty($selectedSemesterId))
                            <input type="hidden" name="semester_id" value="{{ request('semester_id', $selectedSemesterId ?? '') }}">
                        @endif
                        <button type="submit" style="background:linear-gradient(135deg,#059669,#10b981); color:#fff; border:none; padding:0 22px; height:44px; border-radius:10px; font-weight:800; font-size:13.5px; cursor:pointer; white-space:nowrap; box-shadow:0 4px 12px rgba(5,150,105,0.3); display:inline-flex; align-items:center; gap:8px"
                            onclick="return confirm('এই ব্যাচ ও বিষয়ের সকল সক্রিয় শিক্ষার্থীর জন্য ফাইনাল মার্ক স্বয়ংক্রিয়ভাবে জেনারেট / রি-জেনারেট করতে চান?')">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Generate / Regenerate Marks
                        </button>
                    </form>
                @endif

            </div>
        </div>

        {{-- Results Table --}}
        @if($selectedBatch && $selectedSubject)
            <div class="card" style="border-radius:16px; overflow:hidden; padding:0; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.03); background:#fff">
                <div style="padding:16px 22px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc">
                    <div>
                        <span style="font-size:13px; color:#64748b">ব্যাচ: </span>
                        <strong style="font-size:15px; color:#064e3b">{{ $selectedBatch->name }}</strong>
                        @if(($isSemesterBased ?? false) && ($selectedSemesterId ?? null))
                            @php
                                $curSemName = $courseSemesters->firstWhere('id', $selectedSemesterId)?->name ?? 'সেমিস্টার';
                            @endphp
                            <span style="color:#cbd5e1; margin:0 6px">|</span>
                            <span style="font-size:13px; color:#64748b">সেমিস্টার: </span>
                            <strong style="font-size:15px; color:#065f46">{{ $curSemName }}</strong>
                        @endif
                        <span style="color:#cbd5e1; margin:0 6px">|</span>
                        <span style="font-size:13px; color:#64748b">বিষয়: </span>
                        <strong style="font-size:15px; color:#047857">{{ $selectedSubject->name }}</strong>
                    </div>
                    <span class="badge badge-secondary no-dot" style="font-size:12.5px; padding:6px 14px; border-radius:20px; font-weight:700; background:#ecfdf5; color:#047857; border:1px solid #a7f3d0">
                        মোট শিক্ষার্থী: {{ $finalMarks->count() }} জন
                    </span>
                </div>

                @if($finalMarks->isEmpty())
                    <div style="text-align:center; padding:55px 20px; color:#64748b">
                        <i class="fa-solid fa-chart-simple" style="font-size:42px; color:#cbd5e1; margin-bottom:14px; display:block"></i>
                        <strong style="font-size:16px; color:#1e293b; display:block; margin-bottom:6px">এই ব্যাচ ও বিষয়ের জন্য কোনো ফাইনাল মার্ক তৈরি করা হয়নি</strong>
                        <span style="font-size:13px; color:#64748b; margin-bottom:16px; display:inline-block">
                            উপরে উল্লেখিত সবুজ <strong>"Generate / Regenerate Marks"</strong> বাটনে ক্লিক করে ফলাফল তৈরি করুন।
                        </span>
                    </div>
                @else
                    <div style="overflow-x:auto">
                        <table style="width:100%; border-collapse:collapse">
                            <thead>
                                <tr style="background:#f1f5f9; border-bottom:2px solid #e2e8f0">
                                    <th style="padding:12px 14px; width:45px; text-align:center">#</th>
                                    <th style="padding:12px 16px; text-align:left">শিক্ষার্থী (Student)</th>
                                    <th style="padding:12px 12px; text-align:center">
                                        ক্লাস টেস্ট<br>
                                        <small style="color:#64748b;font-weight:600">/{{ $criteria['class_test_full'] }} → /{{ $criteria['class_test_convert'] }}</small>
                                    </th>
                                    <th style="padding:12px 12px; text-align:center">
                                        মিডটার্ম<br>
                                        <small style="color:#64748b;font-weight:600">/{{ $criteria['midterm_full'] }} → /{{ $criteria['midterm_convert'] }}</small>
                                    </th>
                                    <th style="padding:12px 12px; text-align:center">
                                        ফাইনাল পরীক্ষা<br>
                                        <small style="color:#64748b;font-weight:600">/{{ $criteria['final_full'] }} → /{{ $criteria['final_convert'] }}</small>
                                    </th>
                                    <th style="padding:12px 12px; text-align:center; background:#f0fdf4">
                                        উপস্থিতি মার্ক<br>
                                        <small style="color:#047857;font-weight:700">% → /{{ $criteria['attendance_convert'] }}</small>
                                    </th>
                                    <th style="padding:12px 14px; text-align:center; font-weight:800; color:#064e3b; background:#ecfdf5">
                                        মোট নম্বর<br>
                                        <small style="font-weight:700;color:#047857">/{{ $totalConvert }}</small>
                                    </th>
                                    <th style="padding:12px 12px; text-align:center">গ্রেড</th>
                                    <th style="padding:12px 12px; text-align:center">জিপিএ</th>
                                    <th style="padding:12px 12px; text-align:center">স্ট্যাটাস</th>
                                    <th style="padding:12px 12px; text-align:center; font-size:11px; color:#94a3b8">তারিখ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($finalMarks as $i => $fm)
                                <tr style="border-bottom:1px solid #f1f5f9; transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:14px; text-align:center; font-weight:700; color:#94a3b8">{{ $i + 1 }}</td>
                                    <td style="padding:14px 16px">
                                        <strong style="color:#0f172a; font-size:14px">{{ $fm->student->name ?? '—' }}</strong>
                                        <div style="font-size:12px; color:#64748b; margin-top:2px">
                                            আইডি: <span style="font-weight:700; color:#047857">{{ $fm->student->student_code ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td style="padding:14px 12px; text-align:center">
                                        @if($fm->class_test_obtained !== null)
                                            <span style="font-weight:700; color:#1e293b">{{ $fm->class_test_obtained }}</span>
                                            <span style="color:#94a3b8; font-size:11px"> → </span>
                                            <strong style="color:#047857">{{ $fm->class_test_converted }}</strong>
                                        @else
                                            <span style="color:#cbd5e1">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 12px; text-align:center">
                                        @if($fm->midterm_obtained !== null)
                                            <span style="font-weight:700; color:#1e293b">{{ $fm->midterm_obtained }}</span>
                                            <span style="color:#94a3b8; font-size:11px"> → </span>
                                            <strong style="color:#047857">{{ $fm->midterm_converted }}</strong>
                                        @else
                                            <span style="color:#cbd5e1">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 12px; text-align:center">
                                        @if($fm->final_obtained !== null)
                                            <span style="font-weight:700; color:#1e293b">{{ $fm->final_obtained }}</span>
                                            <span style="color:#94a3b8; font-size:11px"> → </span>
                                            <strong style="color:#047857">{{ $fm->final_converted }}</strong>
                                        @else
                                            <span style="color:#cbd5e1">—</span>
                                        @endif
                                    </td>

                                    {{-- Attendance Cell with Direct Edit Button --}}
                                    <td style="padding:14px 12px; text-align:center; background:#f0fdf4">
                                        <div style="display:inline-flex; align-items:center; gap:8px">
                                            <div>
                                                <span style="font-size:12px; color:#64748b">{{ $fm->attendance_percent ?? 0 }}%</span>
                                                <span style="color:#94a3b8; font-size:11px"> → </span>
                                                <strong style="color:#065f46; font-size:15px">{{ $fm->attendance_converted ?? 0 }}</strong>
                                            </div>
                                            <button type="button" 
                                                onclick="openAttendanceModal({{ $fm->id }}, '{{ addslashes($fm->student->name ?? 'Student') }}', {{ $fm->attendance_percent ?? 0 }}, {{ $fm->attendance_converted ?? 0 }}, {{ $criteria['attendance_convert'] }})"
                                                title="উপস্থিতির নম্বর পরিবর্তন করুন"
                                                style="padding:3px 8px; background:#ffffff; border:1px solid #a7f3d0; color:#047857; border-radius:6px; cursor:pointer; font-size:11px; font-weight:700; transition:all .15s"
                                                onmouseover="this.style.background='#047857'; this.style.color='#fff'"
                                                onmouseout="this.style.background='#ffffff'; this.style.color='#047857'">
                                                <i class="fa-solid fa-pen-to-square"></i> এডিট
                                            </button>
                                        </div>
                                    </td>

                                    <td style="padding:14px; text-align:center; background:#ecfdf5">
                                        <span style="font-size:18px; font-weight:900; color:{{ $fm->total_mark >= $criteria['pass_mark'] ? '#059669' : '#dc2626' }}">
                                            {{ $fm->total_mark ?? '—' }}
                                        </span>
                                    </td>
                                    <td style="padding:14px; text-align:center">
                                        @php
                                            $gc = match($fm->grade) {
                                                'A+' => 'grade-ap',
                                                'A'  => 'grade-a',
                                                'A-' => 'grade-am',
                                                'B'  => 'grade-b',
                                                'C'  => 'grade-c',
                                                default => 'grade-f',
                                            };
                                        @endphp
                                        <span class="grade-badge {{ $gc }}">{{ $fm->grade ?? '—' }}</span>
                                    </td>
                                    <td style="padding:14px; text-align:center; font-weight:800; color:#1e293b">
                                        {{ number_format($fm->gpa ?? 0, 2) }}
                                    </td>
                                    <td style="padding:14px; text-align:center">
                                        @if($fm->status === 'PASS')
                                            <span class="status-pass"><i class="fa-solid fa-check"></i> PASS</span>
                                        @elseif($fm->status === 'FAIL')
                                            <span class="status-fail"><i class="fa-solid fa-xmark"></i> FAIL</span>
                                        @else
                                            <span class="status-inc">অসম্পূর্ণ</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px; text-align:center; font-size:11.5px; color:#94a3b8">
                                        {{ $fm->generated_at ? $fm->generated_at->format('d M Y') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary Footer --}}
                    <div style="padding:16px 22px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; gap:24px; flex-wrap:wrap; font-size:13.5px; font-weight:700">
                        @php
                            $passCount = $finalMarks->where('status', 'PASS')->count();
                            $failCount = $finalMarks->where('status', 'FAIL')->count();
                            $avgTotal  = round($finalMarks->avg('total_mark'), 2);
                        @endphp
                        <span>মোট শিক্ষার্থী: <strong style="color:#0f172a">{{ $finalMarks->count() }} জন</strong></span>
                        <span>উত্তীর্ণ: <strong style="color:#16a34a">{{ $passCount }} জন</strong></span>
                        <span>অনুত্তীর্ণ: <strong style="color:#dc2626">{{ $failCount }} জন</strong></span>
                        <span>গড় নম্বর: <strong style="color:#047857">{{ $avgTotal }}/{{ $totalConvert }}</strong></span>
                        <span>পাসের হার: <strong style="color:#047857">{{ $finalMarks->count() > 0 ? round(($passCount / $finalMarks->count()) * 100, 1) : 0 }}%</strong></span>
                    </div>
                @endif
            </div>
        @elseif(!request('batch_id'))
            <div class="card" style="text-align:center; padding:50px 20px; border-radius:16px; color:#64748b; background:#fff; border:1px solid #e2e8f0">
                <i class="fa-solid fa-layer-group" style="font-size:42px; color:#cbd5e1; margin-bottom:12px; display:block"></i>
                <strong style="font-size:16px; color:#1e293b; display:block; margin-bottom:6px">প্রথমে একটি ব্যাচ ও বিষয় নির্বাচন করুন</strong>
                <span style="font-size:13px; color:#64748b">ব্যাচ নির্বাচন করার পর সংশ্লিষ্ট বিষয়ের তালিকা স্বয়ংক্রিয়ভাবে লোড হবে।</span>
            </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL 1: Conversion Criteria Settings Modal                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="criteriaModal" class="modal-overlay" onclick="if(event.target===this) closeCriteriaModal()">
        <div class="modal-card">
            <div style="padding:18px 24px; background:#065f46; color:#fff; display:flex; justify-content:space-between; align-items:center">
                <h3 style="margin:0; font-size:17px; font-weight:800; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-sliders"></i> কনভার্সন ক্রাইটেরিয়া নির্ধারণ (Conversion Criteria)
                </h3>
                <button type="button" onclick="closeCriteriaModal()" style="background:none; border:none; color:#fff; font-size:24px; line-height:1; cursor:pointer">&times;</button>
            </div>
            
            <form method="POST" action="{{ route('admin.final-marks.update-criteria') }}" style="padding:22px 24px">
                @csrf
                <p style="margin:0 0 16px; font-size:13px; color:#64748b">
                    প্রতিটি পরীক্ষার মূল পূর্ণমান এবং চূড়ান্ত ১০০ নম্বরে তা কত নম্বরে রূপান্তর (Convert) হবে তা নিচে নির্ধারণ করুন:
                </p>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:12px">
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#1e293b; display:block; margin-bottom:4px">ক্লাস টেস্ট পূর্ণমান</label>
                        <input type="number" step="0.5" name="class_test_full" class="form-control" value="{{ $criteria['class_test_full'] }}" required oninput="calcTotalConvert()">
                    </div>
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#047857; display:block; margin-bottom:4px">ক্লাস টেস্ট রূপান্তর (Convert)</label>
                        <input type="number" step="0.5" name="class_test_convert" id="inp_ct_conv" class="form-control" value="{{ $criteria['class_test_convert'] }}" required oninput="calcTotalConvert()">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:12px">
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#1e293b; display:block; margin-bottom:4px">মিডটার্ম পূর্ণমান</label>
                        <input type="number" step="0.5" name="midterm_full" class="form-control" value="{{ $criteria['midterm_full'] }}" required oninput="calcTotalConvert()">
                    </div>
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#047857; display:block; margin-bottom:4px">মিডটার্ম রূপান্তর (Convert)</label>
                        <input type="number" step="0.5" name="midterm_convert" id="inp_mid_conv" class="form-control" value="{{ $criteria['midterm_convert'] }}" required oninput="calcTotalConvert()">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:12px">
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#1e293b; display:block; margin-bottom:4px">ফাইনাল পরীক্ষা পূর্ণমান</label>
                        <input type="number" step="0.5" name="final_full" class="form-control" value="{{ $criteria['final_full'] }}" required oninput="calcTotalConvert()">
                    </div>
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#047857; display:block; margin-bottom:4px">ফাইনাল রূপান্তর (Convert)</label>
                        <input type="number" step="0.5" name="final_convert" id="inp_fn_conv" class="form-control" value="{{ $criteria['final_convert'] }}" required oninput="calcTotalConvert()">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:16px">
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#047857; display:block; margin-bottom:4px">উপস্থিতি মার্ক রূপান্তর</label>
                        <input type="number" step="0.5" name="attendance_convert" id="inp_att_conv" class="form-control" value="{{ $criteria['attendance_convert'] }}" required oninput="calcTotalConvert()">
                    </div>
                    <div>
                        <label style="font-size:12.5px; font-weight:700; color:#dc2626; display:block; margin-bottom:4px">পাস মার্ক (Pass Mark)</label>
                        <input type="number" step="0.5" name="pass_mark" class="form-control" value="{{ $criteria['pass_mark'] }}" required>
                    </div>
                </div>

                <div style="padding:12px 16px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center">
                    <span style="font-weight:700; font-size:13px; color:#065f46">সর্বমোট চূড়ান্ত রূপান্তর নম্বর (Target 100):</span>
                    <strong id="liveTotalConvert" style="font-size:18px; color:#064e3b">{{ $totalConvert }} নম্বর</strong>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px">
                    <button type="button" onclick="closeCriteriaModal()" class="btn btn-secondary" style="padding:8px 18px">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="padding:8px 22px; font-weight:800; background:#047857; border-color:#047857">
                        <i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL 2: Student Attendance Mark Override Modal                 --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="attendanceModal" class="modal-overlay" onclick="if(event.target===this) closeAttendanceModal()">
        <div class="modal-card" style="max-width:460px">
            <div style="padding:16px 20px; background:#047857; color:#fff; display:flex; justify-content:space-between; align-items:center">
                <h3 style="margin:0; font-size:16px; font-weight:800; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-user-check"></i> উপস্থিতি নম্বর পরিবর্তন
                </h3>
                <button type="button" onclick="closeAttendanceModal()" style="background:none; border:none; color:#fff; font-size:24px; line-height:1; cursor:pointer">&times;</button>
            </div>
            
            <form id="attendanceForm" method="POST" action="" style="padding:20px 22px">
                @csrf
                @method('PATCH')
                
                <div style="margin-bottom:16px">
                    <span style="font-size:12px; color:#64748b">শিক্ষার্থীর নাম:</span>
                    <div id="modalStudentName" style="font-weight:800; font-size:15px; color:#0f172a"></div>
                </div>

                <div style="margin-bottom:14px">
                    <label style="font-size:13px; font-weight:700; color:#065f46; display:block; margin-bottom:5px">
                        উপস্থিতির রূপান্তর নম্বর (Max: <span id="maxAttendanceSpan">10</span>) *
                    </label>
                    <input type="number" step="0.1" min="0" max="100" name="attendance_converted" id="modalAttendanceConverted" class="form-control" style="height:44px; font-size:16px; font-weight:800" required>
                    <small style="color:#64748b; font-size:11.5px; margin-top:3px; display:block">
                        এই নম্বরটি সরাসরি চূড়ান্ত ফলাফলে যোগ হবে।
                    </small>
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:13px; font-weight:700; color:#1e293b; display:block; margin-bottom:5px">
                        উপস্থিতির শতাংশ (%) (ঐচ্ছিক)
                    </label>
                    <input type="number" step="0.1" min="0" max="100" name="attendance_percent" id="modalAttendancePercent" class="form-control" style="height:40px">
                </div>

                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:10px 14px; margin-bottom:18px; font-size:12px; color:#166534">
                    💡 <span style="font-weight:700">নোট:</span> উপস্থিতি নম্বর পরিবর্তন করার সাথে সাথে শিক্ষার্থীর মোট নম্বর, জিপিএ এবং গ্রেড স্বয়ংক্রিয়ভাবে পুনর্গণনা করা হবে।
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px">
                    <button type="button" onclick="closeAttendanceModal()" class="btn btn-secondary" style="padding:8px 18px">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="padding:8px 20px; font-weight:800; background:#047857; border-color:#047857">
                        <i class="fa-solid fa-check"></i> নম্বর আপডেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.cachedBatchSubjects = [];

        // ── 1. Batch Change & Dynamic Semester / Subject Loading ──
        window.handleBatchChange = function(batchId) {
            const semContainer = document.getElementById('semesterSelectContainer');
            const semSelect = document.getElementById('semesterSelect');
            const subjectSelect = document.getElementById('subjectSelect');
            const numSpan = document.getElementById('subjectLabelNumber');

            if (!subjectSelect) return;

            if (!batchId) {
                if (semContainer) semContainer.style.display = 'none';
                if (semSelect) semSelect.innerHTML = '<option value="">-- সকল সেমিস্টার --</option>';
                if (numSpan) numSpan.textContent = '২';
                subjectSelect.innerHTML = '<option value="">-- বিষয় নির্বাচন করুন --</option>';
                window.cachedBatchSubjects = [];
                return;
            }

            subjectSelect.innerHTML = '<option value="">লোড হচ্ছে...</option>';

            fetch(`{{ route('admin.final-marks.batch-subjects') }}?batch_id=${batchId}`)
                .then(res => res.json())
                .then(data => {
                    window.cachedBatchSubjects = data.subjects || [];

                    // Check if course has semesters (SEMESTER_BASED)
                    if (data.has_semesters && data.semesters && data.semesters.length > 0) {
                        if (semContainer) semContainer.style.display = 'block';
                        if (numSpan) numSpan.textContent = '৩';

                        if (semSelect) {
                            semSelect.innerHTML = '<option value="">-- সকল সেমিস্টার --</option>';
                            let selectedVal = '';
                            data.semesters.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.id;
                                opt.textContent = s.name + (s.is_running ? ' (রানিং / Running)' : '');
                                if (s.is_running || (!selectedVal && s.id == data.running_semester_id)) {
                                    opt.selected = true;
                                    selectedVal = s.id;
                                }
                                semSelect.appendChild(opt);
                            });
                            // Filter subjects for the selected semester
                            window.handleSemesterChange(selectedVal);
                        }
                    } else {
                        // SUBJECT_BASED or no semesters -> HIDE semester selection
                        if (semContainer) semContainer.style.display = 'none';
                        if (semSelect) {
                            semSelect.innerHTML = '<option value="">-- সেমিস্টার প্রযোজ্য নয় --</option>';
                            semSelect.value = '';
                        }
                        if (numSpan) numSpan.textContent = '২';
                        
                        // Populate all subjects directly
                        renderSubjectOptions(window.cachedBatchSubjects);
                    }
                })
                .catch(err => {
                    console.error('Error fetching batch subjects:', err);
                    subjectSelect.innerHTML = '<option value="">বিষয় লোড করতে সমস্যা হয়েছে</option>';
                });
        };

        // ── 1.1 Semester Change Handler ──
        window.handleSemesterChange = function(semesterId) {
            if (!window.cachedBatchSubjects) return;

            let filtered = window.cachedBatchSubjects;
            if (semesterId) {
                const bySem = window.cachedBatchSubjects.filter(s => s.semester_id == semesterId);
                if (bySem.length > 0) {
                    filtered = bySem;
                }
            }
            renderSubjectOptions(filtered);
        };

        function renderSubjectOptions(subjectsList) {
            const subjectSelect = document.getElementById('subjectSelect');
            if (!subjectSelect) return;

            subjectSelect.innerHTML = '<option value="">-- বিষয় নির্বাচন করুন --</option>';
            if (subjectsList && subjectsList.length > 0) {
                subjectsList.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name + (s.code ? ' (' + s.code + ')' : '');
                    subjectSelect.appendChild(opt);
                });
            } else {
                subjectSelect.innerHTML = '<option value="">এই সেমিস্টারে কোনো বিষয় নির্ধারিত নেই</option>';
            }
        }

        // ── 2. Criteria Modal Controls ──
        window.openCriteriaModal = function(e) {
            if (e && typeof e.preventDefault === 'function') {
                e.preventDefault();
            }
            const m = document.getElementById('criteriaModal');
            if (m) {
                m.classList.add('open', 'is-active', 'show');
                m.style.setProperty('display', 'flex', 'important');
                m.style.setProperty('opacity', '1', 'important');
                m.style.setProperty('pointer-events', 'all', 'important');
                document.body.style.overflow = 'hidden';
            }
            window.calcTotalConvert();
        };

        window.closeCriteriaModal = function() {
            const m = document.getElementById('criteriaModal');
            if (m) {
                m.classList.remove('open', 'is-active', 'show');
                m.style.setProperty('display', 'none', 'important');
                m.style.setProperty('opacity', '0', 'important');
                m.style.setProperty('pointer-events', 'none', 'important');
                document.body.style.overflow = '';
            }
        };

        window.calcTotalConvert = function() {
            const ct = parseFloat(document.getElementById('inp_ct_conv')?.value || 0);
            const mid = parseFloat(document.getElementById('inp_mid_conv')?.value || 0);
            const fn = parseFloat(document.getElementById('inp_fn_conv')?.value || 0);
            const att = parseFloat(document.getElementById('inp_att_conv')?.value || 0);
            const sum = Math.round((ct + mid + fn + att) * 100) / 100;
            const el = document.getElementById('liveTotalConvert');
            if (el) {
                el.textContent = sum + ' নম্বর';
                if (sum === 100) {
                    el.style.color = '#065f46';
                } else {
                    el.style.color = '#ea580c';
                }
            }
        };

        // ── 3. Student Attendance Modal Controls ──
        window.openAttendanceModal = function(finalMarkId, studentName, attendancePercent, attendanceConverted, maxAttendance) {
            const nameEl = document.getElementById('modalStudentName');
            if (nameEl) nameEl.textContent = studentName;
            const convEl = document.getElementById('modalAttendanceConverted');
            if (convEl) convEl.value = attendanceConverted;
            const pctEl = document.getElementById('modalAttendancePercent');
            if (pctEl) pctEl.value = attendancePercent;
            const maxEl = document.getElementById('maxAttendanceSpan');
            if (maxEl) maxEl.textContent = maxAttendance;
            
            const form = document.getElementById('attendanceForm');
            if (form) {
                form.action = `{{ url('admin/final-marks') }}/${finalMarkId}/update-attendance`;
            }
            
            const m = document.getElementById('attendanceModal');
            if (m) {
                m.classList.add('open', 'is-active', 'show');
                m.style.setProperty('display', 'flex', 'important');
                m.style.setProperty('opacity', '1', 'important');
                m.style.setProperty('pointer-events', 'all', 'important');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeAttendanceModal = function() {
            const m = document.getElementById('attendanceModal');
            if (m) {
                m.classList.remove('open', 'is-active', 'show');
                m.style.setProperty('display', 'none', 'important');
                m.style.setProperty('opacity', '0', 'important');
                m.style.setProperty('pointer-events', 'none', 'important');
                document.body.style.overflow = '';
            }
        };

        // ── 4. Event Bindings on DOMContentLoaded ──
        function initFinalMarksPage() {
            const btnCriteria = document.getElementById('btnOpenCriteriaModal');
            if (btnCriteria) {
                btnCriteria.onclick = function(e) {
                    window.openCriteriaModal(e);
                };
                btnCriteria.addEventListener('click', function(e) {
                    window.openCriteriaModal(e);
                });
            }

            // Close modals on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.closeCriteriaModal();
                    window.closeAttendanceModal();
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initFinalMarksPage);
        } else {
            initFinalMarksPage();
        }
    </script>

</x-admin-layout>
