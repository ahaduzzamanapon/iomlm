<x-admin-layout>
    <x-slot name="title">Result Management (ফলাফল ও মার্কশীট ব্যবস্থাপনা)</x-slot>

    <style>
        .rm-wrapper {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .rm-header {
            background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);
            border-radius: 16px;
            padding: 22px 26px;
            color: #fff;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 10px 25px -5px rgba(4, 120, 87, 0.3);
        }
        .nav-tabs-wrapper {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 2px;
        }
        .nav-tab-btn {
            padding: 10px 20px;
            font-size: 13.5px;
            font-weight: 800;
            color: #64748b;
            text-decoration: none;
            border-radius: 10px 10px 0 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
            border-bottom: none;
            transition: all 0.2s;
        }
        .nav-tab-btn:hover {
            color: #047857;
            background: #f0fdf4;
        }
        .nav-tab-btn.active {
            color: #064e3b;
            background: #fff;
            border-color: #cbd5e1 #cbd5e1 #fff;
            box-shadow: 0 -2px 6px rgba(0,0,0,0.03);
            margin-bottom: -2px;
            border-top: 3px solid #047857;
        }
        .filter-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }
        .kpi-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
        }
        .tabulation-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }
        .tabulation-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .tabulation-table th {
            background: #f8fafc;
            color: #1e293b;
            font-weight: 800;
            padding: 12px 10px;
            border-bottom: 2px solid #cbd5e1;
            border-right: 1px solid #f1f5f9;
            text-align: center;
            white-space: nowrap;
        }
        .tabulation-table td {
            padding: 11px 10px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f8fafc;
            text-align: center;
            vertical-align: middle;
        }
        .tabulation-table tr:hover {
            background: #f8fafc;
        }
        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 52px;
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: 900;
            font-size: 12.5px;
        }
        .rank-1 {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        .rank-2 {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #475569;
            border: 1px solid #94a3b8;
        }
        .rank-3 {
            background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
            color: #9a3412;
            border: 1px solid #ea580c;
        }
        .rank-other {
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        /* Modal Styles */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
        }
        .modal-content-box {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 820px;
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        /* Marksheet Letterhead */
        .marksheet-sheet {
            padding: 36px 40px;
            background: #fff;
            font-family: 'Kalpurush', sans-serif;
            color: #0f172a;
        }

        @media print {
            .no-print, .no-print * {
                display: none !important;
            }
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .modal-backdrop {
                position: static !important;
                background: transparent !important;
                display: block !important;
                padding: 0 !important;
            }
            .modal-content-box {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .marksheet-sheet {
                padding: 15px !important;
            }
            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            .print-table th, .print-table td {
                border: 1px solid #000 !important;
            }
        }
    </style>

    <div class="rm-wrapper">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px 18px; border-radius:10px; margin-bottom:18px; font-weight:700; display:flex; align-items:center; gap:8px" class="no-print">
                <i class="fa-solid fa-circle-check" style="font-size:16px"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2; border-left:4px solid #ef4444; color:#991b1b; padding:14px 18px; border-radius:10px; margin-bottom:18px; font-weight:700; display:flex; align-items:center; gap:8px" class="no-print">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:16px"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="rm-header no-print">
            <div>
                <h1 style="margin:0 0 6px; font-size:23px; font-weight:900; display:flex; align-items:center; gap:10px">
                    <i class="fa-solid fa-square-poll-vertical" style="color:#a7f3d0"></i> Result Management (ফলাফল ও মার্কশীট ব্যবস্থাপনা)
                </h1>
                <p style="margin:0; font-size:13px; color:#d1fae5">
                    সেমিস্টারভিত্তিক টেবুলেশন শীট, ম্যানুয়াল মার্কিং (তামরিন/এসাইনমেন্ট), একক ও সামগ্রিক মেধা তালিকা এবং অফিশিয়াল নম্বরপত্র
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center">
                {{-- Publish / Unpublish Toggle Form --}}
                @if($selectedBatch)
                <form method="POST" action="{{ route('admin.result-book.publish-toggle') }}" style="display:inline-block">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                    @if($isSemesterBased && $selectedSemester)
                        <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                    @endif
                    @if($isBatchPublished)
                        <button type="submit" onclick="return confirm('আপনি কি নিশ্চিত যে এই ব্যাচের সেমিস্টার ফলাফল অপ্রকাশিত (Unpublish) করতে চান?')"
                                style="background:#dc2626; border:1px solid #b91c1c; color:#fff; padding:9px 16px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(220,38,38,0.3)">
                            <i class="fa-solid fa-eye-slash"></i> ফলাফল অপ্রকাশিত করুন
                        </button>
                    @else
                        <button type="submit" onclick="return confirm('আপনি কি এই ব্যাচের সেমিস্টার ফলাফল প্রকাশ (Publish) করতে চান? শিক্ষার্থীরা তাদের ড্যাশবোর্ড থেকে নম্বর ও মেধা স্থান দেখতে পারবে।')"
                                style="background:#2563eb; border:1px solid #1d4ed8; color:#fff; padding:9px 16px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(37,99,235,0.3)">
                            <i class="fa-solid fa-bullhorn"></i> ফলাফল প্রকাশ করুন (Publish)
                        </button>
                    @endif
                </form>
                @endif
            </div>
        </div>

        {{-- Nav Tabs --}}
        <div class="nav-tabs-wrapper no-print">
            <a href="{{ route('admin.result-book.index', ['tab' => 'tabulation', 'batch_id' => $selectedBatch?->id, 'semester_id' => $selectedSemester?->id, 'exam_type' => $examType]) }}" 
               class="nav-tab-btn {{ $tab === 'tabulation' ? 'active' : '' }}">
                <i class="fa-solid fa-trophy" style="color:{{ $tab === 'tabulation' ? '#047857' : '#94a3b8' }}"></i>
                ১. সেমিস্টার টেবুলেশন ও মেধা তালিকা (Tabulation & Merit)
            </a>
            <a href="{{ route('admin.result-book.index', ['tab' => 'manual_marking', 'batch_id' => $selectedBatch?->id, 'semester_id' => $selectedSemester?->id]) }}" 
               class="nav-tab-btn {{ $tab === 'manual_marking' ? 'active' : '' }}">
                <i class="fa-solid fa-pen-ruler" style="color:{{ $tab === 'manual_marking' ? '#047857' : '#94a3b8' }}"></i>
                ২. ম্যানুয়াল মার্কিং (তামরিন / এসাইনমেন্ট ও এটেন্ডেন্স)
            </a>
            <a href="{{ route('admin.result-book.index', ['tab' => 'batch_merit', 'batch_id' => $selectedBatch?->id]) }}" 
               class="nav-tab-btn {{ $tab === 'batch_merit' ? 'active' : '' }}">
                <i class="fa-solid fa-graduation-cap" style="color:{{ $tab === 'batch_merit' ? '#047857' : '#94a3b8' }}"></i>
                ৩. ৬-সেমিস্টার সামগ্রিক মেধা তালিকা (Combined CGPA)
            </a>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: SEMESTER TABULATION & MERIT LIST                          --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        @if($tab === 'tabulation')
            {{-- Filter Section --}}
            <div class="filter-card no-print">
                <form method="GET" action="{{ route('admin.result-book.index') }}" id="resultFilterForm" style="display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end">
                    <input type="hidden" name="tab" value="tabulation">

                    {{-- Batch Select --}}
                    <div style="flex:1; min-width:240px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">
                            <i class="fa-solid fa-users" style="color:#059669"></i> ব্যাচ নির্বাচন করুন (Batch)
                        </label>
                        <select name="batch_id" class="form-control" style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="this.form.submit()">
                            <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ ($selectedBatch && $selectedBatch->id == $b->id) ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->course->name ?? 'কোর্স' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester Select (Conditional: Only for Semester Based Courses) --}}
                    @if($isSemesterBased)
                    <div style="flex:1; min-width:180px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">
                            <i class="fa-solid fa-layer-group" style="color:#059669"></i> সেমিস্টার (Semester)
                        </label>
                        <select name="semester_id" class="form-control" style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="this.form.submit()">
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}" {{ ($selectedSemester && $selectedSemester->id == $sem->id) ? 'selected' : '' }}>
                                    {{ $sem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div style="min-width:180px">
                        <label style="font-size:13px; font-weight:800; color:#64748b; margin-bottom:6px; display:block">
                            কোর্সের ধরন
                        </label>
                        <div style="height:42px; display:flex; align-items:center; padding:0 14px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:10px; font-size:12.5px; font-weight:700; color:#475569">
                            <i class="fa-solid fa-book-open" style="margin-right:7px; color:#059669"></i> বিষয়ভিত্তিক একক কোর্স
                        </div>
                    </div>
                    @endif

                    {{-- Exam Type --}}
                    <div style="min-width:220px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">
                            <i class="fa-solid fa-calendar-check" style="color:#059669"></i> পরীক্ষার ধরন (Exam Type)
                        </label>
                        <select name="exam_type" class="form-control" style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="this.form.submit()">
                            <option value="FINAL" {{ $examType === 'FINAL' ? 'selected' : '' }}>🎯 সেমিস্টার ফাইনাল পরীক্ষা (Final Exam)</option>
                            <option value="MIDTERM" {{ $examType === 'MIDTERM' ? 'selected' : '' }}>📝 মিডটার্ম পরীক্ষা (Midterm Exam)</option>
                            <option value="QUIZ" {{ $examType === 'QUIZ' ? 'selected' : '' }}>⚡ ক্লাস টেস্ট / সিটি (Class Test)</option>
                            <option value="ALL" {{ $examType === 'ALL' ? 'selected' : '' }}>📜 পূর্ণাঙ্গ সেমিস্টার ফলাফল (১০০% সমন্বিত)</option>
                        </select>
                    </div>

                    {{-- Search Box --}}
                    <div style="flex:1; min-width:190px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">
                            শিক্ষার্থী রোল বা নাম
                        </label>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="রোল বা নাম দিয়ে খুঁজুন..." style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif">
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary" style="height:42px; padding:0 18px; font-weight:800; border-radius:10px; background:#047857; border-color:#047857">
                            <i class="fa-solid fa-filter"></i> ফিল্টার
                        </button>
                    </div>
                </form>
            </div>

            {{-- Summary KPI Cards --}}
            <div class="kpi-grid no-print">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#eff6ff; color:#2563eb">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:#64748b">মোট পরীক্ষার্থী</div>
                        <div style="font-size:19px; font-weight:900; color:#0f172a">{{ $summary['total'] }} জন</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#f0fdf4; color:#16a34a">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:#64748b">মোট বিষয় সংখ্যা</div>
                        <div style="font-size:19px; font-weight:900; color:#16a34a">{{ $subjects->count() }} টি</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#ecfdf5; color:#059669">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:#64748b">কৃতকার্য (পাস)</div>
                        <div style="font-size:19px; font-weight:900; color:#059669">{{ $summary['passed'] }} জন ({{ $summary['pass_rate'] }}%)</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#fef2f2; color:#dc2626">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:#64748b">অকৃতকার্য (ফেল)</div>
                        <div style="font-size:19px; font-weight:900; color:#dc2626">{{ $summary['failed'] }} জন</div>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#fefce8; color:#ca8a04">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:#64748b">সর্বোচ্চ নম্বর</div>
                        <div style="font-size:19px; font-weight:900; color:#ca8a04">{{ $summary['highest_score'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Tabulation Sheet & Merit List Table --}}
            <div class="tabulation-card">
                <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
                    <div>
                        <h3 style="margin:0; font-size:16px; font-weight:900; color:#0f172a; display:flex; align-items:center; gap:8px">
                            <i class="fa-solid fa-table-list" style="color:#059669"></i>
                            @if($isSemesterBased)
                                {{ $selectedBatch->name ?? '' }} &middot; {{ $selectedSemester->name ?? '' }} &middot;
                            @else
                                {{ $selectedBatch->name ?? '' }} &middot;
                            @endif
                            @php
                                $examNames = [
                                    'FINAL' => 'সেমিস্টার ফাইনাল পরীক্ষা',
                                    'MIDTERM' => 'মিডটার্ম পরীক্ষা',
                                    'QUIZ' => 'ক্লাস টেস্ট পরীক্ষা',
                                    'ALL' => 'পূর্ণাঙ্গ সেমিস্টার সমন্বিত ফলাফল',
                                ];
                            @endphp
                            {{ $examNames[$examType] ?? 'পরীক্ষা' }} টেবুলেশন শীট ও মেধা তালিকা
                        </h3>
                        <div style="font-size:12px; color:#64748b; margin-top:3px">
                            স্ট্যাটাস: 
                            @if($isBatchPublished)
                                <span class="badge" style="background:#ecfdf5; color:#065f46; font-weight:800; padding:2px 8px">
                                    <i class="fa-solid fa-circle-check"></i> ফলাফল প্রকাশিত (Published)
                                </span>
                            @else
                                <span class="badge" style="background:#fffbeb; color:#b45309; font-weight:800; padding:2px 8px; border:1px solid #fde68a">
                                    <i class="fa-solid fa-clock"></i> ফলাফল অপ্রকাশিত (Unpublished)
                                </span>
                            @endif
                            &middot; সর্বোচ্চ জিপিএ ও মোট প্রাপ্ত নম্বরের ক্রমানুসারে তালিকাভুক্ত
                        </div>
                    </div>
                    <div class="no-print">
                        <span class="badge" style="background:#eff6ff; color:#1e40af; font-size:12px; font-weight:700; padding:5px 12px; border:1px solid #bfdbfe">
                            {{ $rankedStudents->count() }} জন শিক্ষার্থী তালিকাভুক্ত
                        </span>
                    </div>
                </div>

                @if($rankedStudents->isEmpty())
                    <div style="text-align:center; padding:50px 20px; color:#64748b">
                        <i class="fa-solid fa-folder-open" style="font-size:42px; color:#cbd5e1; margin-bottom:12px; display:block"></i>
                        <strong style="font-size:16px; color:#1e293b; display:block; margin-bottom:4px">এই ব্যাচ বা সেমিস্টারে কোনো ফলাফল পাওয়া যায়নি</strong>
                        <span style="font-size:13px; color:#64748b">অন্য কোনো ব্যাচ বা সেমিস্টার নির্বাচন করুন।</span>
                    </div>
                @else
                    <div style="overflow-x:auto">
                        <table class="tabulation-table print-table">
                            <thead>
                                <tr>
                                    <th style="width:70px">মেধাক্রম</th>
                                    <th style="text-align:left; min-width:160px">রোল ও শিক্ষার্থী</th>
                                    {{-- All subjects of this semester side-by-side --}}
                                    @foreach($subjects as $sub)
                                        <th style="min-width:120px">
                                            <div style="font-size:12.5px; font-weight:800; color:#0f172a">{{ $sub->name }}</div>
                                            <small style="font-size:10.5px; color:#64748b; font-weight:600">{{ $sub->code ?: 'SUB-'.$sub->id }}</small>
                                        </th>
                                    @endforeach
                                    <th style="min-width:105px">সর্বমোট নম্বর</th>
                                    <th style="width:75px">শতকরা</th>
                                    <th style="min-width:105px; background:#ecfdf5; color:#064e3b">সেমিস্টার GPA</th>
                                    <th style="min-width:95px">কওমি মান</th>
                                    <th style="width:80px">ফলাফল</th>
                                    <th style="min-width:140px" class="no-print">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankedStudents as $rs)
                                @php
                                    $pos = $rs['merit_position'];
                                    $rankClass = match($pos) {
                                        1 => 'rank-1',
                                        2 => 'rank-2',
                                        3 => 'rank-3',
                                        default => 'rank-other',
                                    };
                                    $medal = match($pos) {
                                        1 => '🥇 ',
                                        2 => '🥈 ',
                                        3 => '🥉 ',
                                        default => '',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <span class="rank-badge {{ $rankClass }}">
                                            {{ $medal }}{{ $rs['merit_rank_bengali'] }}
                                        </span>
                                    </td>
                                    <td style="text-align:left">
                                        <strong style="color:#0f172a; font-size:13px; display:block">{{ $rs['student_name'] }}</strong>
                                        <span style="font-size:11.5px; color:#64748b; font-family:monospace; font-weight:600">রোল: {{ $rs['student_roll'] }}</span>
                                    </td>

                                    {{-- Marks for each subject --}}
                                    @foreach($subjects as $sub)
                                    @php
                                        $sm = $rs['subject_marks'][$sub->id] ?? null;
                                    @endphp
                                    <td>
                                        @if($sm)
                                            <div style="font-size:13.5px; font-weight:800; color:#0f172a">
                                                {{ $sm['obtained'] }}
                                                <small style="font-size:10.5px; color:#64748b; font-weight:normal">/ {{ $sm['full'] }}</small>
                                            </div>
                                            <div style="display:flex; align-items:center; justify-content:center; gap:4px; margin-top:2px">
                                                <span style="font-size:10.5px; font-weight:800; color:{{ $sm['grade'] === 'F' ? '#dc2626' : '#15803d' }}">
                                                    {{ $sm['grade'] }}
                                                </span>
                                                <span style="font-size:10px; color:#64748b">({{ $sm['gpa'] }})</span>
                                            </div>
                                        @else
                                            <span style="color:#94a3b8">—</span>
                                        @endif
                                    </td>
                                    @endforeach

                                    {{-- Total Marks --}}
                                    <td>
                                        <strong style="font-size:13.5px; color:#0f172a">{{ $rs['total_obtained'] }}</strong>
                                        <span style="font-size:11px; color:#64748b">/ {{ $rs['total_full'] }}</span>
                                    </td>

                                    {{-- Percentage --}}
                                    <td>
                                        <strong style="color:#047857">{{ $rs['percentage'] }}%</strong>
                                    </td>

                                    {{-- GPA & Grade --}}
                                    <td style="background:#f0fdf4">
                                        <div style="font-size:14.5px; font-weight:900; color:#065f46">
                                            {{ $rs['sgpa'] }}
                                        </div>
                                        <span class="badge" style="background:#dcfce7; color:#166534; font-size:10.5px; font-weight:800; padding:1px 6px">
                                            গ্রেড: {{ $rs['grade'] }}
                                        </span>
                                    </td>

                                    {{-- Qawmi Grade --}}
                                    <td>
                                        <span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; font-weight:700">
                                            {{ $rs['qawmi'] }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        @if($rs['status'] === 'PASS')
                                            <span class="badge badge-active" style="font-size:11px; font-weight:800">উত্তীর্ণ</span>
                                        @else
                                            <span class="badge badge-danger" style="font-size:11px; font-weight:800">অনুত্তীর্ণ</span>
                                        @endif
                                    </td>

                                    {{-- Actions: Marksheet & Quick Edit --}}
                                    <td class="no-print" style="white-space:nowrap">
                                        <button type="button" class="btn btn-sm"
                                                onclick='openStudentMarksheet(@json($rs), @json($selectedBatch), @json($selectedSemester), "{{ $examType }}")'
                                                style="background:#047857; color:#fff; font-weight:800; border-radius:8px; padding:5px 10px; font-size:12px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-file-invoice"></i> মার্কশীট
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline"
                                                onclick='openEditMarkModal(@json($rs), @json($subjects))'
                                                style="font-weight:700; border-radius:8px; padding:5px 9px; font-size:12px; margin-left:4px; display:inline-flex; align-items:center; gap:4px"
                                                title="যেকোনো মার্ক ম্যানুয়ালি পরিবর্তন করুন">
                                            <i class="fa-solid fa-pen"></i> এডিট
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: MANUAL MARKING (তামরিন, তাজবীদ, DNS ও এটেন্ডেন্স)            --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        @elseif($tab === 'manual_marking')
            <div class="filter-card no-print">
                <form method="GET" action="{{ route('admin.result-book.index') }}" style="display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end">
                    <input type="hidden" name="tab" value="manual_marking">

                    {{-- Batch --}}
                    <div style="flex:1; min-width:220px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">ব্যাচ নির্বাচন করুন</label>
                        <select name="batch_id" class="form-control" style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="this.form.submit()">
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ ($selectedBatch && $selectedBatch->id == $b->id) ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester --}}
                    @if($isSemesterBased)
                    <div style="flex:1; min-width:180px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">সেমিস্টার</label>
                        <select name="semester_id" class="form-control" style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="this.form.submit()">
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}" {{ ($selectedSemester && $selectedSemester->id == $sem->id) ? 'selected' : '' }}>
                                    {{ $sem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Subject --}}
                    <div style="flex:1; min-width:220px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">বিষয় (Subject)</label>
                        <select name="subject_id" class="form-control" style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="this.form.submit()">
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}" {{ ($selectedSubject && $selectedSubject->id == $sub->id) ? 'selected' : '' }}>
                                    {{ $sub->name }} ({{ $sub->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary" style="height:42px; padding:0 18px; font-weight:800; border-radius:10px; background:#047857; border-color:#047857">
                            <i class="fa-solid fa-filter"></i> লোড করুন
                        </button>
                    </div>
                </form>
            </div>

            {{-- Auto Attendance Action Card --}}
            @if($selectedBatch && $selectedSubject)
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:14px; padding:14px 20px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px" class="no-print">
                <div>
                    <strong style="color:#1e40af; font-size:14px; display:block">
                        <i class="fa-solid fa-calendar-check"></i> ক্লাসের উপস্থিতি থেকে স্বয়ংক্রিয় এটেন্ডেন্স মার্ক জেনারেট (১০ নম্বর)
                    </strong>
                    <span style="font-size:12px; color:#3b82f6">
                        সমাপ্ত ক্লাস সেশনসমূহে শিক্ষার্থীদের উপস্থিতির শতকরা হার অনুযায়ী স্বয়ংক্রিয়ভাবে নম্বর যুক্ত হবে।
                    </span>
                </div>
                <form method="POST" action="{{ route('admin.result-book.auto-attendance') }}">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    @if($isSemesterBased && $selectedSemester)
                        <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                    @endif
                    <button type="submit" onclick="return confirm('আপনি কি এই বিষয়ের সকল শিক্ষার্থীর সমাপ্ত সেশনের উপস্থিতি হিসেব করে নম্বর আপডেট করতে চান?')"
                            style="background:#2563eb; color:#fff; border:none; padding:8px 16px; border-radius:8px; font-weight:800; font-size:12.5px; cursor:pointer; display:inline-flex; align-items:center; gap:6px">
                        <i class="fa-solid fa-bolt"></i> অটো এটেন্ডেন্স জেনারেট করুন
                    </button>
                </form>
            </div>

            {{-- Bulk Manual Marking Form --}}
            <form method="POST" action="{{ route('admin.result-book.manual-marks-bulk') }}">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                @if($isSemesterBased && $selectedSemester)
                    <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">
                @endif

                <div class="tabulation-card">
                    <div style="padding:14px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                        <h3 style="margin:0; font-size:15px; font-weight:800; color:#0f172a">
                            <i class="fa-solid fa-pen-to-square" style="color:#047857"></i>
                            ম্যানুয়াল মার্কিং স্প্রেডশীট: {{ $selectedSubject->name }} ({{ $selectedBatch->name }})
                        </h3>
                        <span style="font-size:12px; color:#64748b">
                            তামরিন / এসাইনমেন্ট ও উপস্থিতি নম্বর ইনপুট দিয়ে নিচে সংরক্ষণ করুন
                        </span>
                    </div>

                    @if($manualMarkingList->isEmpty())
                        <div style="text-align:center; padding:40px 20px; color:#64748b">
                            এই বিষয় এবং ব্যাচে কোনো শিক্ষার্থীর ফাইনাল মার্ক রেকর্ড পাওয়া যায়নি।
                        </div>
                    @else
                        <div style="overflow-x:auto">
                            <table class="tabulation-table">
                                <thead>
                                    <tr>
                                        <th style="width:60px">#</th>
                                        <th style="text-align:left; min-width:160px">রোল ও শিক্ষার্থী</th>
                                        <th style="min-width:140px">তামরিন / এসাইনমেন্ট নম্বর</th>
                                        <th style="min-width:110px">উপস্থিতি নম্বর (/১০)</th>
                                        <th style="min-width:110px">বর্তমান মোট নম্বর</th>
                                        <th style="min-width:140px">মন্তব্য</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($manualMarkingList as $idx => $fm)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td style="text-align:left">
                                            <strong style="color:#0f172a; font-size:13px">{{ $fm->student->name ?? '—' }}</strong><br>
                                            <small style="color:#64748b; font-family:monospace">রোল: {{ $fm->student->student_code ?? $fm->student->student_id ?? '—' }}</small>
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="100" name="marks[{{ $fm->id }}][tamrin_mark]" 
                                                   value="{{ $fm->tamrin_mark }}" class="form-control" placeholder="—" style="width:110px; margin:0 auto; text-align:center; font-weight:800; height:36px; border-radius:8px">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" min="0" max="10" name="marks[{{ $fm->id }}][attendance_converted]" 
                                                   value="{{ $fm->attendance_converted }}" class="form-control" placeholder="০-১০" style="width:90px; margin:0 auto; text-align:center; font-weight:800; height:36px; border-radius:8px">
                                        </td>
                                        <td>
                                            <strong style="font-size:14px; color:#047857">{{ $fm->total_mark }}</strong>
                                            <small style="color:#64748b">({{ $fm->grade }})</small>
                                        </td>
                                        <td>
                                            <input type="text" name="marks[{{ $fm->id }}][remarks]" value="{{ $fm->remarks }}" 
                                                   class="form-control" placeholder="মন্তব্য..." style="height:36px; border-radius:8px; font-size:12px">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="padding:16px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; text-align:right">
                            <button type="submit" class="btn btn-primary" style="padding:10px 24px; font-weight:800; border-radius:10px; background:#047857; border-color:#047857">
                                <i class="fa-solid fa-floppy-disk"></i> সকল ম্যানুয়াল মার্ক সংরক্ষণ করুন (Save All)
                            </button>
                        </div>
                    @endif
                </div>
            </form>
            @endif

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: 6-SEMESTER COMBINED BATCH MERIT                            --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        @elseif($tab === 'batch_merit')
            <div class="filter-card no-print">
                <form method="GET" action="{{ route('admin.result-book.index') }}" style="display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end">
                    <input type="hidden" name="tab" value="batch_merit">
                    <div style="flex:1; min-width:260px">
                        <label style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:6px; display:block">ব্যাচ নির্বাচন করুন</label>
                        <select name="batch_id" class="form-control" style="height:42px; border-radius:10px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="this.form.submit()">
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ ($selectedBatch && $selectedBatch->id == $b->id) ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->course->name ?? 'কোর্স' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" style="height:42px; padding:0 20px; font-weight:800; border-radius:10px; background:#047857; border-color:#047857">
                            <i class="fa-solid fa-filter"></i> ফলাফল দেখুন
                        </button>
                    </div>
                </form>
            </div>

            <div class="tabulation-card">
                <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                    <div>
                        <h3 style="margin:0; font-size:16px; font-weight:900; color:#0f172a">
                            <i class="fa-solid fa-graduation-cap" style="color:#047857"></i>
                            {{ $selectedBatch->name ?? '' }} &middot; ৬-সেমিস্টার সামগ্রিক মেধা তালিকা ও ফলাফল বিবরণী
                        </h3>
                        <div style="font-size:12px; color:#64748b; margin-top:2px">
                            সকল সেমিস্টারের অর্জিত সিজিপিএ (CGPA) ও মোট নম্বরের ভিত্তিতে ১ম, ২য়, ৩য় মেধাক্রম
                        </div>
                    </div>
                    <div class="no-print">
                        <button type="button" onclick="window.print()" class="btn btn-outline btn-sm" style="font-weight:700">
                            <i class="fa-solid fa-print"></i> প্রিন্ট করুন
                        </button>
                    </div>
                </div>

                @if($batchMeritList->isEmpty())
                    <div style="text-align:center; padding:50px 20px; color:#64748b">
                        এই ব্যাচের জন্য কোনো ফলাফল রেকর্ড পাওয়া যায়নি।
                    </div>
                @else
                    <div style="overflow-x:auto">
                        <table class="tabulation-table print-table">
                            <thead>
                                <tr>
                                    <th style="width:70px">মেধাক্রম</th>
                                    <th style="text-align:left; min-width:160px">রোল ও শিক্ষার্থী</th>
                                    @foreach($semesters as $sem)
                                        <th style="min-width:95px">
                                            {{ $sem->name }}
                                            <div style="font-size:10px; color:#64748b; font-weight:normal">(SGPA)</div>
                                        </th>
                                    @endforeach
                                    <th style="min-width:90px">মোট ক্রেডিট</th>
                                    <th style="min-width:105px">মোট নম্বর</th>
                                    <th style="min-width:115px; background:#ecfdf5; color:#064e3b">চূড়ান্ত CGPA</th>
                                    <th style="min-width:100px">কওমি মান</th>
                                    <th style="width:110px" class="no-print">ট্রান্সক্রিপ্ট</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batchMeritList as $bm)
                                @php
                                    $pos = $bm['merit_position'];
                                    $rankClass = match($pos) {
                                        1 => 'rank-1',
                                        2 => 'rank-2',
                                        3 => 'rank-3',
                                        default => 'rank-other',
                                    };
                                    $medal = match($pos) {
                                        1 => '🥇 ',
                                        2 => '🥈 ',
                                        3 => '🥉 ',
                                        default => '',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <span class="rank-badge {{ $rankClass }}">
                                            {{ $medal }}{{ $bm['merit_rank_bengali'] }}
                                        </span>
                                    </td>
                                    <td style="text-align:left">
                                        <strong style="color:#0f172a; font-size:13px; display:block">{{ $bm['student_name'] }}</strong>
                                        <span style="font-size:11.5px; color:#64748b; font-family:monospace; font-weight:600">রোল: {{ $bm['student_roll'] }}</span>
                                    </td>
                                    @foreach($semesters as $sem)
                                    @php
                                        $semData = $bm['semesters_summary'][$sem->id] ?? null;
                                    @endphp
                                    <td>
                                        @if($semData && $semData['sgpa'] > 0)
                                            <strong style="color:#0f172a; font-size:13px">{{ $semData['sgpa'] }}</strong>
                                        @else
                                            <span style="color:#cbd5e1">—</span>
                                        @endif
                                    </td>
                                    @endforeach
                                    <td><strong>{{ $bm['total_credits'] }}</strong></td>
                                    <td><strong>{{ $bm['total_obtained'] }}</strong></td>
                                    <td style="background:#f0fdf4">
                                        <div style="font-size:15px; font-weight:900; color:#065f46">{{ $bm['cgpa'] }}</div>
                                        <span class="badge" style="background:#dcfce7; color:#166534; font-size:10px; font-weight:800">
                                            {{ $bm['grade'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; font-weight:700">
                                            {{ $bm['qawmi'] }}
                                        </span>
                                    </td>
                                    <td class="no-print">
                                        <a href="{{ route('admin.students.transcript', $bm['student_id']) }}" target="_blank"
                                           class="btn btn-sm btn-outline" style="font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-graduation-cap"></i> ট্রান্সক্রিপ্ট
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL 1: OFFICIAL INSTITUTIONAL MARKSHEET MODAL                   --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="modal-backdrop" id="marksheetModal">
        <div class="modal-content-box">
            {{-- Modal Top Action Bar (hidden on print) --}}
            <div class="no-print" style="padding:14px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                <span style="font-size:14px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-file-invoice" style="color:#047857"></i> প্রাতিষ্ঠানিক নম্বরপত্র (Official Mark Sheet)
                </span>
                <div style="display:flex; gap:10px; align-items:center">
                    <button type="button" onclick="window.print()" class="btn btn-primary btn-sm" style="background:#047857; border-color:#047857; font-weight:800; display:inline-flex; align-items:center; gap:6px">
                        <i class="fa-solid fa-print"></i> প্রিন্ট করুন
                    </button>
                    <button type="button" onclick="closeStudentMarksheet()" style="background:none; border:none; font-size:18px; color:#64748b; cursor:pointer; padding:4px 8px">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            {{-- Printable Marksheet Sheet --}}
            <div class="marksheet-sheet" id="marksheetPrintArea">
                {{-- Madrasah Letterhead --}}
                <div style="text-align:center; border-bottom:2px solid #064e3b; padding-bottom:14px; margin-bottom:18px">
                    <div style="font-size:22px; font-weight:900; color:#064e3b; letter-spacing:0.5px">
                        ইসলামিক অনলাইন মাদ্রাসা
                    </div>
                    <div style="font-size:13px; font-weight:700; color:#475569">
                        Islamic Online Madrasah (IOM) &middot; ঢাকা, বাংলাদেশ
                    </div>
                    <div style="display:inline-block; margin-top:10px; background:#f0fdf4; border:1px solid #047857; border-radius:20px; padding:4px 18px">
                        <span id="msExamTitle" style="font-size:13.5px; font-weight:900; color:#064e3b">
                            সেমিস্টার পরীক্ষা নম্বরপত্র
                        </span>
                    </div>
                </div>

                {{-- Student Information Box --}}
                <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:12px; padding:14px 18px; margin-bottom:18px">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px; font-size:13px">
                        <div>
                            <span style="color:#64748b; font-weight:600">শিক্ষার্থীর নাম:</span>
                            <strong id="msStudentName" style="color:#0f172a; margin-left:6px"></strong>
                        </div>
                        <div>
                            <span style="color:#64748b; font-weight:600">রোল নম্বর:</span>
                            <strong id="msStudentRoll" style="color:#0f172a; font-family:monospace; margin-left:6px"></strong>
                        </div>
                        <div>
                            <span style="color:#64748b; font-weight:600">ব্যাচ:</span>
                            <strong id="msBatchName" style="color:#0f172a; margin-left:6px"></strong>
                        </div>
                        <div id="msSemesterContainer">
                            <span style="color:#64748b; font-weight:600">সেমিস্টার:</span>
                            <strong id="msSemesterName" style="color:#0f172a; margin-left:6px"></strong>
                        </div>
                    </div>
                </div>

                {{-- All Subjects Mark Table --}}
                <div style="margin-bottom:18px">
                    <table style="width:100%; border-collapse:collapse; font-size:12px" class="print-table">
                        <thead>
                            <tr style="background:#064e3b; color:#fff">
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:65px">কোড</th>
                                <th style="padding:8px 10px; border:1px solid #047857; text-align:left">বিষয়ের নাম</th>
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:55px">ক্রেডিট</th>
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:60px">পূর্ণমান</th>
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:70px">প্রাপ্ত নম্বর</th>
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:70px">কনভার্ট</th>
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:60px">গ্রেড</th>
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:55px">জিপিএ</th>
                                <th style="padding:8px; border:1px solid #047857; text-align:center; width:65px">ফলাফল</th>
                            </tr>
                        </thead>
                        <tbody id="msSubjectsTbody"></tbody>
                        <tfoot id="msSubjectsTfoot" style="background:#f8fafc; font-weight:900"></tfoot>
                    </table>
                </div>

                {{-- Results Summary Card --}}
                <div style="border:2px solid #047857; border-radius:12px; padding:12px 18px; margin-bottom:26px; background:#f0fdf4; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px">
                    <div>
                        <div style="font-size:11px; color:#065f46; font-weight:700">মেধা স্থান (Merit Position)</div>
                        <div id="msMeritPosition" style="font-size:20px; font-weight:900; color:#064e3b"></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#065f46; font-weight:700">সেমিস্টার জিপিএ (SGPA)</div>
                        <div id="msGpa" style="font-size:20px; font-weight:900; color:#064e3b"></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#065f46; font-weight:700">কওমি মান (Qawmi Standard)</div>
                        <div id="msQawmi" style="font-size:15px; font-weight:900; color:#1e40af"></div>
                    </div>
                    <div>
                        <div style="font-size:11px; color:#065f46; font-weight:700">ফলাফল স্ট্যাটাস</div>
                        <div id="msStatus" style="font-size:15px; font-weight:900"></div>
                    </div>
                </div>

                {{-- Official Signatures --}}
                <div style="margin-top:40px; display:flex; justify-content:space-between; align-items:flex-end; padding:0 10px">
                    <div style="text-align:center; width:160px">
                        <div style="border-top:1px dashed #334155; padding-top:5px; font-size:11.5px; font-weight:700; color:#334155">
                            নিরীক্ষক / বিষয় শিক্ষক
                        </div>
                    </div>
                    <div style="text-align:center; width:120px">
                        <div style="width:70px; height:70px; margin:0 auto; border:2px dashed #047857; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#047857; font-size:9.5px; font-weight:800; text-align:center; line-height:1.2">
                            মাদরাসার<br>সিল
                        </div>
                    </div>
                    <div style="text-align:center; width:160px">
                        <div style="border-top:1px dashed #334155; padding-top:5px; font-size:11.5px; font-weight:700; color:#334155">
                            পরীক্ষা নিয়ন্ত্রক<br>
                            <small style="font-size:9.5px; color:#64748b">ইসলামিক অনলাইন মাদ্রাসা</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL 2: EDIT STUDENT MARKS MODAL                                --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="modal-backdrop" id="editMarkModal">
        <div class="modal-content-box" style="max-width:560px">
            <div style="padding:14px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                <span style="font-size:14px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-pen-to-square" style="color:#047857"></i> শিক্ষার্থী নম্বর সংশোধন / ম্যানুয়াল পরিবর্তন
                </span>
                <button type="button" onclick="closeEditMarkModal()" style="background:none; border:none; font-size:18px; color:#64748b; cursor:pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editMarkForm" method="POST" action="">
                @csrf
                <div style="padding:20px; font-size:13px">
                    <div style="background:#f1f5f9; padding:10px 14px; border-radius:10px; margin-bottom:16px">
                        <strong id="editModalStudentName" style="font-size:14px; color:#0f172a"></strong><br>
                        <span id="editModalStudentRoll" style="font-size:12px; color:#64748b; font-family:monospace"></span>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="font-weight:700; color:#1e293b; margin-bottom:4px; display:block">বিষয় নির্বাচন করুন</label>
                        <select id="editModalSubjectSelect" class="form-control" style="border-radius:8px; font-family:'Kalpurush',sans-serif; font-weight:700" onchange="onEditSubjectChange()">
                            {{-- Populated via JS --}}
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px">
                        <div>
                            <label style="font-weight:700; color:#1e293b; font-size:12px; margin-bottom:4px; display:block">সিটি প্রাপ্ত নম্বর (/৩০)</label>
                            <input type="number" step="0.1" min="0" max="30" name="class_test_obtained" id="editCtObtained" class="form-control" style="border-radius:8px">
                        </div>
                        <div>
                            <label style="font-weight:700; color:#1e293b; font-size:12px; margin-bottom:4px; display:block">মিডটার্ম প্রাপ্ত নম্বর (/৫০)</label>
                            <input type="number" step="0.1" min="0" max="50" name="midterm_obtained" id="editMidObtained" class="form-control" style="border-radius:8px">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px">
                        <div>
                            <label style="font-weight:700; color:#1e293b; font-size:12px; margin-bottom:4px; display:block">ফাইনাল প্রাপ্ত নম্বর (/১০০)</label>
                            <input type="number" step="0.1" min="0" max="100" name="final_obtained" id="editFinObtained" class="form-control" style="border-radius:8px">
                        </div>
                        <div>
                            <label style="font-weight:700; color:#1e293b; font-size:12px; margin-bottom:4px; display:block">এটেন্ডেন্স নম্বর (/১০)</label>
                            <input type="number" step="0.1" min="0" max="10" name="attendance_converted" id="editAttConverted" class="form-control" style="border-radius:8px">
                        </div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="font-weight:700; color:#1e293b; font-size:12px; margin-bottom:4px; display:block">তামরিন / এসাইনমেন্ট নম্বর</label>
                        <input type="number" step="0.1" min="0" max="100" name="tamrin_mark" id="editTamrinMark" class="form-control" style="border-radius:8px">
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="font-weight:700; color:#1e293b; font-size:12px; margin-bottom:4px; display:block">সংশোধনের কারণ / মন্তব্য</label>
                        <input type="text" name="remarks" id="editRemarks" class="form-control" placeholder="যেমন: পুনঃনিরীক্ষণ বা বিশেষ ছাড়..." style="border-radius:8px">
                    </div>
                </div>

                <div style="padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px">
                    <button type="button" onclick="closeEditMarkModal()" class="btn btn-outline" style="font-weight:700; border-radius:8px">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#047857; border-color:#047857; font-weight:800; border-radius:8px">
                        <i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentEditingStudent = null;

        window.openStudentMarksheet = function(studentData, batch, semester, examType) {
            const examLabels = {
                'FINAL': 'সেমিস্টার ফাইনাল পরীক্ষা নম্বরপত্র (Semester Final Mark Sheet)',
                'MIDTERM': 'মিডটার্ম পরীক্ষা নম্বরপত্র (Midterm Mark Sheet)',
                'QUIZ': 'ক্লাস টেস্ট নম্বরপত্র (Class Test Mark Sheet)',
                'ALL': 'পূর্ণাঙ্গ সেমিস্টার ফলাফল ও নম্বরপত্র (Combined Mark Sheet)'
            };

            document.getElementById('msExamTitle').textContent = examLabels[examType] || 'সেমিস্টার নম্বরপত্র';
            document.getElementById('msStudentName').textContent = studentData.student_name;
            document.getElementById('msStudentRoll').textContent = studentData.student_roll;
            document.getElementById('msBatchName').textContent = batch ? batch.name : '—';

            if (semester) {
                document.getElementById('msSemesterContainer').style.display = 'block';
                document.getElementById('msSemesterName').textContent = semester.name;
            } else {
                document.getElementById('msSemesterContainer').style.display = 'none';
            }

            // Summary Badges
            document.getElementById('msMeritPosition').textContent = studentData.merit_rank_bengali + ' স্থান';
            document.getElementById('msGpa').textContent = studentData.sgpa + ' (' + studentData.grade + ')';
            document.getElementById('msQawmi').textContent = studentData.qawmi;
            document.getElementById('msStatus').innerHTML = studentData.status === 'PASS'
                ? '<span style="color:#15803d">উত্তীর্ণ (PASS)</span>'
                : '<span style="color:#dc2626">অনুত্তীর্ণ (FAIL)</span>';

            // Populate Table
            const tbody = document.getElementById('msSubjectsTbody');
            tbody.innerHTML = '';

            let totalCredit = 0;
            const marksObj = studentData.subject_marks || {};
            
            Object.values(marksObj).forEach(sm => {
                totalCredit += (parseFloat(sm.credit) || 3);
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #cbd5e1';
                tr.innerHTML = `
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center; font-family:monospace; font-weight:700">${sm.code}</td>
                    <td style="padding:7px 10px; border:1px solid #cbd5e1; font-weight:700; color:#0f172a">${sm.name}</td>
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center">${sm.credit}</td>
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center">${sm.full}</td>
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center; font-weight:800; color:#047857">${sm.obtained}</td>
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center">${sm.converted}</td>
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center; font-weight:800; color:${sm.grade === 'F' ? '#dc2626' : '#15803d'}">${sm.grade}</td>
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center">${sm.gpa}</td>
                    <td style="padding:7px; border:1px solid #cbd5e1; text-align:center; font-weight:700; color:${sm.status === 'PASS' ? '#15803d' : '#dc2626'}">${sm.status === 'PASS' ? 'উত্তীর্ণ' : 'ফেল'}</td>
                `;
                tbody.appendChild(tr);
            });

            // Footer
            const tfoot = document.getElementById('msSubjectsTfoot');
            tfoot.innerHTML = `
                <tr style="border-top:2px solid #047857; background:#f0fdf4">
                    <td colspan="2" style="padding:9px 10px; border:1px solid #cbd5e1; text-align:left; font-size:12.5px; font-weight:900">সর্বমোট (Aggregate Total):</td>
                    <td style="padding:9px; border:1px solid #cbd5e1; text-align:center">${totalCredit}</td>
                    <td style="padding:9px; border:1px solid #cbd5e1; text-align:center">${studentData.total_full}</td>
                    <td style="padding:9px; border:1px solid #cbd5e1; text-align:center; font-size:13.5px; font-weight:900; color:#064e3b">${studentData.total_obtained}</td>
                    <td style="padding:9px; border:1px solid #cbd5e1; text-align:center">${studentData.percentage}%</td>
                    <td style="padding:9px; border:1px solid #cbd5e1; text-align:center; font-weight:900">${studentData.grade}</td>
                    <td style="padding:9px; border:1px solid #cbd5e1; text-align:center; font-size:13.5px; font-weight:900; color:#064e3b">${studentData.sgpa}</td>
                    <td style="padding:9px; border:1px solid #cbd5e1; text-align:center; font-weight:900; color:${studentData.status === 'PASS' ? '#15803d' : '#dc2626'}">${studentData.status === 'PASS' ? 'উত্তীর্ণ' : 'ফেল'}</td>
                </tr>
            `;

            const m = document.getElementById('marksheetModal');
            if (m) {
                m.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeStudentMarksheet = function() {
            const m = document.getElementById('marksheetModal');
            if (m) {
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        };

        window.openEditMarkModal = function(studentData, subjects) {
            currentEditingStudent = studentData;
            document.getElementById('editModalStudentName').textContent = studentData.student_name;
            document.getElementById('editModalStudentRoll').textContent = 'রোল: ' + studentData.student_roll;

            const select = document.getElementById('editModalSubjectSelect');
            select.innerHTML = '';

            const marksObj = studentData.subject_marks || {};
            Object.values(marksObj).forEach(sm => {
                const opt = document.createElement('option');
                opt.value = sm.subject_id;
                opt.textContent = sm.name + ' (' + sm.code + ')';
                select.appendChild(opt);
            });

            onEditSubjectChange();

            const m = document.getElementById('editMarkModal');
            if (m) {
                m.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        };

        window.onEditSubjectChange = function() {
            if (!currentEditingStudent) return;
            const subId = document.getElementById('editModalSubjectSelect').value;
            const sm = currentEditingStudent.subject_marks[subId];
            if (!sm) return;

            document.getElementById('editCtObtained').value = sm.raw_ct ?? '';
            document.getElementById('editMidObtained').value = sm.raw_mid ?? '';
            document.getElementById('editFinObtained').value = sm.raw_final ?? '';
            document.getElementById('editAttConverted').value = sm.att_conv ?? '';
            document.getElementById('editTamrinMark').value = sm.tamrin ?? '';

            if (sm.final_mark_id) {
                document.getElementById('editMarkForm').action = '/admin/result-book/' + sm.final_mark_id + '/override';
            }
        };

        window.closeEditMarkModal = function() {
            const m = document.getElementById('editMarkModal');
            if (m) {
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeStudentMarksheet();
                window.closeEditMarkModal();
            }
        });
    </script>
</x-admin-layout>
