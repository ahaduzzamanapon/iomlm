<x-admin-layout>
    <x-slot name="title">রেজাল্ট বুক ও মার্কশীট ব্যবস্থাপনা (Result Book)</x-slot>

    <style>
        .rb-wrapper {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .rb-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            border-radius: 16px;
            padding: 24px 28px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 10px 25px -5px rgba(30, 58, 138, 0.25);
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .exam-publish-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
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

    <div class="rb-wrapper">
        {{-- Header --}}
        <div class="rb-header">
            <div>
                <h1 style="margin:0 0 6px; font-size:24px; font-weight:800; display:flex; align-items:center; gap:10px">
                    <i class="fa-solid fa-book-bookmark"></i> রেজাল্ট বুক (Result Book &amp; Mark Sheet)
                </h1>
                <p style="margin:0; font-size:13.5px; color:#bfdbfe">
                    শিক্ষার্থী রোল সার্চ, প্রতিটি পরীক্ষার নম্বর সংশোধন, কওমি মান, মেধাক্রম ও বহু-পরীক্ষা ফলাফল প্রকাশ
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap">
                <a href="{{ route('admin.result-book.batch-merit', request()->only('batch_id')) }}"
                   style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); color:#fff; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:7px">
                    <i class="fa-solid fa-trophy" style="color:#fef08a"></i> ৬-সেমিস্টার ব্যাচ মেধা তালিকা
                </a>
                <a href="{{ route('admin.final-marks.index') }}"
                   style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); color:#fff; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:7px">
                    <i class="fa-solid fa-calculator"></i> ফাইনাল মার্ক জেনারেটর
                </a>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <div style="display:flex; gap:10px; border-bottom:2px solid #e2e8f0; margin-bottom:24px; padding-bottom:2px">
            <a href="{{ route('admin.result-book.index', request()->query()) }}" style="padding:10px 20px; font-size:13.5px; font-weight:800; color:#1e40af; text-decoration:none; border-radius:10px 10px 0 0; background:#eff6ff; border-bottom:3px solid #1e40af; display:inline-flex; align-items:center; gap:8px">
                <i class="fa-solid fa-book-bookmark"></i> বিষয়ভিত্তিক ফলাফল ও সংশোধন (Subject Mark Sheets)
            </a>
            <a href="{{ route('admin.result-book.batch-merit', request()->only('batch_id')) }}" style="padding:10px 20px; font-size:13.5px; font-weight:800; color:#64748b; text-decoration:none; border-radius:10px 10px 0 0; display:inline-flex; align-items:center; gap:8px">
                <i class="fa-solid fa-trophy"></i> ৬-সেমিস্টার ব্যাচ মেধা তালিকা (Batch 6-Sem Merit: ১ম, ২য়, ৩য়...)
            </a>
            <a href="{{ route('admin.final-marks.index') }}" style="padding:10px 20px; font-size:13.5px; font-weight:800; color:#64748b; text-decoration:none; border-radius:10px 10px 0 0; display:inline-flex; align-items:center; gap:8px">
                <i class="fa-solid fa-calculator"></i> ফাইনাল মার্ক জেনারেটর
            </a>
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

        {{-- Statistics Cards --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#dbeafe; color:#1d4ed8">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div style="font-size:12px; color:#64748b; font-weight:700">মোট রেকর্ড</div>
                    <div style="font-size:22px; font-weight:800; color:#0f172a">{{ $totalStudents }} জন</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#dcfce7; color:#15803d">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <div style="font-size:12px; color:#64748b; font-weight:700">উত্তীর্ণ (Passed)</div>
                    <div style="font-size:22px; font-weight:800; color:#15803d">{{ $passedCount }} জন</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fee2e2; color:#b91c1c">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <div style="font-size:12px; color:#64748b; font-weight:700">অনুত্তীর্ণ (Failed)</div>
                    <div style="font-size:22px; font-weight:800; color:#b91c1c">{{ $failedCount }} জন</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7; color:#b45309">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div>
                    <div style="font-size:12px; color:#64748b; font-weight:700">প্রকাশিত রেজাল্ট</div>
                    <div style="font-size:22px; font-weight:800; color:#b45309">{{ $publishedCount }} টি</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#ecfdf5; color:#047857">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <div style="font-size:12px; color:#64748b; font-weight:700">গড় নম্বর (Average)</div>
                    <div style="font-size:22px; font-weight:800; color:#047857">{{ $avgScore }}</div>
                </div>
            </div>
        </div>

        {{-- Multi-Criteria Filter Card --}}
        <div class="card" style="margin-bottom:24px; padding:20px 24px; border-radius:16px; background:#fff; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.03)">
            <form method="GET" action="{{ route('admin.result-book.index') }}" style="display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end">
                <div style="flex:1; min-width:220px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        শিক্ষার্থীর রোল / নাম সার্চ (Roll / Name Search)
                    </label>
                    <div style="position:relative">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:14px; color:#94a3b8"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="যেমন: 26-01-01-M-0001 বা নাম..." class="form-control" style="padding-left:36px; height:42px; border-radius:10px">
                    </div>
                </div>

                <div style="flex:1; min-width:200px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        ব্যাচ নির্বাচন (Batch)
                    </label>
                    <select name="batch_id" class="form-control" style="height:42px; border-radius:10px" onchange="this.form.submit()">
                        <option value="">-- সকল ব্যাচ --</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->course->name ?? 'কোর্স' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($semesters->isNotEmpty())
                <div style="flex:1; min-width:180px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        সেমিস্টার (Semester)
                    </label>
                    <select name="semester_id" class="form-control" style="height:42px; border-radius:10px" onchange="this.form.submit()">
                        <option value="">-- সকল সেমিস্টার --</option>
                        @foreach($semesters as $s)
                            <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div style="flex:1; min-width:200px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        বিষয় (Subject)
                    </label>
                    <select name="subject_id" class="form-control" style="height:42px; border-radius:10px" onchange="this.form.submit()">
                        <option value="">-- সকল বিষয় --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name }} ({{ $sub->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; gap:8px">
                    <button type="submit" class="btn btn-primary" style="height:42px; padding:0 20px; font-weight:800; border-radius:10px; background:#1e40af; border-color:#1e40af">
                        <i class="fa-solid fa-filter"></i> ফিল্টার
                    </button>
                    @if(request()->hasAny(['search', 'batch_id', 'semester_id', 'subject_id']))
                        <a href="{{ route('admin.result-book.index') }}" class="btn btn-secondary" style="height:42px; padding:0 16px; font-weight:700; border-radius:10px; display:inline-flex; align-items:center">
                            রিসেট
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Multi-Exam Publishing Cards (CT, Mid, Final) --}}
        @if($exams->isNotEmpty())
        <div class="card" style="margin-bottom:24px; padding:18px 24px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0">
            <h3 style="margin:0 0 12px; font-size:15px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:8px">
                <i class="fa-solid fa-bullhorn" style="color:#2563eb"></i> এই বিষয়ের একক পরীক্ষার রেজাল্ট প্রকাশ স্ট্যাটাস (Exam Result Publishing)
            </h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:12px">
                @foreach($exams as $ex)
                <div class="exam-publish-card">
                    <div>
                        <span class="badge badge-secondary no-dot" style="font-size:11px; margin-bottom:4px; display:inline-block">
                            {{ $ex->type }} (পূর্ণমান: {{ $ex->full_marks }})
                        </span>
                        <div style="font-size:13.5px; font-weight:800; color:#0f172a">{{ $ex->title }}</div>
                        <div style="font-size:11.5px; color:#64748b; margin-top:2px">
                            স্ট্যাটাস: 
                            @if($ex->is_result_published)
                                <strong style="color:#15803d">প্রকাশিত (Published)</strong>
                            @else
                                <strong style="color:#64748b">অপ্রকাশিত (Draft)</strong>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap">
                        <a href="{{ route('admin.exams.show', $ex) }}" target="_blank"
                           style="padding:6px 12px; border-radius:8px; font-size:12px; font-weight:800; text-decoration:none; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; display:inline-flex; align-items:center; gap:5px"
                           title="এই পরীক্ষার একক মেধা তালিকা ও শিক্ষার্থীদের মার্কশীট দেখুন">
                            <i class="fa-solid fa-trophy" style="color:#eab308"></i> মেধা তালিকা ও মার্কশীট
                        </a>
                        <form method="POST" action="{{ route('admin.result-book.publish-exam') }}">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ $ex->id }}">
                            <button type="submit" 
                               style="padding:6px 12px; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer; border:none; background:{{ $ex->is_result_published ? '#fee2e2' : '#dcfce7' }}; color:{{ $ex->is_result_published ? '#b91c1c' : '#15803d' }}">
                                <i class="fa-solid {{ $ex->is_result_published ? 'fa-eye-slash' : 'fa-check' }}"></i>
                                {{ $ex->is_result_published ? 'প্রত্যাহার' : 'প্রকাশ করুন' }}
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Result Book Table --}}
        <div class="card" style="border-radius:16px; overflow:hidden; background:#fff; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.03)">
            <div style="padding:16px 22px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
                <div style="font-size:15px; font-weight:800; color:#0f172a">
                    <i class="fa-solid fa-list-ol"></i> ফলাফল তালিকা ও নম্বরপত্র (Mark Sheet Records)
                </div>
                <div style="font-size:12.5px; color:#64748b">
                    মেধাক্রম (Merit Position) অনুযায়ী সাজানো হয়েছে
                </div>
            </div>

            @if($finalMarks->isEmpty())
                <div style="text-align:center; padding:55px 20px; color:#64748b">
                    <i class="fa-solid fa-folder-open" style="font-size:42px; color:#cbd5e1; margin-bottom:14px; display:block"></i>
                    <strong style="font-size:16px; color:#1e293b; display:block; margin-bottom:6px">কোনো ফলাফল রেকর্ড পাওয়া যায়নি</strong>
                    <span style="font-size:13px; color:#64748b">ফিল্টার পরিবর্তন করে অনুসন্ধান করুন অথবা ফাইনাল মার্ক জেনারেটর থেকে নম্বর তৈরি করুন।</span>
                </div>
            @else
                <div style="overflow-x:auto">
                    <table style="width:100%; border-collapse:collapse; font-size:13px">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0">
                                <th style="padding:12px 8px; text-align:center; width:55px">মেধাক্রম</th>
                                <th style="padding:12px 12px; text-align:left; min-width:130px">রোল ও শিক্ষার্থী</th>
                                <th style="padding:12px 10px; text-align:left; min-width:120px">বিষয় ও ব্যাচ</th>
                                <th style="padding:10px 6px; text-align:center">
                                    সিটি (/৩০)
                                    <div style="font-size:10px; color:#2563eb; font-weight:700">২০% মান</div>
                                </th>
                                <th style="padding:10px 6px; text-align:center">
                                    মিড (/৫০)
                                    <div style="font-size:10px; color:#2563eb; font-weight:700">৩০% মান</div>
                                </th>
                                <th style="padding:10px 6px; text-align:center">
                                    ফাইনাল (/১০০)
                                    <div style="font-size:10px; color:#2563eb; font-weight:700">৪০% মান</div>
                                </th>
                                <th style="padding:10px 6px; text-align:center; background:#f0fdf4">
                                    উপস্থিতি (/১০)
                                    <div style="font-size:10px; color:#059669; font-weight:700">১০% মান</div>
                                </th>
                                <th style="padding:10px 8px; text-align:center; background:#f8fafc; border-left:1px solid #e2e8f0">
                                    মোট প্রাপ্ত নম্বর
                                    <div style="font-size:10px; color:#475569; font-weight:800">আসল (/১৯০)</div>
                                </th>
                                <th style="padding:10px 10px; text-align:center; background:#ecfdf5; font-weight:800; color:#064e3b; border-left:1px solid #d1fae5">
                                    ১০০% এ মোট
                                    <div style="font-size:10px; color:#059669; font-weight:800">Criteria /১০০</div>
                                </th>
                                <th style="padding:12px 6px; text-align:center">গ্রেড (GPA)</th>
                                <th style="padding:12px 8px; text-align:center">কওমি মান</th>
                                <th style="padding:12px 6px; text-align:center">স্ট্যাটাস</th>
                                <th style="padding:12px 6px; text-align:center">প্রকাশিত?</th>
                                <th style="padding:12px 8px; text-align:center">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($finalMarks as $fm)
                            <tr style="border-bottom:1px solid #f1f5f9; transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding:12px 6px; text-align:center">
                                    @if($fm->merit_position)
                                        <span style="background:#fef3c7; color:#b45309; font-weight:900; padding:4px 8px; border-radius:12px; font-size:12px; border:1px solid #fde68a; display:inline-block">
                                            {{ $fm->merit_rank_bengali }}
                                        </span>
                                    @else
                                        <span style="color:#cbd5e1">—</span>
                                    @endif
                                </td>
                                <td style="padding:12px 12px">
                                    <strong style="color:#0f172a; font-size:13.5px">{{ $fm->student->name ?? '—' }}</strong>
                                    <div style="font-size:11.5px; color:#2563eb; font-weight:700; margin-top:2px">
                                        {{ $fm->student->student_code ?? $fm->student->student_id ?? '—' }}
                                    </div>
                                </td>
                                <td style="padding:12px 10px">
                                    <div style="font-weight:700; color:#1e293b; font-size:13px">{{ $fm->subject->name ?? '—' }}</div>
                                    <div style="font-size:11px; color:#64748b">
                                        {{ $fm->batch->name ?? '—' }} {{ $fm->semester ? "({$fm->semester->name})" : '' }}
                                    </div>
                                </td>
                                {{-- CT --}}
                                <td style="padding:12px 6px; text-align:center">
                                    <strong style="color:#0f172a; font-size:14px">{{ $fm->raw_class_test ?? '—' }}</strong>
                                    @if($fm->class_test_converted !== null)
                                        <div style="font-size:10.5px; color:#2563eb; font-weight:700" title="Criteria ২০% এ রূপান্তর">
                                            ({{ $fm->class_test_converted }})
                                        </div>
                                    @endif
                                </td>
                                {{-- Midterm --}}
                                <td style="padding:12px 6px; text-align:center">
                                    <strong style="color:#0f172a; font-size:14px">{{ $fm->raw_midterm ?? '—' }}</strong>
                                    @if($fm->midterm_converted !== null)
                                        <div style="font-size:10.5px; color:#2563eb; font-weight:700" title="Criteria ৩০% এ রূপান্তর">
                                            ({{ $fm->midterm_converted }})
                                        </div>
                                    @endif
                                </td>
                                {{-- Final --}}
                                <td style="padding:12px 6px; text-align:center">
                                    <strong style="color:#0f172a; font-size:14px">{{ $fm->raw_final ?? '—' }}</strong>
                                    @if($fm->final_converted !== null)
                                        <div style="font-size:10.5px; color:#2563eb; font-weight:700" title="Criteria ৪০% এ রূপান্তর">
                                            ({{ $fm->final_converted }})
                                        </div>
                                    @endif
                                </td>
                                {{-- Attendance --}}
                                <td style="padding:12px 6px; text-align:center; background:#f0fdf4">
                                    <strong style="color:#065f46; font-size:14px">{{ $fm->raw_attendance ?? '—' }}</strong>
                                    <div style="font-size:10px; color:#64748b">{{ $fm->attendance_percent ?? 0 }}%</div>
                                </td>
                                {{-- Raw Total --}}
                                <td style="padding:12px 8px; text-align:center; background:#f8fafc; border-left:1px solid #e2e8f0">
                                    <strong style="font-size:15px; font-weight:900; color:#1e293b">{{ $fm->raw_total_obtained }}</strong>
                                    <div style="font-size:10px; color:#64748b">/{{ (int)$fm->raw_total_full_marks }}</div>
                                </td>
                                {{-- 100% Converted Total --}}
                                <td style="padding:12px 10px; text-align:center; background:#ecfdf5; border-left:1px solid #d1fae5">
                                    <span style="font-size:17px; font-weight:900; color:{{ $fm->total_mark >= 40 ? '#059669' : '#dc2626' }}">
                                        {{ $fm->total_mark }}
                                    </span>
                                    <div style="font-size:10.5px; color:#059669; font-weight:700">{{ round($fm->total_mark, 1) }}%</div>
                                </td>
                                {{-- Grade & GPA --}}
                                <td style="padding:12px 6px; text-align:center">
                                    <strong style="font-size:13.5px; color:#0f172a">{{ $fm->grade }}</strong><br>
                                    <small style="color:#64748b; font-weight:700">{{ number_format($fm->gpa, 2) }}</small>
                                </td>
                                {{-- Qawmi Grade --}}
                                <td style="padding:12px 8px; text-align:center">
                                    <span style="font-size:11px; font-weight:800; color:#0f766e; background:#ccfbf1; padding:3px 8px; border-radius:12px; display:inline-block; white-space:nowrap">
                                        {{ $fm->qawmi_grade['name_bn'] }}
                                    </span>
                                </td>
                                {{-- Status --}}
                                <td style="padding:12px 6px; text-align:center">
                                    @if($fm->status === 'PASS')
                                        <span style="background:#dcfce7; color:#16a34a; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">PASS</span>
                                    @else
                                        <span style="background:#fee2e2; color:#dc2626; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">FAIL</span>
                                    @endif
                                </td>
                                {{-- Published --}}
                                <td style="padding:12px 6px; text-align:center">
                                    @if($fm->is_published)
                                        <span style="color:#15803d; font-weight:800; font-size:11px"><i class="fa-solid fa-check"></i> হ্যাঁ</span>
                                    @else
                                        <span style="color:#94a3b8; font-size:11px">না</span>
                                    @endif
                                </td>
                                {{-- Actions --}}
                                <td style="padding:12px 8px; text-align:center">
                                    <div style="display:inline-flex; gap:5px; align-items:center">
                                        <button type="button" 
                                            onclick="openOverrideModal({{ $fm->id }}, '{{ addslashes($fm->student->name ?? 'Student') }}', '{{ addslashes($fm->student->student_code ?? $fm->student->student_id ?? '') }}', {{ $fm->raw_class_test ?? 'null' }}, {{ $fm->class_test_converted ?? 'null' }}, {{ $fm->raw_midterm ?? 'null' }}, {{ $fm->midterm_converted ?? 'null' }}, {{ $fm->raw_final ?? 'null' }}, {{ $fm->final_converted ?? 'null' }}, {{ $fm->attendance_converted ?? 'null' }}, '{{ addslashes($fm->remarks ?? '') }}')"
                                            style="padding:4px 8px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:6px; cursor:pointer; font-size:11px; font-weight:700"
                                            title="নম্বর সংশোধন ও ওভাররাইড">
                                            <i class="fa-solid fa-pen-to-square"></i> সংশোধন
                                        </button>
                                        <a href="{{ route('admin.students.transcript', $fm->student_id) }}" target="_blank"
                                           style="padding:4px 8px; background:#f0fdf4; border:1px solid #86efac; color:#15803d; border-radius:6px; text-decoration:none; font-size:11px; font-weight:700"
                                           title="৬-সেমিস্টার একাডেমিক ট্রান্সক্রিপ্ট">
                                            <i class="fa-solid fa-file-invoice"></i> ট্রান্সক্রিপ্ট
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Manual Mark Override Modal with Raw & Converted Marks --}}
    <div id="overrideModal" class="modal-overlay" onclick="if(event.target===this) closeOverrideModal()">
        <div class="modal-card" style="max-width:620px">
            <div style="padding:16px 20px; background:#1e40af; color:#fff; display:flex; justify-content:space-between; align-items:center">
                <h3 style="margin:0; font-size:16px; font-weight:800; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-pen-to-square"></i> রেজাল্ট বুক: আসল ও রূপান্তরিত নম্বর সংশোধন
                </h3>
                <button type="button" onclick="closeOverrideModal()" style="background:none; border:none; color:#fff; font-size:24px; line-height:1; cursor:pointer">&times;</button>
            </div>
            
            <form id="overrideForm" method="POST" action="" style="padding:20px 22px">
                @csrf
                <div style="margin-bottom:14px; background:#f8fafc; padding:10px 14px; border-radius:10px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                    <div>
                        <span style="font-size:12px; color:#64748b">শিক্ষার্থী:</span>
                        <strong id="ovStudentName" style="font-weight:800; font-size:15px; color:#0f172a; display:block"></strong>
                    </div>
                    <span id="ovStudentRoll" style="font-size:12px; color:#2563eb; font-weight:700; background:#eff6ff; padding:3px 8px; border-radius:6px; border:1px solid #bfdbfe"></span>
                </div>

                {{-- Mark Input Rows --}}
                <div style="background:#f1f5f9; padding:12px; border-radius:10px; margin-bottom:14px">
                    <div style="font-size:12px; font-weight:800; color:#334155; margin-bottom:8px">
                        <i class="fa-solid fa-sliders"></i> পরীক্ষার নম্বর (আসল ও Criteria ওজন ২০%+৩০%+৪০%+১০%=১০০%)
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px">
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#1e293b; display:block; margin-bottom:2px">
                                সিটি আসল নম্বর (/৩০)
                            </label>
                            <input type="number" step="0.1" min="0" max="30" name="class_test_obtained" id="ovCtOb" class="form-control" style="height:36px" oninput="syncFromRaw('ct')">
                        </div>
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#2563eb; display:block; margin-bottom:2px">
                                সিটি ২০% রূপান্তর (/২০)
                            </label>
                            <input type="number" step="0.1" min="0" max="20" name="class_test_converted" id="ovCtConv" class="form-control" style="height:36px; background:#eff6ff; border-color:#bfdbfe" oninput="syncFromConv('ct')">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px">
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#1e293b; display:block; margin-bottom:2px">
                                মিডটার্ম আসল নম্বর (/৫০)
                            </label>
                            <input type="number" step="0.1" min="0" max="50" name="midterm_obtained" id="ovMidOb" class="form-control" style="height:36px" oninput="syncFromRaw('mid')">
                        </div>
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#2563eb; display:block; margin-bottom:2px">
                                মিডটার্ম ৩০% রূপান্তর (/৩০)
                            </label>
                            <input type="number" step="0.1" min="0" max="30" name="midterm_converted" id="ovMidConv" class="form-control" style="height:36px; background:#eff6ff; border-color:#bfdbfe" oninput="syncFromConv('mid')">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px">
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#1e293b; display:block; margin-bottom:2px">
                                ফাইনাল আসল নম্বর (/১০০)
                            </label>
                            <input type="number" step="0.1" min="0" max="100" name="final_obtained" id="ovFinOb" class="form-control" style="height:36px" oninput="syncFromRaw('fin')">
                        </div>
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#2563eb; display:block; margin-bottom:2px">
                                ফাইনাল ৪০% রূপান্তর (/৪০)
                            </label>
                            <input type="number" step="0.1" min="0" max="40" name="final_converted" id="ovFinConv" class="form-control" style="height:36px; background:#eff6ff; border-color:#bfdbfe" oninput="syncFromConv('fin')">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px">
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#065f46; display:block; margin-bottom:2px">
                                উপস্থিতি নম্বর (/১০) [১০%]
                            </label>
                            <input type="number" step="0.1" min="0" max="10" name="attendance_converted" id="ovAtt" class="form-control" style="height:36px; background:#f0fdf4; border-color:#bbf7d0" oninput="recalcLive()">
                        </div>
                        <div style="display:flex; align-items:center; font-size:12px; color:#64748b; padding-top:16px">
                            <span>* স্বয়ংক্রিয় রূপান্তর কার্যকর হবে</span>
                        </div>
                    </div>
                </div>

                {{-- Live Calculation Display --}}
                <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:10px 14px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
                    <div>
                        <div style="font-size:11px; color:#065f46; font-weight:700">মোট প্রাপ্ত আসল নম্বর:</div>
                        <div style="font-size:15px; font-weight:900; color:#064e3b"><span id="ovLiveRawTotal">0</span> / ১৯০</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#065f46; font-weight:700">Criteria ১০০% এ মোট:</div>
                        <div style="font-size:16px; font-weight:900; color:#047857"><span id="ovLiveConvertedTotal">0</span> / ১০০</div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#065f46; font-weight:700">সম্ভাব্য গ্রেড ও জিপিএ:</div>
                        <div style="font-size:14px; font-weight:900; color:#1d4ed8"><span id="ovLiveGrade">F (0.00)</span></div>
                    </div>
                </div>

                <div style="margin-bottom:18px">
                    <label style="font-size:12.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px">সংশোধনের বিবরণ ও কারণ (Audit Remarks)</label>
                    <textarea name="remarks" id="ovRemarks" rows="2" class="form-control" placeholder="যেমন: খাতা পুনর্মূল্যায়নে প্রাপ্ত নম্বর আপডেট করা হলো..."></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px">
                    <button type="button" onclick="closeOverrideModal()" class="btn btn-secondary" style="padding:8px 18px">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="padding:8px 22px; font-weight:800; background:#1e40af; border-color:#1e40af">
                        <i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ ও মেধা পুনঃনির্ধারণ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function syncFromRaw(type) {
            if (type === 'ct') {
                const raw = parseFloat(document.getElementById('ovCtOb').value) || 0;
                document.getElementById('ovCtConv').value = ((raw / 30) * 20).toFixed(2);
            } else if (type === 'mid') {
                const raw = parseFloat(document.getElementById('ovMidOb').value) || 0;
                document.getElementById('ovMidConv').value = ((raw / 50) * 30).toFixed(2);
            } else if (type === 'fin') {
                const raw = parseFloat(document.getElementById('ovFinOb').value) || 0;
                document.getElementById('ovFinConv').value = ((raw / 100) * 40).toFixed(2);
            }
            recalcLive();
        }

        function syncFromConv(type) {
            if (type === 'ct') {
                const conv = parseFloat(document.getElementById('ovCtConv').value) || 0;
                document.getElementById('ovCtOb').value = ((conv / 20) * 30).toFixed(1);
            } else if (type === 'mid') {
                const conv = parseFloat(document.getElementById('ovMidConv').value) || 0;
                document.getElementById('ovMidOb').value = ((conv / 30) * 50).toFixed(1);
            } else if (type === 'fin') {
                const conv = parseFloat(document.getElementById('ovFinConv').value) || 0;
                document.getElementById('ovFinOb').value = ((conv / 40) * 100).toFixed(1);
            }
            recalcLive();
        }

        function recalcLive() {
            const ctOb   = parseFloat(document.getElementById('ovCtOb').value) || 0;
            const midOb  = parseFloat(document.getElementById('ovMidOb').value) || 0;
            const finOb  = parseFloat(document.getElementById('ovFinOb').value) || 0;
            const att    = parseFloat(document.getElementById('ovAtt').value) || 0;

            const ctConv  = parseFloat(document.getElementById('ovCtConv').value) || 0;
            const midConv = parseFloat(document.getElementById('ovMidConv').value) || 0;
            const finConv = parseFloat(document.getElementById('ovFinConv').value) || 0;

            const rawTotal = (ctOb + midOb + finOb + att).toFixed(1);
            const convTotal = (ctConv + midConv + finConv + att).toFixed(2);

            document.getElementById('ovLiveRawTotal').textContent = rawTotal;
            document.getElementById('ovLiveConvertedTotal').textContent = convTotal;

            const num = parseFloat(convTotal);
            let grade = 'F', gpa = '0.00';
            if (num >= 80) { grade = 'A+'; gpa = '5.00'; }
            else if (num >= 70) { grade = 'A';  gpa = '4.00'; }
            else if (num >= 60) { grade = 'A-'; gpa = '3.50'; }
            else if (num >= 50) { grade = 'B';  gpa = '3.00'; }
            else if (num >= 40) { grade = 'C';  gpa = '2.00'; }

            document.getElementById('ovLiveGrade').textContent = `${grade} (${gpa})`;
        }

        window.openOverrideModal = function(fmId, name, roll, ctOb, ctConv, midOb, midConv, finOb, finConv, att, remarks) {
            document.getElementById('ovStudentName').textContent = name;
            document.getElementById('ovStudentRoll').textContent = 'রোল: ' + (roll || '—');
            
            document.getElementById('ovCtOb').value = ctOb !== null ? ctOb : (ctConv !== null ? ((ctConv / 20) * 30).toFixed(1) : '');
            document.getElementById('ovCtConv').value = ctConv !== null ? ctConv : '';
            
            document.getElementById('ovMidOb').value = midOb !== null ? midOb : (midConv !== null ? ((midConv / 30) * 50).toFixed(1) : '');
            document.getElementById('ovMidConv').value = midConv !== null ? midConv : '';
            
            document.getElementById('ovFinOb').value = finOb !== null ? finOb : (finConv !== null ? ((finConv / 40) * 100).toFixed(1) : '');
            document.getElementById('ovFinConv').value = finConv !== null ? finConv : '';
            
            document.getElementById('ovAtt').value = att !== null ? att : '';
            document.getElementById('ovRemarks').value = remarks || '';

            recalcLive();

            const form = document.getElementById('overrideForm');
            if (form) {
                form.action = `{{ url('admin/result-book') }}/${fmId}/override`;
            }

            const m = document.getElementById('overrideModal');
            if (m) {
                m.classList.add('open', 'is-active', 'show');
                m.style.setProperty('display', 'flex', 'important');
                m.style.setProperty('opacity', '1', 'important');
                m.style.setProperty('pointer-events', 'all', 'important');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeOverrideModal = function() {
            const m = document.getElementById('overrideModal');
            if (m) {
                m.classList.remove('open', 'is-active', 'show');
                m.style.setProperty('display', 'none', 'important');
                m.style.setProperty('opacity', '0', 'important');
                m.style.setProperty('pointer-events', 'none', 'important');
                document.body.style.overflow = '';
            }
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeOverrideModal();
            }
        });
    </script>
</x-admin-layout>
