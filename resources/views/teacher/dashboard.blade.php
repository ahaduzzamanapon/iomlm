<x-teacher-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->name ?? 'Teacher' }}!</h1>
            <p>{{ now()->format('l, d M Y') }} — Here's your teaching overview</p>
        </div>
    </div>

    <!-- Teacher Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon teal">
                <i class="fa-solid fa-video"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['today_classes'] }}</div>
                <div class="stat-label">Today's Classes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['attendance_todo'] }}</div>
                <div class="stat-label">Attendance Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_subjects'] }}</div>
                <div class="stat-label">Subjects Assigned</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fa-solid fa-square-poll-vertical"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['pending_results'] }}</div>
                <div class="stat-label">Results Pending</div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Today's Classes -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-calendar-day" style="color:var(--primary)"></i> Today's Classes</span>
                <a href="{{ route('teacher.classes.today') }}" class="btn btn-ghost btn-sm">View All Today →</a>
            </div>
            <div style="padding:0">
                @forelse($todayClasses as $cs)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--card-border)">
                    <div style="width:36px;height:36px;background:{{ $cs->routineEntry?->color ?? '#3b82f6' }}22;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid {{ $cs->routineEntry?->color ?? '#3b82f6' }}">
                        <i class="fa-solid fa-video" style="font-size:14px;color:{{ $cs->routineEntry?->color ?? '#3b82f6' }}"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cs->subject?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            {{ $cs->batch?->name ?? '' }}
                            @if($cs->routineEntry?->slot) · {{ $cs->routineEntry->slot->name }} @endif
                            @if($cs->start_time) · {{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }} @endif
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;align-items:flex-end">
                        @if($cs->meeting_link)
                            <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-primary btn-sm" style="font-size:11px"><i class="fa-solid fa-video"></i> Join</a>
                        @else
                            <form method="POST" action="{{ route('teacher.classes.setLink', $cs) }}">
                                @csrf
                                <button class="btn btn-outline btn-sm" style="font-size:11px;color:#f59e0b"><i class="fa-solid fa-bolt"></i> Generate Link</button>
                            </form>
                        @endif
                        @if($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                            <a href="{{ route('teacher.classes.conduct', $cs) }}" class="btn btn-ghost btn-sm" style="font-size:11px">Conduct →</a>
                        @else
                            <span class="badge badge-success no-dot" style="font-size:9px">DONE</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-state"><p>No classes today</p></div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Exams -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Upcoming Exams</span>
                <a href="{{ route('teacher.exams.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Subject</th><th>Type</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($upcomingExams as $exam)
                        <tr>
                            <td class="td-primary">{{ $exam->subject->name ?? '—' }}</td>
                            <td><span class="badge badge-secondary no-dot">{{ ucfirst(strtolower($exam->type)) }}</span></td>
                            <td class="td-muted">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M') }}</td>
                            <td><span class="badge badge-scheduled">Scheduled</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--text-muted)">No upcoming exams</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance Pending -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b"></i> Attendance Not Marked</span>
                <span class="badge badge-pending no-dot" style="font-size:11px">Action Required</span>
            </div>
            <div style="padding:0">
                @forelse($attendancePending as $cs)
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--card-border)">
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $cs->subject?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            {{ $cs->batch?->name ?? '' }}
                            @if($cs->session_date) · {{ $cs->session_date->format('d M') }} @endif
                        </div>
                    </div>
                    <a href="{{ route('teacher.attendance.mark', $cs) }}" class="btn btn-outline btn-sm">Mark Now</a>
                </div>
                @empty
                <div class="empty-state"><p>All attendance marked <i class="fa-solid fa-circle-check" style="color:#10b981"></i></p></div>
                @endforelse
            </div>
        </div>

        <!-- Results to Submit -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Results to Submit</span>
                <a href="{{ route('teacher.results.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div style="padding:0">
                @forelse($pendingResults as $exam)
                <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--card-border)">
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $exam->subject->name ?? '—' }} — {{ $exam->title }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}</div>
                    </div>
                    <a href="{{ route('teacher.results.enter', $exam) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i> Enter Marks</a>
                </div>
                @empty
                <div class="empty-state"><p>No pending results</p></div>
                @endforelse
            </div>
        </div>
    </div>

</x-teacher-layout>
