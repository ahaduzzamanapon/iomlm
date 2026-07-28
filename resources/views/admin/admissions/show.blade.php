<x-admin-layout>
    <x-slot name="title">Review Admission — {{ $admission->student->name ?? 'Application' }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.admissions.index') }}">← Back to Admissions</a>
            </div>
            <h1>Application: {{ $admission->student->name ?? '—' }}</h1>
            <p>Attempt #{{ $admission->attempt_no }} · Submitted {{ $admission->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="page-header-actions">
            @if($admission->status === 'PENDING')
                <button class="btn btn-success btn-lg" onclick="openModal('approveModal')">✓ Approve & Activate Student</button>
                <button class="btn btn-danger btn-lg" onclick="openModal('rejectModal')">✕ Reject Application</button>
            @else
                <span class="badge badge-{{ strtolower($admission->status) }}" style="font-size:14px;padding:8px 16px">
                    Status: {{ ucfirst(strtolower($admission->status)) }}
                </span>
            @endif
        </div>
    </div>

    <div class="grid-2">
        <!-- Application Details -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Applicant Details</span>
                @if($admission->student->student_code)
                    <span class="badge badge-active no-dot">Student ID: {{ $admission->student->student_code }}</span>
                @endif
            </div>
            <div class="card-body">
                <table class="table" style="font-size:13px">
                    <tr><th style="width:140px;color:var(--text-muted)">Full Name:</th><td><strong>{{ $admission->student->name }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Phone:</th><td>{{ $admission->student->phone }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Email:</th><td>{{ $admission->student->email ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Course Interested:</th><td><strong>{{ $admission->interestedCourse->name ?? '—' }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Blood Group / NID:</th><td>{{ $admission->student->blood_group ?? '—' }} / {{ $admission->student->national_id ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Guardian:</th><td>{{ $admission->student->guardian_name ?? '—' }} ({{ $admission->student->guardian_phone ?? '—' }})</td></tr>
                    <tr><th style="color:var(--text-muted)">SSC / HSC GPA:</th><td>{{ $admission->student->ssc_gpa ?? '—' }} / {{ $admission->student->hsc_gpa ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Address:</th><td>{{ $admission->student->address ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Lead Source:</th><td>{{ $admission->lead_source ?? 'Direct' }}</td></tr>
                    @if($admission->notes)
                    <tr><th style="color:var(--text-muted)">Notes:</th><td>{{ $admission->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Review Decision & Log -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Review Audit Log</span>
            </div>
            <div class="card-body">
                @if($admission->status === 'APPROVED')
                    <div class="alert alert-success">
                        <strong>✓ Application Approved</strong><br>
                        Reviewed by {{ $admission->reviewer->name ?? 'Admin' }} on {{ $admission->reviewed_at ? \Carbon\Carbon::parse($admission->reviewed_at)->format('d M Y, h:i A') : '—' }}.
                    </div>
                @elseif($admission->status === 'REJECTED')
                    <div class="alert alert-danger">
                        <strong>✕ Application Rejected</strong><br>
                        Reason: {{ $admission->rejection_reason ?? 'Not specified' }}<br>
                        <small>Reviewed by {{ $admission->reviewer->name ?? 'Admin' }} on {{ $admission->reviewed_at ? \Carbon\Carbon::parse($admission->reviewed_at)->format('d M Y, h:i A') : '—' }}.</small>
                    </div>
                @else
                    <div class="alert alert-info">
                        <strong>⏳ Pending Committee Review</strong><br>
                        Verify applicant documents, choose an active batch, and click Approve to generate Student Code & enroll.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal-overlay" id="approveModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Approve Admission & Assign Batch</span>
                <button class="modal-close" onclick="closeModal('approveModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.admissions.approve', $admission) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">
                        Approving will set student status to <strong>ACTIVE</strong>, generate an official <strong>Student Code</strong>, and enroll into the selected batch.
                    </p>
                    <div class="form-group">
                        <label>Select Active Batch for {{ $admission->interestedCourse->name ?? 'Course' }} <span class="required">*</span></label>
                        <select name="batch_id" class="form-control" required>
                            <option value="">-- Choose Batch --</option>
                            @foreach($activeBatches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} (Code: {{ $b->batch_code ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">✓ Approve & Activate Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal-overlay" id="rejectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Reject Admission Application</span>
                <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.admissions.reject', $admission) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason <span class="required">*</span></label>
                        <textarea name="rejection_reason" class="form-control" placeholder="e.g. Incomplete HSC certificates, GPA below course requirement..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('rejectModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
