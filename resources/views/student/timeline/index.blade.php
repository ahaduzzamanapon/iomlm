<x-student-layout>
    <x-slot name="title">My Timeline</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>📅 My Class Timeline</h1>
            <p>All scheduled and completed class sessions for your enrolled courses</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Session History & Schedule</span>
            <span class="badge badge-secondary no-dot">{{ $sessions->count() }} Sessions</span>
        </div>
        <div style="padding:20px">
            <div class="timeline">
                @forelse($sessions as $cs)
                @php
                    $attStatus  = $attendanceMap[$cs->id] ?? null;
                    $isCompleted = $cs->status === 'COMPLETED';
                    $isCancelled = $cs->status === 'CANCELLED';
                    $isToday     = $cs->session_date?->isToday();
                    $dotClass    = $isCompleted ? 'completed' : ($isCancelled ? 'cancelled' : ($isToday ? 'today' : 'pending'));
                @endphp
                <div class="timeline-item">
                    <div class="timeline-dot {{ $dotClass }}">
                        @if($isCompleted && $attStatus === 'PRESENT') ✓
                        @elseif($isCancelled) ✕
                        @else {{ $loop->iteration }}
                        @endif
                    </div>
                    <div class="timeline-content">
                        <div style="display:flex;align-items:start;justify-content:space-between;flex-wrap:wrap;gap:8px">
                            <div>
                                <strong style="font-size:14px;color:var(--text-primary)">
                                    {{ $cs->subject?->name ?? '—' }}
                                </strong>
                                @if($cs->moduleCovered)
                                    <span style="font-size:11px;color:#6366f1;background:#eef2ff;padding:1px 6px;border-radius:4px;margin-left:6px">
                                        {{ $cs->moduleCovered->title }}
                                    </span>
                                @endif
                                <br>
                                <span style="font-size:12px;color:var(--text-muted)">
                                    {{ $cs->session_date?->format('D, d M Y') ?? 'TBA' }}
                                    @if($cs->start_time) · {{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }} @endif
                                    · {{ $cs->batch?->name ?? '—' }}
                                </span>
                            </div>
                            <div style="display:flex;gap:6px;align-items:center">
                                @if($isToday)
                                    <span class="badge badge-warning no-dot">TODAY</span>
                                @endif
                                @if($attStatus)
                                    @php
                                        $attBadge = match($attStatus) {
                                            'PRESENT'  => 'badge-success',
                                            'ABSENT'   => 'badge-danger',
                                            'LATE'     => 'badge-warning',
                                            'EXCUSED'  => 'badge-info',
                                            default    => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $attBadge }} no-dot">{{ $attStatus }}</span>
                                @endif
                                @if($cs->status === 'COMPLETED')
                                    <span class="badge badge-success no-dot">Completed</span>
                                @elseif($cs->status === 'CANCELLED')
                                    <span class="badge badge-danger no-dot">Cancelled</span>
                                @elseif($cs->meeting_link)
                                    <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-sm btn-primary" style="font-size:11px">🎥 Join</a>
                                @else
                                    <span class="badge badge-info no-dot">Scheduled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <p>No class sessions found for your active enrollments.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-student-layout>
