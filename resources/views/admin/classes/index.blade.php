<x-admin-layout>
    <x-slot name="title">Classes & Smart Merge</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Class Sessions & Smart Merge Monitor</h1>
            <p>Live tracking, schedule assignment, date/time & meeting link management</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="tabs">
        <a href="{{ route('admin.classes.index') }}" class="tab-item {{ !$status ? 'active' : '' }}">All Classes</a>
        <a href="{{ route('admin.classes.index', ['status' => 'SCHEDULED']) }}" class="tab-item {{ $status === 'SCHEDULED' ? 'active' : '' }}">Scheduled</a>
        <a href="{{ route('admin.classes.index', ['status' => 'COMPLETED']) }}" class="tab-item {{ $status === 'COMPLETED' ? 'active' : '' }}">Completed</a>
        <a href="{{ route('admin.classes.index', ['status' => 'CANCELLED']) }}" class="tab-item {{ $status === 'CANCELLED' ? 'active' : '' }}">Cancelled / Rescheduled</a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject & Module</th>
                        <th>Batch / Merged Cohort</th>
                        <th>Assigned Teacher</th>
                        <th>Scheduled Date & Time</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $c)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $c->timeline->subject->name ?? '—' }}</strong><br>
                            <span class="td-muted">Module: {{ $c->timeline->module->title ?? '—' }}</span>
                        </td>
                        <td>
                            @if($c->mergedGroups && $c->mergedGroups->count() > 1)
                                <span class="badge badge-rescheduled no-dot">⚡ Merged ({{ $c->mergedGroups->count() }} Batches)</span>
                            @else
                                <span class="badge badge-secondary no-dot">{{ $c->timeline->batch->name ?? 'Single Batch' }}</span>
                            @endif
                        </td>
                        <td>{{ $c->teacher->name ?? 'Unassigned' }}</td>
                        <td>
                            @if($c->timeline->scheduled_date)
                                <strong>{{ \Carbon\Carbon::parse($c->timeline->scheduled_date)->format('d M Y') }}</strong>
                                @if($c->start_time)
                                    <br><span style="font-size:11px;color:var(--blue);font-weight:600">🕒 {{ \Carbon\Carbon::parse($c->start_time)->format('h:i A') }}</span>
                                @endif
                            @else
                                <span class="badge badge-upcoming no-dot">Upcoming (Unscheduled)</span>
                            @endif
                        </td>
                        <td>
                            @if($c->timeline->scheduled_date)
                                <span class="badge badge-{{ strtolower($c->status) }}">{{ ucfirst(strtolower($c->status)) }}</span>
                            @else
                                <span class="badge badge-upcoming">Upcoming</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                                <button class="btn btn-outline btn-sm" onclick="openAdminScheduleModal('{{ $c->id }}', '{{ $c->timeline->scheduled_date }}', '{{ $c->start_time }}', '{{ $c->teacher_id }}', '{{ $c->meeting_link }}')">
                                    📅 Set Date, Time & Link
                                </button>
                                <a href="{{ route('admin.classes.show', $c) }}" class="btn btn-primary btn-sm">Inspect →</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No class sessions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admin Schedule Modal -->
    <div class="modal-overlay" id="adminScheduleModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Schedule Date, Time, Teacher & Link</span>
                <button class="modal-close" onclick="closeModal('adminScheduleModal')">&times;</button>
            </div>
            <form id="adminScheduleForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Scheduled Date <span class="required">*</span></label>
                            <input type="date" id="admin_modal_date" name="scheduled_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Class Start Time</label>
                            <input type="time" id="admin_modal_time" name="start_time" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Assigned Teacher</label>
                        <select id="admin_modal_teacher" name="teacher_id" class="form-control">
                            <option value="">-- Choose Teacher --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->designation }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Meeting Link (Google Meet / Zoom)</label>
                        <input type="url" id="admin_modal_link" name="meeting_link" class="form-control" placeholder="https://meet.google.com/abc-defg-hij">
                        <small style="color:var(--text-muted);margin-top:4px;display:block">Leave blank to auto-generate a unique Google Meet link.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('adminScheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAdminScheduleModal(classId, date, time, teacherId, link) {
            document.getElementById('adminScheduleForm').action = '/admin/classes/' + classId + '/schedule';
            document.getElementById('admin_modal_date').value = date || new Date().toISOString().split('T')[0];
            document.getElementById('admin_modal_time').value = time || '20:00';
            document.getElementById('admin_modal_teacher').value = teacherId || '';
            document.getElementById('admin_modal_link').value = link || '';
            openModal('adminScheduleModal');
        }
    </script>
</x-admin-layout>
