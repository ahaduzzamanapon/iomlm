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
        .sm-tab-btn {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
            font-family: 'Kalpurush', sans-serif;
        }
        .sm-tab-btn:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
        }
        .sm-tab-btn.active {
            background: #1e40af !important;
            color: #fff !important;
            border-color: #1e40af !important;
            box-shadow: 0 2px 6px rgba(30,64,175,0.25);
        }
        @media print {
            body * {
                visibility: hidden !important;
            }
            #studentMarksheetPrintArea, #studentMarksheetPrintArea * {
                visibility: visible !important;
            }
            #studentMarksheetPrintArea {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                padding: 20px !important;
                background: #fff !important;
                font-family: 'Kalpurush', sans-serif !important;
            }
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
                <a href="{{ route('admin.exams.index') }}"
                   style="background:rgba(255,255,255,0.25); border:1px solid rgba(255,255,255,0.5); color:#fff; padding:10px 18px; border-radius:10px; font-weight:800; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:7px">
                    <i class="fa-solid fa-trophy" style="color:#fef08a"></i> একক পরীক্ষা মেধা তালিকা ও মার্কশীট
                </a>
                <a href="{{ route('admin.result-book.batch-merit', request()->only('batch_id')) }}"
                   style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); color:#fff; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:7px">
                    <i class="fa-solid fa-medal" style="color:#fef08a"></i> ৬-সেমিস্টার ব্যাচ মেধা তালিকা
                </a>
                <a href="{{ route('admin.final-marks.index') }}"
                   style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); color:#fff; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:7px">
                    <i class="fa-solid fa-calculator"></i> ফাইনাল মার্ক জেনারেটর
                </a>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <div style="display:flex; gap:10px; border-bottom:2px solid #e2e8f0; margin-bottom:24px; padding-bottom:2px; flex-wrap:wrap">
            <a href="{{ route('admin.result-book.index', request()->query()) }}" style="padding:10px 20px; font-size:13.5px; font-weight:800; color:#1e40af; text-decoration:none; border-radius:10px 10px 0 0; background:#eff6ff; border-bottom:3px solid #1e40af; display:inline-flex; align-items:center; gap:8px">
                <i class="fa-solid fa-book-bookmark"></i> বিষয়ভিত্তিক ফলাফল ও সংশোধন (Subject Mark Sheets)
            </a>
            <a href="{{ route('admin.exams.index') }}" style="padding:10px 20px; font-size:13.5px; font-weight:800; color:#047857; text-decoration:none; border-radius:10px 10px 0 0; background:#f0fdf4; border:1px solid #bbf7d0; display:inline-flex; align-items:center; gap:8px">
                <i class="fa-solid fa-trophy" style="color:#eab308"></i> একক পরীক্ষা মেধা তালিকা ও মার্কশীট (Single Exam Results)
            </a>
            <a href="{{ route('admin.result-book.batch-merit', request()->only('batch_id')) }}" style="padding:10px 20px; font-size:13.5px; font-weight:800; color:#64748b; text-decoration:none; border-radius:10px 10px 0 0; display:inline-flex; align-items:center; gap:8px">
                <i class="fa-solid fa-medal"></i> ৬-সেমিস্টার ব্যাচ মেধা তালিকা (Batch 6-Sem Merit)
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

        {{-- Quick Banner: Jump directly to Single Exam Merit List --}}
        <div class="card" style="margin-bottom:24px; padding:18px 24px; border-radius:16px; background:linear-gradient(135deg, #1e40af 0%, #2563eb 100%); color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; box-shadow:0 4px 15px rgba(37,99,235,0.2)">
            <div>
                <div style="font-size:17px; font-weight:900; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-trophy" style="color:#fef08a"></i> 
                    একক পরীক্ষার (সিটি, মিড, ফাইনাল) মেধা তালিকা ও মার্কশীট দেখতে চান?
                </div>
                <div style="font-size:13px; color:#dbeafe; margin-top:4px">
                    যেকোনো ব্যাচের সেমিস্টার ফাইনাল, মিডটার্ম বা সিটি পরীক্ষার সকল শিক্ষার্থীর মেধা তালিকা (১ম 🥇, ২য় 🥈, ৩য় 🥉) ও একক মার্কশীট সরাসরি দেখুন
                </div>
            </div>
            <a href="{{ route('admin.exams.index') }}" 
               style="background:#fff; color:#1e40af; padding:11px 22px; border-radius:10px; font-weight:800; font-size:13.5px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; box-shadow:0 2px 8px rgba(0,0,0,0.15)">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> সকল একক পরীক্ষার মেধা তালিকায় যান
            </a>
        </div>

        {{-- Multi-Exam Publishing Cards (CT, Mid, Final) --}}
        @if($exams->isNotEmpty())
        <div class="card" style="margin-bottom:24px; padding:18px 24px; border-radius:16px; background:#f8fafc; border:1px solid #e2e8f0">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px">
                <div>
                    <h3 style="margin:0; font-size:15px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-bullhorn" style="color:#2563eb"></i> একক পরীক্ষার তালিকা ও সরাসরি মেধা তালিকা (Single Exam Merit & Results)
                    </h3>
                    <div style="font-size:12px; color:#64748b; margin-top:2px">
                        যেকোনো পরীক্ষার মেধা তালিকা (১ম, ২য়, ৩য়) ও শিক্ষার্থীদের মার্কশীট দেখতে "মেধা তালিকা ও মার্কশীট" বাটনে ক্লিক করুন
                    </div>
                </div>
                <a href="{{ route('admin.exams.index') }}" style="font-size:13px; font-weight:800; color:#1e40af; text-decoration:none; display:inline-flex; align-items:center; gap:5px">
                    পরীক্ষার সম্পূর্ণ তালিকা ফিল্টার করুন <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap:12px">
                @foreach($exams as $ex)
                <div class="exam-publish-card" style="border:1px solid #cbd5e1; border-radius:12px; padding:12px 14px; background:#fff; display:flex; justify-content:space-between; align-items:center; gap:10px">
                    <div>
                        <div style="display:flex; gap:6px; align-items:center; margin-bottom:4px">
                            <span class="badge badge-secondary no-dot" style="font-size:11px">
                                {{ $ex->type }} (পূর্ণমান: {{ $ex->full_marks }})
                            </span>
                            @if($ex->is_result_published)
                                <span style="font-size:11px; color:#15803d; font-weight:800"><i class="fa-solid fa-check"></i> প্রকাশিত</span>
                            @else
                                <span style="font-size:11px; color:#94a3b8">অপ্রকাশিত</span>
                            @endif
                        </div>
                        <div style="font-size:13.5px; font-weight:800; color:#0f172a">{{ $ex->title }}</div>
                        <div style="font-size:11.5px; color:#64748b; margin-top:2px">
                            {{ $ex->subject->name ?? '—' }} &middot; {{ $ex->semester?->name ?? '' }}
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end">
                        <a href="{{ route('admin.exams.show', $ex) }}" target="_blank"
                           style="padding:6px 12px; border-radius:8px; font-size:12px; font-weight:800; text-decoration:none; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; display:inline-flex; align-items:center; gap:5px; white-space:nowrap"
                           title="এই পরীক্ষার একক মেধা তালিকা ও শিক্ষার্থীদের মার্কশীট দেখুন">
                            <i class="fa-solid fa-trophy" style="color:#eab308"></i> মেধা তালিকা ও মার্কশীট
                        </a>
                        <form method="POST" action="{{ route('admin.result-book.publish-exam') }}">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ $ex->id }}">
                            <button type="submit" 
                               style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer; border:none; background:{{ $ex->is_result_published ? '#fee2e2' : '#dcfce7' }}; color:{{ $ex->is_result_published ? '#b91c1c' : '#15803d' }}">
                                {{ $ex->is_result_published ? 'প্রকাশ প্রত্যাহার' : 'রেজাল্ট প্রকাশ' }}
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
                                <th style="padding:10px 6px; text-align:center" title="একক সিটি পরীক্ষার মেধা তালিকা ও মার্কশীট">
                                    সিটি (/৩০)
                                    <div style="font-size:10px; color:#2563eb; font-weight:700">২০% মান</div>
                                </th>
                                <th style="padding:10px 6px; text-align:center" title="একক মিডটার্ম পরীক্ষার মেধা তালিকা ও মার্কশীট">
                                    মিড (/৫০)
                                    <div style="font-size:10px; color:#2563eb; font-weight:700">৩০% মান</div>
                                </th>
                                <th style="padding:10px 6px; text-align:center" title="একক ফাইনাল পরীক্ষার মেধা তালিকা ও মার্কশীট">
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
                            @php
                                $ctExam = $examLookup->get($fm->subject_id . '_' . ($fm->semester_id ?? 0) . '_QUIZ', collect())->first() 
                                    ?? $examLookup->get($fm->subject_id . '_0_QUIZ', collect())->first()
                                    ?? $examLookup->get($fm->subject_id . '_' . ($fm->semester_id ?? 0) . '_CLASS_TEST', collect())->first()
                                    ?? $examLookup->get($fm->subject_id . '_0_CLASS_TEST', collect())->first()
                                    ?? $examLookup->get($fm->subject_id . '_QUIZ', collect())->first();

                                $midExam = $examLookup->get($fm->subject_id . '_' . ($fm->semester_id ?? 0) . '_MIDTERM', collect())->first()
                                    ?? $examLookup->get($fm->subject_id . '_0_MIDTERM', collect())->first()
                                    ?? $examLookup->get($fm->subject_id . '_MIDTERM', collect())->first();

                                $finExam = $examLookup->get($fm->subject_id . '_' . ($fm->semester_id ?? 0) . '_FINAL', collect())->first()
                                    ?? $examLookup->get($fm->subject_id . '_0_FINAL', collect())->first()
                                    ?? $examLookup->get($fm->subject_id . '_FINAL', collect())->first();

                                $studentData = [
                                    'studentName'   => $fm->student->name ?? 'Student',
                                    'studentRoll'   => $fm->student->student_code ?? $fm->student->student_id ?? '—',
                                    'subjectName'   => $fm->subject->name ?? '—',
                                    'batchName'     => $fm->batch->name ?? '—',
                                    'semesterName'  => $fm->semester ? $fm->semester->name : 'সেমিস্টার ১',
                                    'rawCt'         => $fm->raw_class_test ?? 0,
                                    'convCt'        => $fm->class_test_converted ?? 0,
                                    'rawMid'        => $fm->raw_midterm ?? 0,
                                    'convMid'       => $fm->midterm_converted ?? 0,
                                    'rawFinal'      => $fm->raw_final ?? 0,
                                    'convFinal'     => $fm->final_converted ?? 0,
                                    'attendance'    => $fm->attendance_converted ?? 0,
                                    'attendancePct' => $fm->attendance_percent ?? 0,
                                    'rawTotal'      => $fm->raw_total_obtained ?? 0,
                                    'grandTotal'    => $fm->total_mark ?? 0,
                                    'grade'         => $fm->grade ?? 'F',
                                    'gpa'           => number_format($fm->gpa ?? 0, 2),
                                    'qawmi'         => $fm->qawmi_grade['name_bn'] ?? '',
                                    'status'        => $fm->status ?? 'FAIL',
                                    'meritRank'     => $fm->merit_rank_bengali ?? ($fm->merit_position ? $fm->merit_position . 'তম' : '—'),
                                    'finalExamUrl'  => $finExam ? route('admin.exams.show', $finExam) : null,
                                    'midExamUrl'    => $midExam ? route('admin.exams.show', $midExam) : null,
                                    'ctExamUrl'     => $ctExam  ? route('admin.exams.show', $ctExam) : null,
                                    'finalExamTitle'=> $finExam ? $finExam->title : 'সেমিস্টার ফাইনাল পরীক্ষা',
                                    'midExamTitle'  => $midExam ? $midExam->title : 'মিডটার্ম পরীক্ষা',
                                    'ctExamTitle'   => $ctExam  ? $ctExam->title : 'ক্লাস টেস্ট / সিটি',
                                ];
                            @endphp
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
                                    @if($ctExam)
                                        <a href="{{ route('admin.exams.show', $ctExam) }}" target="_blank"
                                           style="color:#1d4ed8; font-weight:800; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:2px"
                                           title="সিটি পরীক্ষার মেধা তালিকা ও ফলাফল দেখুন">
                                            {{ $fm->raw_class_test ?? '—' }} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px; color:#3b82f6"></i>
                                        </a>
                                    @else
                                        <button type="button" onclick='openStudentSingleMarksheet(@json($studentData), "ct")'
                                                style="border:none; background:none; color:#0f172a; font-weight:800; font-size:14px; cursor:pointer"
                                                title="ক্লিক করে এই শিক্ষার্থীর সিটি একক মার্কশীট দেখুন">
                                            {{ $fm->raw_class_test ?? '—' }}
                                        </button>
                                    @endif
                                    @if($fm->class_test_converted !== null)
                                        <div style="font-size:10.5px; color:#2563eb; font-weight:700" title="Criteria ২০% এ রূপান্তর">
                                            ({{ $fm->class_test_converted }})
                                        </div>
                                    @endif
                                </td>
                                {{-- Midterm --}}
                                <td style="padding:12px 6px; text-align:center">
                                    @if($midExam)
                                        <a href="{{ route('admin.exams.show', $midExam) }}" target="_blank"
                                           style="color:#1d4ed8; font-weight:800; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:2px"
                                           title="মিডটার্ম পরীক্ষার মেধা তালিকা ও ফলাফল দেখুন">
                                            {{ $fm->raw_midterm ?? '—' }} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px; color:#3b82f6"></i>
                                        </a>
                                    @else
                                        <button type="button" onclick='openStudentSingleMarksheet(@json($studentData), "mid")'
                                                style="border:none; background:none; color:#0f172a; font-weight:800; font-size:14px; cursor:pointer"
                                                title="ক্লিক করে এই শিক্ষার্থীর মিডটার্ম একক মার্কশীট দেখুন">
                                            {{ $fm->raw_midterm ?? '—' }}
                                        </button>
                                    @endif
                                    @if($fm->midterm_converted !== null)
                                        <div style="font-size:10.5px; color:#2563eb; font-weight:700" title="Criteria ৩০% এ রূপান্তর">
                                            ({{ $fm->midterm_converted }})
                                        </div>
                                    @endif
                                </td>
                                {{-- Final --}}
                                <td style="padding:12px 6px; text-align:center">
                                    @if($finExam)
                                        <a href="{{ route('admin.exams.show', $finExam) }}" target="_blank"
                                           style="color:#1d4ed8; font-weight:800; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; gap:2px"
                                           title="ফাইনাল পরীক্ষার মেধা তালিকা ও ফলাফল দেখুন">
                                            {{ $fm->raw_final ?? '—' }} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px; color:#3b82f6"></i>
                                        </a>
                                    @else
                                        <button type="button" onclick='openStudentSingleMarksheet(@json($studentData), "final")'
                                                style="border:none; background:none; color:#0f172a; font-weight:800; font-size:14px; cursor:pointer"
                                                title="ক্লিক করে এই শিক্ষার্থীর ফাইনাল একক মার্কশীট দেখুন">
                                            {{ $fm->raw_final ?? '—' }}
                                        </button>
                                    @endif
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
                                <td style="padding:12px 8px; text-align:center; white-space:nowrap">
                                    <div style="display:inline-flex; gap:5px; align-items:center">
                                        <button type="button" 
                                            onclick='openStudentSingleMarksheet(@json($studentData), "final")'
                                            style="padding:4px 9px; background:#10b981; border:1px solid #059669; color:#fff; border-radius:6px; cursor:pointer; font-size:11px; font-weight:800; display:inline-flex; align-items:center; gap:4px; box-shadow:0 1px 2px rgba(16,185,129,0.2)"
                                            title="একক পরীক্ষার মার্কশীট (ফাইনাল, মিডটার্ম বা সিটি) দেখুন ও প্রিন্ট করুন">
                                            <i class="fa-solid fa-file-lines"></i> মার্কশীট
                                        </button>
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

    {{-- Student Single Exam Marksheet & Combined Results Modal --}}
    <div id="studentSingleMarksheetModal" class="modal-overlay" onclick="if(event.target===this) closeStudentSingleMarksheet()">
        <div class="modal-card" style="max-width:780px; width:100%; max-height:92vh; display:flex; flex-direction:column">
            
            {{-- Modal Top Bar --}}
            <div style="padding:14px 20px; background:#0f172a; color:#fff; display:flex; justify-content:space-between; align-items:center">
                <h3 style="margin:0; font-size:15px; font-weight:800; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-file-lines" style="color:#38bdf8"></i> শিক্ষার্থী পরীক্ষার নম্বরপত্র (Student Exam Mark Sheet)
                </h3>
                <button type="button" onclick="closeStudentSingleMarksheet()" style="background:none; border:none; color:#cbd5e1; font-size:24px; cursor:pointer; line-height:1">&times;</button>
            </div>

            {{-- Exam Selection Tabs (Instant Tab Switch) --}}
            <div style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 16px; display:flex; gap:8px; flex-wrap:wrap; align-items:center">
                <span style="font-size:12px; font-weight:800; color:#475569; margin-right:4px">পরীক্ষা নির্বাচন:</span>
                <button type="button" class="sm-tab-btn active" id="tabBtnFinal" onclick="switchSmExamTab('final')">
                    <i class="fa-solid fa-trophy" style="color:#eab308"></i> সেমিস্টার ফাইনাল (/১০০)
                </button>
                <button type="button" class="sm-tab-btn" id="tabBtnMid" onclick="switchSmExamTab('mid')">
                    <i class="fa-solid fa-pen-nib" style="color:#2563eb"></i> মিডটার্ম পরীক্ষা (/৫০)
                </button>
                <button type="button" class="sm-tab-btn" id="tabBtnCt" onclick="switchSmExamTab('ct')">
                    <i class="fa-solid fa-bolt" style="color:#f59e0b"></i> ক্লাস টেস্ট (/৩০)
                </button>
                <button type="button" class="sm-tab-btn" id="tabBtnAll" onclick="switchSmExamTab('all')">
                    <i class="fa-solid fa-table-list" style="color:#10b981"></i> সম্পূর্ণ বিষয় ফলাফল (১০০%)
                </button>
            </div>

            {{-- Printable Marksheet Area --}}
            <div id="studentMarksheetPrintArea" style="padding:24px; overflow-y:auto; flex:1; font-family:'Kalpurush',sans-serif; background:#fff">
                {{-- Official Letterhead --}}
                <div style="text-align:center; border-bottom:2px solid #1e293b; padding-bottom:14px; margin-bottom:18px">
                    <div style="font-size:24px; font-weight:900; color:#1e293b; letter-spacing:0.5px">
                        ইসলামিক অনলাইন মাদ্রাসা
                    </div>
                    <div style="font-size:13px; color:#475569; font-weight:700">
                        Islamic Online Madrasah (IOM) &middot; শিক্ষা বর্ষ: ২০২৬-২০২৭
                    </div>
                    <div style="display:inline-block; background:#0f172a; color:#fff; padding:3px 18px; border-radius:20px; font-size:12.5px; font-weight:800; margin-top:8px" id="smModalBadgeTitle">
                        সেমিস্টার ফাইনাল পরীক্ষার নম্বরপত্র
                    </div>
                </div>

                {{-- Student Information Table --}}
                <table style="width:100%; border-collapse:collapse; margin-bottom:18px; font-size:13px">
                    <tr>
                        <td style="padding:6px 8px; font-weight:700; color:#475569; width:18%">শিক্ষার্থীর নাম:</td>
                        <td style="padding:6px 8px; font-weight:900; color:#0f172a; font-size:14px" id="smModalStudentName"></td>
                        <td style="padding:6px 8px; font-weight:700; color:#475569; width:16%">রোল নং:</td>
                        <td style="padding:6px 8px; font-weight:800; color:#2563eb" id="smModalStudentRoll"></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 8px; font-weight:700; color:#475569">ব্যাচ (Batch):</td>
                        <td style="padding:6px 8px; font-weight:800; color:#1e293b" id="smModalBatchName"></td>
                        <td style="padding:6px 8px; font-weight:700; color:#475569">সেমিস্টার:</td>
                        <td style="padding:6px 8px; font-weight:800; color:#1e293b" id="smModalSemesterName"></td>
                    </tr>
                    <tr>
                        <td style="padding:6px 8px; font-weight:700; color:#475569">পাঠ্য বিষয়:</td>
                        <td style="padding:6px 8px; font-weight:800; color:#0f172a" id="smModalSubjectName" colspan="3"></td>
                    </tr>
                </table>

                {{-- Single Exam Specific Box (Visible for Final, Mid, CT) --}}
                <div id="smSingleExamContainer">
                    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13.5px; border:1px solid #cbd5e1">
                        <thead>
                            <tr style="background:#f8fafc; font-weight:800; color:#1e293b">
                                <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:left">বিবরণ / আইটেম</th>
                                <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:center; width:120px">পরীক্ষার পূর্ণমান</th>
                                <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:center; width:130px">প্রাপ্ত আসল নম্বর</th>
                                <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:center; width:150px">Criteria কনভার্ট</th>
                                <th style="padding:10px 12px; border:1px solid #cbd5e1; text-align:center; width:110px">শতকরা (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:12px; border:1px solid #cbd5e1; font-weight:700" id="smSingleItemTitle">
                                    সেমিস্টার ফাইনাল পরীক্ষা
                                </td>
                                <td style="padding:12px; border:1px solid #cbd5e1; text-align:center; font-weight:700" id="smSingleFullMarks">
                                    ১০০
                                </td>
                                <td style="padding:12px; border:1px solid #cbd5e1; text-align:center; font-size:17px; font-weight:900; color:#1e40af" id="smSingleRawMarks">
                                    --
                                </td>
                                <td style="padding:12px; border:1px solid #cbd5e1; text-align:center; font-size:15px; font-weight:800; color:#059669" id="smSingleConvMarks">
                                    --
                                </td>
                                <td style="padding:12px; border:1px solid #cbd5e1; text-align:center; font-weight:800" id="smSinglePercent">
                                    --
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Full Combined Marksheet Box (Visible for 'all') --}}
                <div id="smFullSubjectContainer" style="display:none">
                    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13px; border:1px solid #cbd5e1">
                        <thead>
                            <tr style="background:#f8fafc; font-weight:800; color:#1e293b">
                                <th style="padding:9px 12px; border:1px solid #cbd5e1; text-align:left">পরীক্ষার অংশ (Criteria)</th>
                                <th style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; width:110px">পূর্ণমান (আসল)</th>
                                <th style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; width:120px">প্রাপ্ত আসল নম্বর</th>
                                <th style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; width:130px">১০০% এ ওজন</th>
                                <th style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; width:140px">কনভার্ট প্রাপ্ত নম্বর</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1">ক্লাস টেস্ট / সিটি (Class Test)</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center">৩০</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800" id="smAllCtRaw"></td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; color:#2563eb; font-weight:700">২০%</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800; color:#2563eb" id="smAllCtConv"></td>
                            </tr>
                            <tr>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1">মিডটার্ম পরীক্ষা (Midterm Exam)</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center">৫০</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800" id="smAllMidRaw"></td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; color:#2563eb; font-weight:700">৩০%</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800; color:#2563eb" id="smAllMidConv"></td>
                            </tr>
                            <tr>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1">সেমিস্টার ফাইনাল পরীক্ষা (Final Exam)</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center">১০০</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800" id="smAllFinRaw"></td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; color:#2563eb; font-weight:700">৪০%</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800; color:#2563eb" id="smAllFinConv"></td>
                            </tr>
                            <tr>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1">ক্লাস উপস্থিতি (Attendance)</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center">১০</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800" id="smAllAttRaw"></td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; color:#059669; font-weight:700">১০%</td>
                                <td style="padding:9px 12px; border:1px solid #cbd5e1; text-align:center; font-weight:800; color:#059669" id="smAllAttConv"></td>
                            </tr>
                            <tr style="background:#f1f5f9; font-weight:900">
                                <td style="padding:10px 12px; border:2px solid #94a3b8">সর্বমোট (Grand Total)</td>
                                <td style="padding:10px 12px; border:2px solid #94a3b8; text-align:center">১৯০</td>
                                <td style="padding:10px 12px; border:2px solid #94a3b8; text-align:center; font-size:15px; color:#1e293b" id="smAllRawSum"></td>
                                <td style="padding:10px 12px; border:2px solid #94a3b8; text-align:center; color:#059669">১০০%</td>
                                <td style="padding:10px 12px; border:2px solid #94a3b8; text-align:center; font-size:18px; color:#059669" id="smAllGrandConv"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Performance Badges --}}
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:14px; margin-bottom:28px; display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; text-align:center">
                    <div>
                        <div style="font-size:11.5px; color:#166534; font-weight:700">ফলাফল স্ট্যাটাস</div>
                        <div id="smModalStatusBadge" style="font-size:16px; font-weight:900; color:#15803d; margin-top:3px">উত্তীর্ণ (PASS)</div>
                    </div>
                    <div>
                        <div style="font-size:11.5px; color:#166534; font-weight:700">লেটার গ্রেড &amp; GPA</div>
                        <div id="smModalGradeBadge" style="font-size:16px; font-weight:900; color:#1e40af; margin-top:3px"></div>
                    </div>
                    <div>
                        <div style="font-size:11.5px; color:#166534; font-weight:700">কওমি মাদরাসা মান</div>
                        <div id="smModalQawmiBadge" style="font-size:14px; font-weight:800; color:#0f766e; margin-top:4px"></div>
                    </div>
                    <div>
                        <div style="font-size:11.5px; color:#166534; font-weight:700">ব্যাচ মেধাক্রম (Rank)</div>
                        <div id="smModalRankBadge" style="font-size:16px; font-weight:900; color:#b45309; margin-top:3px"></div>
                    </div>
                </div>

                {{-- Signatures --}}
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:40px; padding-top:16px">
                    <div style="text-align:center; width:170px; border-top:1px dashed #64748b; padding-top:6px">
                        <div style="font-size:12.5px; font-weight:800; color:#0f172a">পরীক্ষকের স্বাক্ষর</div>
                        <div style="font-size:10.5px; color:#64748b">বিষয় শিক্ষক / নিরীক্ষক</div>
                    </div>
                    <div style="text-align:center">
                        <div style="border:2px dashed #94a3b8; border-radius:50%; width:64px; height:64px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:10.5px; font-weight:700; margin:0 auto">
                            মাদরাসার সিল
                        </div>
                    </div>
                    <div style="text-align:center; width:170px; border-top:1px dashed #64748b; padding-top:6px">
                        <div style="font-size:12.5px; font-weight:800; color:#0f172a">পরীক্ষা নিয়ন্ত্রক</div>
                        <div style="font-size:10.5px; color:#64748b">ইসলামিক অনলাইন মাদরাসা</div>
                    </div>
                </div>
            </div>

            {{-- Footer Action Bar --}}
            <div style="padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
                <div>
                    <a id="smModalMeritListBtn" href="{{ route('admin.exams.index') }}" target="_blank"
                       style="padding:8px 16px; background:#1e40af; color:#fff; border-radius:8px; font-size:12.5px; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 3px rgba(30,64,175,0.25)">
                        <i class="fa-solid fa-trophy" style="color:#fef08a"></i> <span id="smModalMeritListBtnText">এই সেমিস্টার ফাইনাল পরীক্ষার সকল শিক্ষার্থীর মেধা তালিকা দেখুন (১ম, ২য়, ৩য়) →</span>
                    </a>
                </div>
                <div style="display:flex; gap:10px">
                    <button type="button" onclick="printStudentSingleMarksheet()" 
                            style="padding:8px 18px; background:#15803d; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 3px rgba(21,128,61,0.25)">
                        <i class="fa-solid fa-print"></i> মার্কশীট প্রিন্ট করুন
                    </button>
                    <button type="button" onclick="closeStudentSingleMarksheet()" 
                            style="padding:8px 14px; background:#e2e8f0; color:#475569; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer">
                        বন্ধ করুন
                    </button>
                </div>
            </div>

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

        // --- Single Student Marksheet Functions ---
        let currSmData = null;
        let currSmTab = 'final';

        window.openStudentSingleMarksheet = function(data, defaultTab = 'final') {
            currSmData = data;
            currSmTab = defaultTab;

            document.getElementById('smModalStudentName').textContent = data.studentName;
            document.getElementById('smModalStudentRoll').textContent = data.studentRoll;
            document.getElementById('smModalBatchName').textContent = data.batchName;
            document.getElementById('smModalSemesterName').textContent = data.semesterName;
            document.getElementById('smModalSubjectName').textContent = data.subjectName;

            // Badges
            document.getElementById('smModalGradeBadge').textContent = `${data.grade} (${data.gpa})`;
            document.getElementById('smModalQawmiBadge').textContent = data.qawmi || '—';
            document.getElementById('smModalRankBadge').textContent = data.meritRank;
            document.getElementById('smModalStatusBadge').innerHTML = data.status === 'PASS' 
                ? '<span style="color:#15803d; font-weight:900">উত্তীর্ণ (PASS)</span>' 
                : '<span style="color:#dc2626; font-weight:900">অনুত্তীর্ণ (FAIL)</span>';

            // Populate Full Subject Table
            document.getElementById('smAllCtRaw').textContent = data.rawCt;
            document.getElementById('smAllCtConv').textContent = data.convCt;
            document.getElementById('smAllMidRaw').textContent = data.rawMid;
            document.getElementById('smAllMidConv').textContent = data.convMid;
            document.getElementById('smAllFinRaw').textContent = data.rawFinal;
            document.getElementById('smAllFinConv').textContent = data.convFinal;
            document.getElementById('smAllAttRaw').textContent = `${data.attendance} (${data.attendancePct}%)`;
            document.getElementById('smAllAttConv').textContent = data.attendance;
            document.getElementById('smAllRawSum').textContent = `${data.rawTotal} / 190`;
            document.getElementById('smAllGrandConv').textContent = `${data.grandTotal} / 100`;

            switchSmExamTab(defaultTab);

            const m = document.getElementById('studentSingleMarksheetModal');
            if (m) {
                m.classList.add('open', 'is-active', 'show');
                m.style.setProperty('display', 'flex', 'important');
                m.style.setProperty('opacity', '1', 'important');
                m.style.setProperty('pointer-events', 'all', 'important');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeStudentSingleMarksheet = function() {
            const m = document.getElementById('studentSingleMarksheetModal');
            if (m) {
                m.classList.remove('open', 'is-active', 'show');
                m.style.setProperty('display', 'none', 'important');
                m.style.setProperty('opacity', '0', 'important');
                m.style.setProperty('pointer-events', 'none', 'important');
                document.body.style.overflow = '';
            }
        };

        window.switchSmExamTab = function(tab) {
            currSmTab = tab;
            ['tabBtnFinal', 'tabBtnMid', 'tabBtnCt', 'tabBtnAll'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.remove('active');
            });

            const singleBox = document.getElementById('smSingleExamContainer');
            const fullBox = document.getElementById('smFullSubjectContainer');
            const badgeTitle = document.getElementById('smModalBadgeTitle');
            const meritBtn = document.getElementById('smModalMeritListBtn');
            const meritBtnText = document.getElementById('smModalMeritListBtnText');

            if (tab === 'final') {
                const activeBtn = document.getElementById('tabBtnFinal');
                if (activeBtn) activeBtn.classList.add('active');
                singleBox.style.display = 'block';
                fullBox.style.display = 'none';
                badgeTitle.textContent = 'সেমিস্টার ফাইনাল পরীক্ষার নম্বরপত্র (Final Mark Sheet)';
                
                document.getElementById('smSingleItemTitle').textContent = currSmData.finalExamTitle || 'সেমিস্টার ফাইনাল পরীক্ষা';
                document.getElementById('smSingleFullMarks').textContent = '১০০';
                document.getElementById('smSingleRawMarks').textContent = currSmData.rawFinal;
                document.getElementById('smSingleConvMarks').textContent = `${currSmData.convFinal} (৪০% মান)`;
                const pct = Math.min(100, Math.round((parseFloat(currSmData.rawFinal) || 0)));
                document.getElementById('smSinglePercent').textContent = pct + '%';

                if (currSmData.finalExamUrl) {
                    meritBtn.href = currSmData.finalExamUrl;
                    meritBtnText.textContent = '🏆 এই সেমিস্টার ফাইনাল পরীক্ষার সকল শিক্ষার্থীর মেধা তালিকা দেখুন (১ম, ২য়, ৩য়) →';
                    meritBtn.style.display = 'inline-flex';
                } else {
                    meritBtn.href = '{{ route("admin.exams.index") }}';
                    meritBtnText.textContent = '🏆 সকল একক পরীক্ষার মেধা তালিকা ও রেজাল্টে যান →';
                    meritBtn.style.display = 'inline-flex';
                }
            } else if (tab === 'mid') {
                const activeBtn = document.getElementById('tabBtnMid');
                if (activeBtn) activeBtn.classList.add('active');
                singleBox.style.display = 'block';
                fullBox.style.display = 'none';
                badgeTitle.textContent = 'মিডটার্ম পরীক্ষার নম্বরপত্র (Midterm Mark Sheet)';

                document.getElementById('smSingleItemTitle').textContent = currSmData.midExamTitle || 'মিডটার্ম পরীক্ষা';
                document.getElementById('smSingleFullMarks').textContent = '৫০';
                document.getElementById('smSingleRawMarks').textContent = currSmData.rawMid;
                document.getElementById('smSingleConvMarks').textContent = `${currSmData.convMid} (৩০% মান)`;
                const pct = Math.min(100, Math.round(((parseFloat(currSmData.rawMid) || 0) / 50) * 100));
                document.getElementById('smSinglePercent').textContent = pct + '%';

                if (currSmData.midExamUrl) {
                    meritBtn.href = currSmData.midExamUrl;
                    meritBtnText.textContent = '🏆 এই মিডটার্ম পরীক্ষার সকল শিক্ষার্থীর মেধা তালিকা দেখুন (১ম, ২য়, ৩য়) →';
                    meritBtn.style.display = 'inline-flex';
                } else {
                    meritBtn.href = '{{ route("admin.exams.index") }}';
                    meritBtnText.textContent = '🏆 সকল একক পরীক্ষার মেধা তালিকা ও রেজাল্টে যান →';
                    meritBtn.style.display = 'inline-flex';
                }
            } else if (tab === 'ct') {
                const activeBtn = document.getElementById('tabBtnCt');
                if (activeBtn) activeBtn.classList.add('active');
                singleBox.style.display = 'block';
                fullBox.style.display = 'none';
                badgeTitle.textContent = 'ক্লাস টেস্ট নম্বরপত্র (Class Test Mark Sheet)';

                document.getElementById('smSingleItemTitle').textContent = currSmData.ctExamTitle || 'ক্লাস টেস্ট / সিটি মূল্যায়ন';
                document.getElementById('smSingleFullMarks').textContent = '৩০';
                document.getElementById('smSingleRawMarks').textContent = currSmData.rawCt;
                document.getElementById('smSingleConvMarks').textContent = `${currSmData.convCt} (২০% মান)`;
                const pct = Math.min(100, Math.round(((parseFloat(currSmData.rawCt) || 0) / 30) * 100));
                document.getElementById('smSinglePercent').textContent = pct + '%';

                if (currSmData.ctExamUrl) {
                    meritBtn.href = currSmData.ctExamUrl;
                    meritBtnText.textContent = '🏆 এই সিটি পরীক্ষার সকল শিক্ষার্থীর মেধা তালিকা দেখুন (১ম, ২য়, ৩য়) →';
                    meritBtn.style.display = 'inline-flex';
                } else {
                    meritBtn.href = '{{ route("admin.exams.index") }}';
                    meritBtnText.textContent = '🏆 সকল একক পরীক্ষার মেধা তালিকা ও রেজাল্টে যান →';
                    meritBtn.style.display = 'inline-flex';
                }
            } else if (tab === 'all') {
                const activeBtn = document.getElementById('tabBtnAll');
                if (activeBtn) activeBtn.classList.add('active');
                singleBox.style.display = 'none';
                fullBox.style.display = 'block';
                badgeTitle.textContent = 'বিষয়ভিত্তিক পূর্ণাঙ্গ নম্বরপত্র ও ফলাফল (Combined Grade Sheet)';

                meritBtn.href = '{{ route("admin.result-book.batch-merit", request()->only("batch_id")) }}';
                meritBtnText.textContent = '🏆 ৬-সেমিস্টার ব্যাচ মেধা তালিকা দেখুন →';
                meritBtn.style.display = 'inline-flex';
            }
        };

        window.printStudentSingleMarksheet = function() {
            window.print();
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeOverrideModal();
                window.closeStudentSingleMarksheet();
            }
        });
    </script>
</x-admin-layout>
