<x-admin-layout>
    <x-slot name="title">Class Session Details</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.classes.index') }}">← Back to Classes</a>
            </div>
            <h1>Class: {{ $class->timeline->subject->name ?? '—' }}</h1>
            <p>Module: {{ $class->timeline->module->title ?? '—' }} · Status: {{ ucfirst(strtolower($class->status)) }}</p>
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
                    <tr><th style="color:var(--text-muted)">Scheduled Date:</th><td>{{ \Carbon\Carbon::parse($class->timeline->scheduled_date)->format('d M Y') }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Teacher Present?</th><td>{{ $class->teacher_present === true ? '✓ Yes' : ($class->teacher_present === false ? '✕ No (Triggered Cancel)' : 'Pending') }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Class Conducted?</th><td>{{ $class->class_conducted === true ? '✓ Yes' : ($class->class_conducted === false ? '✕ No' : 'Pending') }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Meeting Link:</th><td>@if($class->meeting_link)<a href="{{ $class->meeting_link }}" target="_blank" style="color:var(--blue)">{{ $class->meeting_link }}</a>@else<span class="td-muted">None</span>@endif</td></tr>
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
</x-admin-layout>
