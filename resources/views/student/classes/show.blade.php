<x-student-layout>
    <x-slot name="title">Class Details</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('student.classes.index') }}">← Back to My Classes</a>
            </div>
            <h1>{{ $class->subject?->name ?? 'Class Details' }}</h1>
            <p>{{ $class->session_date?->format('l, d M Y') ?? 'TBA' }} · {{ $class->batch?->name ?? '' }}</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Session Details</span>
                @php $badge = match($class->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-secondary' }; @endphp
                <span class="badge {{ $badge }} no-dot">{{ $class->status }}</span>
            </div>
            <div style="padding:20px">
                <table style="width:100%;border-collapse:collapse">
                    <tr><th style="text-align:left;padding:8px 0;color:var(--text-muted);font-weight:500;width:140px">Subject</th><td style="padding:8px 0;font-weight:600">{{ $class->subject?->name ?? '—' }}</td></tr>
                    <tr><th style="text-align:left;padding:8px 0;color:var(--text-muted);font-weight:500">Batch</th><td style="padding:8px 0">{{ $class->batch?->name ?? '—' }}</td></tr>
                    <tr><th style="text-align:left;padding:8px 0;color:var(--text-muted);font-weight:500">Date</th><td style="padding:8px 0">{{ $class->session_date?->format('D, d M Y') ?? 'TBA' }}</td></tr>
                    <tr><th style="text-align:left;padding:8px 0;color:var(--text-muted);font-weight:500">Time</th><td style="padding:8px 0">{{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('h:i A') : '—' }} · {{ $class->routineEntry?->slot?->name ?? '' }}</td></tr>
                    <tr><th style="text-align:left;padding:8px 0;color:var(--text-muted);font-weight:500">Teacher</th><td style="padding:8px 0">{{ $class->teacher?->name ?? '—' }}</td></tr>
                    <tr><th style="text-align:left;padding:8px 0;color:var(--text-muted);font-weight:500">Module Covered</th><td style="padding:8px 0">{{ $class->moduleCovered?->title ?? '—' }}</td></tr>
                    @if($class->notes)
                    <tr><th style="text-align:left;padding:8px 0;color:var(--text-muted);font-weight:500">Notes</th><td style="padding:8px 0">{{ $class->notes }}</td></tr>
                    @endif
                </table>

                @if($class->meeting_link && $class->status === 'SCHEDULED')
                <div style="margin-top:20px">
                    <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-primary">
                        🎥 Join Live Class
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">My Attendance</span></div>
            <div style="padding:20px;text-align:center">
                @if($attendance)
                    @php
                        $color = match($attendance->status) { 'PRESENT'=>'#10b981','ABSENT'=>'#ef4444','LATE'=>'#f59e0b','EXCUSED'=>'#3b82f6',default=>'#6b7280' };
                        $icon  = match($attendance->status) { 'PRESENT'=>'✅','ABSENT'=>'❌','LATE'=>'⏰','EXCUSED'=>'📋',default=>'—' };
                    @endphp
                    <div style="font-size:48px;margin-bottom:8px">{{ $icon }}</div>
                    <div style="font-size:18px;font-weight:700;color:{{ $color }}">{{ $attendance->status }}</div>
                @else
                    <div style="font-size:40px;margin-bottom:8px">📋</div>
                    <div style="color:var(--text-muted)">
                        @if($class->status === 'COMPLETED') Attendance not recorded @else Pending @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-student-layout>
