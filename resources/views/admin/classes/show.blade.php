<x-admin-layout>
    <x-slot name="title">Session: {{ $class->subject?->name ?? 'Class' }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.classes.index') }}">← Back to Classes</a>
            </div>
            <h1>{{ $class->subject?->name ?? '—' }}</h1>
            <p>
                {{ $class->batch?->name ?? '—' }} &middot;
                {{ $class->session_date?->format('d M Y (D)') ?? 'Date TBA' }} &middot;
                {{ $class->routineEntry?->slot?->name ?? '' }}
                @if($class->start_time) · {{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }} @endif
            </p>
        </div>
        <div class="page-header-actions" style="gap:6px;display:flex">
            @if($class->status !== 'COMPLETED' && $class->status !== 'CANCELLED')
            <button class="btn btn-outline btn-sm" onclick="openModal('scheduleModal')">📅 Edit Schedule</button>
            <form method="POST" action="{{ route('admin.classes.cancel', $class) }}" onsubmit="return confirm('Cancel this session?')">
                @csrf
                <button class="btn btn-outline btn-sm" style="color:#ef4444">✕ Cancel Session</button>
            </form>
            @endif
            @if($class->status === 'SCHEDULED' || $class->status === 'RUNNING')
            <button class="btn btn-primary btn-sm" onclick="openModal('completeModal')">✅ Mark Complete</button>
            @endif
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

        {{-- LEFT: Session Info + Attendance --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Session Details --}}
            <div class="card">
                <div class="card-header"><span class="card-title">Session Details</span></div>
                <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px">
                    <div><div style="color:var(--text-muted);font-size:11px">Subject</div><strong>{{ $class->subject?->name ?? '—' }}</strong><br><small style="color:#64748b">{{ $class->subject?->code }}</small></div>
                    <div><div style="color:var(--text-muted);font-size:11px">Batch</div><strong>{{ $class->batch?->name ?? '—' }}</strong></div>
                    <div><div style="color:var(--text-muted);font-size:11px">Teacher</div>{{ $class->teacher?->name ?? 'Unassigned' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Slot</div>{{ $class->routineEntry?->slot?->name ?? '—' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Session Date</div>{{ $class->session_date?->format('d M Y (D)') ?? 'TBA' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Start Time</div>{{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('h:i A') : 'TBA' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Module Covered</div>{{ $class->moduleCovered?->title ?? '—' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Status</div>
                        @php $badge = match($class->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                        <span class="badge {{ $badge }} no-dot">{{ $class->status }}</span>
                    </div>
                    @if($class->meeting_link)
                    <div style="grid-column:span 2"><div style="color:var(--text-muted);font-size:11px">Meeting Link</div>
                        <a href="{{ $class->meeting_link }}" target="_blank" style="color:#3b82f6;font-size:12px">🔗 {{ $class->meeting_link }}</a>
                    </div>
                    @endif
                    @if($class->notes)
                    <div style="grid-column:span 2"><div style="color:var(--text-muted);font-size:11px">Notes</div>{{ $class->notes }}</div>
                    @endif
                </div>
            </div>

            {{-- Attendance Table --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title">👥 Attendance</span>
                    @php $present = $class->attendances->where('status','PRESENT')->count(); $total = $class->attendances->count(); @endphp
                    @if($total > 0)
                    <span class="badge badge-info no-dot">{{ $present }}/{{ $total }} Present</span>
                    @endif
                </div>
                @if($batchStudents->isEmpty())
                    <div style="padding:20px;text-align:center;color:var(--text-muted)">No students enrolled in this batch.</div>
                @else
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Student</th><th>Code</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($batchStudents as $en)
                            @php $att = $class->attendances->firstWhere('student_id', $en->student_id); @endphp
                            <tr>
                                <td class="td-primary">{{ $en->student->name }}</td>
                                <td style="font-size:11px;color:#3b82f6">{{ $en->student->student_code }}</td>
                                <td>
                                    @if($att)
                                        @php $ab = match($att->status) {'PRESENT'=>'badge-success','ABSENT'=>'badge-danger','LATE'=>'badge-warning','EXCUSED'=>'badge-secondary',default=>'badge-secondary'}; @endphp
                                        <span class="badge {{ $ab }} no-dot">{{ $att->status }}</span>
                                    @else
                                        <span style="color:#d1d5db;font-size:12px">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Quick Actions --}}
        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">⚡ Quick Actions</span></div>
                <div style="padding:12px;display:flex;flex-direction:column;gap:8px">
                    @if(!$class->meeting_link)
                    <form method="POST" action="{{ route('admin.classes.updateSchedule', $class) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="session_date" value="{{ $class->session_date?->toDateString() ?? now()->toDateString() }}">
                        <button class="btn btn-outline" style="width:100%">⚡ Auto-Generate Meeting Link</button>
                    </form>
                    @else
                    <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-primary" style="width:100%;text-align:center">🔗 Join Class</a>
                    @endif

                    @if($class->status !== 'COMPLETED' && $class->status !== 'CANCELLED')
                    <button class="btn btn-outline" style="width:100%" onclick="openModal('scheduleModal')">📅 Edit Date / Link</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Schedule Modal --}}
    <div class="modal-overlay" id="scheduleModal">
        <div class="modal" style="max-width:440px">
            <div class="modal-header">
                <span class="modal-title">Edit Session Schedule</span>
                <button class="modal-close" onclick="closeModal('scheduleModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.classes.updateSchedule', $class) }}">
                @csrf @method('PUT')
                <div class="modal-body" style="display:flex;flex-direction:column;gap:12px">
                    <div class="form-group">
                        <label class="form-label">Session Date</label>
                        <input type="date" name="session_date" class="form-control" value="{{ $class->session_date?->toDateString() ?? now()->toDateString() }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ $class->start_time }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-control">
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $class->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meeting Link <small style="color:#9ca3af">(blank = auto-generate)</small></label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..." value="{{ $class->meeting_link }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Module Covered (optional)</label>
                        <select name="module_covered_id" class="form-control">
                            <option value="">— Not specified —</option>
                            @foreach($modules as $mod)
                            <option value="{{ $mod->id }}" {{ $class->module_covered_id == $mod->id ? 'selected' : '' }}>{{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('scheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Complete Modal --}}
    <div class="modal-overlay" id="completeModal">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <span class="modal-title">✅ Mark Class Complete</span>
                <button class="modal-close" onclick="closeModal('completeModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.classes.complete', $class) }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:12px;max-height:70vh;overflow-y:auto">
                    <div class="form-group">
                        <label class="form-label">Module Covered Today (optional)</label>
                        <select name="module_covered_id" class="form-control">
                            <option value="">— Not specified —</option>
                            @foreach($modules as $mod)
                            <option value="{{ $mod->id }}" {{ $class->module_covered_id == $mod->id ? 'selected' : '' }}>{{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($batchStudents->count() > 0)
                    <div class="form-group">
                        <label class="form-label" style="margin-bottom:8px">Attendance</label>
                        @foreach($batchStudents as $en)
                        @php $att = $class->attendances->firstWhere('student_id', $en->student_id); @endphp
                        <div style="display:flex;gap:10px;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9">
                            <span style="flex:1;font-size:12px">{{ $en->student->name }}</span>
                            @foreach(['PRESENT','ABSENT','LATE','EXCUSED'] as $s)
                            <label style="font-size:11px;display:flex;align-items:center;gap:3px;cursor:pointer">
                                <input type="radio" name="attendance[{{ $en->student_id }}]" value="{{ $s }}" {{ ($att?->status===$s || (!$att && $s==='PRESENT')) ? 'checked' : '' }}>{{ $s }}
                            </label>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Class notes...">{{ $class->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('completeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">✅ Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
