<x-admin-layout>
    <x-slot name="title">Student Profile — {{ $student->name }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.students.index') }}">← Back to Students</a>
            </div>
            <h1>{{ $student->name }}</h1>
            <p>Code: {{ $student->student_code ?? 'Unassigned' }} · Status: {{ ucfirst(strtolower($student->status)) }}</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline">Edit Profile</a>
        </div>
    </div>

    <div class="grid-2">
        <!-- Profile Overview -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Personal & Contact Info</span>
            </div>
            <div class="card-body">
                <table class="table" style="font-size:13px">
                    <tr><th style="width:140px;color:var(--text-muted)">Student Code:</th><td><strong>{{ $student->student_code ?? '—' }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Phone:</th><td>{{ $student->phone }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Email:</th><td>{{ $student->email ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Blood Group / NID:</th><td>{{ $student->blood_group ?? '—' }} / {{ $student->national_id ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Father Name:</th><td>{{ $student->father_name ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Mother Name:</th><td>{{ $student->mother_name ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Guardian:</th><td>{{ $student->guardian_name ?? '—' }} ({{ $student->guardian_phone ?? '—' }})</td></tr>
                    <tr><th style="color:var(--text-muted)">Address:</th><td>{{ $student->address ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Enrollments (Parallel Enrollment Support!) -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Course Enrollments</span>
                <span class="badge badge-secondary no-dot">{{ $student->enrollments->count() }} Total Enrollments</span>
            </div>
            <div style="padding:0">
                @forelse($student->enrollments as $enr)
                <div style="padding:14px 20px;border-bottom:1px solid var(--card-border)">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                        <strong style="font-size:13px">{{ $enr->batch->course->name ?? '—' }}</strong>
                        <span class="badge badge-{{ strtolower($enr->status) }}">{{ ucfirst(strtolower($enr->status)) }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted)">
                        Batch: {{ $enr->batch->name ?? '—' }} · Enrolled on {{ \Carbon\Carbon::parse($enr->enrolled_at)->format('d M Y') }}
                    </div>
                </div>
                @empty
                <div class="empty-state"><p>No course enrollments found.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
