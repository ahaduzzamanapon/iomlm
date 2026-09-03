<x-student-layout>
    <x-slot name="title">Online Exams</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Online Examinations</h1>
            <p>Class Quizzes, Class Tests, Half-Term, and Final Examinations</p>
        </div>
    </div>

    @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ℹ️ {{ session('info') }}
        </div>
    @endif

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Exam Title &amp; Subject</th>
                        <th>Type</th>
                        <th>Full Marks / Pass</th>
                        <th>Duration / Negative Mark</th>
                        <th>Schedule</th>
                        <th style="text-align:center">Action / Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $ex)
                    @php
                        $submission = $ex->submissions->first();
                        $isSubmitted = $submission && in_array($submission->status, ['SUBMITTED', 'AUTO_SUBMITTED_VIOLATION']);
                        $typeBadge = match($ex->type) {
                            'QUIZ' => 'badge-secondary',
                            'CLASS_TEST' => 'badge-info',
                            'HALF_TERM' => 'badge-warning',
                            'FINAL' => 'badge-danger',
                            default => 'badge-primary'
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $ex->title }}</strong><br>
                            <span style="color:#64748b;font-size:12px">{{ $ex->subject?->name ?? '—' }} ({{ $ex->subject?->code }})</span>
                        </td>
                        <td><span class="badge {{ $typeBadge }} no-dot">{{ $ex->type }}</span></td>
                        <td>
                            <strong>{{ $ex->full_marks }} Marks</strong><br>
                            <small style="color:#64748b">Pass: {{ $ex->pass_marks }}</small>
                        </td>
                        <td>
                            ⏱️ {{ $ex->duration_minutes }} mins<br>
                            @if($ex->negative_marking > 0)
                                <small style="color:#e11d48">Negative: -{{ $ex->negative_marking }} per wrong</small>
                            @else
                                <small style="color:#10b981">No Negative Mark</small>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:12px">
                            {{ \Carbon\Carbon::parse($ex->exam_date)->format('d M Y') }}
                            @if($ex->start_datetime)
                                <br><small>{{ \Carbon\Carbon::parse($ex->start_datetime)->format('h:i A') }}</small>
                            @endif
                        </td>
                        <td style="text-align:center">
                            @if($isSubmitted)
                                <a href="{{ route('student.exams.result', [$ex, $submission]) }}" class="btn btn-outline btn-sm" style="color:#10b981">
                                    View Result ({{ $submission->total_score }})
                                </a>
                            @else
                                <a href="{{ route('student.exams.take', $ex) }}" class="btn btn-primary btn-sm">
                                    Start Exam
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">
                            No scheduled online exams found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
