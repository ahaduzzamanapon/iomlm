<x-admin-layout>
    <x-slot name="title">সেমিস্টার প্রমোশন ও একাডেমিক অগ্রগতি (Semester Promotion & Progression)</x-slot>

    <style>
        /* Bangladeshi Context & Kalpurush Font */
        .promo-page, .promo-page * {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .promo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .promo-title h1 {
            margin: 0 0 5px;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .promo-title p {
            margin: 0;
            font-size: 13.5px;
            color: #64748b;
        }

        /* Filter Card */
        .promo-filter-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        /* Stats Grid */
        .promo-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .promo-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .promo-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
        }
        .promo-stat-val {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }
        .promo-stat-lbl {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-top: 3px;
        }

        /* Audit Card */
        .audit-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }
        .audit-banner {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .audit-banner.success {
            background: #ecfdf5;
            border-bottom: 1px solid #a7f3d0;
            color: #065f46;
        }
        .audit-banner.warning {
            background: #fffbeb;
            border-bottom: 1px solid #fde68a;
            color: #92400e;
        }

        /* Tables */
        .promo-table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .promo-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .promo-table th {
            background: #f8fafc;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            white-space: nowrap;
        }
        .promo-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #1e293b;
            vertical-align: middle;
        }
        .promo-table tr:hover td {
            background: #fbfcfe;
        }

        /* Status Badges */
        .badge-eligible {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .badge-retake-warn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            background: #ffedd5;
            color: #9a3412;
            border: 1px solid #fed7aa;
        }
        .badge-readmission-warn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .badge-pending-warn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .badge-done {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }

        /* Subject audit pills */
        .check-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }
        .check-pill.ok {
            background: #dcfce7;
            color: #166534;
        }
        .check-pill.no {
            background: #f1f5f9;
            color: #94a3b8;
        }

        /* Buttons */
        .btn-action-promote {
            background: #059669;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        .btn-action-promote:hover {
            background: #047857;
            color: #fff;
        }
        .btn-action-readmission {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        .btn-action-readmission:hover {
            background: #b91c1c;
            color: #fff;
        }
    </style>

    <div class="promo-page">

        {{-- Page Header --}}
        <div class="promo-header">
            <div class="promo-title">
                <h1>
                    <i class="fa-solid fa-graduation-cap" style="color:#059669"></i>
                    সেমিস্টার প্রমোশন ও একাডেমিক অগ্রগতি (Semester Promotion)
                </h1>
                <p>ব্যাচভিত্তিক সেমিস্টার পরীক্ষার ফলাফল অডিট, প্রমোশন ও রি-এডমিশন যোগ্যতা নির্ধারণ</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap">
                <button type="button" class="btn btn-outline" onclick="togglePromotionGuide()" style="font-weight:700; font-size:13px; color:#047857; border-color:#059669; background:#ecfdf5">
                    <i class="fa-solid fa-circle-question"></i> প্রমোশন সহায়িকা ও নিয়মাবলী
                </button>
                <a href="{{ route('admin.readmissions.index') }}" class="btn btn-outline" style="font-weight:700; font-size:13px">
                    <i class="fa-solid fa-user-clock"></i> রি-এডমিশন তালিকা
                </a>
                <button class="btn btn-success" onclick="openManualPromotionModal('', '', '')" style="font-weight:700; font-size:13px">
                    <i class="fa-solid fa-user-graduate"></i> একক শিক্ষার্থী প্রমোশন
                </button>
            </div>
        </div>

        {{-- Promotion Policy & Guide Explainer Box --}}
        <div class="promo-guide-card" id="promotionGuideBox" style="background:#f0fdf4; border:1px solid #86efac; border-radius:14px; padding:18px 22px; margin-bottom:24px; box-shadow:0 4px 15px rgba(0,0,0,0.02)">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap">
                <div style="display:flex; gap:12px; align-items:flex-start">
                    <div style="width:40px; height:40px; border-radius:10px; background:#16a34a; color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 style="margin:0 0 4px; font-size:16px; font-weight:800; color:#14532d">
                            সেমিস্টার প্রমোশন কিভাবে কাজ করে? (How Semester Promotion Works)
                        </h3>
                        <p style="margin:0; font-size:13px; color:#166534">
                            পরীক্ষার ফলাফল অডিট, প্রমোশন নির্ধারণ নীতিমালা এবং সিস্টেমের স্বয়ংক্রিয় কার্যক্রমের নিয়মাবলী নিচে দেওয়া হলো:
                        </p>
                    </div>
                </div>
                <button type="button" onclick="togglePromotionGuide()" style="background:#dcfce7; border:1px solid #86efac; border-radius:8px; padding:4px 12px; font-size:12px; font-weight:700; color:#166534; cursor:pointer">
                    <span id="guideToggleText"><i class="fa-solid fa-chevron-up"></i> লুকান</span>
                </button>
            </div>

            <div id="promotionGuideContent" style="margin-top:16px; display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; border-top:1px dashed #86efac; padding-top:14px">
                <div style="background:#fff; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px">
                    <div style="font-weight:800; color:#15803d; font-size:13.5px; margin-bottom:4px">
                        ১. পরীক্ষা ও ফলাফল অডিট
                    </div>
                    <div style="font-size:12px; color:#374151; line-height:1.5">
                        সংশ্লিষ্ট সেমিস্টারের সব বিষয়ের পরীক্ষা (সিটি, মিড, ফাইনাল) সম্পন্ন হলে <strong>রেজাল্ট বুক</strong> থেকে ফাইনাল মার্কস জেনারেট করুন। মার্কস প্রস্তুত হলে শিক্ষার্থী তালিকা স্বয়ংক্রিয়ভাবে প্রমোশনের জন্য প্রস্তুত হবে।
                    </div>
                </div>

                <div style="background:#fff; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px">
                    <div style="font-weight:800; color:#15803d; font-size:13.5px; margin-bottom:4px">
                        ২. প্রমোশন মানদণ্ড ও নীতিমালা
                    </div>
                    <div style="font-size:12px; color:#374151; line-height:1.5">
                        • 🟢 <strong>সরাসরি প্রমোশন:</strong> সকল বিষয়ে পাস করলে সরাসরি উত্তীর্ণ।<br>
                        • 🟠 <strong>রিটেকসহ প্রমোশন:</strong> ১-২টি বিষয়ে ফেল থাকলে রিটেক সাপেক্ষে উত্তীর্ণ।<br>
                        • 🔴 <strong>রি-এডমিশন:</strong> ২টির বেশি ফেল করলে সেমিস্টার রিপিটের জন্য পাঠানো হবে।
                    </div>
                </div>

                <div style="background:#fff; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px">
                    <div style="font-weight:800; color:#15803d; font-size:13.5px; margin-bottom:4px">
                        ৩. প্রমোশন দেওয়ার পদ্ধতিসমূহ
                    </div>
                    <div style="font-size:12px; color:#374151; line-height:1.5">
                        • <strong>বাল্ক প্রমোশন:</strong> এক ক্লিকে সকল যোগ্য শিক্ষার্থীকে একবারে প্রমোশন দিন।<br>
                        • <strong>সিলেক্টেড প্রমোশন:</strong> পছন্দের শিক্ষার্থীদের টিক দিয়ে একসাথে প্রমোশন।<br>
                        • <strong>বিশেষ বিবেচনা প্রমোশন:</strong> মার্ক বাকি থাকলেও অ্যাডমিন চাইলে ফোর্স প্রমোশন দিতে পারেন।
                    </div>
                </div>

                <div style="background:#fff; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px">
                    <div style="font-weight:800; color:#15803d; font-size:13.5px; margin-bottom:4px">
                        ৪. প্রমোশন দিলে যা ঘটবে
                    </div>
                    <div style="font-size:12px; color:#374151; line-height:1.5">
                        প্রমোশন বাটন চাপার সাথে সাথে শিক্ষার্থীর এনরোলমেন্ট স্বয়ংক্রিয়ভাবে পরবর্তী সেমিস্টারে আপডেট হবে এবং অ্যাকাউন্টিং মডিউলে পরবর্তী সেমিস্টার ফি ইনভয়েস তৈরি হবে।
                    </div>
                </div>
            </div>
        </div>

        {{-- Session Alerts --}}
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:20px; border-radius:10px; font-weight:700">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom:20px; border-radius:10px; font-weight:700">
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info" style="margin-bottom:20px; border-radius:10px; font-weight:700">
                {{ session('info') }}
            </div>
        @endif

        {{-- Batch & Semester Selection Card --}}
        <div class="promo-filter-card">
            <form method="GET" action="{{ route('admin.promotions.index') }}" id="promoFilterForm" style="display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end">
                
                {{-- 1. Course Selection --}}
                <div style="flex:1.5; min-width:220px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        ১. কোর্স নির্বাচন করুন (Select Course)
                    </label>
                    <select name="course_id" id="courseSelect" class="form-control" style="height:44px; border-radius:10px" onchange="filterPromotionBatches(this.value)">
                        <option value="">-- সকল কোর্স (All Courses) --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ ($selectedCourseId == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Batch Selection --}}
                <div style="flex:2; min-width:240px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        ২. ব্যাচ নির্বাচন করুন (Select Batch) *
                    </label>
                    <select name="batch_id" id="batchSelect" class="form-control" style="height:44px; border-radius:10px" required onchange="handleBatchChange(this.value)">
                        <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}" {{ (request('batch_id') == $b->id || ($selectedBatch?->id == $b->id)) ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->course->title ?? $b->course->name ?? 'কোর্স' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Semester Selection (Auto-hides if course is SUBJECT_BASED) --}}
                <div id="semesterSelectContainer" style="flex:1.5; min-width:210px; display: {{ ($isSemesterBased ?? false) ? 'block' : 'none' }};">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        ৩. সেমিস্টার (Semester)
                    </label>
                    <select name="semester_id" id="semesterSelect" class="form-control" style="height:44px; border-radius:10px">
                        <option value="">-- সকল সেমিস্টার --</option>
                        @if(isset($courseSemesters) && $courseSemesters->isNotEmpty())
                            @foreach($courseSemesters as $sem)
                                <option value="{{ $sem->id }}" {{ ($selectedSemesterId == $sem->id) ? 'selected' : '' }}>
                                    {{ $sem->name }} {{ ($sem->id == ($runningSemesterId ?? null)) ? '(রানিং / Running)' : '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="height:44px; padding:0 22px; border-radius:10px; font-weight:800; font-size:13.5px; display:inline-flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-magnifying-glass"></i> মূল্যায়ন ও অডিট দেখুন
                </button>
            </form>
        </div>

        @if($selectedBatch)
            {{-- ── SECTION A: Exam Criteria Audit ("সব পরীক্ষা হয়েছে কিনা") ── --}}
            @if($examAudit)
                <div class="audit-card">
                    <div class="audit-banner {{ $examAudit['all_completed'] ? 'success' : 'warning' }}">
                        <div style="display:flex; align-items:center; gap:10px">
                            <i class="fa-solid {{ $examAudit['all_completed'] ? 'fa-circle-check' : 'fa-triangle-exclamation' }}" style="font-size:20px"></i>
                            <div>
                                <strong style="font-size:14.5px">
                                    {{ $examAudit['all_completed'] ? 'সকল বিষয়ের পরীক্ষা ও মার্ক মূল্যায়ন সম্পন্ন হয়েছে' : 'কিছু বিষয়ের পরীক্ষা অথবা মূল্যায়ন এখনো বাকি রয়েছে' }}
                                </strong>
                                <div style="font-size:12.5px; opacity:0.9; margin-top:2px">
                                    মোট {{ $examAudit['total_subjects'] }}টি বিষয়ের মধ্যে {{ $examAudit['completed_subjects'] }}টি বিষয়ের মূল্যায়ন সম্পন্ন, {{ $examAudit['pending_subjects'] }}টি বিষয় বাকি।
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap">
                            <span style="font-size:11.5px; background:rgba(0,0,0,0.06); padding:4px 10px; border-radius:8px; font-weight:700">
                                ক্রাইটেরিয়া: সিটি {{ $examAudit['criteria']['class_test_convert'] }} + মিড {{ $examAudit['criteria']['midterm_convert'] }} + ফাইনাল {{ $examAudit['criteria']['final_convert'] }} + উপস্থিতি {{ $examAudit['criteria']['attendance_convert'] }} = ১০০
                            </span>
                            @if(!$examAudit['all_completed'])
                                <a href="{{ route('admin.result-book.index', ['batch_id' => $selectedBatch->id, 'semester_id' => $selectedSemesterId]) }}" class="btn btn-sm btn-primary" style="font-weight:800">
                                    <i class="fa-solid fa-square-poll-vertical"></i> রেজাল্ট বুক থেকে মার্কস জেনারেট করুন
                                </a>
                                <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('studentStandingSection').scrollIntoView({behavior:'smooth'})" style="background:#fff; font-weight:800; color:#b45309; border-color:#f59e0b">
                                    <i class="fa-solid fa-bolt"></i> বিশেষ প্রমোশন তালিকা দেখুন ↓
                                </button>
                            @endif
                        </div>
                    </div>
                    @if(!$examAudit['all_completed'])
                        <div style="background:#fffbeb; padding:10px 20px; font-size:12.5px; color:#92400e; border-top:1px solid #fef3c7; display:flex; align-items:center; gap:8px; flex-wrap:wrap">
                            <i class="fa-solid fa-circle-info" style="color:#d97706; font-size:15px"></i>
                            <span><strong>প্রমোশন সংক্রান্ত তথ্য:</strong> সব বিষয়ের মার্ক মূল্যায়ন সম্পন্ন হলে স্বয়ংক্রিয় প্রমোশন সক্রিয় হবে। তবে কোনো বিষয়ের পরীক্ষা বা মার্ক বাকি থাকলেও আপনি চাইলে নিচে নির্দিষ্ট শিক্ষার্থীকে বা সবাইকে <strong>"বিশেষ বিবেচনায় প্রমোশন"</strong> দিতে পারেন।</span>
                        </div>
                    @endif

                    {{-- Subject-wise Exam Checklist --}}
                    <div class="promo-table-wrap">
                        <table class="promo-table">
                            <thead>
                                <tr>
                                    <th>বিষয় ও কোড</th>
                                    <th>ক্লাস টেস্ট / কুইজ</th>
                                    <th>মিডটার্ম পরীক্ষা</th>
                                    <th>ফাইনাল পরীক্ষা</th>
                                    <th>ফাইনাল মার্কস জেনারেট</th>
                                    <th>সামগ্রিক অবস্থা</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($examAudit['subjects'] as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item['subject']->name }}</strong>
                                            @if($item['subject']->code)
                                                <span style="color:#64748b; font-size:12px">({{ $item['subject']->code }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="check-pill {{ $item['has_quiz'] ? 'ok' : 'no' }}">
                                                <i class="fa-solid {{ $item['has_quiz'] ? 'fa-check' : 'fa-xmark' }}"></i>
                                                {{ $item['has_quiz'] ? 'পরীক্ষা হয়েছে' : 'বাকি' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="check-pill {{ $item['has_midterm'] ? 'ok' : 'no' }}">
                                                <i class="fa-solid {{ $item['has_midterm'] ? 'fa-check' : 'fa-xmark' }}"></i>
                                                {{ $item['has_midterm'] ? 'পরীক্ষা হয়েছে' : 'বাকি' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="check-pill {{ $item['has_final'] ? 'ok' : 'no' }}">
                                                <i class="fa-solid {{ $item['has_final'] ? 'fa-check' : 'fa-xmark' }}"></i>
                                                {{ $item['has_final'] ? 'পরীক্ষা হয়েছে' : 'বাকি' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="check-pill {{ $item['has_final_marks'] ? 'ok' : 'no' }}">
                                                <i class="fa-solid {{ $item['has_final_marks'] ? 'fa-check' : 'fa-xmark' }}"></i>
                                                {{ $item['has_final_marks'] ? 'জেনারেটেড' : 'জেনারেট বাকি' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($item['is_complete'])
                                                <span class="badge-eligible"><i class="fa-solid fa-circle-check"></i> সম্পূর্ণ</span>
                                            @else
                                                <span class="badge-pending-warn"><i class="fa-solid fa-clock"></i> অসমাপ্ত</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ── SECTION B: Student Standing & Promotion Decisions ("কে প্রমোশন পাবে, কে রি-এডমিশন") ── --}}
            @php
                $eligibleCount   = $studentEvaluations->whereIn('standing', ['ELIGIBLE_PROMOTION', 'ELIGIBLE_RETAKE'])->count();
                $passCleanCount  = $studentEvaluations->where('standing', 'ELIGIBLE_PROMOTION')->count();
                $retakeCount     = $studentEvaluations->where('standing', 'ELIGIBLE_RETAKE')->count();
                $readmitCount    = $studentEvaluations->where('standing', 'NEEDS_READMISSION')->count();
                $pendingCount    = $studentEvaluations->where('standing', 'PENDING_EXAMS')->count();
                $promotedCount   = $studentEvaluations->where('standing', 'ALREADY_PROMOTED')->count();
            @endphp

            {{-- Summary Stats Grid --}}
            <div class="promo-stat-grid">
                <div class="promo-stat-card">
                    <div class="promo-stat-icon" style="background:#eff6ff; color:#2563eb">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="promo-stat-val">{{ $studentEvaluations->count() }}</div>
                        <div class="promo-stat-lbl">মোট সক্রিয় শিক্ষার্থী</div>
                    </div>
                </div>

                <div class="promo-stat-card">
                    <div class="promo-stat-icon" style="background:#dcfce7; color:#166534">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="promo-stat-val" style="color:#166534">{{ $passCleanCount }}</div>
                        <div class="promo-stat-lbl">🟢 সরাসরি প্রমোশন যোগ্য</div>
                    </div>
                </div>

                <div class="promo-stat-card">
                    <div class="promo-stat-icon" style="background:#ffedd5; color:#9a3412">
                        <i class="fa-solid fa-rotate"></i>
                    </div>
                    <div>
                        <div class="promo-stat-val" style="color:#9a3412">{{ $retakeCount }}</div>
                        <div class="promo-stat-lbl">🟠 রিটেকসহ প্রমোশন (১-২ ফেল)</div>
                    </div>
                </div>

                <div class="promo-stat-card">
                    <div class="promo-stat-icon" style="background:#fee2e2; color:#991b1b">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                    <div>
                        <div class="promo-stat-val" style="color:#991b1b">{{ $readmitCount }}</div>
                        <div class="promo-stat-lbl">🔴 রি-এডমিশন লাগবে (&gt;২ ফেল)</div>
                    </div>
                </div>

                <div class="promo-stat-card">
                    <div class="promo-stat-icon" style="background:#f1f5f9; color:#475569">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="promo-stat-val" style="color:#475569">{{ $pendingCount }}</div>
                        <div class="promo-stat-lbl">🟡 মূল্যায়ন / মার্ক বাকি</div>
                    </div>
                </div>
            </div>

            {{-- Bulk Promote Card / Header --}}
            <div class="card" id="studentStandingSection" style="margin-bottom:24px; padding:0; border-radius:14px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.03)">
                <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px">
                    <div>
                        <h2 style="margin:0; font-size:16px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px">
                            <i class="fa-solid fa-user-check" style="color:#059669"></i>
                            শিক্ষার্থীদের মূল্যায়ন ফলাফল ও প্রমোশন সিদ্ধান্ত
                        </h2>
                        <div style="font-size:12.5px; color:#64748b; margin-top:2px">
                            ব্যাচ: <strong>{{ $selectedBatch->name }}</strong>
                            @if($isSemesterBased && $selectedSemesterId)
                                | বর্তমান সেমিস্টার: <strong>{{ $courseSemesters->firstWhere('id', $selectedSemesterId)?->name ?? 'সেমিস্টার' }}</strong>
                                @if($nextSemester)
                                    | পরবর্তী প্রমোশন সেমিস্টার: <strong style="color:#059669">{{ $nextSemester->name }}</strong>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap">
                        {{-- Checked Promotion Trigger Button --}}
                        <button type="button" id="btnPromoteChecked" onclick="openBulkPromoteCheckedModal()" class="btn btn-primary" style="display:none; font-weight:800; font-size:13px; background:#2563eb; border-color:#2563eb">
                            <i class="fa-solid fa-user-graduate"></i> নির্বাচিত (<span id="checkedCountBadge">0</span> জন) শিক্ষার্থীকে প্রমোশন দিন
                        </button>

                        {{-- Bulk Promotion Action --}}
                        @if($isSemesterBased && $nextSemester && $eligibleCount > 0)
                            <form method="POST" action="{{ route('admin.promotions.bulk-promote') }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে সকল যোগ্য শিক্ষার্থীকে ({{ $eligibleCount }} জন) পরবর্তী সেমিস্টার \'{{ $nextSemester->name }}\'-এ প্রমোশন দিতে চান?')">
                                @csrf
                                <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                                <input type="hidden" name="from_semester_id" value="{{ $selectedSemesterId }}">
                                <input type="hidden" name="to_semester_id" value="{{ $nextSemester->id }}">
                                @foreach($studentEvaluations as $ev)
                                    @if(in_array($ev['standing'], ['ELIGIBLE_PROMOTION', 'ELIGIBLE_RETAKE']))
                                        <input type="hidden" name="student_ids[]" value="{{ $ev['student']->id }}">
                                    @endif
                                @endforeach
                                <button type="submit" style="background:linear-gradient(135deg, #059669 0%, #047857 100%); color:#fff; border:none; padding:10px 18px; border-radius:10px; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(5,150,105,0.25)">
                                    <i class="fa-solid fa-forward-step"></i>
                                    সকল যোগ্য শিক্ষার্থীকে ({{ $eligibleCount }} জন) '{{ $nextSemester->name }}'-এ প্রমোশন দিন
                                </button>
                            </form>
                        @elseif($isSemesterBased && $nextSemester && $eligibleCount === 0)
                            <button type="button" class="btn btn-outline" onclick="selectAllStudentsForForcePromote()" style="font-weight:700; font-size:12.5px; border-color:#f59e0b; color:#b45309; background:#fffbeb" title="পরীক্ষার ফলাফল বাকি থাকলেও অ্যাডমিন বিশেষ বিবেচনায় প্রমোশন দিতে পারেন">
                                <i class="fa-solid fa-bolt"></i> বিশেষ প্রমোশনের জন্য সবাইকে সিলেক্ট করুন
                            </button>
                        @elseif($isSemesterBased && !$nextSemester)
                            <span style="font-size:12.5px; background:#ecfdf5; color:#065f46; padding:6px 12px; border-radius:8px; font-weight:700">
                                🎓 এটি এই কোর্সের চূড়ান্ত সেমিস্টার (কোর্স সমাপ্তি)
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Student Evaluation Table --}}
                <div class="promo-table-wrap">
                    <table class="promo-table">
                        <thead>
                            <tr>
                                <th style="width:40px; text-align:center">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" title="সকল শিক্ষার্থী সিলেক্ট করুন">
                                </th>
                                <th>শিক্ষার্থী</th>
                                <th>উত্তীর্ণ / অনুত্তীর্ণ বিষয়</th>
                                <th>মোট নম্বর ও গড়</th>
                                <th>জিপিএ (GPA)</th>
                                <th>অনুত্তীর্ণ বিষয়সমূহ</th>
                                <th>মূল্যায়ন স্ট্যাটাস</th>
                                <th style="text-align:right">পদক্ষেপ (Action)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentEvaluations as $st)
                                <tr>
                                    {{-- Checkbox --}}
                                    <td style="text-align:center">
                                        @if(!in_array($st['standing'], ['ALREADY_PROMOTED', 'ALREADY_READMISSION']))
                                            <input type="checkbox" class="student-select-cb" value="{{ $st['student']->id }}" 
                                                   data-name="{{ $st['student']->name }}" 
                                                   data-standing="{{ $st['standing'] }}"
                                                   onchange="updateCheckedSelection()">
                                        @else
                                            <i class="fa-solid fa-check" style="color:#10b981" title="ইতিমধ্যে সম্পন্ন"></i>
                                        @endif
                                    </td>

                                    {{-- Student Info --}}
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px">
                                            <div style="width:36px; height:36px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:700; color:#334155; font-size:14px">
                                                {{ mb_substr($st['student']->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong style="color:#0f172a">{{ $st['student']->name }}</strong><br>
                                                <span style="color:#64748b; font-size:11.5px">{{ $st['student']->student_code ?? 'ID: '.$st['student']->id }}</span>
                                                @if($st['student']->phone)
                                                    <span style="color:#94a3b8; font-size:11px">| {{ $st['student']->phone }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Pass/Fail Breakdown --}}
                                    <td>
                                        @if($st['evaluated_count'] > 0)
                                            <span style="display:inline-block; background:#dcfce7; color:#166534; font-weight:700; padding:2px 8px; border-radius:6px; font-size:11.5px">
                                                {{ $st['pass_count'] }} পাস
                                            </span>
                                            @if($st['fail_count'] > 0)
                                                <span style="display:inline-block; background:#fee2e2; color:#991b1b; font-weight:700; padding:2px 8px; border-radius:6px; font-size:11.5px; margin-left:4px">
                                                    {{ $st['fail_count'] }} ফেল
                                                </span>
                                            @endif
                                        @else
                                            <span style="color:#94a3b8; font-size:12px">অমূল্যায়িত</span>
                                        @endif
                                    </td>

                                    {{-- Total Marks & Average --}}
                                    <td>
                                        @if($st['evaluated_count'] > 0)
                                            <strong>{{ $st['total_marks'] }}</strong>
                                            <div style="font-size:11px; color:#64748b">গড়: {{ $st['avg_marks'] }}</div>
                                        @else
                                            <span style="color:#94a3b8">—</span>
                                        @endif
                                    </td>

                                    {{-- GPA --}}
                                    <td>
                                        @if($st['evaluated_count'] > 0)
                                            <span style="font-weight:800; color: {{ $st['avg_gpa'] >= 2.0 ? '#059669' : '#dc2626' }}">
                                                {{ number_format($st['avg_gpa'], 2) }}
                                            </span>
                                        @else
                                            <span style="color:#94a3b8">—</span>
                                        @endif
                                    </td>

                                    {{-- Failed Subjects List --}}
                                    <td>
                                        @if(!empty($st['failed_subjects']) && count($st['failed_subjects']) > 0)
                                            <div style="display:flex; flex-direction:column; gap:3px">
                                                @foreach($st['failed_subjects'] as $fs)
                                                    <span style="display:inline-block; font-size:11px; background:#fef2f2; color:#991b1b; padding:2px 6px; border-radius:4px; border:1px solid #fee2e2">
                                                        {{ $fs['name'] }} ({{ $fs['mark'] }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span style="color:#10b981; font-size:12px; font-weight:600">সব পাস</span>
                                        @endif
                                    </td>

                                    {{-- Status Badge --}}
                                    <td>
                                        @if($st['standing'] === 'ELIGIBLE_PROMOTION')
                                            <span class="badge-eligible"><i class="fa-solid fa-circle-check"></i> {{ $st['status_text'] }}</span>
                                        @elseif($st['standing'] === 'ELIGIBLE_RETAKE')
                                            <span class="badge-retake-warn"><i class="fa-solid fa-rotate"></i> {{ $st['status_text'] }}</span>
                                        @elseif($st['standing'] === 'NEEDS_READMISSION')
                                            <span class="badge-readmission-warn"><i class="fa-solid fa-triangle-exclamation"></i> {{ $st['status_text'] }}</span>
                                        @elseif($st['standing'] === 'ALREADY_PROMOTED')
                                            <span class="badge-done"><i class="fa-solid fa-check-double"></i> ইতিমধ্যে প্রমোশন সম্পন্ন</span>
                                        @elseif($st['standing'] === 'ALREADY_READMISSION')
                                            <span class="badge-readmission-warn"><i class="fa-solid fa-user-clock"></i> রি-এডমিশন প্রক্রিয়ায় আছে</span>
                                        @else
                                            <span class="badge-pending-warn"><i class="fa-solid fa-clock"></i> {{ $st['status_text'] }}</span>
                                        @endif
                                    </td>

                                    {{-- Action Buttons --}}
                                    <td style="text-align:right; white-space:nowrap">
                                        @if(in_array($st['action_type'], ['PROMOTE', 'RETAKE_PROMOTE']))
                                            @if($nextSemester)
                                                <button type="button" class="btn-action-promote" 
                                                        onclick="openSingleQuickPromoteModal({{ $st['student']->id }}, '{{ addslashes($st['student']->name) }}', '{{ $st['student']->student_code }}', '{{ $st['standing'] === 'ELIGIBLE_RETAKE' ? 'FORCE_PROMOTED' : 'PROMOTED' }}', '{{ $st['standing'] === 'ELIGIBLE_RETAKE' ? 'রিটেক পরীক্ষা সাপেক্ষে সেমিস্টার প্রমোশন প্রদান' : 'নিয়মিত একাডেমিক ফলাফলের ভিত্তিতে প্রমোশন প্রদান' }}')">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> প্রমোশন দিন
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline" onclick="openManualPromotionModal({{ $st['student']->id }}, '{{ addslashes($st['student']->name) }}', '{{ $st['student']->student_code }}', {{ $st['student']->enrollments->first()?->course_id ?? 'null' }}, {{ $selectedSemesterId ?? 'null' }}, {{ $nextSemester?->id ?? 'null' }})">
                                                    ম্যানুয়াল প্রমোশন
                                                </button>
                                            @endif
                                        @elseif($st['action_type'] === 'READMISSION')
                                            <form method="POST" action="{{ route('admin.promotions.send-readmission') }}" style="display:inline" onsubmit="return confirm('শিক্ষার্থী \'{{ $st['student']->name }}\' ২টির বেশি বিষয়ে ফেল করায় রি-এডমিশন তালিকায় পাঠাতে চান?')">
                                                @csrf
                                                <input type="hidden" name="student_id" value="{{ $st['student']->id }}">
                                                <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                                                <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                                                <button type="submit" class="btn-action-readmission">
                                                    <i class="fa-solid fa-user-xmark"></i> রি-এডমিশনে পাঠান
                                                </button>
                                            </form>
                                            @if($nextSemester)
                                                <button type="button" class="btn btn-sm btn-ghost" style="color:#b45309; margin-left:4px" onclick="openSingleQuickPromoteModal({{ $st['student']->id }}, '{{ addslashes($st['student']->name) }}', '{{ $st['student']->student_code }}', 'FORCE_PROMOTED', 'বিশেষ বিবেচনায় অ্যাডমিন ফোর্স প্রমোশন')" title="বিশেষ ক্ষমতা প্রয়োগ করে ফোর্স প্রমোশন দিন">
                                                    <i class="fa-solid fa-bolt"></i> ফোর্স
                                                </button>
                                            @endif
                                        @elseif($st['action_type'] === 'GENERATE_MARKS')
                                            <div style="display:inline-flex; gap:6px; align-items:center">
                                                @if($nextSemester)
                                                    <button type="button" class="btn-action-promote" style="background:#4f46e5; border-color:#4f46e5" 
                                                            onclick="openSingleQuickPromoteModal({{ $st['student']->id }}, '{{ addslashes($st['student']->name) }}', '{{ $st['student']->student_code }}', 'FORCE_PROMOTED', 'পরীক্ষার মার্ক অপেক্ষমান থাকা সত্ত্বেও অ্যাডমিন কর্তৃক বিশেষ বিবেচনায় প্রমোশন প্রদান')" 
                                                            title="মার্ক বাকি থাকলেও বিশেষ বিবেচনায় প্রমোশন দিতে পারেন">
                                                        <i class="fa-solid fa-bolt"></i> প্রমোশন দিন
                                                    </button>
                                                @endif
                                                <a href="{{ route('admin.result-book.index', ['batch_id' => $selectedBatch->id, 'semester_id' => $selectedSemesterId]) }}" class="btn btn-sm btn-outline" style="font-size:11.5px" title="রেজাল্ট বুকে গিয়ে মার্ক জেনারেট করুন">
                                                    <i class="fa-solid fa-square-poll-vertical"></i> রেজাল্ট বুক
                                                </a>
                                            </div>
                                        @elseif($st['standing'] === 'ALREADY_PROMOTED')
                                            <span class="badge-done"><i class="fa-solid fa-check-double"></i> সম্পন্ন</span>
                                        @elseif($st['standing'] === 'ALREADY_READMISSION')
                                            <span class="badge-readmission-warn"><i class="fa-solid fa-user-clock"></i> রি-এডমিশনে আছে</span>
                                        @else
                                            @if($nextSemester)
                                                <button type="button" class="btn btn-sm btn-primary" onclick="openSingleQuickPromoteModal({{ $st['student']->id }}, '{{ addslashes($st['student']->name) }}', '{{ $st['student']->student_code }}', 'PROMOTED', 'প্রমোশন প্রদান')">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> প্রমোশন দিন
                                                </button>
                                            @else
                                                <span style="font-size:12px; color:#94a3b8">—</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:35px; color:#64748b">
                                        এই ব্যাচ ও সেমিস্টারে কোনো সক্রিয় শিক্ষার্থী পাওয়া যায়নি।
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Friendly Prompt Card --}}
            <div style="background:#fff; border:1px dashed #cbd5e1; border-radius:14px; padding:45px 20px; text-align:center; margin-bottom:24px">
                <div style="width:60px; height:60px; border-radius:50%; background:#ecfdf5; color:#059669; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:14px">
                    <i class="fa-solid fa-arrow-up-wide-short"></i>
                </div>
                <h3 style="margin:0 0 6px; font-size:18px; font-weight:800; color:#0f172a">ব্যাচ ও সেমিস্টার নির্বাচন করুন</h3>
                <p style="margin:0; font-size:13.5px; color:#64748b; max-width:520px; display:inline-block">
                    উপরের ড্রপডাউন থেকে ব্যাচ নির্বাচন করলে রানিং সেমিস্টার স্বয়ংক্রিয়ভাবে লোড হবে এবং সংশ্লিষ্ট সেমিস্টারের সকল পরীক্ষার অডিট ও শিক্ষার্থীদের প্রমোশন/রি-এডমিশন স্ট্যাটাস প্রদর্শিত হবে।
                </p>
            </div>
        @endif

        {{-- ── SECTION C: Previous Promotion Records History ── --}}
        <div class="card" style="border-radius:14px; overflow:hidden; padding:0; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.03)">
            <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                <h3 style="margin:0; font-size:15px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#64748b"></i>
                    পূর্ববর্তী প্রমোশন রেকর্ড (Promotion History)
                </h3>
                <span style="font-size:12px; color:#64748b">মোট রেকর্ড: {{ $promotions->count() }}</span>
            </div>

            <div class="promo-table-wrap">
                <table class="promo-table">
                    <thead>
                        <tr>
                            <th>শিক্ষার্থী (Student)</th>
                            <th>পূর্বের সেমিস্টার</th>
                            <th>উত্তীর্ণ সেমিস্টার</th>
                            <th>সিদ্ধান্ত (Decision)</th>
                            <th>অনুমোদনকারী</th>
                            <th>তারিখ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $prom)
                            <tr>
                                <td>
                                    <strong>{{ $prom->student->name ?? '—' }}</strong><br>
                                    <span style="font-size:11.5px; color:#64748b">{{ $prom->student->student_code ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $prom->fromSemester->name ?? '—' }}</td>
                                <td><strong style="color:#065f46">{{ $prom->toSemester->name ?? '—' }}</strong></td>
                                <td>
                                    @if($prom->decision === 'PROMOTED')
                                        <span class="badge-eligible">Promoted (উত্তীর্ণ)</span>
                                    @elseif($prom->decision === 'FORCE_PROMOTED')
                                        <span class="badge-retake-warn">Force Promoted (রিটেকসহ)</span>
                                    @else
                                        <span class="badge-readmission-warn">Held Back (স্থগিত)</span>
                                    @endif
                                </td>
                                <td style="color:#64748b">{{ $prom->decidedBy->name ?? 'অ্যাডমিন' }}</td>
                                <td style="color:#64748b">{{ $prom->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:30px; color:#94a3b8">
                                    কোনো পূর্ববর্তী প্রমোশন রেকর্ড পাওয়া যায়নি।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Create Promotion Modal --}}
    <div class="modal-overlay" id="addPromotionModal" onclick="if(event.target===this) closeModal('addPromotionModal')" style="display:none; align-items:flex-start; justify-content:center; z-index:99999; font-family:'Kalpurush', sans-serif; padding:30px 15px; overflow-y:auto">
        <div class="modal" style="max-width:540px; width:100%; border-radius:14px; background:#fff; margin:0 auto; max-height:calc(100vh - 60px); overflow-y:auto; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
            <div class="modal-header" style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                <span class="modal-title" style="font-weight:800; font-size:16px; color:#0f172a">
                    <i class="fa-solid fa-graduation-cap" style="color:#059669"></i>
                    ম্যানুয়াল প্রমোশন রেকর্ড (Record Promotion)
                </span>
                <button type="button" class="modal-close" onclick="closeModal('addPromotionModal')" style="background:none; border:none; font-size:20px; cursor:pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.promotions.store') }}" id="manualPromotionForm">
                @csrf
                <div class="modal-body" style="padding:20px">
                    <div class="form-group" style="margin-bottom:16px; position:relative;" id="studentSearchDropdownContainer">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">
                            শিক্ষার্থী নির্বাচন করুন <span class="required" style="color:#e11d48">*</span>
                        </label>
                        
                        {{-- Hidden real select for form submission --}}
                        <select name="student_id" id="modalStudentSelect" style="position:absolute; opacity:0; pointer-events:none; height:0; width:0;">
                            <option value="">-- শিক্ষার্থী বাছাই করুন --</option>
                            @foreach($students as $st)
                                @php
                                    $activeEnr = $st->enrollments->first();
                                    $studentCourseId = $activeEnr?->course_id ?? '';
                                    $studentSemesterId = $activeEnr?->semester_id ?? '';
                                @endphp
                                <option value="{{ $st->id }}" 
                                        data-code="{{ $st->student_code ?? '' }}" 
                                        data-name="{{ $st->name }}"
                                        data-course-id="{{ $studentCourseId }}"
                                        data-semester-id="{{ $studentSemesterId }}">
                                    {{ $st->student_code ?? 'N/A' }} — {{ $st->name }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Searchable Trigger Box --}}
                        <div id="studentSelectTrigger" 
                             style="height:44px; border-radius:10px; border:1.5px solid #cbd5e1; background:#fff; padding:0 14px; display:flex; align-items:center; justify-content:space-between; cursor:pointer; font-family:'Kalpurush',sans-serif; font-size:14px; color:#1e293b; transition:all 0.2s ease;">
                            <span id="studentSelectDisplay" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:500;">
                                -- শিক্ষার্থী বাছাই করুন --
                            </span>
                            <div style="display:flex; align-items:center; gap:8px">
                                <i class="fa-solid fa-magnifying-glass" style="color:#94a3b8; font-size:12px;"></i>
                                <i class="fa-solid fa-chevron-down" style="color:#64748b; font-size:11px; transition:transform 0.2s" id="studentChevronIcon"></i>
                            </div>
                        </div>

                        {{-- Dropdown Search and Options Panel --}}
                        <div id="studentDropdownPanel" 
                             style="display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:999999; background:#fff; border:1.5px solid #059669; border-radius:10px; box-shadow:0 12px 30px rgba(0,0,0,0.18); overflow:hidden; font-family:'Kalpurush',sans-serif;">
                            
                            {{-- Search input inside dropdown --}}
                            <div style="padding:10px 12px; background:#f8fafc; border-bottom:1px solid #e2e8f0; position:relative;">
                                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:22px; top:50%; transform:translateY(-50%); color:#059669; font-size:13px;"></i>
                                <input type="text" id="studentDropdownSearchInput" 
                                       placeholder="শিক্ষার্থীর নাম, আইডি বা কোড লিখে খুঁজুন..." 
                                       style="width:100%; height:38px; padding:0 12px 0 34px; border-radius:8px; border:1.5px solid #cbd5e1; font-size:13px; font-family:'Kalpurush',sans-serif; outline:none; transition:border-color 0.2s;"
                                       autocomplete="off">
                            </div>

                            {{-- Scrollable List of Students --}}
                            <div id="studentOptionsContainer" style="max-height:220px; overflow-y:auto; padding:4px 0;">
                                <div class="student-select-opt" data-value="" data-text="-- শিক্ষার্থী বাছাই করুন --"
                                     data-course-id="" data-semester-id=""
                                     style="padding:10px 14px; cursor:pointer; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9;">
                                    -- শিক্ষার্থী বাছাই করুন --
                                </div>
                                @foreach($students as $st)
                                    @php
                                        $activeEnr = $st->enrollments->first();
                                        $studentCourseId = $activeEnr?->course_id ?? '';
                                        $studentSemesterId = $activeEnr?->semester_id ?? '';
                                        $studentCourseName = $activeEnr?->course?->name ?? '';
                                    @endphp
                                    <div class="student-select-opt" 
                                         data-value="{{ $st->id }}" 
                                         data-text="{{ $st->student_code ?? 'N/A' }} — {{ $st->name }}"
                                         data-course-id="{{ $studentCourseId }}"
                                         data-semester-id="{{ $studentSemesterId }}"
                                         data-search="{{ strtolower(($st->student_code ?? '') . ' ' . $st->name . ' ' . ($st->phone ?? '') . ' ' . $studentCourseName) }}"
                                         style="padding:9px 14px; cursor:pointer; font-size:13.5px; color:#1e293b; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f8fafc; transition:background 0.15s;">
                                        <div>
                                            <strong style="color:#059669; font-family:monospace; font-size:13px">{{ $st->student_code ?? 'N/A' }}</strong> 
                                            <span style="color:#94a3b8; margin:0 4px">—</span> 
                                            <span style="font-weight:600">{{ $st->name }}</span>
                                            @if($studentCourseName)
                                                <div style="font-size:11.5px; color:#64748b; margin-top:2px">
                                                    <i class="fa-solid fa-graduation-cap" style="font-size:10px; color:#059669"></i> {{ $studentCourseName }}
                                                </div>
                                            @endif
                                        </div>
                                        @if($st->phone)
                                            <span style="font-size:11px; color:#94a3b8">{{ $st->phone }}</span>
                                        @endif
                                    </div>
                                @endforeach
                                <div id="studentDropdownNoResults" style="display:none; padding:18px; text-align:center; color:#94a3b8; font-size:13px;">
                                    <i class="fa-solid fa-circle-question" style="margin-right:6px; color:#f59e0b"></i> কোনো শিক্ষার্থী পাওয়া যায়নি
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row" style="display:flex; gap:12px; margin-bottom:16px">
                        <div class="form-group" style="flex:1">
                            <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">বর্তমান / পূর্বের সেমিস্টার</label>
                            <select name="from_semester_id" id="modalFromSemester" class="form-control" style="height:42px; border-radius:8px">
                                <option value="">-- বর্তমান সেমিস্টার --</option>
                                @foreach($semesters->groupBy(fn($s) => $s->course?->name ?? 'অন্যান্য কোর্স') as $courseName => $grpCourseSemesters)
                                    <optgroup label="{{ $courseName }}" data-course-id="{{ $grpCourseSemesters->first()?->course_id }}">
                                        @foreach($grpCourseSemesters as $sem)
                                            <option value="{{ $sem->id }}" 
                                                    data-course-id="{{ $sem->course_id }}" 
                                                    data-sequence="{{ $sem->sequence_no }}">
                                                {{ $sem->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">পরবর্তী সেমিস্টার</label>
                            <select name="to_semester_id" id="modalToSemester" class="form-control" style="height:42px; border-radius:8px">
                                <option value="">-- পরবর্তী সেমিস্টার --</option>
                                @foreach($semesters->groupBy(fn($s) => $s->course?->name ?? 'অন্যান্য কোর্স') as $courseName => $grpCourseSemesters)
                                    <optgroup label="{{ $courseName }}" data-course-id="{{ $grpCourseSemesters->first()?->course_id }}">
                                        @foreach($grpCourseSemesters as $sem)
                                            <option value="{{ $sem->id }}" 
                                                    data-course-id="{{ $sem->course_id }}" 
                                                    data-sequence="{{ $sem->sequence_no }}">
                                                {{ $sem->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">প্রমোশন সিদ্ধান্ত *</label>
                        <select name="decision" class="form-control" style="height:42px; border-radius:8px" required>
                            <option value="PROMOTED">PROMOTED — নিয়মিত প্রমোশন (সব শর্ত পূরণ হয়েছে)</option>
                            <option value="FORCE_PROMOTED">FORCE_PROMOTED — রিটেকসহ বিশেষ প্রমোশন (অ্যাডমিন অনুমোদন)</option>
                            <option value="HELD_BACK">HELD_BACK — স্থগিত / সেমিস্টার রিপিট</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">অতিরিক্ত নোট বা মন্তব্য</label>
                        <textarea name="notes" class="form-control" style="border-radius:8px" rows="3" placeholder="প্রমোশন সংক্রান্ত মন্তব্য বা রেফারেন্স..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addPromotionModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="font-weight:700">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedBatch)
    {{-- ── Quick Single Student Promotion Modal ── --}}
    <div id="quickSinglePromoteModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.65); z-index:99999; align-items:center; justify-content:center; padding:16px">
        <div class="modal-content" style="background:#fff; border-radius:16px; width:100%; max-width:540px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden">
            <div style="background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color:#fff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center">
                <div>
                    <h3 style="margin:0; font-size:17px; font-weight:800; display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-user-graduate" style="color:#10b981"></i>
                        একক শিক্ষার্থী সেমিস্টার প্রমোশন
                    </h3>
                    <div style="font-size:12px; color:#94a3b8; margin-top:2px">ব্যাচ: {{ $selectedBatch->name }}</div>
                </div>
                <button type="button" onclick="closeModal('quickSinglePromoteModal')" style="background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer; padding:4px 8px; line-height:1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.promotions.store') }}" style="margin:0">
                @csrf
                <input type="hidden" name="student_id" id="quickPromoteStudentId">
                <input type="hidden" name="from_semester_id" value="{{ $selectedSemesterId }}">

                <div style="padding:22px 24px">
                    {{-- Student Banner --}}
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; margin-bottom:18px; display:flex; align-items:center; justify-content:space-between">
                        <div>
                            <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase">শিক্ষার্থী</span>
                            <div style="font-size:15px; font-weight:800; color:#0f172a" id="quickPromoteStudentName">--</div>
                            <div style="font-size:12px; color:#64748b; font-family:monospace" id="quickPromoteStudentCode">--</div>
                        </div>
                        <div style="text-align:right">
                            <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase">বর্তমান সেমিস্টার</span>
                            <div style="font-size:13px; font-weight:700; color:#475569">
                                {{ $selectedBatch->course?->semesters->firstWhere('id', $selectedSemesterId)?->name ?? 'রানিং সেমিস্টার' }}
                            </div>
                        </div>
                    </div>

                    {{-- Next Semester Target --}}
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px; color:#334155">
                            উত্তীর্ণ সেমিস্টার (Promote To) <span style="color:#ef4444">*</span>
                        </label>
                        <select name="to_semester_id" id="quickPromoteToSemester" class="form-control" style="height:42px; border-radius:8px; border:1px solid #cbd5e1" required>
                            @foreach($selectedBatch->course?->semesters ?? [] as $sem)
                                <option value="{{ $sem->id }}" {{ $nextSemester && $sem->id == $nextSemester->id ? 'selected' : '' }}>
                                    {{ $sem->name }} (ক্রম: {{ $sem->sequence_no }}) {{ $nextSemester && $sem->id == $nextSemester->id ? '— পরবর্তী সেমিস্টার' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Decision --}}
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px; color:#334155">
                            প্রমোশন সিদ্ধান্ত (Decision) <span style="color:#ef4444">*</span>
                        </label>
                        <select name="decision" id="quickPromoteDecision" class="form-control" style="height:42px; border-radius:8px; border:1px solid #cbd5e1" required>
                            <option value="PROMOTED">PROMOTED — নিয়মিত প্রমোশন (সব শর্ত পূরণ হয়েছে)</option>
                            <option value="FORCE_PROMOTED">FORCE_PROMOTED — বিশেষ প্রমোশন / রিটেক পরীক্ষা সাপেক্ষে অনুমোদন</option>
                            <option value="HELD_BACK">HELD_BACK — স্থগিত / সেমিস্টার রিপিট</option>
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px; color:#334155">
                            প্রমোশন সংক্রান্ত মন্তব্য / নোট
                        </label>
                        <textarea name="notes" id="quickPromoteNotes" class="form-control" rows="2" style="border-radius:8px; border:1px solid #cbd5e1" placeholder="অ্যাকাডেমিক কারণ, রিটেক শর্ত বা মন্তব্য..."></textarea>
                    </div>

                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; font-size:12px; color:#166534; display:flex; gap:8px; align-items:flex-start">
                        <i class="fa-solid fa-circle-info" style="margin-top:2px; color:#15803d"></i>
                        <div>প্রমোশন কার্যকর করলে শিক্ষার্থীর বর্তমান সেমিস্টার আপডেট হবে এবং পরবর্তী সেমিস্টারের ভর্তি ও কোর্স ফি ইনভয়েস তৈরি হবে।</div>
                    </div>
                </div>

                <div class="modal-footer" style="padding:14px 24px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px">
                    <button type="button" class="btn btn-outline" onclick="closeModal('quickSinglePromoteModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#059669; border-color:#059669; font-weight:700">
                        <i class="fa-solid fa-check"></i> প্রমোশন নিশ্চিত করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Bulk Promote Checked Students Modal ── --}}
    <div id="bulkPromoteCheckedModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.65); z-index:99999; align-items:center; justify-content:center; padding:16px">
        <div class="modal-content" style="background:#fff; border-radius:16px; width:100%; max-width:580px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden">
            <div style="background:linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color:#fff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center">
                <div>
                    <h3 style="margin:0; font-size:17px; font-weight:800; display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-users-gear" style="color:#60a5fa"></i>
                        একসাথে একাধিক শিক্ষার্থীর প্রমোশন
                    </h3>
                    <div style="font-size:12px; color:#bfdbfe; margin-top:2px">ব্যাচ: {{ $selectedBatch->name }}</div>
                </div>
                <button type="button" onclick="closeModal('bulkPromoteCheckedModal')" style="background:none; border:none; color:#bfdbfe; font-size:20px; cursor:pointer; padding:4px 8px; line-height:1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.promotions.bulk-promote') }}" style="margin:0" id="bulkPromoteCheckedForm">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                <input type="hidden" name="from_semester_id" value="{{ $selectedSemesterId }}">
                <div id="bulkCheckedStudentInputsContainer"></div>

                <div style="padding:22px 24px">
                    {{-- Selected count info --}}
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 16px; margin-bottom:16px">
                        <div style="font-weight:800; font-size:14px; color:#1e40af; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between">
                            <span><i class="fa-solid fa-user-check"></i> নির্বাচিত শিক্ষার্থী: <strong id="bulkCheckedCountText">0</strong> জন</span>
                            <span style="font-size:12px; color:#64748b; font-weight:600">বর্তমান: {{ $selectedBatch->course?->semesters->firstWhere('id', $selectedSemesterId)?->name ?? 'রানিং সেমিস্টার' }}</span>
                        </div>
                        <div id="bulkCheckedStudentsList" style="max-height:100px; overflow-y:auto; font-size:12px; background:#fff; border:1px solid #dbeafe; padding:8px 12px; border-radius:6px; line-height:1.6; color:#334155">
                            {{-- Populated by JS --}}
                        </div>
                    </div>

                    {{-- Next Semester Target --}}
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px; color:#334155">
                            উত্তীর্ণ সেমিস্টার (Promote To) <span style="color:#ef4444">*</span>
                        </label>
                        <select name="to_semester_id" class="form-control" style="height:42px; border-radius:8px; border:1px solid #cbd5e1" required>
                            @foreach($selectedBatch->course?->semesters ?? [] as $sem)
                                <option value="{{ $sem->id }}" {{ $nextSemester && $sem->id == $nextSemester->id ? 'selected' : '' }}>
                                    {{ $sem->name }} (ক্রম: {{ $sem->sequence_no }}) {{ $nextSemester && $sem->id == $nextSemester->id ? '— পরবর্তী সেমিস্টার' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Decision --}}
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px; color:#334155">
                            প্রমোশন ধরন (Decision) <span style="color:#ef4444">*</span>
                        </label>
                        <select name="decision" class="form-control" style="height:42px; border-radius:8px; border:1px solid #cbd5e1" required>
                            <option value="PROMOTED">PROMOTED — নিয়মিত প্রমোশন (উত্তীর্ণদের জন্য)</option>
                            <option value="FORCE_PROMOTED" selected>FORCE_PROMOTED — বিশেষ বিবেচনায় প্রমোশন (অ্যাডমিন অনুমোদন)</option>
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px; color:#334155">
                            মন্তব্য বা রেফারেন্স
                        </label>
                        <textarea name="notes" class="form-control" rows="2" style="border-radius:8px; border:1px solid #cbd5e1" placeholder="একসাথে একাধিক শিক্ষার্থীর প্রমোশন সংক্রান্ত অনুমোদন নোট..."></textarea>
                    </div>

                    <div style="background:#fffbeb; border:1px solid #fef3c7; border-radius:8px; padding:10px 14px; font-size:12px; color:#92400e; display:flex; gap:8px; align-items:flex-start">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px; color:#d97706"></i>
                        <div>প্রমোশন কার্যকর করার সাথে সাথে সকল নির্বাচিত শিক্ষার্থীর এনরোলমেন্ট নতুন সেমিস্টারে হালনাগাদ হবে এবং সেমিস্টার ফি ইনভয়েস জেনারেট হবে।</div>
                    </div>
                </div>

                <div class="modal-footer" style="padding:14px 24px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:10px">
                    <button type="button" class="btn btn-outline" onclick="closeModal('bulkPromoteCheckedModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#2563eb; border-color:#2563eb; font-weight:700">
                        <i class="fa-solid fa-paper-plane"></i> প্রমোশন কার্যকর করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Client-side Dynamic Scripts --}}
    <script>
        // ── 0. Course -> Batch Cascading Filter ──
        window.filterPromotionBatches = function(courseId) {
            const batchSelect = document.getElementById('batchSelect');
            if (!batchSelect) return;
            const options = batchSelect.querySelectorAll('option');
            let hasSelected = false;
            options.forEach(opt => {
                if (!opt.value) {
                    opt.style.display = '';
                    return;
                }
                const optCourseId = opt.getAttribute('data-course-id');
                if (!courseId || optCourseId == courseId) {
                    opt.style.display = '';
                    if (opt.selected) hasSelected = true;
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) opt.selected = false;
                }
            });
            if (!hasSelected && courseId) {
                batchSelect.value = '';
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const courseSelect = document.getElementById('courseSelect');
            if (courseSelect && courseSelect.value) {
                filterPromotionBatches(courseSelect.value);
            }
        });

        // ── 1. Batch Change & Dynamic Semester Handling ──
        window.handleBatchChange = function(batchId) {
            const semContainer = document.getElementById('semesterSelectContainer');
            const semSelect = document.getElementById('semesterSelect');

            if (!batchId) {
                if (semContainer) semContainer.style.display = 'none';
                if (semSelect) semSelect.innerHTML = '<option value="">-- সকল সেমিস্টার --</option>';
                return;
            }

            // Fetch course structure via AJAX
            fetch(`{{ route('admin.final-marks.batch-subjects') }}?batch_id=${batchId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.has_semesters && data.semesters && data.semesters.length > 0) {
                        if (semContainer) semContainer.style.display = 'block';
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
                        }
                    } else {
                        // Subject-based course -> Hide semester selection
                        if (semContainer) semContainer.style.display = 'none';
                        if (semSelect) {
                            semSelect.innerHTML = '<option value="">-- সেমিস্টার প্রযোজ্য নয় --</option>';
                            semSelect.value = '';
                        }
                    }

                    // Auto-submit filter form to fetch audit data for newly selected batch
                    const form = document.getElementById('promoFilterForm');
                    if (form) {
                        form.submit();
                    }
                })
                .catch(err => {
                    console.error('Error loading batch details:', err);
                });
        };

        // ── 2. Modal Helper & Searchable Dropdown ──
        window.syncPromotionSemesters = function(courseId, fromSemId, toSemId) {
            const fromSelect = document.getElementById('modalFromSemester');
            const toSelect = document.getElementById('modalToSemester');
            if (!fromSelect || !toSelect) return;

            [fromSelect, toSelect].forEach(select => {
                const optgroups = select.querySelectorAll('optgroup');
                optgroups.forEach(group => {
                    const groupCourseId = group.getAttribute('data-course-id');
                    const match = !courseId || groupCourseId == courseId;
                    group.style.display = match ? '' : 'none';
                    group.hidden = !match;
                    group.disabled = !match;
                    group.querySelectorAll('option').forEach(opt => {
                        opt.style.display = match ? '' : 'none';
                        opt.hidden = !match;
                        opt.disabled = !match;
                    });
                });
            });

            if (fromSemId !== undefined && fromSemId !== null) {
                fromSelect.value = fromSemId || '';
            }

            if (toSemId !== undefined && toSemId !== null) {
                toSelect.value = toSemId || '';
            } else if (fromSemId && courseId) {
                // Auto calculate next semester in sequence
                const selectedOpt = fromSelect.querySelector(`option[value="${fromSemId}"]`);
                if (selectedOpt) {
                    const currentSeq = parseInt(selectedOpt.getAttribute('data-sequence')) || 0;
                    const nextOpt = toSelect.querySelector(`option[data-course-id="${courseId}"][data-sequence="${currentSeq + 1}"]`);
                    if (nextOpt) {
                        toSelect.value = nextOpt.value;
                    } else {
                        toSelect.value = '';
                    }
                }
            } else if (!courseId) {
                if (!fromSemId) fromSelect.value = '';
                if (!toSemId) toSelect.value = '';
            }
        };

        window.openManualPromotionModal = function(studentId, studentName, studentCode, courseId, currentSemesterId, nextSemesterId) {
            const select = document.getElementById('modalStudentSelect');
            if (select) {
                select.value = studentId || '';
            }
            const display = document.getElementById('studentSelectDisplay');
            if (display) {
                display.innerText = (studentCode ? studentCode + ' — ' : '') + (studentName || '-- শিক্ষার্থী বাছাই করুন --');
            }
            const trigger = document.getElementById('studentSelectTrigger');
            if (trigger) {
                trigger.style.borderColor = '#cbd5e1';
                trigger.style.boxShadow = 'none';
            }
            const searchInput = document.getElementById('studentDropdownSearchInput');
            if (searchInput) {
                searchInput.value = '';
            }

            // Sync course & semesters
            if (!studentId) {
                syncPromotionSemesters('', '', '');
            } else {
                if (!courseId && select) {
                    const opt = select.querySelector(`option[value="${studentId}"]`);
                    if (opt) {
                        courseId = opt.getAttribute('data-course-id');
                        currentSemesterId = opt.getAttribute('data-semester-id');
                    }
                }
                syncPromotionSemesters(courseId, currentSemesterId, nextSemesterId);
            }

            openModal('addPromotionModal');
        };

        // Initialize searchable student select in promotion modal
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('studentSearchDropdownContainer');
            if (!container) return;

            const trigger = document.getElementById('studentSelectTrigger');
            const display = document.getElementById('studentSelectDisplay');
            const chevron = document.getElementById('studentChevronIcon');
            const panel = document.getElementById('studentDropdownPanel');
            const searchInput = document.getElementById('studentDropdownSearchInput');
            const optionsContainer = document.getElementById('studentOptionsContainer');
            const hiddenSelect = document.getElementById('modalStudentSelect');
            const noResults = document.getElementById('studentDropdownNoResults');
            const optionItems = optionsContainer.querySelectorAll('.student-select-opt');
            const form = document.getElementById('manualPromotionForm');
            const fromSemesterSelect = document.getElementById('modalFromSemester');
            const toSemesterSelect = document.getElementById('modalToSemester');

            function openDropdown() {
                panel.style.display = 'block';
                trigger.style.borderColor = '#059669';
                trigger.style.boxShadow = '0 0 0 3px rgba(5,150,105,0.15)';
                chevron.style.transform = 'rotate(180deg)';
                searchInput.value = '';
                filterOptions('');
                setTimeout(() => searchInput.focus(), 60);
            }

            function closeDropdown() {
                panel.style.display = 'none';
                if (!hiddenSelect.value && formSubmitted) {
                    trigger.style.borderColor = '#ef4444';
                    trigger.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
                } else {
                    trigger.style.borderColor = '#cbd5e1';
                    trigger.style.boxShadow = 'none';
                }
                chevron.style.transform = 'rotate(0deg)';
            }

            let formSubmitted = false;

            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                if (panel.style.display === 'block') {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });

            panel.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            function filterOptions(term) {
                let visibleCount = 0;
                term = term.toLowerCase().trim();
                optionItems.forEach(opt => {
                    const searchData = opt.getAttribute('data-search') || '';
                    if (!term) {
                        opt.style.display = 'flex';
                        visibleCount++;
                    } else {
                        if (!opt.getAttribute('data-value')) {
                            opt.style.display = 'none';
                        } else if (searchData.includes(term)) {
                            opt.style.display = 'flex';
                            visibleCount++;
                        } else {
                            opt.style.display = 'none';
                        }
                    }
                });
                if (noResults) {
                    noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
                }
            }

            searchInput.addEventListener('input', function() {
                filterOptions(this.value);
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const firstVisible = Array.from(optionItems).find(opt => opt.getAttribute('data-value') && opt.style.display !== 'none');
                    if (firstVisible) {
                        firstVisible.click();
                    }
                }
            });

            optionItems.forEach(opt => {
                opt.addEventListener('mouseenter', function() {
                    this.style.background = '#f0fdf4';
                });
                opt.addEventListener('mouseleave', function() {
                    this.style.background = '';
                });
                opt.addEventListener('click', function() {
                    const val = this.getAttribute('data-value');
                    const text = this.getAttribute('data-text');
                    const courseId = this.getAttribute('data-course-id');
                    const semId = this.getAttribute('data-semester-id');
                    hiddenSelect.value = val;
                    display.innerText = text;
                    trigger.style.borderColor = '#cbd5e1';
                    trigger.style.boxShadow = 'none';
                    formSubmitted = false;
                    closeDropdown();

                    // Filter semesters by student's enrolled course and auto-select
                    syncPromotionSemesters(courseId, semId);
                });
            });

            // When from-semester is manually changed, suggest next semester
            if (fromSemesterSelect && toSemesterSelect) {
                fromSemesterSelect.addEventListener('change', function() {
                    const selectedOpt = this.options[this.selectedIndex];
                    if (selectedOpt && selectedOpt.value) {
                        const courseId = selectedOpt.getAttribute('data-course-id');
                        const currentSeq = parseInt(selectedOpt.getAttribute('data-sequence')) || 0;
                        const nextOpt = toSemesterSelect.querySelector(`option[data-course-id="${courseId}"][data-sequence="${currentSeq + 1}"]`);
                        if (nextOpt) {
                            toSemesterSelect.value = nextOpt.value;
                        }
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!hiddenSelect.value) {
                        e.preventDefault();
                        formSubmitted = true;
                        trigger.style.borderColor = '#ef4444';
                        trigger.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.2)';
                        openDropdown();
                    }
                });
            }
        });

        window.openModal = function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeModal = function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'none';
                document.body.style.overflow = '';
            }
        };

        // ── Promotion Policy Guide Box Toggle ──
        window.togglePromotionGuide = function() {
            const box = document.getElementById('promotionGuideBox');
            if (!box) return;
            if (box.style.display === 'none' || !box.style.display) {
                box.style.display = 'block';
                box.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                box.style.display = 'none';
            }
        };

        // ── Checkbox & Bulk Promotion Selection ──
        window.toggleSelectAll = function(masterCb) {
            const checkboxes = document.querySelectorAll('.student-select-cb');
            checkboxes.forEach(cb => {
                cb.checked = masterCb.checked;
            });
            updateCheckedSelection();
        };

        window.selectAllStudentsForForcePromote = function() {
            const checkboxes = document.querySelectorAll('.student-select-cb');
            checkboxes.forEach(cb => {
                cb.checked = true;
            });
            const masterCb = document.getElementById('selectAllCheckbox');
            if (masterCb) masterCb.checked = true;
            updateCheckedSelection();
            const btn = document.getElementById('btnPromoteChecked');
            if (btn) {
                btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        };

        window.updateCheckedSelection = function() {
            const checkboxes = document.querySelectorAll('.student-select-cb:checked');
            const count = checkboxes.length;
            const btn = document.getElementById('btnPromoteChecked');
            const badge = document.getElementById('checkedCountBadge');
            const masterCb = document.getElementById('selectAllCheckbox');

            if (badge) badge.innerText = count;

            if (count > 0) {
                if (btn) btn.style.display = 'inline-flex';
            } else {
                if (btn) btn.style.display = 'none';
                if (masterCb) masterCb.checked = false;
            }
        };

        window.openSingleQuickPromoteModal = function(studentId, studentName, studentCode, defaultDecision, defaultNotes) {
            const idInput = document.getElementById('quickPromoteStudentId');
            if (idInput) idInput.value = studentId;

            const nameEl = document.getElementById('quickPromoteStudentName');
            if (nameEl) nameEl.innerText = studentName;

            const codeEl = document.getElementById('quickPromoteStudentCode');
            if (codeEl) codeEl.innerText = studentCode || ('আইডি: ' + studentId);
            
            const decSelect = document.getElementById('quickPromoteDecision');
            if (decSelect && defaultDecision) {
                decSelect.value = defaultDecision;
            }
            const notesEl = document.getElementById('quickPromoteNotes');
            if (notesEl) {
                notesEl.value = defaultNotes || '';
            }

            openModal('quickSinglePromoteModal');
        };

        window.openBulkPromoteCheckedModal = function() {
            const checkedBoxes = document.querySelectorAll('.student-select-cb:checked');
            if (checkedBoxes.length === 0) {
                alert('অনুগ্রহ করে অন্তত একজন শিক্ষার্থী নির্বাচন করুন।');
                return;
            }

            const container = document.getElementById('bulkCheckedStudentInputsContainer');
            const listEl = document.getElementById('bulkCheckedStudentsList');
            const countText = document.getElementById('bulkCheckedCountText');

            if (container) container.innerHTML = '';
            if (listEl) listEl.innerHTML = '';
            if (countText) countText.innerText = checkedBoxes.length;

            const names = [];
            checkedBoxes.forEach(cb => {
                const sId = cb.value;
                const sName = cb.getAttribute('data-name') || ('শিক্ষার্থী #' + sId);
                names.push(sName);

                if (container) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'student_ids[]';
                    input.value = sId;
                    container.appendChild(input);
                }
            });

            if (listEl) {
                listEl.innerText = names.join(', ');
            }

            openModal('bulkPromoteCheckedModal');
        };
    </script>
</x-admin-layout>
