<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পেমেন্ট স্ট্যাটাস — ইসলামিক অনলাইন মাদ্রাসা</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background: #f8fafc;
        color: #1e293b;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .status-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        max-width: 520px;
        width: 100%;
        padding: 36px 28px;
        text-align: center;
    }
    .status-icon-wrap {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
    }
    .icon-pending {
        background: #fef3c7;
        color: #d97706;
        animation: pulse 1.5s infinite;
    }
    .icon-failed {
        background: #fee2e2;
        color: #dc2626;
    }
    .icon-success {
        background: #dcfce7;
        color: #16a34a;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.08); opacity: 0.8; }
    }
    h2 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
    p.desc { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
    .trx-details {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 24px;
        text-align: left;
        font-size: 13px;
    }
    .trx-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .trx-row:last-child { border-bottom: none; }
    .trx-label { color: #64748b; font-weight: 600; }
    .trx-val { color: #0f172a; font-weight: 700; }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all .2s;
        border: none;
        font-family: inherit;
    }
    .btn-primary {
        background: #047857;
        color: #fff;
    }
    .btn-primary:hover { background: #065f46; }
    .btn-outline {
        background: #fff;
        border: 1.5px solid #cbd5e1;
        color: #334155;
        margin-top: 10px;
    }
    .btn-outline:hover { background: #f1f5f9; }
    </style>
</head>
<body>

<div class="status-card">
    @if($transaction->status === 'SUCCESS')
        <div class="status-icon-wrap icon-success">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 style="color:#16a34a">পেমেন্ট সফল হয়েছে!</h2>
        <p class="desc">আপনার পেমেন্ট সফলভাবে যাচাই ও গৃহীত হয়েছে। নিচে আপনার বিস্তারিত তথ্য দেখুন।</p>
    @elseif($transaction->isPending())
        <div class="status-icon-wrap icon-pending">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
        <h2 style="color:#d97706">পেমেন্ট যাচাই করা হচ্ছে...</h2>
        <p class="desc">
            পেমেন্ট গেটওয়ের সাথে স্বয়ংক্রিয় যাচাইকরণ প্রক্রিয়া চলছে। নেটওয়ার্ক ইস্যু থাকলে সর্বোচ্চ ৩ মিনিটের মধ্যে সিস্টেম নিজে থেকে স্ট্যাটাস হালনাগাদ করবে।
        </p>
    @else
        <div class="status-icon-wrap icon-failed">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h2 style="color:#dc2626">পেমেন্ট সম্পন্ন হয়নি</h2>
        <p class="desc">
            {{ $transaction->error_message ?: 'পেমেন্ট গেটওয়েতে প্রক্রিয়াটি বাতিল বা ব্যর্থ হয়েছে।' }}
        </p>
    @endif

    {{-- Details Box --}}
    <div class="trx-details">
        <div class="trx-row">
            <span class="trx-label">ট্রানজেকশন আইডি:</span>
            <span class="trx-val" style="font-family:monospace">{{ $transaction->tran_id }}</span>
        </div>
        <div class="trx-row">
            <span class="trx-label">পেমেন্ট মাধ্যম:</span>
            <span class="trx-val">{{ strtoupper($transaction->gateway) }} ({{ strtoupper($transaction->gateway_mode) }})</span>
        </div>
        <div class="trx-row">
            <span class="trx-label">টাকার পরিমাণ:</span>
            <span class="trx-val">৳ {{ number_format($transaction->amount, 2) }}</span>
        </div>
        <div class="trx-row">
            <span class="trx-label">স্ট্যাটাস:</span>
            <span class="trx-val" style="color:{{ $transaction->status === 'SUCCESS' ? '#16a34a' : ($transaction->isPending() ? '#d97706' : '#dc2626') }}">
                {{ $transaction->status }}
            </span>
        </div>
        @if($transaction->gateway_trx_id)
        <div class="trx-row">
            <span class="trx-label">গেটওয়ে TrxID:</span>
            <span class="trx-val" style="font-family:monospace">{{ $transaction->gateway_trx_id }}</span>
        </div>
        @endif
        @if($transaction->admissionForm)
        <div class="trx-row">
            <span class="trx-label">আবেদন নম্বর:</span>
            <span class="trx-val">{{ $transaction->admissionForm->application_no }}</span>
        </div>
        @endif
    </div>

    {{-- Actions --}}
    @if($transaction->status === 'SUCCESS' && $transaction->admissionForm)
        <a href="{{ route('apply.success', $transaction->admissionForm->application_no) }}" class="btn btn-primary">
            <i class="fa-solid fa-arrow-right"></i> আবেদন রসিদ ও লগইন তথ্য দেখুন
        </a>
    @elseif($transaction->isPending())
        <button type="button" class="btn btn-primary" onclick="window.location.reload()">
            <i class="fa-solid fa-arrows-rotate"></i> স্ট্যাটাস রিফ্রেশ করুন
        </button>
    @else
        <a href="{{ route('apply.show') }}" class="btn btn-primary">
            <i class="fa-solid fa-arrow-rotate-left"></i> পুনরায় ভর্তি আবেদন করুন
        </a>
    @endif

    <a href="/" class="btn btn-outline">
        <i class="fa-solid fa-house"></i> হোমপেজে ফিরে যান
    </a>
</div>

@if($transaction->isPending())
<script>
// Auto-poll status every 3 seconds for seamless user experience
let checkCount = 0;
const interval = setInterval(function() {
    checkCount++;
    if (checkCount > 40) { // Stop polling after 2 minutes
        clearInterval(interval);
        return;
    }

    fetch("{{ route('payment.status.ajax', $transaction->tran_id) }}")
        .then(r => r.json())
        .then(data => {
            if (data.success && data.status === 'SUCCESS') {
                clearInterval(interval);
                if (data.application_no) {
                    window.location.href = "/apply/success/" + data.application_no;
                } else {
                    window.location.reload();
                }
            } else if (data.status === 'FAILED' || data.status === 'CANCELLED') {
                clearInterval(interval);
                window.location.reload();
            }
        })
        .catch(e => console.log('Polling check error:', e));
}, 3000);
</script>
@endif

</body>
</html>
