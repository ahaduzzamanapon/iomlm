<x-teacher-layout>
    <x-slot name="title">Conduct: {{ $class->subject?->name ?? 'Class' }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.classes.index') }}">← Back to Classes</a>
            </div>
            <h1>Conduct Class: {{ $class->subject?->name ?? '—' }}</h1>
            <p>
                {{ $class->batch?->name ?? '—' }} &middot;
                {{ $class->session_date?->format('d M Y (D)') ?? 'TBA' }} &middot;
                {{ $class->routineEntry?->slot?->name ?? '' }}
                @if($class->start_time) · {{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }} @endif
            </p>
        </div>
        <div class="page-header-actions">
            @if($class->meeting_link)
                <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-outline btn-sm">🔗 Join Meeting</a>
            @elseif($meetingProvider === 'zoom')
                <form method="POST" action="{{ route('teacher.classes.setLink', $class) }}">
                    @csrf
                    <button class="btn btn-outline btn-sm">⚡ Generate Zoom Meeting</button>
                </form>
            @else
                <button class="btn btn-outline btn-sm" style="color:#f59e0b"
                    onclick="document.getElementById('linkFormTop').style.display='flex'">
                    🔗 Add Meeting Link
                </button>
                <form id="linkFormTop" method="POST" action="{{ route('teacher.classes.setLink', $class) }}"
                    style="display:none;gap:6px;align-items:center">
                    @csrf
                    <input type="url" name="meeting_link" class="form-control" style="min-width:240px;font-size:12px"
                        placeholder="{{ $meetingProvider === 'google_meet' ? 'https://meet.google.com/xxx-xxxx-xxx' : 'Paste any meeting URL…' }}"
                        required>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </form>
            @endif
            <form method="POST" action="{{ route('teacher.classes.cancel', $class) }}" onsubmit="return confirm('Cancel this class?')">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444">✕ Cancel Session</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.classes.complete', $class) }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start">

            {{-- Attendance Roster --}}
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <span class="card-title">👥 Attendance Roster</span>
                        <span class="badge badge-info no-dot">{{ $batchStudents->count() }} Students</span>
                    </div>
                    @if($class->meeting_link || $class->zoom_meeting_id)
                        <form method="POST" action="{{ route('teacher.classes.syncZoomAttendance', $class) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline" style="color:#2563eb;font-weight:600" title="Fetch attendance report from Zoom">
                                ⚡ Sync Zoom Attendance
                            </button>
                        </form>
                    @endif
                </div>

                @if($batchStudents->isEmpty())
                    <div style="padding:30px;text-align:center;color:var(--text-muted)">No students enrolled in this batch.</div>
                @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Code</th>
                                <th>Name</th>
                                <th style="text-align:center">Present ✅</th>
                                <th style="text-align:center">Absent ❌</th>
                                <th style="text-align:center">Late ⏰</th>
                                <th style="text-align:center">Excused 📝</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batchStudents as $i => $en)
                            @php $st = $en->student; $existing = $class->attendances->firstWhere('student_id', $st->id); @endphp
                            <tr>
                                <td class="td-muted">{{ $i + 1 }}</td>
                                <td style="font-size:11px;color:#3b82f6;font-weight:600">{{ $st->student_code }}</td>
                                <td class="td-primary">{{ $st->name }}</td>
                                @foreach(['PRESENT','ABSENT','LATE','EXCUSED'] as $status)
                                <td style="text-align:center">
                                    <input type="radio"
                                        name="attendance[{{ $st->id }}]"
                                        value="{{ $status }}"
                                        {{ ($existing?->status === $status || (!$existing && $status === 'PRESENT')) ? 'checked' : '' }}>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Right Panel --}}
            <div style="display:flex;flex-direction:column;gap:16px">
                {{-- Module Covered --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">📖 Module Covered</span></div>
                    <div style="padding:12px">
                        <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px">Optional — which module did you cover today?</p>
                        <select name="module_covered_id" class="form-control">
                            <option value="">— Not specified —</option>
                            @foreach($modules as $mod)
                            <option value="{{ $mod->id }}" {{ $class->module_covered_id == $mod->id ? 'selected' : '' }}>
                                {{ $mod->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">📝 Session Notes</span></div>
                    <div style="padding:12px">
                        <textarea name="notes" class="form-control" rows="4"
                            placeholder="Topics covered, student questions, announcements...">{{ $class->notes }}</textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:15px">
                    ✅ Complete Class & Save Attendance
                </button>
            </div>
        </div>
    </form>
</x-teacher-layout>
