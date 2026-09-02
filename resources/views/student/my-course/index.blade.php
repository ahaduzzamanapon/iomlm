@extends('student.layouts.app')

@section('title', 'My Course')

@section('content')
<style>
    .course-header-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 16px;
        padding: 24px 28px;
        color: #fff;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .enrollment-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .enrollment-card-header {
        background: linear-gradient(90deg, #1e40af 0%, #2563eb 100%);
        color: #fff;
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(255,255,255,0.15);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.25);
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-item { }
    .info-item .info-label {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }
    .info-item .info-value {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .info-item .info-value.accent { color: #2563eb; }
    .attendance-bar-bg {
        background: #e2e8f0;
        border-radius: 20px;
        height: 8px;
        margin-top: 6px;
        overflow: hidden;
    }
    .attendance-bar-fill {
        height: 100%;
        border-radius: 20px;
        transition: width .4s ease;
    }
    .subject-section {
        padding: 20px 24px;
    }
    .subject-section h4 {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .subject-table { width: 100%; border-collapse: collapse; }
    .subject-table th {
        font-size: 11.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 8px 12px;
        background: #f8fafc;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }
    .subject-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13.5px;
        color: #1e293b;
        vertical-align: middle;
    }
    .subject-table tr:last-child td { border-bottom: none; }
    .semester-badge {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .final-mark-section {
        padding: 0 24px 20px;
        border-top: 1px solid #f1f5f9;
        margin-top: 4px;
    }
    .grade-pill {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 12px;
    }
    .grade-ap  { background:#dcfce7; color:#16a34a; }
    .grade-a   { background:#d1fae5; color:#059669; }
    .grade-am  { background:#e0f2fe; color:#0284c7; }
    .grade-b   { background:#fef9c3; color:#ca8a04; }
    .grade-c   { background:#ffedd5; color:#ea580c; }
    .grade-f   { background:#fee2e2; color:#dc2626; }
    .status-pass { background:#dcfce7; color:#16a34a; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:700; }
    .status-fail { background:#fee2e2; color:#dc2626; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:700; }
</style>

{{-- Page Header --}}
<div class="course-header-card">
    <div>
        <h1 style="margin:0 0 5px; font-size:22px; font-weight:800">🎓 My Course</h1>
        <p style="margin:0; font-size:13px; color:#94a3b8">আপনার ভর্তি হওয়া কোর্স, সাবজেক্ট, উপস্থিতি ও ফলাফলের বিবরণ</p>
    </div>
    <div style="font-size:13px; color:#94a3b8">
        মোট ভর্তি: <strong style="color:#fff">{{ $enrollments->count() }}</strong>
    </div>
</div>

@if($enrollments->isEmpty())
    <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:16px; border:1px solid #e2e8f0">
        <div style="font-size:48px; margin-bottom:12px">📚</div>
        <strong style="font-size:16px; color:#1e293b">কোনো কোর্সে এখনো ভর্তি হননি।</strong><br>
        <span style="font-size:13px; color:#64748b">অ্যাডমিশন সম্পন্ন হলে এখানে আপনার কোর্স দেখা যাবে।</span>
    </div>
@endif

@foreach($enrollments as $enrollment)
@php
    $course  = $enrollment->course;
    $batch   = $enrollment->batch;
    $attPct  = $enrollment->_attendance_pct;
    $attColor = $attPct >= 75 ? '#16a34a' : ($attPct >= 50 ? '#f59e0b' : '#dc2626');
    // Current semester — skip showing total semesters count per user request
    $currentSem = $enrollment->_current_semester;
    $finalMarks = $enrollment->_final_marks;
@endphp

<div class="enrollment-card">

    {{-- Card Header --}}
    <div class="enrollment-card-header">
        <div>
            <div style="font-size:11px; opacity:.7; font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px">
                {{ $batch?->name ?? '—' }}
            </div>
            <div style="font-size:20px; font-weight:800">{{ $course?->name ?? '—' }}</div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap">
            <span class="stat-pill">
                📋 {{ $enrollment->_total_subjects }} Subjects
            </span>
            <span class="stat-pill">
                🎯 {{ $enrollment->_result_count }} Results
            </span>
            <span class="stat-pill" style="background:{{ $enrollment->status === 'ACTIVE' ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)' }}; border-color:rgba(255,255,255,0.2)">
                {{ $enrollment->status }}
            </span>
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">🏫 ব্যাচ</div>
            <div class="info-value">{{ $batch?->name ?? '—' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">📖 কোর্সের ধরন</div>
            <div class="info-value">{{ $course?->type === 'SEMESTER_BASED' ? 'Semester Based' : 'Subject Based' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">🗓️ ভর্তির তারিখ</div>
            <div class="info-value">{{ $enrollment->enrolled_at ? \Carbon\Carbon::parse($enrollment->enrolled_at)->format('d M Y') : '—' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">📅 ব্যাচ শুরু</div>
            <div class="info-value">{{ $batch?->start_date ? \Carbon\Carbon::parse($batch->start_date)->format('d M Y') : '—' }}</div>
        </div>
        @if($currentSem)
        <div class="info-item">
            <div class="info-label">📌 বর্তমান সেমিস্টার</div>
            <div class="info-value accent">{{ $currentSem->name }}</div>
        </div>
        @endif
        <div class="info-item">
            <div class="info-label">🕐 কোর্সের মেয়াদ</div>
            <div class="info-value">{{ $course?->duration_value ?? '—' }} {{ $course?->duration_unit }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">📊 উপস্থিতি ({{ $enrollment->_present_count }}/{{ $enrollment->_total_sessions }} ক্লাস)</div>
            <div class="info-value" style="color:{{ $attColor }}">{{ $attPct }}%</div>
            <div class="attendance-bar-bg">
                <div class="attendance-bar-fill" style="width:{{ $attPct }}%; background:{{ $attColor }}"></div>
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">📚 মোট সাবজেক্ট</div>
            <div class="info-value accent">{{ $enrollment->_total_subjects }}</div>
        </div>
    </div>

    {{-- Subjects Table --}}
    @if($course && $course->courseSubjectMaps->count() > 0)
    <div class="subject-section">
        <h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="#2563eb"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            কোর্স সাবজেক্টসমূহ
        </h4>
        <div style="overflow-x:auto">
            <table class="subject-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Credit</th>
                        <th>Full Marks</th>
                        <th>Pass Marks</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($course->courseSubjectMaps()->with(['subject', 'semester'])->orderBy('sort_order')->get() as $i => $map)
                    <tr>
                        <td style="font-weight:700; color:#94a3b8; font-size:12px">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $map->subject?->name ?? '—' }}</strong>
                        </td>
                        <td style="font-family:monospace; font-size:12px; color:#64748b">{{ $map->subject?->code ?? '—' }}</td>
                        <td style="text-align:center; font-weight:700; color:#2563eb">{{ $map->subject?->credit ?? '—' }}</td>
                        <td style="text-align:center; font-weight:700">{{ $map->subject?->full_marks ?? '—' }}</td>
                        <td style="text-align:center; font-weight:700; color:#dc2626">{{ $map->subject?->pass_marks ?? '—' }}</td>
                        <td>
                            @if($map->semester)
                                <span class="semester-badge">{{ $map->semester->name }}</span>
                            @else
                                <span style="color:#cbd5e1; font-size:12px">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Final Marks Table (if generated) --}}
    @if($finalMarks->isNotEmpty())
    <div class="final-mark-section">
        <h4 style="font-size:14px; font-weight:700; color:#334155; margin:16px 0 12px; display:flex; align-items:center; gap:8px">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="#16a34a"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
            আমার চূড়ান্ত মার্ক (IOM Conversion)
        </h4>
        <div style="overflow-x:auto">
            <table class="subject-table" style="min-width:750px">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th style="text-align:center">Class Test<br><span style="font-weight:400;font-size:10px;color:#94a3b8">/30 → /20</span></th>
                        <th style="text-align:center">Mid Term<br><span style="font-weight:400;font-size:10px;color:#94a3b8">/50 → /30</span></th>
                        <th style="text-align:center">Final Term<br><span style="font-weight:400;font-size:10px;color:#94a3b8">/100 → /40</span></th>
                        <th style="text-align:center">Attendance<br><span style="font-weight:400;font-size:10px;color:#94a3b8">% → /10</span></th>
                        <th style="text-align:center; color:#2563eb; font-weight:800">Total /100</th>
                        <th style="text-align:center">Grade</th>
                        <th style="text-align:center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($finalMarks as $fm)
                    <tr>
                        <td><strong>{{ $fm->subject?->name ?? '—' }}</strong></td>
                        <td style="text-align:center; font-size:12px">
                            @if($fm->class_test_obtained !== null)
                                {{ $fm->class_test_obtained }} → <strong style="color:#2563eb">{{ $fm->class_test_converted }}</strong>
                            @else <span style="color:#cbd5e1">—</span> @endif
                        </td>
                        <td style="text-align:center; font-size:12px">
                            @if($fm->midterm_obtained !== null)
                                {{ $fm->midterm_obtained }} → <strong style="color:#2563eb">{{ $fm->midterm_converted }}</strong>
                            @else <span style="color:#cbd5e1">—</span> @endif
                        </td>
                        <td style="text-align:center; font-size:12px">
                            @if($fm->final_obtained !== null)
                                {{ $fm->final_obtained }} → <strong style="color:#2563eb">{{ $fm->final_converted }}</strong>
                            @else <span style="color:#cbd5e1">—</span> @endif
                        </td>
                        <td style="text-align:center; font-size:12px">
                            @if($fm->attendance_percent !== null)
                                {{ $fm->attendance_percent }}% → <strong style="color:#2563eb">{{ $fm->attendance_converted }}</strong>
                            @else <span style="color:#cbd5e1">—</span> @endif
                        </td>
                        <td style="text-align:center">
                            <strong style="font-size:17px; color:{{ ($fm->total_mark ?? 0) >= 40 ? '#16a34a' : '#dc2626' }}">
                                {{ $fm->total_mark ?? '—' }}
                            </strong>
                        </td>
                        <td style="text-align:center">
                            @php
                                $gc = match($fm->grade) {
                                    'A+' => 'grade-ap', 'A' => 'grade-a', 'A-' => 'grade-am',
                                    'B'  => 'grade-b',  'C' => 'grade-c', default => 'grade-f',
                                };
                            @endphp
                            <span class="grade-pill {{ $gc }}">{{ $fm->grade ?? '—' }}</span>
                        </td>
                        <td style="text-align:center">
                            @if($fm->status === 'PASS')
                                <span class="status-pass">PASS</span>
                            @elseif($fm->status === 'FAIL')
                                <span class="status-fail">FAIL</span>
                            @else
                                <span style="background:#f1f5f9;color:#64748b;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700">INC</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endforeach

@endsection
