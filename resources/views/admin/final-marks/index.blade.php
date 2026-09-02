<x-admin-layout>
    <x-slot name="title">Final Mark Generator</x-slot>

    <style>
        .fm-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }
        .criteria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .criteria-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .criteria-card .label  { font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; }
        .criteria-card .marks  { font-size: 22px; font-weight: 800; color: #1e293b; }
        .criteria-card .arrow  { font-size: 11px; color: #94a3b8; margin: 2px 0; }
        .criteria-card .convert{ font-size: 14px; font-weight: 700; color: #2563eb; }
        .grade-badge {
            display: inline-block;
            padding: 3px 10px;
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
        .status-pass { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; }
        .status-fail { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; }
        .status-inc  { background:#f1f5f9; color:#64748b; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; }
    </style>

    {{-- Header --}}
    <div class="fm-header">
        <div>
            <h1 style="margin:0 0 5px; font-size:22px; font-weight:800">📊 Final Mark Generator</h1>
            <p style="margin:0; font-size:13px; color:#94a3b8">IOM Marks Conversion — Class Test + Mid Term + Final + Attendance → 100</p>
        </div>
        @if(request('batch_id') && request('subject_id') && $finalMarks->isNotEmpty())
            <a href="{{ route('admin.final-marks.export-csv', ['batch_id' => request('batch_id'), 'subject_id' => request('subject_id')]) }}"
               style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.25); color:#fff; padding:9px 18px; border-radius:9px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:6px">
                📥 Export CSV
            </a>
        @endif
    </div>

    {{-- IOM Conversion Criteria Display --}}
    <div class="criteria-grid">
        <div class="criteria-card">
            <div class="label">Class Test</div>
            <div class="marks">30</div>
            <div class="arrow">↓ converts to</div>
            <div class="convert">20 marks</div>
        </div>
        <div class="criteria-card">
            <div class="label">Mid Term</div>
            <div class="marks">50</div>
            <div class="arrow">↓ converts to</div>
            <div class="convert">30 marks</div>
        </div>
        <div class="criteria-card">
            <div class="label">Final Term</div>
            <div class="marks">100</div>
            <div class="arrow">↓ converts to</div>
            <div class="convert">40 marks</div>
        </div>
        <div class="criteria-card">
            <div class="label">Attendance</div>
            <div class="marks">10%</div>
            <div class="arrow">↓ converts to</div>
            <div class="convert">10 marks</div>
        </div>
        <div class="criteria-card" style="border-color:#2563eb; background:#eff6ff">
            <div class="label" style="color:#2563eb">TOTAL</div>
            <div class="marks" style="color:#2563eb">190</div>
            <div class="arrow" style="color:#3b82f6">→ final out of</div>
            <div class="convert" style="font-size:20px">100 marks</div>
        </div>
    </div>

    {{-- Filter + Generate Form --}}
    <div class="card" style="margin-bottom:20px; padding:20px; border-radius:14px">
        <form method="GET" action="{{ route('admin.final-marks.index') }}" style="display:grid; grid-template-columns: 1fr 1fr auto auto; gap:12px; align-items:end; flex-wrap:wrap">
            <div class="form-group" style="margin:0">
                <label class="form-label" style="font-weight:600; font-size:13px">Select Batch</label>
                <select name="batch_id" class="form-control" required onchange="this.form.submit()">
                    <option value="">-- Choose Batch --</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                            {{ $batch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0">
                <label class="form-label" style="font-weight:600; font-size:13px">Select Subject</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">-- Choose Subject --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="font-weight:700; height:42px">View Results</button>
            @if(request('batch_id') && request('subject_id'))
                <form method="POST" action="{{ route('admin.final-marks.generate') }}" style="margin:0">
                    @csrf
                    <input type="hidden" name="batch_id"   value="{{ request('batch_id') }}">
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                    <button type="submit" style="background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border:none; padding:0 20px; height:42px; border-radius:9px; font-weight:700; font-size:13px; cursor:pointer; white-space:nowrap; box-shadow:0 4px 12px rgba(22,163,74,0.3)"
                        onclick="return confirm('Generate / regenerate final marks for all active students?')">
                        ⚙️ Generate / Regenerate Marks
                    </button>
                </form>
            @endif
        </form>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:20px">{{ session('error') }}</div>
    @endif

    {{-- Results Table --}}
    @if($selectedBatch && $selectedSubject)
        <div class="card" style="border-radius:16px; overflow:hidden; padding:0">
            <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                <div>
                    <strong style="font-size:15px; color:#0f172a">{{ $selectedBatch->name }}</strong>
                    <span style="color:#64748b; font-size:13px"> — </span>
                    <strong style="font-size:14px; color:#2563eb">{{ $selectedSubject->name }}</strong>
                </div>
                <span class="badge badge-secondary no-dot" style="font-size:12px; padding:5px 12px; border-radius:20px">
                    {{ $finalMarks->count() }} Students
                </span>
            </div>

            @if($finalMarks->isEmpty())
                <div style="text-align:center; padding:50px 20px; color:#64748b">
                    <div style="font-size:36px; margin-bottom:12px">📊</div>
                    <strong style="font-size:15px; color:#1e293b">No marks generated yet.</strong><br>
                    <span style="font-size:13px">Click <em>"Generate / Regenerate Marks"</em> button above to compute final marks.</span>
                </div>
            @else
                <div style="overflow-x:auto">
                    <table style="min-width:900px">
                        <thead>
                            <tr style="background:#f8fafc">
                                <th style="width:40px">#</th>
                                <th>Student</th>
                                <th style="text-align:center">Class Test<br><small style="color:#94a3b8;font-weight:400">/30 → /20</small></th>
                                <th style="text-align:center">Mid Term<br><small style="color:#94a3b8;font-weight:400">/50 → /30</small></th>
                                <th style="text-align:center">Final Term<br><small style="color:#94a3b8;font-weight:400">/100 → /40</small></th>
                                <th style="text-align:center">Attendance<br><small style="color:#94a3b8;font-weight:400">% → /10</small></th>
                                <th style="text-align:center;font-weight:800;color:#2563eb">Total<br><small style="font-weight:400;color:#94a3b8">/100</small></th>
                                <th style="text-align:center">Grade</th>
                                <th style="text-align:center">GPA</th>
                                <th style="text-align:center">Status</th>
                                <th style="text-align:center; font-size:11px; color:#94a3b8">Generated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($finalMarks as $i => $fm)
                            <tr>
                                <td style="font-weight:700; color:#94a3b8">{{ $i + 1 }}</td>
                                <td>
                                    <strong style="color:#0f172a">{{ $fm->student->name ?? '—' }}</strong>
                                    <div style="font-size:11px; color:#94a3b8">{{ $fm->student->student_code ?? '' }}</div>
                                </td>
                                <td style="text-align:center">
                                    @if($fm->class_test_obtained !== null)
                                        <span style="font-weight:700; color:#1e293b">{{ $fm->class_test_obtained }}</span>
                                        <span style="color:#94a3b8; font-size:11px"> → </span>
                                        <span style="font-weight:700; color:#2563eb">{{ $fm->class_test_converted }}</span>
                                    @else
                                        <span style="color:#cbd5e1">—</span>
                                    @endif
                                </td>
                                <td style="text-align:center">
                                    @if($fm->midterm_obtained !== null)
                                        <span style="font-weight:700; color:#1e293b">{{ $fm->midterm_obtained }}</span>
                                        <span style="color:#94a3b8; font-size:11px"> → </span>
                                        <span style="font-weight:700; color:#2563eb">{{ $fm->midterm_converted }}</span>
                                    @else
                                        <span style="color:#cbd5e1">—</span>
                                    @endif
                                </td>
                                <td style="text-align:center">
                                    @if($fm->final_obtained !== null)
                                        <span style="font-weight:700; color:#1e293b">{{ $fm->final_obtained }}</span>
                                        <span style="color:#94a3b8; font-size:11px"> → </span>
                                        <span style="font-weight:700; color:#2563eb">{{ $fm->final_converted }}</span>
                                    @else
                                        <span style="color:#cbd5e1">—</span>
                                    @endif
                                </td>
                                <td style="text-align:center">
                                    @if($fm->attendance_percent !== null)
                                        <span style="font-weight:700; color:#1e293b">{{ $fm->attendance_percent }}%</span>
                                        <span style="color:#94a3b8; font-size:11px"> → </span>
                                        <span style="font-weight:700; color:#2563eb">{{ $fm->attendance_converted }}</span>
                                    @else
                                        <span style="color:#cbd5e1">—</span>
                                    @endif
                                </td>
                                <td style="text-align:center">
                                    <span style="font-size:18px; font-weight:900; color:{{ $fm->total_mark >= 40 ? '#16a34a' : '#dc2626' }}">
                                        {{ $fm->total_mark ?? '—' }}
                                    </span>
                                </td>
                                <td style="text-align:center">
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
                                <td style="text-align:center; font-weight:700; color:#1e293b">
                                    {{ $fm->gpa ?? '—' }}
                                </td>
                                <td style="text-align:center">
                                    @if($fm->status === 'PASS')
                                        <span class="status-pass">PASS</span>
                                    @elseif($fm->status === 'FAIL')
                                        <span class="status-fail">FAIL</span>
                                    @else
                                        <span class="status-inc">INCOMPLETE</span>
                                    @endif
                                </td>
                                <td style="text-align:center; font-size:11px; color:#94a3b8">
                                    {{ $fm->generated_at ? $fm->generated_at->format('d M Y') : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Footer --}}
                <div style="padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; gap:24px; flex-wrap:wrap; font-size:13px">
                    @php
                        $passCount = $finalMarks->where('status', 'PASS')->count();
                        $failCount = $finalMarks->where('status', 'FAIL')->count();
                        $avgTotal  = round($finalMarks->avg('total_mark'), 2);
                    @endphp
                    <span>📊 <strong>{{ $finalMarks->count() }}</strong> Total Students</span>
                    <span>✅ <strong style="color:#16a34a">{{ $passCount }}</strong> Passed</span>
                    <span>❌ <strong style="color:#dc2626">{{ $failCount }}</strong> Failed</span>
                    <span>📈 Average: <strong style="color:#2563eb">{{ $avgTotal }}/100</strong></span>
                    <span>📅 Pass Rate: <strong>{{ $finalMarks->count() > 0 ? round(($passCount / $finalMarks->count()) * 100, 1) : 0 }}%</strong></span>
                </div>
            @endif
        </div>
    @elseif(!request('batch_id'))
        <div class="card" style="text-align:center; padding:50px 20px; border-radius:16px; color:#64748b">
            <div style="font-size:40px; margin-bottom:12px">📊</div>
            <strong style="font-size:16px; color:#1e293b">Select a Batch and Subject to view or generate final marks.</strong>
        </div>
    @endif

</x-admin-layout>
