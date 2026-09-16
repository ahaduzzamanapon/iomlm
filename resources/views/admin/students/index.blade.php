<x-admin-layout>
    <x-slot name="title">Students Roster</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Students Directory</h1>
            <p>Full roster of enrolled students and applicants</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="tabs">
        <a href="{{ route('admin.students.index') }}" class="tab-item {{ !$status ? 'active' : '' }}">All Students</a>
        <a href="{{ route('admin.students.index', ['status' => 'ACTIVE']) }}" class="tab-item {{ $status === 'ACTIVE' ? 'active' : '' }}">Active</a>
        <a href="{{ route('admin.students.index', ['status' => 'PENDING']) }}" class="tab-item {{ $status === 'PENDING' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('admin.students.index', ['status' => 'GRADUATED']) }}" class="tab-item {{ $status === 'GRADUATED' ? 'active' : '' }}">Graduated</a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student Code</th>
                        <th>Name</th>
                        <th>Phone / Email</th>
                        <th>Active Course & Batch</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $st)
                    <tr>
                        <td>
                            @if($st->student_code)
                                <a href="{{ route('admin.students.impersonate', $st) }}" class="badge badge-active no-dot" style="text-decoration:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px" title="শিক্ষার্থী হিসেবে সরাসরি লগইন করুন (Click to login as student)">
                                    <i class="fa-solid fa-arrow-right-to-bracket" style="font-size:10px"></i>
                                    <strong>{{ $st->student_code }}</strong>
                                </a>
                            @else
                                <span class="td-muted">Unassigned</span>
                            @endif
                        </td>
                        <td class="td-primary">
                            <a href="{{ route('admin.students.show', $st) }}" style="font-weight:600;color:var(--blue)">{{ $st->name }}</a>
                        </td>
                        <td class="td-muted">{{ $st->phone }}<br>{{ $st->email ?? '—' }}</td>
                        <td>
                            @php $activeEnr = $st->enrollments->where('status', 'ACTIVE')->first(); @endphp
                            @if($activeEnr)
                                <strong>{{ $activeEnr->batch->name ?? '—' }}</strong><br>
                                <span class="td-muted">{{ $activeEnr->batch->course->name ?? '—' }}</span>
                            @else
                                <span class="td-muted">No active enrollment</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ strtolower($st->status) }}">{{ ucfirst(strtolower($st->status)) }}</span>
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <a href="{{ route('admin.students.impersonate', $st) }}" class="btn btn-outline btn-sm" style="color:#047857;margin-right:4px" title="শিক্ষার্থী হিসেবে সরাসরি লগইন">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i> লগইন
                            </a>
                            <a href="{{ route('admin.students.accounts', $st) }}" class="btn btn-outline btn-sm" style="color:#4f46e5;margin-right:4px" title="লেজার ও ফি হিসাব">
                                <i class="fa-solid fa-wallet"></i> লেজার
                            </a>
                            <a href="{{ route('admin.students.show', $st) }}" class="btn btn-outline btn-sm">প্রোফাইল →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No students found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
