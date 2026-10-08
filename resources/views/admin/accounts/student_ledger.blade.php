<x-admin-layout>
    <x-slot name="title">শিক্ষার্থী একাউন্ট লেজার — {{ $student->name }}</x-slot>

    <style>
        .sl-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px; font-family: 'Kalpurush', sans-serif; }
        .sl-title { display: flex; align-items: center; gap: 12px; font-size: 22px; font-weight: 700; color: #1e293b; }
        .sl-title i { width: 42px; height: 42px; border-radius: 50%; background: #ecfdf5; color: #047857; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; }
        .sl-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px; font-family: 'Kalpurush', sans-serif; }
        .sl-kpi-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .sl-kpi-label { font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 6px; }
        .sl-kpi-val { font-size: 26px; font-weight: 800; line-height: 1; }
        .table-sl th { background: #f8fafc; font-size: 12px; font-weight: 700; color: #475569; padding: 12px 14px; border-bottom: 1px solid #e2e8f0; font-family: 'Kalpurush', sans-serif; }
        .table-sl td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; font-family: 'Kalpurush', sans-serif; }
        .extra-preset-chip { padding: 5px 12px; border-radius: 20px; border: 1.5px solid #cbd5e1; background: #fff; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; font-family: 'Kalpurush', sans-serif; }
        .extra-preset-chip:hover { border-color: #047857; background: #ecfdf5; color: #047857; }

        /* Modal Overlay System */
        .modal-overlay {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: 100% !important; height: 100% !important;
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            z-index: 99999 !important;
            display: none !important;
            align-items: flex-start !important;
            justify-content: center !important;
            padding: 30px 15px !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            box-sizing: border-box !important;
        }
        .modal-overlay.open,
        .modal-overlay.active,
        .modal-overlay.show {
            display: flex !important;
        }
        .modal-dialog {
            width: 100%;
            margin: 0 auto !important;
            max-height: calc(100vh - 60px) !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            box-sizing: border-box !important;
        }
        .modal-content {
            background: #fff;
            border-radius: 12px;
            overflow: visible;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #cbd5e1;
        }
        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>

    {{-- Page Header --}}
    <div class="sl-header">
        <div>
            <div style="font-size:12px;color:#64748b;margin-bottom:4px">
                <a href="{{ route('admin.students.index') }}" style="color:#047857;text-decoration:none">← শিক্ষার্থী তালিকা</a> /
                <a href="{{ route('admin.students.show', $student) }}" style="color:#047857;text-decoration:none">{{ $student->name }}</a> /
                <span>একাউন্ট লেজার</span>
            </div>
            <div class="sl-title">
                <i class="fa-solid fa-wallet"></i>
                <div>
                    <div>শিক্ষার্থী একাউন্ট ও ফি লেজার (Accounts Ledger)</div>
                    <div style="font-size:13px;color:#64748b;font-weight:400">
                        শিক্ষার্থী: <strong>{{ $student->name }}</strong> |
                        রোল/আইডি: <strong style="color:#047857">{{ str_replace('-', '', $student->student_code ?? 'অনির্ধারিত') }}</strong> |
                        মোবাইল: {{ $student->phone ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('admin.students.impersonate', $student) }}" class="btn btn-outline" style="color:#047857;border-color:#10b981;font-weight:700">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> শিক্ষার্থী হিসেবে লগইন
            </a>

            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline" style="font-family:'Kalpurush',sans-serif">
                প্রোফাইল দেখুন →
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:20px;font-family:'Kalpurush',sans-serif;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:20px;font-family:'Kalpurush',sans-serif;background:#fef2f2;border:1px solid #fecaca;color:#991b1b">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="sl-kpi-grid">
        <div class="sl-kpi-card" style="border-left:4px solid #2563eb">
            <div class="sl-kpi-label">মোট ধার্যকৃত ফি (Total Billed)</div>
            <div class="sl-kpi-val" style="color:#1e40af">৳{{ number_format($totalBilled, 2) }}</div>
        </div>
        <div class="sl-kpi-card" style="border-left:4px solid #10b981">
            <div class="sl-kpi-label">মোট আদায়কৃত / পরিশোধিত (Paid)</div>
            <div class="sl-kpi-val" style="color:#047857">৳{{ number_format($totalPaid, 2) }}</div>
        </div>
        <div class="sl-kpi-card" style="border-left:4px solid #ef4444">
            <div class="sl-kpi-label">সর্বমোট বকেয়া (Total Due)</div>
            <div class="sl-kpi-val" style="color:#b91c1c">৳{{ number_format($totalDue, 2) }}</div>
        </div>
        <div class="sl-kpi-card" style="border-left:4px solid #8b5cf6">
            <div class="sl-kpi-label">ইনভয়েস সংখ্যা</div>
            <div class="sl-kpi-val" style="color:#6d28d9">{{ $invoices->count() }}টি</div>
        </div>
    </div>

    {{-- ── 1. Month-wise Fee Schedule & Collection (মাসভিত্তিক ফি বিবরণ ও সরাসরি জমা) ── --}}
    <div class="card" style="margin-bottom:26px;border-radius:12px;overflow:hidden;border:1px solid #cbd5e1;box-shadow:0 2px 6px rgba(0,0,0,0.04);font-family:'Kalpurush',sans-serif">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;gap:10px">
            <div>
                <span class="card-title" style="font-family:'Kalpurush',sans-serif;font-size:16px;font-weight:700;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-calendar-days" style="color:#047857;font-size:18px"></i>
                    <span>মাসভিত্তিক ফি ও বেতন হিসাব (Month-wise Fee Schedule & Collection)</span>
                </span>
                <span style="font-size:12px;color:#64748b;margin-left:26px;display:block">
                    প্রতিটি মাসের বেতন, ভর্তি ও পরীক্ষার ফি বিবরণ, একক বা একাধিক মাসের ফি সরাসরি জমা এবং এডিট
                </span>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <button type="button" class="btn btn-sm" onclick="openAdminAddFeeModal()"
                    style="font-family:'Kalpurush',sans-serif;background:#059669;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-plus-circle"></i> + নতুন ফি যোগ করুন
                </button>
            </div>
        </div>

        <div class="card-body" style="padding:20px;background:#f8fafc">
            {{-- Semester Selector --}}
            <div style="display:flex;justify-content:center;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
                <label style="font-weight:700;color:#1e293b;font-size:14px">Check Due For:</label>
                <select id="adminCheckDueSemesterSelect"
                    onchange="location.href='{{ route('admin.students.accounts', $student) }}?course_id={{ $course?->id }}&semester_id=' + this.value"
                    style="padding:6px 18px;border:1.5px solid #10b981;border-radius:6px;font-size:14px;font-weight:700;color:#0f172a;background:#fff;outline:none;cursor:pointer;font-family:'Kalpurush',sans-serif">
                    @foreach($semesterDropdownOptions as $sOpt)
                        <option value="{{ $sOpt['id'] }}" {{ (string)$selectedSemesterId === (string)$sOpt['id'] ? 'selected' : '' }}>
                            {{ $sOpt['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Prior Due Guard Warning --}}
            @if($hasPriorSemesterDue && ($selectedSemester?->sequence_no > 1))
                <div style="background:#fff1f2;border:1.5px solid #fecdd3;border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:gap;gap:10px">
                    <div style="display:flex;align-items:center;gap:10px;color:#9f1239;font-size:13px;font-weight:600">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size:16px;color:#e11d48"></i>
                        <span>
                            <strong>পূর্বের সেমিস্টারের বকেয়া অপরিশোধিত:</strong>
                            পূর্বের সেমিস্টারের বকেয়া ({{ $priorDueSemesterName }} — ৳{{ number_format($priorDueAmount, 2) }}) এখনো বকেয়া রয়েছে।
                        </span>
                    </div>
                    @if($priorDueSemesterId)
                        <a href="{{ route('admin.students.accounts', ['student' => $student, 'semester_id' => $priorDueSemesterId]) }}"
                            style="background:#be123c;color:#fff;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-arrow-left"></i> পূর্বের সেমিস্টার দেখুন
                        </a>
                    @endif
                </div>
            @endif

            {{-- Status Filter Buttons & Table Tools --}}
            <div style="max-width:850px;margin:0 auto 12px auto;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span style="font-size:12.5px;font-weight:700;color:#64748b;margin-right:4px">স্ট্যাটাস ফিল্টার:</span>
                    <button type="button" class="admin-step1-filter-btn" id="adminStep1Filter_all"
                        onclick="filterAdminStep1Table('all', this)"
                        style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #2563eb;background:#2563eb;color:#fff;transition:all .15s">
                        <i class="fa-solid fa-list-check"></i> সকল আইটেম (All)
                    </button>
                    <button type="button" class="admin-step1-filter-btn" id="adminStep1Filter_due"
                        onclick="filterAdminStep1Table('due', this)"
                        style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #cbd5e1;background:#fff;color:#be123c;transition:all .15s">
                        <i class="fa-solid fa-clock"></i> বকেয়া (Dues Only)
                    </button>
                    <button type="button" class="admin-step1-filter-btn" id="adminStep1Filter_paid"
                        onclick="filterAdminStep1Table('paid', this)"
                        style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #cbd5e1;background:#fff;color:#15803d;transition:all .15s">
                        <i class="fa-solid fa-circle-check"></i> পরিশোধিত (Paid Only)
                    </button>
                </div>

            </div>

            {{-- Step 1 Particulars Table Container --}}
            <div style="max-width:850px;margin:0 auto;border:1px solid #cbd5e1;border-radius:10px;overflow:hidden;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.05);max-height:480px;overflow-y:auto">
                <table style="width:100%;border-collapse:collapse;font-size:13.5px">
                    <thead style="position:sticky;top:0;z-index:2;background:#f8fafc">
                        <tr style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0">
                            <th style="padding:11px 14px;width:55px;text-align:center;font-weight:700;color:#334155">#SL</th>
                            <th style="padding:11px 14px;font-weight:700;color:#334155;text-align:left">PARTICULAR NAME</th>
                            <th style="padding:11px 14px;width:180px;text-align:center;font-weight:700;color:#334155">DUES</th>
                            <th style="padding:11px 14px;width:70px;text-align:center;font-weight:700;color:#334155">PAY</th>
                            <th style="padding:11px 14px;width:170px;text-align:center;font-weight:700;color:#334155">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($step1Particulars as $p)
                            @if(($p['name'] ?? '') === '_history')
                                @continue
                            @endif
                            <tr style="border-bottom:1px solid #f1f5f9" data-admin-step1-status="{{ $p['is_paid'] ? 'paid' : 'due' }}">
                                <td style="padding:10px 14px;text-align:center;color:#64748b;font-weight:600">
                                    {{ $p['sl'] }}
                                </td>
                                <td style="padding:10px 14px;font-weight:600;color:#1e293b">
                                    <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap">
                                        <span>{{ $p['name'] }}</span>
                                        @if(!empty($p['category']) && $p['category'] === 'FINE')
                                            <span style="font-size:10.5px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'বিলম্ব / জরিমানা' }}">
                                                {{ (str_contains($p['name'], 'এক্টিভিশন') || str_contains(mb_strtolower($p['name']), 'activation')) ? 'এক্টিভিশন ফি' : 'জরিমানা' }}
                                            </span>
                                        @elseif(!empty($p['category']) && $p['category'] === 'DOCUMENT')
                                            <span style="font-size:10.5px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'ডকুমেন্ট ফি' }}">
                                                ডকুমেন্ট ফি
                                            </span>
                                        @elseif(!empty($p['category']) && $p['category'] === 'EXTRA')
                                            <span style="font-size:10.5px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'অতিরিক্ত ফি' }}">
                                                অতিরিক্ত ফি
                                            </span>
                                        @elseif(!empty($p['is_added']))
                                            <span style="font-size:10.5px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:1px 6px;border-radius:8px;font-weight:700" title="{{ $p['custom_remarks'] ?? 'অ্যাডমিন কর্তৃক যুক্ত ফি' }}">
                                                {{ (str_contains($p['name'], 'এক্টিভিশন') || str_contains(mb_strtolower($p['name']), 'activation')) ? 'এক্টিভিশন ফি' : 'নতুন যুক্ত' }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- History & Metadata Subtitle --}}
                                    @if($p['is_paid'] && (!empty($p['paid_by_name']) || !empty($p['paid_at'])))
                                        <div style="font-size:11px;color:#15803d;margin-top:3px;font-weight:500;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                                            <i class="fa-solid fa-circle-check" style="font-size:10px"></i>
                                            <span>পরিশোধকারী: <strong>{{ $p['paid_by_name'] ?? 'অনলাইন / অ্যাডমিন' }}</strong></span>
                                            @if(!empty($p['paid_at']))
                                                <span style="color:#64748b">• {{ $p['paid_at'] }}</span>
                                            @endif
                                            @if(!empty($p['payment_method']))
                                                <span style="background:#dcfce7;color:#166534;padding:0 5px;border-radius:4px;font-size:10px;font-weight:700">({{ $p['payment_method'] }})</span>
                                            @endif
                                        </div>
                                    @elseif(!empty($p['reverted_by_name']))
                                        <div style="font-size:11px;color:#b91c1c;margin-top:3px;font-weight:500;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
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
                                        <div style="font-size:11px;color:#0369a1;margin-top:3px;font-weight:500;display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                                            <i class="fa-solid fa-plus-circle" style="font-size:10px"></i>
                                            <span>যুক্ত করেছেন: <strong>{{ $p['created_by_name'] ?? 'অ্যাডমিন' }}</strong></span>
                                            @if(!empty($p['added_at']))
                                                <span style="color:#64748b">• {{ $p['added_at'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:10px 14px;text-align:center" id="adminPartDueCell_{{ $p['sl'] }}">
                                    @if($p['is_paid'])
                                        <div style="display:inline-flex;align-items:center;gap:6px">
                                            <span style="color:#16a34a;font-weight:700">Paid ({{ number_format($p['amount'], 0) }})</span>
                                            <button type="button"
                                                onclick="openAdminPartEditModal('{{ $p['invoice_id'] ?? $selectedSemesterInvoice?->id }}', '{{ addslashes($p['name']) }}', 0, {{ $p['sl'] }}, {{ !empty($p['is_added']) ? 'true' : 'false' }}, '{{ $p['position'] ?? 'at_bottom' }}', '{{ addslashes($p['relative_to'] ?? '') }}')"
                                                title="টাকার পরিমাণ এডিট করুন"
                                                style="background:#f8fafc;border:1px solid #cbd5e1;color:#64748b;border-radius:4px;padding:2px 6px;font-size:11px;font-weight:700;cursor:pointer">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div style="display:inline-flex;align-items:center;gap:6px">
                                            <span id="adminPartDueVal_{{ $p['sl'] }}" style="font-weight:700;color:#0f172a">{{ number_format($p['due'], 0) }}</span>
                                            @if(($p['paid_amt'] ?? 0) > 0)
                                                <span style="font-size:11px;color:#059669;background:#ecfdf5;padding:1px 6px;border-radius:4px;border:1px solid #a7f3d0;font-weight:600" title="ইতোমধ্যে পরিশোধিত: ৳{{ number_format($p['paid_amt'], 0) }}">
                                                    (পেইড: {{ number_format($p['paid_amt'], 0) }})
                                                </span>
                                            @endif
                                            <button type="button"
                                                onclick="openAdminPartEditModal('{{ $p['invoice_id'] ?? $selectedSemesterInvoice?->id }}', '{{ addslashes($p['name']) }}', {{ $p['due'] }}, {{ $p['sl'] }}, {{ !empty($p['is_added']) ? 'true' : 'false' }}, '{{ $p['position'] ?? 'at_bottom' }}', '{{ addslashes($p['relative_to'] ?? '') }}')"
                                                title="টাকার পরিমাণ এডিট বা সমন্বয় করুন"
                                                style="background:#eff6ff;border:1px solid #93c5fd;color:#1d4ed8;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:3px">
                                                <i class="fa-solid fa-pencil" style="font-size:10px"></i> এডিট
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:10px 14px;text-align:center">
                                    @if(!$p['is_paid'])
                                        <input type="checkbox" class="admin-step1-chk"
                                             data-name="{{ $p['name'] }}"
                                            data-amount="{{ $p['due'] }}"
                                            data-total-amount="{{ $p['amount'] }}"
                                            data-invoice-id="{{ $p['invoice_id'] ?? ($selectedSemesterInvoice?->id ?? '') }}"
                                            data-invoice-no="{{ $p['invoice_no'] ?? ($selectedSemesterInvoice?->invoice_no ?? '') }}"
                                            onchange="onAdminStep1CheckboxChange(this)"
                                            style="width:17px;height:17px;cursor:pointer;accent-color:#16a34a"
                                            title="ফি আইটেমটি নির্বাচন করুন">
                                    @endif
                                </td>
                                <td style="padding:10px 14px;text-align:center">
                                    @if(!$p['is_paid'])
                                        <div style="display:inline-flex;align-items:center;gap:6px;justify-content:center">
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="openAdminSingleCollectModal('{{ $p['invoice_id'] ?? ($selectedSemesterInvoice?->id ?? '') }}', '{{ addslashes($p['name']) }}', {{ $p['due'] }}, {{ $p['amount'] }})"
                                                style="padding:3px 10px;font-size:11.5px;font-family:'Kalpurush',sans-serif;background:#059669;color:#fff;border-radius:5px">
                                                <i class="fa-solid fa-money-bill-wave"></i> জমা দিন
                                            </button>
                                            @if(!empty($p['is_added']))
                                                <button type="button" class="btn btn-sm"
                                                    onclick="confirmDeleteAddedFee('{{ $p['invoice_id'] ?? ($selectedSemesterInvoice?->id ?? '') }}', '{{ addslashes($p['name']) }}')"
                                                    title="এই অতিরিক্ত ফি সম্পূর্ণ মুছে ফেলুন"
                                                    style="padding:3px 8px;font-size:11.5px;font-family:'Kalpurush',sans-serif;background:#fff;border:1px solid #ef4444;color:#ef4444;border-radius:5px;cursor:pointer">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <div style="display:inline-flex;align-items:center;gap:6px;justify-content:center">
                                            <span style="color:#16a34a;font-size:12px;font-weight:700">
                                                <i class="fa-solid fa-circle-check"></i> পেইড
                                            </span>
                                            <button type="button" class="btn btn-sm"
                                                onclick="confirmRevertPayment('{{ $p['invoice_id'] ?? ($selectedSemesterInvoice?->id ?? '') }}', '{{ addslashes($p['name']) }}', {{ $p['paid_amt'] ?? $p['amount'] }})"
                                                title="পেমেন্ট বাতিল করে পুনরায় আনপেইড/বকেয়া তালিকায় যুক্ত করুন"
                                                style="padding:2px 7px;font-size:11px;font-family:'Kalpurush',sans-serif;background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;border-radius:4px;cursor:pointer;display:inline-flex;align-items:center;gap:3px;font-weight:700">
                                                <i class="fa-solid fa-rotate-left"></i> আনপেইড করুন
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:20px;color:#64748b">কোন ফি আইটেম পাওয়া যায়নি।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Multi-select Action Bar --}}
            <div id="adminStep1MultiBar" style="display:none;max-width:850px;margin:16px auto 0 auto;background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:8px;padding:12px 18px;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                <div style="font-weight:700;color:#065f46;font-size:14px">
                    <i class="fa-solid fa-check-circle" style="color:#059669;margin-right:6px"></i>
                    নির্বাচিত <span id="adminStep1Count">0</span>টি ফি আইটেম — সর্বমোট: ৳<span id="adminStep1Total">0</span>
                </div>
                <div>
                    <button type="button" class="btn btn-success" onclick="openAdminMultiCollectModal()"
                        style="background:#059669;color:#fff;border:none;padding:7px 18px;border-radius:6px;font-weight:700;font-size:13px;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-hand-holding-dollar"></i> নির্বাচিত ফি জমা নিন
                    </button>
                </div>
            </div>

            {{-- ── 📜 বিল ও পেমেন্ট হিস্ট্রি (Billing & Payment History Log) ── --}}
            <div style="max-width:850px;margin:20px auto 0 auto;background:#fff;border:1px solid #cbd5e1;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
                <div style="background:#f8fafc;padding:10px 16px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
                    <span style="font-weight:700;color:#1e293b;font-size:13.5px;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-clock-rotate-left" style="color:#0284c7"></i> বিল ও পেমেন্ট হিস্ট্রি (History Log)
                    </span>
                    <span style="font-size:11.5px;color:#64748b">
                        কে পে করেছে, কে আনপেইড করেছে বা ফি যুক্ত করেছে তার পূর্ণাঙ্গ বিবরণ
                    </span>
                </div>
                <div style="max-height:220px;overflow-y:auto">
                    <table style="width:100%;border-collapse:collapse;font-size:12px">
                        <thead style="position:sticky;top:0;background:#f1f5f9;border-bottom:1px solid #e2e8f0">
                            <tr>
                                <th style="padding:7px 12px;text-align:left;color:#475569">তারিখ ও সময়</th>
                                <th style="padding:7px 12px;text-align:left;color:#475569">ফি / বিল বিবরণ</th>
                                <th style="padding:7px 12px;text-align:center;color:#475569">কার্যক্রম</th>
                                <th style="padding:7px 12px;text-align:right;color:#475569">টাকা</th>
                                <th style="padding:7px 12px;text-align:left;color:#475569">ব্যবহারকারী / অ্যাডমিন</th>
                                <th style="padding:7px 12px;text-align:left;color:#475569">মন্তব্য / মাধ্যম</th>
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
                                    <td style="padding:7px 12px;color:#64748b;white-space:nowrap">{{ $hLog['date_time'] ?? '—' }}</td>
                                    <td style="padding:7px 12px;font-weight:600;color:#1e293b">{{ $hLog['particular'] ?? '—' }}</td>
                                    <td style="padding:7px 12px;text-align:center">
                                        <span style="font-size:10.5px;padding:2px 7px;border-radius:10px;font-weight:700;background:{{ $actBadge['bg'] }};color:{{ $actBadge['color'] }}">
                                            {{ $actBadge['text'] }}
                                        </span>
                                    </td>
                                    <td style="padding:7px 12px;text-align:right;font-weight:700;color:#0f172a">
                                        {{ isset($hLog['amount']) ? '৳' . number_format($hLog['amount'], 2) : '—' }}
                                    </td>
                                    <td style="padding:7px 12px;color:#334155;font-weight:600">
                                        {{ $hLog['user_name'] ?? 'অ্যাডমিন' }}
                                    </td>
                                    <td style="padding:7px 12px;color:#64748b">
                                        {{ $hLog['remarks'] ?? ($hLog['method'] ?? '—') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding:15px;text-align:center;color:#94a3b8">এখনও কোনো হিস্ট্রি লগ সংরক্ষিত নেই।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Invoices & Fees List (ইনভয়েস ও ফি ধার্য তালিকা) ── --}}
    <div class="card" style="margin-bottom:26px;border-radius:12px;overflow:hidden">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border-bottom:1px solid #e2e8f0;flex-wrap:wrap;gap:10px">
            <div>
                <span class="card-title" style="font-family:'Kalpurush',sans-serif;font-size:16px;font-weight:700">
                    <i class="fa-solid fa-file-invoice-dollar" style="color:#047857;margin-right:6px"></i> ইনভয়েস ও ফি ধার্য তালিকা (Invoices & Fees)
                </span>
                <span style="font-size:12px;color:#64748b;margin-left:8px">কোন ফি কেন ধার্য করা হয়েছে, প্রদেয়, পেইড ও বকেয়া হিসাব</span>
            </div>
            <div style="display:flex;gap:8px">
                <button type="button" class="btn btn-sm btn-outline" onclick="openExtraFeeModal()" style="font-family:'Kalpurush',sans-serif;border-color:#10b981;color:#047857;font-weight:700">
                    <i class="fa-solid fa-plus"></i> এক্সট্রা ফি যোগ করুন
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="openModal('addCustomFeeModal')" style="font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-plus"></i> নতুন ফি / ইনভয়েস
                </button>
            </div>
        </div>
        {{-- Calculation Explanation Banner --}}
        <div style="background:#f0fdf4;border-bottom:1px solid #bbf7d0;padding:11px 20px;font-family:'Kalpurush',sans-serif">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                <div style="display:flex;align-items:center;gap:8px;font-weight:700;color:#166534;font-size:13px">
                    <i class="fa-solid fa-calculator" style="color:#059669;font-size:15px"></i>
                    <span>হিসাব পদ্ধতি:</span>
                    <span style="background:#dcfce7;border:1px solid #86efac;padding:2px 8px;border-radius:6px;font-weight:800;color:#047857">
                        প্রদেয় = মোট ফি - ছাড়
                    </span>
                    <span style="color:#64748b;font-weight:400">|</span>
                    <span style="background:#fee2e2;border:1px solid #fca5a5;padding:2px 8px;border-radius:6px;font-weight:800;color:#991b1b">
                        বকেয়া = প্রদেয় - পরিশোধিত
                    </span>
                </div>
                <div style="font-size:12px;color:#15803d">
                    <i class="fa-solid fa-circle-info"></i> প্রতি মাসের ফি আদায় বা এডিটের সাথে সাথে ইনভয়েস হিসাব স্বয়ংক্রিয়ভাবে সংরক্ষিত
                </div>
            </div>
        </div>
        <div class="table-wrapper" style="overflow-x:auto;max-height:480px;overflow-y:auto">
            <table class="table-sl" style="width:100%;border-collapse:collapse">
                <thead style="position:sticky;top:0;z-index:2;background:#f8fafc">
                    <tr>
                        <th>ইনভয়েস নং</th>
                        <th>বিবরণ ও ফি ধার্যের কারণ</th>
                        <th>ক্যাটাগরি</th>
                        <th style="text-align:right">মোট ফি</th>
                        <th style="text-align:right">ছাড়</th>
                        <th style="text-align:right">প্রদেয়</th>
                        <th style="text-align:right">পরিশোধিত</th>
                        <th style="text-align:right">বকেয়া</th>
                        <th>শেষ তারিখ</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:center">অ্যাকশন / ম্যানেজ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td style="font-family:monospace;font-weight:700;color:#1e40af;white-space:nowrap">{{ $inv->invoice_no }}</td>
                        <td>
                            <strong style="color:#0f172a;font-size:13.5px">{{ $inv->title }}</strong>
                            @if($inv->notes)
                                <div style="font-size:12px;color:#047857;background:#ecfdf5;padding:2px 8px;border-radius:6px;margin-top:3px;display:inline-block;border:1px solid #a7f3d0">
                                    <i class="fa-solid fa-circle-info" style="font-size:11px"></i> কারণ: {{ $inv->notes }}
                                </div>
                            @endif
                            @if(!empty($inv->custom_particulars))
                                <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:5px">
                                    @foreach($inv->custom_particulars as $cpName => $cpVal)
                                        @if($cpName === '_history') @continue @endif
                                        <span style="font-size:10.5px;background:#f8fafc;border:1px solid #e2e8f0;padding:1px 6px;border-radius:4px;color:#334155" title="ফি: ৳{{ number_format($cpVal['amount'] ?? 0, 0) }} | বকেয়া: ৳{{ number_format($cpVal['due'] ?? 0, 0) }}">
                                            {{ $cpName }}: ৳{{ number_format($cpVal['due'] ?? ($cpVal['amount'] ?? 0), 0) }} {{ ($cpVal['due'] ?? 0) <= 0 ? '✓' : '' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if($inv->enrollment)
                                <div style="font-size:11px;color:#64748b;margin-top:2px">{{ $inv->enrollment->course->name ?? '' }} ({{ $inv->enrollment->batch->name ?? '' }})</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $catBadge = match($inv->category) {
                                    'EXTRA' => 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
                                    'FINE' => 'background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;',
                                    'SEMESTER' => 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;',
                                    'ADMISSION' => 'background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;',
                                    default => 'background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;'
                                };
                            @endphp
                            <span style="font-size:11px;padding:3px 8px;border-radius:10px;font-weight:700;{{ $catBadge }}">
                                {{ $inv->category }}
                            </span>
                        </td>
                        <td style="text-align:right">৳{{ number_format($inv->amount, 2) }}</td>
                        <td style="text-align:right;color:#64748b">৳{{ number_format($inv->discount, 2) }}</td>
                        <td style="text-align:right;font-weight:700">৳{{ number_format($inv->payable_amount, 2) }}</td>
                        <td style="text-align:right;color:#047857;font-weight:700">৳{{ number_format($inv->paid_amount, 2) }}</td>
                        <td style="text-align:right;color:{{ $inv->due_amount > 0 ? '#dc2626' : '#64748b' }};font-weight:700">
                            ৳{{ number_format($inv->due_amount, 2) }}
                        </td>
                        <td style="font-size:12px;color:#475569;white-space:nowrap">
                            {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}
                        </td>
                        <td>
                            @php
                                $statusStyle = match($inv->status) {
                                    'PAID' => 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0',
                                    'PARTIAL' => 'background:#fef3c7;color:#92400e;border:1px solid #fde68a',
                                    'CANCELLED' => 'background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;text-decoration:line-through',
                                    default => 'background:#fee2e2;color:#991b1b;border:1px solid #fca5a5'
                                };
                            @endphp
                            <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:12px;white-space:nowrap;{{ $statusStyle }}">
                                {{ $inv->status }}
                            </span>
                        </td>
                        <td style="text-align:center;white-space:nowrap">
                            <div style="display:inline-flex;gap:5px;align-items:center">
                                {{-- Collect Payment Button --}}
                                @if($inv->due_amount > 0 && $inv->status !== 'CANCELLED')
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="openCollectModal('{{ $inv->id }}', '{{ $inv->invoice_no }}', '{{ $inv->due_amount }}', '{{ addslashes($inv->title) }}')"
                                            style="padding:3px 8px;font-size:11px;font-family:'Kalpurush',sans-serif;background:#059669;color:#fff" title="টাকা জমা নিন">
                                        <i class="fa-solid fa-money-bill-wave"></i> জমা
                                    </button>
                                @endif

                                {{-- Status Update Button --}}
                                <button type="button" class="btn btn-sm btn-outline" 
                                        onclick="openStatusModal('{{ $inv->id }}', '{{ $inv->invoice_no }}', '{{ $inv->status }}', '{{ $inv->due_amount }}')"
                                        style="padding:3px 8px;font-size:11px;color:#7c3aed;border-color:#c4b5fd" title="পেমেন্ট স্ট্যাটাস আপডেট করুন">
                                    <i class="fa-solid fa-sliders"></i> স্ট্যাটাস
                                </button>

                                {{-- Edit Button --}}
                                <button type="button" class="btn btn-sm btn-outline" 
                                        onclick="openEditModal('{{ $inv->id }}', '{{ addslashes($inv->title) }}', '{{ addslashes($inv->notes ?? '') }}', '{{ $inv->category }}', '{{ $inv->amount }}', '{{ $inv->discount }}', '{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') : '' }}')"
                                        style="padding:3px 8px;font-size:11px;color:#2563eb;border-color:#93c5fd" title="ইনভয়েস এডিট করুন (পরিমাণ কমানো/বাড়ানো)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                {{-- Delete / Void Button --}}
                                <form action="{{ route('admin.accounts.invoices.destroy', $inv) }}" method="POST" 
                                      onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ইনভয়েসটি ({{ $inv->invoice_no }}) মুছে ফেলতে চান? যদি এতে পেমেন্ট থাকে তবে এটি বাতিল (CANCELLED) হিসেবে গণ্য হবে।')" 
                                      style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="force_cancel" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline" style="padding:3px 8px;font-size:11px;color:#dc2626;border-color:#fca5a5" title="ইনভয়েস মুছুন / বাতিল করুন">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:24px;color:#94a3b8">
                            এই শিক্ষার্থীর জন্য কোনো ইনভয়েস পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payments History Table --}}
    <div class="card" style="border-radius:12px;overflow:hidden">
        <div class="card-header" style="padding:16px 20px;background:#fff;border-bottom:1px solid #e2e8f0">
            <span class="card-title" style="font-family:'Kalpurush',sans-serif;font-size:16px;font-weight:700">
                <i class="fa-solid fa-receipt" style="color:#047857;margin-right:6px"></i> পরিশোধের রসিদ ও লেনদেন ইতিহাস (Payments Log & Vouchers)
            </span>
        </div>
        <div class="table-wrapper" style="overflow-x:auto">
            <table class="table-sl" style="width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th>রসিদ নং (Receipt)</th>
                        <th>তারিখ ও সময়</th>
                        <th>ইনভয়েস নং</th>
                        <th>পেমেন্ট মেথড</th>
                        <th>বিকাশ / প্রেরক নম্বর</th>
                        <th>ট্রানজেকশন আইডি</th>
                        <th style="text-align:right">পরিশোধিত টাকা</th>
                        <th>গ্রহণকারী</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:center">রসিদ / ভাউচার</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                    <tr>
                        <td style="font-family:monospace;font-weight:700;color:#047857">{{ $pay->payment_no }}</td>
                        <td style="font-size:12px;color:#475569;white-space:nowrap">{{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y, h:i A') : '—' }}</td>
                        <td style="font-family:monospace;color:#1e40af">{{ $pay->invoice?->invoice_no ?? '—' }}</td>
                        <td>
                            <span style="font-size:11px;padding:2px 8px;border-radius:10px;background:#f1f5f9;color:#1e293b;font-weight:700">
                                {{ $pay->payment_method }}
                            </span>
                        </td>
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
                        <td style="text-align:right;font-weight:800;color:#047857">৳{{ number_format($pay->amount, 2) }}</td>
                        <td style="font-size:12px;color:#475569">{{ $pay->receivedBy?->name ?? 'Online / System' }}</td>
                        <td>
                            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;background:#dcfce7;color:#166534">
                                {{ $pay->status }}
                            </span>
                        </td>
                        <td style="text-align:center">
                            <a href="{{ route('admin.accounts.payments.receipt', $pay) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:2px 8px;font-size:11px">
                                <i class="fa-solid fa-print"></i> মানি রসিদ
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:24px;color:#94a3b8">
                            এখনও কোনো পেমেন্ট রেকর্ড নেই।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL: Add Custom Fee / Invoice (Regular Fee) --}}
    <div class="modal-overlay" id="addCustomFeeModal">
        <div class="modal-dialog" style="max-width:540px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form action="{{ route('admin.accounts.invoices.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="fa-solid fa-plus-circle" style="color:#047857"></i> নতুন ফি / ইনভয়েস ধার্য করুন</h3>
                        <button type="button" class="btn-close" onclick="closeModal('addCustomFeeModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        <div class="form-group">
                            <label style="font-weight:600">ফি এর খাত / শিরোনাম (Title) *</label>
                            <input type="text" name="title" class="form-control" placeholder="যেমন: সেমিস্টার ফি, ল্যাব ফি, পুনঃভর্তি ফি..." required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">ফি ধার্যের কারণ / উদ্দেশ্য (Reason / Notes)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="কেন এই ফি ধার্য করা হয়েছে তা পরিষ্কারভাবে লিখুন..."></textarea>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">ক্যাটাগরি (Category) *</label>
                            <select name="category" class="form-control" required>
                                <option value="SEMESTER">SEMESTER (সেমিস্টার ফি / মাসিক বেতন)</option>
                                <option value="ADMISSION">ADMISSION (ভর্তি ফি)</option>
                                <option value="RETAKE">RETAKE (রিটেক ফি)</option>
                                <option value="EXAM">EXAM (পরীক্ষা ফি)</option>
                                <option value="FINE">FINE (জরিমানা / বিলম্ব ফি)</option>
                                <option value="DOCUMENT">DOCUMENT (সনদ / ডকুমেন্ট ফি)</option>
                                <option value="EXTRA">EXTRA (অতিরিক্ত ফি / বিশেষ চার্জ)</option>
                                <option value="MANUAL">MANUAL (অন্যান্য ম্যানুয়াল ফি)</option>
                            </select>
                        </div>
                        <div class="grid-2" style="gap:12px">
                            <div class="form-group">
                                <label style="font-weight:600">মোট পরিমাণ (৳) *</label>
                                <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600">ছাড় / ডিসকাউন্ট (৳)</label>
                                <input type="number" step="0.01" min="0" name="discount" class="form-control" value="0.00">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">পরিশোধের শেষ তারিখ (Due Date)</label>
                            <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                        </div>

                        {{-- Optional Immediate Payment --}}
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-top:4px">
                            <label style="display:flex;align-items:center;gap:8px;font-weight:700;cursor:pointer;color:#047857;margin:0">
                                <input type="checkbox" name="record_payment" value="1" id="chk_record_payment" onchange="toggleImmediatePay(this.checked)" style="accent-color:#047857;width:16px;height:16px">
                                এখনই পেমেন্ট গ্রহণ করুন (Add with Payment)
                            </label>

                            <div id="immediatePayFields" style="display:none;margin-top:12px;flex-direction:column;gap:10px">
                                <div class="grid-2" style="gap:10px">
                                    <div>
                                        <label style="font-size:12px;font-weight:600">পেমেন্ট মেথড</label>
                                        <select name="payment_method" class="form-control" style="font-size:13px">
                                            <option value="CASH">CASH (কাউন্টার নগদ)</option>
                                            <option value="BKASH">BKASH (বিকাশ)</option>
                                            <option value="NAGAD">NAGAD (নগদ)</option>
                                            <option value="ROCKET">ROCKET (রকেট)</option>
                                            <option value="BANK_TRANSFER">BANK_TRANSFER (ব্যাংক)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:12px;font-weight:600">জমার পরিমাণ (৳)</label>
                                        <input type="number" step="0.01" min="1" name="paid_amount" class="form-control" placeholder="0.00" style="font-size:13px">
                                    </div>
                                </div>
                                <div class="grid-2" style="gap:10px">
                                    <div>
                                        <label style="font-size:12px;font-weight:600">বিকাশ / প্রেরক নম্বর</label>
                                        <input type="text" name="sender_number" class="form-control" placeholder="e.g. 017XXXXXXXX" style="font-size:13px">
                                    </div>
                                    <div>
                                        <label style="font-size:12px;font-weight:600">TrxID / রেফারেন্স নং</label>
                                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TRX123456" style="font-size:13px">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('addCustomFeeModal')">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> ইনভয়েস তৈরি করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Add Extra Fee (Special Presets) --}}
    <div class="modal-overlay" id="addExtraFeeModal">
        <div class="modal-dialog" style="max-width:540px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form action="{{ route('admin.accounts.invoices.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="category" value="EXTRA" id="extra_category_input">
                    <div class="modal-header" style="background:#ecfdf5;border-bottom:1px solid #a7f3d0">
                        <h3 class="modal-title" style="color:#065f46">
                            <i class="fa-solid fa-file-circle-plus" style="color:#047857"></i> অতিরিক্ত ফি ধার্য (Add Extra Fee)
                        </h3>
                        <button type="button" class="btn-close" onclick="closeModal('addExtraFeeModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        {{-- Quick Presets --}}
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#334155;margin-bottom:6px;display:block">
                                <i class="fa-solid fa-bolt" style="color:#f59e0b"></i> দ্রুত ফি প্রিসেট নির্বাচন করুন:
                            </label>
                            <div style="display:flex;flex-wrap:wrap;gap:8px">
                                <button type="button" class="extra-preset-chip" onclick="applyExtraPreset('বিলম্ব জরিমানা (Late Fine)', 100, 'নির্ধারিত তারিখের পর বিলম্বে ফি পরিশোধ করার জন্য জরিমানা', 'FINE')">
                                    + বিলম্ব জরিমানা (৳১০০)
                                </button>
                                <button type="button" class="extra-preset-chip" onclick="applyExtraPreset('পুনঃপরীক্ষা ফি (Re-exam Fee)', 500, 'পুনরায় পরীক্ষায় অংশগ্রহণের নির্ধারিত বোর্ড ফি', 'EXAM')">
                                    + পুনঃপরীক্ষা ফি (৳৫০০)
                                </button>
                                <button type="button" class="extra-preset-chip" onclick="applyExtraPreset('সনদ / ট্রান্সক্রিপ্ট উত্তোলন ফি', 300, 'সার্টিফিকেট বা মার্কশিট উত্তোলনের প্রাতিষ্ঠানিক প্রসেসিং ফি', 'DOCUMENT')">
                                    + সনদ / ট্রান্সক্রিপ্ট (৳৩০০)
                                </button>
                                <button type="button" class="extra-preset-chip" onclick="applyExtraPreset('আইডি কার্ড রিপ্লেসমেন্ট ফি', 200, 'হারিয়ে যাওয়া আইডি কার্ড পুনরায় ইস্যু ফি', 'EXTRA')">
                                    + আইডি কার্ড (৳২০০)
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-weight:600">অতিরিক্ত ফি এর শিরোনাম (Title) *</label>
                            <input type="text" id="extra_title" name="title" class="form-control" placeholder="যেমন: সনদ ফি, জরিমানা, বিশেষ চার্জ..." required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">কেন এই ফি ধার্য করা হয়েছে? (Reason / Purpose) *</label>
                            <textarea id="extra_notes" name="notes" class="form-control" rows="2" placeholder="ফি ধার্যের কারণ বিস্তারিত লিখুন..." required></textarea>
                        </div>
                        <div class="grid-2" style="gap:12px">
                            <div class="form-group">
                                <label style="font-weight:600">পরিমাণ (৳) *</label>
                                <input type="number" step="0.01" min="1" id="extra_amount" name="amount" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600">পরিশোধের শেষ তারিখ</label>
                                <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(5)->format('Y-m-d') }}">
                            </div>
                        </div>

                        {{-- Immediate Payment Checkbox --}}
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px">
                            <label style="display:flex;align-items:center;gap:8px;font-weight:700;cursor:pointer;color:#047857;margin:0">
                                <input type="checkbox" name="record_payment" value="1" onchange="toggleExtraImmediatePay(this.checked)" style="accent-color:#047857;width:16px;height:16px">
                                ফি ধার্যের সাথে সাথে পেমেন্ট জমা নিন (Instant Payment)
                            </label>
                            <div id="extraImmediatePayFields" style="display:none;margin-top:12px;flex-direction:column;gap:10px">
                                <div class="grid-2" style="gap:10px">
                                    <div>
                                        <label style="font-size:12px;font-weight:600">পেমেন্ট মেথড</label>
                                        <select name="payment_method" class="form-control" style="font-size:13px">
                                            <option value="CASH">CASH (নগদ ক্যাশ)</option>
                                            <option value="BKASH">BKASH (বিকাশ)</option>
                                            <option value="NAGAD">NAGAD (নগদ)</option>
                                            <option value="ROCKET">ROCKET (রকেট)</option>
                                            <option value="BANK_TRANSFER">BANK_TRANSFER (ব্যাংক)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:12px;font-weight:600">জমার পরিমাণ (৳)</label>
                                        <input type="number" step="0.01" min="1" id="extra_paid_amount" name="paid_amount" class="form-control" placeholder="0.00" style="font-size:13px">
                                    </div>
                                </div>
                                <div class="grid-2" style="gap:10px">
                                    <div>
                                        <label style="font-size:12px;font-weight:600">বিকাশ / প্রেরক নম্বর</label>
                                        <input type="text" name="sender_number" class="form-control" placeholder="e.g. 017XXXXXXXX" style="font-size:13px">
                                    </div>
                                    <div>
                                        <label style="font-size:12px;font-weight:600">TrxID / রেফারেন্স নং</label>
                                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TRX123456" style="font-size:13px">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('addExtraFeeModal')">বাতিল</button>
                        <button type="submit" class="btn btn-success" style="background:#059669;color:#fff"><i class="fa-solid fa-check"></i> অতিরিক্ত ফি ধার্য করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Edit Existing Invoice (Increase/Decrease amount, Title, Reason) --}}
    <div class="modal-overlay" id="editInvoiceModal">
        <div class="modal-dialog" style="max-width:520px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form id="editInvoiceForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h3 class="modal-title"><i class="fa-solid fa-pen-to-square" style="color:#2563eb"></i> ফি ও ইনভয়েস এডিট করুন</h3>
                        <button type="button" class="btn-close" onclick="closeModal('editInvoiceModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        <div class="form-group">
                            <label style="font-weight:600">ফি এর শিরোনাম (Title) *</label>
                            <input type="text" id="edit_title" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">ফি ধার্যের কারণ / উদ্দেশ্য (Reason / Notes)</label>
                            <textarea id="edit_notes" name="notes" class="form-control" rows="2" placeholder="কেন এই ফি ধার্য করা হয়েছে..."></textarea>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">ক্যাটাগরি (Category) *</label>
                            <select id="edit_category" name="category" class="form-control" required>
                                <option value="SEMESTER">SEMESTER (সেমিস্টার ফি / মাসিক বেতন)</option>
                                <option value="ADMISSION">ADMISSION (ভর্তি ফি)</option>
                                <option value="RETAKE">RETAKE (রিটেক ফি)</option>
                                <option value="EXAM">EXAM (পরীক্ষা ফি)</option>
                                <option value="FINE">FINE (জরিমানা / বিলম্ব ফি)</option>
                                <option value="DOCUMENT">DOCUMENT (সনদ / ডকুমেন্ট ফি)</option>
                                <option value="EXTRA">EXTRA (অতিরিক্ত ফি / বিশেষ চার্জ)</option>
                                <option value="MANUAL">MANUAL (অন্যান্য কাস্টম ফি)</option>
                                <option value="COURSE_TRANSFER">COURSE_TRANSFER (কোর্স পরিবর্তন)</option>
                            </select>
                        </div>
                        <div class="grid-2" style="gap:12px">
                            <div class="form-group">
                                <label style="font-weight:600">মোট পরিমাণ বাড়ানো/কমানো (৳) *</label>
                                <input type="number" step="0.01" min="0" id="edit_amount" name="amount" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:600">ছাড় / ডিসকাউন্ট (৳)</label>
                                <input type="number" step="0.01" min="0" id="edit_discount" name="discount" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">শেষ তারিখ (Due Date)</label>
                            <input type="date" id="edit_due_date" name="due_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('editInvoiceModal')">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> পরিবর্তন সংরক্ষণ করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Update Payment Status --}}
    <div class="modal-overlay" id="statusModal">
        <div class="modal-dialog" style="max-width:480px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form id="statusForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header" style="background:#f5f3ff;border-bottom:1px solid #ddd6fe">
                        <h3 class="modal-title" style="color:#6d28d9">
                            <i class="fa-solid fa-sliders" style="color:#7c3aed"></i> পেমেন্ট স্ট্যাটাস পরিবর্তন করুন
                        </h3>
                        <button type="button" class="btn-close" onclick="closeModal('statusModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        <div style="background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px">
                            ইনভয়েস: <strong id="status_inv_no"></strong><br>
                            বর্তমান বকেয়া: <strong style="color:#dc2626" id="status_inv_due"></strong>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">নতুন স্ট্যাটাস নির্বাচন করুন *</label>
                            <select name="status" id="status_select" class="form-control" required onchange="onStatusChange(this.value)">
                                <option value="PAID">PAID (সম্পূর্ণ পরিশোধিত / মওকুফ সম্পন্ন)</option>
                                <option value="UNPAID">UNPAID (অপরিশোধিত)</option>
                                <option value="PARTIAL">PARTIAL (আংশিক পরিশোধিত)</option>
                                <option value="CANCELLED">CANCELLED (বাতিল / মওকুফ)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">স্ট্যাটাস পরিবর্তনের কারণ / নোট (Audit Remarks)</label>
                            <input type="text" name="remarks" class="form-control" placeholder="যেমন: বিশেষ ছাড়ে মওকুফ / অফিস অনুমোদনক্রমে">
                        </div>

                        {{-- If Paid is selected, optional transaction fields --}}
                        <div id="statusPaidFields" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;display:flex;flex-direction:column;gap:10px">
                            <div style="font-size:12px;font-weight:700;color:#166534">
                                <i class="fa-solid fa-receipt"></i> পরিশোধ নিষ্পত্তি তথ্য (যদি প্রযোজ্য হয়):
                            </div>
                            <div class="grid-2" style="gap:10px">
                                <div>
                                    <label style="font-size:11.5px;font-weight:600">পেমেন্ট মেথড</label>
                                    <select name="payment_method" class="form-control" style="font-size:12.5px">
                                        <option value="CASH">CASH (নগদ)</option>
                                        <option value="BKASH">BKASH (বিকাশ)</option>
                                        <option value="NAGAD">NAGAD (নগদ)</option>
                                        <option value="BANK_TRANSFER">BANK_TRANSFER (ব্যাংক)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:11.5px;font-weight:600">বিকাশ / প্রেরক নম্বর</label>
                                    <input type="text" name="sender_number" class="form-control" placeholder="017XXXXXXXX" style="font-size:12.5px">
                                </div>
                            </div>
                            <div>
                                <label style="font-size:11.5px;font-weight:600">ট্রানজেকশন আইডি (TrxID)</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TRX987654" style="font-size:12.5px">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('statusModal')">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> স্ট্যাটাস আপডেট করুন</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: Collect Offline Payment (with bKash Number & TrxID) --}}
    <div class="modal-overlay" id="collectModal">
        <div class="modal-dialog" style="max-width:500px;font-family:'Kalpurush',sans-serif">
            <div class="modal-content">
                <form id="collectForm" method="POST">
                    @csrf
                    <div class="modal-header" style="background:#ecfdf5;border-bottom:1px solid #a7f3d0">
                        <h3 class="modal-title" style="color:#065f46"><i class="fa-solid fa-cash-register" style="color:#047857"></i> অফলাইন পেমেন্ট গ্রহণ ও রসিদ তৈরি</h3>
                        <button type="button" class="btn-close" onclick="closeModal('collectModal')">&times;</button>
                    </div>
                    <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                        <div style="background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px">
                            ইনভয়েস: <strong id="collect_inv_no"></strong><br>
                            বিবরণ: <span id="collect_inv_title"></span><br>
                            বকেয়া প্রদেয়: <strong style="color:#dc2626" id="collect_inv_due"></strong>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">জমার পরিমাণ (৳) *</label>
                            <input type="number" step="0.01" min="1" id="collect_amount" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">পেমেন্ট মেথড (Payment Method) *</label>
                            <select name="payment_method" id="collect_method_select" class="form-control" required onchange="onCollectMethodChange(this.value)">
                                <option value="CASH">CASH (কাউন্টারে নগদ ক্যাশ গ্রহণ)</option>
                                <option value="BKASH">BKASH (ম্যানুয়াল বিকাশ ট্রানজেকশন)</option>
                                <option value="NAGAD">NAGAD (ম্যানুয়াল নগদ ট্রানজেকশন)</option>
                                <option value="ROCKET">ROCKET (ম্যানুয়াল রকেট ট্রানজেকশন)</option>
                                <option value="BANK_TRANSFER">BANK_TRANSFER (ব্যাংক ডিপোজিট)</option>
                            </select>
                        </div>
                        
                        {{-- bKash / Sender Mobile Number --}}
                        <div class="form-group" id="collectSenderNumberGroup">
                            <label style="font-weight:600">বিকাশ / প্রেরক মোবাইল নম্বর (Sender Mobile Number)</label>
                            <input type="text" name="sender_number" class="form-control" placeholder="যেমন: 01712345678 বা গ্রাহকের বিকাশ নম্বর">
                            <small style="color:#64748b;font-size:11px">বিকাশ/মোবাইল ব্যাংকিংয়ের ক্ষেত্রে নম্বরটি রসিদে প্রদর্শিত হবে।</small>
                        </div>

                        <div class="form-group">
                            <label style="font-weight:600">ট্রানজেকশন আইডি / রেফারেন্স নং (TrxID)</label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="e.g. 8N7A6B5C4D বা ব্যাংকের স্লিপ নং">
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600">মন্তব্য (Remarks)</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="প্রয়োজনে নোট লিখুন..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeModal('collectModal')">বাতিল</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> টাকা গ্রহণ ও রসিদ তৈরি</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Admin Particular Edit Modal --}}
    <div id="adminPartEditModal" class="modal-overlay" onclick="if(event.target===this) closeAdminPartEditModal()">
        <div class="modal-dialog" style="max-width:480px">
            <div class="modal-content" style="border-radius:14px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);font-family:'Kalpurush',sans-serif">
                <div class="modal-header" style="background:#1e40af;color:#fff;padding:16px 20px">
                    <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-pencil"></i> ফি এর টাকার পরিমাণ সমন্বয় / এডিট
                    </div>
                    <button type="button" onclick="closeAdminPartEditModal()" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;line-height:1">&times;</button>
                </div>
                <form id="adminPartEditForm" onsubmit="submitAdminPartEdit(event)" style="padding:20px">
                    <input type="hidden" id="admin_pe_invoice_id">
                    <input type="hidden" id="admin_pe_particular_name">
                    <input type="hidden" id="admin_pe_current_due">
                    <input type="hidden" id="admin_pe_row_sl">

                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;margin-bottom:14px">
                        <div style="font-size:11.5px;color:#166534;font-weight:700">নির্বাচিত ফি আইটেম:</div>
                        <div style="font-size:14px;font-weight:700;color:#0f172a;margin-top:2px" id="admin_pe_display_name">—</div>
                        <div style="font-size:12px;color:#64748b;margin-top:4px">
                            বর্তমান বকেয়া: <strong style="color:#dc2626" id="admin_pe_display_due">৳0</strong>
                        </div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#1e293b;margin-bottom:6px">
                            নতুন টাকার পরিমাণ (New Due Amount - ৳) <span style="color:#dc2626">*</span>:
                        </label>
                        <input type="number" step="1" min="0" id="admin_pe_new_amount" required
                            style="width:100%;padding:9px 12px;border:1.5px solid #2563eb;border-radius:8px;font-size:16px;font-weight:800;color:#0f172a;outline:none;box-sizing:border-box">
                        <small style="font-size:11px;color:#64748b;display:block;margin-top:4px">
                            * পরিমাণ ০ (শূন্য) বসালে এই আইটেমটি মওকুফ / পেইড হিসেবে চিহ্নিত হবে।
                        </small>
                    </div>

                    <div id="admin_pe_position_wrapper" style="margin-bottom:14px;display:none">
                        <label style="display:block;font-size:12px;font-weight:700;color:#1e293b;margin-bottom:4px">
                            ফি এর অবস্থান / ক্রম (Fee Position):
                        </label>
                        <select id="admin_pe_position" onchange="onAdminPartEditPositionChange(this.value)"
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;color:#0f172a;outline:none;background:#fff;box-sizing:border-box">
                            <option value="at_bottom">তালিকার সবার নিচে (At the End - ডিফল্ট)</option>
                            <option value="after">নির্দিষ্ট আইটেমের পরে (After Specific Item)</option>
                            <option value="before">নির্দিষ্ট আইটেমের আগে (Before Specific Item)</option>
                            <option value="at_top">তালিকার সবার উপরে (At the Top)</option>
                        </select>
                        <div id="admin_pe_relative_group" style="display:none;margin-top:8px">
                            <label style="display:block;font-size:11.5px;font-weight:600;color:#64748b;margin-bottom:3px" id="admin_pe_relative_label">
                                কোন আইটেমের পর/আগে?
                            </label>
                            <select id="admin_pe_relative_to"
                                style="width:100%;padding:7px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;outline:none;background:#fff;box-sizing:border-box">
                                @foreach($step1Particulars as $item)
                                    <option value="{{ $item['name'] }}">{{ $item['sl'] }}. {{ $item['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                            কারণ / পরিবর্তনের নোট (Remarks / Reason - ঐচ্ছিক):
                        </label>
                        <input type="text" id="admin_pe_remarks" placeholder="যেমন: বিশেষ ছাড় দেওয়া হয়েছে বা বোর্ড সমন্বয়..."
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;box-sizing:border-box;outline:none">
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;padding-top:14px">
                        <div>
                            <button type="button" id="admin_pe_delete_btn" onclick="submitAdminPartDelete()"
                                style="display:none;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer">
                                <i class="fa-solid fa-trash"></i> ফি মুছে ফেলুন
                            </button>
                        </div>
                        <div style="display:flex;gap:8px">
                            <button type="button" onclick="closeAdminPartEditModal()" class="btn btn-outline" style="border-color:#cbd5e1;color:#475569;font-size:12px">
                                বাতিল
                            </button>
                            <button type="submit" id="admin_pe_submit_btn" class="btn btn-primary" style="background:#2563eb;border-color:#2563eb;font-weight:700;font-size:12px">
                                <i class="fa-solid fa-check"></i> সংরক্ষণ করুন
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Admin Add Custom Fee Modal --}}
    <div id="adminAddFeeModal" class="modal-overlay" onclick="if(event.target===this) closeAdminAddFeeModal()">
        <div class="modal-dialog" style="max-width:480px">
            <div class="modal-content" style="border-radius:14px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);font-family:'Kalpurush',sans-serif">
                <div class="modal-header" style="background:#15803d;color:#fff;padding:16px 20px">
                    <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-plus-circle"></i> নতুন ফি যোগ করুন (Add Custom Fee)
                    </div>
                    <button type="button" onclick="closeAdminAddFeeModal()" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;line-height:1">&times;</button>
                </div>
                <form id="adminAddFeeForm" onsubmit="submitAdminAddFee(event)" style="padding:20px">
                    <input type="hidden" id="admin_aaf_invoice_id" value="{{ $selectedSemesterInvoice?->id }}">
                    <input type="hidden" id="admin_aaf_student_id" value="{{ $student->id }}">
                    <input type="hidden" id="admin_aaf_semester_id" value="{{ $selectedSemesterId }}">

                    <div style="margin-bottom:12px">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#64748b;margin-bottom:3px">প্রযোজ্য সেমিস্টার / কোর্স:</label>
                        <div style="font-size:13.5px;font-weight:700;color:#15803d;background:#f0fdf4;padding:7px 12px;border-radius:6px;border:1px solid #bbf7d0">
                            {{ $selectedSemester?->name ?? 'সাধারণ কোর্স ফি' }}
                        </div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#1e293b;margin-bottom:6px">
                            ফি এর নাম / বিবরণ (Particular Name) <span style="color:#dc2626">*</span>:
                        </label>
                        <input type="text" id="admin_aaf_particular_name" required
                            placeholder="যেমন: লেট ফি, পুনঃপরীক্ষা ফি, জরিমানা..."
                            style="width:100%;padding:9px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13.5px;color:#0f172a;outline:none;box-sizing:border-box">

                        <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px">
                            <span style="font-size:11px;color:#64748b;align-self:center;font-weight:600">কুইক সিলেক্ট:</span>
                            @foreach(['কোর্স এক্টিভিশন ফি (Activation Fee)', 'লেট ফি (Late Fee)', 'পুনঃপরীক্ষা ফি (Retake Fee)', 'সার্টিফিকেট ফি (Certificate Fee)', 'আইডি কার্ড ফি (ID Card Fee)', 'জরিমানা (Fine)', 'অন্যান্য ফি (Other Fee)'] as $preset)
                                <button type="button" onclick="setAdminFeeNamePreset('{{ $preset }}')"
                                    style="font-size:10.5px;padding:2px 7px;border-radius:10px;border:1px solid #cbd5e1;background:#f8fafc;color:#334155;cursor:pointer">
                                    + {{ $preset }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:6px">
                            টাকার পরিমাণ (Fee Amount - ৳) <span style="color:#dc2626">*</span>:
                        </label>
                        <input type="number" step="1" min="1" id="admin_aaf_amount" required placeholder="0"
                            style="width:100%;padding:9px 12px;border:1.5px solid #16a34a;border-radius:8px;font-size:16px;font-weight:800;color:#0f172a;outline:none;box-sizing:border-box">
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#1e293b;margin-bottom:6px">
                            ফি এর অবস্থান / ক্রম (Fee Placement / Position) <span style="color:#dc2626">*</span>:
                        </label>
                        <select id="admin_aaf_position" onchange="onAdminAddFeePositionChange(this.value)"
                            style="width:100%;padding:9px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#0f172a;outline:none;background:#fff;box-sizing:border-box">
                            <option value="at_bottom" selected>তালিকার সবার নিচে (At the End - ডিফল্ট)</option>
                            <option value="after">নির্দিষ্ট আইটেমের পরে (After Specific Item)</option>
                            <option value="before">নির্দিষ্ট আইটেমের আগে (Before Specific Item)</option>
                            <option value="at_top">তালিকার সবার উপরে (At the Top)</option>
                        </select>
                    </div>

                    <div id="admin_aaf_relative_group" style="display:none;margin-bottom:14px;background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:5px">
                            <span id="admin_aaf_relative_label">কোন আইটেমের পরে যুক্ত হবে?</span> <span style="color:#dc2626">*</span>:
                        </label>
                        <select id="admin_aaf_relative_to"
                            style="width:100%;padding:8px 12px;border:1.5px solid #059669;border-radius:8px;font-size:13px;color:#0f172a;outline:none;background:#fff;box-sizing:border-box">
                            @foreach($step1Particulars as $item)
                                <option value="{{ $item['name'] }}">{{ $item['sl'] }}. {{ $item['name'] }} (৳{{ number_format($item['amount'], 0) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                            কারণ / নোট (Reason / Remarks - ঐচ্ছিক):
                        </label>
                        <input type="text" id="admin_aaf_remarks" placeholder="যেমন: কর্তৃপক্ষের নির্দেশে বিশেষ ফি ধার্য..."
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;box-sizing:border-box;outline:none">
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #f1f5f9;padding-top:14px">
                        <button type="button" onclick="closeAdminAddFeeModal()" class="btn btn-outline" style="border-color:#cbd5e1;color:#475569;font-size:12px">
                            বাতিল
                        </button>
                        <button type="submit" id="admin_aaf_submit_btn" class="btn btn-primary" style="background:#16a34a;border-color:#16a34a;font-weight:700;font-size:12px">
                            <i class="fa-solid fa-plus-circle"></i> ফি যোগ করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Admin Collect Particular Payment Modal --}}
    <div id="adminCollectParticularPaymentModal" class="modal-overlay" onclick="if(event.target===this) closeAdminCollectModal()">
        <div class="modal-dialog" style="max-width:520px">
            <div class="modal-content" style="border-radius:14px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);font-family:'Kalpurush',sans-serif">
                <div class="modal-header" style="background:#059669;color:#fff;padding:16px 20px">
                    <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-money-bill-wave"></i> ফি জমা গ্রহণ (Collect Fee Payment)
                    </div>
                    <button type="button" onclick="closeAdminCollectModal()" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;line-height:1">&times;</button>
                </div>
                <form action="{{ route('admin.accounts.particular.collect') }}" method="POST" style="padding:20px">
                    @csrf
                    <input type="hidden" name="invoice_id" id="acp_invoice_id">
                    <input type="hidden" name="student_id" id="acp_student_id" value="{{ $student->id }}">
                    <input type="hidden" name="semester_id" id="acp_semester_id" value="{{ $selectedSemesterId }}">
                    <div id="acp_hidden_names_container"></div>

                    <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;padding:12px 16px;border-radius:10px;margin-bottom:16px">
                        <div style="font-size:11.5px;color:#166534;font-weight:700">নির্বাচিত ফি আইটেম সমূহ:</div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px" id="acp_items_summary">—</div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:6px">
                            জমা / আদায়ের পরিমাণ (টাকা - ৳) <span style="color:#dc2626">*</span>:
                        </label>
                        <input type="number" step="0.01" min="1" name="amount" id="acp_amount" required
                            style="width:100%;padding:10px 14px;border:1.5px solid #059669;border-radius:8px;font-size:17px;font-weight:800;color:#065f46;outline:none;box-sizing:border-box">
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                            পেমেন্ট মাধ্যম (Payment Method) <span style="color:#dc2626">*</span>:
                        </label>
                        <select name="payment_method" id="acp_payment_method" required class="form-control"
                            style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;font-weight:600">
                            <option value="CASH" selected>CASH (সরাসরি অফিস ক্যাশ)</option>
                            <option value="BKASH">BKASH (বিকাশ)</option>
                            <option value="NAGAD">NAGAD (নগদ)</option>
                            <option value="ROCKET">ROCKET (রকেট)</option>
                            <option value="BANK_TRANSFER">BANK_TRANSFER (ব্যাংক ডিপোজিট / স্লিপ)</option>
                            <option value="CARD">CARD (কার্ড)</option>
                            <option value="ONLINE">ONLINE (অন্যান্য অনলাইন)</option>
                        </select>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                            প্রেরক মোবাইল নং / অ্যাকাউন্ট (ঐচ্ছিক):
                        </label>
                        <input type="text" name="sender_number" id="acp_sender_number" placeholder="যেমন: 017XXXXXXXX (বিকাশ/গ্রাহকের নম্বর)"
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;box-sizing:border-box;outline:none">
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                            ট্রানজেকশন আইডি / রেফারেন্স নং (ঐচ্ছিক):
                        </label>
                        <input type="text" name="transaction_id" id="acp_transaction_id" placeholder="যেমন: TrxID বা ব্যাংক স্লিপ নং"
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;box-sizing:border-box;outline:none">
                    </div>

                    <div style="margin-bottom:16px">
                        <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px">
                            মন্তব্য / নোট (Remarks - ঐচ্ছিক):
                        </label>
                        <input type="text" name="remarks" id="acp_remarks" placeholder="যেমন: জানুয়ারি-মার্চ মাসের বেতন গ্রহণ..."
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;box-sizing:border-box;outline:none">
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #f1f5f9;padding-top:14px">
                        <button type="button" onclick="closeAdminCollectModal()" class="btn btn-outline" style="border-color:#cbd5e1;color:#475569;font-size:12.5px">
                            বাতিল
                        </button>
                        <button type="submit" class="btn btn-success" style="background:#059669;color:#fff;border:none;font-weight:700;font-size:12.5px;padding:8px 18px;display:inline-flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-check"></i> টাকা জমা গ্রহণ ও রসিদ তৈরি
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openModal(modalId) {
        const m = document.getElementById(modalId);
        if (m) {
            m.classList.add('open', 'active', 'show');
            m.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const m = (typeof modalId === 'string') ? document.getElementById(modalId) : modalId;
        if (m) {
            m.classList.remove('open', 'active', 'show');
            m.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function openExtraFeeModal() {
        openModal('addExtraFeeModal');
    }

    function applyExtraPreset(title, amount, notes, category) {
        document.getElementById('extra_title').value = title;
        document.getElementById('extra_amount').value = amount;
        document.getElementById('extra_notes').value = notes;
        document.getElementById('extra_category_input').value = category;
        const extraPaid = document.getElementById('extra_paid_amount');
        if (extraPaid) extraPaid.value = amount;
    }

    function toggleImmediatePay(checked) {
        document.getElementById('immediatePayFields').style.display = checked ? 'flex' : 'none';
    }

    function toggleExtraImmediatePay(checked) {
        document.getElementById('extraImmediatePayFields').style.display = checked ? 'flex' : 'none';
    }

    function openEditModal(id, title, notes, category, amount, discount, dueDate) {
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_notes').value = notes;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_amount').value = amount;
        document.getElementById('edit_discount').value = discount;
        document.getElementById('edit_due_date').value = dueDate;
        document.getElementById('editInvoiceForm').action = "/admin/accounts/invoices/" + id;
        openModal('editInvoiceModal');
    }

    function openCollectModal(id, invNo, due, title) {
        document.getElementById('collect_inv_no').innerText = invNo;
        document.getElementById('collect_inv_title').innerText = title;
        document.getElementById('collect_inv_due').innerText = '৳' + due;
        document.getElementById('collect_amount').value = due;
        document.getElementById('collect_amount').max = due;
        document.getElementById('collectForm').action = "/admin/accounts/invoices/" + id + "/collect";
        openModal('collectModal');
    }

    function openStatusModal(id, invNo, currentStatus, due) {
        document.getElementById('status_inv_no').innerText = invNo;
        document.getElementById('status_inv_due').innerText = '৳' + due;
        document.getElementById('status_select').value = currentStatus;
        document.getElementById('statusForm').action = "/admin/accounts/invoices/" + id + "/status";
        onStatusChange(currentStatus);
        openModal('statusModal');
    }

    function onStatusChange(status) {
        const paidBox = document.getElementById('statusPaidFields');
        if (paidBox) {
            paidBox.style.display = (status === 'PAID') ? 'flex' : 'none';
        }
    }

    function onCollectMethodChange(method) {
        const numInput = document.querySelector('#collectSenderNumberGroup input');
        if (numInput) {
            if (method === 'BKASH') {
                numInput.placeholder = "যেমন: 01XXXXXXXXX (বিকাশ নম্বর)";
            } else if (method === 'NAGAD') {
                numInput.placeholder = "যেমন: 01XXXXXXXXX (নগদ নম্বর)";
            } else if (method === 'ROCKET') {
                numInput.placeholder = "যেমন: 01XXXXXXXXX (রকেট নম্বর)";
            } else {
                numInput.placeholder = "যেমন: 01XXXXXXXXX (প্রেরক মোবাইল নম্বর)";
            }
        }
    }

    // ── Month-wise Particulars Management JavaScript ──

    function filterAdminStep1Table(type, btn) {
        document.querySelectorAll('.admin-step1-filter-btn').forEach(b => {
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

        const rows = document.querySelectorAll('tr[data-admin-step1-status]');
        rows.forEach(r => {
            const status = r.getAttribute('data-admin-step1-status');
            if (type === 'all') {
                r.style.display = '';
            } else if (type === 'due') {
                r.style.display = (status === 'due') ? '' : 'none';
            } else if (type === 'paid') {
                r.style.display = (status === 'paid') ? '' : 'none';
            }
        });
    }

    function onAdminStep1CheckboxChange(clickedChk) {
        // Admin has full freedom to select or deselect any fee independently without auto-selecting upper rows
        updateAdminStep1Selection();
    }

    function updateAdminStep1Selection() {
        const chks = document.querySelectorAll('.admin-step1-chk:checked');
        let total = 0;
        chks.forEach(c => total += parseFloat(c.dataset.amount) || 0);

        const bar = document.getElementById('adminStep1MultiBar');
        const countSpan = document.getElementById('adminStep1Count');
        const totalSpan = document.getElementById('adminStep1Total');

        if (chks.length > 0) {
            bar.style.display = 'flex';
            countSpan.innerText = chks.length;
            totalSpan.innerText = Math.round(total).toLocaleString('en-BD');
        } else {
            bar.style.display = 'none';
        }
    }

    function onAdminPartEditPositionChange(val) {
        const group = document.getElementById('admin_pe_relative_group');
        const label = document.getElementById('admin_pe_relative_label');
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

    function openAdminPartEditModal(invoiceId, pName, currentDue, sl, isAdded = false, position = 'at_bottom', relativeTo = '') {
        document.getElementById('admin_pe_invoice_id').value = invoiceId || '';
        document.getElementById('admin_pe_particular_name').value = pName;
        document.getElementById('admin_pe_current_due').value = currentDue;
        document.getElementById('admin_pe_row_sl').value = sl;

        document.getElementById('admin_pe_display_name').innerText = pName;
        document.getElementById('admin_pe_display_due').innerText = '৳' + Number(currentDue).toLocaleString('en-BD');
        document.getElementById('admin_pe_new_amount').value = currentDue;
        document.getElementById('admin_pe_remarks').value = '';

        const posWrapper = document.getElementById('admin_pe_position_wrapper');
        if (posWrapper) {
            posWrapper.style.display = isAdded ? 'block' : 'none';
            const posSelect = document.getElementById('admin_pe_position');
            if (posSelect) posSelect.value = position || 'at_bottom';
            const relSelect = document.getElementById('admin_pe_relative_to');
            if (relSelect && relativeTo) relSelect.value = relativeTo;
            onAdminPartEditPositionChange(position || 'at_bottom');
        }

        const delBtn = document.getElementById('admin_pe_delete_btn');
        if (delBtn) {
            delBtn.style.display = isAdded ? 'inline-flex' : 'none';
        }

        openModal('adminPartEditModal');
        setTimeout(() => {
            const input = document.getElementById('admin_pe_new_amount');
            if (input) { input.focus(); input.select(); }
        }, 100);
    }

    function closeAdminPartEditModal() {
        closeModal('adminPartEditModal');
    }

    function submitAdminPartDelete() {
        const pName = document.getElementById('admin_pe_particular_name').value;
        const invoiceId = document.getElementById('admin_pe_invoice_id').value;
        if (!confirm('আপনি কি নিশ্চিত যে "' + pName + '" ফি আইটেমটি সম্পূর্ণ মুছে ফেলতে চান?')) {
            return;
        }
        const btn = document.getElementById('admin_pe_delete_btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> মুছা হচ্ছে...';

        fetch("{{ route('admin.accounts.particular.delete') }}", {
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
            if (data.success) {
                closeAdminPartEditModal();
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'ফি মুছে ফেলতে সমস্যা হয়েছে।');
            }
        })
        .catch(err => {
            btn.disabled = false;
            alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
        });
    }

    function submitAdminPartEdit(e) {
        e.preventDefault();
        const btn = document.getElementById('admin_pe_submit_btn');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> সেভ হচ্ছে...';

        const invoiceId = document.getElementById('admin_pe_invoice_id').value;
        const pName = document.getElementById('admin_pe_particular_name').value;
        const currentDue = document.getElementById('admin_pe_current_due').value;
        const newAmount = document.getElementById('admin_pe_new_amount').value;
        const remarks = document.getElementById('admin_pe_remarks').value;

        const posWrapper = document.getElementById('admin_pe_position_wrapper');
        const isPosVisible = posWrapper && posWrapper.style.display !== 'none';
        const posEl = document.getElementById('admin_pe_position');
        const position = (isPosVisible && posEl) ? posEl.value : null;
        const relEl = document.getElementById('admin_pe_relative_to');
        const relativeTo = (isPosVisible && (position === 'after' || position === 'before') && relEl) ? relEl.value : null;

        fetch("{{ route('admin.accounts.particular.update') }}", {
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
            btn.innerHTML = origText;
            if (data.success) {
                closeAdminPartEditModal();
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'ফি আপডেট করতে ত্রুটি হয়েছে।');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origText;
            alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
        });
    }

    function onAdminAddFeePositionChange(val) {
        const group = document.getElementById('admin_aaf_relative_group');
        const label = document.getElementById('admin_aaf_relative_label');
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
        document.getElementById('admin_aaf_particular_name').value = '';
        document.getElementById('admin_aaf_amount').value = '';
        document.getElementById('admin_aaf_remarks').value = '';
        const posSelect = document.getElementById('admin_aaf_position');
        if (posSelect) posSelect.value = 'at_bottom';
        onAdminAddFeePositionChange('at_bottom');
        openModal('adminAddFeeModal');
        setTimeout(() => {
            const input = document.getElementById('admin_aaf_particular_name');
            if (input) input.focus();
        }, 100);
    }

    function closeAdminAddFeeModal() {
        closeModal('adminAddFeeModal');
    }

    function setAdminFeeNamePreset(name) {
        const input = document.getElementById('admin_aaf_particular_name');
        if (input) {
            input.value = name;
            const amtInput = document.getElementById('admin_aaf_amount');
            if (amtInput) amtInput.focus();
        }
    }

    function submitAdminAddFee(e) {
        e.preventDefault();
        const btn = document.getElementById('admin_aaf_submit_btn');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> যোগ হচ্ছে...';

        const invoiceId = document.getElementById('admin_aaf_invoice_id').value;
        const studentId = document.getElementById('admin_aaf_student_id').value;
        const semesterId = document.getElementById('admin_aaf_semester_id').value;
        const pName = document.getElementById('admin_aaf_particular_name').value;
        const amount = document.getElementById('admin_aaf_amount').value;
        const remarks = document.getElementById('admin_aaf_remarks').value;

        const posEl = document.getElementById('admin_aaf_position');
        const position = posEl ? posEl.value : 'at_bottom';
        const relEl = document.getElementById('admin_aaf_relative_to');
        const relativeTo = (position === 'after' || position === 'before') && relEl ? relEl.value : null;

        fetch("{{ route('admin.accounts.particular.store') }}", {
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
            btn.innerHTML = origText;
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
            btn.innerHTML = origText;
            alert('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।');
        });
    }

    function openAdminSingleCollectModal(invoiceId, pName, due, totalAmount) {
        document.getElementById('acp_invoice_id').value = invoiceId || '';
        document.getElementById('acp_items_summary').innerText = pName;
        document.getElementById('acp_amount').value = due;
        document.getElementById('acp_remarks').value = pName + ' বাবদ জমা';

        const totalAmt = totalAmount || due;
        const container = document.getElementById('acp_hidden_names_container');
        let html = '<input type="hidden" name="particular_names[]" value="' + pName.replace(/"/g, '&quot;') + '">' +
                   '<input type="hidden" name="particular_dues[' + pName.replace(/"/g, '&quot;') + ']" value="' + due + '">' +
                   '<input type="hidden" name="particular_amounts[' + pName.replace(/"/g, '&quot;') + ']" value="' + totalAmt + '">';
        if (invoiceId) {
            html += '<input type="hidden" name="particular_invoices[' + pName.replace(/"/g, '&quot;') + ']" value="' + invoiceId + '">';
        }
        container.innerHTML = html;

        openModal('adminCollectParticularPaymentModal');
    }

    function openAdminMultiCollectModal() {
        const chks = document.querySelectorAll('.admin-step1-chk:checked');
        if (chks.length === 0) {
            alert('অনুগ্রহ করে ফি জমা নিতে কমপক্ষে একটি ফি আইটেম নির্বাচন করুন।');
            return;
        }

        let total = 0;
        let names = [];
        let targetInvoiceId = null;

        chks.forEach(c => {
            total += parseFloat(c.dataset.amount) || 0;
            names.push(c.dataset.name);
            if (!targetInvoiceId && c.dataset.invoiceId) {
                targetInvoiceId = c.dataset.invoiceId;
            }
        });

        document.getElementById('acp_invoice_id').value = targetInvoiceId || "{{ $selectedSemesterInvoice?->id ?? '' }}";
        document.getElementById('acp_items_summary').innerText = names.join(', ');
        document.getElementById('acp_amount').value = total;
        document.getElementById('acp_remarks').value = names.join(', ') + ' বাবদ ফি জমা';

        const container = document.getElementById('acp_hidden_names_container');
        container.innerHTML = '';
        chks.forEach(c => {
            const n = c.dataset.name;
            const d = parseFloat(c.dataset.amount) || 0;
            const a = parseFloat(c.dataset.totalAmount) || d;
            const invId = c.dataset.invoiceId;

            const inputName = document.createElement('input');
            inputName.type = 'hidden';
            inputName.name = 'particular_names[]';
            inputName.value = n;
            container.appendChild(inputName);

            const inputDue = document.createElement('input');
            inputDue.type = 'hidden';
            inputDue.name = 'particular_dues[' + n + ']';
            inputDue.value = d;
            container.appendChild(inputDue);

            const inputAmt = document.createElement('input');
            inputAmt.type = 'hidden';
            inputAmt.name = 'particular_amounts[' + n + ']';
            inputAmt.value = a;
            container.appendChild(inputAmt);

            if (invId) {
                const inputInv = document.createElement('input');
                inputInv.type = 'hidden';
                inputInv.name = 'particular_invoices[' + n + ']';
                inputInv.value = invId;
                container.appendChild(inputInv);
            }
        });

        openModal('adminCollectParticularPaymentModal');
    }

    function closeAdminCollectModal() {
        closeModal('adminCollectParticularPaymentModal');
    }

    function confirmRevertPayment(invoiceId, particularName, amount) {
        if (!invoiceId) {
            alert('ইনভয়েস আইডি পাওয়া যায়নি।');
            return;
        }
        const reason = prompt('আপনি কি এই বিলটির পরিশোধিত অবস্থা বাতিল করে পুনরায় বকেয়া/আনপেইড তালিকায় ফেরত আনতে চান?\n\nবাতিলের কারণ/মন্তব্য লিখুন (ঐচ্ছিক):', 'এডমিন কর্তৃক আনপেইড করা হলো');
        if (reason === null) return;

        fetch("{{ route('admin.accounts.particular.revert_payment') }}", {
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

    function confirmDeleteAddedFee(invoiceId, particularName) {
        if (!invoiceId) {
            alert('ইনভয়েস আইডি পাওয়া যায়নি।');
            return;
        }
        if (!confirm('আপনি কি নিশ্চিত যে "' + particularName + '" অতিরিক্ত ফিটি সম্পূর্ণ ডিলিট করতে চান?')) {
            return;
        }

        fetch("{{ route('admin.accounts.particular.delete') }}", {
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

    // Escape closes modals
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            closeAdminPartEditModal();
            closeAdminAddFeeModal();
            closeAdminCollectModal();
        }
    });
    </script>
</x-admin-layout>
