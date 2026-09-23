<x-admin-layout>
    <x-slot name="title">{{ $exam->title }} — Details</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.exams.index') }}">← Back to Exams</a>
            </div>
            <h1>{{ $exam->title }}</h1>
            <p>Subject: {{ $exam->subject->name ?? '—' }} · Date: {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }} · Marks: {{ $exam->full_marks }} (Pass: {{ $exam->pass_marks }})</p>
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px">
                @if($exam->has_mcq)
                    <span style="font-size:11px;font-weight:700;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;padding:2px 8px;border-radius:6px">
                        MCQ: {{ $exam->mcq_marks }}
                    </span>
                @endif
                @if($exam->has_written)
                    <span style="font-size:11px;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:2px 8px;border-radius:6px">
                        লিখিত: {{ $exam->written_marks }}
                    </span>
                @endif
                @if($exam->has_tamrin)
                    <span style="font-size:11px;font-weight:700;background:#f3e8ff;color:#7e22ce;border:1px solid #e9d5ff;padding:2px 8px;border-radius:6px">
                        তামরিন: {{ $exam->tamrin_marks }}
                    </span>
                @endif
                @if($exam->has_viva)
                    <span style="font-size:11px;font-weight:700;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:2px 8px;border-radius:6px">
                        ভাইভা: {{ $exam->viva_marks }}
                    </span>
                @endif
            </div>
        </div>
        <div class="page-header-actions" style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('admin.exams.test-exam', $exam) }}" class="btn btn-outline" style="background:#fef3c7;border-color:#fde68a;color:#92400e;display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-vial"></i> 🧪 টেস্ট এক্সাম (Test Exam)
            </a>
            <a href="{{ route('admin.exams.builder', $exam) }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-puzzle-piece"></i> Open Paper Builder
            </a>
        </div>
    </div>

    {{-- All Students Exam Merit List (মেধা তালিকা ও একক মার্কশীট) --}}
    <div class="card" style="margin-bottom:24px; border-radius:16px; overflow:hidden; background:#fff; border:1px solid #e2e8f0; box-shadow:0 4px 14px rgba(0,0,0,0.04)" id="examMeritSection">
        <div style="padding:16px 22px; background:linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px">
            <div>
                <h3 style="margin:0 0 4px; font-size:17px; font-weight:800; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-trophy" style="color:#fef08a"></i> 
                    {{ $exam->title }} — সকল শিক্ষার্থীর মেধা তালিকা (All Students Merit List)
                </h3>
                <p style="margin:0; font-size:12.5px; color:#bfdbfe">
                    বিষয়: {{ $exam->subject->name ?? '—' }} &middot; সেমিস্টার: {{ $exam->semester?->name ?? 'সেমিস্টার ' . $exam->semester_id }} &middot; পূর্ণমান: {{ $exam->full_marks }} (পাস: {{ $exam->pass_marks }})
                </p>
            </div>
            <div style="display:flex; gap:10px; align-items:center">
                <span style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); color:#fff; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:800">
                    {{ $meritList->count() }} জন মূল্যায়িত
                </span>
                <button onclick="printExamMeritList()" 
                   style="background:#fff; color:#1e40af; border:none; padding:8px 16px; border-radius:10px; font-size:12.5px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(0,0,0,0.1)">
                    <i class="fa-solid fa-print"></i> মেধা তালিকা প্রিন্ট করুন
                </button>
            </div>
        </div>

        @if($meritList->isEmpty())
            <div style="text-align:center; padding:45px 20px; color:#64748b">
                <i class="fa-solid fa-clipboard-list" style="font-size:38px; color:#cbd5e1; margin-bottom:12px; display:block"></i>
                <strong style="font-size:15px; color:#1e293b; display:block; margin-bottom:4px">এখনো কোনো শিক্ষার্থীর ফলাফল মূল্যায়ন হয়নি</strong>
                <span style="font-size:13px; color:#64748b">শিক্ষার্থীরা অনলাইন পরীক্ষা জমা দিলে বা শিক্ষক নম্বর এন্ট্রি করলে মেধা তালিকা তৈরি হবে।</span>
            </div>
        @else
            <div style="overflow-x:auto">
                <table style="width:100%; border-collapse:collapse; font-size:13px" id="meritTablePrint">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0">
                            <th style="padding:12px 10px; text-align:center; width:65px">মেধা স্থান</th>
                            <th style="padding:12px 14px; text-align:left; min-width:140px">রোল ও শিক্ষার্থী</th>
                            <th style="padding:12px 10px; text-align:center; background:#ecfdf5; font-weight:800; color:#064e3b">প্রাপ্ত মোট নম্বর</th>
                            <th style="padding:12px 10px; text-align:center">নম্বরের বিভাজন</th>
                            <th style="padding:12px 8px; text-align:center">শতকরা হার</th>
                            <th style="padding:12px 8px; text-align:center">লেটার গ্রেড</th>
                            <th style="padding:12px 10px; text-align:center">কওমি মান</th>
                            <th style="padding:12px 8px; text-align:center">ফলাফল</th>
                            <th style="padding:12px 12px; text-align:center" class="no-print">প্রাতিষ্ঠানিক মার্কশীট</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meritList as $item)
                        @php
                            $rankPos = $item['merit_position'];
                            $rankBg = match($rankPos) {
                                1 => 'background:#fef3c7; color:#b45309; border:1px solid #fde68a;',
                                2 => 'background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;',
                                3 => 'background:#ffedd5; color:#c2410c; border:1px solid #fed7aa;',
                                default => 'background:#f8fafc; color:#64748b; border:1px solid #e2e8f0;'
                            };
                            $medal = match($rankPos) {
                                1 => '🥇',
                                2 => '🥈',
                                3 => '🥉',
                                default => ''
                            };
                            $st = $item['student'];
                            $stName = $st->name ?? '—';
                            $stRoll = $st->student_code ?? $st->student_id ?? '—';
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9; transition:background .15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 10px; text-align:center">
                                <span style="{{ $rankBg }} font-weight:900; padding:4px 10px; border-radius:14px; font-size:12px; display:inline-block">
                                    {{ $medal }} {{ $item['merit_rank_bengali'] }}
                                </span>
                            </td>
                            <td style="padding:12px 14px">
                                <strong style="color:#0f172a; font-size:13.5px">{{ $stName }}</strong>
                                <div style="font-size:11.5px; color:#2563eb; font-weight:700; margin-top:2px">
                                    {{ $stRoll }}
                                </div>
                            </td>
                            <td style="padding:12px 10px; text-align:center; background:#ecfdf5">
                                <span style="font-size:17px; font-weight:900; color:{{ $item['marks'] >= $exam->pass_marks ? '#059669' : '#dc2626' }}">
                                    {{ $item['marks'] }}
                                </span>
                                <span style="font-size:12px; color:#64748b">/{{ $exam->full_marks }}</span>
                            </td>
                            <td style="padding:12px 10px; text-align:center">
                                <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap">
                                    @if($exam->has_mcq)
                                        <span style="font-size:11px; font-weight:700; background:#eff6ff; color:#1e40af; padding:2px 7px; border-radius:6px; border:1px solid #bfdbfe">
                                            MCQ: {{ $item['mcq_marks'] ?? '০' }}/{{ $exam->mcq_marks }}
                                        </span>
                                    @endif
                                    @if($exam->has_written)
                                        <span style="font-size:11px; font-weight:700; background:#fef3c7; color:#92400e; padding:2px 7px; border-radius:6px; border:1px solid #fde68a">
                                            লিখিত: {{ $item['written_marks'] ?? '০' }}/{{ $exam->written_marks }}
                                        </span>
                                    @endif
                                    @if($exam->has_tamrin)
                                        <span style="font-size:11px; font-weight:700; background:#f3e8ff; color:#7e22ce; padding:2px 7px; border-radius:6px; border:1px solid #e9d5ff">
                                            তামরিন: {{ $item['tamrin_marks'] ?? '০' }}/{{ $exam->tamrin_marks }}
                                        </span>
                                    @endif
                                    @if($exam->has_viva)
                                        <span style="font-size:11px; font-weight:700; background:#ecfdf5; color:#047857; padding:2px 7px; border-radius:6px; border:1px solid #a7f3d0">
                                            ভাইভা: {{ $item['viva_marks'] ?? '০' }}/{{ $exam->viva_marks }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding:12px 8px; text-align:center; font-weight:800; color:#334155">
                                {{ $item['percentage'] }}%
                            </td>
                            <td style="padding:12px 8px; text-align:center">
                                <strong style="font-size:14px; color:#0f172a">{{ $item['grade'] }}</strong>
                            </td>
                            <td style="padding:12px 10px; text-align:center">
                                <span style="font-size:11px; font-weight:800; color:#0f766e; background:#ccfbf1; padding:3px 9px; border-radius:12px; display:inline-block; white-space:nowrap">
                                    {{ $item['qawmi_grade']['name_bn'] ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:12px 8px; text-align:center">
                                @if($item['status'] === 'PASS')
                                    <span style="background:#dcfce7; color:#16a34a; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">উত্তীর্ণ (PASS)</span>
                                @else
                                    <span style="background:#fee2e2; color:#dc2626; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:800">অনুত্তীর্ণ (FAIL)</span>
                                @endif
                            </td>
                            <td style="padding:12px 12px; text-align:center" class="no-print">
                                <button type="button" 
                                    onclick="openSingleMarksheetModal({
                                        studentName: '{{ addslashes($stName) }}',
                                        studentRoll: '{{ addslashes($stRoll) }}',
                                        examTitle: '{{ addslashes($exam->title) }}',
                                        examType: '{{ addslashes($exam->type) }}',
                                        subjectName: '{{ addslashes($exam->subject->name ?? '—') }}',
                                        batchName: '{{ addslashes($exam->subject->batch->name ?? $exam->semester?->course?->title ?? '—') }}',
                                        semesterName: '{{ addslashes($exam->semester?->name ?? 'সেমিস্টার ' . $exam->semester_id) }}',
                                        examDate: '{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}',
                                        fullMarks: '{{ $exam->full_marks }}',
                                        passMarks: '{{ $exam->pass_marks }}',
                                        marks: '{{ $item['marks'] }}',
                                        mcqMarks: '{{ $item['mcq_marks'] ?? '0' }}',
                                        mcqFull: '{{ $exam->has_mcq ? $exam->mcq_marks : '0' }}',
                                        writtenMarks: '{{ $item['written_marks'] ?? '0' }}',
                                        writtenFull: '{{ $exam->has_written ? $exam->written_marks : '0' }}',
                                        tamrinMarks: '{{ $item['tamrin_marks'] ?? '0' }}',
                                        tamrinFull: '{{ $exam->has_tamrin ? $exam->tamrin_marks : '0' }}',
                                        vivaMarks: '{{ $item['viva_marks'] ?? '0' }}',
                                        vivaFull: '{{ $exam->has_viva ? $exam->viva_marks : '0' }}',
                                        percentage: '{{ $item['percentage'] }}',
                                        grade: '{{ $item['grade'] }}',
                                        qawmiGrade: '{{ addslashes($item['qawmi_grade']['name_bn'] ?? '') }}',
                                        status: '{{ $item['status'] }}',
                                        rankBengali: '{{ $item['merit_rank_bengali'] }}'
                                    })"
                                    style="padding:6px 12px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:8px; cursor:pointer; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:6px"
                                    title="এই শিক্ষার্থীর একক পরীক্ষার প্রাতিষ্ঠানিক মার্কশীট দেখুন ও প্রিন্ট করুন">
                                    <i class="fa-solid fa-file-lines" style="color:#2563eb"></i> একক মার্কশীট
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Eligible Attendees (Collapsed / Secondary info) --}}
    <div class="card" style="margin-bottom:24px">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center">
            <span class="card-title"><i class="fa-solid fa-id-card-clip" style="color:#0284c7"></i> পরীক্ষায় নিবন্ধিত শিক্ষার্থী ও এডমিট কার্ড (Attendees & Admit Cards)</span>
            <span class="badge badge-secondary no-dot">{{ $exam->attendees->count() }} Registered</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Admit Card #</th>
                        <th>শিক্ষার্থী</th>
                        <th>স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exam->attendees as $att)
                    <tr>
                        <td><span class="badge badge-scheduled no-dot">{{ $att->admit_card_no ?? 'ADM-PENDING' }}</span></td>
                        <td class="td-primary">{{ $att->student->name ?? '—' }}</td>
                        <td><span class="badge badge-active">Eligible</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">No attendees registered for this exam yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Online Exam Submissions & Retake Reset -->
    <div class="card" style="margin-top:24px">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <span class="card-title"><i class="fa-solid fa-file-signature" style="color:#6366f1"></i> অনলাইন পরীক্ষার খাতা ও পুনরায় সুযোগ (Submissions & Retake Reset)</span>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge badge-primary no-dot">{{ $exam->submissions->count() }} Submissions</span>
                @if($exam->submissions->isNotEmpty())
                <form method="POST" action="{{ route('admin.exams.regrade', $exam) }}" onsubmit="return confirm('এই পরীক্ষার সকল শিক্ষার্থীর খাতা বর্তমান প্রশ্ন ও সঠিক উত্তর অনুযায়ী পুনরায় মূল্যায়ন (Re-grade) করতে চান?')">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff;display:inline-flex;align-items:center;gap:6px;font-weight:600">
                        <i class="fa-solid fa-arrows-rotate"></i> সকল খাতা রি-গ্রেড করুন (Regrade All)
                    </button>
                </form>
                @endif
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>শিক্ষার্থী</th>
                        <th>আইডি</th>
                        <th>প্রাপ্ত স্কোর</th>
                        <th>সঠিক / ভুল</th>
                        <th>জমা দেওয়ার সময়</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exam->submissions as $sub)
                    <tr>
                        <td class="td-primary"><strong>{{ $sub->student?->name ?? '—' }}</strong></td>
                        <td><span style="font-family:monospace;font-weight:700">{{ $sub->student?->student_code ?? $sub->student?->student_id ?? '—' }}</span></td>
                        <td>
                            <strong style="color:#4338ca;font-size:15px">{{ number_format($sub->total_score, 1) }}</strong>
                            @if($sub->written_score > 0 || $sub->tamrin_score > 0 || $sub->viva_score > 0)
                                <div style="font-size:10.5px;color:#64748b">
                                    MCQ: {{ number_format($sub->mcq_score, 1) }} | লিখিত: {{ number_format($sub->written_score, 1) }}
                                    @if($sub->tamrin_score > 0) | তামরিন: {{ number_format($sub->tamrin_score, 1) }} @endif
                                    @if($sub->viva_score > 0) | ভাইভা: {{ number_format($sub->viva_score, 1) }} @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="color:#166534;font-weight:600"><i class="fa-solid fa-check"></i> {{ $sub->correct_count }}</span> /
                            <span style="color:#991b1b;font-weight:600"><i class="fa-solid fa-xmark"></i> {{ $sub->wrong_count }}</span>
                        </td>
                        <td class="td-muted">{{ $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y, h:i A') : 'চলমান' }}</td>
                        <td>
                            <span class="badge badge-{{ str_contains($sub->status, 'VIOLATION') ? 'danger' : 'active' }} no-dot">
                                {{ $sub->status }}
                            </span>
                        </td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('admin.exams.submissions.reset', [$exam, $sub]) }}" style="display:inline" onsubmit="return confirm('এই শিক্ষার্থীর পরীক্ষার খাতা মুছে পুনরায় পরীক্ষা দেওয়ার সুযোগ (Retake Reset) দিতে চান?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#e11d48;border-color:#fecdd3" title="পুনরায় পরীক্ষার সুযোগ দিন">
                                    <i class="fa-solid fa-rotate-left"></i> পুনরায় সুযোগ দিন (Reset)
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">এখনো কোনো শিক্ষার্থী এই পরীক্ষায় অংশ নেয়নি।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Re-Exam Appeals Section -->
    <div class="card" style="margin-top:24px">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <span class="card-title"><i class="fa-solid fa-file-circle-question" style="color:#d97706"></i> পুনরায় পরীক্ষার আবেদনসমূহ (Re-Exam Appeals)</span>
            <div>
                <span class="badge badge-warning no-dot">{{ $exam->appeals->where('status', 'PENDING')->count() }} Pending</span>
                <span class="badge badge-secondary no-dot">{{ $exam->appeals->count() }} Total Appeals</span>
            </div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>শিক্ষার্থী</th>
                        <th>আইডি</th>
                        <th style="min-width:250px">আবেদনের কারণ</th>
                        <th>জমার সময়</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exam->appeals as $appeal)
                    <tr>
                        <td class="td-primary"><strong>{{ $appeal->student?->name ?? '—' }}</strong></td>
                        <td><span style="font-family:monospace;font-weight:700">{{ $appeal->student?->student_code ?? $appeal->student?->student_id ?? '—' }}</span></td>
                        <td>
                            <div style="font-size:13px;color:#334155;line-height:1.4">"{{ $appeal->reason }}"</div>
                            @if($appeal->admin_remarks)
                                <div style="font-size:11px;color:#64748b;margin-top:2px">মন্তব্য: {{ $appeal->admin_remarks }}</div>
                            @endif
                        </td>
                        <td class="td-muted">{{ $appeal->created_at ? $appeal->created_at->format('d M Y, h:i A') : '—' }}</td>
                        <td>
                            @if($appeal->isPending())
                                <span class="badge badge-warning no-dot" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:700">
                                    <i class="fa-regular fa-clock"></i> PENDING
                                </span>
                            @elseif($appeal->isApproved())
                                <span class="badge badge-success no-dot" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:700">
                                    <i class="fa-solid fa-check-circle"></i> APPROVED
                                </span>
                                <div style="font-size:10px;color:#047857">{{ $appeal->reviewer?->name }}</div>
                            @else
                                <span class="badge badge-danger no-dot" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-weight:700">
                                    <i class="fa-solid fa-xmark"></i> REJECTED
                                </span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            @if($appeal->isPending())
                                <form method="POST" action="{{ route('admin.exams.appeals.approve', $appeal) }}" style="display:inline" onsubmit="return confirm('এই শিক্ষার্থীর পুনরায় পরীক্ষার আপিল অনুমোদন করতে চান? পূর্বের পরীক্ষার খাতা রিসেট হয়ে যাবে এবং শিক্ষার্থী নতুন করে পরীক্ষা দিতে পারবে।')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" style="background:#059669;border-color:#047857;color:#fff;font-size:12px;font-weight:700" title="আপিল অনুমোদন করে পুনরায় পরীক্ষা দেওয়ার সুযোগ দিন">
                                        <i class="fa-solid fa-check"></i> অনুমোদন (Approve)
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.exams.appeals.reject', $appeal) }}" style="display:inline" onsubmit="return confirm('এই আপিলটি বাতিল করতে চান?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline" style="color:#e11d48;border-color:#fecdd3;font-size:12px" title="আপিল বাতিল করুন">
                                        <i class="fa-solid fa-xmark"></i> বাতিল
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px;color:#94a3b8">সম্পন্ন</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">এই পরীক্ষায় পুনরায় পরীক্ষা দেওয়ার কোনো আপিল আবেদন জমা হয়নি।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Single Exam Official Marksheet Modal (একক পরীক্ষার প্রাতিষ্ঠানিক মার্কশীট) --}}
    <div id="singleMarksheetModal" class="modal-overlay" onclick="if(event.target===this) closeSingleMarksheetModal()">
        <div class="modal-card" style="max-width:720px; border-radius:18px; overflow:hidden; font-family:'Kalpurush', sans-serif; box-shadow:0 10px 40px rgba(0,0,0,0.25)">
            <div class="no-print" style="padding:14px 20px; background:#1e40af; color:#fff; display:flex; justify-content:space-between; align-items:center">
                <span style="font-weight:800; font-size:15px; display:flex; align-items:center; gap:8px">
                    <i class="fa-solid fa-stamp"></i> একক পরীক্ষার প্রাতিষ্ঠানিক মার্কশীট (Single Exam Official Marksheet)
                </span>
                <div style="display:flex; gap:10px; align-items:center">
                    <button type="button" onclick="printSingleMarksheet()" class="btn btn-sm" style="background:#22c55e; color:#fff; border:none; padding:6px 14px; font-weight:800; border-radius:8px; display:inline-flex; align-items:center; gap:6px; cursor:pointer">
                        <i class="fa-solid fa-print"></i> মার্কশীট প্রিন্ট
                    </button>
                    <button type="button" onclick="closeSingleMarksheetModal()" style="background:none; border:none; color:#fff; font-size:24px; line-height:1; cursor:pointer">&times;</button>
                </div>
            </div>

            {{-- Printable Marksheet Body --}}
            <div id="printableMarksheet" style="padding:32px; background:#fff; color:#0f172a">
                {{-- Letterhead --}}
                <div style="text-align:center; border-bottom:2px solid #1e40af; padding-bottom:16px; margin-bottom:20px">
                    <div style="font-size:24px; font-weight:900; color:#1e40af; letter-spacing:0.5px">
                        ইসলামিক অনলাইন মাদরাসা
                    </div>
                    <div style="font-size:13px; color:#475569; font-weight:700; margin-top:2px">
                        Islamic Online Madrasah (IOM) &middot; শিক্ষা ও পরীক্ষা বিভাগ
                    </div>
                    <div style="margin-top:10px; display:inline-block; background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:4px 16px; border-radius:20px; font-weight:800; font-size:13.5px">
                        একক পরীক্ষার মূল্যায়ন পত্র / মার্কশীট (OFFICIAL MARK SHEET)
                    </div>
                </div>

                {{-- Exam & Student Meta --}}
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:20px">
                    <div>
                        <div style="font-size:12px; color:#64748b">শিক্ষার্থীর নাম:</div>
                        <div id="smStudentName" style="font-size:16px; font-weight:800; color:#0f172a"></div>
                        <div style="font-size:12.5px; color:#2563eb; font-weight:700; margin-top:3px">
                            স্টুডেন্ট আইডি / রোল: <span id="smStudentRoll"></span>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:12px; color:#64748b">পরীক্ষার শিরোনাম:</div>
                        <strong id="smExamTitle" style="font-size:15px; color:#0f172a; display:block"></strong>
                        <div style="font-size:12px; color:#475569; margin-top:3px">
                            <span id="smSubjectName"></span> &middot; <span id="smSemesterName"></span>
                        </div>
                        <div style="font-size:11.5px; color:#64748b; margin-top:2px">
                            তারিখ: <span id="smExamDate"></span> &middot; ধরন: <span id="smExamType" style="font-weight:700"></span>
                        </div>
                    </div>
                </div>

                {{-- Breakdown Table --}}
                <table style="width:100%; border-collapse:collapse; margin-bottom:20px; font-size:13.5px">
                    <thead>
                        <tr style="background:#1e40af; color:#fff">
                            <th style="padding:10px 12px; text-align:left; border:1px solid #1e40af">পরীক্ষার অংশ (Component)</th>
                            <th style="padding:10px 12px; text-align:center; width:110px; border:1px solid #1e40af">নির্ধারিত পূর্ণমান</th>
                            <th style="padding:10px 12px; text-align:center; width:120px; border:1px solid #1e40af">প্রাপ্ত নম্বর</th>
                            <th style="padding:10px 12px; text-align:center; width:100px; border:1px solid #1e40af">অবস্থা</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="rowSmMcq">
                            <td style="padding:10px 12px; border:1px solid #cbd5e1">বহুনির্বাচনী প্রশ্ন (MCQ)</td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1" id="smMcqFull"></td>
                            <td style="padding:10px 12px; text-align:center; font-weight:800; border:1px solid #cbd5e1; color:#1e40af" id="smMcqScore"></td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1; font-size:12px; color:#64748b">মূল্যায়িত</td>
                        </tr>
                        <tr id="rowSmWritten">
                            <td style="padding:10px 12px; border:1px solid #cbd5e1">লিখিত পরীক্ষা (Written)</td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1" id="smWrittenFull"></td>
                            <td style="padding:10px 12px; text-align:center; font-weight:800; border:1px solid #cbd5e1; color:#1e40af" id="smWrittenScore"></td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1; font-size:12px; color:#64748b">মূল্যায়িত</td>
                        </tr>
                        <tr id="rowSmTamrin">
                            <td style="padding:10px 12px; border:1px solid #cbd5e1">তামরিন / ব্যবহারিক অনুশীলন (Tamrin)</td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1" id="smTamrinFull"></td>
                            <td style="padding:10px 12px; text-align:center; font-weight:800; border:1px solid #cbd5e1; color:#1e40af" id="smTamrinScore"></td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1; font-size:12px; color:#64748b">মূল্যায়িত</td>
                        </tr>
                        <tr id="rowSmViva">
                            <td style="padding:10px 12px; border:1px solid #cbd5e1">মৌখিক পরীক্ষা / ভাইভা (Viva Voce)</td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1" id="smVivaFull"></td>
                            <td style="padding:10px 12px; text-align:center; font-weight:800; border:1px solid #cbd5e1; color:#1e40af" id="smVivaScore"></td>
                            <td style="padding:10px 12px; text-align:center; border:1px solid #cbd5e1; font-size:12px; color:#64748b">মূল্যায়িত</td>
                        </tr>
                        <tr style="background:#f1f5f9; font-weight:900">
                            <td style="padding:12px 12px; border:2px solid #94a3b8">সর্বমোট প্রাপ্ত নম্বর (Grand Total)</td>
                            <td style="padding:12px 12px; text-align:center; border:2px solid #94a3b8" id="smGrandFull"></td>
                            <td style="padding:12px 12px; text-align:center; border:2px solid #94a3b8; font-size:18px; color:#1e40af" id="smGrandObtained"></td>
                            <td style="padding:12px 12px; text-align:center; border:2px solid #94a3b8" id="smFinalStatus"></td>
                        </tr>
                    </tbody>
                </table>

                {{-- Performance Evaluation Badges --}}
                <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:12px; padding:16px; margin-bottom:30px; display:grid; grid-template-columns: repeat(4, 1fr); gap:12px; text-align:center">
                    <div>
                        <div style="font-size:12px; color:#065f46; font-weight:700">মেধাক্রম / স্থান</div>
                        <div id="smRankBadge" style="font-size:18px; font-weight:900; color:#b45309; margin-top:2px"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#065f46; font-weight:700">শতকরা প্রাপ্তি</div>
                        <div id="smPctBadge" style="font-size:18px; font-weight:900; color:#064e3b; margin-top:2px"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#065f46; font-weight:700">লেটার গ্রেড</div>
                        <div id="smGradeBadge" style="font-size:18px; font-weight:900; color:#1d4ed8; margin-top:2px"></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#065f46; font-weight:700">কওমি মাদরাসা মান</div>
                        <div id="smQawmiBadge" style="font-size:14px; font-weight:800; color:#047857; margin-top:4px"></div>
                    </div>
                </div>

                {{-- Official Signatures --}}
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:45px; padding-top:16px">
                    <div style="text-align:center; width:180px; border-top:1px dashed #64748b; padding-top:6px">
                        <div style="font-size:13px; font-weight:800; color:#0f172a">পরীক্ষকের স্বাক্ষর</div>
                        <div style="font-size:11px; color:#64748b">বিষয় শিক্ষক / নিরীক্ষক</div>
                    </div>
                    <div style="text-align:center">
                        <div style="border:2px dashed #94a3b8; border-radius:50%; width:70px; height:70px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:11px; font-weight:700; margin:0 auto">
                            মাদরাসার সিল
                        </div>
                    </div>
                    <div style="text-align:center; width:180px; border-top:1px dashed #64748b; padding-top:6px">
                        <div style="font-size:13px; font-weight:800; color:#0f172a">পরীক্ষা নিয়ন্ত্রক</div>
                        <div style="font-size:11px; color:#64748b">ইসলামিক অনলাইন মাদরাসা</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }
            #printableMarksheet, #printableMarksheet * {
                visibility: visible !important;
            }
            #printableMarksheet {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                padding: 20px !important;
                background: #fff !important;
                font-family: 'Kalpurush', sans-serif !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>

    <script>
        window.openSingleMarksheetModal = function(data) {
            document.getElementById('smStudentName').textContent = data.studentName;
            document.getElementById('smStudentRoll').textContent = data.studentRoll;
            document.getElementById('smExamTitle').textContent = data.examTitle;
            document.getElementById('smSubjectName').textContent = data.subjectName;
            document.getElementById('smSemesterName').textContent = data.semesterName;
            document.getElementById('smExamDate').textContent = data.examDate;
            document.getElementById('smExamType').textContent = data.examType;

            // MCQ
            const rowMcq = document.getElementById('rowSmMcq');
            if (parseFloat(data.mcqFull) > 0) {
                rowMcq.style.display = '';
                document.getElementById('smMcqFull').textContent = data.mcqFull;
                document.getElementById('smMcqScore').textContent = data.mcqMarks;
            } else {
                rowMcq.style.display = 'none';
            }

            // Written
            const rowWritten = document.getElementById('rowSmWritten');
            if (parseFloat(data.writtenFull) > 0) {
                rowWritten.style.display = '';
                document.getElementById('smWrittenFull').textContent = data.writtenFull;
                document.getElementById('smWrittenScore').textContent = data.writtenMarks;
            } else {
                rowWritten.style.display = 'none';
            }

            // Tamrin
            const rowTamrin = document.getElementById('rowSmTamrin');
            if (parseFloat(data.tamrinFull) > 0) {
                rowTamrin.style.display = '';
                document.getElementById('smTamrinFull').textContent = data.tamrinFull;
                document.getElementById('smTamrinScore').textContent = data.tamrinMarks;
            } else {
                rowTamrin.style.display = 'none';
            }

            // Viva
            const rowViva = document.getElementById('rowSmViva');
            if (parseFloat(data.vivaFull) > 0) {
                rowViva.style.display = '';
                document.getElementById('smVivaFull').textContent = data.vivaFull;
                document.getElementById('smVivaScore').textContent = data.vivaMarks;
            } else {
                rowViva.style.display = 'none';
            }

            // Total
            document.getElementById('smGrandFull').textContent = data.fullMarks;
            document.getElementById('smGrandObtained').textContent = data.marks;
            document.getElementById('smFinalStatus').innerHTML = data.status === 'PASS' 
                ? '<span style="color:#16a34a; font-weight:800">উত্তীর্ণ (PASS)</span>' 
                : '<span style="color:#dc2626; font-weight:800">অনুত্তীর্ণ (FAIL)</span>';

            // Badges
            document.getElementById('smRankBadge').textContent = data.rankBengali;
            document.getElementById('smPctBadge').textContent = data.percentage + '%';
            document.getElementById('smGradeBadge').textContent = data.grade;
            document.getElementById('smQawmiBadge').textContent = data.qawmiGrade;

            const m = document.getElementById('singleMarksheetModal');
            if (m) {
                m.classList.add('open', 'is-active', 'show');
                m.style.setProperty('display', 'flex', 'important');
                m.style.setProperty('opacity', '1', 'important');
                m.style.setProperty('pointer-events', 'all', 'important');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeSingleMarksheetModal = function() {
            const m = document.getElementById('singleMarksheetModal');
            if (m) {
                m.classList.remove('open', 'is-active', 'show');
                m.style.setProperty('display', 'none', 'important');
                m.style.setProperty('opacity', '0', 'important');
                m.style.setProperty('pointer-events', 'none', 'important');
                document.body.style.overflow = '';
            }
        };

        window.printSingleMarksheet = function() {
            window.print();
        };

        window.printExamMeritList = function() {
            window.print();
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeSingleMarksheetModal();
            }
        });
    </script>
</x-admin-layout>
