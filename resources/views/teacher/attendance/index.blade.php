<x-teacher-layout>
    <x-slot name="title">Attendance Management</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Student Attendance Logs</h1>
            <p>Recorded attendance per class session</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Batch</th>
                        <th>Class Date</th>
                        <th>Slot</th>
                        <th>Students Present</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $c)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $c->subject?->name ?? '—' }}</strong><br>
                            <span class="td-muted">{{ $c->subject?->code }}</span>
                        </td>
                        <td class="td-muted">{{ $c->batch?->name ?? '—' }}</td>
                        <td class="td-muted">
                            {{ $c->session_date?->format('d M Y (D)') ?? 'TBA' }}
                        </td>
                        <td class="td-muted" style="font-size:11px">
                            {{ $c->routineEntry?->slot?->name ?? '—' }}
                            @if($c->start_time)
                                <br><small>{{ \Carbon\Carbon::parse($c->start_time)->format('h:i A') }}</small>
                            @endif
                        </td>
                        <td>
                            @php $badge = $c->status === 'COMPLETED' ? 'badge-success' : 'badge-info'; @endphp
                            <span class="badge {{ $badge }} no-dot">
                                {{ $c->attendances->where('status', 'PRESENT')->count() }} / {{ $c->attendances->count() }} Present
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('teacher.attendance.mark', $c) }}" class="btn btn-outline btn-sm">Mark / Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No conducted classes with attendance logs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-teacher-layout>
