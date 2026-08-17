<x-admin-layout>
    <x-slot name="title">Poor Fund & Waiver Applications</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Poor Fund / Waiver Applications</h1>
            <p>Review financial assistance requests and poor fund applications submitted by applicants</p>
        </div>
        <div class="page-header-actions">
            <a href="/poor-fund" target="_blank" class="btn btn-outline">🌐 View Public Form ↗</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid-3" style="margin-bottom:24px">
        <div class="stat-card">
            <div class="stat-label">Pending Review</div>
            <div class="stat-value" style="color:var(--yellow)">{{ $pendingCount }}</div>
            <div class="stat-sub">Requires committee decision</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Approved Waivers</div>
            <div class="stat-value" style="color:var(--green)">{{ $approvedCount }}</div>
            <div class="stat-sub">Financial assistance granted</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Rejected</div>
            <div class="stat-value" style="color:var(--red)">{{ $rejectedCount }}</div>
            <div class="stat-sub">Applications declined</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:20px;padding:16px">
        <form method="GET" action="{{ route('admin.waiver-applications.index') }}" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <input type="text" name="search" class="form-control" style="max-width:280px" value="{{ $search }}" placeholder="Search name, phone, APP no...">
            <select name="status" class="form-control" style="max-width:180px" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>Pending Only</option>
                <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>Approved Only</option>
                <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>Rejected Only</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if($status || $search)
                <a href="{{ route('admin.waiver-applications.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card" style="overflow:visible">
        <table>
            <thead>
                <tr>
                    <th>App No</th>
                    <th>Applicant Name</th>
                    <th>Phone / Email</th>
                    <th>Monthly Income</th>
                    <th>Reason / Fee Convenience</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr>
                    <td><span class="badge badge-scheduled no-dot"><strong>{{ $app->application_no }}</strong></span></td>
                    <td class="td-primary">
                        <a href="{{ route('admin.waiver-applications.show', $app) }}" style="font-weight:600;color:var(--blue)">{{ $app->full_name }}</a>
                        @if($app->is_abroad) <span class="badge badge-secondary no-dot" style="font-size:10px">Abroad</span> @endif
                    </td>
                    <td>
                        <div>{{ $app->phone }}</div>
                        <div class="td-muted" style="font-size:11px">{{ $app->email }}</div>
                    </td>
                    <td><strong>৳ {{ number_format($app->monthly_income, 0) }}</strong></td>
                    <td>
                        @php
                            $applyLabel = match($app->apply_for ?? '') {
                                'ADMISSION_FEE' => 'Admission Fee Only',
                                'TUITION_FEE'   => 'Tuition Fee Only',
                                'BOTH'          => 'Both (Admission + Tuition)',
                                default         => $app->apply_reason_type ?? '—',
                            };
                        @endphp
                        <span class="badge badge-secondary no-dot">{{ $applyLabel }}</span>
                        <div class="td-muted" style="font-size:11px">
                            @if($app->convenient_admission_fee) Adm: ৳{{ $app->convenient_admission_fee }} @endif
                            @if($app->convenient_monthly_fee) Monthly: ৳{{ $app->convenient_monthly_fee }} @endif
                        </div>
                    </td>
                    <td>
                        @if($app->status === 'PENDING')
                            <span class="badge badge-pending">⏳ Pending Review</span>
                        @elseif($app->status === 'APPROVED')
                            @php
                                $parts = [];
                                if ($app->approved_admission_fee !== null) $parts[] = 'Adm: ৳'.number_format($app->approved_admission_fee, 0);
                                if ($app->approved_package_id) $parts[] = 'Pkg: #'.$app->approved_package_id;
                                if (empty($parts) && $app->approved_discount_value > 0) {
                                    $parts[] = ($app->discount_type === 'FIXED' ? '৳'.number_format($app->approved_discount_value,0).' Fixed' : $app->approved_discount_value.'%');
                                }
                                $discDisplay = implode(' | ', $parts) ?: '—';
                            @endphp
                            <span class="badge badge-active">✓ Approved ({{ $discDisplay }})</span>
                        @else
                            <span class="badge badge-danger">✕ Rejected</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.waiver-applications.show', $app) }}" class="btn btn-outline btn-sm">Review App →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No poor fund applications found matching filter.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:16px">
            {{ $applications->links() }}
        </div>
    </div>
</x-admin-layout>
