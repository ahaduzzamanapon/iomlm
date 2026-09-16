<x-admin-layout>
    <x-slot name="title">একাউন্ট ও রাজস্ব রিপোর্ট (Accounts Financial Reports)</x-slot>

    <style>
        .rep-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px; font-family: 'Kalpurush', sans-serif; }
        .rep-title { display: flex; align-items: center; gap: 12px; font-size: 22px; font-weight: 700; color: #1e293b; }
        .rep-title i { width: 42px; height: 42px; border-radius: 50%; background: #ecfdf5; color: #047857; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; }
        .rep-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px; font-family: 'Kalpurush', sans-serif; }
        .rep-kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; }
        .rep-kpi-label { font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 6px; }
        .rep-kpi-val { font-size: 26px; font-weight: 800; line-height: 1; }
        .table-rep th { background: #f8fafc; font-size: 12px; font-weight: 700; color: #475569; padding: 12px 14px; border-bottom: 1px solid #e2e8f0; font-family: 'Kalpurush', sans-serif; }
        .table-rep td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; font-family: 'Kalpurush', sans-serif; }
    </style>

    {{-- Page Header --}}
    <div class="rep-header">
        <div>
            <div class="rep-title">
                <i class="fa-solid fa-chart-line"></i>
                <div>
                    <div>একাউন্ট ও রাজস্ব রিপোর্ট (Financial &amp; Revenue Reports)</div>
                    <div style="font-size:13px;color:#64748b;font-weight:400">
                        কোর্স-ভিত্তিক, মাস-ভিত্তিক ও নির্দিষ্ট তারিখ রেঞ্জে ফি আদায়ের বিস্তারিত বিবরণী
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <a href="{{ route('admin.accounts.dashboard') }}" class="btn btn-outline" style="font-family:'Kalpurush',sans-serif">
                ← একাউন্টস কাউন্টারে ফিরুন
            </a>
            <a href="{{ route('admin.accounts.invoices') }}" class="btn btn-outline" style="font-family:'Kalpurush',sans-serif">
                সকল ইনভয়েস
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.accounts.reports') }}" class="card" style="padding:18px 20px;margin-bottom:24px;border-radius:12px;font-family:'Kalpurush',sans-serif">
        <div style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap">
            {{-- Course Filter --}}
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px">
                <label style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;display:block">
                    <i class="fa-solid fa-graduation-cap" style="color:#047857"></i> কোর্স নির্বাচন (Course)
                </label>
                <select name="course_id" class="form-control" style="height:40px;font-size:13px">
                    <option value="">সকল কোর্স (All Courses)</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Month Quick Filter --}}
            <div class="form-group" style="margin-bottom:0;width:180px">
                <label style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;display:block">
                    <i class="fa-solid fa-calendar-days" style="color:#2563eb"></i> নির্দিষ্ট মাস (Month)
                </label>
                <input type="month" name="month" class="form-control" value="{{ $month }}" style="height:40px;font-size:13px">
            </div>

            {{-- From Date --}}
            <div class="form-group" style="margin-bottom:0;width:160px">
                <label style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;display:block">
                    হতে তারিখ (From Date)
                </label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}" style="height:40px;font-size:13px">
            </div>

            {{-- To Date --}}
            <div class="form-group" style="margin-bottom:0;width:160px">
                <label style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;display:block">
                    পর্যন্ত তারিখ (To Date)
                </label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}" style="height:40px;font-size:13px">
            </div>

            {{-- Category Filter --}}
            <div class="form-group" style="margin-bottom:0;width:160px">
                <label style="font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;display:block">
                    ফি খাত (Category)
                </label>
                <select name="category" class="form-control" style="height:40px;font-size:13px">
                    <option value="">সকল খাত</option>
                    <option value="SEMESTER" {{ $category === 'SEMESTER' ? 'selected' : '' }}>SEMESTER (সেমিস্টার ফি)</option>
                    <option value="ADMISSION" {{ $category === 'ADMISSION' ? 'selected' : '' }}>ADMISSION (ভর্তি ফি)</option>
                    <option value="FINE" {{ $category === 'FINE' ? 'selected' : '' }}>FINE (এক্টিভিশন/জরিমানা)</option>
                    <option value="RETAKE" {{ $category === 'RETAKE' ? 'selected' : '' }}>RETAKE (রিটেক ফি)</option>
                    <option value="COURSE_TRANSFER" {{ $category === 'COURSE_TRANSFER' ? 'selected' : '' }}>COURSE_TRANSFER</option>
                </select>
            </div>

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary" style="height:40px;padding:0 20px">
                    <i class="fa-solid fa-filter"></i> ফিল্টার রিপোর্ট
                </button>
                @if($courseId || $month || request()->has('from_date') || $category)
                    <a href="{{ route('admin.accounts.reports') }}" class="btn btn-outline" style="height:40px;display:inline-flex;align-items:center">
                        রিসেট
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- KPI Cards --}}
    <div class="rep-kpi-grid">
        <div class="rep-kpi-card" style="border-left:4px solid #10b981">
            <div class="rep-kpi-label">মোট সংগৃহীত ফি (Total Collected)</div>
            <div class="rep-kpi-val" style="color:#047857">৳{{ number_format($totalCollected, 2) }}</div>
        </div>
        <div class="rep-kpi-card" style="border-left:4px solid #2563eb">
            <div class="rep-kpi-label">মোট সফল ট্রানজেকশন সংখ্যা</div>
            <div class="rep-kpi-val" style="color:#1e40af">{{ $payments->count() }}টি</div>
        </div>
        <div class="rep-kpi-card" style="border-left:4px solid #8b5cf6">
            <div class="rep-kpi-label">নির্বাচিত কোর্স</div>
            <div class="rep-kpi-val" style="font-size:18px;color:#6d28d9;margin-top:4px">
                @if($courseId)
                    {{ $courses->firstWhere('id', $courseId)?->name ?? 'কোর্স ফিল্টার' }}
                @else
                    সকল কোর্স
                @endif
            </div>
        </div>
        <div class="rep-kpi-card" style="border-left:4px solid #f59e0b">
            <div class="rep-kpi-label">রিপোর্টের সময়কাল</div>
            <div class="rep-kpi-val" style="font-size:14px;color:#b45309;font-weight:700;margin-top:6px">
                {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} হতে {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
            </div>
        </div>
    </div>

    {{-- Course & Head Breakdown Side-by-Side --}}
    <div class="grid-2" style="margin-bottom:24px;gap:18px;font-family:'Kalpurush',sans-serif">
        {{-- Course-wise Revenue Breakdown --}}
        <div class="card" style="border-radius:12px;overflow:hidden">
            <div class="card-header" style="padding:14px 18px;background:#fff;border-bottom:1px solid #e2e8f0">
                <span class="card-title" style="font-size:15px;font-weight:700">
                    <i class="fa-solid fa-graduation-cap" style="color:#047857"></i> কোর্স-ভিত্তিক ফি আদায় (Course Breakdown)
                </span>
            </div>
            <div class="card-body" style="padding:0">
                <table class="table-rep" style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr>
                            <th>কোর্সের নাম</th>
                            <th style="text-align:right">আদায়ের পরিমাণ (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courseSummary as $cName => $sum)
                        <tr>
                            <td><strong>{{ $cName }}</strong></td>
                            <td style="text-align:right;font-weight:700;color:#047857">৳{{ number_format($sum, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="text-align:center;padding:16px;color:#94a3b8">এই সময়কালে কোনো কোর্স ফি জমা হয়নি।</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Head-wise Revenue Breakdown --}}
        <div class="card" style="border-radius:12px;overflow:hidden">
            <div class="card-header" style="padding:14px 18px;background:#fff;border-bottom:1px solid #e2e8f0">
                <span class="card-title" style="font-size:15px;font-weight:700">
                    <i class="fa-solid fa-tags" style="color:#2563eb"></i> ফি খাত-ভিত্তিক আদায় (Head Breakdown)
                </span>
            </div>
            <div class="card-body" style="padding:0">
                <table class="table-rep" style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr>
                            <th>ফি ক্যাটাগরি / খাত</th>
                            <th style="text-align:right">আদায়ের পরিমাণ (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categorySummary as $cat => $sum)
                        <tr>
                            <td><strong>{{ $cat }}</strong></td>
                            <td style="text-align:right;font-weight:700;color:#1e40af">৳{{ number_format($sum, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="text-align:center;padding:16px;color:#94a3b8">কোনো রাজস্ব জমা হয়নি।</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Transactions Statement Table --}}
    <div class="card" style="border-radius:12px;overflow:hidden">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border-bottom:1px solid #e2e8f0;font-family:'Kalpurush',sans-serif">
            <div>
                <span class="card-title" style="font-size:16px;font-weight:700">
                    <i class="fa-solid fa-list-check" style="color:#047857;margin-right:6px"></i> ট্রানজেকশন অডিট ও আদায়ের পূর্ণাঙ্গ লগ (Detailed Collection Log)
                </span>
                <span style="font-size:12px;color:#64748b;margin-left:6px">সর্বমোট {{ $payments->count() }}টি লেনদেন</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="window.print()" style="font-size:12px">
                <i class="fa-solid fa-print"></i> রিপোর্ট প্রিন্ট করুন
            </button>
        </div>
        <div class="table-wrapper" style="overflow-x:auto">
            <table class="table-rep" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>রসিদ নং</th>
                        <th>শিক্ষার্থী (Student)</th>
                        <th>কোর্স ও ব্যাচ</th>
                        <th>ইনভয়েস বিবরণ / খাত</th>
                        <th>পেমেন্ট মেথড</th>
                        <th>ট্রানজেকশন আইডি</th>
                        <th style="text-align:right">পরিশোধিত টাকা</th>
                        <th>তারিখ</th>
                        <th style="text-align:center">রসিদ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                    <tr>
                        <td style="font-family:monospace;font-weight:700;color:#047857">{{ $pay->payment_no }}</td>
                        <td>
                            <strong>{{ $pay->student->name ?? '—' }}</strong><br>
                            <span style="font-size:11px;color:#64748b">
                                আইডি: {{ $pay->student->student_code ?? 'N/A' }} | ফোন: {{ $pay->student->phone ?? '—' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $cName = $pay->invoice?->enrollment?->course?->name 
                                    ?? $pay->student?->enrollments?->first()?->course?->name ?? '—';
                                $bName = $pay->invoice?->enrollment?->batch?->name 
                                    ?? $pay->student?->enrollments?->first()?->batch?->name ?? '';
                            @endphp
                            <strong>{{ $cName }}</strong>
                            @if($bName)
                                <div style="font-size:11px;color:#64748b">{{ $bName }}</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $pay->invoice?->title ?? '—' }}</strong>
                            <div style="font-size:11px;color:#64748b">
                                খাত: {{ $pay->invoice?->category ?? 'OTHER' }} ({{ $pay->invoice?->invoice_no ?? '' }})
                            </div>
                        </td>
                        <td>
                            <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#f1f5f9;color:#1e293b;font-weight:600">
                                {{ $pay->payment_method }}
                            </span>
                        </td>
                        <td style="font-family:monospace;font-size:11px">{{ $pay->transaction_id ?? '—' }}</td>
                        <td style="text-align:right;font-weight:800;color:#047857">৳{{ number_format($pay->amount, 2) }}</td>
                        <td style="font-size:12px;color:#475569">
                            {{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y, h:i A') : '—' }}
                        </td>
                        <td style="text-align:center">
                            <a href="{{ route('admin.accounts.payments.receipt', $pay) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:2px 8px;font-size:11px">
                                রসিদ ↗
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:26px;color:#94a3b8;font-family:'Kalpurush',sans-serif">
                            নির্বাচিত ফিল্টার শর্তে কোনো ট্রানজেকশন রেকর্ড পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
