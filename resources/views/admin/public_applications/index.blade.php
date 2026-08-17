<x-admin-layout>
    <x-slot name="title">Public Applications</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Public Applications</h1>
            <p>Online Admission Form (/apply) থেকে আসা আবেদনসমূহ</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('apply.show') }}" target="_blank" class="btn btn-outline">🔗 View Form</a>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <input type="text" name="search" class="form-control" placeholder="Search name, phone, app no..." value="{{ request('search') }}" style="max-width:260px">
        <select name="status" class="form-control" style="max-width:160px">
            <option value="">All Status</option>
            @foreach(['PENDING','REVIEWED','APPROVED','REJECTED'] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="{{ route('admin.public-applications.index') }}" class="btn btn-ghost">Clear</a>
    </form>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>App No.</th>
                        <th>Applicant</th>
                        <th>Course</th>
                        <th>Session</th>
                        <th>Applied At</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td><code style="font-size:12px">{{ $app->application_no }}</code></td>
                        <td class="td-primary">
                            <strong>{{ $app->applicant_name }}</strong>
                            <div class="td-muted">{{ $app->phone }}</div>
                        </td>
                        <td style="font-size:12px">{{ $app->course->name ?? '—' }}</td>
                        <td style="font-size:12px">{{ $app->session->name ?? '—' }}</td>
                        <td style="font-size:12px;color:var(--text-muted)">{{ $app->created_at->format('d M Y') }}</td>
                        <td>
                            <span class="badge {{ $app->status_badge }} no-dot">{{ $app->status }}</span>
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('admin.public-applications.show', $app) }}" class="btn btn-outline btn-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
        <div style="padding:14px 20px">{{ $applications->links() }}</div>
        @endif
    </div>
</x-admin-layout>
