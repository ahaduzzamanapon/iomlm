<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ভর্তি আবেদন ট্র্যাকার — ইসলামিক অনলাইন মাদ্রাসা</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Kalpurush', 'Inter', sans-serif;
        background: #f8fafc;
        color: #1e293b;
        min-height: 100vh;
    }
    .site-header {
        background: #fff;
        border-bottom: 1px solid #d1fae5;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 68px;
    }
    .site-logo { display: flex; align-items: center; gap: 12px; }
    .site-logo-name { font-size: 16px; font-weight: 700; color: #047857; line-height: 1.2; }
    .site-logo-sub { font-size: 11px; color: #64748b; }
    .btn-outline-sm {
        padding: 7px 16px;
        border: 1.5px solid #047857;
        border-radius: 6px;
        color: #047857;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        background: #fff;
        transition: all .15s;
    }
    .btn-outline-sm:hover { background: #047857; color: #fff; }

    .hero-tracker {
        background: linear-gradient(135deg, #022c22 0%, #064e3b 100%);
        color: #fff;
        padding: 32px 20px 48px;
        text-align: center;
    }
    .hero-tracker h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .hero-tracker p { font-size: 13.5px; opacity: 0.9; max-width: 600px; margin: 0 auto; line-height: 1.6; }

    .tracker-container {
        max-width: 680px;
        margin: -24px auto 40px;
        padding: 0 16px;
    }
    .search-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
        padding: 24px;
        margin-bottom: 24px;
    }
    .search-bar-wrap {
        display: flex;
        gap: 10px;
    }
    .search-input {
        flex: 1;
        height: 46px;
        padding: 10px 16px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        font-size: 15px;
        outline: none;
        transition: border-color .15s;
        font-family: inherit;
    }
    .search-input:focus {
        border-color: #047857;
        box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.15);
    }
    .search-btn {
        height: 46px;
        padding: 0 24px;
        background: #047857;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .2s;
        font-family: inherit;
    }
    .search-btn:hover { background: #064e3b; }

    /* Result Card */
    .status-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .status-header-pending {
        background: #fef3c7;
        border-bottom: 1px solid #fde68a;
        color: #92400e;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .status-header-approved {
        background: #dcfce7;
        border-bottom: 1px solid #bbf7d0;
        color: #166534;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .status-header-rejected {
        background: #fee2e2;
        border-bottom: 1px solid #fecaca;
        color: #991b1b;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .status-body {
        padding: 24px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .data-table th {
        width: 180px;
        text-align: left;
        color: #64748b;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 600;
    }
    .data-table td {
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
    }
    </style>
</head>
<body>

<header class="site-header">
    <div class="site-logo">
        <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:44px;width:auto;object-fit:contain">
        <div>
            <div class="site-logo-name">ISLAMIC ONLINE MADRASAH</div>
            <div class="site-logo-sub">Through Knowledge, Towards Jannah</div>
        </div>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('apply.show') }}" class="btn-outline-sm"><i class="fa-solid fa-file-pen"></i> ভর্তি আবেদন</a>
        <a href="/" class="btn-outline-sm">মূলপাতা (Home)</a>
    </div>
</header>

<div class="hero-tracker">
    <h1><i class="fa-solid fa-magnifying-glass-location"></i> ভর্তি আবেদন ট্র্যাকার (Applicant Tracker)</h1>
    <p>আপনার আবেদন নম্বর (Application No) অথবা মোবাইল নম্বর প্রদান করে ভর্তি আবেদনের বর্তমান অবস্থা ও ফলাফল যাচাই করুন।</p>
</div>

<div class="tracker-container">
    {{-- Search Form --}}
    <div class="search-card">
        <form method="POST" action="{{ route('admission.status.lookup') }}">
            @csrf
            <label style="display:block;font-size:14px;font-weight:700;color:#334155;margin-bottom:8px;">
                আবেদন নম্বর অথবা মোবাইল নম্বর লিখুন:
            </label>
            <div class="search-bar-wrap">
                <input type="text" name="search" value="{{ $searchQuery ?? '' }}" required placeholder="উদাঃ APP-2026-0001 অথবা 017xxxxxxxx" class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> সন্ধান করুন
                </button>
            </div>
            @error('search')
                <div style="color:#dc2626;font-size:12px;margin-top:6px;">{{ $message }}</div>
            @enderror
        </form>
    </div>

    {{-- Search Result Display --}}
    @if(!empty($searchQuery))
        @if($admission)
            <div class="status-card">
                @if($admission->status === 'APPROVED')
                    <div class="status-header-approved">
                        <span style="font-weight:700;font-size:16px;">
                            <i class="fa-solid fa-circle-check"></i> অভিনন্দন! ভর্তি অনুমোদিত হয়েছে (Admission Approved)
                        </span>
                        <span style="font-size:12px;font-weight:700;background:#15803d;color:#fff;padding:3px 10px;border-radius:20px;">
                            অনুমোদিত
                        </span>
                    </div>
                @elseif($admission->status === 'REJECTED')
                    <div class="status-header-rejected">
                        <span style="font-weight:700;font-size:16px;">
                            <i class="fa-solid fa-circle-xmark"></i> আবেদন প্রত্যাখ্যাত হয়েছে (Application Rejected)
                        </span>
                        <span style="font-size:12px;font-weight:700;background:#b91c1c;color:#fff;padding:3px 10px;border-radius:20px;">
                            প্রত্যাখ্যাত
                        </span>
                    </div>
                @else
                    <div class="status-header-pending">
                        <span style="font-weight:700;font-size:16px;">
                            <i class="fa-solid fa-clock"></i> আবেদন প্রক্রিয়াধীন (Under Review)
                        </span>
                        <span style="font-size:12px;font-weight:700;background:#b45309;color:#fff;padding:3px 10px;border-radius:20px;">
                            পেন্ডিং
                        </span>
                    </div>
                @endif

                <div class="status-body">
                    <table class="data-table">
                        <tr>
                            <th>আবেদন নম্বর:</th>
                            <td><strong style="color:#0284c7;">{{ $admission->application_no }}</strong></td>
                        </tr>
                        <tr>
                            <th>আবেদনকারীর নাম:</th>
                            <td><strong>{{ $admission->student?->name ?? $admission->applicant_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>ভর্তিকৃত কোর্স:</th>
                            <td><strong>{{ $admission->interestedCourse?->name ?? '—' }}</strong></td>
                        </tr>
                        @if($admission->batch)
                        <tr>
                            <th>নির্ধারিত ব্যাচ:</th>
                            <td>{{ $admission->batch->name }}</td>
                        </tr>
                        @endif
                        @if($admission->circular)
                        <tr>
                            <th>ভর্তি সেশন:</th>
                            <td>{{ $admission->circular->short_name ?: $admission->circular->name }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>আবেদনের তারিখ:</th>
                            <td>{{ $admission->created_at ? $admission->created_at->format('d M, Y (h:i A)') : '—' }}</td>
                        </tr>

                        {{-- Official Student Code when Approved --}}
                        @if($admission->status === 'APPROVED' && $admission->student && $admission->student->student_code)
                        <tr style="background:#f0fdf4;">
                            <th style="color:#166534;font-size:15px;">অফিসিয়াল স্টুডেন্ট আইডি:</th>
                            <td>
                                <span style="font-size:18px;font-weight:800;color:#047857;letter-spacing:1px;">
                                    {{ $admission->student->student_code }}
                                </span>
                            </td>
                        </tr>
                        @endif

                        {{-- Payment Status --}}
                        <tr>
                            <th>ফি পরিশোধের অবস্থা:</th>
                            <td>
                                @if($isPaid)
                                    <span style="color:#15803d;font-weight:700;"><i class="fa-solid fa-check-circle"></i> পরিশোধিত (Paid)</span>
                                @else
                                    <span style="color:#b91c1c;font-weight:700;"><i class="fa-solid fa-circle-exclamation"></i> অপরিশোধিত (Unpaid)</span>
                                    <a href="{{ route('apply.payment', $admission->application_no) }}" style="display:inline-block;margin-left:12px;padding:4px 12px;background:#047857;color:#fff;text-decoration:none;border-radius:4px;font-size:12px;font-weight:700;">
                                        এখনই ফি পরিশোধ করুন ↗
                                    </a>
                                @endif
                            </td>
                        </tr>
                    </table>

                    {{-- Status Specific Notices --}}
                    @if($admission->status === 'APPROVED')
                        <div style="background:#ecfdf5;border-left:4px solid #047857;padding:14px 16px;border-radius:0 8px 8px 0;font-size:13.5px;color:#065f46;margin-bottom:16px;line-height:1.6;">
                            🎉 আলহামদুলিল্লাহ! আপনার ভর্তি সফলভাবে অনুমোদিত হয়েছে। আপনার মোবাইল ও ইমেইলে রোল ও পাসওয়ার্ড পাঠানো হয়েছে। স্টুডেন্ট পোর্টালে লগইন করে আপনার ক্লাস ও শিক্ষা কার্যক্রম শুরু করুন।
                        </div>
                        <div style="text-align:center;margin-top:16px;">
                            <a href="{{ url('/login') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#047857;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:15px;">
                                <i class="fa-solid fa-right-to-bracket"></i> স্টুডেন্ট পোর্টালে লগইন করুন
                            </a>
                        </div>
                    @elseif($admission->status === 'REJECTED')
                        <div style="background:#fef2f2;border-left:4px solid #dc2626;padding:14px 16px;border-radius:0 8px 8px 0;font-size:13.5px;color:#991b1b;margin-bottom:16px;line-height:1.6;">
                            <strong>বাতিল করার কারণ:</strong> {{ $admission->rejection_reason ?: 'ভর্তির ন্যূনতম শর্তাবলী বা প্রয়োজনীয় ডকুমেন্টের অসম্পূর্ণতা।' }}
                            <div style="margin-top:6px;font-size:12.5px;color:#7f1d1d;">
                                অনুগ্রহ করে তথ্য সংশোধন করে পুনরায় আবেদন করুন অথবা মাদ্রাসার হেল্পলাইনে যোগাযোগ করুন।
                            </div>
                        </div>
                    @else
                        <div style="background:#fffbeb;border-left:4px solid #d97706;padding:14px 16px;border-radius:0 8px 8px 0;font-size:13.5px;color:#92400e;line-height:1.6;">
                            ⏳ আপনার ভর্তি আবেদনটি গ্রহণ করা হয়েছে এবং অ্যাডমিন কর্তৃক ভেরিফিকেশনের জন্য অপেক্ষমান রয়েছে। ভেরিফিকেশন ও অনুমোদন সম্পন্ন হওয়ার সাথে সাথে আপনাকে এসএমএস ও ইমেইলের মাধ্যমে অবহিত করা হবে।
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:36px;text-align:center;color:#64748b;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:40px;color:#f59e0b;margin-bottom:12px;"></i>
                <h3 style="font-size:17px;font-weight:700;color:#334155;margin-bottom:6px;">কোনো আবেদন পাওয়া যায়নি</h3>
                <p style="font-size:13.5px;color:#64748b;">
                    "{{ $searchQuery }}" দিয়ে কোনো ভর্তি আবেদন খুঁজে পাওয়া যায়নি। অনুগ্রহ করে সঠিক আবেদন নম্বর বা ফোন নম্বর প্রদান করুন।
                </p>
            </div>
        @endif
    @endif
</div>

<footer style="text-align:center;padding:24px;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;background:#fff;">
    &copy; {{ date('Y') }} Islamic Online Madrasah (IOM). All Rights Reserved.
</footer>

</body>
</html>
