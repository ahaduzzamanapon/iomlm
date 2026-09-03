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
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['enrolled_courses'] }}</div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['upcoming_classes'] }}</div>
                <div class="stat-label">Upcoming Classes</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['attendance_percent'] }}%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
        </div>
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['upcoming_exams'] }}</div>
                <div class="stat-label">Upcoming Exams</div>
            </div>
        </div>
    </div>

    {{-- Central Notice Board Widget --}}
    @if(isset($notices) && $notices->count() > 0)
    <div class="card" style="margin-bottom:24px;border-top:4px solid #047857">
        <div class="card-header">
            <span class="card-title">Notice Board &amp; Announcements</span>
        </div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:12px">
            @foreach($notices as $n)
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                    <strong style="font-size:14px;color:#0f172a">{{ $n->title }}</strong>
                    <span class="badge {{ $n->priority === 'URGENT' ? 'badge-danger' : ($n->priority === 'IMPORTANT' ? 'badge-warning' : 'badge-info') }} no-dot" style="font-size:10px">
                        {{ $n->priority }}
                    </span>
                </div>
                <div style="font-size:13px;color:#334155;line-height:1.5;margin-bottom:6px">
                    {{ $n->content }}
                </div>
                <div style="font-size:11px;color:#64748b">
                    Published {{ $n->created_at->diffForHumans() }} ({{ $n->created_at->format('d M Y') }})
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid-2">
        <!-- Recent Sessions / Covered Modules -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Subjects & Coverage</span>
                <a href="{{ route('student.classes.index') }}" class="btn btn-ghost btn-sm">All Classes</a>
            </div>
            <div style="padding:0">
                @forelse($currentModules as $cs)
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--card-border)">
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $cs->subject?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            {{ $cs->session_date?->format('d M Y (D)') ?? 'TBA' }}
                            @if($cs->moduleCovered) · <i class="fa-solid fa-book"></i> {{ $cs->moduleCovered->title }} @endif
                        </div>
                    </div>
                    @php $badge = match($cs->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                    <span class="badge {{ $badge }} no-dot">{{ $cs->status }}</span>
                </div>
                @empty
                <div class="empty-state"><p>No class sessions yet</p></div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Classes -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Upcoming Classes</span>
                <a href="{{ route('student.classes.index') }}" class="btn btn-ghost btn-sm">All →</a>
            </div>
            <div style="padding:0">
                @forelse($upcomingClasses as $class)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--card-border)">
                    <div style="width:38px;height:38px;background:#f5f3ff;border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;color:#8b5cf6;font-size:12px">
                        <span>{{ $class->session_date?->format('d') }}</span>
                        <span style="font-size:9px">{{ $class->session_date?->format('M') }}</span>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:600">{{ $class->subject?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">
                            {{ $class->batch?->name ?? '' }}
                            @if($class->routineEntry?->slot) · {{ $class->routineEntry->slot->name }} @endif
                            · {{ $class->teacher?->name ?? '—' }}
                        </div>
                    </div>
                    @if($class->meeting_link)
                    <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-video"></i> Join</a>
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
