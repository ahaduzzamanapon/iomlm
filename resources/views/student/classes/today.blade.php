<x-student-layout>
    <x-slot name="title">Today's Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Today's Classes</h1>
            <p>{{ \Carbon\Carbon::parse($today)->format('l, d F Y') }} — Your scheduled sessions for today</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('student.classes.index') }}" class="btn btn-ghost btn-sm">All Classes →</a>
        </div>
    </div>

    @if($sessions->isEmpty())
        <div class="card" style="padding:60px;text-align:center">
            <div style="font-size:48px;margin-bottom:12px"></div>
            <div style="font-size:18px;font-weight:600;color:#1e293b;margin-bottom:6px">No classes today!</div>
            <p style="color:var(--text-muted)">You have no scheduled sessions for {{ \Carbon\Carbon::parse($today)->format('l, d M Y') }}.</p>
            <a href="{{ route('student.calendar') }}" class="btn btn-outline" style="margin-top:16px">View Calendar</a>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:14px">
            @foreach($sessions as $cs)
            @php
                $statusColor = match($cs->status) {
                    'COMPLETED' => '#10b981',
                    'CANCELLED' => '#ef4444',
                    'RUNNING'   => '#f59e0b',
                    default     => '#3b82f6',
                };
            @endphp
            <div class="card" style="border-left:4px solid {{ $statusColor }};padding:0">
                <div style="display:flex;align-items:stretch;gap:0">
                    {{-- Time column --}}
                    <div style="min-width:90px;background:{{ $statusColor }}11;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px 12px;border-right:1px solid {{ $statusColor }}33">
                        @if($cs->start_time)
                            <div style="font-size:18px;font-weight:700;color:{{ $statusColor }}">{{ \Carbon\Carbon::parse($cs->start_time)->format('h:i') }}</div>
                            <div style="font-size:10px;color:{{ $statusColor }};font-weight:600">{{ \Carbon\Carbon::parse($cs->start_time)->format('A') }}</div>
                        @else
                            <div style="font-size:11px;color:var(--text-muted)">TBA</div>
                        @endif
                        @if($cs->routineEntry?->slot)
                            <div style="font-size:9px;color:var(--text-muted);margin-top:4px;text-align:center">{{ $cs->routineEntry->slot->name }}</div>
                        @endif
                    </div>

                    {{-- Info column --}}
                    <div style="flex:1;padding:16px 20px">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
                            <div>
                                <div style="font-size:16px;font-weight:700;color:#1e293b">{{ $cs->subject?->name ?? '—' }}</div>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:3px">
                                    {{ $cs->batch?->name ?? '' }}
                                    @if($cs->teacher) · {{ $cs->teacher->name }} @endif
                                </div>
                                @if($cs->moduleCovered)
                                    <div style="font-size:11px;color:#6366f1;margin-top:4px">{{ $cs->moduleCovered->title }}</div>
                                @endif
                            </div>
                            @php $badge = match($cs->status) { 'COMPLETED'=>'badge-success','CANCELLED'=>'badge-danger','RUNNING'=>'badge-warning',default=>'badge-info' }; @endphp
                            <span class="badge {{ $badge }} no-dot" style="font-size:11px">{{ $cs->status }}</span>
                        </div>

                        {{-- Action row --}}
                        <div style="display:flex;align-items:center;gap:10px;margin-top:14px;flex-wrap:wrap">
                            @if($cs->meeting_link && in_array($cs->status, ['SCHEDULED','RUNNING']))
                                <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">
                                    Join Live Class
                                </a>
                            @elseif($cs->status === 'SCHEDULED')
                                <span class="badge badge-secondary no-dot" style="font-size:11px">⏳ Awaiting link from teacher</span>
                            @elseif($cs->status === 'COMPLETED')
                                <span class="badge badge-success no-dot" style="font-size:11px">Class completed</span>
                            @elseif($cs->status === 'CANCELLED')
                                <span class="badge badge-danger no-dot" style="font-size:11px">Cancelled</span>
                            @endif
                            <a href="{{ route('student.classes.show', $cs) }}" class="btn btn-ghost btn-sm" style="font-size:11px">Details →</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</x-student-layout>
