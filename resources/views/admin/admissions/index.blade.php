<x-admin-layout>
    <x-slot name="title">Admissions</x-slot>

    <style>
    /* ─── Compact Admission Page View Styling ─── */
    .page-content {
        padding: 12px 18px 16px !important;
        font-family: 'Kalpurush', 'Inter', -apple-system, sans-serif !important;
    }
    .admission-filter-grid {
        display: grid;
        grid-template-columns: minmax(130px, 1.25fr) minmax(120px, 1.1fr) minmax(100px, 0.85fr) minmax(140px, 1.4fr) auto;
        gap: 6px;
        align-items: center;
    }
    @media (max-width: 900px) {
        .admission-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 580px) {
        .admission-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Screen-Fit Table Container with Sticky Thead & Viewport-Constrained Scroll */
    #admissions-table-wrapper {
        max-height: calc(100vh - 215px);
        min-height: 280px;
        overflow-x: auto !important;
        overflow-y: auto !important;
        position: relative;
        width: 100% !important;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        -webkit-overflow-scrolling: touch;
    }
    #admissions-table {
        width: 100% !important;
        min-width: 820px;
        margin: 0;
        border-collapse: collapse;
        font-family: 'Kalpurush', sans-serif;
    }
    #admissions-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        border-bottom: 1px solid #cbd5e1;
        padding: 6px 8px;
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
    }
    #admissions-table tbody td {
        padding: 5px 8px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11.5px;
    }
    #admissions-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Sleek Custom Scrollbars */
    #admissions-table-wrapper::-webkit-scrollbar,
    #table-scroll-top-bar::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    #admissions-table-wrapper::-webkit-scrollbar-track,
    #table-scroll-top-bar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    #admissions-table-wrapper::-webkit-scrollbar-thumb,
    #table-scroll-top-bar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    #admissions-table-wrapper::-webkit-scrollbar-thumb:hover,
    #table-scroll-top-bar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    </style>

    {{-- Compact Page Header --}}
    <div class="page-header" style="margin-bottom:6px;padding-bottom:2px;display:flex;align-items:center;justify-content:space-between">
        <div class="page-header-left" style="display:flex;align-items:baseline;gap:10px">
            <h1 style="font-size:17px;margin:0;font-weight:700;color:#0f172a">Admission Management</h1>
            <span style="font-size:11.5px;color:var(--text-muted)">Admin-added এবং Public Form থেকে আসা সকল আবেদন</span>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.admissions.create') }}" class="btn btn-primary btn-sm" style="height:28px;padding:0 10px;font-size:11.5px;display:inline-flex;align-items:center;gap:4px">
                <i class="fa-solid fa-plus"></i> New Admission
            </a>
        </div>
    </div>

    {{-- ── Compact Course & Session-wise Admission Statistics Report Section ── --}}
    <div class="card" style="margin-bottom:6px;border:1px solid #cbd5e1;border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,0.03);font-family:'Kalpurush',sans-serif">
        <div class="card-header" style="background:#f8fafc;padding:5px 10px;min-height:32px;display:flex;align-items:center;justify-content:space-between;cursor:pointer" onclick="toggleReportSection()">
            <div style="display:flex;align-items:center;gap:7px">
                <span style="background:#047857;color:#fff;width:22px;height:22px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:11px">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>
                <span style="font-weight:700;font-size:12.5px;color:#0f172a">কোর্স ও সেশন ভিত্তিক ভর্তি রিপোর্ট (Admission Statistics Report)</span>
                <span style="font-size:11px;color:#64748b;display:inline-block">({{ $reportTotalApps }} টি আবেদন)</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <span class="badge badge-secondary no-dot" style="font-size:10.5px;padding:1px 6px">{{ $admissionReport->count() }} টি গ্রুপ</span>
                <button type="button" id="btn-toggle-report" class="btn btn-outline btn-sm" style="font-size:10.5px;padding:1px 6px;height:24px" onclick="event.stopPropagation(); toggleReportSection();">
                    <i class="fa-solid fa-chevron-down" id="report-chevron"></i> <span id="report-toggle-text">রিপোর্ট দেখুন</span>
                </button>
            </div>
        </div>

        <div id="report-content-body" style="display:none;padding:10px 12px;border-top:1px solid #e2e8f0;background:#ffffff">
            {{-- Quick Summary Mini Cards --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:6px;margin-bottom:8px">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:5px;padding:6px 8px">
                    <div style="font-size:10.5px;color:#166534;font-weight:600">মোট আবেদন (Total)</div>
                    <div style="font-size:16px;font-weight:800;color:#047857">{{ $reportTotalApps }}</div>
                </div>
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:5px;padding:6px 8px">
                    <div style="font-size:10.5px;color:#065f46;font-weight:600">অনুমোদিত (Approved)</div>
                    <div style="font-size:16px;font-weight:800;color:#059669">{{ $reportApprovedApps }}</div>
                </div>
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:5px;padding:6px 8px">
                    <div style="font-size:10.5px;color:#92400e;font-weight:600">অপেক্ষমাণ (Pending)</div>
                    <div style="font-size:16px;font-weight:800;color:#d97706">{{ $reportPendingApps }}</div>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:5px;padding:6px 8px">
                    <div style="font-size:10.5px;color:#1e40af;font-weight:600">ভাই শাখা (পুরুষ)</div>
                    <div style="font-size:16px;font-weight:800;color:#2563eb">{{ $reportMaleApps }}</div>
                </div>
                <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:5px;padding:6px 8px">
                    <div style="font-size:10.5px;color:#9d174d;font-weight:600">বোন শাখা (মহিলা)</div>
                    <div style="font-size:16px;font-weight:800;color:#db2777">{{ $reportFemaleApps }}</div>
                </div>
            </div>

            {{-- Breakdown Table --}}
            <div style="overflow-x:auto;max-height:200px">
                <table class="table" style="font-size:11px;margin:0">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th style="width:30px;padding:3px 5px">ক্রম</th>
                            <th style="padding:3px 5px">কোর্সের নাম</th>
                            <th style="padding:3px 5px">শিক্ষাবর্ষ ও সেশন</th>
                            <th style="text-align:center;padding:3px 5px">মোট</th>
                            <th style="text-align:center;padding:3px 5px">অনুমোদিত</th>
                            <th style="text-align:center;padding:3px 5px">অপেক্ষমাণ</th>
                            <th style="text-align:center;padding:3px 5px">ভাই</th>
                            <th style="text-align:center;padding:3px 5px">বোন</th>
                            <th style="text-align:right;padding:3px 5px">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissionReport as $idx => $r)
                        <tr>
                            <td style="padding:3px 5px">{{ $idx + 1 }}</td>
                            <td style="padding:3px 5px"><strong>{{ $r->interestedCourse->name ?? 'অনির্ধারিত কোর্স' }}</strong></td>
                            <td style="padding:3px 5px">
                                @if($r->session)
                                    <span style="font-weight:600;color:#0f172a">{{ $r->session->name }}</span>
                                    @if($r->session->academicYear)
                                        <span style="color:#047857;font-size:10px">({{ $r->session->academicYear->name }})</span>
                                    @endif
                                @else
                                    <span style="color:#94a3b8">সাধারণ সেশন</span>
                                @endif
                            </td>
                            <td style="text-align:center;font-weight:700;padding:3px 5px">
                                <span class="badge badge-secondary no-dot" style="font-size:10.5px">{{ $r->total_apps }}</span>
                            </td>
                            <td style="text-align:center;color:#059669;font-weight:700;padding:3px 5px">{{ $r->approved_count }}</td>
                            <td style="text-align:center;color:#d97706;font-weight:700;padding:3px 5px">{{ $r->pending_count }}</td>
                            <td style="text-align:center;color:#2563eb;font-weight:600;padding:3px 5px">{{ $r->male_count }}</td>
                            <td style="text-align:center;color:#db2777;font-weight:600;padding:3px 5px">{{ $r->female_count }}</td>
                            <td style="text-align:right;padding:3px 5px">
                                <a href="?tab={{ $tab }}&course_id={{ $r->interested_course_id }}&session_id={{ $r->academic_session_id }}"
                                   class="btn btn-outline btn-sm" style="font-size:10px;padding:1px 5px;border-radius:4px">
                                    ফিল্টার
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:8px">কোনো তথ্য পাওয়া যায়নি।</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Compact Tabs --}}
    @php
        $tab    = request('tab', 'all');
        $status = request('status');
    @endphp
    <div class="tabs" style="margin-bottom:0;display:flex;gap:3px;border-bottom:1px solid #cbd5e1;padding:0">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'all']) }}" class="tab-item {{ $tab === 'all' ? 'active' : '' }}" style="padding:4px 10px;font-size:11.5px">All
            <span class="badge badge-secondary no-dot" style="margin-left:3px;font-size:10.5px">{{ $totalCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'admin']) }}" class="tab-item {{ $tab === 'admin' ? 'active' : '' }}" style="padding:4px 10px;font-size:11.5px">
            <i class="fa-solid fa-building-columns"></i> Admin Added
            <span class="badge badge-secondary no-dot" style="margin-left:3px;font-size:10.5px">{{ $adminCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'public']) }}" class="tab-item {{ $tab === 'public' ? 'active' : '' }}" style="padding:4px 10px;font-size:11.5px">
            <i class="fa-solid fa-globe"></i> Public Form
            <span class="badge badge-secondary no-dot" style="margin-left:3px;background:rgba(139,92,246,.15);color:#7c3aed;font-size:10.5px">{{ $publicCount }}</span>
            @if($publicPending > 0)<span class="badge no-dot" style="background:#ef4444;color:#fff;margin-left:3px;font-size:10px;padding:1px 5px">{{ $publicPending }}</span>@endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'unpaid']) }}" class="tab-item {{ $tab === 'unpaid' ? 'active' : '' }}" style="padding:4px 10px;font-size:11.5px">
            <i class="fa-solid fa-credit-card"></i> Unpaid
            <span class="badge badge-secondary no-dot" style="margin-left:3px;background:#fef3c7;color:#b45309;font-size:10.5px">{{ $unpaidCount ?? 0 }}</span>
        </a>
    </div>

    {{-- Compact Inline Filter Bar --}}
    <div style="background:var(--card-bg);border:1px solid var(--card-border);border-top:0;border-radius:0 0 6px 6px;padding:5px 8px;margin-bottom:6px;font-family:'Kalpurush',sans-serif">
        <form method="GET" action="{{ route('admin.admissions.index') }}" id="admissionFilterForm" style="display:flex;flex-direction:column;gap:4px">
            <input type="hidden" name="tab" value="{{ $tab }}">

            {{-- Line 1: Status Pills + Reset Button --}}
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:4px">
                <div style="display:flex;align-items:center;gap:3px;flex-wrap:wrap">
                    <span style="font-size:11px;color:var(--text-muted);font-weight:700">স্ট্যাটাস:</span>
                    @foreach([''=>'সকল (All)','PENDING'=>'Pending','APPROVED'=>'Approved','TRASH'=>'Trash'] as $s => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $s]) }}"
                       style="font-size:10.5px;padding:1px 7px;border-radius:12px;text-decoration:none;border:1px solid var(--card-border);display:inline-flex;align-items:center;gap:3px;
                              {{ ($status === $s || ($s === 'TRASH' && $status === 'REJECTED')) ? 'background:var(--blue);color:#fff;border-color:var(--blue);font-weight:700' : 'color:var(--text-secondary);background:#fff' }}">
                        @if($s === 'TRASH') <i class="fa-solid fa-trash-can" style="font-size:9px"></i> @endif
                        {{ $label }}
                    </a>
                    @endforeach
                    <input type="hidden" name="status" value="{{ $status }}">
                </div>

                @if(request('course_id') || request('session_id') || request('gender') || request('search') || request('status'))
                <a href="?tab={{ $tab }}" style="font-size:10.5px;color:#dc2626;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:3px">
                    <i class="fa-solid fa-rotate-left"></i> ফিল্টার রিসেট
                </a>
                @endif
            </div>

            {{-- Line 2: 1-Line Sleek Grid Toolbar: Course, Session, Gender, Search, Filter --}}
            <div class="admission-filter-grid">
                <div>
                    <select name="course_id" class="form-control" style="width:100%;height:28px;font-size:11px;padding:1px 5px" onchange="this.form.submit()">
                        <option value="">-- সকল কোর্স --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ ($courseId ?? request('course_id')) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="session_id" class="form-control" style="width:100%;height:28px;font-size:11px;padding:1px 5px" onchange="this.form.submit()">
                        <option value="">-- সকল সেশন --</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ ($sessionId ?? request('session_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}{{ $s->academicYear ? ' (' . $s->academicYear->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="gender" class="form-control" style="width:100%;height:28px;font-size:11px;padding:1px 5px" onchange="this.form.submit()">
                        <option value="">-- সকল শাখা --</option>
                        <option value="Male" {{ ($gender ?? request('gender')) === 'Male' ? 'selected' : '' }}>ভাই (পুরুষ)</option>
                        <option value="Female" {{ ($gender ?? request('gender')) === 'Female' ? 'selected' : '' }}>বোন (মহিলা)</option>
                    </select>
                </div>
                <div>
                    <input type="text" name="search" value="{{ $search ?? request('search') }}" class="form-control" placeholder="নাম, মোবাইল, আবেদন নং..." style="width:100%;height:28px;font-size:11px;padding:1px 7px">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-sm" style="height:28px;padding:0 10px;font-size:11px;display:inline-flex;align-items:center;gap:3px;white-space:nowrap">
                        <i class="fa-solid fa-filter"></i> ফিল্টার
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Synchronized Top Scrollbar (Visible without scrolling down) --}}
    <div id="table-scroll-top-bar" style="overflow-x:auto;overflow-y:hidden;height:6px;width:100%;display:none;background:#f1f5f9;border-radius:3px 3px 0 0;margin-bottom:2px">
        <div id="table-scroll-top-dummy" style="height:6px"></div>
    </div>

    {{-- Screen-Fit Table Container with Sticky Thead & Viewport Bottom Scrollbar --}}
    <div id="admissions-table-wrapper">
        <table id="admissions-table" class="table">
            <thead>
                <tr>
                    <th style="width:65px;white-space:nowrap">Source</th>
                    <th style="width:17%;white-space:nowrap">Applicant</th>
                    <th style="width:23%;white-space:nowrap">Course / Session</th>
                    <th style="width:85px;white-space:nowrap">Applied On</th>
                    <th style="width:75px;white-space:nowrap;text-align:center">Payment</th>
                    <th style="width:115px;white-space:nowrap">Status &amp; Reviewer</th>
                    <th style="width:115px;white-space:nowrap;text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $paidIds = isset($allPaidFormIds) ? $allPaidFormIds->toArray() : [];
                @endphp

                {{-- ── Unpaid Tab Applications ── --}}
                @if($tab === 'unpaid')
                    @forelse($unpaidApplications ?? [] as $unp)
                    <tr>
                        <td>
                            <span class="badge no-dot" style="background:rgba(239,68,68,.1);color:#dc2626;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-clock"></i> Unpaid</span>
                            <div style="font-size:9px;color:var(--text-muted);margin-top:1px">{{ $unp->application_no }}</div>
                        </td>
                        <td class="td-primary">
                            <strong style="font-size:12px;color:#0f172a">{{ $unp->student->name ?? $unp->applicant_name }}</strong>
                            <div class="td-muted" style="font-size:10.5px">{{ $unp->student->phone ?? $unp->phone }}</div>
                        </td>
                        <td>
                            <div style="font-weight:600;color:#0f172a">{{ $unp->interestedCourse->name ?? '—' }}</div>
                            <div class="td-muted" style="font-size:10px">৳ {{ number_format($unp->interestedCourse->admission_fee ?? 0, 0) }}</div>
                        </td>
                        <td class="td-muted" style="font-size:10.5px;white-space:nowrap">{{ $unp->created_at->format('d M Y') }}</td>
                        <td style="text-align:center">
                            <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-circle-xmark"></i> Unpaid</span>
                        </td>
                        <td>
                            @if($unp->status === 'APPROVED')
                                <span class="badge badge-active" style="font-size:9.5px;padding:1px 5px">Approved</span>
                            @elseif(in_array($unp->status, ['TRASH', 'REJECTED']))
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-trash-can"></i> Trash</span>
                            @else
                                <span class="badge badge-pending" style="font-size:9.5px;padding:1px 5px">Pending</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <div style="display:inline-flex;align-items:center;gap:3px">
                                @if(in_array($unp->status, ['TRASH', 'REJECTED']))
                                    <form method="POST" action="{{ route('admin.admissions.untrash', $unp) }}" style="display:inline;" onsubmit="return confirm('আবেদনটি কি ট্র্যাশ থেকে পুনরুদ্ধার (Untrash) করতে চান?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:600;padding:1px 5px;font-size:10px;height:24px" title="Restore to pending">
                                            <i class="fa-solid fa-rotate-left"></i> রিস্টোর
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.admissions.send-repayment-email', $unp) }}" style="display:inline;" onsubmit="return confirm('আবেদনকারীর কাছে রি-পেমেন্ট মেইল পাঠাতে চান?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm" style="color:#b45309;border-color:#fde68a;background:#fffbeb;padding:1px 5px;font-size:10px;height:24px" title="Send Re-Payment Email">
                                        <i class="fa-solid fa-paper-plane"></i> রি-পেমেন্ট
                                    </button>
                                </form>
                                <a href="{{ route('admin.admissions.show', $unp) }}" class="btn btn-outline btn-sm" style="padding:1px 5px;font-size:10px;height:24px"><i class="fa-solid fa-eye"></i> ভিউ</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-muted)">কোনো অপরিশোধিত (Unpaid) আবেদন পাওয়া যায়নি।</td></tr>
                    @endforelse
                @endif

                {{-- ── Admin-created Admissions ── --}}
                @if($tab !== 'public' && $tab !== 'unpaid')
                    @forelse($adminAdmissions as $adm)
                    <tr>
                        <td>
                            <span class="badge no-dot" style="background:rgba(59,130,246,.1);color:#1d4ed8;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-building-columns"></i> Admin</span>
                        </td>
                        <td class="td-primary">
                            <strong style="font-size:12px;color:#0f172a">{{ $adm->student->name ?? '—' }}</strong>
                            <div class="td-muted" style="font-size:10.5px">{{ $adm->student->phone ?? '—' }}</div>
                            @if($adm->waiver_code)
                                <span class="badge badge-active no-dot" style="font-size:9px;padding:1px 4px;margin-top:1px"><i class="fa-solid fa-gift"></i> {{ $adm->waiver_code }} ({{ $adm->discount_percent }}%)</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600;color:#0f172a">{{ $adm->interestedCourse->name ?? '—' }}</div>
                            <div class="td-muted" style="font-size:10px">Attempt #{{ $adm->attempt_no }}</div>
                        </td>
                        <td class="td-muted" style="font-size:10.5px;white-space:nowrap">{{ $adm->created_at->format('d M Y') }}</td>
                        <td style="text-align:center">
                            @if(in_array($adm->id, $paidIds) || ($adm->interestedCourse && $adm->interestedCourse->admission_fee == 0))
                                <span class="badge badge-active" style="background:#dcfce7;color:#166534;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-circle-check"></i> Paid</span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-circle-xmark"></i> Unpaid</span>
                            @endif
                        </td>
                        <td>
                            @if($adm->status === 'PENDING')
                                <span class="badge badge-pending" style="font-size:9.5px;padding:1px 5px">Pending</span>
                            @elseif($adm->status === 'APPROVED')
                                <span class="badge badge-active" style="font-size:9.5px;padding:1px 5px">Approved</span>
                                <div style="font-size:9.5px;color:#15803d;margin-top:1px;">
                                    অনুমোদন: <strong>{{ $adm->reviewer->name ?? 'এডমিন' }}</strong>
                                </div>
                            @elseif(in_array($adm->status, ['TRASH', 'REJECTED']))
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-trash-can"></i> Trash</span>
                                <div style="font-size:9.5px;color:#991b1b;margin-top:1px;">
                                    পর্যালোচক: {{ $adm->reviewer->name ?? 'এডমিন' }}
                                </div>
                            @else
                                <span class="badge badge-pending" style="font-size:9.5px;padding:1px 5px">{{ $adm->status }}</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <div style="display:inline-flex;align-items:center;gap:3px">
                                @if(in_array($adm->status, ['TRASH', 'REJECTED']))
                                    <form method="POST" action="{{ route('admin.admissions.untrash', $adm) }}" style="display:inline;" onsubmit="return confirm('আবেদনটি কি ট্র্যাশ থেকে পুনরুদ্ধার (Untrash) করতে চান?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:600;padding:1px 5px;font-size:10px;height:24px" title="Restore to pending">
                                            <i class="fa-solid fa-rotate-left"></i> রিস্টোর
                                        </button>
                                    </form>
                                @endif
                                @if(!in_array($adm->id, $paidIds) && ($adm->interestedCourse && $adm->interestedCourse->admission_fee > 0))
                                    <form method="POST" action="{{ route('admin.admissions.send-repayment-email', $adm) }}" style="display:inline;" onsubmit="return confirm('আবেদনকারীর কাছে রি-পেমেন্ট মেইল পাঠাতে চান?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#b45309;border-color:#fde68a;background:#fffbeb;padding:1px 5px;font-size:10px;height:24px" title="Send Re-Payment Email">
                                            <i class="fa-solid fa-paper-plane"></i> রি-পেমেন্ট
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.admissions.show', $adm) }}" class="btn btn-outline btn-sm" style="padding:1px 5px;font-size:10px;height:24px"><i class="fa-solid fa-eye"></i> ভিউ</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @if($tab === 'admin')<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-muted)">No admin-added admissions found.</td></tr>@endif
                    @endforelse
                @endif

                {{-- ── Public Form Applications ── --}}
                @if($tab !== 'admin' && $tab !== 'unpaid')
                    @forelse($publicApplications as $pub)
                    <tr>
                        <td>
                            <span class="badge no-dot" style="background:rgba(139,92,246,.1);color:#7c3aed;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-globe"></i> Public</span>
                            <div style="font-size:9px;color:var(--text-muted);margin-top:1px">{{ $pub->application_no }}</div>
                        </td>
                        <td class="td-primary">
                            <strong style="font-size:12px;color:#0f172a">{{ $pub->student->name ?? '—' }}</strong>
                            <div class="td-muted" style="font-size:10.5px">{{ $pub->student->phone ?? '—' }}</div>
                            @if($pub->waiver_code)
                                <span class="badge badge-active no-dot" style="font-size:9px;padding:1px 4px;margin-top:1px"><i class="fa-solid fa-gift"></i> {{ $pub->waiver_code }} ({{ $pub->discount_percent }}%)</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600;color:#0f172a">{{ $pub->interestedCourse->name ?? '—' }}</div>
                            <div class="td-muted" style="font-size:10px">{{ $pub->session->name ?? '—' }}</div>
                        </td>
                        <td class="td-muted" style="font-size:10.5px;white-space:nowrap">{{ $pub->created_at->format('d M Y') }}</td>
                        <td style="text-align:center">
                            @if(in_array($pub->id, $paidIds) || ($pub->interestedCourse && $pub->interestedCourse->admission_fee == 0))
                                <span class="badge badge-active" style="background:#dcfce7;color:#166534;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-circle-check"></i> Paid</span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-circle-xmark"></i> Unpaid</span>
                            @endif
                        </td>
                        <td>
                            @if($pub->status === 'PENDING')
                                <span class="badge badge-pending" style="font-size:9.5px;padding:1px 5px">Pending</span>
                            @elseif($pub->status === 'APPROVED')
                                <span class="badge badge-active" style="font-size:9.5px;padding:1px 5px">Approved</span>
                                <div style="font-size:9.5px;color:#15803d;margin-top:1px;">
                                    অনুমোদন: <strong>{{ $pub->reviewer->name ?? 'এডমিন' }}</strong>
                                </div>
                            @elseif($pub->status === 'REVIEWED')
                                <span class="badge badge-scheduled" style="font-size:9.5px;padding:1px 5px">Reviewed</span>
                            @elseif(in_array($pub->status, ['TRASH', 'REJECTED']))
                                <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-size:9.5px;padding:1px 5px"><i class="fa-solid fa-trash-can"></i> Trash</span>
                                <div style="font-size:9.5px;color:#991b1b;margin-top:1px;">
                                    পর্যালোচক: {{ $pub->reviewer->name ?? 'এডমিন' }}
                                </div>
                            @else
                                <span class="badge badge-pending" style="font-size:9.5px;padding:1px 5px">{{ $pub->status }}</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <div style="display:inline-flex;align-items:center;gap:3px">
                                @if(in_array($pub->status, ['TRASH', 'REJECTED']))
                                    <form method="POST" action="{{ route('admin.admissions.untrash', $pub) }}" style="display:inline;" onsubmit="return confirm('আবেদনটি কি ট্র্যাশ থেকে পুনরুদ্ধার (Untrash) করতে চান?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:600;padding:1px 5px;font-size:10px;height:24px" title="Restore to pending">
                                            <i class="fa-solid fa-rotate-left"></i> রিস্টোর
                                        </button>
                                    </form>
                                @endif
                                @if(!in_array($pub->id, $paidIds) && ($pub->interestedCourse && $pub->interestedCourse->admission_fee > 0))
                                    <form method="POST" action="{{ route('admin.admissions.send-repayment-email', $pub) }}" style="display:inline;" onsubmit="return confirm('আবেদনকারীর কাছে রি-পেমেন্ট মেইল পাঠাতে চান?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#b45309;border-color:#fde68a;background:#fffbeb;padding:1px 5px;font-size:10px;height:24px" title="Send Re-Payment Email">
                                            <i class="fa-solid fa-paper-plane"></i> রি-পেমেন্ট
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.admissions.show', $pub) }}" class="btn btn-outline btn-sm" style="padding:1px 5px;font-size:10px;height:24px"><i class="fa-solid fa-eye"></i> ভিউ</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @if($tab === 'public')<tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-muted)">No public applications found.</td></tr>@endif
                    @endforelse
                @endif

                @if($tab === 'all' && $adminAdmissions->isEmpty() && $publicApplications->isEmpty())
                <tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-muted)">No applications found.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <script>
    function toggleReportSection() {
        const body = document.getElementById('report-content-body');
        const chevron = document.getElementById('report-chevron');
        const text = document.getElementById('report-toggle-text');
        if (!body) return;
        if (body.style.display === 'none' || body.style.display === '') {
            body.style.display = 'block';
            if (chevron) chevron.className = 'fa-solid fa-chevron-up';
            if (text) text.innerText = 'রিপোর্ট লুকান';
        } else {
            body.style.display = 'none';
            if (chevron) chevron.className = 'fa-solid fa-chevron-down';
            if (text) text.innerText = 'রিপোর্ট দেখুন';
        }
    }

    // Synchronize Top Scrollbar with Table Wrapper
    document.addEventListener('DOMContentLoaded', function() {
        const topScroll = document.getElementById('table-scroll-top-bar');
        const topDummy = document.getElementById('table-scroll-top-dummy');
        const mainScroll = document.getElementById('admissions-table-wrapper');
        if (topScroll && topDummy && mainScroll) {
            function syncScrollWidth() {
                if (mainScroll.scrollWidth > mainScroll.clientWidth + 2) {
                    topScroll.style.display = 'block';
                    topDummy.style.width = mainScroll.scrollWidth + 'px';
                } else {
                    topScroll.style.display = 'none';
                }
            }
            syncScrollWidth();
            window.addEventListener('resize', syncScrollWidth);

            let isSyncingTop = false;
            let isSyncingMain = false;

            topScroll.addEventListener('scroll', function() {
                if (!isSyncingTop) {
                    isSyncingMain = true;
                    mainScroll.scrollLeft = topScroll.scrollLeft;
                }
                isSyncingTop = false;
            });

            mainScroll.addEventListener('scroll', function() {
                if (!isSyncingMain) {
                    isSyncingTop = true;
                    topScroll.scrollLeft = mainScroll.scrollLeft;
                }
                isSyncingMain = false;
            });
        }
    });
    </script>
</x-admin-layout>
