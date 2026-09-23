<x-admin-layout>
    <x-slot name="title">৬-সেমিস্টার ব্যাচ মেধা তালিকা (Batch Combined Merit Ranking)</x-slot>

    <style>
        .bm-wrapper {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .bm-header {
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
        .nav-tabs-custom {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
            padding-bottom: 2px;
        }
        .nav-tab-item {
            padding: 10px 20px;
            font-size: 13.5px;
            font-weight: 800;
            color: #64748b;
            text-decoration: none;
            border-radius: 10px 10px 0 0;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .nav-tab-item.active {
            color: #1e40af;
            background: #eff6ff;
            border-bottom: 3px solid #1e40af;
        }
        .nav-tab-item:hover:not(.active) {
            color: #0f172a;
            background: #f8fafc;
        }
        .rank-badge-1 {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.35);
        }
        .rank-badge-2 {
            background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
            color: #fff;
            box-shadow: 0 4px 10px rgba(100, 116, 139, 0.3);
        }
        .rank-badge-3 {
            background: linear-gradient(135deg, #b45309 0%, #78350f 100%);
            color: #fff;
            box-shadow: 0 4px 10px rgba(120, 53, 15, 0.3);
        }
        .rank-badge-other {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .card { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>

    <div class="bm-wrapper">
        {{-- Header --}}
        <div class="bm-header no-print">
            <div>
                <h1 style="margin:0 0 6px; font-size:24px; font-weight:800; display:flex; align-items:center; gap:10px">
                    <i class="fa-solid fa-trophy" style="color:#fef08a"></i> ৬-সেমিস্টার ব্যাচ সামগ্রিক মেধা তালিকা (Batch Combined Merit List)
                </h1>
                <p style="margin:0; font-size:13.5px; color:#bfdbfe">
                    ৬টি সেমিস্টার সমাপ্তকারী ব্যাচের শিক্ষার্থীদের সম্মিলিত সিজিপিএ (CGPA), কওমি মান ও ১ম, ২য়, ৩য় মেধা স্থান
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap">
                <button onclick="window.print()" 
                   style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); color:#fff; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:7px">
                    <i class="fa-solid fa-print"></i> মেধা তালিকা প্রিন্ট করুন
                </button>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <div class="nav-tabs-custom no-print">
            <a href="{{ route('admin.result-book.index', request()->query()) }}" class="nav-tab-item">
                <i class="fa-solid fa-book-bookmark"></i> বিষয়ভিত্তিক ফলাফল ও সংশোধন (Subject Mark Sheets)
            </a>
            <a href="{{ route('admin.result-book.batch-merit', request()->query()) }}" class="nav-tab-item active">
                <i class="fa-solid fa-trophy"></i> ৬-সেমিস্টার ব্যাচ মেধা তালিকা (Batch 6-Sem Merit: ১ম, ২য়, ৩য়...)
            </a>
            <a href="{{ route('admin.final-marks.index') }}" class="nav-tab-item">
                <i class="fa-solid fa-calculator"></i> ফাইনাল মার্ক জেনারেটর
            </a>
        </div>

        {{-- Filter Card --}}
        <div class="card no-print" style="margin-bottom:24px; padding:20px 24px; border-radius:16px; background:#fff; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.03)">
            <form method="GET" action="{{ route('admin.result-book.batch-merit') }}" style="display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end">
                <div style="flex:1; min-width:280px">
                    <label class="form-label" style="font-weight:700; font-size:13px; color:#1e293b; margin-bottom:6px; display:block">
                        ব্যাচ নির্বাচন করুন (Select Batch)
                    </label>
                    <select name="batch_id" class="form-control" style="height:42px; border-radius:10px" onchange="this.form.submit()">
                        <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->course->name ?? 'কোর্স' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="height:42px; padding:0 22px; font-weight:800; border-radius:10px; background:#1e40af; border-color:#1e40af">
                        <i class="fa-solid fa-ranking-star"></i> মেধা তালিকা দেখুন
                    </button>
                </div>
            </form>
        </div>

        @if($selectedBatch)
            {{-- Batch Summary Info --}}
            <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:14px; padding:18px 22px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px">
                <div>
                    <span style="font-size:12px; color:#64748b; font-weight:700">নির্বাচিত ব্যাচ:</span>
                    <h2 style="margin:2px 0 0; font-size:20px; font-weight:900; color:#0f172a">
                        {{ $selectedBatch->name }} &middot; <span style="font-size:16px; color:#2563eb; font-weight:700">{{ $selectedBatch->course->name ?? '' }}</span>
                    </h2>
                </div>
                <div style="display:flex; gap:14px; flex-wrap:wrap">
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:8px 16px; text-align:center">
                        <div style="font-size:11px; color:#64748b; font-weight:700">মোট শিক্ষার্থী</div>
                        <div style="font-size:18px; font-weight:900; color:#0f172a">{{ $rankedStudents->count() }} জন</div>
                    </div>
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:8px 16px; text-align:center">
                        <div style="font-size:11px; color:#64748b; font-weight:700">সেমিস্টার সংখ্যা</div>
                        <div style="font-size:18px; font-weight:900; color:#15803d">{{ $semesters->count() }} টি</div>
                    </div>
                </div>
            </div>

            {{-- Leaderboard Table --}}
            <div class="card" style="border-radius:16px; overflow:hidden; background:#fff; border:1px solid #e2e8f0; box-shadow:0 4px 14px rgba(0,0,0,0.04)">
                <div style="padding:16px 22px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                    <div style="font-size:16px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-medal" style="color:#eab308"></i> ৬-সেমিস্টার সামগ্রিক মেধা তালিকা (Overall 6-Semester Merit Ranking)
                    </div>
                    <div style="font-size:12.5px; color:#64748b">
                        সর্বোচ্চ সিজিপিএ (CGPA) ও মোট নম্বরের ভিত্তিতে সাজানো
                    </div>
                </div>

                @if($rankedStudents->isEmpty())
                    <div style="text-align:center; padding:55px 20px; color:#64748b">
                        <i class="fa-solid fa-folder-open" style="font-size:42px; color:#cbd5e1; margin-bottom:14px; display:block"></i>
                        <strong style="font-size:16px; color:#1e293b; display:block; margin-bottom:6px">এই ব্যাচে কোনো ফলাফল বা শিক্ষার্থী পাওয়া যায়নি</strong>
                        <span style="font-size:13px; color:#64748b">অন্য কোনো ব্যাচ নির্বাচন করুন অথবা ফাইনাল মার্ক জেনারেটর থেকে ফলাফল তৈরি করুন।</span>
                    </div>
                @else
                    <div style="overflow-x:auto">
                        <table style="width:100%; border-collapse:collapse; font-size:13px">
                            <thead>
                                <tr style="background:#f1f5f9; border-bottom:2px solid #cbd5e1">
                                    <th style="padding:14px 10px; text-align:center; width:85px">মেধাক্রম</th>
                                    <th style="padding:14px 14px; text-align:left">শিক্ষার্থী ও রোল</th>
                                    @foreach($semesters as $sem)
                                        <th style="padding:14px 8px; text-align:center">
                                            @php
                                                $semLabels = [1 => '১ম সেম', 2 => '২য় সেম', 3 => '৩য় সেম', 4 => '৪র্থ সেম', 5 => '৫ম সেম', 6 => '৬ষ্ঠ সেম'];
                                            @endphp
                                            {{ $semLabels[$sem->sequence_no] ?? $sem->name }}<br>
                                            <small style="color:#64748b; font-weight:normal">SGPA</small>
                                        </th>
                                    @endforeach
                                    <th style="padding:14px 10px; text-align:center">অর্জিত ক্রেডিট</th>
                                    <th style="padding:14px 10px; text-align:center">সর্বমোট নম্বর</th>
                                    <th style="padding:14px 12px; text-align:center; background:#ecfdf5; color:#064e3b; font-weight:900">
                                        চূড়ান্ত সিজিপিএ<br><small style="font-size:11px">CGPA / 5.00</small>
                                    </th>
                                    <th style="padding:14px 10px; text-align:center">কওমি মান</th>
                                    <th style="padding:14px 12px; text-align:center" class="no-print">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rankedStudents as $rs)
                                @php
                                    $pos = $rs['merit_position'];
                                    $badgeClass = match($pos) {
                                        1 => 'rank-badge-1',
                                        2 => 'rank-badge-2',
                                        3 => 'rank-badge-3',
                                        default => 'rank-badge-other',
                                    };
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9; transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:14px 10px; text-align:center">
                                        <span class="{{ $badgeClass }}" style="font-weight:900; padding:6px 12px; border-radius:20px; font-size:13px; display:inline-block">
                                            @if($pos === 1) 🥇 @elseif($pos === 2) 🥈 @elseif($pos === 3) 🥉 @endif
                                            {{ $rs['merit_rank_bengali'] }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 14px">
                                        <strong style="color:#0f172a; font-size:14px">{{ $rs['student']->name }}</strong>
                                        <div style="font-size:12px; color:#2563eb; font-weight:700; margin-top:2px">
                                            {{ $rs['student']->student_code ?? $rs['student']->student_id ?? '—' }}
                                        </div>
                                    </td>
                                    @foreach($semesters as $sem)
                                        @php
                                            $sgpaData = $rs['semesters_sgpa'][$sem->sequence_no] ?? null;
                                        @endphp
                                        <td style="padding:14px 8px; text-align:center">
                                            @if($sgpaData && $sgpaData['credit'] > 0)
                                                <strong style="color:{{ $sgpaData['sgpa'] >= 3.5 ? '#15803d' : ($sgpaData['sgpa'] >= 2.0 ? '#1e293b' : '#dc2626') }}">
                                                    {{ number_format($sgpaData['sgpa'], 2) }}
                                                </strong>
                                            @else
                                                <span style="color:#cbd5e1">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td style="padding:14px 10px; text-align:center; font-weight:700; color:#475569">
                                        {{ $rs['total_credits_earned'] }} / {{ $rs['total_credits_attempted'] }}
                                    </td>
                                    <td style="padding:14px 10px; text-align:center; font-weight:800; color:#0f172a">
                                        {{ $rs['total_marks'] }}
                                    </td>
                                    <td style="padding:14px 12px; text-align:center; background:#ecfdf5">
                                        <span style="font-size:18px; font-weight:900; color:{{ $rs['cgpa'] >= 3.5 ? '#059669' : ($rs['cgpa'] >= 2.0 ? '#1e3a8a' : '#dc2626') }}">
                                            {{ number_format($rs['cgpa'], 2) }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 10px; text-align:center">
                                        <span style="font-size:11.5px; font-weight:800; color:#0f766e; background:#ccfbf1; padding:4px 10px; border-radius:12px; display:inline-block">
                                            {{ $rs['qawmi_grade']['name_bn'] }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 12px; text-align:center" class="no-print">
                                        <a href="{{ route('admin.students.transcript', $rs['student']->id) }}" target="_blank"
                                           style="padding:6px 12px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:8px; text-decoration:none; font-size:11.5px; font-weight:800; display:inline-flex; align-items:center; gap:5px">
                                            <i class="fa-solid fa-file-invoice"></i> ট্রান্সক্রিপ্ট
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @else
            <div class="card" style="padding:60px 20px; text-align:center; border-radius:16px; border:1px solid #e2e8f0; background:#fff">
                <i class="fa-solid fa-arrow-up-long" style="font-size:36px; color:#94a3b8; margin-bottom:12px; display:block"></i>
                <h3 style="margin:0 0 6px; font-size:17px; font-weight:800; color:#1e293b">মেধা তালিকা দেখতে অনুগ্রহ করে ওপর থেকে একটি ব্যাচ নির্বাচন করুন</h3>
                <p style="margin:0; font-size:13.5px; color:#64748b">ব্যাচ নির্বাচন করলেই ৬টি সেমিস্টার মিলে ১ম, ২য়, ৩য় মেধা ক্রম ও সমন্বিত সিজিপিএ স্বয়ংক্রিয়ভাবে প্রদর্শিত হবে।</p>
            </div>
        @endif
    </div>
</x-admin-layout>
