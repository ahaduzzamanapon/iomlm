<x-student-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Welcome, {{ auth()->user()->name ?? 'Student' }}!</h1>
            <p>{{ now()->format('l, d M Y') }} — Your learning overview</p>
        </div>
    </div>

    <!-- Student Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon violet">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['enrolled_courses'] }}</div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['upcoming_classes'] }}</div>
                <div class="stat-label">Upcoming Classes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['attendance_percent'] }}%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['upcoming_exams'] }}</div>
                <div class="stat-label">Upcoming Exams</div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- My Current Modules (Timeline) -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">My Learning Timeline</span>
                <a href="{{ route('student.timeline') }}" class="btn btn-ghost btn-sm">Full View</a>
            </div>
            <div class="module-list" style="padding:16px">
                @forelse($currentModules as $timeline)
                <div class="module-item">
                    <div class="module-seq">{{ $loop->iteration }}</div>
                    <div class="module-info">
                        <div class="module-title">{{ $timeline->module->title ?? '—' }}</div>
                        <div class="module-sub">{{ $timeline->subject->name ?? '—' }} · {{ $timeline->scheduled_date ? \Carbon\Carbon::parse($timeline->scheduled_date)->format('d M') : 'TBD' }}</div>
                    </div>
                    <span class="badge badge-{{ strtolower($timeline->status) }}">{{ ucfirst(strtolower($timeline->status)) }}</span>
                </div>
                @empty
                <div class="empty-state"><p>No active modules</p></div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Upcoming Classes</span>
                <a href="{{ route('student.classes.index') }}" class="btn btn-ghost btn-sm">All Classes</a>
            </div>
            <div style="padding:0">
                @forelse($upcomingClasses as $class)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--card-border)">
                    <div style="width:40px;height:40px;background:#f5f3ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="#8b5cf6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $class->timeline->subject->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $class->timeline->module->title ?? '—' }} · Teacher: {{ $class->teacher->name ?? '—' }}</div>
                    </div>
                    @if($class->meeting_link)
                    <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">Join</a>
                    @endif
                </div>
                @empty
                <div class="empty-state"><p>No upcoming classes</p></div>
                @endforelse
            </div>
        </div>

        <!-- Recent Results -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Results</span>
                <a href="{{ route('student.results.index') }}" class="btn btn-ghost btn-sm">All Results</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Subject</th><th>Marks</th><th>Grade</th><th>Result</th></tr></thead>
                    <tbody>
                        @forelse($recentResults as $result)
                        <tr>
                            <td class="td-primary">{{ $result->exam->subject->name ?? '—' }}</td>
                            <td>{{ $result->marks ?? '—' }}/{{ $result->exam->full_marks ?? '—' }}</td>
                            <td><strong>{{ $result->grade ?? '—' }}</strong></td>
                            <td><span class="badge badge-{{ strtolower($result->status ?? 'secondary') }}">{{ ucfirst(strtolower($result->status ?? 'N/A')) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--text-muted)">No results yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Exams -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Upcoming Exams</span>
                <a href="{{ route('student.exams.index') }}" class="btn btn-ghost btn-sm">View All</a>
            </div>
            <div style="padding:0">
                @forelse($upcomingExamsList as $exam)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--card-border)">
                    <div style="width:40px;height:40px;background:#fff7ed;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;font-weight:700;color:#f59e0b">
                        {{ \Carbon\Carbon::parse($exam->exam_date)->format('d') }}<br><span style="font-size:9px">{{ \Carbon\Carbon::parse($exam->exam_date)->format('M') }}</span>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $exam->subject->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $exam->title }} · {{ ucfirst(strtolower($exam->type)) }}</div>
                    </div>
                    <span class="badge badge-scheduled">Scheduled</span>
                </div>
                @empty
                <div class="empty-state"><p>No upcoming exams</p></div>
                @endforelse
            </div>
        </div>
    </div>

</x-student-layout>
