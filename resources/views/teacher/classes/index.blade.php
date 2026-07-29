<x-teacher-layout>
    <x-slot name="title">My Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Class Schedule & Execution</h1>
            <p>Conduct classes, schedule dates & meeting links, and mark attendance</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject & Module</th>
                        <th>Batch</th>
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
                            <span class="td-muted">Module #{{ $c->timeline->module->sequence_no ?? 1 }}: {{ $c->timeline->module->title ?? '—' }}</span>
                        </td>
                        <td><span class="badge badge-secondary no-dot">{{ $c->timeline->batch->name ?? '—' }}</span></td>
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
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                                <button class="btn btn-outline btn-sm" onclick="openScheduleModal('{{ $c->id }}', '{{ $c->timeline->scheduled_date }}', '{{ $c->start_time }}', '{{ $c->meeting_link }}')">
                                    📅 Set Date, Time & Link
                                </button>
                                @if($c->status === 'SCHEDULED' || $c->status === 'RUNNING')
                                    <a href="{{ route('teacher.classes.conduct', $c) }}" class="btn btn-primary btn-sm">▶ Conduct Class</a>
                                @else
                                    <span class="td-muted" style="font-size:12px">Completed</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No class sessions scheduled for your assigned subjects.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Teacher Schedule Modal -->
    <div class="modal-overlay" id="teacherScheduleModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Schedule Class Date, Time & Meeting Link</span>
                <button class="modal-close" onclick="closeModal('teacherScheduleModal')">&times;</button>
            </div>
            <form id="teacherScheduleForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Scheduled Date <span class="required">*</span></label>
                            <input type="date" id="modal_scheduled_date" name="scheduled_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Class Start Time</label>
                            <input type="time" id="modal_start_time" name="start_time" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Meeting Link (Google Meet / Zoom)</label>
                        <input type="url" id="modal_meeting_link" name="meeting_link" class="form-control" placeholder="https://meet.google.com/abc-defg-hij">
                        <small style="color:var(--text-muted);margin-top:4px;display:block">Leave blank to auto-generate a unique Google Meet link.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('teacherScheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openScheduleModal(classId, date, time, link) {
            document.getElementById('teacherScheduleForm').action = '/teacher/classes/' + classId + '/schedule';
            document.getElementById('modal_scheduled_date').value = date || new Date().toISOString().split('T')[0];
            document.getElementById('modal_start_time').value = time || '20:00';
            document.getElementById('modal_meeting_link').value = link || '';
            openModal('teacherScheduleModal');
        }
    </script>
</x-teacher-layout>
