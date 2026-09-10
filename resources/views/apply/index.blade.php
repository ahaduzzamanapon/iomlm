<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>অনলাইন ভর্তি আবেদন — ইসলামিক অনলাইন মাদ্রাসা</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
        --iom-green: #047857;
        --iom-dark: #064e3b;
        --iom-light: #ecfdf5;
        --iom-mint: #d1fae5;
        --border: #e2e8f0;
        --text: #1e293b;
        --muted: #64748b;
        --bg: #f8fafc;
        --red: #dc2626;
    }
    body {
        font-family: 'Kalpurush', 'Noto Sans Bengali', 'Inter', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
    }
    .site-header {
        background: #fff;
        border-bottom: 1px solid var(--iom-mint);
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 68px;
    }
    .site-logo { display: flex; align-items: center; gap: 12px; }
    .site-logo-name { font-size: 16px; font-weight: 700; color: var(--iom-green); line-height: 1.2; }
    .site-logo-sub { font-size: 11px; color: var(--muted); }
    .btn-outline-sm {
        padding: 7px 16px;
        border: 1.5px solid var(--iom-green);
        border-radius: 6px;
        color: var(--iom-green);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        background: #fff;
        transition: all .15s;
    }
    .btn-outline-sm:hover { background: var(--iom-green); color: #fff; }

    .hero-banner {
        background: linear-gradient(135deg, #022c22 0%, #064e3b 100%);
        color: #fff;
        padding: 24px 20px;
        text-align: center;
    }
    .hero-banner h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
    .hero-banner p { font-size: 13px; opacity: 0.9; max-width: 600px; margin: 0 auto; line-height: 1.6; }

    .apply-container { max-width: 680px; margin: -20px auto 40px; padding: 0 16px; }
    .form-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .form-card-header {
        background: #fff;
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .form-card-title { font-size: 17px; font-weight: 700; color: #064e3b; }
    .form-card-body { padding: 24px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media(max-width:600px) { .form-row { grid-template-columns: 1fr; gap: 12px; } }
    .form-group { margin-bottom: 18px; }
    .form-group:last-child { margin-bottom: 0; }
    label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    label .req { color: var(--red); margin-left: 3px; font-weight: 700; }
    input[type="text"], input[type="email"], select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text);
        background: #fff;
        outline: none;
        transition: border-color .15s;
        font-family: inherit;
    }
    input:focus, select:focus {
        border-color: var(--iom-green);
        box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.12);
    }

    .waiver-box {
        background: #f0fdf4;
        border: 1.5px dashed #86efac;
        padding: 14px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .terms-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 12px;
        line-height: 1.7;
        color: #475569;
        max-height: 110px;
        overflow-y: auto;
        margin-bottom: 12px;
    }
    .info-callout {
        background: #ecfdf5;
        border-left: 4px solid var(--iom-green);
        padding: 12px 16px;
        border-radius: 0 8px 8px 0;
        font-size: 13px;
        color: #065f46;
        margin-bottom: 20px;
        line-height: 1.6;
    }

    /* Fee & Payment Card Styling */
    .fee-summary-card {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .fee-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 13.5px;
        color: #475569;
    }
    .fee-row.total-row {
        border-top: 1.5px dashed #cbd5e1;
        margin-top: 8px;
        padding-top: 10px;
        font-size: 16px;
        font-weight: 700;
        color: #064e3b;
    }
    .payment-gateways-wrap {
        margin-bottom: 22px;
    }
    .payment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 10px;
    }
    @media(max-width: 540px) {
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
        accent-color: #047857;
        width: 18px;
        height: 18px;
    }
    .payment-card.selected {
        border-color: #047857;
        background: #f0fdf4;
        box-shadow: 0 0 0 1px #047857;
    }
    .gateway-title {
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .gateway-desc {
        font-size: 11px;
        color: #64748b;
        margin-top: 3px;
        line-height: 1.4;
    }

    .btn-submit {
        width: 100%;
        padding: 13px;
        background: linear-gradient(135deg, #047857 0%, #064e3b 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s;
        box-shadow: 0 2px 6px rgba(4, 120, 87, 0.25);
    }
    .btn-submit:hover {
        background: linear-gradient(135deg, #064e3b 0%, #022c22 100%);
        box-shadow: 0 4px 12px rgba(4, 120, 87, 0.35);
    }
    .site-footer {
        background: #022c22;
        color: rgba(255,255,255,.7);
        text-align: center;
        padding: 20px;
        font-size: 12px;
    }
    </style>
</head>
<body>

{{-- Header --}}
<header class="site-header">
    <div class="site-logo">
        <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:44px;width:auto;object-fit:contain">
        <div>
            <div class="site-logo-name">ISLAMIC ONLINE MADRASAH</div>
            <div class="site-logo-sub">Through Knowledge, Towards Jannah</div>
        </div>
    </div>
    <div class="header-actions">
        <a href="/" class="btn-outline-sm">মূলপাতা (Home)</a>
    </div>
</header>

{{-- Hero Banner --}}
<div class="hero-banner">
    <h1>অনলাইন সংক্ষিপ্ত ভর্তি আবেদন</h1>
    <p>সহজ ও দ্রুত ভর্তি প্রক্রিয়া। প্রয়োজনীয় তথ্য প্রদান ও অনলাইনে পেমেন্ট সম্পন্ন করে ভর্তি নিশ্চিত করুন।</p>
</div>

<div class="apply-container">
    <div class="form-card">
        <div class="form-card-header">
            <span class="form-card-title"><i class="fa-solid fa-file-pen" style="color:var(--iom-green)"></i> ভর্তির প্রাথমিক আবেদন ফর্ম</span>
            <span style="font-size:12px;color:var(--iom-green);font-weight:600">আইওএম শিক্ষা বিভাগ</span>
        </div>

        <div class="form-card-body">
            @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991b1b">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
            @endif

            @if(isset($errors) && $errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991b1b">
                <strong>অনুগ্রহ করে ত্রুটিগুলো সংশোধন করুন:</strong>
                <ul style="margin-top:6px;margin-left:18px">
                    @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                </ul>
            </div>
            @endif

            <div class="info-callout">
                <strong>📌 তথ্য নির্দেশিকা:</strong> প্রয়োজনীয় মৌলিক তথ্য প্রদান করে অনলাইনে পেমেন্ট সম্পন্ন করুন। পেমেন্ট সম্পন্ন হলেই তাৎক্ষণিকভাবে ভর্তি নিশ্চিত হবে ও লগইন তথ্য প্রদর্শিত হবে।
            </div>

            <form method="POST" action="{{ route('apply.store') }}" id="applyForm">
                @csrf

                {{-- Course Selection --}}
                <div class="form-group">
                    <label>ভর্তি হতে ইচ্ছুক কোর্স / প্রোগ্রাম <span class="req">*</span></label>
                    <select name="course_id" id="course_id" required onchange="onCourseChange(this)">
                        <option value="">-- কোর্স নির্বাচন করুন --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}"
                                    data-fee="{{ (float)($course->admission_fee ?? 0) }}"
                                    {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }} ({{ $course->duration_value }} {{ strtolower($course->duration_unit) }}s)
                                @if(($course->admission_fee ?? 0) > 0)
                                    — ভর্তি ফি: ৳{{ number_format($course->admission_fee, 0) }}
                                @else
                                    — ফ্রি ভর্তি
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Batch Selection --}}
                <div class="form-group">
                    <label>নির্ধারিত ব্যাচ (টার্গেট ব্যাচ)</label>
                    <select name="batch_id" id="batch_id" onchange="onBatchChange(this)">
                        <option value="">-- ব্যাচ নির্বাচন করুন (খোলা থাকলে) --</option>
                        @foreach($activeBatches as $batch)
                            @if($batch->is_admission_open)
                                <option value="{{ $batch->id }}"
                                        data-course-id="{{ $batch->course_id }}"
                                        data-fee="{{ $batch->admission_fee !== null ? (float)$batch->admission_fee : '' }}"
                                        {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                                    {{ $batch->name }} ({{ $batch->batch_code }})
                                    @if($batch->admission_fee !== null && $batch->admission_fee > 0)
                                        — ফি: ৳{{ number_format($batch->admission_fee, 0) }}
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Name & Phone --}}
                <div class="form-row">
                    <div class="form-group">
                        <label>আবেদনকারীর পূর্ণ নাম <span class="req">*</span></label>
                        <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" placeholder="আপনার পূর্ণ নাম লিখুন" required>
                    </div>
                    <div class="form-group">
                        <label>মোবাইল নম্বর <span class="req">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                    </div>
                </div>

                {{-- Email & Gender --}}
                <div class="form-row">
                    <div class="form-group">
                        <label>ইমেইল ঠিকানা <span class="req">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                        <small style="color:var(--muted);font-size:11px">পাসওয়ার্ড ও লগইন তথ্য এই ইমেইলে পাঠানো হবে</small>
                    </div>
                    <div class="form-group">
                        <label>লিঙ্গ / শাখা <span class="req">*</span></label>
                        <select name="gender" required>
                            <option value="">-- শাখা নির্বাচন করুন --</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>ভাই শাখা (পুরুষ)</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>বোন শাখা (মহিলা)</option>
                        </select>
                    </div>
                </div>

                @if(!empty($terms))
                <div class="form-group" style="margin-top:14px">
                    <label>মাদ্রাসার নিয়ম ও ভর্তির শর্তাবলী</label>
                    <div class="terms-box">{{ $terms }}</div>
                </div>
                @endif

                <div class="form-group" style="margin-top:16px">
                    <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-weight:normal;font-size:13px">
                        <input type="checkbox" name="terms_agreed" value="1" required style="width:18px;height:18px;margin-top:2px;accent-color:var(--iom-green)">
                        <span>আমি সাক্ষ্য দিচ্ছি যে উপরোক্ত সকল তথ্য সত্য এবং আমি মাদ্রাসার সকল নিয়ম-কানুন মেনে চলতে সম্মত। <span class="req">*</span></span>
                    </label>
                </div>

                <div style="margin-top:24px">
                    <button type="submit" class="btn-submit" id="btn-submit">
                        <span>পরবর্তী ধাপ: পেমেন্ট ও ভর্তি সম্পন্ন করুন</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="site-footer">
    <p>&copy; {{ date('Y') }} Islamic Online Madrasah (IOM). All Rights Reserved.</p>
</footer>

<script>
function filterBatchesByCourse(courseId) {
    const select = document.getElementById('batch_id');
    const options = select.querySelectorAll('option');
    let hasMatch = false;

    options.forEach(opt => {
        if (!opt.value) { opt.style.display = ''; return; }
        const cId = opt.getAttribute('data-course-id');
        if (!courseId || cId === courseId) {
            opt.style.display = '';
            if (!hasMatch && opt.value) { opt.selected = true; hasMatch = true; }
        } else {
            opt.style.display = 'none';
        }
    });

    if (!hasMatch) select.value = '';
}

function onCourseChange(courseSelect) {
    filterBatchesByCourse(courseSelect.value);
}

document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_id');
    if (courseSelect && courseSelect.value) {
        filterBatchesByCourse(courseSelect.value);
    }
});
</script>
</body>
</html>
