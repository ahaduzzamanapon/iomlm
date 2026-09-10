<x-student-layout>
    <x-slot name="title">My Fees & Dues</x-slot>

    {{-- Page Header & Course Selector --}}
    <div class="page-header" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px">
        <div class="page-header-left">
            <h1 style="display:flex;align-items:center;gap:10px; flex-wrap:wrap">
                My Fees &amp; Payment Receipts
                @if($course)
                    <span class="badge badge-primary no-dot" style="font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px">
                        {{ $course->name }} ({{ $courseType === 'SUBJECT_BASED' ? 'Subject-Based Course' : 'Semester-Based Course' }})
                    </span>
                @endif
            </h1>
            <p>Track your running semester dues, overall course fees, and download official payment receipts</p>
        </div>

        {{-- Multi-Course Selector --}}
        @if(isset($studentCourses) && $studentCourses->count() > 1)
        <div style="display:flex; align-items:center; gap:8px; background:#fff; padding:6px 12px; border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,0.03)">
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
        <div style="background:#dcfce7; color:#15803d; padding:14px 18px; border-radius:12px; border:1px solid #bbf7d0; margin-bottom:20px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fee2e2; color:#b91c1c; padding:14px 18px; border-radius:12px; border:1px solid #fca5a5; margin-bottom:20px; font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── STATS SUMMARY CARDS ── --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        {{-- Card 1: Running Semester Dues or Course Dues --}}
        @if($courseType === 'SEMESTER_BASED')
            <div class="stat-card" style="border: 2px solid {{ $runningSemesterDue > 0 ? '#fecdd3' : '#a7f3d0' }}; background: {{ $runningSemesterDue > 0 ? '#fff1f2' : '#f0fdf4' }}">
                <div class="stat-info">
                    <div class="stat-value" style="color:{{ $runningSemesterDue > 0 ? '#be123c' : '#047857' }}; font-weight:800; font-size:22px">
                        ৳{{ number_format($runningSemesterDue, 2) }}
                    </div>
                    <div class="stat-label" style="font-weight:700; color:{{ $runningSemesterDue > 0 ? '#9f1239' : '#065f46' }}">
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
                <div class="stat-value" style="color:{{ $totalDue > 0 ? '#e11d48' : '#10b981' }}; font-weight:800; font-size:22px">
                    ৳{{ number_format($totalDue, 2) }}
                </div>
                <div class="stat-label" style="font-weight:700">Total Outstanding Dues</div>
                <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Combined total across all semesters &amp; fees</div>
            </div>
        </div>

        {{-- Card 3: Total Fees Paid --}}
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value" style="font-weight:800; font-size:22px">৳{{ number_format($totalPaid, 2) }}</div>
                <div class="stat-label" style="font-weight:700">Total Fees Paid</div>
                <div style="font-size:11px; margin-top:2px; color:var(--text-muted)">Total payments received &amp; verified</div>
            </div>
        </div>

    </div>

    {{-- ── ITEMWISE FEE & SEMESTER BREAKDOWN TABLE ── --}}
    <div class="card" style="margin-bottom:24px; border-top:3px solid #3b82f6">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center">
            <span class="card-title" style="display:flex; align-items:center; gap:8px">
                {{ $courseType === 'SUBJECT_BASED' ? 'Course Fee & Particulars Breakdown' : 'Semester & Category Payment Breakdown' }}
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
                        <th style="text-align:center">Action</th>
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
                            $invObj     = $row['invoice'] ?? null;
                            $cleanName  = str_replace(' 🔵', '', $row['label']);
                        @endphp
                        <tr style="{{ $isRunning ? 'background:#f0f9ff;' : (!$hasInvoice ? 'background:#fafafa; opacity:.75;' : '') }}">
                            <td>
                                <strong style="font-size:13.5px; color:#1e293b">{{ $cleanName }}</strong>
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
                                    <span class="badge badge-success no-dot" style="padding:4px 10px">Cleared</span>
                                @elseif($gPaid > 0)
                                    <span class="badge badge-warning no-dot" style="padding:4px 10px">Partial Paid</span>
                                @else
                                    <span class="badge badge-danger no-dot" style="padding:4px 10px">Pending Due</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                @if($hasInvoice && $gDue > 0 && $invObj)
                                    <button onclick="openPayModal('{{ $invObj->id }}', '{{ e($cleanName) }}', '{{ $invObj->invoice_no }}', '{{ $gDue }}')"
                                        style="background:linear-gradient(135deg,#16a34a,#22c55e); color:#fff; border:none; padding:5px 12px; border-radius:7px; font-weight:700; font-size:12px; cursor:pointer; box-shadow:0 2px 6px rgba(22,163,74,0.3); display:inline-flex; align-items:center; gap:4px">
                                        Pay Now
                                    </button>
                                @elseif($hasInvoice && $gDue <= 0)
                                    <span style="color:#16a34a; font-size:12px; font-weight:700">Paid</span>
                                @else
                                    <span style="color:#cbd5e1; font-size:12px">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px; color:var(--text-muted)">No breakdown available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($packageItemsBreakdown) && $packageItemsBreakdown->isNotEmpty())
        <div style="background:#f8fafc; padding:16px 20px; border-top:1px solid #e2e8f0">
            <div style="font-size:13px; font-weight:700; color:#334155; margin-bottom:10px; display:flex; align-items:center; gap:8px">
                কোর্স ফি প্যাকেজের আইটেমভিত্তিক বিস্তারিত হিসেব (Itemized Package Fee Breakdown)
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px">
                @foreach($packageItemsBreakdown as $pItem)
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:10px 14px">
                    <div style="font-size:12px; font-weight:700; color:#1e293b">{{ $pItem['name'] }}</div>
                    <div style="display:flex; justify-content:space-between; margin-top:4px; font-size:11px; color:#64748b">
                        <span>{{ $courseType === 'SUBJECT_BASED' ? 'কোর্স ফি:' : 'প্রতি সেমিস্টারে অংশ:' }}</span>
                        <strong style="color:#2563eb">৳{{ number_format($pItem['per_semester_amt'], 2) }}</strong>
                    </div>
                    @if($courseType !== 'SUBJECT_BASED')
                    <div style="display:flex; justify-content:space-between; margin-top:2px; font-size:10.5px; color:#94a3b8">
                        <span>মোট প্যাকেজ মূল্য:</span>
                        <span>৳{{ number_format($pItem['total_package'], 2) }}</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── MY INVOICES & DETAILED FEE STATEMENTS ── --}}
    <div class="card" style="margin-bottom:24px">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center">
            <span class="card-title">My Invoices &amp; Detailed Statements</span>
            <div class="btn-group" id="invFilterGroup" style="display:flex; gap:6px">
                <button class="btn btn-sm btn-primary filter-btn active" onclick="filterInvoices('all', this)">All Invoices</button>
                <button class="btn btn-sm btn-outline filter-btn" onclick="filterInvoices('running', this)">Running Semester</button>
                <button class="btn btn-sm btn-outline filter-btn" onclick="filterInvoices('unpaid', this)">Unpaid</button>
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
                    <tr class="inv-row {{ $inv->is_current_running_semester ? 'row-running' : '' }} {{ $inv->due_amount > 0 ? 'row-unpaid' : 'row-paid' }}">
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
                                    <span class="badge badge-primary no-dot" style="font-size:10px; background:#7c3aed">Current Course</span>
                                @else
                                    <span class="badge badge-primary no-dot" style="font-size:10px; background:#3b82f6">Running Semester</span>
                                @endif
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
                        <td class="td-muted" style="font-size:12px">{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                        <td>
                            @php
                                $badge = match($inv->status) { 'PAID'=>'badge-success', 'PARTIAL'=>'badge-warning', default=>'badge-danger' };
                            @endphp
                            <span class="badge {{ $badge }} no-dot">{{ $inv->status }}</span>
                        </td>
                        <td style="text-align:center">
                            @if($inv->due_amount > 0)
                                <button onclick="openPayModal('{{ $inv->id }}', '{{ e($inv->title) }}', '{{ $inv->invoice_no }}', '{{ $inv->due_amount }}')"
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
                        <td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">No fee invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── PAYMENT RECEIPT HISTORY ── --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Payment Receipt History</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Invoice Purpose</th>
                        <th>Amount Paid</th>
                        <th>Payment Method</th>
                        <th>Status</th>
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
                        <td>
                            @if(($pay->status ?? 'APPROVED') === 'APPROVED')
                                <span class="badge badge-success no-dot" style="font-size:11px">Approved</span>
                            @elseif(($pay->status ?? 'APPROVED') === 'PENDING')
                                <span class="badge badge-warning no-dot" style="font-size:11px">⏳ Pending Approval</span>
                            @else
                                <span class="badge badge-danger no-dot" style="font-size:11px">Rejected</span>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:12px">{{ $pay->paid_at ? \Carbon\Carbon::parse($pay->paid_at)->format('d M Y, h:i A') : '—' }}</td>
                        <td style="text-align:center">
                            @if(($pay->status ?? 'APPROVED') === 'APPROVED')
                                <a href="{{ route('student.fees.receipt', $pay) }}" target="_blank" class="btn btn-outline btn-sm">
                                    Download Receipt
                                </a>
                            @else
                                <span style="font-size:11px; color:#b45309; font-style:italic">⏳ Verification Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No payment transactions recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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

    <div id="payInvoiceModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center; padding:20px; box-sizing:border-box">
        <div style="background:#fff; border-radius:18px; max-width:500px; width:100%; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); animation:modalSlideUp .3s ease">
            <div style="background:linear-gradient(135deg,#047857,#065f46); color:#fff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="font-weight:800; font-size:16px; display:flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-credit-card"></i> অনলাইন ফি পরিশোধ
                    </div>
                    <div style="font-size:11px; opacity:.85; margin-top:2px" id="modalInvNo">INV-00000</div>
                </div>
                <button type="button" onclick="closePayModal()" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer; line-height:1; opacity:0.85">&times;</button>
            </div>

            <form id="payForm" method="POST" action="" style="padding:22px">
                @csrf
                <div style="background:#f0fdf4; border:1.5px solid #bbf7d0; padding:14px 18px; border-radius:12px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center">
                    <div>
                        <div style="font-size:12px; color:#166534; font-weight:700" id="modalInvTitle">Invoice Title</div>
                        <div style="font-size:11px; color:#64748b; margin-top:2px">পরিশোধযোগ্য মোট বকেয়া</div>
                    </div>
                    <div style="font-size:22px; font-weight:800; color:#15803d; text-align:right">
                        ৳<span id="modalDueAmount">0.00</span>
                    </div>
                </div>

                {{-- Amount to Pay --}}
                <div style="margin-bottom:16px">
                    <label style="display:flex; justify-content:space-between; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:6px">
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
                        <label class="modal-gateway-card selected" id="card_sslcommerz" onclick="selectModalGateway('sslcommerz')">
                            <input type="radio" name="payment_method" value="sslcommerz" checked style="display:none">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                                <img src="{{ asset('images/gateways/sslcommerz.png') }}" alt="SSLCommerz" style="height:22px; max-width:115px; object-fit:contain">
                                <span style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px; background:#e0f2fe; color:#0369a1">সব মাধ্যম</span>
                            </div>
                            <div style="font-size:11px; color:#64748b; line-height:1.3">
                                কার্ড, নগদ, রকেট ও ব্যাংক
                            </div>
                        </label>

                        {{-- bKash Card --}}
                        <label class="modal-gateway-card" id="card_bkash" onclick="selectModalGateway('bkash')">
                            <input type="radio" name="payment_method" value="bkash" style="display:none">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                                <img src="{{ asset('images/gateways/bkash.png') }}" alt="bKash" style="height:24px; max-width:85px; object-fit:contain">
                                <span style="font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px; background:#fce7f3; color:#be185d">বিকাশ</span>
                            </div>
                            <div style="font-size:11px; color:#64748b; line-height:1.3">
                                সরাসরি বিকাশ ওয়ালেট ও পিন
                            </div>
                        </label>
                    </div>

                    {{-- Manual / Offline Payment Toggle --}}
                    <div style="margin-top:10px; text-align:right">
                        <button type="button" onclick="toggleManualPayment()" id="toggleManualBtn" style="background:none; border:none; color:#2563eb; font-size:11.5px; font-weight:600; cursor:pointer; text-decoration:underline">
                            অথবা ম্যানুয়াল ব্যাংক / ক্যাশ ভাউচার জমা দিন
                        </button>
                    </div>

                    {{-- Manual Fields (Hidden by default) --}}
                    <div id="manualPaymentFields" style="display:none; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:10px; padding:12px; margin-top:10px">
                        <div style="margin-bottom:10px">
                            <label style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px">ম্যানুয়াল মাধ্যম নির্বাচন করুন</label>
                            <select id="manualMethodSelect" class="form-control" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #cbd5e1; font-size:12.5px" onchange="onManualMethodChange(this.value)">
                                <option value="BANK_TRANSFER">ব্যাংক ডিপোজিট / স্লিপ (Bank Transfer)</option>
                                <option value="CASH">সরাসরি অফিস ক্যাশ (Cash at Office)</option>
                                <option value="NAGAD">নগদ ম্যানুয়াল ট্রানজেকশন (Nagad TrxID)</option>
                                <option value="ROCKET">রকেট ম্যানুয়াল ট্রানজেকশন (Rocket TrxID)</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px">Transaction ID / রেফারেন্স (যদি থাকে)</label>
                            <input type="text" name="transaction_id" id="manualTrxInput" placeholder="যেমন: 8N7A6B5C4D" class="form-control" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #cbd5e1; font-size:12.5px; box-sizing:border-box">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px">
                    <button type="button" onclick="closePayModal()" style="padding:11px 18px; border-radius:9px; border:1.5px solid #cbd5e1; background:#fff; color:#475569; font-weight:600; font-size:13px; cursor:pointer">
                        বাতিল
                    </button>
                    <button type="submit" id="modalPaySubmitBtn" style="padding:11px 24px; border-radius:9px; border:none; background:linear-gradient(135deg,#047857,#065f46); color:#fff; font-weight:700; font-size:14px; cursor:pointer; box-shadow:0 4px 12px rgba(4,120,87,0.3); display:inline-flex; align-items:center; gap:8px">
                        <i class="fa-solid fa-lock"></i>
                        <span id="modalPayBtnText">পেমেন্ট সম্পন্ন করুন</span>
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
        const formatted = '৳' + amt.toLocaleString('en-IN', {minimumFractionDigits: 2});
        const btnText = document.getElementById('modalPayBtnText');

        if (isManualModeActive) {
            btnText.innerText = formatted + ' জমা দিন (ভেরিফিকেশন পেন্ডিং)';
        } else if (currentSelectedGateway === 'bkash') {
            btnText.innerText = 'bKash দিয়ে ' + formatted + ' পরিশোধ করুন';
        } else {
            btnText.innerText = 'SSLCommerz দিয়ে ' + formatted + ' পরিশোধ করুন';
        }
    }

    function openPayModal(invId, title, invNo, dueAmt) {
        document.getElementById('modalInvNo').innerText = invNo;
        document.getElementById('modalInvTitle').innerText = title;
        document.getElementById('modalDueAmount').innerText = parseFloat(dueAmt).toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('payAmountInput').value = dueAmt;
        document.getElementById('payAmountInput').max = dueAmt;

        let actionUrl = "{{ route('student.fees.pay', ':id') }}".replace(':id', invId);
        document.getElementById('payForm').action = actionUrl;

        selectModalGateway('sslcommerz');

        document.getElementById('payInvoiceModal').style.display = 'flex';
    }

    function closePayModal() {
        document.getElementById('payInvoiceModal').style.display = 'none';
    }

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
