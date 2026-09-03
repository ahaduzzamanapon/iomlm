<x-admin-layout>
    <x-slot name="title">Admissions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Admission Management</h1>
            <p>Admin-added এবং Public Form থেকে আসা সকল আবেদন এখানে দেখুন</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.admissions.create') }}" class="btn btn-primary">
                New Admission
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    @php
        $tab    = request('tab', 'all');
        $status = request('status');
    @endphp
    <div class="tabs" style="margin-bottom:0">
        <a href="?tab=all"    class="tab-item {{ $tab === 'all'    ? 'active' : '' }}">All
            <span class="badge badge-secondary no-dot" style="margin-left:4px">{{ $totalCount }}</span>
        </a>
        <a href="?tab=admin"  class="tab-item {{ $tab === 'admin'  ? 'active' : '' }}">
            <i class="fa-solid fa-building-columns"></i> Admin Added
            <span class="badge badge-secondary no-dot" style="margin-left:4px">{{ $adminCount }}</span>
        </a>
        <a href="?tab=public" class="tab-item {{ $tab === 'public' ? 'active' : '' }}">
            <i class="fa-solid fa-globe"></i> Public Form
            <span class="badge badge-secondary no-dot" style="margin-left:4px;background:rgba(139,92,246,.15);color:#7c3aed">{{ $publicCount }}</span>
            @if($publicPending > 0)<span class="badge no-dot" style="background:#ef4444;color:#fff;margin-left:4px">{{ $publicPending }}</span>@endif
        </a>
    </div>

    {{-- Status filter row --}}
    <div style="display:flex;align-items:center;gap:8px;background:var(--card-bg);border:1px solid var(--card-border);border-top:0;border-radius:0 0 8px 8px;padding:10px 16px;margin-bottom:16px;flex-wrap:wrap">
        <span style="font-size:12px;color:var(--text-muted);font-weight:500">Filter:</span>
        @foreach([''=>'All Status','PENDING'=>'Pending','APPROVED'=>'Approved','REJECTED'=>'Rejected'] as $s => $label)
        <a href="?tab={{ $tab }}&status={{ $s }}"
           style="font-size:12px;padding:4px 12px;border-radius:20px;text-decoration:none;border:1px solid var(--card-border);
                  {{ $status === $s ? 'background:var(--blue);color:#fff;border-color:var(--blue)' : 'color:var(--text-secondary)' }}">
            {{ $label }}
        </a>
        @endforeach
        <form method="GET" style="margin-left:auto;display:flex;gap:6px">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, phone..." style="width:220px;height:32px;font-size:12px">
            <button type="submit" class="btn btn-outline btn-sm">Search</button>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Applicant</th>
                        <th>Course / Session</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ── Admin-created Admissions ── --}}
                    @if($tab !== 'public')
                        @forelse($adminAdmissions as $adm)
                        <tr>
                            <td>
                                <span class="badge no-dot" style="background:rgba(59,130,246,.1);color:#1d4ed8;font-size:11px"><i class="fa-solid fa-building-columns"></i> Admin</span>
                            </td>
                            <td class="td-primary">
                                <strong>{{ $adm->student->name ?? '—' }}</strong>
                                <div class="td-muted">{{ $adm->student->phone ?? '—' }}</div>
                                @if($adm->waiver_code)
                                    <span class="badge badge-active no-dot" style="font-size:10px;padding:2px 6px;margin-top:2px"><i class="fa-solid fa-gift"></i> Waiver: {{ $adm->waiver_code }} ({{ $adm->discount_percent }}%)</span>
                                @endif
                            </td>
                            <td style="font-size:12px">
                                {{ $adm->interestedCourse->name ?? '—' }}
                                <div class="td-muted">Attempt #{{ $adm->attempt_no }}</div>
                            </td>
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
                                <a href="{{ route('admin.admissions.show', $adm) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                            </td>
                        </tr>
                        @empty
                        @if($tab === 'admin')<tr><td colspan="6" style="text-align:center;padding:28px;color:var(--text-muted)">No admin-added admissions found.</td></tr>@endif
                        @endforelse
                    @endif

                    {{-- ── Public Form Applications ── --}}
                    @if($tab !== 'admin')
                        @forelse($publicApplications as $pub)
                        <tr>
                            <td>
                                <span class="badge no-dot" style="background:rgba(139,92,246,.1);color:#7c3aed;font-size:11px"><i class="fa-solid fa-globe"></i> Public</span>
                                <div style="font-size:10px;color:var(--text-muted);margin-top:2px">{{ $pub->application_no }}</div>
                            </td>
                            <td class="td-primary">
                                <strong>{{ $pub->student->name ?? '—' }}</strong>
                                <div class="td-muted">{{ $pub->student->phone ?? '—' }}</div>
                                @if($pub->waiver_code)
                                    <span class="badge badge-active no-dot" style="font-size:10px;padding:2px 6px;margin-top:2px"><i class="fa-solid fa-gift"></i> Waiver: {{ $pub->waiver_code }} ({{ $pub->discount_percent }}%)</span>
                                @endif
                            </td>
                            <td style="font-size:12px">
                                {{ $pub->interestedCourse->name ?? '—' }}
                                <div class="td-muted">{{ $pub->session->name ?? '—' }}</div>
                            </td>
                            <td class="td-muted">{{ $pub->created_at->format('d M Y') }}</td>
                            <td>
                                @if($pub->status === 'PENDING')
                                    <span class="badge badge-pending">Pending</span>
                                @elseif($pub->status === 'APPROVED')
                                    <span class="badge badge-active">Approved</span>
                                @elseif($pub->status === 'REVIEWED')
                                    <span class="badge badge-scheduled">Reviewed</span>
                                @else
                                    <span class="badge badge-cancelled">Rejected</span>
                                @endif
                            </td>
                            <td style="text-align:right">
                                <a href="{{ route('admin.admissions.show', $pub) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i> View</a>
                            </td>
                        </tr>
                        @empty
                        @if($tab === 'public')<tr><td colspan="6" style="text-align:center;padding:28px;color:var(--text-muted)">No public applications found.</td></tr>@endif
                        @endforelse
                    @endif

                    @if($tab === 'all' && $adminAdmissions->isEmpty() && $publicApplications->isEmpty())
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No applications found.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
