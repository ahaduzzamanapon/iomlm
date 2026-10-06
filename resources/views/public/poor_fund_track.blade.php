<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পুউর ফান্ড / স্কলারশিপ আবেদন ট্র্যাকার — ইসলামিক অনলাইন মাদ্রাসা</title>
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
    .site-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .site-logo-name { font-size: 16px; font-weight: 700; color: #047857; line-height: 1.2; }
    .site-logo-sub { font-size: 11px; color: #64748b; }
    .header-links { display: flex; align-items: center; gap: 10px; }
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
        padding: 36px 20px 52px;
        text-align: center;
    }
    .hero-tracker h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .hero-tracker p { font-size: 13.5px; opacity: 0.92; max-width: 620px; margin: 0 auto; line-height: 1.6; }

    .tracker-container {
        max-width: 700px;
        margin: -28px auto 40px;
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
        height: 48px;
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
        height: 48px;
        padding: 0 24px;
        background: #047857;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .2s;
        font-family: inherit;
    }
    .search-btn:hover { background: #065f46; }

    .status-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .status-header-approved {
        background: #ecfdf5;
        border-bottom: 1.5px solid #a7f3d0;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #065f46;
    }
    .status-header-pending {
        background: #fffbeb;
        border-bottom: 1.5px solid #fde68a;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #92400e;
    }
    .status-header-rejected {
        background: #fef2f2;
        border-bottom: 1.5px solid #fecaca;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #991b1b;
    }
    .status-body {
        padding: 24px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .data-table tr { border-bottom: 1px solid #f1f5f9; }
    .data-table tr:last-child { border-bottom: none; }
    .data-table th {
        text-align: left;
        padding: 11px 8px;
        color: #64748b;
        font-weight: 600;
        width: 40%;
    }
    .data-table td {
        padding: 11px 8px;
        color: #0f172a;
    }

    .selector-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 16px 18px;
        margin-bottom: 18px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .app-chip {
        display: block;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        text-decoration: none;
        color: inherit;
        background: #fff;
        transition: all .15s;
    }
    .app-chip:hover {
        border-color: #047857;
        background: #f0fdf4;
    }
    .app-chip.active {
        border-color: #047857;
        background: #ecfdf5;
        box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.15);
    }
    </style>
</head>
<body>

<header class="site-header">
    <a href="{{ url('/') }}" class="site-logo">
        <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:44px;width:auto;" onerror="this.style.display='none'">
        <div>
            <div class="site-logo-name">ইসলামিক অনলাইন মাদ্রাসা</div>
            <div class="site-logo-sub">ইলম অর্জনের এক নির্ভরযোগ্য প্রতিষ্ঠান</div>
        </div>
    </a>
    <div class="header-links">
        <a href="{{ route('poor_fund.show') }}" class="btn-outline-sm">
            <i class="fa-solid fa-hand-holding-dollar"></i> পুওর ফান্ড আবেদন
        </a>
        <a href="{{ route('admission.status') }}" class="btn-outline-sm">
            <i class="fa-solid fa-magnifying-glass"></i> ভর্তি ট্র্যাকার
        </a>
    </div>
</header>

<div class="hero-tracker">
    <h1>
        <i class="fa-solid fa-hand-holding-dollar" style="color:#fbbf24;margin-right:8px;"></i>
        পুওর ফান্ড / স্কলারশিপ আবেদন ট্র্যাকার
    </h1>
    <p>
        আপনার পুওর ফান্ড আবেদন নম্বর (উদা: PF-2026-0001) বা আবেদন করার সময় ব্যবহৃত মোবাইল নম্বর / এনআইডি দিয়ে বর্তমান অবস্থা যাচাই করুন।
    </p>
</div>

<div class="tracker-container">
    {{-- Search Form --}}
    <div class="search-card">
        <form method="POST" action="{{ route('poor_fund.status.lookup') }}">
            @csrf
            <div class="search-bar-wrap">
                <input type="text" name="search" class="search-input" 
                       value="{{ $searchQuery }}" 
                       placeholder="আবেদন আইডি (উদা: PF-2026-0001) বা ফোন নম্বর..." 
                       required autofocus>
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i> স্ট্যাটাস দেখুন
                </button>
            </div>
        </form>
    </div>

    @if(!empty($searchQuery))
        {{-- Multiple Applications Found Selector --}}
        @if($applications->count() > 1)
            <div class="selector-card">
                <div style="font-size:13.5px;font-weight:700;color:#0f172a;margin-bottom:10px;">
                    <i class="fa-solid fa-layer-group" style="color:#047857;"></i> আপনার একাধিক আবেদন পাওয়া গেছে ({{ $applications->count() }}টি) — বিস্তারিত দেখতে নির্বাচন করুন:
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($applications as $appItem)
                        @php $isActive = ($application && $application->id === $appItem->id); @endphp
                        <a href="{{ route('poor_fund.status', ['app_no' => $searchQuery, 'selected_id' => $appItem->id]) }}" 
                           class="app-chip {{ $isActive ? 'active' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                                <div>
                                    <strong style="color:#047857;font-size:14.5px;">{{ $appItem->application_no }}</strong>
                                    <span style="font-size:13px;color:#334155;margin-left:8px;">— {{ $appItem->course?->name ?? 'কোর্স' }}</span>
                                </div>
                                <div>
                                    @if($appItem->status === 'APPROVED')
                                        <span style="font-size:12px;font-weight:700;background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:12px;">অনুমোদিত</span>
                                    @elseif($appItem->status === 'REJECTED')
                                        <span style="font-size:12px;font-weight:700;background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:12px;">প্রত্যাখ্যাত</span>
                                    @else
                                        <span style="font-size:12px;font-weight:700;background:#fef3c7;color:#b45309;padding:2px 8px;border-radius:12px;">পেন্ডিং</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Single Application Details Display --}}
        @if($application)
            <div class="status-card">
                @if($application->status === 'APPROVED')
                    <div class="status-header-approved">
                        <span style="font-weight:700;font-size:16px;">
                            <i class="fa-solid fa-circle-check"></i> অভিনন্দন! আপনার পুওর ফান্ড আবেদন অনুমোদিত হয়েছে
                        </span>
                        <span style="font-size:12px;font-weight:700;background:#15803d;color:#fff;padding:3px 12px;border-radius:20px;">
                            অনুমোদিত (Approved)
                        </span>
                    </div>
                @elseif($application->status === 'REJECTED')
                    <div class="status-header-rejected">
                        <span style="font-weight:700;font-size:16px;">
                            <i class="fa-solid fa-circle-xmark"></i> আবেদনটি অনুমোদন করা সম্ভব হয়নি
                        </span>
                        <span style="font-size:12px;font-weight:700;background:#b91c1c;color:#fff;padding:3px 12px;border-radius:20px;">
                            প্রত্যাখ্যাত (Rejected)
                        </span>
                    </div>
                @else
                    <div class="status-header-pending">
                        <span style="font-weight:700;font-size:16px;">
                            <i class="fa-solid fa-clock"></i> আবেদনটি বর্তমানে বিবেচনাধীন রয়েছে (Under Review)
                        </span>
                        <span style="font-size:12px;font-weight:700;background:#b45309;color:#fff;padding:3px 12px;border-radius:20px;">
                            পেন্ডিং (Pending)
                        </span>
                    </div>
                @endif

                <div class="status-body">
                    <table class="data-table">
                        <tr>
                            <th>পুওর ফান্ড আবেদন নম্বর:</th>
                            <td>
                                <strong style="color:#047857;font-size:16px;letter-spacing:0.5px;">
                                    {{ $application->application_no }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th>আবেদনকারীর নাম:</th>
                            <td><strong>{{ $application->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>আবেদনের কোর্স:</th>
                            <td><strong>{{ $application->course?->name ?? '—' }}</strong></td>
                        </tr>
                        <tr>
                            <th>আবেদনের ধরন:</th>
                            <td>
                                @if($application->apply_reason_type === 'Admission Fee' || $application->apply_for === 'ADMISSION_FEE')
                                    ভর্তি ফি (Admission Fee)
                                @elseif($application->apply_reason_type === 'Monthly Fee' || $application->apply_for === 'TUITION_FEE')
                                    মাসিক টিউশন ফি (Monthly Fee)
                                @else
                                    ভর্তি ফি ও মাসিক ফি উভয়ই (Both)
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>আবেদনের তারিখ:</th>
                            <td>{{ $application->created_at ? $application->created_at->format('d M, Y (h:i A)') : '—' }}</td>
                        </tr>

                        @if($application->status === 'APPROVED')
                            @if($application->approved_admission_fee !== null)
                            <tr style="background:#f0fdf4;">
                                <th style="color:#166534;font-weight:700;">অনুমোদিত ভর্তি ফি:</th>
                                <td>
                                    <strong style="color:#047857;font-size:16px;">
                                        ৳{{ number_format($application->approved_admission_fee, 0) }}
                                    </strong>
                                </td>
                            </tr>
                            @endif

                            @if($application->approved_package_id && $application->approvedPackage)
                            <tr style="background:#f0fdf4;">
                                <th style="color:#166534;font-weight:700;">অনুমোদিত মাসিক প্যাকেজ:</th>
                                <td>
                                    <strong style="color:#047857;">{{ $application->approvedPackage->name }}</strong>
                                    (৳{{ number_format($application->approvedPackage->package_fee ?? $application->approvedPackage->monthly_fee, 0) }})
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <th>ব্যবহারের অবস্থা:</th>
                                <td>
                                    @if($application->is_used)
                                        <span style="color:#0284c7;font-weight:700;">
                                            <i class="fa-solid fa-check-circle"></i> ইতিমধ্যে ভর্তি ফর্মে ব্যবহৃত হয়েছে
                                        </span>
                                    @else
                                        <span style="color:#15803d;font-weight:700;">
                                            <i class="fa-solid fa-clock"></i> এখনো ভর্তি ফর্মে ব্যবহৃত হয়নি (Active)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>

                    {{-- Status Specific Guidance Cards --}}
                    @if($application->status === 'APPROVED')
                        @if(!$application->is_used)
                            <div style="background:#ecfdf5;border:1.5px solid #a7f3d0;padding:18px 20px;border-radius:10px;margin-bottom:20px;line-height:1.7;">
                                <div style="font-size:15px;font-weight:700;color:#065f46;margin-bottom:6px;">
                                    🎉 আপনার স্কলারশিপ / পুওর ফান্ড কুপন কোড: 
                                    <span style="background:#047857;color:#fff;padding:3px 10px;border-radius:6px;font-family:monospace;letter-spacing:1px;">
                                        {{ $application->application_no }}
                                    </span>
                                </div>
                                <p style="font-size:13.5px;color:#065f46;margin:0;">
                                    আলহামদুলিল্লাহ! আপনার আবেদনটি মঞ্জুর করা হয়েছে। ভর্তি ফরম পূরণের সময় <strong>"কুপন / পুওর ফান্ড কোড"</strong> ফিল্ডে এই কোডটি লিখলে আপনার জন্য অনুমোদিত বিশেষ ফি স্বয়ংক্রিয়ভাবে কার্যকর হবে।
                                </p>
                            </div>

                            <div style="text-align:center;margin-top:10px;">
                                <a href="{{ route('apply.show', ['waiver_code' => $application->application_no]) }}" 
                                   style="display:inline-flex;align-items:center;gap:10px;padding:13px 32px;background:#047857;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:15px;box-shadow:0 4px 12px rgba(4,120,87,0.3);transition:all .15s;" onmouseover="this.style.background='#065f46'" onmouseout="this.style.background='#047857'">
                                    <i class="fa-solid fa-user-plus"></i> এই কোড দিয়ে ভর্তি আবেদন করুন ↗
                                </a>
                            </div>
                        @else
                            <div style="background:#eff6ff;border:1px solid #bfdbfe;padding:16px 18px;border-radius:8px;font-size:13.5px;color:#1e40af;line-height:1.6;">
                                <i class="fa-solid fa-info-circle"></i> এই পুওর ফান্ড কোডটি দিয়ে ইতিমধ্যে ভর্তি আবেদন সম্পন্ন করা হয়েছে। আপনার ভর্তির স্ট্যাটাস জানতে <a href="{{ route('admission.status') }}" style="color:#1d4ed8;font-weight:700;text-decoration:underline;">ভর্তি ট্র্যাকার</a> ব্যবহার করুন।
                            </div>
                        @endif
                    @elseif($application->status === 'REJECTED')
                        <div style="background:#fef2f2;border-left:4px solid #dc2626;padding:16px 18px;border-radius:0 8px 8px 0;font-size:13.5px;color:#991b1b;margin-bottom:16px;line-height:1.6;">
                            <strong>মাদ্রাসা কর্তৃপক্ষের সিদ্ধান্ত:</strong>
                            <div style="margin-top:4px;">
                                {{ $application->reviewer_notes ?: 'পুওর ফান্ডের সীমিত কোটা এবং সামগ্রিক নীতিমালার কারণে এই মুহূর্তে আপনার আবেদনটি মঞ্জুর করা সম্ভব হয়নি।' }}
                            </div>
                            <div style="margin-top:8px;font-size:12.5px;color:#7f1d1d;">
                                বিস্তারিত তথ্যের জন্য মাদ্রাসার হেল্পলাইনে অথবা অফিস চলাকালীন যোগাযোগ করার অনুরোধ করা হলো।
                            </div>
                        </div>
                    @else
                        <div style="background:#fffbeb;border-left:4px solid #d97706;padding:16px 18px;border-radius:0 8px 8px 0;font-size:13.5px;color:#92400e;line-height:1.7;">
                            ⏳ <strong>আপনার আবেদনটি জমা হয়েছে এবং যাচাই-বাছাই চলছে।</strong><br>
                            পুওর ফান্ড কমিটি আবেদনসমূহ নিয়মিত পর্যালোচনা করে থাকে। আপনার আবেদন অনুমোদিত হওয়ার সাথে সাথে আপনার মোবাইল নম্বরে ও ইমেইলে নোটিফিকেশনের মাধ্যমে অবহিত করা হবে।
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:36px;text-align:center;color:#64748b;">
                <i class="fa-solid fa-circle-question" style="font-size:42px;color:#f59e0b;margin-bottom:12px;display:block;"></i>
                <h3 style="font-size:17px;font-weight:700;color:#334155;margin-bottom:6px;">কোনো পুওর ফান্ড আবেদন পাওয়া যায়নি</h3>
                <p style="font-size:13.5px;color:#64748b;max-width:480px;margin:0 auto;">
                    "{{ $searchQuery }}" দিয়ে কোনো পুওর ফান্ড আবেদন পাওয়া যায়নি। অনুগ্রহ করে আপনার সঠিক আবেদন নম্বর (যেমন: PF-2026-0001) অথবা আবেদনের সময় প্রদত্ত ফোন নম্বর দিয়ে পুনরায় চেষ্টা করুন।
                </p>
                <div style="margin-top:18px;">
                    <a href="{{ route('poor_fund.show') }}" style="color:#047857;font-weight:700;text-decoration:underline;font-size:13.5px;">
                        নতুন পুওর ফান্ড আবেদন করতে চান? এখানে ক্লিক করুন
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>

<footer style="text-align:center;padding:24px;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;background:#fff;">
    &copy; {{ date('Y') }} Islamic Online Madrasah (IOM). All Rights Reserved.
</footer>

</body>
</html>
