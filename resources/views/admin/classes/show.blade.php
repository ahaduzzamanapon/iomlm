<x-admin-layout>
    <x-slot name="title">Class Session Details</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.classes.index') }}">← Back to Classes</a>
            </div>
            <h1>Class: {{ $class->timeline->subject->name ?? '—' }}</h1>
            <p>
                Module: {{ $class->timeline->module->title ?? '—' }} · 
                Status: 
                @if($class->timeline->scheduled_date)
                    <span class="badge badge-{{ strtolower($class->status) }}">{{ ucfirst(strtolower($class->status)) }}</span>
                @else
                    <span class="badge badge-upcoming">Upcoming (Unscheduled)</span>
                @endif
            </p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('scheduleModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                📅 Set Date, Time & Link
            </button>
        </div>
    </div>

    <div class="grid-2">
        <!-- Class Metadata -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Class Execution State</span>
            </div>
            <div class="card-body">
                <table class="table" style="font-size:13px">
                    <tr><th style="width:140px;color:var(--text-muted)">Subject:</th><td><strong>{{ $class->timeline->subject->name ?? '—' }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Module Step:</th><td>Module #{{ $class->timeline->module->sequence_no ?? 1 }} — {{ $class->timeline->module->title ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Teacher Assigned:</th><td>{{ $class->teacher->name ?? 'Unassigned' }}</td></tr>
                    <tr>
                        <th style="color:var(--text-muted)">Scheduled Date:</th>
                        <td>
                            @if($class->timeline->scheduled_date)
                                <strong>{{ \Carbon\Carbon::parse($class->timeline->scheduled_date)->format('d M Y') }}</strong>
                                @if($class->start_time)
                                    · <span style="color:var(--blue);font-weight:600">🕒 {{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }}</span>
                                @endif
                                <span class="badge badge-scheduled no-dot" style="margin-left:6px">Scheduled</span>
                            @else
                                <span class="badge badge-upcoming no-dot">Upcoming (Date Not Assigned)</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th style="color:var(--text-muted)">Teacher Present?</th><td>{{ $class->teacher_present === true ? '✓ Yes' : ($class->teacher_present === false ? '✕ No (Triggered Cancel)' : 'Pending') }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Class Conducted?</th><td>{{ $class->class_conducted === true ? '✓ Yes' : ($class->class_conducted === false ? '✕ No' : 'Pending') }}</td></tr>
                    <tr>
                        <th style="color:var(--text-muted)">Meeting Link:</th>
                        <td>
                            @if($class->meeting_link)
                                <a href="{{ $class->meeting_link }}" target="_blank" style="color:var(--blue);font-weight:600">{{ $class->meeting_link }} ↗</a>
                            @else
                                <span class="td-muted">Meeting link not added</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Attendance Roster for this class -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Student Attendance Roster</span>
                <span class="badge badge-secondary no-dot">{{ $class->attendances->count() }} Recorded</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($class->attendances as $att)
                        <tr>
                            <td class="td-primary">{{ $att->student->name ?? '—' }}</td>
                            <td><span class="badge badge-{{ strtolower($att->status) }}">{{ ucfirst(strtolower($att->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" style="text-align:center;padding:20px;color:var(--text-muted)">Attendance not marked yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Schedule Date, Time & Meeting Link Modal -->
    <div class="modal-overlay" id="scheduleModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Schedule Date, Time & Meeting Link</span>
                <button class="modal-close" onclick="closeModal('scheduleModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.classes.schedule.update', $class) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Scheduled Date <span class="required">*</span></label>
                            <input type="date" name="scheduled_date" class="form-control" value="{{ $class->timeline->scheduled_date ?? date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Class Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ $class->start_time ?? '20:00' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Assigned Teacher</label>
                        <select name="teacher_id" class="form-control">
                            <option value="">-- Choose Teacher --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ $class->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->designation }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Meeting Link (Google Meet / Zoom)</label>
                        <input type="url" name="meeting_link" class="form-control" value="{{ $class->meeting_link }}" placeholder="https://meet.google.com/abc-defg-hij">
                        <small style="color:var(--text-muted);margin-top:4px;display:block">Leave blank to auto-generate a unique Google Meet link.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('scheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
