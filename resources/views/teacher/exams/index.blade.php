<x-teacher-layout>
    <x-slot name="title">Exams & Evaluations</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Subject Examinations</h1>
            <p>View scheduled exams and candidate attendees for your assigned subjects</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Title</th>
                        <th>Exam Date</th>
                        <th>Registered Candidates</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                    <tr>
                        <td class="td-primary"><strong>{{ $exam->subject->name ?? '—' }}</strong></td>
                        <td>{{ $exam->title }}</td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}</td>
                        <td><span class="badge badge-secondary no-dot">{{ $exam->attendees->count() }} Candidates</span></td>
                        <td><span class="badge badge-{{ strtolower($exam->status) }}">{{ ucfirst(strtolower($exam->status)) }}</span></td>
                        <td style="text-align:right">
                            <a href="{{ route('teacher.results.enter', $exam) }}" class="btn btn-primary btn-sm">Enter Results →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No exams found for your assigned subjects.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-teacher-layout>
