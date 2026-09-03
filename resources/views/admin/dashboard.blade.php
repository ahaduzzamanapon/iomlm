<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    <!-- Welcome Banner -->
    <div class="dashboard-banner">
        <div>
            <h2 class="banner-title">Assalamu Alaikum, {{ auth()->user()->name ?? 'Admin' }}</h2>
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
                New Admission
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="stats-grid">
        <a href="{{ route('admin.students.index') }}" class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_students'] }}</div>
                <div class="stat-label">Total Students</div>
            </div>
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_teachers'] }}</div>
                <div class="stat-label">Teachers</div>
            </div>
        </a>
        <a href="{{ route('admin.courses.index') }}" class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['total_courses'] }}</div>
                <div class="stat-label">Courses</div>
            </div>
        </a>
        <a href="{{ route('admin.batches.index') }}" class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['active_batches'] }}</div>
                <div class="stat-label">Active Batches</div>
            </div>
        </a>
        <a href="{{ route('admin.admissions.index') }}" class="stat-card">
            
            <div class="stat-info">
                <div class="stat-value">{{ $stats['pending_admissions'] }}</div>
                <div class="stat-label">Pending Admissions</div>
            </div>
        </a>
        <a href="{{ route('admin.classes.index', ['date' => now()->toDateString()]) }}" class="stat-card">
            
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
                    <i class="fa-solid fa-inbox" style="font-size:16px;color:#3b82f6"></i>
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
                                <div style="font-size:24px;margin-bottom:6px"><i class="fa-solid fa-circle-check" style="color:#10b981"></i></div>
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
                    <i class="fa-solid fa-video" style="font-size:16px;color:#3b82f6"></i>
                    <span class="card-title">Today's Scheduled Classes</span>
                </div>
                <a href="{{ route('admin.classes.index', ['date' => now()->toDateString()]) }}" class="btn btn-ghost btn-sm">View Routine →</a>
            </div>
            @if($todayClasses->isEmpty())
                <div style="padding:36px;text-align:center;color:var(--text-muted)">
                    <div style="font-size:24px;margin-bottom:6px"><i class="fa-solid fa-calendar-xmark" style="color:#94a3b8"></i></div>
                    <div>No live classes scheduled for today.</div>
                </div>
            @else
            <div style="padding:0">
                @foreach($todayClasses as $cs)
                <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid #f1f5f9">
                    <div style="width:38px;height:38px;background:{{ $cs->routineEntry?->color ?? '#2563eb' }}18;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1.5px solid {{ $cs->routineEntry?->color ?? '#2563eb' }}">
                        <i class="fa-solid fa-video" style="font-size:14px;color:{{ $cs->routineEntry?->color ?? '#2563eb' }}"></i>
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
                            <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-primary btn-sm" style="font-size:11px;padding:4px 10px"><i class="fa-solid fa-link"></i> Join</a>
                        @else
                            <form method="POST" action="{{ route('admin.classes.updateSchedule', $cs) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="session_date" value="{{ $cs->session_date?->toDateString() ?? now()->toDateString() }}">
                                <button class="btn btn-outline btn-sm" style="font-size:11px;padding:4px 10px;color:#d97706;border-color:#fde68a">Auto-Link</button>
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
                    <i class="fa-solid fa-graduation-cap" style="font-size:16px;color:#3b82f6"></i>
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
                    <div style="font-size:24px;margin-bottom:6px"><i class="fa-solid fa-book-open" style="color:#94a3b8"></i></div>
                    <div>No active batches</div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Setup Card -->
        <div class="card">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:10px">
                    <i class="fa-solid fa-gears" style="font-size:16px;color:#3b82f6"></i>
                    <span class="card-title">Quick Academic Setup</span>
                </div>
            </div>
            <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <a href="{{ route('admin.subjects.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#eff6ff;color:#2563eb"><i class="fa-solid fa-book"></i></div>
                    <div>Subjects &amp; Modules</div>
                </a>
                <a href="{{ route('admin.courses.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div>Courses &amp; Semesters</div>
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#ecfdf5;color:#059669"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <div>Teachers &amp; Faculty</div>
                </a>
                <a href="{{ route('admin.batches.index') }}" class="quick-tile">
                    <div class="quick-tile-icon" style="background:#fffbeb;color:#d97706"><i class="fa-solid fa-users"></i></div>
                    <div>Batches &amp; Timelines</div>
                </a>
            </div>
        </div>
    </div>
    </div>

</x-admin-layout>
