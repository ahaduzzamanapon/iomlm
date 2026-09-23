<x-student-layout>
    <x-slot name="title">My Fees & Dues</x-slot>

    {{-- Page Header & Course Selector --}}
    <div class="page-header"
        style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px">
        <div class="page-header-left">
            <h1 style="display:flex;align-items:center;gap:10px; flex-wrap:wrap">
                My Fees &amp; Payment Receipts
                @if($course)
                    <span class="badge badge-primary no-dot"
                        style="font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px">
                        {{ $course->name }}
                        ({{ $courseType === 'SUBJECT_BASED' ? 'Subject-Based Course' : 'Semester-Based Course' }})
                    </span>
                @endif
            </h1>
            <p>Track your running semester dues, overall course fees, and download official payment receipts</p>
        </div>

        {{-- Multi-Course Selector --}}
        @if(isset($studentCourses) && $studentCourses->count() > 1)
            <div
                style="display:flex; align-items:center; gap:8px; background:#fff; padding:6px 12px; border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,0.03)">
                <span style="font-size:12px; font-weight:700; color:#64748b">সিলেক্টেড কোর্স:</span>
                @foreach($studentCourses as $sCourse)
                    <a href="{{ route('student.fees.index', ['course_id' => $sCourse->id]) }}"
                        style="padding:5px 12px; border-radius:20px; font-size:12px; font-weight:700; text-decoration:none; transition:all .2s; {{ ($course && $course->id == $sCourse->id) ? 'background:#2563eb; color:#fff;' : 'background:#f1f5f9; color:#475569;' }}">
                        {{ $sCourse->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Alert Banners --}}
    @if(session('success'))
        <div
            style="background:#dcfce7; color:#15803d; padding:14px 18px; border-radius:12px; border:1px solid #bbf7d0; margin-bottom:20px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div
            style="background:#fee2e2; color:#b91c1c; padding:14px 18px; border-radius:12px; border:1px solid #fca5a5; margin-bottom:20px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── STATS SUMMARY CARDS ── --}}
    <div class="stats-grid"
        style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">

        {{-- Card 1: Running Semester Dues or Course Dues --}}
        @if($courseType === 'SEMESTER_BASED')
            <div class="stat-card"
                style="border: 2px solid {{ $runningSemesterDue > 0 ? '#fecdd3' : '#a7f3d0' }}; background: {{ $runningSemesterDue > 0 ? '#fff1f2' : '#f0fdf4' }}">
                <div class="stat-info">
                    <div class="stat-value"
                        style="color:{{ $runningSemesterDue > 0 ? '#be123c' : '#047857' }}; font-weight:800; font-size:22px">
                        ৳{{ number_format($runningSemesterDue, 2) }}
                    </div>
                    <div class="stat-label"
                        style="font-weight:700; color:{{ $runningSemesterDue > 0 ? '#9f1239' : '#065f46' }}">
                        {{ $runningSemesterDue > 0 ? 'Running Semester Dues' : 'Running Semester Cleared' }}
                    </div>
                    <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">
                        {{ $runningSemesterName }}
                    </div>
                </div>
            </div>
        @else
            <div class="stat-card">

                <div class="stat-info">
                    <div class="stat-value" style="color:{{ $totalDue > 0 ? '#e11d48' : '#10b981' }}">
                        ৳{{ number_format($totalDue, 2) }}
                    </div>
                    <div class="stat-label" style="font-weight:700">Course Dues (Subject-Based)</div>
                    <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Overall course tuition dues</div>
                </div>
            </div>
        @endif

        {{-- Card 2: Total Outstanding Dues --}}
        <div class="stat-card">

            <div class="stat-info">
                <div class="stat-value"
                    style="color:{{ $totalDue > 0 ? '#e11d48' : '#10b981' }}; font-weight:800; font-size:22px">
                    ৳{{ number_format($totalDue, 2) }}
                </div>
                <div class="stat-label" style="font-weight:700">Total Outstanding Dues</div>
                <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Combined total across all semesters
                    &amp; fees</div>
            </div>
        </div>

        {{-- Card 3: Total Fees Paid --}}
        <div class="stat-card">

            <div class="stat-info">
                <div class="stat-value" style="font-weight:800; font-size:22px">৳{{ number_format($totalPaid, 2) }}
                </div>
                <div class="stat-label" style="font-weight:700">Total Fees Paid</div>
                <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Total payments received &amp;
                    verified</div>
            </div>
        </div>

    </div>

    {{-- ── PORTAL NAVIGATION TABS: MONTHLY PAYMENTS vs DUES & VOUCHERS ── --}}
    <div
        style="display:flex;gap:12px;margin-bottom:24px;border-bottom:2px solid #e2e8f0;padding-bottom:12px;font-family:'Kalpurush',sans-serif;flex-wrap:wrap">
        <button type="button" id="tabBtn_monthly" onclick="switchPortalTab('monthly')"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;border:none;transition:all .2s;background:#2563eb;color:#fff;box-shadow:0 4px 10px rgba(37,99,235,0.25)">
            <i class="fa-solid fa-calendar-days"></i> মান্থলি পেমেন্ট (Monthly Fees & Installments)
        </button>
        <button type="button" id="tabBtn_dues" onclick="switchPortalTab('dues')"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;border:1.5px solid #cbd5e1;transition:all .2s;background:#fff;color:#475569">
            <i class="fa-solid fa-file-invoice-dollar"></i> ডিউ সেকশন ও একাউন্টস লেজার (Dues, Paid, History & Vouchers)
        </button>
    </div>

    {{-- ═══════════════ TAB 1: MONTHLY PAYMENTS SECTION ═══════════════ --}}
    {{-- ═══════════════ TAB 1: MONTHLY PAYMENTS SECTION ═══════════════ --}}
    <div id="portalSection_monthly">

        {{-- ── Step 1: Select Payment Amount (Matching Client Screenshot 1) ── --}}
        <div class="card"
            style="margin-bottom:24px;border:1px solid #cbd5e1;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.04);font-family:'Kalpurush',sans-serif">
            <div
                style="background:#38bdf8;color:#fff;padding:12px 20px;font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-forward-step"></i> Step 1: Select Payment Amount
            </div>
            <div style="padding:20px">
                @php
                    $isAdminSession = session()->has('admin_impersonator_id')
                        || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));
                @endphp

                @if($isAdminSession)
                    <div
                        style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:9px 16px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px;color:#1e40af">
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="fa-solid fa-user-shield" style="font-size:16px;color:#2563eb"></i>
                            <span><strong>অ্যাডমিন মোড:</strong> আপনি নিচে টেবিলের ফি আইটেমের টাকা সরাসরি এডিট
                                (কমানো/বাড়ানো) করে সেভ করতে পারবেন।</span>
                        </div>
                        <span
                            style="background:#2563eb;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:12px">অ্যাডমিন
                            এডিট সক্রিয়</span>
                    </div>
                @endif

                {{-- Semester Selector --}}
                <div
                    style="display:flex;justify-content:center;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap">
                    <label style="font-weight:700;color:#1e293b;font-size:14px">Check Due For:</label>
                    <select id="checkDueSemesterSelect"
                        onchange="location.href='{{ route('student.fees.index') }}?course_id={{ $course?->id }}&semester_id=' + this.value"
                        style="padding:6px 16px;border:1.5px solid #10b981;border-radius:6px;font-size:13.5px;font-weight:600;color:#0f172a;background:#fff;outline:none;cursor:pointer">
                        @foreach($semesterDropdownOptions as $sOpt)
                            <option value="{{ $sOpt['id'] }}" {{ $selectedSemesterId == $sOpt['id'] ? 'selected' : '' }}>
                                {{ $sOpt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Prior Due Guard Warning --}}
                @if($hasPriorSemesterDue && ($selectedSemester?->sequence_no > 1))
                    <div
                        style="background:#fff1f2;border:1.5px solid #fecdd3;border-radius:8px;padding:14px 18px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                        <div
                            style="display:flex;align-items:center;gap:10px;color:#9f1239;font-size:13.5px;font-weight:600">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:18px;color:#e11d48"></i>
                            <span>
                                <strong>পূর্বের সেমিস্টারের বকেয়া অপরিশোধিত:</strong> পূর্বের সেমিস্টারের বকেয়া
                                ({{ $priorDueSemesterName }} — ৳{{ number_format($priorDueAmount, 2) }}) পরিশোধ না করা
                                পর্যন্ত রানিং সেমিস্টারের বেতন পরিশোধ করা যাবে না।
                            </span>
                        </div>
                        @if($priorDueSemesterId)
                            <a href="{{ route('student.fees.index', ['semester_id' => $priorDueSemesterId]) }}"
                                style="background:#be123c;color:#fff;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                                <i class="fa-solid fa-arrow-left"></i> পূর্বের বকেয়া পরিশোধ করুন
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Status Filter Buttons for Step 1 Table --}}
                <div
                    style="display:flex;justify-content:center;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap">
                    <span style="font-size:12.5px;font-weight:700;color:#64748b;margin-right:4px">স্ট্যাটাস
                        ফিল্টার:</span>
                    <button type="button" class="step1-filter-btn" id="step1Filter_all"
                        onclick="filterStep1Table('all', this)"
                        style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #2563eb;background:#2563eb;color:#fff;transition:all .15s">
                        <i class="fa-solid fa-list-check"></i> সকল আইটেম (All)
                    </button>
                    <button type="button" class="step1-filter-btn" id="step1Filter_due"
                        onclick="filterStep1Table('due', this)"
                        style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #cbd5e1;background:#fff;color:#be123c;transition:all .15s">
                        <i class="fa-solid fa-clock"></i> বকেয়া (Dues Only)
                    </button>
                    <button type="button" class="step1-filter-btn" id="step1Filter_paid"
                        onclick="filterStep1Table('paid', this)"
                        style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #cbd5e1;background:#fff;color:#15803d;transition:all .15s">
                        <i class="fa-solid fa-circle-check"></i> পরিশোধিত (Paid Only)
                    </button>
                </div>

                {{-- Step 1 Particulars Table --}}
                <div style="max-width:750px;margin:0 auto;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden">
                    <table style="width:100%;border-collapse:collapse;font-size:13px">
                        <thead>
                            <tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1">
                                <th
                                    style="padding:10px 14px;width:60px;text-align:center;font-weight:700;color:#334155">
                                    #SL</th>
                                <th style="padding:10px 14px;font-weight:700;color:#334155;text-align:left">Particular
                                    Name</th>
                                <th
                                    style="padding:10px 14px;width:160px;text-align:center;font-weight:700;color:#334155">
                                    Dues</th>
                                <th
                                    style="padding:10px 14px;width:100px;text-align:center;font-weight:700;color:#334155">
                                    Pay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($step1Particulars as $p)
                                <tr style="border-bottom:1px solid #f1f5f9"
                                    data-step1-status="{{ $p['is_paid'] ? 'paid' : 'due' }}">
                                    <td style="padding:9px 14px;text-align:center;color:#64748b;font-weight:600">
                                        {{ $p['sl'] }}</td>
                                    <td style="padding:9px 14px;font-weight:600;color:#1e293b">
                                        {{ $p['name'] }}
                                        @if(!empty($p['is_custom']))
                                            <span
                                                style="font-size:10px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:1px 5px;border-radius:8px;margin-left:4px;font-weight:700"
                                                title="{{ $p['custom_remarks'] ?? 'অ্যাডমিন কর্তৃক সমন্বয়কৃত' }}">সমন্বয়কৃত</span>
                                        @endif
                                    </td>
                                    <td style="padding:9px 14px;text-align:center" id="particularDueCell_{{ $p['sl'] }}">
                                        @if($p['is_paid'])
                                            <div style="display:inline-flex;align-items:center;gap:6px">
                                                <span id="particularDueVal_{{ $p['sl'] }}"
                                                    style="color:#16a34a;font-weight:700">Paid
                                                    ({{ number_format($p['amount'], 0) }})</span>
                                                @if($isAdminSession && $selectedSemesterInvoice)
                                                    <button type="button"
                                                        onclick="openAdminParticularEditModal('{{ $selectedSemesterInvoice->id }}', '{{ addslashes($p['name']) }}', 0, {{ $p['sl'] }})"
                                                        title="টাকার পরিমাণ এডিট করুন (Admin Edit)"
                                                        style="background:#f8fafc;border:1px solid #cbd5e1;color:#64748b;border-radius:4px;padding:2px 6px;font-size:10px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:2px">
                                                        <i class="fa-solid fa-pencil"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <div style="display:inline-flex;align-items:center;gap:6px">
                                                <span id="particularDueVal_{{ $p['sl'] }}"
                                                    style="font-weight:700;color:#0f172a">{{ number_format($p['due'], 0) }}</span>
                                                @if($isAdminSession && $selectedSemesterInvoice)
                                                    <button type="button"
                                                        onclick="openAdminParticularEditModal('{{ $selectedSemesterInvoice->id }}', '{{ addslashes($p['name']) }}', {{ $p['due'] }}, {{ $p['sl'] }})"
                                                        title="টাকার পরিমাণ এডিট বা সমন্বয় করুন (Admin Edit: Increase/Decrease Taka)"
                                                        style="background:#eff6ff;border:1px solid #93c5fd;color:#1d4ed8;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:3px;transition:all .15s"
                                                        onmouseover="this.style.background='#dbeafe'"
                                                        onmouseout="this.style.background='#eff6ff'">
                                                        <i class="fa-solid fa-pencil" style="font-size:10px"></i> এডিট
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding:9px 14px;text-align:center" id="particularPayCell_{{ $p['sl'] }}">
                                        @if(!$p['is_paid'])
                                            @if($hasPriorSemesterDue && ($selectedSemester?->sequence_no > 1))
                                                <span title="পূর্বের বকেয়া পরিশোধ আবশ্যক"
                                                    style="display:inline-flex;align-items:center;gap:4px">
                                                    <input type="checkbox" disabled style="cursor:not-allowed">
                                                    <i class="fa-solid fa-lock" style="color:#dc2626;font-size:11px"></i>
                                                </span>
                                            @else
                                                <input type="checkbox" class="step1-chk" data-name="{{ $p['name'] }}"
                                                    data-amount="{{ $p['due'] }}" onchange="updateStep1Total()"
                                                    style="width:16px;height:16px;cursor:pointer;accent-color:#16a34a">
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Next Button --}}
                <div style="text-align:center;margin-top:16px">
                    <button type="button" class="btn btn-success" id="step1NextBtn" onclick="goToStep2()" disabled
                        style="padding:10px 28px;font-size:14px;font-weight:700;border-radius:8px;display:inline-flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-arrow-right"></i> Next (Go Step 2)
                    </button>
                </div>
            </div>
        </div>

        {{-- ── 2. Semester & Category Payment Breakdown (Organized by Semester Tabs) ── --}}
        <div class="card" style="margin-bottom:24px; border-top:3px solid #3b82f6; font-family:'Kalpurush',sans-serif">
            <div class="card-header"
                style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px">
                <div>
                    <span class="card-title"
                        style="display:flex; align-items:center; gap:8px; font-size:16px; color:#1e293b">
                        <i class="fa-solid fa-layer-group" style="color:#2563eb"></i>
                        {{ $courseType === 'SUBJECT_BASED' ? 'কোর্স ফি ও বেতন বিবরণ (Course Fee Breakdown)' : 'সেমিস্টারভিত্তিক ফি ও বেতন বিবরণ (Semester Breakdown)' }}
                    </span>
                    <span style="font-size:12px; color:var(--text-muted); display:block; margin-top:2px">
                        {{ $courseType === 'SUBJECT_BASED' ? 'কোর্সের মোট প্রদেয় ও বেতনর হিসাব' : 'প্রতিটি সেমিস্টারের প্রদেয়, পরিশোধিত ও মাসিক বেতনর তথ্য' }}
                    </span>
                </div>

                {{-- Semester Tabs (For Semester-Based Courses) --}}
                @if($courseType !== 'SUBJECT_BASED' && $semesterBreakdown->isNotEmpty())
                    <div style="display:flex; gap:6px; flex-wrap:wrap">
                        @foreach($semesterBreakdown as $idx => $row)
                            @php
                                $cleanTabName = str_replace(' 🔵', '', $row['label']);
                                $isTabRunning = $row['isRunning'] ?? false;
                                $hasDue = ($row['due'] ?? 0) > 0;
                                $isCleared = ($row['hasInvoice'] ?? false) && !$hasDue;
                                $isActiveTab = $isTabRunning || ($loop->first && !$semesterBreakdown->contains('isRunning', true));
                            @endphp
                            <button type="button" class="sem-breakdown-tab-btn" id="semTabBtn_{{ $idx }}"
                                onclick="switchSemBreakdownTab({{ $idx }})"
                                style="padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; border:1.5px solid {{ $isActiveTab ? '#2563eb' : '#cbd5e1' }}; background:{{ $isActiveTab ? '#2563eb' : '#fff' }}; color:{{ $isActiveTab ? '#fff' : '#334155' }}; display:inline-flex; align-items:center; gap:6px; transition:all .15s">
                                <span>{{ $cleanTabName }}</span>
                                @if($isTabRunning)
                                    <span
                                        style="background:rgba(255,255,255,0.25); font-size:10px; padding:1px 6px; border-radius:10px">চলতি</span>
                                @elseif($hasDue)
                                    <span
                                        style="background:{{ $isActiveTab ? '#fecdd3' : '#fee2e2' }}; color:{{ $isActiveTab ? '#9f1239' : '#be123c' }}; font-size:10px; padding:1px 6px; border-radius:10px">বকেয়া</span>
                                @elseif($isCleared)
                                    <span
                                        style="background:{{ $isActiveTab ? '#a7f3d0' : '#dcfce7' }}; color:{{ $isActiveTab ? '#065f46' : '#15803d' }}; font-size:10px; padding:1px 6px; border-radius:10px">ক্লিয়ার</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div style="padding:16px 20px">
                @forelse($semesterBreakdown as $idx => $row)
                    @php
                        $gPayable = $row['payable'];
                        $gPaid = $row['paid'];
                        $gDue = $row['due'];
                        $isRunning = $row['isRunning'] ?? false;
                        $hasInvoice = $row['hasInvoice'] ?? false;
                        $invObj = $row['invoice'] ?? null;
                        $cleanName = str_replace(' 🔵', '', $row['label']);
                        $isActivePane = ($courseType === 'SUBJECT_BASED') ? true : ($isRunning || ($loop->first && !$semesterBreakdown->contains('isRunning', true)));
                    @endphp

                    <div class="sem-breakdown-pane" id="semPane_{{ $idx }}"
                        style="display:{{ $isActivePane ? 'block' : 'none' }}">
                        {{-- Semester Summary Bar --}}
                        <div
                            style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px 18px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px">
                            <div>
                                <span style="font-size:16px; font-weight:800; color:#1e293b">{{ $cleanName }}</span>
                                @if($isRunning)
                                    <span class="badge badge-primary no-dot" style="margin-left:6px; font-size:11px">Current
                                        Running</span>
                                @elseif(!$hasInvoice)
                                    <span style="font-size:11px; color:#94a3b8; margin-left:6px; font-style:italic">— Upcoming
                                        Semester</span>
                                @endif
                            </div>

                            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap">
                                <div>
                                    <span style="font-size:11px; color:#64748b">মোট প্রদেয়:</span>
                                    <div style="font-size:14px; font-weight:700; color:#0f172a">
                                        {{ $hasInvoice ? '৳' . number_format($gPayable, 2) : '—' }}</div>
                                </div>
                                <div>
                                    <span style="font-size:11px; color:#64748b">পরিশোধিত:</span>
                                    <div style="font-size:14px; font-weight:700; color:#10b981">
                                        {{ $hasInvoice ? '৳' . number_format($gPaid, 2) : '—' }}</div>
                                </div>
                                <div>
                                    <span style="font-size:11px; color:#64748b">অবশিষ্ট বকেয়া:</span>
                                    <div
                                        style="font-size:15px; font-weight:800; color:{{ $gDue > 0 ? '#e11d48' : '#10b981' }}">
                                        {{ !$hasInvoice ? '—' : ($gDue > 0 ? '৳' . number_format($gDue, 2) : '৳0.00') }}
                                    </div>
                                </div>
                                <div>
                                    <span style="font-size:11px; color:#64748b">স্ট্যাটাস:</span>
                                    <div>
                                        @if(!$hasInvoice)
                                            <span class="badge badge-secondary no-dot"
                                                style="padding:4px 10px; font-size:11px">⏳ Upcoming</span>
                                        @elseif($gDue <= 0)
                                            <span class="badge badge-success no-dot" style="padding:4px 10px">Cleared</span>
                                        @elseif($gPaid > 0)
                                            <span class="badge badge-warning no-dot" style="padding:4px 10px">Partial
                                                Paid</span>
                                        @else
                                            <span class="badge badge-danger no-dot" style="padding:4px 10px">Pending Due</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($hasInvoice && $gDue > 0 && $invObj)
                                        @php
                                            $firstDueMonth = collect($row['monthlyItems'] ?? [])->where('due', '>', 0)->first();
                                            $mRate = $firstDueMonth['due'] ?? ($row['monthlyRate'] ?? 0);
                                        @endphp
                                        <button
                                            onclick="openPayModal('{{ $invObj->id }}', '{{ e($cleanName) }}', '{{ $invObj->invoice_no }}', '{{ $gDue }}', '{{ min($mRate > 0 ? $mRate : $gDue, $gDue) }}', '{{ $firstDueMonth['label'] ?? '' }} বেতন', '{{ $mRate }}')"
                                            style="background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border:none; padding:7px 16px; border-radius:7px; font-weight:700; font-size:13px; cursor:pointer; box-shadow:0 2px 6px rgba(22,163,74,0.3); display:inline-flex; align-items:center; gap:5px">
                                            <i class="fa-solid fa-credit-card"></i> Pay Now
                                        </button>
                                    @elseif($hasInvoice && $gDue <= 0)
                                        <span
                                            style="color:#16a34a; font-size:13px; font-weight:700; display:inline-flex; align-items:center; gap:4px">
                                            <i class="fa-solid fa-circle-check"></i> সম্পূর্ণ পরিশোধিত
                                        </span>
                                    @else
                                        <span style="color:#cbd5e1; font-size:13px">—</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Monthly Installment Cards for this Semester --}}
                        @if(!empty($row['monthlyItems']))
                            <div
                                style="margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px">
                                <div
                                    style="font-size:13px; font-weight:700; color:#334155; display:flex; align-items:center; gap:6px">
                                    <i class="fa-solid fa-calendar-days" style="color:#2563eb"></i> মাসিক ফি বেতন তালিকা
                                    (Monthly Installments):
                                </div>
                                @if($hasInvoice && $gDue > 0 && $invObj)
                                    @php
                                        $firstDueMonth = collect($row['monthlyItems'])->where('due', '>', 0)->first();
                                        $mRate = $firstDueMonth['due'] ?? ($row['monthlyRate'] ?? 0);
                                    @endphp
                                    @if($firstDueMonth)
                                        <button type="button"
                                            onclick="openPayModal('{{ $invObj->id }}', '{{ e($cleanName) }} — {{ $firstDueMonth['label'] }}', '{{ $invObj->invoice_no }}', '{{ $gDue }}', '{{ min($mRate, $gDue) }}', '{{ $firstDueMonth['label'] }} বেতন', '{{ $mRate }}')"
                                            style="background:#eff6ff; border:1.5px solid #bfdbfe; color:#1d4ed8; padding:5px 14px; border-radius:7px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; box-shadow:0 1px 3px rgba(37,99,235,0.1)">
                                            <i class="fa-solid fa-bolt" style="color:#2563eb"></i> চলতি {{ $firstDueMonth['label'] }}
                                            পরিশোধ করুন (৳{{ number_format(min($mRate, $gDue), 0) }})
                                        </button>
                                    @endif
                                @endif
                            </div>

                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px">
                                @foreach($row['monthlyItems'] as $mi)
                                    @php
                                        $isPaid = ($mi['status'] === 'PAID');
                                        $isPartial = ($mi['status'] === 'PARTIAL');
                                        $mStatusBadge = match ($mi['status']) {
                                            'PAID' => 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;',
                                            'PARTIAL' => 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
                                            default => 'background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;',
                                        };
                                        $mStatusText = match ($mi['status']) {
                                            'PAID' => 'পরিশোধিত',
                                            'PARTIAL' => 'আংশিক',
                                            default => 'অপরিশোধিত',
                                        };
                                    @endphp
                                    <div style="background:#fff; border:1.5px solid {{ $isPaid ? '#bbf7d0' : '#e2e8f0' }}; border-radius:10px; padding:12px 14px; box-shadow:0 1px 3px rgba(0,0,0,0.03); display:flex; flex-direction:column; justify-content:space-between; transition:all .15s"
                                        class="month-card-box">
                                        <div>
                                            <div
                                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                                                <strong style="font-size:13px; color:#1e293b">{{ $mi['label'] }}</strong>
                                                <span
                                                    style="font-size:10px; font-weight:700; padding:2px 7px; border-radius:10px; {{ $mStatusBadge }}">
                                                    @if($isPaid) <i class="fa-solid fa-check" style="font-size:9px"></i> @endif
                                                    {{ $mStatusText }}
                                                </span>
                                            </div>
                                            <div
                                                style="display:flex; justify-content:space-between; font-size:11.5px; color:#64748b; margin-bottom:4px">
                                                <span>নির্ধারিত: ৳{{ number_format($mi['payable'], 0) }}</span>
                                                @if($mi['due'] > 0)
                                                    <span style="color:#e11d48; font-weight:700">বকেয়া:
                                                        ৳{{ number_format($mi['due'], 0) }}</span>
                                                @else
                                                    <span style="color:#10b981; font-weight:700">ক্লিয়ার</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div
                                            style="margin-top:10px; padding-top:8px; border-top:1px dashed #e2e8f0; display:flex; justify-content:space-between; align-items:center">
                                            @if($isPaid)
                                                <span
                                                    style="color:#16a34a; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px">
                                                    <i class="fa-solid fa-circle-check"></i> পরিশোধ সম্পন্ন
                                                </span>
                                            @elseif($hasInvoice && $gDue > 0 && $invObj)
                                                <span style="font-size:11px; color:#64748b">বেতন নং {{ $mi['month_no'] }}</span>
                                                <button type="button"
                                                    onclick="openPayModal('{{ $invObj->id }}', '{{ e($cleanName) }} — {{ $mi['label'] }}', '{{ $invObj->invoice_no }}', '{{ $gDue }}', '{{ min($mi['due'], $gDue) }}', '{{ $mi['label'] }} বেতন', '{{ $row['monthlyRate'] ?? $mi['payable'] }}')"
                                                    style="background:linear-gradient(135deg,#059669,#10b981); color:#fff; border:none; padding:4px 11px; border-radius:6px; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; box-shadow:0 2px 4px rgba(16,185,129,0.25)">
                                                    <i class="fa-solid fa-credit-card" style="font-size:10px"></i> পে করুন
                                                </button>
                                            @else
                                                <span style="color:#94a3b8; font-size:11px">—</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align:center; padding:20px; color:#94a3b8; font-size:13px">
                                এই সেমিস্টারের জন্য কোনো বেতন সক্রিয় নেই।
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="text-align:center; padding:30px; color:var(--text-muted)">
                        কোনো সেমিস্টার বা কোর্স ফি তথ্য পাওয়া যায়নি।
                    </div>
                @endforelse
            </div>
        </div>
    </div>{{-- Close #portalSection_monthly --}}

    {{-- ═══════════════ TAB 2: DUES & VOUCHERS SECTION ═══════════════ --}}
    <div id="portalSection_dues" style="display:none">
        {{-- ── MY INVOICES & DETAILED FEE STATEMENTS ── --}}
        <div class="card" style="margin-bottom:24px;border-top:3px solid #059669">
            <div class="card-header"
                style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
                <div>
                    <span class="card-title" style="font-size:16px;color:#065f46">
                        <i class="fa-solid fa-file-invoice-dollar" style="color:#059669;margin-right:6px"></i> ইনভয়েস
                        বিবরণ ও বকেয়া তালিকা (Invoices & Due Statements)
                    </span>
                </div>
                <div class="btn-group" id="invFilterGroup" style="display:flex; gap:6px; flex-wrap:wrap">
                    <button class="btn btn-sm btn-primary filter-btn active" onclick="filterInvoices('all', this)">সকল
                        ইনভয়েস (All)</button>
                    <button class="btn btn-sm btn-outline filter-btn" onclick="filterInvoices('unpaid', this)">বকেয়া
                        ইনভয়েস (Due Only)</button>
                    <button class="btn btn-sm btn-outline filter-btn" onclick="filterInvoices('paid', this)">পরিশোধিত
                        (Paid Only)</button>
                    <button class="btn btn-sm btn-outline filter-btn" onclick="filterInvoices('running', this)">চলতি
                        সেমিস্টার</button>
                </div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Particulars / Category</th>
                            <th>Payable</th>
                            <th>Paid</th>
                            <th>Due Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="invoicesTableBody">
                        @forelse($invoices as $inv)
                            <tr
                                class="inv-row {{ $inv->is_current_running_semester ? 'row-running' : '' }} {{ $inv->due_amount > 0 ? 'row-unpaid' : 'row-paid' }}">
                                <td style="font-weight:700;color:#3b82f6;font-size:12px">{{ $inv->invoice_no }}</td>
                                <td>
                                    <strong>{{ $inv->title }}</strong><br>
                                    @php
                                        $catLabel = ($inv->category === 'SEMESTER' && $courseType === 'SUBJECT_BASED')
                                            ? 'COURSE FEE'
                                            : $inv->category;
                                    @endphp
                                    <span class="badge badge-secondary no-dot" style="font-size:10px">{{ $catLabel }}</span>
                                    @if($inv->is_current_running_semester)
                                        @if($courseType === 'SUBJECT_BASED')
                                            <span class="badge badge-primary no-dot"
                                                style="font-size:10px; background:#7c3aed">Current Course</span>
                                        @else
                                            <span class="badge badge-primary no-dot"
                                                style="font-size:10px; background:#3b82f6">Running Semester</span>
                                        @endif
                                    @endif
                                </td>
                                <td>৳{{ number_format($inv->payable_amount, 2) }}</td>
                                <td><span
                                        style="color:#10b981;font-weight:600">৳{{ number_format($inv->paid_amount, 2) }}</span>
                                </td>
                                <td>
                                    @if($inv->due_amount > 0)
                                        <strong
                                            style="color:#e11d48;font-size:13px">৳{{ number_format($inv->due_amount, 2) }}</strong>
                                    @else
                                        <span style="color:#10b981;font-weight:600">৳0.00</span>
                                    @endif
                                </td>
                                <td class="td-muted" style="font-size:12px">
                                    {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                                <td>
                                    @php
                                        $badge = match ($inv->status) { 'PAID' => 'badge-success', 'PARTIAL' => 'badge-warning', default => 'badge-danger'};
                                    @endphp
                                    <span class="badge {{ $badge }} no-dot">{{ $inv->status }}</span>
                                </td>
                                <td style="text-align:center">
                                    @if($inv->due_amount > 0)
                                        <button
                                            onclick="openPayModal('{{ $inv->id }}', '{{ e($inv->title) }}', '{{ $inv->invoice_no }}', '{{ $inv->due_amount }}')"
                                            style="background:linear-gradient(135deg,#2563eb,#3b82f6); color:#fff; border:none; padding:5px 12px; border-radius:7px; font-weight:700; font-size:12px; cursor:pointer; box-shadow:0 2px 6px rgba(37,99,235,0.3)">
                                            Pay Now
                                        </button>
                                    @else
                                        <span style="color:#10b981; font-size:12px; font-weight:700">Paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">No fee
                                    invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── PAYMENT RECEIPT HISTORY & VOUCHERS ── --}}
        <div class="card" style="border-top:3px solid #6366f1">
            <div class="card-header"
                style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px">
                <span class="card-title" style="font-size:16px;color:#3730a3">
                    <i class="fa-solid fa-receipt" style="color:#6366f1;margin-right:6px"></i> পেমেন্ট ট্রানজেকশন
                    হিস্ট্রি ও অফিসিয়াল ভাউচার (Payment History & Receipts)
                </span>
                <span style="font-size:12px;color:var(--text-muted)">সকল অনুমোদিত ও প্রক্রিয়াধীন লেনদেন</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>রসিদ নং (Receipt)</th>
                            <th>ফি বিবরণ (Purpose)</th>
                            <th>পরিশোধিত টাকা</th>
                            <th>পেমেন্ট মেথড</th>
                            <th>বিকাশ / প্রেরক নম্বর</th>
                            <th>ট্রানজেকশন আইডি (TrxID)</th>
                            <th>স্ট্যাটাস</th>
                            <th>তারিখ ও সময়</th>
                            <th style="text-align:center">ভাউচার / রসিদ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $pay)
                            <tr>
                                <td style="font-weight:700;color:#6366f1;font-size:12px;font-family:monospace">
                                    {{ $pay->payment_no }}</td>
                                <td class="td-primary">
                                    <strong>{{ $pay->invoice->title ?? '—' }}</strong>
                                    <div style="font-size:11px;color:#64748b">{{ $pay->invoice?->invoice_no ?? '' }}</div>
                                </td>
                                <td><strong
                                        style="color:#10b981;font-size:14px">৳{{ number_format($pay->amount, 2) }}</strong>
                                </td>
                                <td><span class="badge badge-secondary no-dot"
                                        style="font-weight:700">{{ $pay->payment_method }}</span></td>
                                <td>
                                    @if($pay->sender_number)
                                        <span style="font-family:monospace;font-size:12px;color:#047857;font-weight:700">
                                            <i class="fa-solid fa-mobile-screen"></i> {{ $pay->sender_number }}
                                        </span>
                                    @else
                                        <span style="color:#94a3b8">—</span>
                                    @endif
                                </td>
                                <td style="font-family:monospace;font-size:12px">
                                    @if($pay->transaction_id)
                                        <strong style="color:#2563eb">{{ $pay->transaction_id }}</strong>
                                    @else
                                        <span style="color:#94a3b8">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(($pay->status ?? 'APPROVED') === 'APPROVED')
                                        <span class="badge badge-success no-dot" style="font-size:11px">অনুমোদিত
                                            (Approved)</span>
                                    @elseif(($pay->status ?? 'APPROVED') === 'PENDING')
                                        <span class="badge badge-warning no-dot" style="font-size:11px">⏳ যাচাই অপেক্ষমান</span>
                                    @else
                                        <span class="badge badge-danger no-dot" style="font-size:11px">বাতিল (Rejected)</span>
                                    @endif
                                </td>
                                <td class="td-muted" style="font-size:12px;white-space:nowrap">
                                    {{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y, h:i A') : '—' }}
                                </td>
                                <td style="text-align:center;white-space:nowrap">
                                    @if(($pay->status ?? 'APPROVED') === 'APPROVED')
                                        <a href="{{ route('student.fees.receipt', $pay) }}" target="_blank"
                                            class="btn btn-outline btn-sm"
                                            style="font-size:11px;display:inline-flex;align-items:center;gap:4px">
                                            <i class="fa-solid fa-print"></i> ভাউচার ডাউনলোড
                                        </a>
                                    @else
                                        <span style="font-size:11px; color:#b45309; font-style:italic">⏳ অনুমোদন বাকি</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">এখনও কোনো
                                    পেমেন্ট রেকর্ড পাওয়া যায়নি।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>{{-- Close #portalSection_dues --}}

    {{-- ── INTERACTIVE PAYMENT MODAL ── --}}
    <style>
        .modal-gateway-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            background: #fff;
            transition: all .2s;
            display: block;
            text-align: left;
        }

        .modal-gateway-card:hover {
            border-color: #a7f3d0;
            background: #f0fdf4;
        }

        .modal-gateway-card.selected {
            border-color: #047857;
            background: #f0fdf4;
            box-shadow: 0 0 0 1.5px #047857;
        }
    </style>

    <div id="payInvoiceModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center; padding:20px; box-sizing:border-box">
        <div
            style="background:#fff; border-radius:18px; max-width:500px; width:100%; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); animation:modalSlideUp .3s ease">
            <div
                style="background:linear-gradient(135deg,#047857,#065f46); color:#fff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-credit-card"></i> অনলাইন ফি পরিশোধ
                    </div>
                    <div style="font-size:11px; opacity:.85; margin-top:2px" id="modalInvNo">INV-00000</div>
                </div>
                <button type="button" onclick="closePayModal()"
                    style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer; line-height:1; opacity:0.85">&times;</button>
            </div>

            <form id="payForm" method="POST" action="" style="padding:22px">
                @csrf
                <div
                    style="background:#f0fdf4; border:1.5px solid #bbf7d0; padding:14px 18px; border-radius:12px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center">
                    <div>
                        <div style="font-size:12px; color:#166534; font-weight:700" id="modalInvTitle">Invoice Title
                        </div>
                        <div style="font-size:11px; color:#64748b; margin-top:2px">পরিশোধযোগ্য মোট বকেয়া</div>
                    </div>
                    <div style="font-size:22px; font-weight:800; color:#15803d; text-align:right">
                        ৳<span id="modalDueAmount">0.00</span>
                    </div>
                </div>

                <input type="hidden" name="remarks" id="modalRemarksInput" value="">

                {{-- Month / Installment Quick Presets --}}
                <div id="modalInstallmentOptions"
                    style="margin-bottom:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; font-family:'Kalpurush',sans-serif">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                        <label style="font-size:12px; font-weight:700; color:#334155; margin:0">
                            <i class="fa-solid fa-calendar-check" style="color:#2563eb"></i> বেতনর বিকল্প বেছে নিন:
                        </label>
                        <span id="modalSelectedMonthLabel"
                            style="font-size:11.5px; color:#2563eb; font-weight:700"></span>
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px" id="modalPresetChips">
                        <!-- Rendered dynamically by JS -->
                    </div>
                </div>

                {{-- Amount to Pay --}}
                <div style="margin-bottom:16px">
                    <label
                        style="display:flex; justify-content:space-between; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:6px">
                        <span>পরিশোধের পরিমাণ (টাকা) <span style="color:#dc2626">*</span></span>
                        <span style="font-size:11px; color:#64748b; font-weight:500">(আংশিক বা সম্পূর্ণ প্রদেয়)</span>
                    </label>
                    <input type="number" step="0.01" id="payAmountInput" name="amount" class="form-control" required
                        style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid #cbd5e1; font-size:16px; font-weight:700; color:#1e293b; box-sizing:border-box"
                        oninput="updateModalButtonAmount()">
                </div>

                {{-- Payment Gateway Selection --}}
                <div style="margin-bottom:16px">
                    <label style="display:block; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:8px">
                        পেমেন্ট মাধ্যম নির্বাচন করুন <span style="color:#dc2626">*</span>
                    </label>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px" id="modalGatewayGrid">
                        {{-- SSLCommerz Card --}}
                        <label class="modal-gateway-card selected" id="card_sslcommerz"
                            onclick="selectModalGateway('sslcommerz')">
                            <input type="radio" name="payment_method" value="sslcommerz" checked style="display:none">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                                <img src="{{ asset('images/gateways/sslcommerz.png') }}" alt="SSLCommerz"
                                    style="height:22px; max-width:115px; object-fit:contain">
                                <span
                                    style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px; background:#e0f2fe; color:#0369a1">সব
                                    মাধ্যম</span>
                            </div>
                            <div style="font-size:11px; color:#64748b; line-height:1.3">
                                কার্ড, নগদ, রকেট ও ব্যাংক
                            </div>
                        </label>

                        {{-- bKash Card --}}
                        <label class="modal-gateway-card" id="card_bkash" onclick="selectModalGateway('bkash')">
                            <input type="radio" name="payment_method" value="bkash" style="display:none">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                                <img src="{{ asset('images/gateways/bkash.png') }}" alt="bKash"
                                    style="height:24px; max-width:85px; object-fit:contain">
                                <span
                                    style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px; background:#fce7f3; color:#be185d">বিকাশ</span>
                            </div>
                            <div style="font-size:11px; color:#64748b; line-height:1.3">
                                সরাসরি বিকাশ ওয়ালেট ও পিন
                            </div>
                        </label>
                    </div>

                    {{-- Manual / Offline Payment Toggle --}}
                    <div style="margin-top:10px; text-align:right">
                        <button type="button" onclick="toggleManualPayment()" id="toggleManualBtn"
                            style="background:none; border:none; color:#2563eb; font-size:11.5px; font-weight:600; cursor:pointer; text-decoration:underline">
                            অথবা ম্যানুয়াল ব্যাংক / ক্যাশ ভাউচার জমা দিন
                        </button>
                    </div>

                    {{-- Manual Fields (Hidden by default) --}}
                    <div id="manualPaymentFields"
                        style="display:none; background:#f8fafc; border:1.5px dashed #cbd5e1; border-radius:10px; padding:14px; margin-top:10px">
                        <div style="margin-bottom:10px">
                            <label
                                style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px">ম্যানুয়াল
                                মাধ্যম নির্বাচন করুন</label>
                            <select id="manualMethodSelect" class="form-control"
                                style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #cbd5e1; font-size:12.5px"
                                onchange="onManualMethodChange(this.value)">
                                <option value="BKASH" selected>ম্যানুয়াল বিকাশ ট্রানজেকশন (bKash Manual Send Money /
                                    Payment)</option>
                                <option value="NAGAD">নগদ ম্যানুয়াল ট্রানজেকশন (Nagad TrxID)</option>
                                <option value="ROCKET">রকেট ম্যানুয়াল ট্রানজেকশন (Rocket TrxID)</option>
                                <option value="BANK_TRANSFER">ব্যাংক ডিপোজিট / স্লিপ (Bank Transfer)</option>
                                <option value="CASH">সরাসরি অফিস ক্যাশ (Cash at Office)</option>
                            </select>
                        </div>
                        <div style="margin-bottom:10px">
                            <label
                                style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px">বিকাশ
                                / প্রেরক মোবাইল নম্বর (Sender Mobile No)</label>
                            <input type="text" name="sender_number" id="manualSenderInput"
                                placeholder="যেমন: 01712345678 বা আপনার বিকাশ নম্বর" class="form-control"
                                style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #cbd5e1; font-size:12.5px; box-sizing:border-box">
                        </div>
                        <div>
                            <label
                                style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px">Transaction
                                ID / রেফারেন্স ট্রানজেকশন আইডি (TrxID)</label>
                            <input type="text" name="transaction_id" id="manualTrxInput"
                                placeholder="যেমন: 8N7A6B5C4D (বিকাশ TrxID)" class="form-control"
                                style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #cbd5e1; font-size:12.5px; box-sizing:border-box">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px">
                    <button type="button" onclick="closePayModal()"
                        style="padding:11px 18px; border-radius:9px; border:1.5px solid #cbd5e1; background:#fff; color:#475569; font-weight:600; font-size:13px; cursor:pointer">
                        বাতিল
                    </button>
                    <button type="submit" id="modalPaySubmitBtn"
                        style="padding:11px 24px; border-radius:9px; border:none; background:linear-gradient(135deg,#047857,#065f46); color:#fff; font-weight:700; font-size:14px; cursor:pointer; box-shadow:0 4px 12px rgba(4,120,87,0.3); display:inline-flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-lock"></i>
                        <span id="modalPayBtnText">পেমেন্ট সম্পন্ন করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Admin Fee Particular Edit Modal ── --}}
    <div id="adminParticularEditModal"
        style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:999999;align-items:center;justify-content:center;font-family:'Kalpurush',sans-serif">
        <div
            style="background:#fff;border-radius:12px;width:95%;max-width:440px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1">
            <div
                style="background:#1e40af;color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center">
                <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-pencil"></i> ফি এর পরিমাণ পরিবর্তন / সমন্বয় (Admin Edit)
                </div>
                <button type="button" onclick="closeAdminParticularEditModal()"
                    style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1">&times;</button>
            </div>
            <form id="adminParticularEditForm" onsubmit="submitAdminParticularEdit(event)" style="padding:20px">
                <input type="hidden" id="ape_invoice_id" name="invoice_id">
                <input type="hidden" id="ape_particular_name" name="particular_name">
                <input type="hidden" id="ape_current_due" name="current_due">
                <input type="hidden" id="ape_row_sl" name="row_sl">

                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:4px">আইটেমের
                        নাম (Particular Name):</label>
                    <div id="ape_display_name"
                        style="font-size:13.5px;font-weight:700;color:#1e293b;background:#f8fafc;padding:8px 12px;border-radius:6px;border:1px solid #e2e8f0">
                    </div>
                </div>

                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:4px">বর্তমান
                        বকেয়া (Current Dues):</label>
                    <div id="ape_display_current_due" style="font-size:15px;font-weight:800;color:#be123c"></div>
                </div>

                <div style="margin-bottom:16px">
                    <label for="ape_new_amount"
                        style="display:block;font-size:13px;font-weight:700;color:#0f172a;margin-bottom:6px">
                        নতুন টাকার পরিমাণ (New Amount - ৳): <span style="color:#dc2626">*</span>
                    </label>
                    <div style="position:relative">
                        <span
                            style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:#64748b;font-size:15px">৳</span>
                        <input type="number" step="1" min="0" id="ape_new_amount" name="new_amount" required
                            style="width:100%;padding:10px 12px 10px 32px;border:1.5px solid #2563eb;border-radius:8px;font-size:16px;font-weight:800;color:#0f172a;outline:none;box-sizing:border-box">
                    </div>
                    <div style="font-size:11.5px;color:#64748b;margin-top:4px">
                        (টাকা কমাতে চাইলে কম লিখুন, বাড়াতে চাইলে বেশি লিখুন, বা সম্পূর্ণ মওকুফ করতে ০ লিখুন)
                    </div>
                </div>

                <div style="margin-bottom:18px">
                    <label for="ape_remarks"
                        style="display:block;font-size:12.5px;font-weight:700;color:#334155;margin-bottom:4px">
                        সমন্বয়ের কারণ / নোট (Reason / Remarks):
                    </label>
                    <input type="text" id="ape_remarks" name="remarks"
                        placeholder="যেমন: কর্তৃপক্ষের অনুমোদনক্রমে বিশেষ ফি ছাড়..."
                        style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;box-sizing:border-box;outline:none">
                </div>

                <div
                    style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #f1f5f9;padding-top:14px">
                    <button type="button" onclick="closeAdminParticularEditModal()" class="btn btn-outline"
                        style="border-color:#cbd5e1;color:#475569">
                        বাতিল (Cancel)
                    </button>
                    <button type="submit" id="ape_submit_btn" class="btn btn-primary"
                        style="background:#2563eb;border-color:#2563eb;font-weight:700">
                        <i class="fa-solid fa-check"></i> সংরক্ষণ করুন (Save Amount)
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentSelectedGateway = 'sslcommerz';
            let isManualModeActive = false;

            function selectModalGateway(method) {
                isManualModeActive = false;
                currentSelectedGateway = method;

                const manualFields = document.getElementById('manualPaymentFields');
                if (manualFields) manualFields.style.display = 'none';

                // Re-enable radio buttons and remove hidden manual method if any
                document.querySelectorAll('#modalGatewayGrid input[type="radio"]').forEach(r => {
                    r.disabled = false;
                    r.checked = (r.value === method);
                });

                const hiddenManual = document.getElementById('hiddenManualMethod');
                if (hiddenManual) hiddenManual.disabled = true;

                document.querySelectorAll('.modal-gateway-card').forEach(c => c.classList.remove('selected'));
                const card = document.getElementById('card_' + method);
                if (card) card.classList.add('selected');

                updateModalButtonAmount();
            }

            function toggleManualPayment() {
                isManualModeActive = !isManualModeActive;
                const manualFields = document.getElementById('manualPaymentFields');

                if (isManualModeActive) {
                    manualFields.style.display = 'block';
                    document.querySelectorAll('.modal-gateway-card').forEach(c => c.classList.remove('selected'));
                    setManualMethodValue(document.getElementById('manualMethodSelect').value);
                } else {
                    manualFields.style.display = 'none';
                    selectModalGateway(currentSelectedGateway);
                }
                updateModalButtonAmount();
            }

            function onManualMethodChange(val) {
                if (isManualModeActive) {
                    setManualMethodValue(val);
                }
            }

            function setManualMethodValue(val) {
                let hidden = document.getElementById('hiddenManualMethod');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.id = 'hiddenManualMethod';
                    hidden.name = 'payment_method';
                    document.getElementById('payForm').appendChild(hidden);
                }
                hidden.value = val;
                hidden.disabled = false;
                document.querySelectorAll('#modalGatewayGrid input[type="radio"]').forEach(r => r.disabled = true);
            }

            function updateModalButtonAmount() {
                const amt = parseFloat(document.getElementById('payAmountInput').value) || 0;
                const formatted = '৳' + amt.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                const btnText = document.getElementById('modalPayBtnText');

                if (isManualModeActive) {
                    btnText.innerText = formatted + ' জমা দিন (ভেরিফিকেশন পেন্ডিং)';
                } else if (currentSelectedGateway === 'bkash') {
                    btnText.innerText = 'bKash দিয়ে ' + formatted + ' পরিশোধ করুন';
                } else {
                    btnText.innerText = 'SSLCommerz দিয়ে ' + formatted + ' পরিশোধ করুন';
                }
            }

            function openPayModal(invId, title, invNo, dueAmt, suggestedAmt, remarks, monthlyRate) {
                dueAmt = parseFloat(dueAmt) || 0;
                monthlyRate = parseFloat(monthlyRate) || 0;

                document.getElementById('modalInvNo').innerText = invNo;
                document.getElementById('modalInvTitle').innerText = title;
                document.getElementById('modalDueAmount').innerText = dueAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 });

                let targetAmt;
                if (suggestedAmt !== undefined && suggestedAmt !== null && suggestedAmt !== '') {
                    targetAmt = parseFloat(suggestedAmt);
                } else if (monthlyRate > 0 && monthlyRate <= dueAmt) {
                    targetAmt = monthlyRate;
                } else {
                    targetAmt = dueAmt;
                }
                targetAmt = Math.min(targetAmt, dueAmt);

                document.getElementById('payAmountInput').value = targetAmt;
                document.getElementById('payAmountInput').max = dueAmt;

                const remarksEl = document.getElementById('modalRemarksInput');
                if (remarksEl) {
                    remarksEl.value = remarks || '';
                }

                const labelEl = document.getElementById('modalSelectedMonthLabel');
                if (labelEl) {
                    labelEl.innerText = remarks ? '📌 ' + remarks : '';
                }

                renderPresetChips(dueAmt, targetAmt, monthlyRate, remarks);

                let actionUrl = "{{ route('student.fees.pay', ':id') }}".replace(':id', invId);
                document.getElementById('payForm').action = actionUrl;

                selectModalGateway('sslcommerz');

                document.getElementById('payInvoiceModal').style.display = 'flex';
            }

            function renderPresetChips(dueAmt, currentAmt, monthlyRate, remarks) {
                const container = document.getElementById('modalPresetChips');
                if (!container) return;
                container.innerHTML = '';

                const chips = [];

                if (monthlyRate > 0 && monthlyRate < dueAmt) {
                    chips.push({
                        label: '১ মাসের বেতন',
                        amount: Math.min(monthlyRate, dueAmt),
                        note: '১ মাসের বেতন'
                    });

                    if (dueAmt >= monthlyRate * 1.5) {
                        chips.push({
                            label: '২ মাসের বেতন',
                            amount: Math.min(Math.round(monthlyRate * 2 * 100) / 100, dueAmt),
                            note: '২ মাসের বেতন'
                        });
                    }

                    if (dueAmt >= monthlyRate * 2.5) {
                        chips.push({
                            label: '৩ মাসের বেতন',
                            amount: Math.min(Math.round(monthlyRate * 3 * 100) / 100, dueAmt),
                            note: '৩ মাসের বেতন'
                        });
                    }
                }

                chips.push({
                    label: 'পূর্ণ বকেয়া পরিশোধ',
                    amount: dueAmt,
                    note: 'পূর্ণ বকেয়া'
                });

                chips.forEach((c) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    const isMatch = Math.abs(currentAmt - c.amount) < 0.5;
                    btn.className = 'preset-chip' + (isMatch ? ' active' : '');
                    btn.style.padding = '5px 12px';
                    btn.style.borderRadius = '20px';
                    btn.style.fontSize = '12px';
                    btn.style.fontWeight = '600';
                    btn.style.cursor = 'pointer';
                    btn.style.border = isMatch ? '1.5px solid #2563eb' : '1px solid #cbd5e1';
                    btn.style.background = isMatch ? '#eff6ff' : '#fff';
                    btn.style.color = isMatch ? '#1d4ed8' : '#334155';
                    btn.style.display = 'inline-flex';
                    btn.style.alignItems = 'center';
                    btn.style.gap = '4px';

                    btn.innerHTML = c.label + ' (<strong>৳' + Math.round(c.amount).toLocaleString('en-BD') + '</strong>)';

                    btn.onclick = function () {
                        document.getElementById('payAmountInput').value = c.amount;
                        if (document.getElementById('modalRemarksInput')) {
                            document.getElementById('modalRemarksInput').value = c.note;
                        }
                        if (document.getElementById('modalSelectedMonthLabel')) {
                            document.getElementById('modalSelectedMonthLabel').innerText = '📌 ' + c.note;
                        }
                        document.querySelectorAll('.preset-chip').forEach(b => {
                            b.style.border = '1px solid #cbd5e1';
                            b.style.background = '#fff';
                            b.style.color = '#334155';
                            b.classList.remove('active');
                        });
                        btn.style.border = '1.5px solid #2563eb';
                        btn.style.background = '#eff6ff';
                        btn.style.color = '#1d4ed8';
                        btn.classList.add('active');

                        updateModalButtonAmount();
                    };

                    container.appendChild(btn);
                });
            }

            function onMonthCheckChange(semIdx) {
                const chks = document.querySelectorAll('.month-chk-sem-' + semIdx + ':checked');
                const bar = document.getElementById('bulkPayBar_' + semIdx);
                const text = document.getElementById('bulkPayText_' + semIdx);
                const btn = document.getElementById('bulkPayBtn_' + semIdx);

                if (!bar || !btn) return;

                if (chks.length === 0) {
                    bar.style.display = 'none';
                    return;
                }

                let totalDue = 0;
                let labels = [];
                let invId = '', invNo = '', semName = '', gDue = 0, mRate = 0;

                chks.forEach(chk => {
                    totalDue += parseFloat(chk.dataset.due) || 0;
                    labels.push(chk.dataset.label);
                    invId = chk.dataset.invid;
                    invNo = chk.dataset.invno;
                    semName = chk.dataset.sem;
                    gDue = parseFloat(chk.dataset.gdue) || totalDue;
                    mRate = parseFloat(chk.dataset.mrate) || 0;
                });

                const formatted = '৳' + Math.round(totalDue).toLocaleString('en-BD');
                text.innerText = 'নির্বাচিত ' + chks.length + 'টি মাস: ' + formatted;
                bar.style.display = 'inline-flex';

                btn.onclick = function () {
                    openPayModal(invId, semName + ' — ' + labels.join(', '), invNo, gDue, Math.min(totalDue, gDue), labels.join(', ') + ' বেতন', mRate);
                };
            }

            function updateStep1Total() {
                const chks = document.querySelectorAll('.step1-chk:checked');
                let total = 0;
                chks.forEach(c => total += parseFloat(c.dataset.amount) || 0);

                const summaryEl = document.getElementById('step1SelectedSummary');
                const totalDisp = document.getElementById('step1TotalDisplay');
                const countDisp = document.getElementById('step1CountDisplay');

                if (chks.length > 0) {
                    summaryEl.style.display = 'block';
                    totalDisp.innerText = Math.round(total).toLocaleString('en-BD');
                    countDisp.innerText = chks.length;
                } else {
                    summaryEl.style.display = 'none';
                }
            }

            function goToStep2Payment() {
                const chks = document.querySelectorAll('.step1-chk:checked');
                if (chks.length === 0) {
                    alert('অনুগ্রহ করে ফি পরিশোধ করতে কমপক্ষে একটি ফি আইটেম নির্বাচন করুন। (Please select at least one fee item to proceed to Step 2)');
                    return;
                }

                let total = 0;
                let names = [];
                chks.forEach(c => {
                    total += parseFloat(c.dataset.amount) || 0;
                    names.push(c.dataset.name);
                });

                const invoiceId = "{{ $selectedSemesterInvoice?->id ?? ($invoices->first()?->id ?? '') }}";
                const invoiceNo = "{{ $selectedSemesterInvoice?->invoice_no ?? ($invoices->first()?->invoice_no ?? 'INV-001') }}";
                const title = "{{ $selectedSemester?->name ?? 'সেমিস্টার ফি' }} — " + names.join(', ');
                const totalDue = parseFloat("{{ $totalDue }}") || total;

                openPayModal(invoiceId, title, invoiceNo, totalDue, total, names.join(', '), {{ $monthlyTuition ?? 500 }});
            }

            function closePayModal() {
                document.getElementById('payInvoiceModal').style.display = 'none';
            }

            function switchPortalTab(tab) {
                const secMonthly = document.getElementById('portalSection_monthly');
                const secDues = document.getElementById('portalSection_dues');
                const btnMonthly = document.getElementById('tabBtn_monthly');
                const btnDues = document.getElementById('tabBtn_dues');

                if (!secMonthly || !secDues || !btnMonthly || !btnDues) return;

                if (tab === 'dues') {
                    secMonthly.style.display = 'none';
                    secDues.style.display = 'block';

                    btnDues.style.background = '#059669';
                    btnDues.style.color = '#fff';
                    btnDues.style.border = 'none';
                    btnDues.style.boxShadow = '0 4px 10px rgba(5,150,105,0.25)';

                    btnMonthly.style.background = '#fff';
                    btnMonthly.style.color = '#475569';
                    btnMonthly.style.border = '1.5px solid #cbd5e1';
                    btnMonthly.style.boxShadow = 'none';
                } else {
                    secMonthly.style.display = 'block';
                    secDues.style.display = 'none';

                    btnMonthly.style.background = '#2563eb';
                    btnMonthly.style.color = '#fff';
                    btnMonthly.style.border = 'none';
                    btnMonthly.style.boxShadow = '0 4px 10px rgba(37,99,235,0.25)';

                    btnDues.style.background = '#fff';
                    btnDues.style.color = '#475569';
                    btnDues.style.border = '1.5px solid #cbd5e1';
                    btnDues.style.boxShadow = 'none';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');
                if (tab === 'dues') {
                    switchPortalTab('dues');
                } else {
                    switchPortalTab('monthly');
                }
            });

            function filterInvoices(type, btn) {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline');
                });
                btn.classList.remove('btn-outline');
                btn.classList.add('btn-primary', 'active');

                const rows = document.querySelectorAll('.inv-row');
                rows.forEach(r => {
                    if (type === 'all') {
                        r.style.display = '';
                    } else if (type === 'running') {
                        r.style.display = r.classList.contains('row-running') ? '' : 'none';
                    } else if (type === 'unpaid') {
                        r.style.display = r.classList.contains('row-unpaid') ? '' : 'none';
                    } else if (type === 'paid') {
                        r.style.display = r.classList.contains('row-paid') ? '' : 'none';
                    }
                });
            }

            function openAdminParticularEditModal(invoiceId, pName, currentDue, sl) {
                document.getElementById('ape_invoice_id').value = invoiceId;
                document.getElementById('ape_particular_name').value = pName;
                document.getElementById('ape_current_due').value = currentDue;
                document.getElementById('ape_row_sl').value = sl;

                document.getElementById('ape_display_name').innerText = pName;
                document.getElementById('ape_display_current_due').innerText = '৳' + Number(currentDue).toLocaleString('en-BD');
                document.getElementById('ape_new_amount').value = currentDue;
                document.getElementById('ape_remarks').value = '';

                const modal = document.getElementById('adminParticularEditModal');
                modal.style.display = 'flex';
                setTimeout(() => {
                    const input = document.getElementById('ape_new_amount');
                    if (input) { input.focus(); input.select(); }
                }, 100);
            }

            function closeAdminParticularEditModal() {
                const modal = document.getElementById('adminParticularEditModal');
                if (modal) modal.style.display = 'none';
            }

            function submitAdminParticularEdit(e) {
                e.preventDefault();
                const btn = document.getElementById('ape_submit_btn');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> সেভ হচ্ছে...';

                const invoiceId = document.getElementById('ape_invoice_id').value;
                const pName = document.getElementById('ape_particular_name').value;
                const currentDue = document.getElementById('ape_current_due').value;
                const newAmount = document.getElementById('ape_new_amount').value;
                const remarks = document.getElementById('ape_remarks').value;
                const sl = document.getElementById('ape_row_sl').value;

                fetch("{{ route('student.fees.particular.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        particular_name: pName,
                        current_due: currentDue,
                        new_amount: newAmount,
                        remarks: remarks
                    })
                })
                    .then(r => r.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (data.success) {
                            closeAdminParticularEditModal();
                            // Update UI elements
                            const cell = document.getElementById('particularDueCell_' + sl);
                            const payCell = document.getElementById('particularPayCell_' + sl);
                            const valSpan = document.getElementById('particularDueVal_' + sl);
                            const chk = document.querySelector('.step1-chk[data-name="' + pName + '"]');

                            if (data.is_paid) {
                                if (cell) {
                                    cell.innerHTML = '<div style="display:inline-flex;align-items:center;gap:6px"><span style="color:#16a34a;font-weight:700">Paid (0)</span><button type="button" onclick="openAdminParticularEditModal(\'' + invoiceId + '\', \'' + pName.replace(/'/g, "\\'") + '\', 0, ' + sl + ')" style="background:#f8fafc;border:1px solid #cbd5e1;color:#64748b;border-radius:4px;padding:2px 6px;font-size:10px;font-weight:700;cursor:pointer"><i class="fa-solid fa-pencil"></i></button></div>';
                                }
                                if (payCell) {
                                    payCell.innerHTML = '';
                                }
                            } else {
                                if (valSpan) {
                                    valSpan.innerText = Number(data.new_due).toLocaleString('en-BD');
                                    valSpan.style.color = '#2563eb';
                                }
                                if (chk) {
                                    chk.dataset.amount = data.new_due;
                                }
                            }

                            if (typeof updateStep1Total === 'function') {
                                updateStep1Total();
                            }

                            alert(data.message);
                        } else {
                            alert(data.message || 'Error updating fee amount.');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
                    });
            }

            function filterStep1Table(type, btn) {
                document.querySelectorAll('.step1-filter-btn').forEach(b => {
                    b.style.background = '#fff';
                    b.style.color = '#475569';
                    b.style.borderColor = '#cbd5e1';
                });
                if (type === 'all') {
                    btn.style.background = '#2563eb';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#2563eb';
                } else if (type === 'due') {
                    btn.style.background = '#be123c';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#be123c';
                } else if (type === 'paid') {
                    btn.style.background = '#15803d';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#15803d';
                }

                const rows = document.querySelectorAll('tr[data-step1-status]');
                rows.forEach(r => {
                    const status = r.getAttribute('data-step1-status');
                    if (type === 'all') {
                        r.style.display = '';
                    } else if (type === 'due') {
                        r.style.display = (status === 'due') ? '' : 'none';
                    } else if (type === 'paid') {
                        r.style.display = (status === 'paid') ? '' : 'none';
                    }
                });
            }

            function switchSemBreakdownTab(activeIdx) {
                document.querySelectorAll('.sem-breakdown-tab-btn').forEach((btn, idx) => {
                    if (idx === activeIdx) {
                        btn.style.background = '#2563eb';
                        btn.style.color = '#fff';
                        btn.style.borderColor = '#2563eb';
                    } else {
                        btn.style.background = '#fff';
                        btn.style.color = '#334155';
                        btn.style.borderColor = '#cbd5e1';
                    }
                });

                document.querySelectorAll('.sem-breakdown-pane').forEach((pane, idx) => {
                    pane.style.display = (idx === activeIdx) ? 'block' : 'none';
                });
            }
        </script>
    @endpush
</x-student-layout>