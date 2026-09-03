<x-student-layout>
    <x-slot name="title">My Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Classes</h1>
            <p>All scheduled sessions for your enrolled batches</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Batch</th>
                        <th>Date & Time</th>
                        <th>Slot</th>
                        <th>Teacher</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $c)
                    @php $isToday = $c->session_date?->isToday(); @endphp
                    <tr style="{{ $isToday ? 'background:#eff6ff;' : '' }}">
                        <td class="td-primary">
                            <strong>{{ $c->subject?->name ?? '—' }}</strong>
                            @if($c->moduleCovered)
                                <br><span class="td-muted">{{ $c->moduleCovered->title }}</span>
                            @endif
                        </td>
                        <td class="td-muted">{{ $c->batch?->name ?? '—' }}</td>
                        <td>
                            @if($c->session_date)
                                <strong style="font-size:12px">{{ $c->session_date->format('d M Y (D)') }}</strong>
                                @if($isToday)<br><span class="badge badge-success no-dot" style="font-size:9px">TODAY</span>@endif
                                @if($c->start_time)
                                    <br><span style="font-size:11px;color:#3b82f6;font-weight:600">{{ \Carbon\Carbon::parse($c->start_time)->format('h:i A') }}</span>
                                @endif
                            @else
                                <span class="badge badge-secondary no-dot">Date TBA</span>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:11px">{{ $c->routineEntry?->slot?->name ?? '—' }}</td>
                        <td class="td-muted">{{ $c->teacher?->name ?? '—' }}</td>
                        <td>
                            @php $badge = match($c->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                            <span class="badge {{ $badge }} no-dot">{{ $c->status }}</span>
                        </td>
                        <td style="text-align:right">
                            @if($c->meeting_link && in_array($c->status, ['SCHEDULED','RUNNING']))
                                <a href="{{ $c->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">Join</a>
                            @elseif($c->status === 'COMPLETED')
                                <a href="{{ route('student.classes.show', $c) }}" class="btn btn-ghost btn-sm">View →</a>
                            @else
                                <span class="td-muted" style="font-size:11px">No link yet</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No class sessions found for your enrolled batches.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
