<x-student-layout>
    <x-slot name="title">আমার ফলাফল ও ট্রান্সক্রিপ্ট (My Results)</x-slot>

    <style>
        .res-wrapper {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .res-header {
            background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
            border-radius: 16px;
            padding: 22px 26px;
            color: #fff;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.25);
        }
    </style>

    <div class="res-wrapper">
        {{-- Page Header --}}
        <div class="res-header">
            <div>
                <h1 style="margin:0 0 6px; font-size:22px; font-weight:800; display:flex; align-items:center; gap:10px">
                    <i class="fa-solid fa-award"></i> আমার ফলাফল ও গ্রেডশীট (Academic Results)
                </h1>
                <p style="margin:0; font-size:13.5px; color:#d1fae5">
                    সেমিস্টারভিত্তিক বিষয়ভিত্তিক মার্কস, কওমি মাদরাসা গ্রেড মানদণ্ড ও মেধা স্থান
                </p>
            </div>
            <div>
                <a href="{{ route('student.results.transcript') }}" 
                   style="background:#ffffff; color:#065f46; border:none; padding:10px 20px; border-radius:10px; font-weight:800; font-size:13.5px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 10px rgba(0,0,0,0.15)">
                    <i class="fa-solid fa-file-invoice"></i> ৬-সেমিস্টার একত্রিত ট্রান্সক্রিপ্ট (Consolidated Transcript)
                </a>
            </div>
        </div>

        {{-- Section 1: Semester Final Marks & Merit Rank --}}
        <div class="card" style="margin-bottom:24px; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0">
            <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
                <div style="font-size:15px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-graduation-cap" style="color:#059669"></i> সেমিস্টার ফাইনাল মার্কশীট ও মেধা স্থান (Final Results &amp; Merit Rank)
                </div>
                <div style="font-size:12.5px; color:#64748b">
                    শুধুমাত্র প্রকাশিত ফলাফল প্রদর্শিত হচ্ছে
                </div>
            </div>

            <div class="table-wrapper">
                <table style="width:100%; border-collapse:collapse; font-size:13px">
                    <thead>
                        <tr style="background:#f1f5f9; border-bottom:2px solid #e2e8f0">
                            <th style="padding:12px 10px; text-align:center">মেধাক্রম</th>
                            <th style="padding:12px 14px; text-align:left">বিষয় ও কোড</th>
                            <th style="padding:12px 10px; text-align:left">সেমিস্টার</th>
                            <th style="padding:12px 8px; text-align:center">সিটি</th>
                            <th style="padding:12px 8px; text-align:center">মিড</th>
                            <th style="padding:12px 8px; text-align:center">ফাইনাল</th>
                            <th style="padding:12px 8px; text-align:center">উপস্থিতি (/১০)</th>
                            <th style="padding:12px 10px; text-align:center; background:#ecfdf5; font-weight:800; color:#064e3b">মোট নম্বর (/১০০)</th>
                            <th style="padding:12px 8px; text-align:center">গ্রেড (GPA)</th>
                            <th style="padding:12px 10px; text-align:center">কওমি মান</th>
                            <th style="padding:12px 10px; text-align:center">ফলাফল</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($finalMarks as $fm)
                        <tr style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:14px 10px; text-align:center">
                                @if($fm->merit_position)
                                    <span style="background:#fef3c7; color:#b45309; font-weight:900; padding:4px 9px; border-radius:12px; font-size:12px; border:1px solid #fde68a; display:inline-block">
                                        {{ $fm->merit_rank_bengali }}
                                    </span>
                                @else
                                    <span style="color:#cbd5e1">—</span>
                                @endif
                            </td>
                            <td style="padding:14px 14px">
                                <strong style="color:#0f172a">{{ $fm->subject->name ?? '—' }}</strong><br>
                                <small style="color:#64748b">{{ $fm->subject->code ?? '—' }}</small>
                            </td>
                            <td style="padding:14px 10px">
                                {{ $fm->semester->name ?? 'রানিং সেমিস্টার' }}
                            </td>
                            <td style="padding:14px 8px; text-align:center">{{ $fm->class_test_converted ?? '—' }}</td>
                            <td style="padding:14px 8px; text-align:center">{{ $fm->midterm_converted ?? '—' }}</td>
                            <td style="padding:14px 8px; text-align:center">{{ $fm->final_converted ?? '—' }}</td>
                            <td style="padding:14px 8px; text-align:center">{{ $fm->attendance_converted ?? '—' }}</td>
                            <td style="padding:14px 10px; text-align:center; background:#ecfdf5">
                                <strong style="font-size:16px; color:{{ $fm->total_mark >= 40 ? '#059669' : '#dc2626' }}">
                                    {{ $fm->total_mark }}
                                </strong>
                            </td>
                            <td style="padding:14px 8px; text-align:center">
                                <strong>{{ $fm->grade }}</strong><br>
                                <small style="color:#64748b">{{ number_format($fm->gpa, 2) }}</small>
                            </td>
                            <td style="padding:14px 10px; text-align:center">
                                <span style="font-size:11px; font-weight:800; color:#0f766e; background:#ccfbf1; padding:3px 8px; border-radius:12px; display:inline-block">
                                    {{ $fm->qawmi_grade['name_bn'] }}
                                </span>
                            </td>
                            <td style="padding:14px 10px; text-align:center">
                                @if($fm->status === 'PASS')
                                    <span style="background:#dcfce7; color:#16a34a; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">উত্তীর্ণ (PASS)</span>
                                @else
                                    <span style="background:#fee2e2; color:#dc2626; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">অনুত্তীর্ণ (FAIL)</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" style="text-align:center; padding:35px; color:#94a3b8">
                                এখনো কোনো সেমিস্টার ফাইনাল ফলাফল প্রকাশিত হয়নি।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 2: Individual Exam Attempts & Scores --}}
        <div class="card" style="border-radius:16px; overflow:hidden; border:1px solid #e2e8f0">
            <div style="padding:16px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; font-size:15px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px">
                <i class="fa-solid fa-list-check" style="color:#2563eb"></i> একক পরীক্ষার ফলাফল ও মূল্যায়ন (Individual Examination Results)
            </div>

            <div class="table-wrapper">
                <table style="width:100%; border-collapse:collapse; font-size:13px">
                    <thead>
                        <tr style="background:#f1f5f9; border-bottom:2px solid #e2e8f0">
                            <th style="padding:12px 14px; text-align:left">বিষয়</th>
                            <th style="padding:12px 14px; text-align:left">পরীক্ষার নাম</th>
                            <th style="padding:12px 10px; text-align:center">অ্যাটেম্পট</th>
                            <th style="padding:12px 10px; text-align:center">প্রাপ্ত নম্বর</th>
                            <th style="padding:12px 10px; text-align:center">গ্রেড</th>
                            <th style="padding:12px 10px; text-align:center">স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $res)
                        <tr style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:14px">
                                <strong>{{ $res->subject->name ?? $res->exam?->subject?->name ?? '—' }}</strong>
                            </td>
                            <td style="padding:14px">{{ $res->exam->title ?? '—' }}</td>
                            <td style="padding:14px 10px; text-align:center">
                                <span class="badge badge-secondary no-dot">অ্যাটেম্পট #{{ $res->attempt_no }}</span>
                            </td>
                            <td style="padding:14px 10px; text-align:center">
                                <strong style="color:#059669; font-size:14px">{{ $res->marks }}</strong> / {{ $res->exam->full_marks ?? 100 }}
                            </td>
                            <td style="padding:14px 10px; text-align:center">
                                <span class="badge badge-active no-dot" style="font-size:12px">{{ $res->grade ?? 'A' }}</span>
                            </td>
                            <td style="padding:14px 10px; text-align:center">
                                @if($res->status === 'PASS')
                                    <span style="background:#dcfce7; color:#16a34a; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">Passed</span>
                                @else
                                    <span style="background:#fee2e2; color:#dc2626; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">Failed (Retake Required)</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:35px; color:#94a3b8">
                                এখনো কোনো প্রকাশিত পরীক্ষার ফলাফল নেই।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-student-layout>
