<x-student-layout>
    <x-slot name="title">My Timeline</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Learning Timeline</h1>
            <p>Track your module-by-module academic progress and attendance record</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Module Sequence Progress</span>
            <span class="badge badge-secondary no-dot">{{ $timelines->count() }} Modules</span>
        </div>
        <div style="padding:20px">
            <div class="timeline">
                @forelse($timelines as $tl)
                @php
                    $isCompleted = $tl->status === 'COMPLETED';
                    $isCancelled = $tl->status === 'CANCELLED';
                @endphp
                <div class="timeline-item">
                    <div class="timeline-dot {{ $isCompleted ? 'completed' : ($isCancelled ? 'cancelled' : 'pending') }}">
                        {{ $tl->module->sequence_no ?? 1 }}
                    </div>
                    <div class="timeline-content">
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <div>
                                <strong style="font-size:14px;color:var(--text-primary)">{{ $tl->module->title ?? '—' }}</strong><br>
                                <span style="font-size:12px;color:var(--text-muted)">Subject: <strong>{{ $tl->subject->name ?? '—' }}</strong> · Scheduled Date: {{ \Carbon\Carbon::parse($tl->scheduled_date)->format('d M Y') }}</span>
                            </div>
                            <div>
                                @if($tl->status === 'COMPLETED')
                                    <span class="badge badge-completed">Completed (Attended)</span>
                                @elseif($tl->status === 'CANCELLED')
                                    <span class="badge badge-cancelled">Cancelled (Teacher Absent / Rescheduled)</span>
                                @else
                                    <span class="badge badge-scheduled">Scheduled</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <p>No module timelines assigned to your active course enrollment yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-student-layout>
