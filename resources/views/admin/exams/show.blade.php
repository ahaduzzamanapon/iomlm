<x-admin-layout>
    <x-slot name="title">{{ $exam->title }} — Details</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.exams.index') }}">← Back to Exams</a>
            </div>
            <h1>{{ $exam->title }}</h1>
            <p>Subject: {{ $exam->subject->name ?? '—' }} · Date: {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }} · Marks: {{ $exam->full_marks }} (Pass: {{ $exam->pass_marks }})</p>
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
                            <td>{{ $res->marks }}/{{ $exam->full_marks }}</td>
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
        <div class="card-header">
            <span class="card-title"><i class="fa-solid fa-file-signature" style="color:#6366f1"></i> অনলাইন পরীক্ষার খাতা ও পুনরায় সুযোগ (Submissions & Retake Reset)</span>
            <span class="badge badge-primary no-dot">{{ $exam->submissions->count() }} Submissions</span>
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
                        <td><strong style="color:#4338ca;font-size:15px">{{ number_format($sub->total_score, 1) }}</strong></td>
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
</x-admin-layout>
