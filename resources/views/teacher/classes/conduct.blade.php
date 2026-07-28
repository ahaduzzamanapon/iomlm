<x-teacher-layout>
    <x-slot name="title">Conduct Class — {{ $class->timeline->subject->name ?? 'Class' }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.classes.index') }}">← Back to Classes</a>
            </div>
            <h1>Conducting Class: {{ $class->timeline->subject->name ?? '—' }}</h1>
            <p>Module #{{ $class->timeline->module->sequence_no ?? 1 }}: {{ $class->timeline->module->title ?? '—' }} · Batch: {{ $class->timeline->batch->name ?? '—' }}</p>
        </div>
        <div class="page-header-actions">
            <form method="POST" action="{{ route('teacher.classes.cancel', $class) }}" onsubmit="return confirm('Cancel this class? This will trigger automatic reschedule for next week.')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-danger">✕ Mark Class Cancelled (Teacher Absent)</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('teacher.classes.complete', $class) }}">
        @csrf @method('PATCH')
        <div class="grid-2">
            <!-- Student Attendance Marking Roster -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Student Attendance Roster</span>
                    <span class="badge badge-active no-dot">{{ $batchStudents->count() }} Students Enrolled</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Code</th>
                                <th>Student Name</th>
                                <th>Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batchStudents as $enr)
                            @php $st = $enr->student; @endphp
                            <tr>
                                <td><span class="badge badge-secondary no-dot">{{ $st->student_code ?? 'N/A' }}</span></td>
                                <td class="td-primary"><strong>{{ $st->name ?? '—' }}</strong></td>
                                <td>
                                    <div style="display:flex;gap:12px">
                                        <label class="form-check">
                                            <input type="radio" name="attendance[{{ $st->id }}]" value="PRESENT" checked> Present
                                        </label>
                                        <label class="form-check">
                                            <input type="radio" name="attendance[{{ $st->id }}]" value="ABSENT"> Absent
                                        </label>
                                        <label class="form-check">
                                            <input type="radio" name="attendance[{{ $st->id }}]" value="LATE"> Late
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">No active students in this batch roster.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Class Completion Notes -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Class Execution Notes</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Class Topics Covered / Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Summary of topics explained during this module session..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        💡 <strong>No-Makeup Rule (§6.3):</strong> Marking a student ABSENT will record "MISSED" in their individual timeline. Progress continues normally without makeup classes.
                    </div>
                </div>
                <div class="card-footer" style="text-align:right">
                    <button type="submit" class="btn btn-success btn-lg">✓ Complete Class & Save Attendance</button>
                </div>
            </div>
        </div>
    </form>
</x-teacher-layout>
