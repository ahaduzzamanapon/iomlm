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
</x-admin-layout>
