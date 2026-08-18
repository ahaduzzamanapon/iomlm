<x-teacher-layout>
    <x-slot name="title">Student Record — {{ $student->name }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.students.index') }}">← Back to Students</a>
            </div>
            <h1>Student Profile: {{ $student->name }}</h1>
            <p>Student Code: {{ $student->student_code ?? 'N/A' }} · Phone: {{ $student->phone }}</p>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-header"><span class="card-title">Attendance Audit</span></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Subject</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($student->attendances as $att)
                        <tr>
                            <td>{{ $att->classSession?->subject?->name ?? $att->classSession?->timeline?->subject?->name ?? '—' }}</td>
                            <td><span class="badge badge-{{ strtolower($att->status) }}">{{ ucfirst(strtolower($att->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="text-align:center;padding:20px;color:var(--text-muted)">No attendance logs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Exam Results</span></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Subject</th><th>Marks</th><th>Grade</th></tr></thead>
                    <tbody>
                        @forelse($student->results as $res)
                        <tr>
                            <td>{{ $res->exam->subject->name ?? '—' }}</td>
                            <td>{{ $res->marks }}</td>
                            <td><span class="badge badge-active no-dot">{{ $res->grade ?? 'A' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">No exam results.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-teacher-layout>
