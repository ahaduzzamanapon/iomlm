<x-teacher-layout>
    <x-slot name="title">Attendance Management</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Student Attendance Logs</h1>
            <p>View recorded student attendance for your conducted class sessions</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject & Module</th>
                        <th>Class Date</th>
                        <th>Students Present</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $c)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $c->timeline->subject->name ?? '—' }}</strong><br>
                            <span class="td-muted">Module: {{ $c->timeline->module->title ?? '—' }}</span>
                        </td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($c->timeline->scheduled_date)->format('d M Y') }}</td>
                        <td>
                            <span class="badge badge-active no-dot">
                                {{ $c->attendances->where('status', 'PRESENT')->count() }} / {{ $c->attendances->count() }} Present
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('teacher.classes.conduct', $c) }}" class="btn btn-outline btn-sm">Edit Attendance</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">No conducted classes with attendance logs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-teacher-layout>
