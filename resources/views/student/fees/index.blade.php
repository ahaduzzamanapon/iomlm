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
                @if(!empty($batch))
                    <span class="badge no-dot"
                        style="font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;font-family:'Kalpurush',sans-serif">
                        <i class="fa-solid fa-users-rectangle"></i> ব্যাচ: {{ $batch->name }}
                    </span>
                @endif
                @if(!empty($academicYear))
                    <span class="badge no-dot"
                        style="font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-family:'Kalpurush',sans-serif">
                        <i class="fa-solid fa-calendar-days"></i> শিক্ষাবর্ষ: {{ $academicYear->name }}
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
                        style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:9px 16px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px;color:#1e40af;flex-wrap:wrap">
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="fa-solid fa-user-shield" style="font-size:16px;color:#2563eb"></i>
                            <span><strong>অ্যাডমিন মোড:</strong> আপনি নিচে টেবিলের ফি আইটেমের টাকা সরাসরি এডিট
                                (কমানো/বাড়ানো) বা নতুন ফি যোগ করতে পারবেন।</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <button type="button" onclick="openAdminAddFeeModal()"
                                style="background:#16a34a;color:#fff;border:none;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 1px 3px rgba(22,163,74,0.3);transition:all .15s"
                                onmouseover="this.style.background='#15803d'"
                                onmouseout="this.style.background='#16a34a'">
                                <i class="fa-solid fa-plus-circle"></i> নতুন ফি যোগ করুন (Add Fee)
                            </button>
                            <span
                                style="background:#2563eb;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:12px">অ্যাডমিন
                                এডিট সক্রিয়</span>
                        </div>
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
                    style="max-width:750px;margin:0 auto 14px auto;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
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
                    @if($isAdminSession)
                        <button type="button" onclick="openAdminAddFeeModal()"
                            style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #16a34a;background:#16a34a;color:#fff;display:inline-flex;align-items:center;gap:6px;box-shadow:0 1px 3px rgba(22,163,74,0.25);transition:all .15s"
                            onmouseover="this.style.background='#15803d'"
                            onmouseout="this.style.background='#16a34a'">
                            <i class="fa-solid fa-plus-circle"></i> + নতুন ফি যোগ করুন
                        </button>
                    @endif
                </div>

                {{-- Step 1 Particulars Table --}}
                <div style="max-width:750px;margin:0 auto;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;font-family:'Kalpurush',sans-serif">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;font-family:'Kalpurush',sans-serif">
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
                                @if($isAdminSession)
                                    <th
                                        style="padding:10px 14px;width:130px;text-align:center;font-weight:700;color:#334155">
                                        অ্যাকশন</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($step1Particulars as $p)
                                @if(($p['name'] ?? '') === '_history')
                                    @continue
                                @endif
                                <tr style="border-bottom:1px solid #f1f5f9"
                                    data-step1-status="{{ $p['is_paid'] ? 'paid' : 'due' }}">
                                    <td style="padding:9px 14px;text-align:center;color:#64748b;font-weight:600">
                                        {{ $p['sl'] }}</td>
                                    <td style="padding:9px 14px;font-weight:600;color:#1e293b">
                                        <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap">
                                            <span>{{ $p['name'] }}</span>
                                            @if(!empty($p['category']) && $p['category'] === 'FINE')
                                                <span style="font-size:10px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'বিলম্ব / জরিমানা' }}">
                                                    {{ (str_contains($p['name'], 'এক্টিভিশন') || str_contains(mb_strtolower($p['name']), 'activation')) ? 'এক্টিভিশন ফি' : 'জরিমানা' }}
                                                </span>
                                            @elseif(!empty($p['category']) && $p['category'] === 'DOCUMENT')
                                                <span style="font-size:10px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'ডকুমেন্ট ফি' }}">
                                                    ডকুমেন্ট ফি
                                                </span>
                                            @elseif(!empty($p['category']) && $p['category'] === 'EXTRA')
                                                <span style="font-size:10px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'অতিরিক্ত ফি' }}">
                                                    অতিরিক্ত ফি
                                                </span>
                                            @elseif(!empty($p['is_added']))
                                                <span style="font-size:10px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'অ্যাডমিন কর্তৃক যুক্ত ফি' }}">
                                                    {{ (str_contains($p['name'], 'এক্টিভিশন') || str_contains(mb_strtolower($p['name']), 'activation')) ? 'এক্টিভিশন ফি' : 'নতুন যুক্ত' }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- History & Metadata Subtitle --}}
                                        @if($p['is_paid'] && (!empty($p['paid_by_name']) || !empty($p['paid_at'])))
                                            <div style="font-size:10.5px;color:#15803d;margin-top:2px;font-weight:500;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                                                <i class="fa-solid fa-circle-check" style="font-size:10px"></i>
                                                <span>পরিশোধকারী: <strong>{{ $p['paid_by_name'] ?? 'অনলাইন / শিক্ষার্থী' }}</strong></span>
                                                @if(!empty($p['paid_at']))
                                                    <span style="color:#64748b">• {{ $p['paid_at'] }}</span>
                                                @endif
                                                @if(!empty($p['payment_method']))
                                                    <span style="background:#dcfce7;color:#166534;padding:0 5px;border-radius:4px;font-size:9.5px;font-weight:700">({{ $p['payment_method'] }})</span>
                                                @endif
                                            </div>
                                        @elseif(!empty($p['reverted_by_name']))
                                            <div style="font-size:10.5px;color:#b91c1c;margin-top:2px;font-weight:500;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                                                <i class="fa-solid fa-rotate-left" style="font-size:10px"></i>
                                                <span>আনপেইড করেছেন: <strong>{{ $p['reverted_by_name'] }}</strong></span>
                                                @if(!empty($p['reverted_at']))
                                                    <span style="color:#64748b">• {{ $p['reverted_at'] }}</span>
                                                @endif
                                                @if(!empty($p['revert_reason']))
                                                    <span style="color:#64748b;font-style:italic">({{ $p['revert_reason'] }})</span>
                                                @endif
                                            </div>
                                        @elseif(!empty($p['is_added']) && (!empty($p['created_by_name']) || !empty($p['added_at'])))
                                            <div style="font-size:10.5px;color:#0369a1;margin-top:2px;font-weight:500;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                                                <i class="fa-solid fa-plus-circle" style="font-size:10px"></i>
                                                <span>যুক্ত করেছেন: <strong>{{ $p['created_by_name'] ?? 'অ্যাডমিন' }}</strong></span>
                                                @if(!empty($p['added_at']))
                                                    <span style="color:#64748b">• {{ $p['added_at'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding:9px 14px;text-align:center" id="particularDueCell_{{ $p['sl'] }}">
                                        @if($p['is_paid'])
                                            <div style="display:inline-flex;align-items:center;gap:6px">
                                                <span id="particularDueVal_{{ $p['sl'] }}"
                                                    style="color:#16a34a;font-weight:700">Paid
                                                    ({{ number_format($p['amount'], 0) }})</span>
                                                @if($isAdminSession && ($p['invoice_id'] ?? $selectedSemesterInvoice?->id))
                                                    <button type="button"
                                                        onclick="openAdminParticularEditModal('{{ $p['invoice_id'] ?? $selectedSemesterInvoice->id }}', '{{ addslashes($p['name']) }}', 0, {{ $p['sl'] }}, {{ !empty($p['is_added']) ? 'true' : 'false' }}, '{{ $p['position'] ?? 'at_bottom' }}', '{{ addslashes($p['relative_to'] ?? '') }}')"
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
                                                @if(($p['paid_amt'] ?? 0) > 0)
                                                    <span style="font-size:11px;color:#059669;background:#ecfdf5;padding:1px 6px;border-radius:4px;border:1px solid #a7f3d0;font-weight:600" title="ইতোমধ্যে পরিশোধিত: ৳{{ number_format($p['paid_amt'], 0) }}">
                                                        (পেইড: {{ number_format($p['paid_amt'], 0) }})
                                                    </span>
                                                @endif
                                                @if($isAdminSession && ($p['invoice_id'] ?? $selectedSemesterInvoice?->id))
                                                    <button type="button"
                                                        onclick="openAdminParticularEditModal('{{ $p['invoice_id'] ?? $selectedSemesterInvoice->id }}', '{{ addslashes($p['name']) }}', {{ $p['due'] }}, {{ $p['sl'] }}, {{ !empty($p['is_added']) ? 'true' : 'false' }}, '{{ $p['position'] ?? 'at_bottom' }}', '{{ addslashes($p['relative_to'] ?? '') }}')"
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
                                                @php
                                                    $isMonthly = !empty($p['name']) && (
                                                        str_contains($p['name'], 'Tuition Fee') || 
                                                        str_contains($p['name'], 'মাস') || 
                                                        str_contains($p['name'], 'Month')
                                                    ) && empty($p['is_added']) && (empty($p['category']) || $p['category'] === 'SEMESTER');
                                                @endphp
                                                <input type="checkbox" class="step1-chk" data-name="{{ $p['name'] }}"
                                                    data-amount="{{ $p['due'] }}"
                                                    data-total-amount="{{ $p['amount'] }}"
                                                    data-invoice-id="{{ $p['invoice_id'] ?? ($selectedSemesterInvoice?->id ?? '') }}"
                                                    data-invoice-no="{{ $p['invoice_no'] ?? ($selectedSemesterInvoice?->invoice_no ?? '') }}"
                                                    data-is-monthly="{{ $isMonthly ? '1' : '0' }}"
                                                    onchange="onStep1CheckboxChange(this)"
                                                    style="width:16px;height:16px;cursor:pointer;accent-color:#16a34a"
                                                    title="{{ $isMonthly ? 'পরবর্তী মাস নির্বাচন করলে পূর্বের সকল বকেয়া মাস ক্রমানুসারে নির্বাচিত হবে' : 'স্বতন্ত্রভাবে নির্বাচন করুন' }}">
                                            @endif
                                        @endif
                                    </td>
                                    @if($isAdminSession)
                                        <td style="padding:9px 14px;text-align:center">
                                            @if($p['is_paid'])
                                                <button type="button" class="btn btn-sm"
                                                    onclick="confirmStudentRevertPayment('{{ $p['invoice_id'] ?? ($selectedSemesterInvoice?->id ?? '') }}', '{{ addslashes($p['name']) }}', {{ $p['paid_amt'] ?? $p['amount'] }})"
                                                    title="পেমেন্ট বাতিল করে পুনরায় আনপেইড তালিকায় যুক্ত করুন"
                                                    style="padding:2px 7px;font-size:11px;font-family:'Kalpurush',sans-serif;background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;border-radius:4px;cursor:pointer;display:inline-flex;align-items:center;gap:3px;font-weight:700">
                                                    <i class="fa-solid fa-rotate-left"></i> আনপেইড করুন
                                                </button>
                                            @elseif(!empty($p['is_added']))
                                                <button type="button" class="btn btn-sm"
                                                    onclick="confirmStudentDeleteAddedFee('{{ $p['invoice_id'] ?? ($selectedSemesterInvoice?->id ?? '') }}', '{{ addslashes($p['name']) }}')"
                                                    title="এই অতিরিক্ত ফি সম্পূর্ণ মুছে ফেলুন"
                                                    style="padding:2px 7px;font-size:11px;font-family:'Kalpurush',sans-serif;background:#fff;border:1px solid #ef4444;color:#ef4444;border-radius:4px;cursor:pointer;display:inline-flex;align-items:center;gap:3px;font-weight:700">
                                                    <i class="fa-solid fa-trash-can"></i> মুছুন
                                                </button>
                                            @else
                                                <span style="color:#94a3b8;font-size:11px">—</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ── 📜 বিল ও পেমেন্ট হিস্ট্রি (Billing & Payment History Log) ── --}}
                <div style="max-width:750px;margin:20px auto 0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);font-family:'Kalpurush',sans-serif">
                    <div style="background:#f8fafc;padding:9px 14px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
                        <span style="font-weight:700;color:#1e293b;font-size:13px;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-clock-rotate-left" style="color:#0284c7"></i> বিল ও পেমেন্ট হিস্ট্রি (History Log)
                        </span>
                        <span style="font-size:11px;color:#64748b">
                            পেমেন্ট ও বিল পরিবর্তনের বিস্তারিত বিবরণ
                        </span>
                    </div>
                    <div style="max-height:200px;overflow-y:auto">
                        <table style="width:100%;border-collapse:collapse;font-size:11.5px">
                            <thead style="position:sticky;top:0;background:#f1f5f9;border-bottom:1px solid #cbd5e1">
                                <tr>
                                    <th style="padding:6px 10px;text-align:left;color:#475569">তারিখ ও সময়</th>
                                    <th style="padding:6px 10px;text-align:left;color:#475569">ফি / বিল বিবরণ</th>
                                    <th style="padding:6px 10px;text-align:center;color:#475569">কার্যক্রম</th>
                                    <th style="padding:6px 10px;text-align:right;color:#475569">টাকা</th>
                                    <th style="padding:6px 10px;text-align:left;color:#475569">ব্যবহারকারী / অ্যাডমিন</th>
                                    <th style="padding:6px 10px;text-align:left;color:#475569">মন্তব্য / মাধ্যম</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historyLogs ?? [] as $hLog)
                                    @php
                                        $actBadge = match($hLog['action'] ?? '') {
                                            'PAYMENT_COLLECTED' => ['bg' => '#dcfce7', 'color' => '#166534', 'text' => 'পেমেন্ট জমা'],
                                            'PAYMENT_REVERTED'  => ['bg' => '#fee2e2', 'color' => '#991b1b', 'text' => 'পেমেন্ট বাতিল / আনপেইড'],
                                            'FEE_ADDED'         => ['bg' => '#e0f2fe', 'color' => '#0369a1', 'text' => 'নতুন ফি যুক্ত'],
                                            'FEE_DELETED'       => ['bg' => '#fef2f2', 'color' => '#b91c1c', 'text' => 'ফি মোছা হয়েছে'],
                                            default             => ['bg' => '#f1f5f9', 'color' => '#475569', 'text' => $hLog['action'] ?? 'হিস্ট্রি'],
                                        };
                                    @endphp
                                    <tr style="border-bottom:1px solid #f8fafc">
                                        <td style="padding:6px 10px;color:#64748b;white-space:nowrap">{{ $hLog['date_time'] ?? '—' }}</td>
                                        <td style="padding:6px 10px;font-weight:600;color:#1e293b">{{ $hLog['particular'] ?? '—' }}</td>
                                        <td style="padding:6px 10px;text-align:center">
                                            <span style="font-size:10px;padding:2px 6px;border-radius:10px;font-weight:700;background:{{ $actBadge['bg'] }};color:{{ $actBadge['color'] }}">
                                                {{ $actBadge['text'] }}
                                            </span>
                                        </td>
                                        <td style="padding:6px 10px;text-align:right;font-weight:700;color:#0f172a">
                                            {{ isset($hLog['amount']) ? '৳' . number_format($hLog['amount'], 2) : '—' }}
                                        </td>
                                        <td style="padding:6px 10px;color:#334155;font-weight:600">
                                            {{ $hLog['user_name'] ?? 'অ্যাডমিন' }}
                                        </td>
                                        <td style="padding:6px 10px;color:#64748b">
                                            {{ $hLog['remarks'] ?? ($hLog['method'] ?? '—') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="padding:12px;text-align:center;color:#94a3b8">এখনও কোনো হিস্ট্রি লগ সংরক্ষিত নেই।</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Selected Summary Bar --}}
                <div id="step1SelectedSummary" style="display:none;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:14px 18px;margin-top:14px">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                        <div>
                            <span style="font-weight:700;color:#166534;font-size:14px">
                                <i class="fa-solid fa-circle-check" style="margin-right:6px"></i> নির্বাচিত আইটেম:
                                <span id="step1CountDisplay" style="background:#16a34a;color:#fff;padding:1px 8px;border-radius:12px;font-size:12px">0</span> টি
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px">
                            <span style="font-size:13px;color:#166534">মোট পেমেন্ট:</span>
                            <span style="font-size:20px;font-weight:800;color:#15803d">৳<span id="step1TotalDisplay">0</span></span>
                        </div>
                    </div>
                </div>

                {{-- Next Button --}}
                <div style="text-align:center;margin-top:16px">
                    <button type="button" class="btn btn-success" id="step1NextBtn" onclick="goToStep2Payment()" disabled
                        style="padding:10px 28px;font-size:14px;font-weight:700;border-radius:8px;display:inline-flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-arrow-right"></i> Next (Go Step 2)
                    </button>
                </div>
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
                                        $isActInv = ($inv->category === 'FINE' && (str_contains($inv->invoice_no, 'INV-ACT') || str_contains($inv->title, 'এক্টিভিশন') || str_contains(mb_strtolower($inv->title), 'activation')));
                                    @endphp
                                    @if($isActInv)
                                        <span class="badge no-dot" style="font-size:10.5px; background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-weight:700">এক্টিভিশন ফি</span>
                                    @else
                                        <span class="badge badge-secondary no-dot" style="font-size:10px">{{ $catLabel }}</span>
                                    @endif
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

    <div id="payInvoiceModal" onclick="if(event.target===this) closePayModal()"
        style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:999999; justify-content:center; align-items:flex-start; padding:25px 15px; box-sizing:border-box; overflow-y:auto; -webkit-overflow-scrolling:touch">
        <div onclick="event.stopPropagation()"
            style="background:#fff; border-radius:18px; max-width:520px; width:100%; margin:auto; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); animation:modalSlideUp .3s ease; display:flex; flex-direction:column; max-height:calc(100vh - 50px); overflow:hidden">
            <div
                style="background:linear-gradient(135deg,#047857,#065f46); color:#fff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; flex-shrink:0">
                <div>
                    <div style="font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-credit-card"></i> অনলাইন ফি পরিশোধ
                    </div>
                    <div style="font-size:11px; opacity:.85; margin-top:2px" id="modalInvNo">INV-00000</div>
                </div>
                <button type="button" onclick="closePayModal()"
                    style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer; line-height:1; opacity:0.85">&times;</button>
            </div>

            <form id="payForm" method="POST" action="" style="padding:22px; overflow-y:auto; flex:1">
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
                        style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid #cbd5e1; font-size:16px; font-weight:700; color:#1e                    {{-- Manual Fields (Hidden by default) --}}
                    <div id="manualPaymentFields"
                        style="display:none; background:#f8fafc; border:1.5px dashed #059669; border-radius:10px; padding:16px; margin-top:10px; font-family:'Kalpurush',sans-serif">
                        
                        {{-- Official Merchant Number Banner --}}
                        <div id="merchantNoticeBox" style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:8px; padding:12px 14px; margin-bottom:14px">
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px">
                                <div style="display:flex; align-items:center; gap:8px">
                                    <span style="background:#047857; color:#fff; font-size:11px; font-weight:700; padding:2px 7px; border-radius:5px">মার্চেন্ট তথ্য</span>
                                    <span id="merchantTypeLabel" style="font-weight:700; font-size:13px; color:#14532d">বিকাশ মার্চেন্ট / পেমেন্ট নম্বর:</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:6px">
                                    <code id="merchantNumberCode" style="font-size:15px; font-weight:800; color:#047857; background:#dcfce7; padding:3px 10px; border-radius:6px; letter-spacing:1px">01766305059</code>
                                    <button type="button" onclick="copyMerchantNumber()" id="copyMerchantBtn" style="background:#047857; color:#fff; border:none; padding:4px 10px; border-radius:6px; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px">
                                        <i class="fa-solid fa-copy"></i> <span id="copyBtnText">কপি</span>
                                    </button>
                                </div>
                            </div>
                            <div id="merchantInstructions" style="margin-top:6px; font-size:11.5px; color:#166534; line-height:1.4">
                                📌 আপনার বিকাশ অ্যাপ থেকে <strong>Payment</strong> অথবা <strong>Send Money</strong> করে প্রাপ্ত <strong>১০ ডিজিটের TrxID</strong> এবং আপনার প্রেরক নম্বরটি নিচে দিন।
                            </div>
                        </div>

                        <div style="margin-bottom:12px">
                            <label
                                style="font-size:12px; font-weight:700; color:#334155; display:block; margin-bottom:4px">ম্যানুয়াল
                                মাধ্যম নির্বাচন করুন <span style="color:#dc2626">*</span></label>
                            <select id="manualMethodSelect" class="form-control"
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1.5px solid #cbd5e1; font-size:13px"
                                onchange="onManualMethodChange(this.value)">
                                <option value="BKASH_MANUAL" selected>ম্যানুয়াল বিকাশ ট্রানজেকশন (bKash Manual Send Money / Payment)</option>
                                <option value="NAGAD_MANUAL">নগদ ম্যানুয়াল ট্রানজেকশন (Nagad TrxID)</option>
                                <option value="ROCKET_MANUAL">রকেট ম্যানুয়াল ট্রানজেকশন (Rocket TrxID)</option>
                                <option value="BANK_TRANSFER">ব্যাংক ডিপোজিট / স্লিপ (Bank Transfer)</option>
                                <option value="CASH">সরাসরি অফিস ক্যাশ (Cash at Office)</option>
                            </select>
                        </div>
                        <div style="margin-bottom:12px">
                            <label id="manualSenderLabel"
                                style="font-size:12px; font-weight:700; color:#334155; display:block; margin-bottom:4px">বিকাশ
                                / প্রেরক মোবাইল নম্বর (Sender Mobile No) <span style="color:#dc2626">*</span></label>
                            <input type="text" name="sender_number" id="manualSenderInput" maxlength="15"
                                placeholder="যেমন: 01712345678 বা আপনার বিকাশ নম্বর" class="form-control"
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1.5px solid #cbd5e1; font-size:13px; box-sizing:border-box">
                        </div>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
                                <label id="manualTrxLabel"
                                    style="font-size:12px; font-weight:700; color:#334155; margin:0">Transaction
                                    ID / রেফারেন্স ট্রানজেকশন আইডি (TrxID) <span style="color:#dc2626">*</span></label>
                                <span id="trxCharCounter" style="font-size:11px; font-weight:700; color:#64748b">১০ ডিজিট আবশ্যক</span>
                            </div>
                            <input type="text" name="transaction_id" id="manualTrxInput" maxlength="10" minlength="10"
                                placeholder="যেমন: 8N7A6B5C4D (১০ ডিজিটের TrxID)" class="form-control"
                                oninput="onTrxInput(this)"
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1.5px solid #cbd5e1; font-size:14px; font-weight:700; letter-spacing:1px; box-sizing:border-box; text-transform:uppercase">
                            <div id="trxHelperText" style="font-size:11px; color:#64748b; margin-top:4px">
                                বিকাশ/নগদ এর এসএমএস বা অ্যাপে প্রদর্শিত ১০ অক্ষরের TrxID লিখুন। এক TrxID একাধিকবার ব্যবহার করা যাবে না।
                            </div>
                        </div>
                    </div>ওয়ালেট ও পিন
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
    <div id="adminParticularEditModal" onclick="if(event.target===this) closeAdminParticularEditModal()"
        style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:999999;align-items:flex-start;justify-content:center;font-family:'Kalpurush',sans-serif;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:25px 15px;box-sizing:border-box">
        <div onclick="event.stopPropagation()"
            style="background:#fff;border-radius:14px;width:95%;max-width:440px;margin:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.25);display:flex;flex-direction:column;max-height:calc(100vh - 50px);overflow:hidden;border:1px solid #cbd5e1">
            <div
                style="background:#1e40af;color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0">
                <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-pencil"></i> ফি এর পরিমাণ পরিবর্তন / সমন্বয় (Admin Edit)
                </div>
                <button type="button" onclick="closeAdminParticularEditModal()"
                    style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1">&times;</button>
            </div>
            <form id="adminParticularEditForm" onsubmit="submitAdminParticularEdit(event)" style="padding:20px;overflow-y:auto;flex:1">
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

                <div id="ape_position_wrapper" style="display:none;margin-bottom:16px">
                    <label for="ape_position" style="display:block;font-size:12.5px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        তালিকায় অবস্থান (Position in List):
                    </label>
                    <select id="ape_position" onchange="onAdminParticularEditPositionChange(this.value)"
                        style="width:100%;padding:9px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#0f172a;outline:none;background:#fff;box-sizing:border-box">
                        <option value="at_bottom">তালিকার সবার নিচে (At the End - ডিফল্ট)</option>
                        <option value="after">নির্দিষ্ট আইটেমের পরে (After Specific Item)</option>
                        <option value="before">নির্দিষ্ট আইটেমের আগে (Before Specific Item)</option>
                        <option value="at_top">তালিকার সবার উপরে (At the Top)</option>
                    </select>

                    <div id="ape_relative_group" style="display:none;margin-top:10px;background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:5px">
                            <span id="ape_relative_label">কোন আইটেমের পরে যুক্ত হবে?</span> <span style="color:#dc2626">*</span>:
                        </label>
                        <select id="ape_relative_to"
                            style="width:100%;padding:8px 12px;border:1.5px solid #2563eb;border-radius:8px;font-size:13px;color:#0f172a;outline:none;background:#fff;box-sizing:border-box">
                            @foreach($step1Particulars as $item)
                                <option value="{{ $item['name'] }}">{{ $item['sl'] }}. {{ $item['name'] }} (৳{{ number_format($item['amount'], 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div
                    style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;padding-top:14px">
                    <button type="button" id="ape_delete_btn" onclick="submitAdminParticularDelete()" class="btn btn-outline"
                        style="display:none;border-color:#fca5a5;color:#dc2626;background:#fef2f2;font-weight:700">
                        <i class="fa-solid fa-trash-can"></i> মুছে ফেলুন
                    </button>
                    <div style="display:flex;gap:10px;margin-left:auto">
                        <button type="button" onclick="closeAdminParticularEditModal()" class="btn btn-outline"
                            style="border-color:#cbd5e1;color:#475569">
                            বাতিল (Cancel)
                        </button>
                        <button type="submit" id="ape_submit_btn" class="btn btn-primary"
                            style="background:#2563eb;border-color:#2563eb;font-weight:700">
                            <i class="fa-solid fa-check"></i> সংরক্ষণ করুন (Save Amount)
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Admin Add Fee Modal ── --}}
    <div id="adminAddFeeModal" onclick="if(event.target===this) closeAdminAddFeeModal()"
        style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:999999;align-items:flex-start;justify-content:center;font-family:'Kalpurush',sans-serif;overflow-y:auto;-webkit-overflow-scrolling:touch;padding:25px 15px;box-sizing:border-box">
        <div onclick="event.stopPropagation()"
            style="background:#fff;border-radius:14px;width:95%;max-width:480px;margin:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.25);display:flex;flex-direction:column;max-height:calc(100vh - 50px);overflow:hidden;border:1px solid #cbd5e1">
            <div
                style="background:linear-gradient(135deg, #15803d, #166534);color:#fff;padding:15px 22px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0">
                <div style="font-weight:700;font-size:16px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-plus-circle"></i> নতুন ফি যোগ করুন (Add Fee)
                </div>
                <button type="button" onclick="closeAdminAddFeeModal()"
                    style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;line-height:1">&times;</button>
            </div>
            <form id="adminAddFeeForm" onsubmit="submitAdminAddFee(event)" style="padding:22px;overflow-y:auto;flex:1">
                <input type="hidden" id="aaf_invoice_id" name="invoice_id" value="{{ $selectedSemesterInvoice?->id }}">
                <input type="hidden" id="aaf_student_id" name="student_id" value="{{ $student?->id }}">
                <input type="hidden" id="aaf_semester_id" name="semester_id" value="{{ $selectedSemesterId }}">

                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:4px">
                        প্রযোজ্য সেমিস্টার / কোর্স:
                    </label>
                    <div style="font-size:13.5px;font-weight:700;color:#15803d;background:#f0fdf4;padding:8px 12px;border-radius:6px;border:1px solid #bbf7d0">
                        {{ $selectedSemester?->name ?? ($runningSemesterName ?? 'সাধারণ কোর্স ফি') }}
                    </div>
                </div>

                <div style="margin-bottom:14px">
                    <label for="aaf_particular_name" style="display:block;font-size:12.5px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        ফি এর নাম / বিবরণ (Particular Name) <span style="color:#dc2626">*</span>:
                    </label>
                    <input type="text" id="aaf_particular_name" name="particular_name" required
                        placeholder="যেমন: লেট ফি, পুনঃপরীক্ষা ফি, জরিমানা..."
                        style="width:100%;padding:9px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;color:#0f172a;outline:none;box-sizing:border-box">

                    {{-- Quick preset pills --}}
                    <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:5px">
                        <span style="font-size:11px;color:#64748b;align-self:center;font-weight:600">কুইক সিলেক্ট:</span>
                        @foreach(['কোর্স এক্টিভিশন ফি (Activation Fee)', 'লেট ফি (Late Fee)', 'পুনঃপরীক্ষা ফি (Retake Fee)', 'সার্টিফিকেট ফি (Certificate Fee)', 'আইডি কার্ড ফি (ID Card Fee)', 'জরিমানা (Fine)', 'অন্যান্য ফি (Other Fee)'] as $preset)
                            <button type="button" onclick="setFeeNamePreset('{{ $preset }}')"
                                style="font-size:11px;padding:2px 8px;border-radius:12px;border:1px solid #cbd5e1;background:#f8fafc;color:#334155;cursor:pointer;transition:all .15s"
                                onmouseover="this.style.background='#e2e8f0'"
                                onmouseout="this.style.background='#f8fafc'">
                                + {{ $preset }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div style="margin-bottom:16px">
                    <label for="aaf_amount" style="display:block;font-size:13px;font-weight:700;color:#0f172a;margin-bottom:6px">
                        টাকার পরিমাণ (Fee Amount - ৳) <span style="color:#dc2626">*</span>:
                    </label>
                    <div style="position:relative">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-weight:700;color:#15803d;font-size:16px">৳</span>
                        <input type="number" step="1" min="1" id="aaf_amount" name="amount" required placeholder="0"
                            style="width:100%;padding:10px 12px 10px 32px;border:1.5px solid #16a34a;border-radius:8px;font-size:16px;font-weight:800;color:#0f172a;outline:none;box-sizing:border-box">
                    </div>
                </div>

                <div style="margin-bottom:16px">
                    <label for="aaf_position" style="display:block;font-size:12.5px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        তালিকায় অবস্থান (Position in List):
                    </label>
                    <select id="aaf_position" onchange="onAdminAddFeePositionChange(this.value)"
                        style="width:100%;padding:9px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#0f172a;outline:none;background:#fff;box-sizing:border-box">
                        <option value="at_bottom" selected>তালিকার সবার নিচে (At the End - ডিফল্ট)</option>
                        <option value="after">নির্দিষ্ট আইটেমের পরে (After Specific Item)</option>
                        <option value="before">নির্দিষ্ট আইটেমের আগে (Before Specific Item)</option>
                        <option value="at_top">তালিকার সবার উপরে (At the Top)</option>
                    </select>
                </div>

                <div id="aaf_relative_group" style="display:none;margin-bottom:14px;background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0">
                    <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:5px">
                        <span id="aaf_relative_label">কোন আইটেমের পরে যুক্ত হবে?</span> <span style="color:#dc2626">*</span>:
                    </label>
                    <select id="aaf_relative_to"
                        style="width:100%;padding:8px 12px;border:1.5px solid #16a34a;border-radius:8px;font-size:13px;color:#0f172a;outline:none;background:#fff;box-sizing:border-box">
                        @foreach($step1Particulars as $item)
                            <option value="{{ $item['name'] }}">{{ $item['sl'] }}. {{ $item['name'] }} (৳{{ number_format($item['amount'], 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:18px">
                    <label for="aaf_remarks" style="display:block;font-size:12.5px;font-weight:700;color:#334155;margin-bottom:4px">
                        কারণ / নোট (Reason / Remarks - ঐচ্ছিক):
                    </label>
                    <input type="text" id="aaf_remarks" name="remarks"
                        placeholder="যেমন: কর্তৃপক্ষের নির্দেশে বিশেষ ফি ধার্য..."
                        style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;box-sizing:border-box;outline:none">
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #f1f5f9;padding-top:14px">
                    <button type="button" onclick="closeAdminAddFeeModal()" class="btn btn-outline"
                        style="border-color:#cbd5e1;color:#475569">
                        বাতিল (Cancel)
                    </button>
                    <button type="submit" id="aaf_submit_btn" class="btn btn-primary"
                        style="background:#16a34a;border-color:#16a34a;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-plus-circle"></i> ফি যোগ করুন (Add Fee)
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

            function copyMerchantNumber() {
                const num = '01766305059';
                navigator.clipboard.writeText(num).then(() => {
                    const btnText = document.getElementById('copyBtnText');
                    if (btnText) btnText.innerText = 'কপি হয়েছে!';
                    setTimeout(() => { if (btnText) btnText.innerText = 'কপি'; }, 2000);
                }).catch(() => {
                    alert('মার্চেন্ট নম্বর: ' + num);
                });
            }

            function onTrxInput(input) {
                input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                const len = input.value.length;
                const counter = document.getElementById('trxCharCounter');
                if (counter) {
                    if (len === 10) {
                        counter.innerText = '✓ ১০ ডিজিট পূর্ণ';
                        counter.style.color = '#047857';
                        input.style.borderColor = '#10b981';
                    } else {
                        counter.innerText = len + '/১০ ডিজিট';
                        counter.style.color = len > 0 ? '#b45309' : '#64748b';
                        input.style.borderColor = len > 0 ? '#f59e0b' : '#cbd5e1';
                    }
                }
            }

            function onManualMethodChange(val) {
                if (isManualModeActive) {
                    setManualMethodValue(val);
                }

                const noticeBox = document.getElementById('merchantNoticeBox');
                const typeLabel = document.getElementById('merchantTypeLabel');
                const numCode = document.getElementById('merchantNumberCode');
                const copyBtn = document.getElementById('copyMerchantBtn');
                const instText = document.getElementById('merchantInstructions');
                const senderLabel = document.getElementById('manualSenderLabel');
                const senderInput = document.getElementById('manualSenderInput');
                const trxLabel = document.getElementById('manualTrxLabel');
                const trxInput = document.getElementById('manualTrxInput');
                const trxHelper = document.getElementById('trxHelperText');
                const trxCounter = document.getElementById('trxCharCounter');

                if (noticeBox) noticeBox.style.display = 'block';

                if (val === 'BKASH_MANUAL') {
                    if (typeLabel) typeLabel.innerText = 'বিকাশ মার্চেন্ট / পেমেন্ট নম্বর:';
                    if (numCode) { numCode.innerText = '01766305059'; numCode.style.display = 'inline'; }
                    if (copyBtn) copyBtn.style.display = 'inline-flex';
                    if (instText) instText.innerHTML = '📌 আপনার বিকাশ অ্যাপ থেকে <strong>Payment</strong> অথবা <strong>Send Money</strong> করে প্রাপ্ত <strong>১০ ডিজিটের TrxID</strong> এবং প্রেরক নম্বরটি নিচে দিন।';
                    if (senderLabel) senderLabel.innerHTML = 'বিকাশ / প্রেরক মোবাইল নম্বর (Sender Mobile No) <span style="color:#dc2626">*</span>';
                    if (senderInput) senderInput.placeholder = 'যেমন: 01712345678 বা আপনার বিকাশ নম্বর';
                    if (trxLabel) trxLabel.innerHTML = 'Transaction ID / রেফারেন্স ট্রানজেকশন আইডি (TrxID) <span style="color:#dc2626">*</span>';
                    if (trxInput) { trxInput.placeholder = 'যেমন: 8N7A6B5C4D (১০ ডিজিটের বিকাশ TrxID)'; trxInput.maxLength = 10; }
                    if (trxHelper) trxHelper.innerText = 'বিকাশ অ্যাপ বা এসএমএস-এ প্রাপ্ত ১০ অক্ষরের TrxID লিখুন। এক TrxID একাধিকবার ব্যবহার করা যাবে না।';
                    if (trxCounter) trxCounter.style.display = 'inline';
                } else if (val === 'NAGAD_MANUAL') {
                    if (typeLabel) typeLabel.innerText = 'নগদ পেমেন্ট / মার্চেন্ট নম্বর:';
                    if (numCode) { numCode.innerText = '01766305059'; numCode.style.display = 'inline'; }
                    if (copyBtn) copyBtn.style.display = 'inline-flex';
                    if (instText) instText.innerHTML = '📌 আপনার নগদ একাউন্ট থেকে <strong>Merchant Pay</strong> অথবা <strong>Send Money</strong> করে প্রাপ্ত <strong>১০ ডিজিটের TrxID</strong> এবং প্রেরক নম্বর দিন।';
                    if (senderLabel) senderLabel.innerHTML = 'নগদ / প্রেরক মোবাইল নম্বর (Sender Mobile No) <span style="color:#dc2626">*</span>';
                    if (senderInput) senderInput.placeholder = 'যেমন: 01812345678 বা আপনার নগদ নম্বর';
                    if (trxLabel) trxLabel.innerHTML = 'নগদ ট্রানজেকশন আইডি (Nagad TrxID) <span style="color:#dc2626">*</span>';
                    if (trxInput) { trxInput.placeholder = 'যেমন: 7K9M2P4Q1R (১০ ডিজিটের নগদ TrxID)'; trxInput.maxLength = 10; }
                    if (trxHelper) trxHelper.innerText = 'নগদের ১০ অক্ষরের TrxID লিখুন। এক TrxID একাধিকবার ব্যবহার করা যাবে না।';
                    if (trxCounter) trxCounter.style.display = 'inline';
                } else if (val === 'ROCKET_MANUAL') {
                    if (typeLabel) typeLabel.innerText = 'রকেট পেমেন্ট নম্বর:';
                    if (numCode) { numCode.innerText = '01766305059'; numCode.style.display = 'inline'; }
                    if (copyBtn) copyBtn.style.display = 'inline-flex';
                    if (instText) instText.innerHTML = '📌 রকেট ওয়ালেট থেকে পেমেন্ট বা ট্রান্সফার করে প্রাপ্ত ১০ ডিজিটের ট্রানজেকশন আইডি প্রদান করুন।';
                    if (senderLabel) senderLabel.innerHTML = 'রকেট প্রেরক নম্বর <span style="color:#dc2626">*</span>';
                    if (senderInput) senderInput.placeholder = 'যেমন: 017123456789 (রকেট ১২ ডিজিট)';
                    if (trxLabel) trxLabel.innerHTML = 'রকেট TrxID <span style="color:#dc2626">*</span>';
                    if (trxInput) { trxInput.placeholder = '১০ ডিজিটের ট্রানজেকশন আইডি'; trxInput.maxLength = 10; }
                    if (trxHelper) trxHelper.innerText = 'রকেট এসএমএস-এর ১০ অক্ষরের TrxID লিখুন।';
                    if (trxCounter) trxCounter.style.display = 'inline';
                } else if (val === 'BANK_TRANSFER') {
                    if (typeLabel) typeLabel.innerText = 'ব্যাংক একাউন্ট বিবরণ:';
                    if (numCode) { numCode.innerText = 'IOM Account: 2050XXXXXXXXX'; numCode.style.display = 'inline'; }
                    if (copyBtn) copyBtn.style.display = 'none';
                    if (instText) instText.innerHTML = '📌 ব্যাংকে সরাসরি বা অনলাইন ট্রান্সফার (BEFTN/NPSB) করে ব্যাংক স্লিপ নম্বর / ডিপোজিট রেফারেন্স নিচে দিন।';
                    if (senderLabel) senderLabel.innerHTML = 'ডিপোজিটর নাম / প্রেরক ব্যাংক ও একাউন্ট <span style="color:#dc2626">*</span>';
                    if (senderInput) senderInput.placeholder = 'যেমন: মো: আব্দুল্লাহ, ইসলামী ব্যাংক বাংলাদেশ';
                    if (trxLabel) trxLabel.innerHTML = 'ব্যাংক ডিপোজিট স্লিপ নং / রেফারেন্স <span style="color:#dc2626">*</span>';
                    if (trxInput) { trxInput.placeholder = 'যেমন: SLIP-123456 বা রেফারেন্স আইডি'; trxInput.maxLength = 30; }
                    if (trxHelper) trxHelper.innerText = 'ব্যাংক রসিদ বা ট্রান্সফারের রেফারেন্স নম্বর লিখুন।';
                    if (trxCounter) trxCounter.style.display = 'none';
                } else {
                    if (typeLabel) typeLabel.innerText = 'অফিস ক্যাশ পেমেন্ট:';
                    if (numCode) numCode.style.display = 'none';
                    if (copyBtn) copyBtn.style.display = 'none';
                    if (instText) instText.innerHTML = '📌 মাদ্রাসার অফিসে সরাসরি নগদ জমা দিয়ে থাকলে ক্যাশ ভাউচার বা রসিদের তথ্য উল্লেখ করুন।';
                    if (senderLabel) senderLabel.innerHTML = 'জমাদানকারীর নাম / যোগাযোগের নম্বর <span style="color:#dc2626">*</span>';
                    if (senderInput) senderInput.placeholder = 'যেমন: অভিভাবক বা শিক্ষার্থীর মোবাইল নম্বর';
                    if (trxLabel) trxLabel.innerHTML = 'অফিস মানি রিসিট নং (যদি থাকে)';
                    if (trxInput) { trxInput.placeholder = 'যেমন: MR-2026-001 (ঐচ্ছিক)'; trxInput.maxLength = 30; }
                    if (trxHelper) trxHelper.innerText = 'অফিস থেকে প্রদত্ত রসিদ নম্বর লিখুন (যদি থাকে)।';
                    if (trxCounter) trxCounter.style.display = 'none';
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
                document.body.style.overflow = 'hidden';
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

            function onStep1CheckboxChange(clickedChk) {
                const isMonthly = clickedChk.dataset.isMonthly === '1';

                if (isMonthly) {
                    const monthlyChks = Array.from(document.querySelectorAll('.step1-chk:not(:disabled)[data-is-monthly="1"]'));
                    const clickedIndex = monthlyChks.indexOf(clickedChk);

                    if (clickedIndex !== -1) {
                        if (clickedChk.checked) {
                            // ক্রমানুসারে আগের সকল বকেয়া মাস অটো-সিলেক্ট হবে
                            for (let i = 0; i <= clickedIndex; i++) {
                                monthlyChks[i].checked = true;
                            }
                        } else {
                            // কোনো মাস আনসিলেক্ট করলে পরবর্তী মাসগুলো আনসিলেক্ট হবে
                            for (let i = clickedIndex; i < monthlyChks.length; i++) {
                                monthlyChks[i].checked = false;
                            }
                        }
                    }
                }
                // এক্টিভিশন ফি, জরিমানা, ডকুমেন্ট ফি বা অন্যান্য ফি স্বতন্ত্রভাবে সিলেক্ট/আনসিলেক্ট করা যাবে

                updateStep1Total();
            }

            function updateStep1Total() {
                const chks = document.querySelectorAll('.step1-chk:checked');
                let total = 0;
                chks.forEach(c => total += parseFloat(c.dataset.amount) || 0);

                const summaryEl = document.getElementById('step1SelectedSummary');
                const totalDisp = document.getElementById('step1TotalDisplay');
                const countDisp = document.getElementById('step1CountDisplay');
                const nextBtn = document.getElementById('step1NextBtn');

                if (chks.length > 0) {
                    if (summaryEl) summaryEl.style.display = 'flex';
                    if (totalDisp) totalDisp.innerText = Math.round(total).toLocaleString('en-BD');
                    if (countDisp) countDisp.innerText = chks.length;
                    if (nextBtn) {
                        nextBtn.disabled = false;
                        nextBtn.style.opacity = '1';
                        nextBtn.style.cursor = 'pointer';
                    }
                } else {
                    if (summaryEl) summaryEl.style.display = 'none';
                    if (nextBtn) {
                        nextBtn.disabled = true;
                        nextBtn.style.opacity = '0.6';
                        nextBtn.style.cursor = 'not-allowed';
                    }
                }
            }

            function goToStep2Payment() {
                const chks = document.querySelectorAll('.step1-chk:checked');
                if (chks.length === 0) {
                    alert('অনুগ্রহ করে ফি পরিশোধ করতে কমপক্ষে একটি ফি আইটেম নির্বাচন করুন। (Please select at least one fee item to proceed to Step 2)');
                    return;
                }

                let total = 0;
                let invoiceMap = {};

                chks.forEach(c => {
                    const amt = parseFloat(c.dataset.amount) || 0;
                    total += amt;
                    const invId = c.dataset.invoiceId || "{{ $selectedSemesterInvoice?->id ?? '' }}";
                    const invNo = c.dataset.invoiceNo || "{{ $selectedSemesterInvoice?->invoice_no ?? '' }}";
                    if (!invoiceMap[invId]) {
                        invoiceMap[invId] = { id: invId, no: invNo, due: 0, names: [] };
                    }
                    invoiceMap[invId].due += amt;
                    invoiceMap[invId].names.push(c.dataset.name);
                });

                const invoiceIds = Object.keys(invoiceMap);
                if (invoiceIds.length > 1) {
                    alert('অনলাইন গেটওয়ের মাধ্যমে একসাথে একাধিক ভিন্ন ইনভয়েস পরিশোধ করা যায় না। অনুগ্রহ করে কোর্স ফি এবং এক্টিভিশন ফি আলাদাভাবে নির্বাচন করে পরিশোধ করুন।');
                    return;
                }

                const target = invoiceMap[invoiceIds[0]];
                const invoiceId = target.id || "{{ $selectedSemesterInvoice?->id ?? ($invoices->first()?->id ?? '') }}";
                const invoiceNo = target.no || "{{ $selectedSemesterInvoice?->invoice_no ?? ($invoices->first()?->invoice_no ?? 'INV-001') }}";
                const title = target.names.join(', ');
                const invoiceDue = parseFloat(target.due) || total;

                openPayModal(invoiceId, title, invoiceNo, invoiceDue, total, target.names.join(', '), {{ $monthlyTuition ?? 100 }});
            }
            window.goToStep2 = goToStep2Payment;

            function closePayModal() {
                document.getElementById('payInvoiceModal').style.display = 'none';
                document.body.style.overflow = '';
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

            function onAdminParticularEditPositionChange(val) {
                const group = document.getElementById('ape_relative_group');
                const label = document.getElementById('ape_relative_label');
                if (!group) return;
                if (val === 'after') {
                    group.style.display = 'block';
                    if (label) label.innerText = 'কোন আইটেমের পরে যুক্ত হবে? (Insert After)';
                } else if (val === 'before') {
                    group.style.display = 'block';
                    if (label) label.innerText = 'কোন আইটেমের আগে যুক্ত হবে? (Insert Before)';
                } else {
                    group.style.display = 'none';
                }
            }

            function openAdminParticularEditModal(invoiceId, pName, currentDue, sl, isAdded = false, position = 'at_bottom', relativeTo = '') {
                document.getElementById('ape_invoice_id').value = invoiceId;
                document.getElementById('ape_particular_name').value = pName;
                document.getElementById('ape_current_due').value = currentDue;
                document.getElementById('ape_row_sl').value = sl;

                document.getElementById('ape_display_name').innerText = pName;
                document.getElementById('ape_display_current_due').innerText = '৳' + Number(currentDue).toLocaleString('en-BD');
                document.getElementById('ape_new_amount').value = currentDue;
                document.getElementById('ape_remarks').value = '';

                const posWrapper = document.getElementById('ape_position_wrapper');
                if (posWrapper) {
                    posWrapper.style.display = isAdded ? 'block' : 'none';
                    const posSelect = document.getElementById('ape_position');
                    if (posSelect) posSelect.value = position || 'at_bottom';
                    const relSelect = document.getElementById('ape_relative_to');
                    if (relSelect && relativeTo) relSelect.value = relativeTo;
                    onAdminParticularEditPositionChange(position || 'at_bottom');
                }

                const delBtn = document.getElementById('ape_delete_btn');
                if (delBtn) {
                    delBtn.style.display = isAdded ? 'inline-flex' : 'none';
                }

                const modal = document.getElementById('adminParticularEditModal');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    const input = document.getElementById('ape_new_amount');
                    if (input) { input.focus(); input.select(); }
                }, 100);
            }

            function closeAdminParticularEditModal() {
                const modal = document.getElementById('adminParticularEditModal');
                if (modal) modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            function submitAdminParticularDelete() {
                const pName = document.getElementById('ape_particular_name').value;
                const invoiceId = document.getElementById('ape_invoice_id').value;
                if (!confirm('আপনি কি নিশ্চিত যে "' + pName + '" ফি আইটেমটি সম্পূর্ণ মুছে ফেলতে চান?')) {
                    return;
                }
                const btn = document.getElementById('ape_delete_btn');
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> মুছা হচ্ছে...';

                fetch("{{ route('student.fees.particular.delete') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        particular_name: pName
                    })
                })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    if (data.success) {
                        closeAdminParticularEditModal();
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || 'ফি মুছে ফেলতে সমস্যা হয়েছে।');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
                });
            }

            function onAdminAddFeePositionChange(val) {
                const group = document.getElementById('aaf_relative_group');
                const label = document.getElementById('aaf_relative_label');
                if (!group) return;
                if (val === 'after') {
                    group.style.display = 'block';
                    if (label) label.innerText = 'কোন আইটেমের পরে যুক্ত হবে? (Insert After)';
                } else if (val === 'before') {
                    group.style.display = 'block';
                    if (label) label.innerText = 'কোন আইটেমের আগে যুক্ত হবে? (Insert Before)';
                } else {
                    group.style.display = 'none';
                }
            }

            function openAdminAddFeeModal() {
                document.getElementById('aaf_particular_name').value = '';
                document.getElementById('aaf_amount').value = '';
                document.getElementById('aaf_remarks').value = '';
                const posSelect = document.getElementById('aaf_position');
                if (posSelect) posSelect.value = 'at_bottom';
                onAdminAddFeePositionChange('at_bottom');
                const modal = document.getElementById('adminAddFeeModal');
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                    setTimeout(() => {
                        const input = document.getElementById('aaf_particular_name');
                        if (input) input.focus();
                    }, 100);
                }
            }

            function closeAdminAddFeeModal() {
                const modal = document.getElementById('adminAddFeeModal');
                if (modal) modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            function setFeeNamePreset(name) {
                const input = document.getElementById('aaf_particular_name');
                if (input) {
                    input.value = name;
                    const amtInput = document.getElementById('aaf_amount');
                    if (amtInput) amtInput.focus();
                }
            }

            function submitAdminAddFee(e) {
                e.preventDefault();
                const btn = document.getElementById('aaf_submit_btn');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> যোগ হচ্ছে...';

                const invoiceId = document.getElementById('aaf_invoice_id').value;
                const studentId = document.getElementById('aaf_student_id').value;
                const semesterId = document.getElementById('aaf_semester_id').value;
                const pName = document.getElementById('aaf_particular_name').value;
                const amount = document.getElementById('aaf_amount').value;
                const remarks = document.getElementById('aaf_remarks').value;

                const posEl = document.getElementById('aaf_position');
                const position = posEl ? posEl.value : 'at_bottom';
                const relEl = document.getElementById('aaf_relative_to');
                const relativeTo = (position === 'after' || position === 'before') && relEl ? relEl.value : null;

                fetch("{{ route('student.fees.particular.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        student_id: studentId,
                        semester_id: semesterId,
                        particular_name: pName,
                        amount: amount,
                        remarks: remarks,
                        position: position,
                        relative_to: relativeTo
                    })
                })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (data.success) {
                        closeAdminAddFeeModal();
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || 'ফি যোগ করতে ত্রুটি হয়েছে।');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
                });
            }

            // Global alias for goToStep2
            window.goToStep2 = function() {
                if (typeof goToStep2Payment === 'function') {
                    goToStep2Payment();
                }
            };

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

                const posWrapper = document.getElementById('ape_position_wrapper');
                const isPosVisible = posWrapper && posWrapper.style.display !== 'none';
                const posEl = document.getElementById('ape_position');
                const position = (isPosVisible && posEl) ? posEl.value : null;
                const relEl = document.getElementById('ape_relative_to');
                const relativeTo = (isPosVisible && (position === 'after' || position === 'before') && relEl) ? relEl.value : null;

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
                        remarks: remarks,
                        position: position,
                        relative_to: relativeTo
                    })
                })
                    .then(r => r.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (data.success) {
                            if (position) {
                                alert(data.message);
                                location.reload();
                                return;
                            }
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

            function confirmStudentRevertPayment(invoiceId, particularName, amount) {
                if (!invoiceId) {
                    alert('ইনভয়েস আইডি পাওয়া যায়নি।');
                    return;
                }
                const reason = prompt('আপনি কি এই বিলটির পরিশোধিত অবস্থা বাতিল করে পুনরায় বকেয়া/আনপেইড তালিকায় ফেরত আনতে চান?\n\nবাতিলের কারণ/মন্তব্য লিখুন (ঐচ্ছিক):', 'এডমিন কর্তৃক আনপেইড করা হলো');
                if (reason === null) return;

                fetch("{{ route('student.fees.particular.revert_payment') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        particular_name: particularName,
                        reason: reason,
                        paid_amount_hint: amount
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'পেমেন্ট বাতিল করে সফলভাবে আনপেইড করা হয়েছে।');
                        location.reload();
                    } else {
                        alert(data.message || 'ব্যর্থ হয়েছে।');
                    }
                })
                .catch(err => {
                    alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
                });
            }

            function confirmStudentDeleteAddedFee(invoiceId, particularName) {
                if (!invoiceId) {
                    alert('ইনভয়েস আইডি পাওয়া যায়নি।');
                    return;
                }
                if (!confirm('আপনি কি নিশ্চিত যে "' + particularName + '" অতিরিক্ত ফিটি সম্পূর্ণ ডিলিট করতে চান?')) {
                    return;
                }

                fetch("{{ route('student.fees.particular.delete') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        invoice_id: invoiceId,
                        particular_name: particularName
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'ফি সফলভাবে মুছে ফেলা হয়েছে।');
                        location.reload();
                    } else {
                        alert(data.message || 'ফি মুছে ফেলা সম্ভব হয়নি।');
                    }
                })
                .catch(err => {
                    alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
                });
            }

            // Click outside backdrop to close active modal
            window.addEventListener('click', function (e) {
                const payModal = document.getElementById('payInvoiceModal');
                if (payModal && payModal.style.display !== 'none' && e.target === payModal) {
                    closePayModal();
                }
                const editModal = document.getElementById('adminParticularEditModal');
                if (editModal && editModal.style.display !== 'none' && e.target === editModal) {
                    closeAdminParticularEditModal();
                }
                const addFeeModal = document.getElementById('adminAddFeeModal');
                if (addFeeModal && addFeeModal.style.display !== 'none' && e.target === addFeeModal) {
                    closeAdminAddFeeModal();
                }
            });

            // Client-side validation on payForm submission
            const payFormEl = document.getElementById('payForm');
            if (payFormEl) {
                payFormEl.addEventListener('submit', function (e) {
                    if (isManualModeActive) {
                        const method = document.getElementById('manualMethodSelect').value;
                        const sender = (document.getElementById('manualSenderInput').value || '').trim();
                        const trx = (document.getElementById('manualTrxInput').value || '').trim();

                        if (['BKASH_MANUAL', 'NAGAD_MANUAL', 'ROCKET_MANUAL'].includes(method)) {
                            if (!sender) {
                                e.preventDefault();
                                alert('অনুগ্রহ করে আপনার প্রেরক মোবাইল নম্বর প্রদান করুন।');
                                document.getElementById('manualSenderInput').focus();
                                return false;
                            }
                            if (!/^01[3-9]\d{8}$/.test(sender)) {
                                e.preventDefault();
                                alert('অনুগ্রহ করে সঠিক ১১ ডিজিটের বাংলাদেশী মোবাইল নম্বর প্রদান করুন (যেমন: 01712345678)।');
                                document.getElementById('manualSenderInput').focus();
                                return false;
                            }
                            if (!trx) {
                                e.preventDefault();
                                alert('অনুগ্রহ করে ১০ ডিজিটের ট্রানজেকশন আইডি (TrxID) প্রদান করুন।');
                                document.getElementById('manualTrxInput').focus();
                                return false;
                            }
                            if (trx.length !== 10 || !/^[A-Za-z0-9]{10}$/.test(trx)) {
                                e.preventDefault();
                                alert('ট্রানজেকশন আইডি অবশ্যই সঠিক ১০ ডিজিট/অক্ষরের হতে হবে (যেমন: 8N7A6B5C4D)। বর্তমান দৈর্ঘ্য: ' + trx.length);
                                document.getElementById('manualTrxInput').focus();
                                return false;
                            }
                        } else if (method === 'BANK_TRANSFER') {
                            if (!sender) {
                                e.preventDefault();
                                alert('অনুগ্রহ করে ডিপোজিটর নাম / একাউন্ট তথ্য লিখুন।');
                                document.getElementById('manualSenderInput').focus();
                                return false;
                            }
                            if (!trx) {
                                e.preventDefault();
                                alert('অনুগ্রহ করে ব্যাংক স্লিপ নম্বর / ডিপোজিট রেফারেন্স প্রদান করুন।');
                                document.getElementById('manualTrxInput').focus();
                                return false;
                            }
                        }
                    }
                });
            }

            // Escape key closes any active modal
            window.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    closePayModal();
                    closeAdminParticularEditModal();
                    closeAdminAddFeeModal();
                }
            });
        </script>
    @endpush
</x-student-layout>