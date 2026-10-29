<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>আজকের ক্লাসের রিমাইন্ডার</title>
    <style>
        @import url('https://fonts.maateen.me/kalpurush/font.css');
        body {
            font-family: 'Kalpurush', 'SolaimanLipi', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px 12px;
            color: #1e293b;
        }
        .container {
            max-width: 620px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #022c22 0%, #064e3b 50%, #047857 100%);
            padding: 26px 28px;
            text-align: center;
            color: #ffffff;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13.5px;
            color: #a7f3d0;
        }
        .content {
            padding: 28px 24px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #064e3b;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 14px;
            line-height: 1.65;
            color: #334155;
            margin-bottom: 20px;
        }
        .class-card {
            background: #f8fafc;
            border-left: 4px solid #047857;
            border-radius: 8px;
            padding: 16px 18px;
            margin-bottom: 14px;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .class-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .badge-extra {
            background: #fef3c7;
            color: #92400e;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #fde68a;
        }
        .class-meta {
            font-size: 13px;
            color: #475569;
            margin-top: 6px;
            line-height: 1.5;
        }
        .class-meta strong {
            color: #1e293b;
        }
        .instructions-box {
            background: #ecfdf5;
            border: 1.5px solid #a7f3d0;
            border-radius: 10px;
            padding: 18px 20px;
            margin: 24px 0;
        }
        .instructions-title {
            margin: 0 0 10px;
            font-size: 15px;
            font-weight: 700;
            color: #064e3b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .instructions-list {
            margin: 0;
            padding-left: 20px;
            font-size: 13.5px;
            line-height: 1.7;
            color: #1e3a2e;
        }
        .cta-container {
            text-align: center;
            margin: 28px 0 10px;
        }
        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #047857 0%, #064e3b 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 34px;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(4, 120, 87, 0.35);
        }
        .footer {
            background: #f8fafc;
            padding: 18px 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #f1f5f9;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h2>{{ $instituteName ?? 'ইসলামিক অনলাইন মাদ্রাসা' }}</h2>
            <p>অ্যাকাডেমিক ক্লাস রিমাইন্ডার ও লাইভ ক্লাস নোটিফিকেশন</p>
        </div>

        {{-- Content --}}
        <div class="content">
            <div class="greeting">
                আসসালামু আলাইকুম ওয়া রাহমাতুল্লাহ, {{ $studentName ?? 'সম্মানিত শিক্ষার্থী' }}!
            </div>
            <div class="intro-text">
                আজ <strong>{{ $dateBangla }}</strong> আপনার নিম্নলিখিত <strong>{{ count($classes) }}টি</strong> লাইভ ক্লাস অনুষ্ঠিত হবে ইনশাআল্লাহ। নির্ধারিত সময়ে ক্লাসে উপস্থিত থাকার জন্য অনুরোধ করা যাচ্ছে।
            </div>

            {{-- Classes List --}}
            <div style="margin-bottom: 20px;">
                @foreach($classes as $c)
                    <div class="class-card">
                        <div class="class-title">
                            <span>📖 {{ $c['subject_name'] }}</span>
                            @if(!empty($c['is_extra']))
                                <span class="badge-extra">এক্সট্রা ক্লাস</span>
                            @endif
                        </div>
                        @if(!empty($c['class_title']))
                            <div style="font-size: 13px; color: #be123c; font-weight: 600; margin-top: 3px;">
                                বিষয়বস্তু/টপিক: {{ $c['class_title'] }}
                            </div>
                        @endif
                        <div class="class-meta">
                            <div><strong>🕒 ক্লাসের সময়:</strong> <span style="color:#047857; font-weight:700;">{{ $c['time_range'] }}</span> @if(!empty($c['slot_name'])) ({{ $c['slot_name'] }}) @endif</div>
                            <div><strong>👥 ব্যাচ ও কোর্স:</strong> {{ $c['batch_name'] }} @if(!empty($c['course_name'])) · {{ $c['course_name'] }} @endif</div>
                            <div><strong>👨‍🏫 সম্মানিত উস্তাদ/শিক্ষক:</strong> {{ $c['teacher_name'] ?? 'অনলাইন শিক্ষক' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Instructions on How to Join --}}
            <div class="instructions-box">
                <div class="instructions-title">
                    📌 লাইভ ক্লাসে জয়েন করার নিয়ম ও পদ্ধতি:
                </div>
                <ol class="instructions-list">
                    <li>প্রথমে আপনার স্টুডেন্ট পোর্টালের অ্যাকাউন্টে লগইন করুন।</li>
                    <li>ড্যাশবোর্ডের মেনু থেকে <strong>"Today's Classes" (আজকের ক্লাস)</strong> অপশনে প্রবেশ করুন অথবা নিচের বাটনে ক্লিক করুন।</li>
                    <li>নির্ধারিত ক্লাসের পাশে থাকা <strong>"Join Live Class" (ক্লাসে জয়েন করুন)</strong> বাটনে ক্লিক করুন।</li>
                    <li>বাটনে ক্লিক করার সাথে সাথে আপনার উপস্থিতি (Attendance) সিস্টেমে রেকর্ড হয়ে যাবে এবং জুম (Zoom) বা গুগল মিট (Google Meet) ওপেন হয়ে যাবে।</li>
                    <li>ক্লাসে প্রবেশের পর অনুগ্রহ করে আপনার মাইক্রোফোন মিউট রাখুন এবং উস্তাদের পাঠদান মনোযোগ দিয়ে শুনুন।</li>
                </ol>
            </div>

            {{-- Call To Action Button --}}
            <div class="cta-container">
                <a href="{{ $portalUrl }}" class="cta-btn" target="_blank">
                    👉 সরাসরি আজকের ক্লাসে প্রবেশ করুন (Join Class)
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            যে কোনো কারিগরি সহায়তার জন্য স্টুডেন্ট পোর্টালে লগইন করে <strong>"অনলাইন সাপোর্ট"</strong> টিকেটে যোগাযোগ করুন।<br>
            © {{ date('Y') }} {{ $instituteName ?? 'IOM Education' }} · সর্বস্বত্ব সংরক্ষিত
        </div>
    </div>
</body>
</html>
