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
                <a href="{{ route('admin.readmissions.index') }}" class="btn btn-outline" style="font-weight:700; font-size:13px">
                    <i class="fa-solid fa-user-clock"></i> রি-এডমিশন তালিকা
                </a>
                <button class="btn btn-primary" onclick="openModal('addPromotionModal')" style="font-weight:700; font-size:13px">
                    <i class="fa-solid fa-plus"></i> ম্যানুয়াল প্রমোশন রেকর্ড
                </button>
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
                
                {{-- 1. Batch Selection --}}
                <div style="flex:2; min-width:240px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        ১. ব্যাচ নির্বাচন করুন (Select Batch) *
                    </label>
                    <select name="batch_id" id="batchSelect" class="form-control" style="height:44px; border-radius:10px" required onchange="handleBatchChange(this.value)">
                        <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ (request('batch_id') == $b->id || ($selectedBatch?->id == $b->id)) ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->course->title ?? $b->course->name ?? 'কোর্স' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Semester Selection (Auto-hides if course is SUBJECT_BASED) --}}
                <div id="semesterSelectContainer" style="flex:1.5; min-width:210px; display: {{ ($isSemesterBased ?? false) ? 'block' : 'none' }};">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        ২. সেমিস্টার (Semester)
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
                                <a href="{{ route('admin.result-book.index', ['batch_id' => $selectedBatch->id, 'semester_id' => $selectedSemesterId]) }}" class="btn btn-sm btn-outline" style="background:#fff; font-weight:700">
                                    <i class="fa-solid fa-square-poll-vertical"></i> রেজাল্ট ম্যানেজমেন্টে যান
                                </a>
                            @endif
                        </div>
                    </div>

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
            <div class="card" style="margin-bottom:24px; padding:0; border-radius:14px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.03)">
                <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px">
                    <div>
                        <h2 style="margin:0; font-size:16px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px">
                            <i class="fa-solid fa-user-check" style="color:#059669"></i>
                            শিক্ষার্থীদের মূল্যায়ন ফলাফল ও প্রমোশন সিদ্ধান্ত
                        </h2>
                        <div style="font-size:12.5px; color:#64748b; margin-top:2px">
                            ব্যাচ: <strong>{{ $selectedBatch->name }}</strong>
                            @if($isSemesterBased && $selectedSemesterId)
                                | সেমিস্টার: <strong>{{ $courseSemesters->firstWhere('id', $selectedSemesterId)?->name ?? 'সেমিস্টার' }}</strong>
                            @endif
                        </div>
                    </div>

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
                    @elseif($isSemesterBased && !$nextSemester)
                        <span style="font-size:12.5px; background:#ecfdf5; color:#065f46; padding:6px 12px; border-radius:8px; font-weight:700">
                            🎓 এটি এই কোর্সের চূড়ান্ত সেমিস্টার (কোর্স সমাপ্তি)
                        </span>
                    @endif
                </div>

                {{-- Student Evaluation Table --}}
                <div class="promo-table-wrap">
                    <table class="promo-table">
                        <thead>
                            <tr>
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
                                                <form method="POST" action="{{ route('admin.promotions.store') }}" style="display:inline" onsubmit="return confirm('শিক্ষার্থী \'{{ $st['student']->name }}\'-কে পরবর্তী সেমিস্টার \'{{ $nextSemester->name }}\'-এ প্রমোশন দিতে চান?')">
                                                    @csrf
                                                    <input type="hidden" name="student_id" value="{{ $st['student']->id }}">
                                                    <input type="hidden" name="from_semester_id" value="{{ $selectedSemesterId }}">
                                                    <input type="hidden" name="to_semester_id" value="{{ $nextSemester->id }}">
                                                    <input type="hidden" name="decision" value="{{ $st['standing'] === 'ELIGIBLE_RETAKE' ? 'FORCE_PROMOTED' : 'PROMOTED' }}">
                                                    <input type="hidden" name="notes" value="{{ $st['standing'] === 'ELIGIBLE_RETAKE' ? 'রিটেক পরীক্ষা সাপেক্ষে সেমিস্টার প্রমোশন প্রদান' : 'নিয়মিত একাডেমিক ফলাফলের ভিত্তিতে প্রমোশন প্রদান' }}">
                                                    <button type="submit" class="btn-action-promote">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> প্রমোশন দিন
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline" onclick="openManualPromotionModal({{ $st['student']->id }}, '{{ addslashes($st['student']->name) }}', '{{ $st['student']->student_code }}')">
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
                                        @elseif($st['action_type'] === 'GENERATE_MARKS')
                                            <a href="{{ route('admin.result-book.index', ['batch_id' => $selectedBatch->id, 'semester_id' => $selectedSemesterId]) }}" class="btn btn-sm btn-outline" style="font-size:11.5px">
                                                <i class="fa-solid fa-square-poll-vertical"></i> রেজাল্ট ম্যানেজমেন্ট
                                            </a>
                                        @else
                                            <span style="font-size:12px; color:#94a3b8">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:35px; color:#64748b">
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
    <div class="modal-overlay" id="addPromotionModal" style="display:none; align-items:center; justify-content:center; z-index:99999; font-family:'Kalpurush', sans-serif">
        <div class="modal" style="max-width:540px; width:100%; border-radius:14px; background:#fff; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
            <div class="modal-header" style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                <span class="modal-title" style="font-weight:800; font-size:16px; color:#0f172a">
                    <i class="fa-solid fa-graduation-cap" style="color:#059669"></i>
                    ম্যানুয়াল প্রমোশন রেকর্ড (Record Promotion)
                </span>
                <button type="button" class="modal-close" onclick="closeModal('addPromotionModal')" style="background:none; border:none; font-size:20px; cursor:pointer">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.promotions.store') }}">
                @csrf
                <div class="modal-body" style="padding:20px">
                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">শিক্ষার্থী নির্বাচন করুন *</label>
                        <select name="student_id" id="modalStudentSelect" class="form-control" style="height:42px; border-radius:8px" required>
                            <option value="">-- শিক্ষার্থী বাছাই করুন --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}">{{ $st->student_code ?? 'N/A' }} — {{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row" style="display:flex; gap:12px; margin-bottom:16px">
                        <div class="form-group" style="flex:1">
                            <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">বর্তমান / পূর্বের সেমিস্টার</label>
                            <select name="from_semester_id" id="modalFromSemester" class="form-control" style="height:42px; border-radius:8px">
                                <option value="">-- বর্তমান সেমিস্টার --</option>
                                @foreach($semesters as $sem)
                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="flex:1">
                            <label style="font-weight:700; font-size:13px; display:block; margin-bottom:6px">পরবর্তী সেমিস্টার</label>
                            <select name="to_semester_id" id="modalToSemester" class="form-control" style="height:42px; border-radius:8px">
                                <option value="">-- পরবর্তী সেমিস্টার --</option>
                                @foreach($semesters as $sem)
                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
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

    {{-- Client-side Dynamic Scripts --}}
    <script>
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

        // ── 2. Modal Helper ──
        window.openManualPromotionModal = function(studentId, studentName, studentCode) {
            const select = document.getElementById('modalStudentSelect');
            if (select) {
                select.value = studentId;
            }
            openModal('addPromotionModal');
        };

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
    </script>
</x-admin-layout>
