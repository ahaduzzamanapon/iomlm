<x-teacher-layout>
    <x-slot name="title">My Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Class Schedule & Execution</h1>
            <p>Conduct classes, mark attendance, and upload learning resources</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject & Module</th>
                        <th>Batch</th>
                        <th>Scheduled Date</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $c)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $c->timeline->subject->name ?? '—' }}</strong><br>
                            <span class="td-muted">Module #{{ $c->timeline->module->sequence_no ?? 1 }}: {{ $c->timeline->module->title ?? '—' }}</span>
                        </td>
                        <td><span class="badge badge-secondary no-dot">{{ $c->timeline->batch->name ?? '—' }}</span></td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($c->timeline->scheduled_date)->format('d M Y') }}</td>
                        <td><span class="badge badge-{{ strtolower($c->status) }}">{{ ucfirst(strtolower($c->status)) }}</span></td>
                        <td style="text-align:right">
                            @if($c->status === 'SCHEDULED' || $c->status === 'RUNNING')
                                <a href="{{ route('teacher.classes.conduct', $c) }}" class="btn btn-primary btn-sm">▶ Conduct Class & Mark Attendance</a>
                            @else
                                <span class="td-muted">Class Completed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No class sessions scheduled for your assigned subjects.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-teacher-layout>
