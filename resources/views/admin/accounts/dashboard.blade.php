<x-admin-layout>
    <x-slot name="title">Accounts Counter & Fee Collection</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Counter &amp; Fee Collection</h1>
            <p>Real-time financial summary, instant student search, and quick fee collection counter</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.accounts.invoices') }}" class="btn btn-outline">All Invoices</a>
            <a href="{{ route('admin.accounts.reports') }}" class="btn btn-primary">Collection Reports</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon green">৳</div>
            <div class="stat-info">
                <div class="stat-value">৳{{ number_format($stats['today_collected'], 2) }}</div>
                <div class="stat-label">Collected Today</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">৳{{ number_format($stats['month_collected'], 2) }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">৳{{ number_format($stats['total_collected'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value" style="color:#e11d48">৳{{ number_format($stats['total_due'], 2) }}</div>
                <div class="stat-label">Total Outstanding Dues</div>
            </div>
        </div>
    </div>

    {{-- Course Activation Fee Automation Banner --}}
    <div class="card" style="margin-bottom:24px;border:1.5px solid #fde68a;background:#fffbeb;font-family:'Kalpurush',sans-serif;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
        <div style="padding:16px 20px">
            <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:14px">
                <i class="fa-solid fa-bolt" style="font-size:24px;color:#d97706;margin-top:2px"></i>
                <div>
                    <strong style="font-size:16px;color:#92400e">কোর্স এক্টিভেশন ফি ও পোর্টাল এক্সেস অটোমেশন</strong>
                    <div style="font-size:13px;color:#78350f;margin-top:2px;line-height:1.5">
                        নির্দিষ্ট কোর্স, ব্যাচ (একসাথে সব ব্যাচ বা নির্দিষ্ট ব্যাচ) ও মাস-বছর সিলেক্ট করে এক্টিভেশন ফি প্রয়োগ করুন। <strong>যাদের ওই মাসের বেতন আনপেইড (বকেয়া) থাকবে শুধু তাদের এক্টিভেশন ফি যুক্ত হবে এবং তাদের পোর্টাল এক্সেস অফ হয়ে যাবে।</strong> শিক্ষার্থী ফি পেমেন্ট করলে তাৎক্ষণিকভাবে অটো এক্সেস অন হয়ে যাবে।
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.accounts.apply-activation-fees') }}" method="POST" id="activationFeeForm">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;align-items:flex-end;background:#fef3c7;padding:14px;border-radius:10px;border:1px solid #fde68a">
                    {{-- 1. Course Selection --}}
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#92400e;display:block;margin-bottom:4px">
                            কোর্স নির্বাচন <span style="color:#dc2626">*</span>
                        </label>
                        <select name="course_id" id="activationCourseSelect" class="form-control" required style="font-size:13px;height:38px;background:#fff">
                            <option value="">-- কোর্স নির্বাচন করুন --</option>
                            <option value="all">সকল কোর্স (All Courses)</option>
                            @foreach($courses ?? [] as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Batch Selection (All or Individual) --}}
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#92400e;display:block;margin-bottom:4px">
                            ব্যাচ নির্বাচন (সব ব্যাচ বা নির্দিষ্ট)
                        </label>
                        <select name="batch_id" id="activationBatchSelect" class="form-control" style="font-size:13px;height:38px;background:#fff">
                            <option value="all">সব ব্যাচ (একসাথে সব ব্যাচে প্রয়োগ)</option>
                            @foreach($batches ?? [] as $b)
                                <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}">
                                    {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Month Selection --}}
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#92400e;display:block;margin-bottom:4px">
                            বেতনের মাস <span style="color:#dc2626">*</span>
                        </label>
                        @php $currM = now()->month; @endphp
                        <select name="month" class="form-control" style="font-size:13px;height:38px;background:#fff">
                            @foreach([
                                1 => 'জানুয়ারি (Jan)',
                                2 => 'ফেব্রুয়ারি (Feb)',
                                3 => 'মার্চ (Mar)',
                                4 => 'এপ্রিল (Apr)',
                                5 => 'মে (May)',
                                6 => 'জুন (Jun)',
                                7 => 'জুলাই (Jul)',
                                8 => 'আগস্ট (Aug)',
                                9 => 'সেপ্টেম্বর (Sep)',
                                10 => 'অক্টোবর (Oct)',
                                11 => 'নভেম্বর (Nov)',
                                12 => 'ডিসেম্বর (Dec)'
                            ] as $mNum => $mLabel)
                                <option value="{{ $mNum }}" {{ $currM == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Year Selection --}}
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#92400e;display:block;margin-bottom:4px">
                            বছর <span style="color:#dc2626">*</span>
                        </label>
                        @php $currY = now()->year; @endphp
                        <select name="year" class="form-control" style="font-size:13px;height:38px;background:#fff">
                            @foreach([$currY - 1, $currY, $currY + 1, $currY + 2] as $yVal)
                                <option value="{{ $yVal }}" {{ $currY == $yVal ? 'selected' : '' }}>{{ $yVal }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. Amount & Submit --}}
                    <div style="display:flex;gap:8px;align-items:flex-end">
                        <div style="width:90px">
                            <label style="font-size:12px;font-weight:700;color:#92400e;display:block;margin-bottom:4px">
                                ফি (টাকা)
                            </label>
                            <input type="number" name="amount" class="form-control" value="100" min="1" step="1" required
                                style="font-size:13px;height:38px;background:#fff;font-weight:700" title="এক্টিভেশন ফি এর পরিমাণ">
                        </div>
                        <button type="submit" class="btn btn-warning" style="font-weight:700;background:#d97706;border-color:#b45309;color:#fff;height:38px;white-space:nowrap;padding:0 16px;display:inline-flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-bolt"></i> এক্টিভেশন ফি প্রয়োগ ও পোর্টাল অফ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Instant Student Search Counter --}}
    <div class="card" style="margin-bottom:24px;border-top:4px solid #6366f1">
        <div class="card-header">
            <span class="card-title">Quick Fee Collection Counter</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounts.dashboard') }}" style="display:flex;gap:12px;align-items:center">
                <input type="text" name="search" class="form-control" style="font-size:14px"
                    placeholder="Enter Student Name, ID Code (e.g. STD-2026-001), Phone or Invoice No..."
                    value="{{ $search }}" required>
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:14px">Search Dues</button>
                @if($search)
                    <a href="{{ route('admin.accounts.dashboard') }}" class="btn btn-outline">Clear</a>
                @endif
            </form>

            @if($search)
                <div style="margin-top:20px">
                    <h4 style="font-size:14px;font-weight:700;margin-bottom:12px;color:#334155">Search Results — Pending Invoices for Payment:</h4>
                    @forelse($counterInvoices as $inv)
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                            <div>
                                <div style="font-weight:700;font-size:15px;color:#0f172a">
                                    {{ $inv->title }}
                                    <span class="badge badge-info" style="margin-left:6px">{{ $inv->invoice_no }}</span>
                                </div>
                                <div style="font-size:13px;color:#64748b;margin-top:4px">
                                    Student: <strong>{{ $inv->student->name }}</strong> (ID: <span style="color:#2563eb;font-weight:600">{{ $inv->student->student_code }}</span>, Phone: {{ $inv->student->phone }})
                                </div>
                                <div style="font-size:12px;color:#475569;margin-top:2px">
                                    Total: ৳{{ number_format($inv->payable_amount, 2) }} &middot;
                                    Paid: ৳{{ number_format($inv->paid_amount, 2) }} &middot;
                                    <strong style="color:#e11d48">Due: ৳{{ number_format($inv->due_amount, 2) }}</strong>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-success btn-sm" onclick="openPaymentModal({{ $inv->id }}, '{{ $inv->invoice_no }}', {{ $inv->due_amount }})">
                                    Receive ৳{{ number_format($inv->due_amount, 2) }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div style="padding:20px;text-align:center;color:#94a3b8">
                            No pending dues found for <strong>"{{ $search }}"</strong>.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    {{-- Pending Online Payments Awaiting Admin Approval --}}
    <div class="card" style="margin-bottom:24px; border-top:4px solid #f59e0b">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; background:#fffbeb">
            <span class="card-title" style="color:#b45309; display:flex; align-items:center; gap:8px">
                ⏳ অনলাইন পেমেন্ট ভেরিফিকেশন ও অনুমোদন (Pending Online Payments)
                @if(isset($pendingPayments) && $pendingPayments->count() > 0)
                    <span class="badge badge-warning" style="font-size:12px">{{ $pendingPayments->count() }} PENDING</span>
                @endif
            </span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Trx ID / Ref</th>
                        <th>Student Details</th>
                        <th>Invoice Purpose</th>
                        <th>Amount Submitted</th>
                        <th>Method</th>
                        <th>Date &amp; Time</th>
                        <th style="text-align:center">Action / Verification</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingPayments ?? [] as $pPay)
                    <tr style="background:#fff">
                        <td style="font-weight:700; color:#b45309; font-size:12px">
                            <code>{{ $pPay->transaction_id ?: $pPay->payment_no }}</code>
                            @if($pPay->sender_number)
                                <div style="font-size:11px; color:#047857; margin-top:3px; font-weight:600">
                                    <i class="fa-solid fa-phone"></i> {{ $pPay->sender_number }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $pPay->student->name }}</strong><br>
                            <small style="color:#64748b">Code: {{ $pPay->student->student_code }} | {{ $pPay->student->phone }}</small>
                        </td>
                        <td class="td-primary" style="font-size:13px">
                            {{ $pPay->invoice->title ?? '—' }}
                            <br><small style="color:#94a3b8">Inv No: {{ $pPay->invoice->invoice_no ?? 'N/A' }}</small>
                        </td>
                        <td><strong style="color:#2563eb; font-size:15px">৳{{ number_format($pPay->amount, 2) }}</strong></td>
                        <td>
                            @if(str_contains($pPay->payment_method, 'BKASH'))
                                <span class="badge" style="background:#fce7f3; color:#be185d; font-weight:700">বিকাশ ম্যানুয়াল</span>
                            @elseif(str_contains($pPay->payment_method, 'NAGAD'))
                                <span class="badge" style="background:#ffedd5; color:#c2410c; font-weight:700">নগদ ম্যানুয়াল</span>
                            @elseif(str_contains($pPay->payment_method, 'ROCKET'))
                                <span class="badge" style="background:#ede9fe; color:#6d28d9; font-weight:700">রকেট ম্যানুয়াল</span>
                            @elseif(str_contains($pPay->payment_method, 'BANK'))
                                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700">ব্যাংক ট্রান্সফার</span>
                            @else
                                <span class="badge badge-warning no-dot" style="font-weight:700">{{ $pPay->payment_method }}</span>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:12px">{{ $pPay->paid_at ? $pPay->paid_at->format('d M Y, h:i A') : '—' }}</td>
                        <td style="text-align:center">
                            <div style="display:flex; justify-content:center; gap:6px">
                                <form method="POST" action="{{ route('admin.accounts.payments.approve', $pPay) }}" onsubmit="return confirm('আপনি কি এই পেমেন্টটি এপ্রুভ করতে চান?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" style="padding:5px 10px; font-size:11.5px">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.accounts.payments.reject', $pPay) }}" onsubmit="return confirm('আপনি কি এই পেমেন্টটি বাতিল করতে চান?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:5px 10px; font-size:11.5px">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px; color:#94a3b8">কোনো অনলাইন পেমেন্ট পেন্ডিং নেই।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Payment History --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Payment Transactions</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Student</th>
                        <th>Invoice / Purpose</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $pay)
                    <tr>
                        <td style="font-weight:700;color:#6366f1;font-size:12px">{{ $pay->payment_no }}</td>
                        <td>
                            <strong>{{ $pay->student->name }}</strong><br>
                            <small style="color:#64748b">{{ $pay->student->student_code }}</small>
                        </td>
                        <td class="td-primary">{{ $pay->invoice->title ?? '—' }}</td>
                        <td><strong style="color:#10b981">৳{{ number_format($pay->amount, 2) }}</strong></td>
                        <td><span class="badge badge-secondary no-dot">{{ $pay->payment_method }}</span></td>
                        <td>
                            @if($pay->status === 'APPROVED')
                                <span class="badge badge-success no-dot" style="font-size:11px">Approved</span>
                            @elseif($pay->status === 'PENDING')
                                <span class="badge badge-warning no-dot" style="font-size:11px">⏳ Pending</span>
                            @else
                                <span class="badge badge-danger no-dot" style="font-size:11px">Rejected</span>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:12px">{{ $pay->paid_at->format('d M Y, h:i A') }}</td>
                        <td style="text-align:center">
                            <a href="{{ route('admin.accounts.payments.receipt', $pay) }}" target="_blank" class="btn btn-outline btn-sm">
                                Receipt
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:#94a3b8">No recent payments logged.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Collect Payment Modal --}}
    <div class="modal-overlay" id="collectModal">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <span class="modal-title">Collect Payment</span>
                <button class="modal-close" onclick="closeModal('collectModal')">&times;</button>
            </div>
            <form id="collectForm" method="POST" action="">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="background:#eff6ff;padding:10px 14px;border-radius:8px;border:1px solid #bfdbfe;font-size:13px">
                        Invoice No: <strong id="modalInvNo" style="color:#1d4ed8"></strong>
                    </div>

                    <div class="form-group">
                        <label>Amount to Collect (৳) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="amount" id="modalAmount" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Payment Method <span class="required">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="CASH">CASH (নগদ টাকা)</option>
                            <option value="BKASH">BKASH (বিকাশ)</option>
                            <option value="NAGAD">NAGAD (নগদ)</option>
                            <option value="ROCKET">ROCKET (রকেট)</option>
                            <option value="BANK_TRANSFER">BANK TRANSFER (ব্যাংক ট্রান্সফার)</option>
                            <option value="CARD">CARD (কার্ড)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Transaction ID / Ref (Optional)</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TRX12345678">
                    </div>

                    <div class="form-group">
                        <label>Remarks / Note (Optional)</label>
                        <input type="text" name="remarks" class="form-control" placeholder="e.g. Received at counter">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('collectModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm &amp; Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openPaymentModal(invoiceId, invNo, dueAmount) {
        document.getElementById('modalInvNo').innerText = invNo;
        document.getElementById('modalAmount').value = dueAmount;
        document.getElementById('modalAmount').max = dueAmount;
        document.getElementById('collectForm').action = '/admin/accounts/invoices/' + invoiceId + '/collect';
        openModal('collectModal');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const courseSelect = document.getElementById('activationCourseSelect');
        const batchSelect  = document.getElementById('activationBatchSelect');

        if (courseSelect && batchSelect) {
            courseSelect.addEventListener('change', function() {
                const selectedCourseId = this.value;
                const options = batchSelect.querySelectorAll('option');

                options.forEach(opt => {
                    if (opt.value === 'all') {
                        opt.style.display = '';
                        opt.selected = true;
                        return;
                    }
                    const courseId = opt.getAttribute('data-course-id');
                    if (!selectedCourseId || selectedCourseId === 'all' || courseId === selectedCourseId) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            });
        }

        const actForm = document.getElementById('activationFeeForm');
        if (actForm) {
            actForm.addEventListener('submit', function(e) {
                const courseText = courseSelect.options[courseSelect.selectedIndex]?.text || '';
                const batchText  = batchSelect.options[batchSelect.selectedIndex]?.text || '';
                const confirmMsg = '⚠️ নিশ্চিতকরণ:\n' +
                    'নির্বাচিত [' + courseText + ' - ' + batchText + '] এর যে সকল শিক্ষার্থীর উক্ত মাসের বেতন আনপেইড (বকেয়া) রয়েছে, শুধুমাত্র তাদের পোর্টালে এক্টিভেশন ফি যুক্ত হবে এবং তাদের পোর্টাল এক্সেস অফ হয়ে যাবে।\n\n' +
                    'শিক্ষার্থী ফি পরিশোধ করা মাত্রই তাদের পোর্টাল এক্সেস স্বয়ংক্রিয়ভাবে অটো অন হবে।\n\nআপনি কি নিশ্চিত?';
                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
    </script>
    @endpush
</x-admin-layout>
