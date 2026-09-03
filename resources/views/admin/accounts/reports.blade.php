<x-admin-layout>
    <x-slot name="title">Accounts Financial Reports</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Financial &amp; Collection Reports</h1>
            <p>Date-wise collection statement, head-wise revenue summary, and transaction audit log</p>
        </div>
    </div>

    {{-- Date Filter Form --}}
    <form method="GET" action="{{ route('admin.accounts.reports') }}" class="card" style="padding:16px;margin-bottom:24px">
        <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
            <div class="form-group" style="margin-bottom:0">
                <label style="font-size:12px;color:#64748b">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}" style="height:38px">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label style="font-size:12px;color:#64748b">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}" style="height:38px">
            </div>
            <div style="margin-top:18px">
                <button type="submit" class="btn btn-primary" style="height:38px">Filter Statement</button>
            </div>
        </div>
    </form>

    {{-- Category Revenue Breakdown --}}
    <div class="grid-2" style="margin-bottom:24px">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Head-wise Revenue Breakdown</span>
            </div>
            <div class="card-body">
                <table class="table" style="font-size:13px">
                    <thead>
                        <tr>
                            <th>Fee Category</th>
                            <th style="text-align:right">Collected Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalHeadSum = 0; @endphp
                        @forelse($categorySummary as $cat => $sum)
                        @php $totalHeadSum += $sum; @endphp
                        <tr>
                            <td><strong>{{ $cat }}</strong></td>
                            <td style="text-align:right;font-weight:700;color:#10b981">৳{{ number_format($sum, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="text-align:center;color:#94a3b8">No revenue recorded in this period.</td></tr>
                        @endforelse
                        <tr style="background:#f8fafc;font-size:14px">
                            <th>Total Revenue</th>
                            <th style="text-align:right;color:#2563eb">৳{{ number_format($totalHeadSum, 2) }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">ℹ️ Report Summary</span>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:#475569;margin-bottom:8px">
                    Period: <strong>{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</strong>
                </p>
                <p style="font-size:13px;color:#475569;margin-bottom:8px">
                    Total Transactions: <strong>{{ $payments->count() }} payments received</strong>
                </p>
                <p style="font-size:13px;color:#475569">
                    Net Cash Inflow: <strong style="color:#10b981;font-size:16px">৳{{ number_format($payments->sum('amount'), 2) }}</strong>
                </p>
            </div>
        </div>
    </div>

    {{-- Transactions Statement Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Detailed Transaction Collection Log</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Student Name &amp; Code</th>
                        <th>Invoice / Particulars</th>
                        <th>Method</th>
                        <th>Amount Paid</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                    <tr>
                        <td style="font-weight:700;color:#2563eb;font-size:12px">{{ $pay->payment_no }}</td>
                        <td>
                            <strong>{{ $pay->student->name }}</strong><br>
                            <span style="color:#64748b;font-size:11px">{{ $pay->student->student_code }}</span>
                        </td>
                        <td class="td-primary">{{ $pay->invoice->title ?? '—' }}</td>
                        <td><span class="badge badge-secondary no-dot">{{ $pay->payment_method }}</span></td>
                        <td><strong style="color:#10b981">৳{{ number_format($pay->amount, 2) }}</strong></td>
                        <td class="td-muted" style="font-size:12px">{{ $pay->paid_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:#94a3b8">No transactions found in selected date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
