<x-teacher-layout>
    <x-slot name="title">My Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Classes</h1>
            <p>Routine-based weekly schedule — add meeting links & take attendance</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('teacher.classes.today') }}" class="btn btn-primary btn-sm">Today's Classes</a>
        </div>
    </div>

    @if($sessions->isEmpty())
        <div class="card" style="padding:40px;text-align:center;color:var(--text-muted)">
            <p>No classes assigned to you yet. Contact admin to set up your routine.</p>
        </div>
    @else
    @php $today = \Carbon\Carbon::today()->toDateString(); @endphp
    @foreach($sessions as $weekKey => $weekSessions)
    @php
        $firstDate = $weekSessions->first()->session_date;
        $weekLabel = $firstDate ? 'Week of ' . $firstDate->startOfWeek()->format('d M Y') : 'Unscheduled';
    @endphp
    <div class="card" style="margin-bottom:16px">
        <div class="card-header" style="background:#f8fafc">
            <span class="card-title" style="font-size:13px;color:#64748b">{{ $weekLabel }}</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Batch</th>
                        <th>Slot</th>
                        <th>Meeting Link</th>
                        <th>Module Covered</th>
                        <th>Attendance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weekSessions->sortBy('session_date') as $cs)
                    @php
                        $isToday   = $cs->session_date?->toDateString() === $today;
                        $attended  = $cs->attendances->where('status','PRESENT')->count();
                        $atTotal   = $cs->attendances->count();
                    @endphp
                    <tr style="{{ $isToday ? 'background:#eff6ff;' : '' }}">
                        <td>
                            <strong style="font-size:12px">{{ $cs->session_date?->format('d M (D)') ?? 'TBA' }}</strong>
                            @if($isToday)<div><span class="badge badge-success no-dot" style="font-size:9px">TODAY</span></div>@endif
                        </td>
                        <td class="td-primary">
                            {{ $cs->subject?->name ?? '—' }}
                            @if($cs->teacher)<div style="font-size:11px;color:var(--text-muted)">{{ $cs->teacher->name }}</div>@endif
                        </td>
                        <td class="td-muted" style="font-size:11px">{{ $cs->batch?->name ?? '—' }}</td>
                        <td class="td-muted" style="font-size:11px">
                            {{ $cs->routineEntry?->slot?->name ?? '—' }}
                            @if($cs->start_time)<br><small>{{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }}</small>@endif
                        </td>
                        <td>
                            @if($cs->meeting_link)
                                <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-sm btn-outline" style="font-size:11px">Join</a>
                            @elseif($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                                @if($meetingProvider === 'zoom')
                                    <form method="POST" action="{{ route('teacher.classes.setLink', $cs) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline" style="font-size:11px;color:#2563eb">Zoom</button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-outline" style="font-size:11px;color:#f59e0b"
                                        onclick="document.getElementById('lf{{$cs->id}}').style.display='flex'">
                                        Add Link
                                    </button>
                                    <form id="lf{{$cs->id}}" method="POST"
                                        action="{{ route('teacher.classes.setLink', $cs) }}"
                                        style="display:none;gap:4px;align-items:center;margin-top:4px">
                                        @csrf
                                        <input type="url" name="meeting_link" class="form-control"
                                            style="font-size:11px;min-width:180px"
                                            placeholder="{{ $meetingProvider === 'google_meet' ? 'meet.google.com/…' : 'Meeting URL…' }}"
                                            required>
                                        <button type="submit" class="btn btn-primary btn-sm" style="font-size:10px">Save</button>
                                    </form>
                                @endif
                            @else
                                <span style="color:#d1d5db;font-size:11px">—</span>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:11px">{{ $cs->moduleCovered?->title ?? '—' }}</td>
                        <td style="text-align:center">
                            @if($atTotal > 0)
                                <span style="font-weight:700;font-size:12px;color:{{ $attended==$atTotal?'#10b981':'#f59e0b' }}">{{ $attended }}/{{ $atTotal }}</span>
                            @else
                                <span style="color:#d1d5db">—</span>
                            @endif
                        </td>
                        <td>
                            @php $badge = match($cs->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                            <span class="badge {{ $badge }} no-dot">{{ $cs->status }}</span>
                        </td>
                        <td>
                            @if($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                                <a href="{{ route('teacher.classes.conduct', $cs) }}" class="btn btn-sm btn-primary" style="font-size:11px">Conduct →</a>
                            @elseif($cs->status === 'COMPLETED')
                                <a href="{{ route('teacher.attendance.mark', $cs) }}" class="btn btn-sm btn-ghost" style="font-size:11px">Attendance</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
    @endif
</x-teacher-layout>
