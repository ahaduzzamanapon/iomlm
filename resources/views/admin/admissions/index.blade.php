<x-admin-layout>
    <x-slot name="title">Admissions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Admission Management</h1>
            <p>Review student applications, approve activations, and manage re-applies</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.admissions.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Admission Form
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="tabs">
        <a href="{{ route('admin.admissions.index') }}" class="tab-item {{ !$status ? 'active' : '' }}">All Applications</a>
        <a href="{{ route('admin.admissions.index', ['status' => 'PENDING']) }}" class="tab-item {{ $status === 'PENDING' ? 'active' : '' }}">
            Pending Review
            @php $p = \App\Models\AdmissionForm::where('status','PENDING')->count() @endphp
            @if($p > 0)<span class="badge badge-pending no-dot" style="margin-left:4px">{{ $p }}</span>@endif
        </a>
        <a href="{{ route('admin.admissions.index', ['status' => 'APPROVED']) }}" class="tab-item {{ $status === 'APPROVED' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('admin.admissions.index', ['status' => 'REJECTED']) }}" class="tab-item {{ $status === 'REJECTED' ? 'active' : '' }}">Rejected</a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Interested Course</th>
                        <th>Attempt</th>
                        <th>Lead Source</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admissions as $adm)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $adm->student->name ?? '—' }}</strong><br>
                            <span class="td-muted">{{ $adm->student->phone ?? '—' }} · {{ $adm->student->email ?? 'No email' }}</span>
                        </td>
                        <td>{{ $adm->interestedCourse->name ?? '—' }}</td>
                        <td><span class="badge badge-secondary no-dot">Attempt #{{ $adm->attempt_no }}</span></td>
                        <td class="td-muted">{{ $adm->lead_source ?? 'Direct' }}</td>
                        <td class="td-muted">{{ $adm->created_at->format('d M Y') }}</td>
                        <td>
                            @if($adm->status === 'PENDING')
                                <span class="badge badge-pending">Pending</span>
                            @elseif($adm->status === 'APPROVED')
                                <span class="badge badge-active">Approved</span>
                            @else
                                <span class="badge badge-cancelled">Rejected</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('admin.admissions.show', $adm) }}" class="btn btn-outline btn-sm">Review / View →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No admission applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
