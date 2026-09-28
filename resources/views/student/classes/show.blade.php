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

                @if($class->meeting_link && in_array($class->status, ['SCHEDULED', 'RUNNING']))
                <div style="margin-top:20px">
                    <a href="{{ route('student.classes.join', $class) }}" target="_blank" class="btn btn-primary">
                        Join Live Class
                    </a>
                </div>
                @endif

                @if($class->has_recorded_videos)
                <div style="margin-top:20px;background:#eff6ff;padding:14px 16px;border-radius:10px;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;font-family:'Kalpurush',sans-serif">
                    <div>
                        <div style="font-weight:700;color:#1e40af;font-size:14px;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-circle-play" style="color:#2563eb"></i> এই ক্লাসের রেকর্ডিং ভিডিও উপলব্ধ আছে
                        </div>
                        <div style="font-size:12px;color:#3b82f6;margin-top:2px">
                            মোট {{ $class->video_count }} টি ভিডিও পার্ট সংযুক্ত রয়েছে।
                        </div>
                    </div>
                    <a href="{{ route('student.recordings.show', $class) }}" class="btn btn-primary btn-sm" style="font-weight:800;padding:8px 18px;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-play"></i> রেকর্ড ভিডিও দেখুন →
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
                        $icon  = match($attendance->status) { 'PRESENT'=>'','ABSENT'=>'','LATE'=>'','EXCUSED'=>'',default=>'—' };
                    @endphp
                    <div style="font-size:48px;margin-bottom:8px">{{ $icon }}</div>
                    <div style="font-size:18px;font-weight:700;color:{{ $color }}">{{ $attendance->status }}</div>
                @else
                    <div style="font-size:40px;margin-bottom:8px"></div>
                    <div style="color:var(--text-muted)">
                        @if($class->status === 'COMPLETED') Attendance not recorded @else Pending @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-student-layout>
