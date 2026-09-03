<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Islamic Online Madrasah (IOM) — উচ্চতর অনলাইন ইসলামিক শিক্ষা ও সহায়তা পোর্টাল</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary: #047857;
            --primary-dark: #064e3b;
            --secondary: #10b981;
            --warning: #fbbf24;
            --dark-bg: #064e3b;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Kalpurush', 'Poppins', Helvetica, sans-serif;
            background: #f8fafc;
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header Topbar ── */
        .topbar {
            background: var(--dark-bg);
            color: #ffffff;
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(2,44,34,0.25);
            border-bottom: 2px solid #047857;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #047857, #064e3b);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(4, 120, 87, 0.4);
        }

        .brand-text h1 {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            color: #ffffff;
        }

        .brand-text span {
            font-size: 12px;
            color: #fbbf24;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-top {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-top-outline {
            border: 1px solid rgba(255,255,255,0.25);
            color: #e2e8f0;
        }

        .btn-top-outline:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-color: #fff;
        }

        .btn-top-primary {
            background: #047857;
            color: #fff;
            box-shadow: 0 4px 14px rgba(4, 120, 87, 0.4);
        }

        .btn-top-primary:hover {
            background: #064e3b;
            transform: translateY(-1px);
        }

        /* ── Hero Section ── */
        .hero {
            background: linear-gradient(180deg, #022c22 0%, #064e3b 100%);
            color: #ffffff;
            padding: 60px 20px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 1000px;
            height: 1000px;
            background: radial-gradient(circle, rgba(4, 120, 87, 0.25) 0%, rgba(2, 44, 34, 0) 70%);
            pointer-events: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(4, 120, 87, 0.35);
            border: 1px solid rgba(52, 211, 153, 0.4);
            color: #a7f3d0;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #ffffff;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 16px;
            color: #94a3b8;
            max-width: 680px;
            margin: 0 auto 32px;
        }

        /* ── Action Grid Cards ── */
        .main-container {
            max-width: 1100px;
            margin: -50px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
            flex: 1;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }

        .action-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(2, 132, 199, 0.12);
            border-color: #cbd5e1;
        }

        .card-top {
            margin-bottom: 20px;
        }

        .card-icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 18px;
        }

        .card-icon-green { background: #dcfce7; color: #15803d; }
        .card-icon-blue { background: #e0f2fe; color: #0369a1; }
        .card-icon-amber { background: #fef3c7; color: #b45309; }
        .card-icon-purple { background: #f3e8ff; color: #6b21a8; }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .card-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
        }

        .card-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 46px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-green { background: #047857; color: #ffffff; }
        .btn-green:hover { background: #064e3b; }

        .btn-blue { background: #047857; color: #ffffff; }
        .btn-blue:hover { background: #064e3b; }

        .btn-amber { background: #d97706; color: #ffffff; }
        .btn-amber:hover { background: #b45309; }

        .btn-purple { background: #065f46; color: #ffffff; }
        .btn-purple:hover { background: #047857; }

        /* ── Info Features Banner ── */
        .features-banner {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #d1fae5;
            padding: 32px;
            margin-top: 36px;
            box-shadow: 0 4px 15px rgba(6,78,59,0.04);
        }

        .banner-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #064e3b;
            margin-bottom: 24px;
        }

        .feature-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            text-align: center;
        }

        .feature-box {
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .feature-box-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .feature-box h4 {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .feature-box p {
            font-size: 13px;
            color: #64748b;
        }

        /* ── Footer ── */
        .footer {
            background: #022c22;
            color: #a7f3d0;
            text-align: center;
            padding: 24px;
            font-size: 13px;
            margin-top: auto;
            border-top: 1px solid #064e3b;
        }

        .footer a {
            color: #fbbf24;
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .hero-title { font-size: 26px; }
            .topbar { padding: 12px 16px; }
            .card-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    {{-- Top Navigation Bar --}}
    <header class="topbar">
        <a href="{{ url('/') }}" class="brand-logo">
            <div class="brand-text">
                <h1>IOM Learning Plus</h1>
                <span>Islamic Online Madrasah</span>
            </div>
        </a>

        <div class="topbar-actions">
            @if(auth()->check())
                @php
                    $role = auth()->user()->role ?? 'admin';
                    $dashboardRoute = match($role) {
                        'teacher'       => route('teacher.dashboard'),
                        'student'       => route('student.dashboard'),
                        'support_agent' => route('support.dashboard'),
                        'support'       => route('support.dashboard'),
                        default         => route('admin.dashboard'),
                    };
                @endphp
                <a href="{{ $dashboardRoute }}" class="btn-top btn-top-primary">
                    My Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-top btn-top-outline">
                    Portal Login
                </a>
                <a href="{{ route('apply.show') }}" class="btn-top btn-top-primary">
                    Online Admission
                </a>
            @endif
        </div>
    </header>

    {{-- Hero Section --}}
    <section class="hero">
        <div class="hero-badge">
            আসসালামু আলাইকুম — আইওএম সেবা পোর্টালে আপনাকে স্বাগতম
        </div>
        <h2 class="hero-title">ইসলামিক অনলাইন মাদ্রাসা (IOM) সহায়তা কেন্দ্র</h2>
        <p class="hero-subtitle">
            দরিদ্র তহবিল আবেদন (Poor Fund Waiver Application), অনলাইন লাইভ সাপোর্ট হেল্পডেস্ক এবং নতুন ভর্তি সেবাসমূহ এক ছাদের নিচে।
        </p>
    </section>

    {{-- Main Action Cards Grid --}}
    <main class="main-container">
        <div class="card-grid">
            
            {{-- Card 1: Poor Fund / Waiver Application --}}
            <div class="action-card">
                <div class="card-top">
                    <h3 class="card-title">দরিদ্র তহবিল ও ফি মওকুফ আবেদন</h3>
                    <p class="card-desc">
                        দরিদ্র, অসচ্ছল ও মেধাবী শিক্ষার্থীদের জন্য কোর্স ফি বিশেষ মওকুফ বা আল-ইহসান দরিদ্র তহবিলের সহায়তার আবেদন করুন।
                    </p>
                </div>
                <a href="{{ route('poor_fund.show') }}" class="card-btn btn-green">
                    আবেদন করুন (Apply Poor Fund) ↗
                </a>
            </div>

            {{-- Card 2: Online Support & Live Chat --}}
            <div class="action-card">
                <div class="card-top">
                    <h3 class="card-title">অনলাইন সাপোর্ট ও লাইভ চ্যাট</h3>
                    <p class="card-desc">
                        ভর্তি, ক্লাস, কোর্স কনটেন্ট বা কারিগরি যেকোনো প্রয়োজনে সরাসরি আমাদের সাপোর্ট প্রতিনিধির সাথে লাইভ চ্যাট করুন।
                    </p>
                </div>
                <a href="{{ route('online-support.index') }}" class="card-btn btn-blue">
                    অনলাইন সাপোর্ট নিন (Live Chat) ↗
                </a>
            </div>

            {{-- Card 3: Online Admission Form --}}
            <div class="action-card">
                <div class="card-top">
                    <h3 class="card-title">নতুন ভর্তি আবেদন</h3>
                    <p class="card-desc">
                        আইওএম-এর বিভিন্ন ডিপ্লোমা, ডিগ্রি ও সার্টিফিকেট কোর্সে অনলাইন রেজিস্ট্রেশন ও ভর্তি ফরম পূরণ করুন।
                    </p>
                </div>
                <a href="{{ route('apply.show') }}" class="card-btn btn-amber">
                    অনলাইন ভর্তি ফরম (Apply Admission) ↗
                </a>
            </div>

            {{-- Card 4: Portal Login --}}
            <div class="action-card">
                <div class="card-top">
                    <h3 class="card-title">স্টুডেন্ট ও শিক্ষক পোর্টাল</h3>
                    <p class="card-desc">
                        শিক্ষার্থী, শিক্ষক, সাপোর্ট এজেন্ট ও এডমিন প্যানেলে প্রবেশের জন্য আপনার অ্যাকাউন্ট পাসওয়ার্ড দিয়ে লগইন করুন।
                    </p>
                </div>
                <a href="{{ route('login') }}" class="card-btn btn-purple">
                    পোর্টাল লগইন (Portal Login) ↗
                </a>
            </div>

        </div>

        {{-- Features & Guidance Banner --}}
        <div class="features-banner">
            <h3 class="banner-title">আইওএম অনলাইন পোর্টালের সুবিধাসমূহ</h3>
            <div class="feature-items">
                <div class="feature-box">
                    <h4>দ্রুত রেসপন্স টাইম</h4>
                    <p>সাপোর্ট টিমের সরাসরি লাইভ চ্যাট সহায়তা</p>
                </div>
                <div class="feature-box">
                    <h4>আল-ইহসান দরিদ্র তহবিল</h4>
                    <p>অসচ্ছলদের জন্য শিক্ষা ফি রিবেট সুবিধা</p>
                </div>
                <div class="feature-box">
                    <h4>টিকিট ট্র্যাকিং সিস্টেম</h4>
                    <p>ফোন বা ইমেইল দিয়ে সহজে আবেদনের অবস্থা জানুন</p>
                </div>
                <div class="feature-box">
                    <h4>ইউজার ফ্রেন্ডলি ইন্টারফেস</h4>
                    <p>যেকোনো ডিভাইস থেকে সহজে ব্যবহারযোগ্য</p>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="footer">
        <p>&copy; {{ date('Y') }} Islamic Online Madrasah (IOM)</p>
        <p style="margin-top:4px;font-size:12px;color:#64748b">
            <a href="{{ route('online-support.index') }}">অনলাইন সাপোর্ট</a> &middot; 
            <a href="{{ route('poor_fund.show') }}">দরিদ্র তহবিল</a> &middot; 
            <a href="{{ route('apply.show') }}">নতুন ভর্তি</a> &middot; 
            <a href="{{ route('login') }}">লগইন</a>
        </p>
    </footer>

</body>
</html>
