<x-student-layout>
    <x-slot name="title">My Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Live & Scheduled Classes</h1>
            <p>Join virtual classrooms via Google Meet / Zoom</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject & Module</th>
                        <th>Teacher</th>
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
                            <span class="td-muted">Module: {{ $c->timeline->module->title ?? '—' }}</span>
                        </td>
                        <td>{{ $c->teacher->name ?? 'Faculty' }}</td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($c->timeline->scheduled_date)->format('d M Y') }}</td>
                        <td><span class="badge badge-{{ strtolower($c->status) }}">{{ ucfirst(strtolower($c->status)) }}</span></td>
                        <td style="text-align:right">
                            @if($c->meeting_link && ($c->status === 'SCHEDULED' || $c->status === 'RUNNING'))
                                <a href="{{ $c->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">🎥 Join Live Class</a>
                            @else
                                <span class="td-muted">Class Session Ended</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No class sessions scheduled for your enrolled batches.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
