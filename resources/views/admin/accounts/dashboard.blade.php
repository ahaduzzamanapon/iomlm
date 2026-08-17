<x-admin-layout>
    <x-slot name="title">Accounts Counter & Fee Collection</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>💳 Counter &amp; Fee Collection</h1>
            <p>Real-time financial summary, instant student search, and quick fee collection counter</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.accounts.invoices') }}" class="btn btn-outline">📑 All Invoices</a>
            <a href="{{ route('admin.accounts.reports') }}" class="btn btn-primary">📊 Collection Reports</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon green">৳</div>
            <div class="stat-info">
                <div class="stat-value">৳{{ number_format($stats['today_collected'], 2) }}</div>
                <div class="stat-label">Collected Today</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">📅</div>
            <div class="stat-info">
                <div class="stat-value">৳{{ number_format($stats['month_collected'], 2) }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon violet">🏛️</div>
            <div class="stat-info">
                <div class="stat-value">৳{{ number_format($stats['total_collected'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">⚠️</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#e11d48">৳{{ number_format($stats['total_due'], 2) }}</div>
                <div class="stat-label">Total Outstanding Dues</div>
            </div>
        </div>
    </div>

    {{-- Instant Student Search Counter --}}
    <div class="card" style="margin-bottom:24px;border-top:4px solid #6366f1">
        <div class="card-header">
            <span class="card-title">🔍 Quick Fee Collection Counter</span>
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
                                    👤 Student: <strong>{{ $inv->student->name }}</strong> (ID: <span style="color:#2563eb;font-weight:600">{{ $inv->student->student_code }}</span>, Phone: {{ $inv->student->phone }})
                                </div>
                                <div style="font-size:12px;color:#475569;margin-top:2px">
                                    Total: ৳{{ number_format($inv->payable_amount, 2) }} &middot;
                                    Paid: ৳{{ number_format($inv->paid_amount, 2) }} &middot;
                                    <strong style="color:#e11d48">Due: ৳{{ number_format($inv->due_amount, 2) }}</strong>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-success btn-sm" onclick="openPaymentModal({{ $inv->id }}, '{{ $inv->invoice_no }}', {{ $inv->due_amount }})">
                                    💵 Receive ৳{{ number_format($inv->due_amount, 2) }}
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

    {{-- Recent Payment History --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">🧾 Recent Payment Transactions</span>
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
                        <td class="td-muted" style="font-size:12px">{{ $pay->paid_at->format('d M Y, h:i A') }}</td>
                        <td style="text-align:center">
                            <a href="{{ route('admin.accounts.payments.receipt', $pay) }}" target="_blank" class="btn btn-outline btn-sm">
                                🖨️ Receipt
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
    <div class="modal-overlay" id="collectModal" style="display:none">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <span class="modal-title">💵 Collect Payment</span>
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
                    <button type="submit" class="btn btn-success">✓ Confirm &amp; Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openPaymentModal(invoiceId, invNo, dueAmount) {
        document.getElementById('modalInvNo').innerText = invNo;
        document.getElementById('modalAmount').value = dueAmount;
        document.getElementById('modalAmount').max = dueAmount;
        document.getElementById('collectForm').action = '/admin/accounts/invoices/' + invoiceId + '/collect';
        document.getElementById('collectModal').style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    </script>
</x-admin-layout>
