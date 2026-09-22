<x-student-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Welcome, {{ auth()->user()->name ?? 'Student' }}!</h1>
            <p>{{ now()->format('l, d M Y') }} — Your learning overview</p>
        </div>
    </div>

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

    {{-- ── 1. MONTHLY PAYMENTS SECTION (মান্থলি পেমেন্ট সেকশন) ── --}}
    @if((isset($dashboardMonthly) && count($dashboardMonthly) > 0) || (isset($studentCourses) && $studentCourses->count() > 0))
    <div class="card" style="margin-bottom:24px;border-top:4px solid #2563eb;font-family:'Kalpurush',sans-serif">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
            <div>
                <span class="card-title" style="display:flex;align-items:center;gap:8px;font-size:16px;color:#1e40af">
                    <i class="fa-solid fa-calendar-days" style="color:#2563eb"></i> {{ $runningSemesterName }} — মান্থলি পেমেন্ট (Monthly Fees Breakdown)
                </span>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    চলতি সেমিস্টারের মাসভিত্তিক কিস্তির অবস্থা ও পরিশোধের হিসাব
                </div>
            </div>

            {{-- Multi-Course Switcher in Dashboard --}}
            @if(isset($studentCourses) && $studentCourses->count() > 1)
                <div style="display:flex;align-items:center;gap:6px;background:#f8fafc;padding:4px 10px;border-radius:20px;border:1px solid #e2e8f0;flex-wrap:wrap">
                    <span style="font-size:11.5px;font-weight:700;color:#64748b">কোর্স নির্বাচন:</span>
                    @foreach($studentCourses as $sC)
                        <a href="{{ route('student.dashboard', ['course_id' => $sC->id]) }}"
                           style="padding:3px 10px;border-radius:14px;font-size:11.5px;font-weight:700;text-decoration:none;transition:all .15s;{{ ($selectedCourse && $selectedCourse->id == $sC->id) ? 'background:#2563eb;color:#fff;' : 'background:#fff;color:#475569;border:1px solid #cbd5e1' }}">
                            {{ $sC->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div style="display:flex;align-items:center;gap:10px">
                @if(!$hasRunningSemesterInvoices)
                    <span class="badge" style="background:#f1f5f9;color:#475569;font-size:12px;padding:4px 10px;border:1px solid #cbd5e1">
                        চলতি সেমিস্টার ফি নির্ধারিত নেই
                    </span>
                @elseif($runningSemDue > 0)
                    <span class="badge badge-danger no-dot" style="font-size:12px;padding:4px 10px">
                        চলতি সেমিস্টার বকেয়া: ৳{{ number_format($runningSemDue, 0) }}
                    </span>
                @else
                    <span class="badge badge-success no-dot" style="font-size:12px;padding:4px 10px">
                        চলতি সেমিস্টার পরিশোধিত (Cleared)
                    </span>
                @endif
                <a href="{{ route('student.fees.index', array_filter(['course_id' => $selectedCourse?->id, 'tab' => 'monthly'])) }}" class="btn btn-primary btn-sm" style="font-size:12px">
                    মান্থলি ফি পরিশোধ →
                </a>
            </div>
        </div>
        <div style="padding:14px 18px">
            @if(count($dashboardMonthly) > 0)
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));gap:10px">
                    @foreach($dashboardMonthly as $dm)
                        @php
                            $badgeStyle = match($dm['status']) {
                                'PAID' => 'background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;',
                                'PARTIAL' => 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
                                default => 'background:#fff1f2;color:#be123c;border:1px solid #fecdd3;',
                            };
                            $badgeText = match($dm['status']) {
                                'PAID' => 'পরিশোধিত',
                                'PARTIAL' => 'আংশিক',
                                default => 'অপরিশোধিত',
                            };
                        @endphp
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.02)">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                                <strong style="font-size:13px;color:#1e293b">{{ $dm['name'] }}</strong>
                                <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:12px;{{ $badgeStyle }}">
                                    {{ $badgeText }}
                                </span>
                            </div>
                            <div style="font-size:12px;color:#64748b;font-weight:600">
                                ফি: ৳{{ number_format($dm['rate'], 0) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:14px;color:#64748b;font-size:13px;background:#f8fafc;border-radius:8px;border:1px dashed #cbd5e1">
                    <i class="fa-solid fa-circle-info" style="color:#3b82f6;margin-right:4px"></i>
                    নির্বাচিত কোর্স @if($selectedCourse)<strong>'{{ $selectedCourse->name }}'</strong>@endif-এর চলতি সেমিস্টারের কোনো বকেয়া বা ফি শিডিউল নির্ধারিত নেই।
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── 2. DUES, PAID, HISTORY & VOUCHERS SECTION (ডিউ পেমেন্ট, পেইড, হিস্ট্রি ও ভাউচার সেকশন) ── --}}
    <div class="card" style="margin-bottom:24px;border-top:4px solid #059669;font-family:'Kalpurush',sans-serif">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
            <div>
                <span class="card-title" style="display:flex;align-items:center;gap:8px;font-size:16px;color:#065f46">
                    <i class="fa-solid fa-file-invoice-dollar" style="color:#059669"></i> ডিউ পেমেন্ট, পরিশোধিত ফি ও ভাউচার (Dues & Payment Vouchers)
                </span>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    বকেয়া ফি পরিশোধ, পরিশোধিত ইনভয়েস তালিকা এবং অফিসিয়াল মানি রসিদ / ভাউচার
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <a href="{{ route('student.fees.index', ['tab' => 'dues']) }}" class="btn btn-outline btn-sm" style="font-size:12px;color:#047857;border-color:#10b981;font-weight:700">
                    <i class="fa-solid fa-receipt"></i> সম্পূর্ণ একাউন্টস লেজার ও রসিদ →
                </a>
            </div>
        </div>

        {{-- Financial Summary KPI Chips --}}
        <div style="padding:14px 18px 0 18px">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px">
                <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;padding:10px 14px">
                    <div style="font-size:11px;color:#9f1239;font-weight:700">সর্বমোট বকেয়া (Total Outstanding Due)</div>
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
                <div style="font-size:13.5px;font-weight:700;color:#1e293b;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
                    <span><i class="fa-solid fa-clock" style="color:#e11d48"></i> বর্তমান বকেয়া ফি সমূহ (Due List)</span>
                    <span class="badge badge-danger no-dot" style="font-size:11px">{{ isset($dueInvoices) ? $dueInvoices->count() : 0 }}টি</span>
                </div>
                @if(isset($dueInvoices) && $dueInvoices->count() > 0)
                    <div style="display:flex;flex-direction:column;gap:8px">
                        @foreach($dueInvoices->take(4) as $dInv)
                        <div style="background:#fff;border:1px solid #fee2e2;border-radius:8px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center">
                            <div>
                                <div style="font-size:12.5px;font-weight:700;color:#0f172a">{{ $dInv->title }}</div>
                                <div style="font-size:11px;color:#64748b">ইনভয়েস: {{ $dInv->invoice_no }}</div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-size:14px;font-weight:800;color:#dc2626">৳{{ number_format($dInv->due_amount, 2) }}</div>
                                <a href="{{ route('student.fees.index') }}" style="font-size:11px;color:#2563eb;font-weight:700;text-decoration:none">
                                    পে করুন →
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center;padding:16px;color:#047857;background:#fff;border-radius:8px;font-size:12.5px">
                        <i class="fa-solid fa-circle-check" style="font-size:20px;color:#10b981;display:block;margin-bottom:6px"></i>
                        আলহামদুলিল্লাহ! আপনার কোনো বকেয়া ফি নেই।
                    </div>
                @endif
            </div>

            {{-- Recent Vouchers / Payment History --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px">
                <div style="font-size:13.5px;font-weight:700;color:#1e293b;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
                    <span><i class="fa-solid fa-receipt" style="color:#059669"></i> পেমেন্ট হিস্ট্রি ও ভাউচার (Receipts)</span>
                    <span class="badge badge-success no-dot" style="font-size:11px">{{ isset($recentVoucherPayments) ? $recentVoucherPayments->count() : 0 }}টি</span>
                </div>
                @if(isset($recentVoucherPayments) && $recentVoucherPayments->count() > 0)
                    <div style="display:flex;flex-direction:column;gap:8px">
                        @foreach($recentVoucherPayments->take(4) as $rPay)
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;display:flex;justify-content:space-between;align-items:center">
                            <div>
                                <div style="font-size:12.5px;font-weight:700;color:#0f172a">{{ $rPay->invoice->title ?? 'ফি পরিশোধ' }}</div>
                                <div style="font-size:11px;color:#64748b">
                                    রসিদ: {{ $rPay->payment_no }} · {{ $rPay->payment_method }}
                                    @if($rPay->sender_number)
                                        · বিকাশ: {{ $rPay->sender_number }}
                                    @endif
                                </div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-size:13.5px;font-weight:800;color:#047857">৳{{ number_format($rPay->amount, 2) }}</div>
                                @if(($rPay->status ?? 'APPROVED') === 'APPROVED')
                                    <a href="{{ route('student.fees.receipt', $rPay) }}" target="_blank" style="font-size:11px;color:#047857;font-weight:700;text-decoration:none">
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
                    <div style="text-align:center;padding:16px;color:#94a3b8;background:#fff;border-radius:8px;font-size:12.5px">
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
                    <span class="badge {{ $n->priority === 'URGENT' ? 'badge-danger' : ($n->priority === 'IMPORTANT' ? 'badge-warning' : 'badge-info') }} no-dot" style="font-size:10px">
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
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--card-border)">
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $cs->subject?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            {{ $cs->session_date?->format('d M Y (D)') ?? 'TBA' }}
                            @if($cs->moduleCovered) · <i class="fa-solid fa-book"></i> {{ $cs->moduleCovered->title }} @endif
                        </div>
                    </div>
                    @php $badge = match($cs->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                    <span class="badge {{ $badge }} no-dot">{{ $cs->status }}</span>
                </div>
                @empty
                <div class="empty-state"><p>No class sessions yet</p></div>
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
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--card-border)">
                    <div style="width:38px;height:38px;background:#f5f3ff;border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;color:#8b5cf6;font-size:12px">
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
                    <a href="{{ route('student.classes.join', $class) }}" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-video"></i> Join</a>
                    @endif
                </div>
                @empty
                <div class="empty-state"><p>No upcoming classes</p></div>
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
                    <thead><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Result</th></tr></thead>
                    <tbody>
                        @forelse($recentResults as $result)
                        <tr>
                            <td class="td-primary">{{ $result->exam->subject->name ?? '—' }}</td>
                            <td>{{ $result->marks ?? '—' }}/{{ $result->exam->full_marks ?? '—' }}</td>
                            <td><strong>{{ $result->grade ?? '—' }}</strong></td>
                            <td><span class="badge badge-{{ strtolower($result->status ?? 'secondary') }}">{{ ucfirst(strtolower($result->status ?? 'N/A')) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--text-muted)">No results yet</td></tr>
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
                <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--card-border)">
                    <div style="width:40px;height:40px;background:#fff7ed;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;font-weight:700;color:#f59e0b">
                        {{ \Carbon\Carbon::parse($exam->exam_date)->format('d') }}<br><span style="font-size:9px">{{ \Carbon\Carbon::parse($exam->exam_date)->format('M') }}</span>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $exam->subject->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $exam->title }} · {{ ucfirst(strtolower($exam->type)) }}</div>
                    </div>
                    <span class="badge badge-scheduled">Scheduled</span>
                </div>
                @empty
                <div class="empty-state"><p>No upcoming exams</p></div>
                @endforelse
            </div>
        </div>
    </div>

</x-student-layout>
