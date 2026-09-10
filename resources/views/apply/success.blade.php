<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ভর্তি আবেদন নিশ্চিতকরণ — ইসলামিক অনলাইন মাদ্রাসা</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Kalpurush', 'Inter', -apple-system, sans-serif;
        background: #f8fafc;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        color: #1e293b;
    }
    .card {
        background: #fff;
        border-radius: 16px;
        padding: 36px 40px;
        max-width: 560px;
        width: 100%;
        text-align: center;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
        border: 1px solid #d1fae5;
        border-top: 5px solid #047857;
    }
    @media(max-width: 500px) {
        .card { padding: 24px 18px; }
    }
    .icon {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, #047857, #064e3b);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
    }
    .icon i { font-size: 30px; color: #fff; }
    h1 { font-size: 22px; font-weight: 700; color: #064e3b; margin-bottom: 6px; }
    p.subtext { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 18px; }
    .app-badge {
        display: inline-block;
        background: #ecfdf5;
        border: 1.5px solid #a7f3d0;
        border-radius: 8px;
        padding: 8px 20px;
        margin-bottom: 18px;
        font-size: 20px;
        font-weight: 700;
        color: #047857;
        letter-spacing: .05em;
    }
    .info-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        margin: 16px 0;
        font-size: 13.5px;
    }
    .info-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-table td:first-child {
        color: #64748b;
        font-weight: 600;
        width: 42%;
    }
    .info-table td:last-child {
        font-weight: 600;
        color: #0f172a;
    }
    .credentials-box {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 10px;
        padding: 14px 16px;
        margin: 18px 0;
        text-align: left;
    }
    .credentials-box h4 {
        color: #065f46;
        font-size: 14px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .note {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 12.5px;
        color: #92400e;
        margin-top: 16px;
        text-align: left;
        line-height: 1.6;
    }
    .actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 22px;
        flex-wrap: wrap;
    }
    .btn {
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }
    .btn-primary {
        background: #047857;
        color: #fff;
    }
    .btn-primary:hover { background: #064e3b; }
    .btn-secondary {
        background: #f1f5f9;
        color: #334155;
    }
    .btn-secondary:hover { background: #e2e8f0; }
    @media print {
        .actions { display: none; }
        body { background: #fff; }
        .card { box-shadow: none; border: none; }
    }
    </style>
</head>
<body>

<div class="card" id="printArea">
    <div class="icon">
        <i class="fa-solid fa-check"></i>
    </div>

    @if($form->status === 'APPROVED')
        <h1>ভর্তি নিশ্চিত ও ফি পরিশোধিত!</h1>
        <p class="subtext">আলহামদুলিল্লাহ! আপনার ভর্তি ফি সফলভাবে পরিশোধ হয়েছে এবং ভর্তি অনুমোদিত হয়েছে।</p>
    @else
        <h1>ভর্তি আবেদন জমা সম্পন্ন হয়েছে</h1>
        <p class="subtext">আপনার ভর্তি আবেদন সফলভাবে জমা হয়েছে। নিচের আবেদন নম্বরটি সংরক্ষণ করুন।</p>
    @endif

    <div class="app-badge">{{ $form->application_no }}</div>

    <table class="info-table">
        <tr>
            <td>শিক্ষার্থীর নাম:</td>
            <td>{{ $form->student->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>মোবাইল নম্বর:</td>
            <td>{{ $form->student->phone ?? '—' }}</td>
        </tr>
        <tr>
            <td>কোর্স / প্রোগ্রাম:</td>
            <td>{{ $form->interestedCourse->name ?? '—' }}</td>
        </tr>
        @if($form->batch)
        <tr>
            <td>ব্যাচ:</td>
            <td>{{ $form->batch->name }} ({{ $form->batch->batch_code }})</td>
        </tr>
        @endif
        <tr>
            <td>ভর্তির স্ট্যাটাস:</td>
            <td>
                @if($form->status === 'APPROVED')
                    <span style="background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-circle-check"></i> APPROVED (অনুমোদিত)
                    </span>
                @else
                    <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:700">
                        PENDING REVIEW
                    </span>
                @endif
            </td>
        </tr>
        @if($transaction)
        <tr>
            <td>পেমেন্ট মাধ্যম:</td>
            <td>{{ strtoupper($transaction->gateway) }} ({{ strtoupper($transaction->gateway_mode) }})</td>
        </tr>
        <tr>
            <td>পরিশোধিত ফি:</td>
            <td style="color:#047857;font-weight:700">৳ {{ number_format($transaction->amount, 2) }}</td>
        </tr>
        @if($transaction->gateway_trx_id)
        <tr>
            <td>ট্রানজেকশন TrxID:</td>
            <td style="font-family:monospace">{{ $transaction->gateway_trx_id }}</td>
        </tr>
        @endif
        @endif
    </table>

    {{-- Official Student Credentials Box (If Approved) --}}
    @if($form->status === 'APPROVED' && !empty($form->student->student_code))
    <div class="credentials-box">
        <h4><i class="fa-solid fa-id-card"></i> আপনার অফিসিয়াল শিক্ষার্থী আইডি:</h4>
        <div style="font-size:17px;font-weight:700;color:#047857;letter-spacing:.05em;margin-bottom:6px">
            {{ $form->student->student_code }}
        </div>
        <p style="font-size:12px;color:#475569;margin:0">
            লগইন পাসওয়ার্ড: আপনার মোবাইল নম্বর <strong>{{ $form->student->phone }}</strong> (অথবা এসএমএস/ইমেইলে প্রেরিত পাসওয়ার্ড)।
        </p>
    </div>
    @endif

    <div class="note">
        <i class="fa-solid fa-circle-info" style="margin-right:4px"></i>
        ভর্তির বিস্তারিত তথ্যের কপি আপনার ইমেইলে পাঠানো হয়েছে। আপনার শিক্ষার্থী আইডি বা আবেদন নম্বরটি ভবিষ্যতে ব্যবহারের জন্য সংরক্ষণ করুন।
    </div>

    <div class="actions">
        @if($form->status === 'APPROVED')
            <a href="/login" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i> স্টুডেন্ট পোর্টালে লগইন করুন
            </a>
        @endif
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fa-solid fa-print"></i> রসিদ প্রিন্ট করুন
        </button>
        <a href="/" class="btn btn-secondary">
            <i class="fa-solid fa-house"></i> মূলপাতা
        </a>
    </div>
</div>

</body>
</html>
