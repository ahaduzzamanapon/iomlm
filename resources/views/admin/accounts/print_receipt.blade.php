<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Money Receipt #{{ $payment->payment_no }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #1e293b; }
        .receipt-card { max-width: 650px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 20px; }
        .institute-name { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
        .receipt-title { font-size: 14px; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; font-size: 13px; }
        .info-label { color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 600; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
        .table th, .table td { border: 1px solid #e2e8f0; padding: 10px 12px; text-align: left; }
        .table th { background: #f1f5f9; color: #475569; font-weight: 700; }
        .amount-box { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px; text-align: center; font-size: 18px; font-weight: 800; color: #047857; margin-bottom: 30px; }
        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; font-size: 12px; color: #64748b; }
        .sign-line { border-top: 1px solid #94a3b8; width: 160px; text-align: center; padding-top: 4px; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-card { border: none; box-shadow: none; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div style="text-align:center;margin-bottom:16px" class="btn-print">
        <button onclick="window.print()" style="background:#6366f1;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-weight:700;cursor:pointer">
            Print Money Receipt
        </button>
    </div>

    <div class="receipt-card">
        <div class="header" style="display:flex;flex-direction:column;align-items:center">
            <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:48px;width:auto;object-fit:contain;margin-bottom:6px">
            <h1 class="institute-name">Islamic Online Madrasah</h1>
            <div class="receipt-title">OFFICIAL MONEY RECEIPT</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px">Receipt No: <strong>{{ $payment->payment_no }}</strong></div>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-label">Student Details</div>
                <strong>{{ $payment->student->name }}</strong><br>
                Student ID: <span style="color:#2563eb;font-weight:700">{{ $payment->student->student_code ?? 'Unassigned' }}</span><br>
                Phone: {{ $payment->student->phone }}
            </div>
            <div style="text-align:right">
                <div class="info-label">Transaction Details</div>
                Date: <strong>{{ $payment->paid_at->format('d M Y, h:i A') }}</strong><br>
                Payment Method: <strong>{{ $payment->payment_method }}</strong><br>
                Trx ID: {{ $payment->transaction_id ?? 'N/A' }}
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Particulars / Description</th>
                    <th style="text-align:right">Paid Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:700;color:#2563eb">{{ $payment->invoice->invoice_no }}</td>
                    <td>{{ $payment->invoice->title }}</td>
                    <td style="text-align:right;font-weight:700">৳{{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="amount-box">
            Amount Received: ৳{{ number_format($payment->amount, 2) }} BDT
        </div>

        <div class="footer">
            <div>
                Received By: <strong>{{ $payment->receivedBy->name ?? 'Accounts Officer' }}</strong><br>
                <small>Generated automatically by IOM LMS</small>
            </div>
            <div class="sign-line">
                Authorized Signature
            </div>
        </div>
    </div>

</body>
</html>
