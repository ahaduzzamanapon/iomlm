<x-admin-layout>
    <x-slot name="title">{{ $batch->name }} — Timeline</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.batches.index') }}">← Back to Batches</a>
            </div>
            <h1>{{ $batch->name }}</h1>
            <p>Course: {{ $batch->course->name ?? '—' }} · Code: {{ $batch->batch_code }} · Start Date: {{ \Carbon\Carbon::parse($batch->start_date)->format('d M Y') }}</p>
        </div>
        <div class="page-header-actions">
            <form method="POST" action="{{ route('admin.batches.generate-timeline', $batch) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-outline">⚡ Re-generate Timeline</button>
            </form>
        </div>
    </div>

    <div class="grid-2">
        <!-- Module Timeline Sequence -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Batch Learning Timeline</span>
                <span class="badge badge-secondary no-dot">{{ $batch->timelines->count() }} Modules</span>
            </div>
            <div style="padding:16px">
                <div class="timeline">
                    @forelse($batch->timelines as $tl)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ strtolower($tl->status) }}">
                            {{ $tl->module->sequence_no ?? 1 }}
                        </div>
                        <div class="timeline-content">
                            <div style="display:flex;align-items:center;justify-content:space-between">
                                <strong style="font-size:13px">{{ $tl->module->title ?? '—' }}</strong>
                                <span class="badge badge-{{ strtolower($tl->status) }}">{{ ucfirst(strtolower($tl->status)) }}</span>
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                                Subject: <strong>{{ $tl->subject->name ?? '—' }}</strong> · Scheduled: {{ \Carbon\Carbon::parse($tl->scheduled_date)->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state"><p>No timeline slots generated yet. Click "Re-generate Timeline" to populate!</p></div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Enrolled Students Roster -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Enrolled Students in Batch</span>
                <span class="badge badge-active no-dot">{{ $batch->enrollments->count() }} Students</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Student Name</th>
                            <th>Enrolled On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batch->enrollments as $enr)
                        <tr>
                            <td><span class="badge badge-active no-dot">{{ $enr->student->student_code ?? 'N/A' }}</span></td>
                            <td class="td-primary">
                                <a href="{{ route('admin.students.show', $enr->student) }}" style="color:var(--blue)">{{ $enr->student->name ?? '—' }}</a>
                            </td>
                            <td class="td-muted">{{ \Carbon\Carbon::parse($enr->enrolled_at)->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">No students enrolled in this batch yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
