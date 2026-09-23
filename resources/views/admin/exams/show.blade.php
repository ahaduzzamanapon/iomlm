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

    <div class="grid-2">
        <!-- Eligible Attendees -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Exam Attendees & Admit Cards</span>
                <span class="badge badge-secondary no-dot">{{ $exam->attendees->count() }} Registered</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Admit Card #</th>
                            <th>Student</th>
                            <th>Status</th>
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

        <!-- Exam Results -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Entered Results</span>
                <span class="badge badge-active no-dot">{{ $exam->results->count() }} Results</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exam->results as $res)
                        <tr>
                            <td class="td-primary">{{ $res->student->name ?? '—' }}</td>
                            <td>
                                <strong>{{ $res->marks }}/{{ $exam->full_marks }}</strong>
                                @if($res->mcq_marks !== null || $res->written_marks !== null || $res->tamrin_marks !== null || $res->viva_marks !== null)
                                    <div style="font-size:11px;color:#64748b;margin-top:2px">
                                        @if($res->mcq_marks !== null) <span>MCQ: {{ $res->mcq_marks }}</span> @endif
                                        @if($res->written_marks !== null) <span style="margin-left:4px">লিখিত: {{ $res->written_marks }}</span> @endif
                                        @if($res->tamrin_marks !== null) <span style="margin-left:4px">তামরিন: {{ $res->tamrin_marks }}</span> @endif
                                        @if($res->viva_marks !== null) <span style="margin-left:4px">ভাইভা: {{ $res->viva_marks }}</span> @endif
                                    </div>
                                @endif
                            </td>
                            <td><strong>{{ $res->grade ?? '—' }}</strong></td>
                            <td><span class="badge badge-{{ strtolower($res->status) }}">{{ ucfirst(strtolower($res->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--text-muted)">Results have not been entered by teacher yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
</x-admin-layout>
