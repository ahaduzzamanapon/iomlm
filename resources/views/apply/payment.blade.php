<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ভর্তি ফি পরিশোধ ও নিশ্চিতকরণ — ইসলামিক অনলাইন মাদ্রাসা</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
    :root {
        --iom-green: #047857;
        --iom-dark: #064e3b;
        --iom-light: #ecfdf5;
        --border: #e2e8f0;
        --muted: #64748b;
        --card-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Kalpurush', 'Inter', -apple-system, sans-serif;
        background: #f8fafc;
        color: #1e293b;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .site-header {
        background: #fff;
        border-bottom: 1px solid var(--border);
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .site-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
    }
    .site-logo-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--iom-dark);
        letter-spacing: .02em;
    }
    .site-logo-sub {
        font-size: 11px;
        color: var(--muted);
    }
    .hero-banner {
        background: linear-gradient(135deg, #064e3b 0%, #047857 60%, #059669 100%);
        color: #fff;
        text-align: center;
        padding: 30px 16px 40px;
    }
    .hero-banner h1 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .hero-banner p {
        font-size: 14px;
        opacity: .9;
        max-width: 580px;
        margin: 0 auto;
    }

    .container {
        max-width: 680px;
        width: 100%;
        margin: -24px auto 40px;
        padding: 0 16px;
        flex: 1;
    }

    /* Stepper */
    .stepper {
        display: flex;
        background: #fff;
        border-radius: 12px;
        padding: 12px 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border);
        margin-bottom: 20px;
        align-items: center;
        justify-content: space-between;
    }
    .step-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
    }
    .step-item.active {
        color: var(--iom-dark);
    }
    .step-item.completed {
        color: #047857;
    }
    .step-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
    }
    .step-item.active .step-circle {
        background: var(--iom-green);
        color: #fff;
    }
    .step-item.completed .step-circle {
        background: #10b981;
        color: #fff;
    }
    .step-line {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
        margin: 0 12px;
    }

    .card {
        background: #fff;
        border-radius: 14px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border);
        margin-bottom: 20px;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .summary-table td {
        padding: 8px 6px;
        border-bottom: 1px solid #f1f5f9;
    }
    .summary-table td:first-child {
        color: var(--muted);
        width: 40%;
        font-weight: 500;
    }
    .summary-table td:last-child {
        font-weight: 600;
        color: #0f172a;
    }

    /* Waiver Box */
    .waiver-box {
        background: #f0fdf4;
        border: 1.5px dashed #86efac;
        border-radius: 10px;
        padding: 16px;
        margin: 18px 0;
    }
    .waiver-box label {
        color: #047857;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 8px;
        display: block;
    }
    .waiver-input-group {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .waiver-input {
        flex: 1;
        padding: 9px 12px;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        background: #fff;
        outline: none;
    }
    .waiver-input:focus {
        border-color: var(--iom-green);
    }
    .btn-apply {
        background: var(--iom-green);
        color: #fff;
        border: none;
        padding: 9px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
    }
    .btn-apply:hover {
        background: var(--iom-dark);
    }

    /* Fee Breakdown Box */
    .fee-summary-card {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        margin: 18px 0;
    }
    .fee-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 14px;
        color: #475569;
    }
    .fee-row.total-row {
        border-top: 1.5px dashed #cbd5e1;
        margin-top: 8px;
        padding-top: 10px;
        font-size: 17px;
        font-weight: 700;
        color: #064e3b;
    }

    /* Gateways */
    .payment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 10px;
    }
    @media(max-width: 520px) {
        .payment-grid { grid-template-columns: 1fr; }
    }
    .payment-card {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #fff;
    }
    .payment-card:hover {
        border-color: #a7f3d0;
        background: #f0fdf4;
    }
    .payment-card input[type="radio"] {
        margin-top: 3px;
        accent-color: var(--iom-green);
        width: 18px;
        height: 18px;
    }
    .payment-card.selected {
        border-color: var(--iom-green);
        background: #f0fdf4;
        box-shadow: 0 0 0 1px var(--iom-green);
    }
    .gateway-title {
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .gateway-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
        min-height: 32px;
    }
    .gateway-img {
        display: block;
        object-fit: contain;
    }
    .gateway-tag {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        white-space: nowrap;
    }
    .gateway-tag-ssl {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .gateway-tag-bkash {
        background: #fce7f3;
        color: #be185d;
        border: 1px solid #fbcfe8;
    }
    .gateway-desc {
        font-size: 11px;
        color: #64748b;
        margin-top: 3px;
        line-height: 1.4;
    }

    .btn-pay {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #047857 0%, #064e3b 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s;
        box-shadow: 0 3px 8px rgba(4, 120, 87, 0.25);
        margin-top: 16px;
    }
    .btn-pay:hover {
        background: linear-gradient(135deg, #064e3b 0%, #022c22 100%);
        box-shadow: 0 4px 14px rgba(4, 120, 87, 0.35);
    }

    .site-footer {
        background: #022c22;
        color: rgba(255,255,255,.7);
        text-align: center;
        padding: 20px;
        font-size: 12px;
        margin-top: auto;
    }
    </style>
</head>
<body>

<header class="site-header">
    <a href="/" class="site-logo">
        <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:44px;width:auto;object-fit:contain">
        <div>
            <div class="site-logo-name">ISLAMIC ONLINE MADRASAH</div>
            <div class="site-logo-sub">Through Knowledge, Towards Jannah</div>
        </div>
    </a>
</header>

<div class="hero-banner">
    <h1>ভর্তি ফি পরিশোধ ও চূড়ান্ত নিশ্চিতকরণ</h1>
    <p>আপনার প্রাথমিক আবেদন সফলভাবে গৃহীত হয়েছে। অনুগ্রহ করে ভর্তি ফি পরিশোধ করে আপনার ভর্তি সম্পন্ন করুন।</p>
</div>

<div class="container">
    {{-- 2-Step Progress Stepper --}}
    <div class="stepper">
        <div class="step-item completed">
            <div class="step-circle"><i class="fa-solid fa-check"></i></div>
            <span>১. প্রাথমিক তথ্য পূরণ</span>
        </div>
        <div class="step-line" style="background:#10b981"></div>
        <div class="step-item active">
            <div class="step-circle">২</div>
            <span>২. ফি পরিশোধ ও ভর্তি</span>
        </div>
    </div>

    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991b1b">
        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('apply.payment.process', $form->application_no) }}" id="paymentForm">
        @csrf

        {{-- Applicant & Course Information --}}
        <div class="card">
            <div style="font-weight:700;font-size:16px;color:#0f172a;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between">
                <span><i class="fa-solid fa-id-card text-emerald-600"></i> আবেদন বিবরণী</span>
                <span style="font-size:12px;padding:4px 10px;background:#ecfdf5;color:#047857;border-radius:6px;font-weight:700">
                    আবেদন নং: {{ $form->application_no }}
                </span>
            </div>

            <table class="summary-table">
                <tr>
                    <td>আবেদনকারীর নাম:</td>
                    <td>{{ $form->student->name }}</td>
                </tr>
                <tr>
                    <td>নির্বাচিত কোর্স:</td>
                    <td>{{ $course->name }} ({{ $course->duration_value }} {{ strtolower($course->duration_unit) }}s)</td>
                </tr>
                <tr>
                    <td>নির্ধারিত ব্যাচ:</td>
                    <td>{{ $batch ? $batch->name . ' (' . $batch->batch_code . ')' : 'সাধারণ ব্যাচ' }}</td>
                </tr>
                <tr>
                    <td>মোবাইল নম্বর:</td>
                    <td>{{ $form->student->phone }}</td>
                </tr>
                <tr>
                    <td>ইমেইল ঠিকানা:</td>
                    <td>{{ $form->student->email }}</td>
                </tr>
                <tr>
                    <td>শাখা / লিঙ্গ:</td>
                    <td>{{ $form->student->gender === 'Female' ? 'বোন শাখা (মহিলা)' : 'ভাই শাখা (পুরুষ)' }}</td>
                </tr>
            </table>

            {{-- Coupon / Waiver Code Box --}}
            <div class="waiver-box">
                <label>
                    <i class="fa-solid fa-ticket"></i> কুপন কোড / ছাড় কোড (ঐচ্ছিক)
                </label>
                <div class="waiver-input-group">
                    <input type="text" id="waiver_code_input" name="waiver_code" value="{{ old('waiver_code', $form->waiver_code) }}"
                           placeholder="কুপন কোড থাকলে লিখুন" class="waiver-input">
                    <button type="button" class="btn-apply" onclick="applyWaiverCode()">যাচাই করুন (Apply)</button>
                </div>
                <div id="waiver-status-msg" style="font-size:12px;margin-top:6px;font-weight:600">
                    @if($form->waiver_code)
                        <span style="color:#047857">✓ কুপন কোড '{{ $form->waiver_code }}' সংযুক্ত আছে।</span>
                    @endif
                </div>
            </div>

            {{-- Fee Summary Breakdown --}}
            <div class="fee-summary-card">
                <div style="font-weight:700;font-size:15px;color:#0f172a;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-calculator text-emerald-600"></i> ভর্তি ফি হিসাব বিবরণী
                </div>
                <div class="fee-row">
                    <span>কোর্স/ব্যাচ ভর্তি ফি:</span>
                    <span>৳ <span id="summary-base-fee">{{ number_format($baseFee, 2) }}</span></span>
                </div>
                <div class="fee-row" id="summary-discount-row" style="{{ $discountAmount > 0 ? 'display:flex;' : 'display:none;' }}color:#047857">
                    <span>কুপন / স্কলারশিপ ছাড়:</span>
                    <span>- ৳ <span id="summary-discount-fee">{{ number_format($discountAmount, 2) }}</span></span>
                </div>
                <div class="fee-row total-row">
                    <span>মোট প্রদেয় ফি (Net Payable):</span>
                    <span>৳ <span id="summary-net-fee">{{ number_format($netPayable, 2) }}</span></span>
                </div>
            </div>

            {{-- Payment Gateways --}}
            @if($sslActive || $bkashActive)
            <div class="payment-gateways-wrap" id="payment-gateways-wrap" style="{{ $netPayable > 0 ? '' : 'display:none;' }}">
                <label style="font-weight:700;font-size:14px;color:#0f172a;display:flex;align-items:center;gap:6px;margin-bottom:8px">
                    <i class="fa-solid fa-credit-card text-emerald-600"></i>
                    পেমেন্ট মাধ্যম নির্বাচন করুন <span style="color:#ef4444">*</span>
                </label>

                <div class="payment-grid">
                    @if($sslActive)
                    <label class="payment-card {{ !$bkashActive || old('payment_gateway', 'sslcommerz') === 'sslcommerz' ? 'selected' : '' }}" onclick="selectGatewayCard(this)">
                        <input type="radio" name="payment_gateway" value="sslcommerz"
                               {{ !$bkashActive || old('payment_gateway', 'sslcommerz') === 'sslcommerz' ? 'checked' : '' }}>
                        <div style="flex:1">
                            <div class="gateway-header">
                                <img src="{{ asset('images/gateways/sslcommerz.png') }}" alt="SSLCommerz" class="gateway-img" style="height:26px;max-width:135px">
                                <span class="gateway-tag gateway-tag-ssl">কার্ড / নেট ব্যাংকিং</span>
                            </div>
                            <div class="gateway-desc">
                                কার্ড, বিকাশ, নগদ, রকেট ও সব ব্যাংকের মাধ্যমে পেমেন্ট
                            </div>
                        </div>
                    </label>
                    @endif

                    @if($bkashActive)
                    <label class="payment-card {{ (!$sslActive || old('payment_gateway') === 'bkash') ? 'selected' : '' }}" onclick="selectGatewayCard(this)">
                        <input type="radio" name="payment_gateway" value="bkash"
                               {{ (!$sslActive || old('payment_gateway') === 'bkash') ? 'checked' : '' }}>
                        <div style="flex:1">
                            <div class="gateway-header">
                                <img src="{{ asset('images/gateways/bkash.png') }}" alt="bKash" class="gateway-img" style="height:28px;max-width:100px">
                                <span class="gateway-tag gateway-tag-bkash">বিকাশ</span>
                            </div>
                            <div class="gateway-desc">
                                বিকাশ ওয়ালেট ও পিন দিয়ে সরাসরি দ্রুত পেমেন্ট
                            </div>
                        </div>
                    </label>
                    @endif
                </div>
            </div>
            @endif

            {{-- Submit / Pay Button --}}
            <button type="submit" class="btn-pay" id="btn-pay-submit">
                <i class="fa-solid fa-lock"></i>
                <span id="btn-pay-text">
                    @if($netPayable > 0)
                        ৳{{ number_format($netPayable, 0) }} পরিশোধ করে ভর্তি সম্পন্ন করুন
                    @else
                        বিনামূল্যে ভর্তি সম্পন্ন করুন (১০০% স্কলারশিপ)
                    @endif
                </span>
            </button>
        </div>
    </form>
</div>

<footer class="site-footer">
    © {{ date('Y') }} ইসলামিক অনলাইন মাদ্রাসা (IOM). সর্বস্বত্ব সংরক্ষিত।
</footer>

<script>
let currentBaseFee = {{ (float)$baseFee }};
let currentDiscountAmount = {{ (float)$discountAmount }};
let waiverApprovedFee = null;
let currentDiscountPercent = {{ (float)($form->discount_percent ?? 0) }};

function selectGatewayCard(cardElement) {
    document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('selected'));
    cardElement.classList.add('selected');
    const radio = cardElement.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}

function applyWaiverCode() {
    const code = document.getElementById('waiver_code_input').value.trim();
    const msg = document.getElementById('waiver-status-msg');
    if (!code) {
        msg.style.color = '#dc2626';
        msg.innerText = 'দয়া করে কুপন বা ছাড় কোডটি লিখুন।';
        return;
    }

    msg.style.color = '#64748b';
    msg.innerText = 'যাচাই করা হচ্ছে...';

    const courseId = {{ $course->id }};

    fetch(`/api/waiver-lookup?code=${encodeURIComponent(code)}&course_id=${courseId}`)
        .then(r => r.json())
        .then(data => {
            if (data.valid) {
                msg.style.color = '#047857';
                let info = `✓ কুপন কোড সক্রিয় হয়েছে!`;

                if (data.approved_admission_fee !== null && (data.apply_for === 'ADMISSION_FEE' || data.apply_for === 'BOTH')) {
                    waiverApprovedFee = parseFloat(data.approved_admission_fee);
                    currentDiscountPercent = 0;
                    info += ` (নির্ধারিত ভর্তি ফি: ৳${waiverApprovedFee})`;
                } else if (data.discount_percent > 0) {
                    waiverApprovedFee = null;
                    currentDiscountPercent = parseFloat(data.discount_percent);
                    info += ` (${currentDiscountPercent}% ছাড়)`;
                }

                msg.innerText = info;
                updateFees();
            } else {
                msg.style.color = '#dc2626';
                msg.innerText = data.message || 'কুপন কোডটি সঠিক নয় বা এই কোর্সে প্রযোজ্য নয়।';
                waiverApprovedFee = null;
                currentDiscountPercent = 0;
                updateFees();
            }
        })
        .catch(() => {
            msg.style.color = '#dc2626';
            msg.innerText = 'কুপন যাচাই করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
        });
}

function updateFees() {
    let discount = 0.0;
    if (waiverApprovedFee !== null) {
        const payable = Math.min(currentBaseFee, waiverApprovedFee);
        discount = Math.max(0, currentBaseFee - payable);
    } else if (currentDiscountPercent > 0) {
        discount = Math.round((currentBaseFee * currentDiscountPercent) / 100 * 100) / 100;
    }

    currentDiscountAmount = Math.min(currentBaseFee, discount);
    const net = Math.max(0, currentBaseFee - currentDiscountAmount);

    document.getElementById('summary-base-fee').innerText = currentBaseFee.toFixed(2);
    const discountRow = document.getElementById('summary-discount-row');
    if (currentDiscountAmount > 0) {
        discountRow.style.display = 'flex';
        document.getElementById('summary-discount-fee').innerText = currentDiscountAmount.toFixed(2);
    } else {
        discountRow.style.display = 'none';
    }
    document.getElementById('summary-net-fee').innerText = net.toFixed(2);

    const payBtnText = document.getElementById('btn-pay-text');
    const gatewayWrap = document.getElementById('payment-gateways-wrap');

    if (net > 0) {
        payBtnText.innerText = '৳' + net.toFixed(0) + ' পরিশোধ করে ভর্তি সম্পন্ন করুন';
        if (gatewayWrap) gatewayWrap.style.display = 'block';
    } else {
        payBtnText.innerText = 'বিনামূল্যে ভর্তি সম্পন্ন করুন (১০০% স্কলারশিপ)';
        if (gatewayWrap) gatewayWrap.style.display = 'none';
    }
}
</script>
</body>
</html>
