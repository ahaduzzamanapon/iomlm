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
                        <th>Scheduled Date & Time</th>
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
                        <td>
                            @if($c->timeline->scheduled_date)
                                <strong>{{ \Carbon\Carbon::parse($c->timeline->scheduled_date)->format('d M Y') }}</strong>
                                @if($c->start_time)
                                    <br><span style="font-size:11px;color:var(--blue);font-weight:600">🕒 {{ \Carbon\Carbon::parse($c->start_time)->format('h:i A') }}</span>
                                @endif
                            @else
                                <span class="badge badge-upcoming no-dot">Upcoming (Date Pending)</span>
                            @endif
                        </td>
                        <td>
                            @if($c->timeline->scheduled_date)
                                <span class="badge badge-{{ strtolower($c->status) }}">{{ ucfirst(strtolower($c->status)) }}</span>
                            @else
                                <span class="badge badge-upcoming">Upcoming</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            @if($c->timeline->scheduled_date && $c->meeting_link && ($c->status === 'SCHEDULED' || $c->status === 'RUNNING'))
                                <a href="{{ $c->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">🎥 Join Live Class</a>
                            @elseif(!$c->timeline->scheduled_date)
                                <span class="badge badge-upcoming no-dot">Awaiting Date</span>
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
