<x-admin-layout>
    <x-slot name="title">{{ $batch->name }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.batches.index') }}">← Back to Batches</a>
            </div>
            <h1>{{ $batch->name }}</h1>
            <p>Course: {{ $batch->course->name }} &middot; Code: {{ $batch->batch_code }} &middot; Start: {{ \Carbon\Carbon::parse($batch->start_date)->format('d M Y') }}</p>
        </div>
        <div class="page-header-actions">
            <form method="POST" action="{{ route('admin.batches.generateTimeline', $batch) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">⚡ Generate Sessions (8 Weeks)</button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

        {{-- LEFT: Class Sessions --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">📅 Class Sessions ({{ $sessions->count() }} total)</span>
                    <span style="font-size:11px;color:var(--text-muted)">Routine-based · per class date</span>
                </div>

                @if($sessions->isEmpty())
                    <div style="padding:30px;text-align:center;color:var(--text-muted)">
                        No sessions yet. Click "Generate Sessions" to create from routine.
                    </div>
                @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Slot</th>
                                <th>Teacher</th>
                                <th>Module Covered</th>
                                <th>Attendance</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $cs)
                            @php
                                $isToday  = $cs->session_date?->isToday();
                                $isPast   = $cs->session_date?->isPast() && !$isToday;
                                $present  = $cs->attendances->where('status','PRESENT')->count();
                                $total    = $cs->attendances->count();
                            @endphp
                            <tr style="{{ $isToday ? 'background:#eff6ff;' : ($isPast && $cs->status !== 'COMPLETED' ? 'opacity:.7;' : '') }}">
                                <td>
                                    <strong style="font-size:12px">
                                        {{ $cs->session_date?->format('d M') ?? 'TBA' }}
                                    </strong>
                                    <div class="td-muted" style="font-size:10px">
                                        {{ $cs->session_date?->format('D Y') ?? '' }}
                                        @if($isToday)<span class="badge badge-success no-dot" style="font-size:9px">TODAY</span>@endif
                                    </div>
                                </td>
                                <td class="td-primary" style="font-size:12px">{{ $cs->subject?->name ?? '—' }}</td>
                                <td class="td-muted" style="font-size:11px">
                                    {{ $cs->routineEntry?->slot?->name ?? '—' }}<br>
                                    @if($cs->start_time)<small>{{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }}</small>@endif
                                </td>
                                <td class="td-muted" style="font-size:11px">{{ $cs->teacher?->name ?? '—' }}</td>
                                <td class="td-muted" style="font-size:11px">{{ $cs->moduleCovered?->title ?? '—' }}</td>
                                <td style="text-align:center">
                                    @if($total > 0)
                                        <span style="font-size:12px;font-weight:700;color:{{ $present==$total ? '#10b981' : '#f59e0b' }}">{{ $present }}/{{ $total }}</span>
                                    @else
                                        <span style="color:#d1d5db">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cs->meeting_link)
                                        <a href="{{ $cs->meeting_link }}" target="_blank" style="font-size:11px;color:#3b82f6">🔗</a>
                                    @else
                                        <span style="color:#d1d5db;font-size:11px">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php $badge = match($cs->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                                    <span class="badge {{ $badge }} no-dot" style="font-size:10px">{{ $cs->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.classes.show', $cs) }}" class="btn btn-ghost btn-sm">→</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Curriculum Syllabus (informational) --}}
            @if($subjects->isNotEmpty())
            <div class="card" style="margin-top:16px">
                <div class="card-header">
                    <span class="card-title">📖 Curriculum Syllabus</span>
                    <span style="font-size:11px;color:var(--text-muted)">Module coverage tracked via class sessions</span>
                </div>
                @foreach($subjects as $subject)
                <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
                    <div style="font-weight:600;font-size:13px;margin-bottom:6px">{{ $subject->name }} ({{ $subject->code }})</div>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        @foreach($subject->modules as $mod)
                        @php $covered = in_array($mod->id, $coveredModuleIds); @endphp
                        <div style="display:flex;align-items:center;gap:8px;font-size:12px;padding:4px 8px;background:{{ $covered ? '#f0fdf4' : '#f8fafc' }};border-radius:4px;border:1px solid {{ $covered ? '#bbf7d0' : '#e2e8f0' }}">
                            <span style="color:{{ $covered ? '#10b981' : '#9ca3af' }};font-size:14px">{{ $covered ? '✅' : '○' }}</span>
                            <span style="{{ $covered ? '' : 'color:#64748b' }}">{{ $mod->title }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- RIGHT: Enrolled Students --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">👥 Enrolled Students</span>
                    <span class="badge badge-info no-dot">{{ $batch->enrollments->count() }}</span>
                </div>
                @if($batch->enrollments->isEmpty())
                    <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">No students enrolled yet.</div>
                @else
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Code</th><th>Name</th><th>Enrolled</th></tr></thead>
                        <tbody>
                            @foreach($batch->enrollments as $en)
                            <tr>
                                <td style="font-size:11px;color:#3b82f6;font-weight:600">{{ $en->student->student_code }}</td>
                                <td class="td-primary" style="font-size:12px">{{ $en->student->name }}</td>
                                <td class="td-muted" style="font-size:11px">{{ \Carbon\Carbon::parse($en->enrolled_at)->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
