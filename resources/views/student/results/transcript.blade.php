<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>৬-সেমিস্টার একত্রিত একাডেমিক ট্রান্সক্রিপ্ট — {{ $student->name }}</title>
    <style>
        @font-face {
            font-family: 'Kalpurush';
            src: url('/fonts/Kalpurush.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Kalpurush', Arial, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 24px 16px;
            color: #0f172a;
        }
        .transcript-container {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #1e3a8a;
            border-radius: 16px;
            padding: 36px 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            position: relative;
        }
        .transcript-header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .inst-title {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a8a;
            margin: 0 0 4px;
        }
        .inst-sub {
            font-size: 14px;
            font-weight: 700;
            color: #059669;
            letter-spacing: 0.5px;
            margin: 0 0 6px;
        }
        .doc-title {
            display: inline-block;
            background: #1e3a8a;
            color: #ffffff;
            font-size: 16px;
            font-weight: 800;
            padding: 6px 24px;
            border-radius: 20px;
            letter-spacing: 1px;
            margin-top: 6px;
        }
        .student-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 26px;
            font-size: 13.5px;
        }
        .student-card-item {
            display: flex;
            gap: 8px;
            margin-bottom: 4px;
        }
        .student-card-item .label {
            color: #64748b;
            font-weight: 700;
            width: 130px;
            flex-shrink: 0;
        }
        .student-card-item .value {
            color: #0f172a;
            font-weight: 800;
        }
        .semesters-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 26px;
        }
        @media (max-width: 768px) {
            .semesters-grid { grid-template-columns: 1fr; }
        }
        .semester-card {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            background: #ffffff;
        }
        .sem-title-bar {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 800;
            font-size: 13.5px;
            color: #1e3a8a;
        }
        .sem-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .sem-table th, .sem-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .sem-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            font-size: 11px;
        }
        .sem-footer {
            background: #f8fafc;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            font-weight: 800;
            border-top: 1px solid #cbd5e1;
        }
        .cgpa-summary-card {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .grading-legend {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 11px;
        }
        .legend-item {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 4px;
        }
        .official-signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 50px;
            padding-top: 20px;
        }
        .sig-box {
            text-align: center;
            width: 200px;
            font-size: 12px;
            color: #334155;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-bottom: 6px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .transcript-container { border: none; box-shadow: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- Print & Back Buttons --}}
    <div style="max-width:960px; margin:0 auto 16px; display:flex; justify-content:space-between; align-items:center" class="no-print">
        <a href="{{ route('student.results.index') }}" style="background:#e2e8f0; color:#1e293b; padding:8px 18px; border-radius:8px; text-decoration:none; font-weight:700; font-size:13px">
            ← ফলাফল পেজে ফিরুন (Back to Results)
        </a>
        <button onclick="window.print()" style="background:#1e3a8a; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:800; font-size:14px; cursor:pointer">
            🖨️ প্রিন্ট / সেভ করুন (Print Official Transcript)
        </button>
    </div>

    <div class="transcript-container">
        {{-- Header --}}
        <div class="transcript-header">
            <h1 class="inst-title">ইসলামিক অনলাইন মাদরাসা (Islamic Online Madrasah)</h1>
            <div class="inst-sub">দারুল উলুম দেওবন্দ সিলেবাস ও আধুনিক প্রযুক্তি ভিত্তিক অনলাইন উচ্চতর ইসলামিক শিক্ষালয়</div>
            <div class="doc-title">৬-সেমিস্টার একত্রিত একাডেমিক ট্রান্সক্রিপ্ট (CONSOLIDATED TRANSCRIPT)</div>
            <div style="font-size:12px; color:#64748b; margin-top:8px">
                ইস্যুর তারিখ: {{ date('d F Y') }} | ট্র্যাকিং আইডি: IOM-TR-{{ $student->id }}-{{ date('Ymd') }}
            </div>
        </div>

        {{-- Student Information Card --}}
        <div class="student-card">
            <div>
                <div class="student-card-item">
                    <span class="label">শিক্ষার্থীর নাম:</span>
                    <span class="value">{{ $student->name }}</span>
                </div>
                <div class="student-card-item">
                    <span class="label">রোল / স্টুডেন্ট আইডি:</span>
                    <span class="value" style="color:#1e3a8a; font-size:15px">{{ $student->student_code ?? $student->student_id ?? '—' }}</span>
                </div>
                <div class="student-card-item">
                    <span class="label">পিতার নাম:</span>
                    <span class="value">{{ $student->father_name ?? '—' }}</span>
                </div>
            </div>
            <div>
                <div class="student-card-item">
                    <span class="label">কোর্স / প্রোগ্রাম:</span>
                    <span class="value">{{ $course->name ?? '—' }}</span>
                </div>
                <div class="student-card-item">
                    <span class="label">শিক্ষাবর্ষ / সেশন:</span>
                    <span class="value">{{ $primaryEnrollment?->batch?->name ?? 'রানিং সেশন' }}</span>
                </div>
                <div class="student-card-item">
                    <span class="label">রেজিস্ট্রেশন স্ট্যাটাস:</span>
                    <span class="value" style="color:#059669">উত্তীর্ণ ও সনদযোগ্য (Compliant)</span>
                </div>
            </div>
        </div>

        {{-- Semester-by-Semester Grid (1st to 6th Semester) --}}
        <div class="semesters-grid">
            @foreach($semestersData as $index => $sd)
            <div class="semester-card">
                <div class="sem-title-bar">
                    <span>
                        @php
                            $semNames = [1 => '১ম সেমিস্টার', 2 => '২য় সেমিস্টার', 3 => '৩য় সেমিস্টার', 4 => '৪র্থ সেমিস্টার', 5 => '৫ম সেমিস্টার', 6 => '৬ষ্ঠ সেমিস্টার'];
                            $bnSemName = $semNames[$sd['sequence_no']] ?? $sd['name'];
                        @endphp
                        {{ $bnSemName }}
                    </span>
                    <span style="font-size:12px; color:#059669">
                        SGPA: {{ number_format($sd['sgpa'], 2) }}
                    </span>
                </div>
                <table class="sem-table">
                    <thead>
                        <tr>
                            <th style="width:40%">বিষয়</th>
                            <th style="text-align:center">ক্রেডিট</th>
                            <th style="text-align:center">নম্বর</th>
                            <th style="text-align:center">গ্রেড</th>
                            <th style="text-align:center">GPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sd['subjects'] as $sub)
                        <tr>
                            <td>
                                <strong>{{ $sub['name'] }}</strong><br>
                                <small style="color:#64748b">{{ $sub['code'] }}</small>
                            </td>
                            <td style="text-align:center">{{ $sub['credit'] }}</td>
                            <td style="text-align:center; font-weight:700">{{ $sub['total_mark'] }}</td>
                            <td style="text-align:center"><strong>{{ $sub['grade'] }}</strong></td>
                            <td style="text-align:center; font-weight:700">{{ number_format($sub['gpa'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:18px; color:#94a3b8">
                                এই সেমিস্টারের কোনো প্রকাশিত ফলাফল নেই
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="sem-footer">
                    <span>মোট ক্রেডিট: {{ $sd['total_credit'] }} (অর্জিত: {{ $sd['earned_credit'] }})</span>
                    <span>কওমি মান: <strong style="color:#1e3a8a">{{ $sd['qawmi_grade']['name_bn'] }}</strong></span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Cumulative CGPA & Qawmi Honor Summary Card --}}
        <div class="cgpa-summary-card">
            <div>
                <div style="font-size:13px; color:#a7f3d0; font-weight:700">সর্বমোট অর্জিত ক্রেডিট (Total Earned Credits)</div>
                <div style="font-size:24px; font-weight:800">{{ $totalCreditsEarned }} / {{ $totalCreditsAttempted }} Credits</div>
            </div>
            <div>
                <div style="font-size:13px; color:#a7f3d0; font-weight:700">চূড়ান্ত কিউমুলেটিভ সিজিপিএ (Cumulative GPA)</div>
                <div style="font-size:32px; font-weight:900; color:#fef08a">{{ number_format($cgpa, 2) }} <span style="font-size:18px">/ 5.00</span></div>
            </div>
            <div>
                <div style="font-size:13px; color:#a7f3d0; font-weight:700">কওমি মাদরাসা গ্রেড মানদণ্ড (Qawmi Honor)</div>
                <div style="font-size:22px; font-weight:800; color:#fff">
                    {{ $overallQawmiGrade['name_bn'] }} <small style="font-size:15px; color:#a7f3d0">({{ $overallQawmiGrade['name_ar'] }})</small>
                </div>
                <div style="font-size:12px; color:#d1fae5">{{ $overallQawmiGrade['label'] }}</div>
            </div>
        </div>

        {{-- Grading Standard Reference Table --}}
        <div style="font-size:12px; font-weight:800; color:#1e3a8a; margin-bottom:6px">
            ফলাফল মূল্যায়ন ও কওমি গ্রেডিং মানদণ্ড (Academic &amp; Qawmi Grading Standard):
        </div>
        <div class="grading-legend">
            <div class="legend-item" style="border-color:#10b981; background:#ecfdf5">
                <strong style="color:#065f46">মুমতাজ (ممتاز)</strong><br>
                <span>৮০% - ১০০%</span><br>
                <small>GPA: 4.75 - 5.00 (A+)</small>
            </div>
            <div class="legend-item" style="border-color:#3b82f6; background:#eff6ff">
                <strong style="color:#1d4ed8">জায়্যিদ জিদ্দান</strong><br>
                <span>৬৫% - ৭৯%</span><br>
                <small>GPA: 3.75 - 4.74 (A)</small>
            </div>
            <div class="legend-item" style="border-color:#06b6d4; background:#ecfeff">
                <strong style="color:#0e7490">জায়্যিদ (جيد)</strong><br>
                <span>৫০% - ৬৪%</span><br>
                <small>GPA: 2.75 - 3.74 (B/A-)</small>
            </div>
            <div class="legend-item" style="border-color:#f59e0b; background:#fffbeb">
                <strong style="color:#b45309">মাকবুল (مقبول)</strong><br>
                <span>৪০% - ৪৯%</span><br>
                <small>GPA: 2.00 - 2.74 (C)</small>
            </div>
            <div class="legend-item" style="border-color:#ef4444; background:#fef2f2">
                <strong style="color:#b91c1c">রাসিব (راسب)</strong><br>
                <span>৪০% এর নিচে</span><br>
                <small>GPA: 0.00 (F / Fail)</small>
            </div>
            <div class="legend-item" style="border-color:#8b5cf6; background:#f5f3ff">
                <strong style="color:#6d28d9">নন-এক্সাম মার্কস</strong><br>
                <span>তামরিন, তাজবীদ</span><br>
                <small>ও ডিএনএস ব্যবহারিক</small>
            </div>
        </div>

        {{-- Official Signatures --}}
        <div class="official-signatures">
            <div class="sig-box">
                <div class="sig-line"></div>
                <strong>পরীক্ষা নিয়ন্ত্রক</strong><br>
                ইসলামিক অনলাইন মাদরাসা
            </div>
            <div class="sig-box" style="width:140px">
                <div style="border:2px dashed #94a3b8; border-radius:50%; width:90px; height:90px; margin:0 auto 6px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:11px; text-transform:uppercase; font-weight:800">
                    অফিসিয়াল<br>সিলমোহর
                </div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <strong>রেজিস্ট্রার / মুহতামিম</strong><br>
                ইসলামিক অনলাইন মাদরাসা
            </div>
        </div>
    </div>
</body>
</html>
