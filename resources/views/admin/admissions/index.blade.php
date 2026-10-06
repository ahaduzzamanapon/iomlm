<x-admin-layout>
    <x-slot name="title">Admissions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Admission Management</h1>
            <p>Admin-added এবং Public Form থেকে আসা সকল আবেদন এখানে দেখুন</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.admissions.create') }}" class="btn btn-primary">
                New Admission
            </a>
        </div>
    </div>

    {{-- ── Course & Session-wise Admission Statistics Report Section ── --}}
    <div class="card" style="margin-bottom:16px;border:1px solid #cbd5e1;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.05);font-family:'Kalpurush',sans-serif">
        <div class="card-header" style="background:#f8fafc;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;cursor:pointer" onclick="toggleReportSection()">
            <div style="display:flex;align-items:center;gap:10px">
                <span style="background:#047857;color:#fff;width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>
                <div>
                    <span style="font-weight:700;font-size:15px;color:#0f172a">কোর্স ও সেশন ভিত্তিক ভর্তি রিপোর্ট (Admission Statistics Report)</span>
                    <span style="font-size:11.5px;color:#64748b;display:block">মোট {{ $reportTotalApps }} টি আবেদনের কোর্স ও সেশন ভিত্তিক সারসংক্ষেপ ও পরিসংখ্যান</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge badge-secondary no-dot" style="font-size:12px;padding:4px 10px">{{ $admissionReport->count() }} টি গ্রুপ</span>
                <button type="button" id="btn-toggle-report" class="btn btn-outline btn-sm" style="font-size:12px;padding:4px 10px" onclick="event.stopPropagation(); toggleReportSection();">
                    <i class="fa-solid fa-chevron-down" id="report-chevron"></i> <span id="report-toggle-text">রিপোর্ট দেখুন</span>
                </button>
            </div>
        </div>

        <div id="report-content-body" style="display:none;padding:16px;border-top:1px solid #e2e8f0;background:#ffffff">
            {{-- Quick Summary Mini Cards --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:12px;margin-bottom:16px">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px">
                    <div style="font-size:11.5px;color:#166534;font-weight:600">মোট আবেদন (Total Applications)</div>
                    <div style="font-size:22px;font-weight:800;color:#047857">{{ $reportTotalApps }}</div>
                </div>
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:12px">
                    <div style="font-size:11.5px;color:#065f46;font-weight:600">অনুমোদিত (Approved)</div>
                    <div style="font-size:22px;font-weight:800;color:#059669">{{ $reportApprovedApps }}</div>
                </div>
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px">
                    <div style="font-size:11.5px;color:#92400e;font-weight:600">অপেক্ষমাণ (Pending)</div>
                    <div style="font-size:22px;font-weight:800;color:#d97706">{{ $reportPendingApps }}</div>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px">
                    <div style="font-size:11.5px;color:#1e40af;font-weight:600">ভাই শাখা (পুরুষ - Male)</div>
                    <div style="font-size:22px;font-weight:800;color:#2563eb">{{ $reportMaleApps }}</div>
                </div>
                <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:8px;padding:12px">
                    <div style="font-size:11.5px;color:#9d174d;font-weight:600">বোন শাখা (মহিলা - Female)</div>
                    <div style="font-size:22px;font-weight:800;color:#db2777">{{ $reportFemaleApps }}</div>
                </div>
            </div>

            {{-- Breakdown Table --}}
            <div style="overflow-x:auto">
                <table class="table" style="font-size:12.5px;margin:0">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th style="width:45px">ক্রম</th>
                            <th>কোর্সের নাম (Course)</th>
                            <th>শিক্ষাবর্ষ ও সেশন (Session &amp; Academic Year)</th>
                            <th style="text-align:center">মোট আবেদন</th>
                            <th style="text-align:center">অনুমোদিত</th>
                            <th style="text-align:center">অপেক্ষমাণ</th>
                            <th style="text-align:center">ভাই শাখা</th>
                            <th style="text-align:center">বোন শাখা</th>
                            <th style="text-align:right">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissionReport as $idx => $r)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $r->interestedCourse->name ?? 'অনির্ধারিত কোর্স' }}</strong>
                            </td>
                            <td>
                                @if($r->session)
                                    <span style="font-weight:600;color:#0f172a">{{ $r->session->name }}</span>
                                    @if($r->session->academicYear)
                                        <span style="color:#047857;font-size:11.5px">({{ $r->session->academicYear->name }})</span>
                                    @endif
                                @else
                                    <span style="color:#94a3b8">সাধারণ / অনির্ধারিত সেশন</span>
                                @endif
                            </td>
                            <td style="text-align:center;font-weight:700">
                                <span class="badge badge-secondary no-dot" style="font-size:12px">{{ $r->total_apps }}</span>
                            </td>
                            <td style="text-align:center;color:#059669;font-weight:700">{{ $r->approved_count }}</td>
                            <td style="text-align:center;color:#d97706;font-weight:700">{{ $r->pending_count }}</td>
                            <td style="text-align:center;color:#2563eb;font-weight:600">{{ $r->male_count }}</td>
                            <td style="text-align:center;color:#db2777;font-weight:600">{{ $r->female_count }}</td>
                            <td style="text-align:right">
                                <a href="?tab={{ $tab }}&course_id={{ $r->interested_course_id }}&session_id={{ $r->academic_session_id }}"
                                   class="btn btn-outline btn-sm" style="font-size:11px;padding:3px 8px;border-radius:6px" title="এই কোর্স ও সেশনের আবেদনগুলো ফিল্টার করুন">
                                    <i class="fa-solid fa-filter"></i> ফিল্টার
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align:center;color:#94a3b8;padding:16px">কোনো তথ্য পাওয়া যায়নি।</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    @php
        $tab    = request('tab', 'all');
        $status = request('status');
    @endphp
    <div class="tabs" style="margin-bottom:0">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'all']) }}"    class="tab-item {{ $tab === 'all'    ? 'active' : '' }}">All
            <span class="badge badge-secondary no-dot" style="margin-left:4px">{{ $totalCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'admin']) }}"  class="tab-item {{ $tab === 'admin'  ? 'active' : '' }}">
            <i class="fa-solid fa-building-columns"></i> Admin Added
            <span class="badge badge-secondary no-dot" style="margin-left:4px">{{ $adminCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'public']) }}" class="tab-item {{ $tab === 'public' ? 'active' : '' }}">
            <i class="fa-solid fa-globe"></i> Public Form
            <span class="badge badge-secondary no-dot" style="margin-left:4px;background:rgba(139,92,246,.15);color:#7c3aed">{{ $publicCount }}</span>
            @if($publicPending > 0)<span class="badge no-dot" style="background:#ef4444;color:#fff;margin-left:4px">{{ $publicPending }}</span>@endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'unpaid']) }}" class="tab-item {{ $tab === 'unpaid' ? 'active' : '' }}">
            <i class="fa-solid fa-credit-card"></i> Unpaid (অপরিশোধিত)
            <span class="badge badge-secondary no-dot" style="margin-left:4px;background:#fef3c7;color:#b45309">{{ $unpaidCount ?? 0 }}</span>
        </a>
    </div>

    {{-- Advanced Filter Bar (Status, Course, Session, Gender, Search) --}}
    <div style="background:var(--card-bg);border:1px solid var(--card-border);border-top:0;border-radius:0 0 8px 8px;padding:12px 16px;margin-bottom:16px;font-family:'Kalpurush',sans-serif">
        <form method="GET" action="{{ route('admin.admissions.index') }}" id="admissionFilterForm" style="display:flex;flex-direction:column;gap:10px">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                {{-- Status Pills --}}
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                    <span style="font-size:12px;color:var(--text-muted);font-weight:700">স্ট্যাটাস:</span>
                    @foreach([''=>'সকল স্ট্যাটাস (All)','PENDING'=>'Pending','APPROVED'=>'Approved','TRASH'=>'Trash'] as $s => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $s]) }}"
                       style="font-size:11.5px;padding:3px 10px;border-radius:16px;text-decoration:none;border:1px solid var(--card-border);display:inline-flex;align-items:center;gap:4px;
                              {{ ($status === $s || ($s === 'TRASH' && $status === 'REJECTED')) ? 'background:var(--blue);color:#fff;border-color:var(--blue);font-weight:700' : 'color:var(--text-secondary);background:#fff' }}">
                        @if($s === 'TRASH') <i class="fa-solid fa-trash-can" style="font-size:10px"></i> @endif
                        {{ $label }}
                    </a>
                    @endforeach
                    <input type="hidden" name="status" value="{{ $status }}">
                </div>

                {{-- Active filter clear link --}}
                @if(request('course_id') || request('session_id') || request('gender') || request('search') || request('status'))
                <a href="?tab={{ $tab }}" style="font-size:11.5px;color:#dc2626;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                    <i class="fa-solid fa-rotate-left"></i> সকল ফিল্টার রিসেট
                </a>
                @endif
            </div>

            {{-- 3 Requested Filters: Course, Session, Gender + Search --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)) auto;gap:8px;align-items:center">
                {{-- 1. Course Filter --}}
                <div>
                    <select name="course_id" class="form-control" style="width:100%;height:34px;font-size:12px;padding:4px 8px" onchange="this.form.submit()">
                        <option value="">-- সকল কোর্স (Course) --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ ($courseId ?? request('course_id')) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Session Filter (with Academic Year) --}}
                <div>
                    <select name="session_id" class="form-control" style="width:100%;height:34px;font-size:12px;padding:4px 8px" onchange="this.form.submit()">
                        <option value="">-- সকল সেশন (Session) --</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ ($sessionId ?? request('session_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}{{ $s->academicYear ? ' (' . $s->academicYear->name . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Gender Filter (ভাই শাখা / বোন শাখা) --}}
                <div>
                    <select name="gender" class="form-control" style="width:100%;height:34px;font-size:12px;padding:4px 8px" onchange="this.form.submit()">
                        <option value="">-- সকল শাখা / লিঙ্গ --</option>
                        <option value="Male" {{ ($gender ?? request('gender')) === 'Male' ? 'selected' : '' }}>ভাই শাখা (পুরুষ)</option>
                        <option value="Female" {{ ($gender ?? request('gender')) === 'Female' ? 'selected' : '' }}>বোন শাখা (মহিলা)</option>
                    </select>
                </div>

                {{-- Search Box --}}
                <div>
                    <input type="text" name="search" value="{{ $search ?? request('search') }}" class="form-control" placeholder="নাম, মোবাইল, আবেদন নং..." style="width:100%;height:34px;font-size:12px;padding:4px 10px">
                </div>

                {{-- Filter Action Buttons --}}
                <div style="display:flex;gap:4px">
                    <button type="submit" class="btn btn-primary btn-sm" style="height:34px;padding:0 14px;font-size:12px;display:inline-flex;align-items:center;gap:5px">
                        <i class="fa-solid fa-filter"></i> ফিল্টার
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Applicant</th>
                        <th>Course / Session</th>
                        <th>Applied On</th>
                        <th>Payment</th>
                        <th>Status &amp; Reviewer</th>
                        <th style="text-align:right">Action</th>
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
                                <span class="badge no-dot" style="background:rgba(239,68,68,.1);color:#dc2626;font-size:11px"><i class="fa-solid fa-clock"></i> Unpaid</span>
                                <div style="font-size:10px;color:var(--text-muted);margin-top:2px">{{ $unp->application_no }}</div>
                            </td>
                            <td class="td-primary">
                                <strong>{{ $unp->student->name ?? $unp->applicant_name }}</strong>
                                <div class="td-muted">{{ $unp->student->phone ?? $unp->phone }}</div>
                            </td>
                            <td style="font-size:12px">
                                {{ $unp->interestedCourse->name ?? '—' }}
                                <div class="td-muted">৳ {{ number_format($unp->interestedCourse->admission_fee ?? 0, 0) }}</div>
                            </td>
                            <td class="td-muted">{{ $unp->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="badge" style="background:#fee2e2;color:#991b1b;"><i class="fa-solid fa-circle-xmark"></i> Unpaid</span>
                            </td>
                            <td>
                                @if($unp->status === 'APPROVED')
                                    <span class="badge badge-active">Approved</span>
                                @elseif(in_array($unp->status, ['TRASH', 'REJECTED']))
                                    <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;"><i class="fa-solid fa-trash-can"></i> Trash</span>
                                @else
                                    <span class="badge badge-pending">Pending</span>
                                @endif
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                @if(in_array($unp->status, ['TRASH', 'REJECTED']))
                                    <form method="POST" action="{{ route('admin.admissions.untrash', $unp) }}" style="display:inline;" onsubmit="return confirm('আবেদনটি কি ট্র্যাশ থেকে পুনরুদ্ধার (Untrash) করতে চান?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:600;" title="Restore to pending">
                                            <i class="fa-solid fa-rotate-left"></i> Untrash
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.admissions.send-repayment-email', $unp) }}" style="display:inline;" onsubmit="return confirm('আবেদনকারীর কাছে রি-পেমেন্ট মেইল পাঠাতে চান?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm" style="color:#b45309;border-color:#fde68a;background:#fffbeb;" title="Send Re-Payment Email">
                                        <i class="fa-solid fa-paper-plane"></i> রি-পেমেন্ট মেইল পাঠান
                                    </button>
                                </form>
                                <a href="{{ route('admin.admissions.show', $unp) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--text-muted)">কোনো অপরিশোধিত (Unpaid) আবেদন পাওয়া যায়নি।</td></tr>
                        @endforelse
                    @endif

                    {{-- ── Admin-created Admissions ── --}}
                    @if($tab !== 'public' && $tab !== 'unpaid')
                        @forelse($adminAdmissions as $adm)
                        <tr>
                            <td>
                                <span class="badge no-dot" style="background:rgba(59,130,246,.1);color:#1d4ed8;font-size:11px"><i class="fa-solid fa-building-columns"></i> Admin</span>
                            </td>
                            <td class="td-primary">
                                <strong>{{ $adm->student->name ?? '—' }}</strong>
                                <div class="td-muted">{{ $adm->student->phone ?? '—' }}</div>
                                @if($adm->waiver_code)
                                    <span class="badge badge-active no-dot" style="font-size:10px;padding:2px 6px;margin-top:2px"><i class="fa-solid fa-gift"></i> Waiver: {{ $adm->waiver_code }} ({{ $adm->discount_percent }}%)</span>
                                @endif
                            </td>
                            <td style="font-size:12px">
                                {{ $adm->interestedCourse->name ?? '—' }}
                                <div class="td-muted">Attempt #{{ $adm->attempt_no }}</div>
                            </td>
                            <td class="td-muted">{{ $adm->created_at->format('d M Y') }}</td>
                            <td>
                                @if(in_array($adm->id, $paidIds) || ($adm->interestedCourse && $adm->interestedCourse->admission_fee == 0))
                                    <span class="badge badge-active" style="background:#dcfce7;color:#166534;"><i class="fa-solid fa-circle-check"></i> Paid</span>
                                @else
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;"><i class="fa-solid fa-circle-xmark"></i> Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($adm->status === 'PENDING')
                                    <span class="badge badge-pending">Pending</span>
                                @elseif($adm->status === 'APPROVED')
                                    <span class="badge badge-active">Approved</span>
                                    <div style="font-size:11px;color:#15803d;margin-top:2px;">
                                        অনুমোদনকারী: <strong>{{ $adm->reviewer->name ?? 'এডমিন' }}</strong>
                                    </div>
                                @elseif(in_array($adm->status, ['TRASH', 'REJECTED']))
                                    <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;"><i class="fa-solid fa-trash-can"></i> Trash</span>
                                    <div style="font-size:11px;color:#991b1b;margin-top:2px;">
                                        পর্যালোচক: {{ $adm->reviewer->name ?? 'এডমিন' }}
                                    </div>
                                @else
                                    <span class="badge badge-pending">{{ $adm->status }}</span>
                                @endif
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                @if(in_array($adm->status, ['TRASH', 'REJECTED']))
                                    <form method="POST" action="{{ route('admin.admissions.untrash', $adm) }}" style="display:inline;" onsubmit="return confirm('আবেদনটি কি ট্র্যাশ থেকে পুনরুদ্ধার (Untrash) করতে চান?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:600;" title="Restore to pending">
                                            <i class="fa-solid fa-rotate-left"></i> Untrash
                                        </button>
                                    </form>
                                @endif
                                @if(!in_array($adm->id, $paidIds) && ($adm->interestedCourse && $adm->interestedCourse->admission_fee > 0))
                                    <form method="POST" action="{{ route('admin.admissions.send-repayment-email', $adm) }}" style="display:inline;" onsubmit="return confirm('আবেদনকারীর কাছে রি-পেমেন্ট মেইল পাঠাতে চান?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#b45309;border-color:#fde68a;background:#fffbeb;" title="Send Re-Payment Email">
                                            <i class="fa-solid fa-paper-plane"></i> রি-পেমেন্ট মেইল
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.admissions.show', $adm) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                            </td>
                        </tr>
                        @empty
                        @if($tab === 'admin')<tr><td colspan="7" style="text-align:center;padding:28px;color:var(--text-muted)">No admin-added admissions found.</td></tr>@endif
                        @endforelse
                    @endif

                    {{-- ── Public Form Applications ── --}}
                    @if($tab !== 'admin' && $tab !== 'unpaid')
                        @forelse($publicApplications as $pub)
                        <tr>
                            <td>
                                <span class="badge no-dot" style="background:rgba(139,92,246,.1);color:#7c3aed;font-size:11px"><i class="fa-solid fa-globe"></i> Public</span>
                                <div style="font-size:10px;color:var(--text-muted);margin-top:2px">{{ $pub->application_no }}</div>
                            </td>
                            <td class="td-primary">
                                <strong>{{ $pub->student->name ?? '—' }}</strong>
                                <div class="td-muted">{{ $pub->student->phone ?? '—' }}</div>
                                @if($pub->waiver_code)
                                    <span class="badge badge-active no-dot" style="font-size:10px;padding:2px 6px;margin-top:2px"><i class="fa-solid fa-gift"></i> Waiver: {{ $pub->waiver_code }} ({{ $pub->discount_percent }}%)</span>
                                @endif
                            </td>
                            <td style="font-size:12px">
                                {{ $pub->interestedCourse->name ?? '—' }}
                                <div class="td-muted">{{ $pub->session->name ?? '—' }}</div>
                            </td>
                            <td class="td-muted">{{ $pub->created_at->format('d M Y') }}</td>
                            <td>
                                @if(in_array($pub->id, $paidIds) || ($pub->interestedCourse && $pub->interestedCourse->admission_fee == 0))
                                    <span class="badge badge-active" style="background:#dcfce7;color:#166534;"><i class="fa-solid fa-circle-check"></i> Paid</span>
                                @else
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;"><i class="fa-solid fa-circle-xmark"></i> Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($pub->status === 'PENDING')
                                    <span class="badge badge-pending">Pending</span>
                                @elseif($pub->status === 'APPROVED')
                                    <span class="badge badge-active">Approved</span>
                                    <div style="font-size:11px;color:#15803d;margin-top:2px;">
                                        অনুমোদনকারী: <strong>{{ $pub->reviewer->name ?? 'এডমিন' }}</strong>
                                    </div>
                                @elseif($pub->status === 'REVIEWED')
                                    <span class="badge badge-scheduled">Reviewed</span>
                                @elseif(in_array($pub->status, ['TRASH', 'REJECTED']))
                                    <span class="badge" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;"><i class="fa-solid fa-trash-can"></i> Trash</span>
                                    <div style="font-size:11px;color:#991b1b;margin-top:2px;">
                                        পর্যালোচক: {{ $pub->reviewer->name ?? 'এডমিন' }}
                                    </div>
                                @else
                                    <span class="badge badge-pending">{{ $pub->status }}</span>
                                @endif
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                @if(in_array($pub->status, ['TRASH', 'REJECTED']))
                                    <form method="POST" action="{{ route('admin.admissions.untrash', $pub) }}" style="display:inline;" onsubmit="return confirm('আবেদনটি কি ট্র্যাশ থেকে পুনরুদ্ধার (Untrash) করতে চান?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:600;" title="Restore to pending">
                                            <i class="fa-solid fa-rotate-left"></i> Untrash
                                        </button>
                                    </form>
                                @endif
                                @if(!in_array($pub->id, $paidIds) && ($pub->interestedCourse && $pub->interestedCourse->admission_fee > 0))
                                    <form method="POST" action="{{ route('admin.admissions.send-repayment-email', $pub) }}" style="display:inline;" onsubmit="return confirm('আবেদনকারীর কাছে রি-পেমেন্ট মেইল পাঠাতে চান?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#b45309;border-color:#fde68a;background:#fffbeb;" title="Send Re-Payment Email">
                                            <i class="fa-solid fa-paper-plane"></i> রি-পেমেন্ট মেইল পাঠান
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.admissions.show', $pub) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                            </td>
                        </tr>
                        @empty
                        @if($tab === 'public')<tr><td colspan="7" style="text-align:center;padding:28px;color:var(--text-muted)">No public applications found.</td></tr>@endif
                        @endforelse
                    @endif

                    @if($tab === 'all' && $adminAdmissions->isEmpty() && $publicApplications->isEmpty())
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No applications found.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
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
    </script>
</x-admin-layout>
