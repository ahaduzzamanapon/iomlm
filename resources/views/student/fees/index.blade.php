<x-student-layout>
    <x-slot name="title">My Fees & Dues</x-slot>

    <div class="page-header" style="margin-bottom:20px">
        <div class="page-header-left">
            <h1 style="display:flex;align-items:center;gap:10px">
                💳 My Fees &amp; Payment Receipts
                @if($course)
                    <span class="badge badge-primary no-dot" style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px">
                        {{ $course->name }} ({{ $courseType === 'SUBJECT_BASED' ? 'Subject-Based Course' : 'Semester-Based Course' }})
                    </span>
                @endif
            </h1>
            <p>Track your running semester dues, overall course fees, and download official payment receipts</p>
        </div>
    </div>

    {{-- ── STATS SUMMARY CARDS (Semester Breakdown & Overall Dues) ── --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        {{-- Card 1: Running Semester Dues (for Semester-based course) or Course Dues (for Subject-based course) --}}
        @if($courseType === 'SEMESTER_BASED')
            <div class="stat-card" style="border: 2px solid {{ $runningSemesterDue > 0 ? '#fecdd3' : '#a7f3d0' }}; background: {{ $runningSemesterDue > 0 ? '#fff1f2' : '#f0fdf4' }}">
                <div class="stat-icon" style="background: {{ $runningSemesterDue > 0 ? '#ffe4e6' : '#dcfce7' }}; color: {{ $runningSemesterDue > 0 ? '#e11d48' : '#10b981' }}">
                    {{ $runningSemesterDue > 0 ? '⚠️' : '✓' }}
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="color:{{ $runningSemesterDue > 0 ? '#be123c' : '#047857' }}; font-weight:800; font-size:22px">
                        ৳{{ number_format($runningSemesterDue, 2) }}
                    </div>
                    <div class="stat-label" style="font-weight:700; color:{{ $runningSemesterDue > 0 ? '#9f1239' : '#065f46' }}">
                        {{ $runningSemesterDue > 0 ? '⚡ Running Semester Dues' : '✓ Running Semester Cleared' }}
                    </div>
                    <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">
                        {{ $runningSemesterName }}
                    </div>
                </div>
            </div>
        @else
            <div class="stat-card">
                <div class="stat-icon orange">📖</div>
                <div class="stat-info">
                    <div class="stat-value" style="color:{{ $totalDue > 0 ? '#e11d48' : '#10b981' }}">
                        ৳{{ number_format($totalDue, 2) }}
                    </div>
                    <div class="stat-label" style="font-weight:700">Course Dues (Subject-Based)</div>
                    <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Overall course tuition dues</div>
                </div>
            </div>
        @endif

        {{-- Card 2: Total Outstanding Dues (All Categories Combined) --}}
        <div class="stat-card">
            <div class="stat-icon red" style="background:#fee2e2;color:#dc2626">🏛️</div>
            <div class="stat-info">
                <div class="stat-value" style="color:{{ $totalDue > 0 ? '#e11d48' : '#10b981' }}; font-weight:800; font-size:22px">
                    ৳{{ number_format($totalDue, 2) }}
                </div>
                <div class="stat-label" style="font-weight:700">Total Outstanding Dues</div>
                <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Combined total across all semesters &amp; admission</div>
            </div>
        </div>

        {{-- Card 3: Total Fees Paid --}}
        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-info">
                <div class="stat-value" style="font-weight:800; font-size:22px">৳{{ number_format($totalPaid, 2) }}</div>
                <div class="stat-label" style="font-weight:700">Total Fees Paid</div>
                <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Total payments received &amp; verified</div>
            </div>
        </div>

    </div>

    {{-- ── 📊 SEMESTER-WISE & CATEGORY DUES BREAKDOWN TABLE ── --}}
    <div class="card" style="margin-bottom:24px; border-top:3px solid #3b82f6">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center">
            <span class="card-title" style="display:flex; align-items:center; gap:8px">
                📊 {{ $courseType === 'SUBJECT_BASED' ? 'Course Fee & Particulars Breakdown' : 'Semester & Category Payment Breakdown' }}
            </span>
            <span style="font-size:12px; color:var(--text-muted)">
                {{ $courseType === 'SUBJECT_BASED' ? 'Summary by Category' : 'Summary by Semester' }}
            </span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr style="background:#f8fafc">
                        <th>{{ $courseType === 'SUBJECT_BASED' ? 'Particulars / Fee Item' : 'Semester / Particulars' }}</th>
                        <th style="text-align:right">Payable Amount</th>
                        <th style="text-align:right">Paid Amount</th>
                        <th style="text-align:right">Remaining Due</th>
                        <th style="text-align:center">{{ $courseType === 'SUBJECT_BASED' ? 'Payment Status' : 'Semester Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semesterBreakdown as $row)
                        @php
                            $gPayable   = $row['payable'];
                            $gPaid      = $row['paid'];
                            $gDue       = $row['due'];
                            $isRunning  = $row['isRunning'];
                            $hasInvoice = $row['hasInvoice'];
                            $cleanName  = str_replace(' 🔵', '', $row['label']);
                        @endphp
                        <tr style="{{ $isRunning ? 'background:#f0f9ff;' : (!$hasInvoice ? 'background:#fafafa; opacity:.75;' : '') }}">
                            <td>
                                <strong style="font-size:13px; color:#1e293b">{{ $cleanName }}</strong>
                                @if($isRunning)
                                    <span class="badge badge-primary no-dot" style="font-size:10px; margin-left:6px">Current Running</span>
                                @elseif(!$hasInvoice)
                                    <span style="font-size:10px; color:#94a3b8; margin-left:6px; font-style:italic">— upcoming</span>
                                @endif
                            </td>
                            <td style="text-align:right; font-weight:600">
                                {{ $hasInvoice ? '৳' . number_format($gPayable, 2) : '—' }}
                            </td>
                            <td style="text-align:right; color:#10b981; font-weight:600">
                                {{ $hasInvoice ? '৳' . number_format($gPaid, 2) : '—' }}
                            </td>
                            <td style="text-align:right">
                                @if(!$hasInvoice)
                                    <span style="color:#94a3b8; font-size:12px">—</span>
                                @elseif($gDue > 0)
                                    <strong style="color:#e11d48; font-size:14px">৳{{ number_format($gDue, 2) }}</strong>
                                @else
                                    <span style="color:#10b981; font-weight:700">৳0.00</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                @if(!$hasInvoice)
                                    <span class="badge badge-secondary no-dot" style="padding:4px 10px; font-size:11px">⏳ Upcoming</span>
                                @elseif($gDue <= 0)
                                    <span class="badge badge-success no-dot" style="padding:4px 10px">✓ Cleared</span>
                                @elseif($gPaid > 0)
                                    <span class="badge badge-warning no-dot" style="padding:4px 10px">Partial Paid</span>
                                @else
                                    <span class="badge badge-danger no-dot" style="padding:4px 10px">⚠️ Pending Due</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:20px; color:var(--text-muted)">No breakdown available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── 📑 MY INVOICES & DETAILED FEE STATEMENTS ── --}}
    <div class="card" style="margin-bottom:24px">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center">
            <span class="card-title">📑 My Invoices &amp; Detailed Statements</span>
            <div class="btn-group" id="invFilterGroup" style="display:flex; gap:6px">
                <button class="btn btn-sm btn-primary filter-btn active" onclick="filterInvoices('all', this)">All Invoices</button>
                <button class="btn btn-sm btn-outline filter-btn" onclick="filterInvoices('running', this)">⚡ Running Semester</button>
                <button class="btn btn-sm btn-outline filter-btn" onclick="filterInvoices('unpaid', this)">⚠️ Unpaid</button>
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
                    </tr>
                </thead>
                <tbody id="invoicesTableBody">
                    @forelse($invoices as $inv)
                    <tr class="inv-row {{ $inv->is_current_running_semester ? 'row-running' : '' }} {{ $inv->due_amount > 0 ? 'row-unpaid' : 'row-paid' }}">
                        <td style="font-weight:700;color:#3b82f6;font-size:12px">{{ $inv->invoice_no }}</td>
                        <td>
                            <strong>{{ $inv->title }}</strong><br>
                            <span class="badge badge-secondary no-dot" style="font-size:10px">{{ $inv->category }}</span>
                            @if($inv->is_current_running_semester)
                                <span class="badge badge-primary no-dot" style="font-size:10px; background:#3b82f6">⚡ Running Semester</span>
                            @endif
                        </td>
                        <td>৳{{ number_format($inv->payable_amount, 2) }}</td>
                        <td><span style="color:#10b981;font-weight:600">৳{{ number_format($inv->paid_amount, 2) }}</span></td>
                        <td>
                            @if($inv->due_amount > 0)
                                <strong style="color:#e11d48;font-size:13px">৳{{ number_format($inv->due_amount, 2) }}</strong>
                            @else
                                <span style="color:#10b981;font-weight:600">৳0.00</span>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:12px">{{ $inv->due_date ? $inv->due_date->format('d M Y') : '—' }}</td>
                        <td>
                            @php
                                $badge = match($inv->status) { 'PAID'=>'badge-success', 'PARTIAL'=>'badge-warning', default=>'badge-danger' };
                            @endphp
                            <span class="badge {{ $badge }} no-dot">{{ $inv->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No fee invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── 🧾 PAYMENT RECEIPT HISTORY ── --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">🧾 Payment Receipt History</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Invoice Purpose</th>
                        <th>Amount Paid</th>
                        <th>Payment Method</th>
                        <th>Date &amp; Time</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                    <tr>
                        <td style="font-weight:700;color:#6366f1;font-size:12px">{{ $pay->payment_no }}</td>
                        <td class="td-primary">{{ $pay->invoice->title ?? '—' }}</td>
                        <td><strong style="color:#10b981">৳{{ number_format($pay->amount, 2) }}</strong></td>
                        <td><span class="badge badge-secondary no-dot">{{ $pay->payment_method }}</span></td>
                        <td class="td-muted" style="font-size:12px">{{ $pay->paid_at->format('d M Y, h:i A') }}</td>
                        <td style="text-align:center">
                            <a href="{{ route('student.fees.receipt', $pay) }}" target="_blank" class="btn btn-outline btn-sm">
                                🖨️ Download Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No payment transactions recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
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
            }
        });
    }
    </script>
    @endpush
</x-student-layout>
