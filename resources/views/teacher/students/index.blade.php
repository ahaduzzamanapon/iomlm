<x-teacher-layout>
    <x-slot name="title">My Students</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Enrolled Students Roster</h1>
            <p>View active students enrolled in your teaching subjects and batches</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student Code</th>
                        <th>Student Name</th>
                        <th>Phone</th>
                        <th>Enrolled Course</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $st)
                    <tr>
                        <td><span class="badge badge-active no-dot"><strong>{{ $st->student_code ?? 'N/A' }}</strong></span></td>
                        <td class="td-primary"><strong>{{ $st->name }}</strong></td>
                        <td class="td-muted">{{ $st->phone }}</td>
                        <td>{{ $st->enrollments->first()->batch->course->name ?? '—' }}</td>
                        <td style="text-align:right">
                            <a href="{{ route('teacher.students.show', $st) }}" class="btn btn-outline btn-sm">View Academic Record →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No active students in your class rosters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-teacher-layout>
