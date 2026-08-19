<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    <!-- Welcome Banner -->
    <div class="dashboard-banner">
        <div>
            <h2 class="banner-title">Assalamu Alaikum, {{ auth()->user()->name ?? 'Admin' }} 👋</h2>
            <p class="banner-sub">Here is your live academic overview — {{ now()->format('l, d M Y') }}</p>
        </div>
    </div>

    <div class="page-header" style="margin-bottom:20px">
        <div class="page-header-left">
            <h1 style="font-size:22px">System Overview</h1>
            <p>Real-time metrics and quick management controls</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.admissions.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> New Admission
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="stats-grid">
        <a href="{{ route('admin.students.index') }}" class="stat-card">
            <div class="stat-icon blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_students'] }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="stat-card">
            <div class="stat-icon green">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_teachers'] }}</div>
                <div class="stat-label">Teachers</div>
            </div>
        </a>
        <a href="{{ route('admin.courses.index') }}" class="stat-card">
            <div class="stat-icon violet">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_courses'] }}</div>
                <div class="stat-label">Courses</div>
            </div>
        </a>
        <a href="{{ route('admin.batches.index') }}" class="stat-card">
            <div class="stat-icon orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['active_batches'] }}</div>
                <div class="stat-label">Active Batches</div>
            </div>
        </a>
        <a href="{{ route('admin.admissions.index') }}" class="stat-card">
            <div class="stat-icon red">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['pending_admissions'] }}</div>
                <div class="stat-label">Pending Admissions</div>
            </div>
        </a>
        <a href="{{ route('admin.classes.index') }}" class="stat-card">
            <div class="stat-icon teal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['today_classes'] }}</div>
                <div class="stat-label">Today's Classes</div>
            </div>
        </a>
    </div>

    <div class="grid-2">
        <!-- Pending Admissions Card -->
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:18px">📥</span>
                    <span class="card-title">Pending Admissions</span>
                </div>
                <a href="{{ route('admin.admissions.index') }}" class="btn btn-ghost btn-sm">View All →</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Name</th><th>Course</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($pendingAdmissions as $admission)
                        <tr>
                            <td class="td-primary">{{ $admission->student->name ?? '—' }}</td>
                            <td>{{ $admission->interestedCourse->name ?? '—' }}</td>
                            <td class="td-muted">{{ $admission->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.admissions.show', $admission) }}" class="btn btn-outline btn-sm">Review</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:32px 20px;color:var(--text-muted)">
                                <div style="font-size:24px;margin-bottom:6px">✨</div>
                                <div>No pending admissions</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Today's Classes Card -->
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:18px">🎥</span>
                    <span class="card-title">Today's Scheduled Classes</span>
                </div>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-ghost btn-sm">View Routine →</a>
            </div>
            @if($todayClasses->isEmpty())
                <div style="padding:36px;text-align:center;color:var(--text-muted)">
                    <div style="font-size:24px;margin-bottom:6px">📅</div>
                    <div>No live classes scheduled for today.</div>
                </div>
            @else
            <div style="padding:0">
                @foreach($todayClasses as $cs)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid #f1f5f9">
                    <div style="width:38px;height:38px;background:{{ $cs->routineEntry?->color ?? '#2563eb' }}18;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1.5px solid {{ $cs->routineEntry?->color ?? '#2563eb' }}">
                        <span style="font-size:14px">📹</span>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:13px;font-weight:700;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cs->subject?->name ?? '—' }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                            {{ $cs->batch?->name ?? '' }}
                            @if($cs->routineEntry?->slot) · {{ $cs->routineEntry->slot->name }} @endif
                            · {{ $cs->teacher?->name ?? 'No teacher' }}
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;align-items:flex-end">
                        @if($cs->meeting_link)
                            <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-primary btn-sm" style="font-size:11px;padding:4px 10px">🔗 Join</a>
                        @else
                            <form method="POST" action="{{ route('admin.classes.updateSchedule', $cs) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="session_date" value="{{ $cs->session_date?->toDateString() ?? now()->toDateString() }}">
                                <button class="btn btn-outline btn-sm" style="font-size:11px;padding:4px 10px;color:#d97706;border-color:#fde68a">⚡ Auto-Link</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.classes.show', $cs) }}" style="font-size:11px;color:#4f46e5;font-weight:600">Manage →</a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Active Batches Card -->
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:18px">🎓</span>
                    <span class="card-title">Active Academic Batches</span>
                </div>
                <a href="{{ route('admin.batches.index') }}" class="btn btn-ghost btn-sm">Manage</a>
            </div>
            <div style="padding:0">
                @forelse($activeBatches as $batch)
                <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid #f1f5f9">
                    <div style="width:40px;height:40px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;flex-shrink:0;box-shadow:0 2px 8px rgba(37,99,235,0.2)">
                        {{ strtoupper(substr($batch->name,0,2)) }}
                    </div>
                    <div style="flex:1">
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-primary)">{{ $batch->name }}</div>
                        <div style="font-size:11.5px;color:var(--text-muted);margin-top:1px">{{ $batch->course->name ?? '—' }}</div>
                    </div>
                    <span class="badge badge-active">Active</span>
                </div>
                @empty
                <div style="padding:32px;text-align:center;color:var(--text-muted)">
                    <div style="font-size:24px;margin-bottom:6px">📚</div>
                    <div>No active batches</div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Setup Card -->
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:18px">⚙️</span>
                    <span class="card-title">Quick Academic Setup</span>
                </div>
            </div>
            <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <a href="{{ route('admin.subjects.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#eff6ff;color:#2563eb">📖</div>
                    <div>Subjects & Modules</div>
                </a>
                <a href="{{ route('admin.courses.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#f5f3ff;color:#7c3aed">🎓</div>
                    <div>Courses & Semesters</div>
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#ecfdf5;color:#059669">👨‍🏫</div>
                    <div>Teachers & Faculty</div>
                </a>
                <a href="{{ route('admin.batches.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#fffbeb;color:#d97706">👥</div>
                    <div>Batches & Timelines</div>
                </a>
            </div>
        </div>
    </div>

</x-admin-layout>
