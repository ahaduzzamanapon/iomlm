<x-teacher-layout>
    <x-slot name="title">Mark Attendance</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.attendance.index') }}">← Back to Attendance</a>
            </div>
            <h1>✅ Mark Attendance</h1>
            <p>
                {{ $class->subject?->name ?? '—' }} &middot;
                {{ $class->batch?->name ?? '—' }} &middot;
                {{ $class->session_date?->format('d M Y (D)') ?? 'TBA' }} &middot;
                {{ $class->routineEntry?->slot?->name ?? '' }}
            </p>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('teacher.attendance.save', $class) }}">
            @csrf

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
                        @php $existing = $existingAttendance[$en->student_id] ?? null; @endphp
                        <tr>
                            <td class="td-muted">{{ $i + 1 }}</td>
                            <td style="font-size:11px;color:#3b82f6;font-weight:600">{{ $en->student->student_code }}</td>
                            <td class="td-primary">{{ $en->student->name }}</td>
                            @foreach(['PRESENT', 'ABSENT', 'LATE', 'EXCUSED'] as $status)
                            <td style="text-align:center">
                                <input type="radio"
                                    name="attendance[{{ $en->student_id }}]"
                                    value="{{ $status }}"
                                    {{ ($existing?->status === $status || (!$existing && $status === 'PRESENT')) ? 'checked' : '' }}>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">💾 Save Attendance</button>
            </div>
            @endif
        </form>
    </div>
</x-teacher-layout>
