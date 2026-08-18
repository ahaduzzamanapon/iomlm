<x-student-layout>
    <x-slot name="title">My Fees & Dues</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>💳 My Fees &amp; Payment Receipts</h1>
            <p>Track your semester dues, admission fees, and download official payment receipts</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon orange">⚠️</div>
            <div class="stat-info">
                <div class="stat-value" style="color:{{ $totalDue > 0 ? '#e11d48' : '#10b981' }}">
                    ৳{{ number_format($totalDue, 2) }}
                </div>
                <div class="stat-label">Total Outstanding Dues</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✓</div>
            <div class="stat-info">
                <div class="stat-value">৳{{ number_format($totalPaid, 2) }}</div>
                <div class="stat-label">Total Fees Paid</div>
            </div>
        </div>
    </div>

    {{-- Dues & Invoices --}}
    <div class="card" style="margin-bottom:24px">
        <div class="card-header">
            <span class="card-title">📑 My Invoices &amp; Fee Statements</span>
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
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td style="font-weight:700;color:#3b82f6;font-size:12px">{{ $inv->invoice_no }}</td>
                        <td>
                            <strong>{{ $inv->title }}</strong><br>
                            <span class="badge badge-secondary no-dot" style="font-size:10px">{{ $inv->category }}</span>
                        </td>
                        <td>৳{{ number_format($inv->payable_amount, 2) }}</td>
                        <td><span style="color:#10b981">৳{{ number_format($inv->paid_amount, 2) }}</span></td>
                        <td>
                            @if($inv->due_amount > 0)
                                <strong style="color:#e11d48">৳{{ number_format($inv->due_amount, 2) }}</strong>
                            @else
                                <span style="color:#10b981">৳0.00</span>
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

    {{-- Payment Receipts History --}}
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
</x-student-layout>
