<x-student-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Welcome, {{ auth()->user()->name ?? 'Student' }}!</h1>
            <p>{{ now()->format('l, d M Y') }} — Your learning overview</p>
        </div>
    </div>

    @if(isset($student->has_course_access) && !$student->has_course_access)
        <div style="background:#fef2f2;border:1.5px solid #f87171;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;font-family:'Kalpurush',sans-serif;box-shadow:0 2px 4px rgba(220,38,38,0.06)">
            <div style="display:flex;align-items:center;gap:14px">
                <div style="width:44px;height:44px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:700;color:#991b1b">⚠️ কোর্স ও ক্লাসরুম অ্যাক্সেস সাময়িকভাবে স্থগিত (Portal Access Suspended)</div>
                    <div style="font-size:13px;color:#7f1d1d;margin-top:2px;line-height:1.5">
                        বকেয়া বেতন এবং কোর্স এক্টিভেশন ফি প্রযোজ্য হওয়ায় আপনার অ্যাকাডেমিক ক্লাসরুম ও কোর্সের এক্সেস অফ করা হয়েছে। <strong>বকেয়া ফি পরিশোধ করার সাথে সাথে তাৎক্ষণিকভাবে স্বয়ংক্রিয়ভাবে আপনার এক্সেস অটো অন হয়ে যাবে।</strong>
                    </div>
                </div>
            </div>
            <a href="{{ route('student.fees.index', ['tab' => 'dues']) }}" class="btn btn-sm" style="background:#dc2626;border-color:#b91c1c;color:#fff;font-weight:700;padding:10px 20px;border-radius:8px;font-size:13px;display:inline-flex;align-items:center;gap:8px;text-decoration:none">
                <i class="fa-solid fa-credit-card"></i> বকেয়া ফি পরিশোধ করুন
            </a>
        </div>
    @endif

    {{-- ── TODAY'S CLASS REMINDER & JOINING GUIDE BANNER ── --}}
    @if(isset($todayClassesForReminder) && $todayClassesForReminder->isNotEmpty())
        <div style="background:linear-gradient(135deg, #022c22 0%, #064e3b 60%, #047857 100%);color:#ffffff;border-radius:14px;padding:22px 24px;margin-bottom:24px;box-shadow:0 8px 24px rgba(4,120,87,0.22);border:1px solid #34d399;font-family:'Kalpurush',sans-serif">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,0.18);color:#fbbf24;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:800;color:#ffffff;display:flex;align-items:center;gap:8px">
                            <span>আজকের ক্লাস রিমাইন্ডার (Today's Scheduled Classes)</span>
                            <span class="badge" style="background:#fbbf24;color:#022c22;font-size:11px;font-weight:800;padding:2px 8px;border-radius:12px">
                                মোট {{ $todayClassesForReminder->count() }}টি ক্লাস
                            </span>
                        </div>
                        <div style="font-size:13px;color:#a7f3d0;margin-top:2px">
                            {{ now()->format('l, d F Y') }} — নির্ধারিত সময়ে ক্লাসে উপস্থিত থাকুন এবং হাজিরা নিশ্চিত করুন।
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('student.classes.today') }}" class="btn" style="background:#fbbf24;color:#022c22;font-weight:800;padding:10px 22px;border-radius:8px;font-size:13.5px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2)">
                        <i class="fa-solid fa-video"></i> আজকের ক্লাসরুমে প্রবেশ করুন →
                    </a>
                </div>
            </div>

            <div style="margin-top:18px;display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:12px">
                @foreach($todayClassesForReminder as $tc)
                    <div style="background:rgba(255,255,255,0.1);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,0.16);border-radius:10px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:12px">
                        <div>
                            <div style="font-size:15px;font-weight:700;color:#ffffff;display:flex;align-items:center;gap:6px">
                                <span>📖 {{ $tc->subject?->name ?? 'নির্ধারিত বিষয়' }}</span>
                                @if($tc->is_extra)
                                    <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:1px 6px;border-radius:4px">এক্সট্রা</span>
                                @endif
                            </div>
                            <div style="font-size:12.5px;color:#fcd34d;font-weight:700;margin-top:3px">
                                🕒 সময়: {{ $tc->start_time ? \Carbon\Carbon::parse($tc->start_time)->format('h:i A') : 'TBA' }}
                                @if($tc->routineEntry?->slot) ({{ $tc->routineEntry->slot->name }}) @endif
                            </div>
                            <div style="font-size:11.5px;color:#d1fae5;margin-top:2px">
                                👨‍🏫 {{ $tc->teacher?->name ?? 'অনলাইন শিক্ষক' }} · {{ $tc->batch?->name ?? '' }}
                            </div>
                        </div>
                        <div>
                            @if($tc->meeting_link && in_array($tc->status, ['SCHEDULED', 'RUNNING']))
                                <a href="{{ route('student.classes.join', $tc) }}" target="_blank" class="btn btn-sm" style="background:#10b981;color:#ffffff;font-weight:700;padding:8px 16px;border-radius:6px;font-size:12px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;white-space:nowrap">
                                    <i class="fa-solid fa-play"></i> Join Now
                                </a>
                            @else
                                <a href="{{ route('student.classes.show', $tc) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:#ffffff;font-weight:700;padding:8px 14px;border-radius:6px;font-size:12px;text-decoration:none;white-space:nowrap">
                                    বিবরণ →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Step-by-step join guide --}}
            <div style="margin-top:16px;background:rgba(0,0,0,0.18);border-radius:8px;padding:12px 16px;font-size:12px;color:#ecfdf5;line-height:1.6;display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-circle-info" style="font-size:18px;color:#fbbf24;flex-shrink:0"></i>
                <div>
                    <strong>📌 ক্লাসে যোগদানের পদ্ধতি:</strong> "Join Now" বা আজকের ক্লাসে প্রবেশ করে নির্ধারিত ক্লাসের পাশের বাটনে ক্লিক করলেই স্বয়ংক্রিয়ভাবে আপনার হাজিরা (Attendance) রেকর্ড হয়ে জুম/মিট ক্লাসরুম ওপেন হবে।
                </div>
            </div>
        </div>
    @endif

    <!-- Student Stats -->
    <div class="stats-grid">
        <div class="stat-card">

            <div class="stat-info">
                <div class="stat-value">{{ $stats['enrolled_courses'] }}</div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
        </div>
        <div class="stat-card">

            <div class="stat-info">
                <div class="stat-value">{{ $stats['upcoming_classes'] }}</div>
                <div class="stat-label">Upcoming Classes</div>
            </div>
        </div>
        <div class="stat-card">

            <div class="stat-info">
                <div class="stat-value">{{ $stats['attendance_percent'] }}%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
        </div>
        <div class="stat-card">

            <div class="stat-info">
                <div class="stat-value">{{ $stats['upcoming_exams'] }}</div>
                <div class="stat-label">Upcoming Exams</div>
            </div>
        </div>
    </div>


    {{-- ── 2. DUES, PAID, HISTORY & VOUCHERS SECTION (ডিউ পেমেন্ট, পেইড, হিস্ট্রি ও ভাউচার সেকশন) ── --}}
    <div class="card" style="margin-bottom:24px;border-top:4px solid #059669;font-family:'Kalpurush',sans-serif">
        <div class="card-header"
            style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
            <div>
                <span class="card-title" style="display:flex;align-items:center;gap:8px;font-size:16px;color:#065f46">
                    <i class="fa-solid fa-file-invoice-dollar" style="color:#059669"></i> ডিউ পেমেন্ট, পরিশোধিত ফি ও
                    ভাউচার (Dues & Payment Vouchers)
                </span>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    বকেয়া ফি পরিশোধ, পরিশোধিত ইনভয়েস তালিকা এবং অফিসিয়াল মানি রসিদ / ভাউচার
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <a href="{{ route('student.fees.index', ['tab' => 'dues']) }}" class="btn btn-outline btn-sm"
                    style="font-size:12px;color:#047857;border-color:#10b981;font-weight:700">
                    <i class="fa-solid fa-receipt"></i> সম্পূর্ণ একাউন্টস লেজার ও রসিদ →
                </a>
            </div>
        </div>

        {{-- Financial Summary KPI Chips --}}
        <div style="padding:14px 18px 0 18px">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px">
                <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;padding:10px 14px">
                    <div style="font-size:11px;color:#9f1239;font-weight:700">সর্বমোট বকেয়া (Total Outstanding Due)
                    </div>
                    <div style="font-size:20px;font-weight:800;color:#be123c;margin-top:2px">
                        ৳{{ number_format($totalOverallDue ?? 0, 2) }}
                    </div>
                </div>
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:10px 14px">
                    <div style="font-size:11px;color:#065f46;font-weight:700">সর্বমোট পরিশোধিত (Total Paid)</div>
                    <div style="font-size:20px;font-weight:800;color:#047857;margin-top:2px">
                        ৳{{ number_format($totalOverallPaid ?? 0, 2) }}
                    </div>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:10px 14px">
                    <div style="font-size:11px;color:#1e40af;font-weight:700">পরিশোধিত ইনভয়েস সংখ্যা</div>
                    <div style="font-size:20px;font-weight:800;color:#2563eb;margin-top:2px">
                        {{ isset($paidInvoices) ? $paidInvoices->count() : 0 }}টি
                    </div>
                </div>
            </div>
        </div>

        <div style="padding:16px 18px;display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:18px">
            {{-- Outstanding Dues List --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px">
                <div
                    style="font-size:13.5px;font-weight:700;color:#1e293b;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
                    <span><i class="fa-solid fa-clock" style="color:#e11d48"></i> বর্তমান বকেয়া ফি সমূহ (Due
                        List)</span>
                    <span class="badge badge-danger no-dot"
                        style="font-size:11px">{{ isset($dueInvoices) ? $dueInvoices->count() : 0 }}টি</span>
                </div>
                @if(isset($dueInvoices) && $dueInvoices->count() > 0)
                    <div style="display:flex;flex-direction:column;gap:8px">
                        @foreach($dueInvoices->take(4) as $dInv)
                            <div
                                style="background:#fff;border:1px solid #fee2e2;border-radius:8px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <div style="font-size:12.5px;font-weight:700;color:#0f172a">{{ $dInv->title }}</div>
                                    <div style="font-size:11px;color:#64748b">ইনভয়েস: {{ $dInv->invoice_no }}</div>
                                </div>
                                <div style="text-align:right">
                                    <div style="font-size:14px;font-weight:800;color:#dc2626">
                                        ৳{{ number_format($dInv->due_amount, 2) }}</div>
                                    <a href="{{ route('student.fees.index') }}"
                                        style="font-size:11px;color:#2563eb;font-weight:700;text-decoration:none">
                                        পে করুন →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        style="text-align:center;padding:16px;color:#047857;background:#fff;border-radius:8px;font-size:12.5px">
                        <i class="fa-solid fa-circle-check"
                            style="font-size:20px;color:#10b981;display:block;margin-bottom:6px"></i>
                        আলহামদুলিল্লাহ! আপনার কোনো বকেয়া ফি নেই।
                    </div>
                @endif
            </div>

            {{-- Recent Vouchers / Payment History --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px">
                <div
                    style="font-size:13.5px;font-weight:700;color:#1e293b;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
                    <span><i class="fa-solid fa-receipt" style="color:#059669"></i> পেমেন্ট হিস্ট্রি ও ভাউচার
                        (Receipts)</span>
                    <span class="badge badge-success no-dot"
                        style="font-size:11px">{{ isset($recentVoucherPayments) ? $recentVoucherPayments->count() : 0 }}টি</span>
                </div>
                @if(isset($recentVoucherPayments) && $recentVoucherPayments->count() > 0)
                    <div style="display:flex;flex-direction:column;gap:8px">
                        @foreach($recentVoucherPayments->take(4) as $rPay)
                            <div
                                style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <div style="font-size:12.5px;font-weight:700;color:#0f172a">
                                        {{ $rPay->invoice->title ?? 'ফি পরিশোধ' }}</div>
                                    <div style="font-size:11px;color:#64748b">
                                        রসিদ: {{ $rPay->payment_no }} · {{ $rPay->payment_method }}
                                        @if($rPay->sender_number)
                                            · বিকাশ: {{ $rPay->sender_number }}
                                        @endif
                                    </div>
                                </div>
                                <div style="text-align:right">
                                    <div style="font-size:13.5px;font-weight:800;color:#047857">
                                        ৳{{ number_format($rPay->amount, 2) }}</div>
                                    @if(($rPay->status ?? 'APPROVED') === 'APPROVED')
                                        <a href="{{ route('student.fees.receipt', $rPay) }}" target="_blank"
                                            style="font-size:11px;color:#047857;font-weight:700;text-decoration:none">
                                            <i class="fa-solid fa-print"></i> ভাউচার
                                        </a>
                                    @else
                                        <span style="font-size:10.5px;color:#b45309">অপেক্ষমান</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        style="text-align:center;padding:16px;color:#94a3b8;background:#fff;border-radius:8px;font-size:12.5px">
                        এখনও কোনো পেমেন্ট রেকর্ড নেই।
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Central Notice Board Widget --}}
    @if(isset($notices) && $notices->count() > 0)
        <div class="card" style="margin-bottom:24px;border-top:4px solid #047857">
            <div class="card-header">
                <span class="card-title">Notice Board &amp; Announcements</span>
            </div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:12px">
                @foreach($notices as $n)
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                            <strong style="font-size:14px;color:#0f172a">{{ $n->title }}</strong>
                            <span
                                class="badge {{ $n->priority === 'URGENT' ? 'badge-danger' : ($n->priority === 'IMPORTANT' ? 'badge-warning' : 'badge-info') }} no-dot"
                                style="font-size:10px">
                                {{ $n->priority }}
                            </span>
                        </div>
                        <div style="font-size:13px;color:#334155;line-height:1.5;margin-bottom:6px">
                            {{ $n->content }}
                        </div>
                        <div style="font-size:11px;color:#64748b">
                            Published {{ $n->created_at->diffForHumans() }} ({{ $n->created_at->format('d M Y') }})
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid-2">
        <!-- Recent Sessions / Covered Modules -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Subjects & Coverage</span>
                <a href="{{ route('student.classes.index') }}" class="btn btn-ghost btn-sm">All Classes</a>
            </div>
            <div style="padding:0">
                @forelse($currentModules as $cs)
                    <div
                        style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--card-border)">
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600">{{ $cs->subject?->name ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                {{ $cs->session_date?->format('d M Y (D)') ?? 'TBA' }}
                                @if($cs->moduleCovered) · <i class="fa-solid fa-book"></i> {{ $cs->moduleCovered->title }}
                                @endif
                            </div>
                        </div>
                        @php $badge = match ($cs->status) { 'COMPLETED' => 'badge-success', 'SCHEDULED' => 'badge-info', 'CANCELLED' => 'badge-danger', default => 'badge-warning'}; @endphp
                        <span class="badge {{ $badge }} no-dot">{{ $cs->status }}</span>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No class sessions yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Upcoming Classes</span>
                <a href="{{ route('student.classes.index') }}" class="btn btn-ghost btn-sm">All →</a>
            </div>
            <div style="padding:0">
                @forelse($upcomingClasses as $class)
                    <div
                        style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--card-border)">
                        <div
                            style="width:38px;height:38px;background:#f5f3ff;border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;color:#8b5cf6;font-size:12px">
                            <span>{{ $class->session_date?->format('d') }}</span>
                            <span style="font-size:9px">{{ $class->session_date?->format('M') }}</span>
                        </div>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600">{{ $class->subject?->name ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                {{ $class->batch?->name ?? '' }}
                                @if($class->routineEntry?->slot) · {{ $class->routineEntry->slot->name }} @endif
                                · {{ $class->teacher?->name ?? '—' }}
                            </div>
                        </div>
                        @if($class->meeting_link)
                            <a href="{{ route('student.classes.join', $class) }}" target="_blank"
                                class="btn btn-primary btn-sm"><i class="fa-solid fa-video"></i> Join</a>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No upcoming classes</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Results -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Results</span>
                <a href="{{ route('student.results.index') }}" class="btn btn-ghost btn-sm">All Results</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentResults as $result)
                            <tr>
                                <td class="td-primary">{{ $result->exam->subject->name ?? '—' }}</td>
                                <td>{{ $result->marks ?? '—' }}/{{ $result->exam->full_marks ?? '—' }}</td>
                                <td><strong>{{ $result->grade ?? '—' }}</strong></td>
                                <td><span
                                        class="badge badge-{{ strtolower($result->status ?? 'secondary') }}">{{ ucfirst(strtolower($result->status ?? 'N/A')) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center;padding:20px;color:var(--text-muted)">No results
                                    yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Exams -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Upcoming Exams</span>
                <a href="{{ route('student.exams.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div style="padding:0">
                @forelse($upcomingExamsList as $exam)
                    <div
                        style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--card-border)">
                        <div
                            style="width:40px;height:40px;background:#fff7ed;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;font-weight:700;color:#f59e0b">
                            {{ \Carbon\Carbon::parse($exam->exam_date)->format('d') }}<br><span
                                style="font-size:9px">{{ \Carbon\Carbon::parse($exam->exam_date)->format('M') }}</span>
                        </div>
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600">{{ $exam->subject->name ?? '—' }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $exam->title }} ·
                                {{ ucfirst(strtolower($exam->type)) }}</div>
                        </div>
                        <span class="badge badge-scheduled">Scheduled</span>
                    </div>
                @empty
                    <div class="empty-state">
                        <p>No upcoming exams</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Learning Resources & Study Materials Widget -->
        <div class="card" style="font-family:'Kalpurush',sans-serif">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                <span class="card-title" style="display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-book-bookmark" style="color:#047857"></i>
                    <span>লার্নিং রিসোর্স ও স্টাডি ম্যাটেরিয়াল</span>
                </span>
                <a href="{{ route('student.resources.index') }}" class="btn btn-ghost btn-sm" style="font-size:12px;font-weight:700">সকল রিসোর্স দেখুন →</a>
            </div>
            <div style="padding:0">
                @forelse($latestResources ?? [] as $res)
                    @php
                        $type = strtoupper($res['type'] ?? 'LINK');
                        $iconClass = match($type) {
                            'PDF'        => 'fa-file-pdf',
                            'SLIDES'     => 'fa-file-powerpoint',
                            'DRIVE'      => 'fa-google-drive',
                            'VIDEO'      => 'fa-video',
                            'ATTACHMENT' => 'fa-paperclip',
                            default      => 'fa-link',
                        };
                        $iconColor = match($type) {
                            'PDF'        => '#dc2626',
                            'SLIDES'     => '#ea580c',
                            'DRIVE'      => '#2563eb',
                            'VIDEO'      => '#7c3aed',
                            'ATTACHMENT' => '#059669',
                            default      => '#475569',
                        };
                    @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 18px;border-bottom:1px solid var(--card-border)">
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="width:36px;height:36px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:{{ $iconColor }};font-size:16px">
                                <i class="fa-solid {{ $iconClass }}"></i>
                            </div>
                            <div>
                                <div style="font-size:13.5px;font-weight:700;color:#1e293b">{{ $res['title'] }}</div>
                                <div style="font-size:11.5px;color:var(--text-muted)">{{ $res['subject_name'] }} · {{ $type }}</div>
                            </div>
                        </div>
                        <div>
                            @if(!empty($res['url']) && $res['url'] !== '#')
                                <a href="{{ $res['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm" style="border-color:#047857;color:#047857;font-weight:700;padding:4px 10px;font-size:11.5px">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> ওপেন
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding:24px;text-align:center">
                        <p style="margin:0;font-size:13px;color:#94a3b8">কোনো লার্নিং রিসোর্স পাওয়া যায়নি</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</x-student-layout>