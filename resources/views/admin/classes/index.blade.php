<x-admin-layout>
    <x-slot name="title">Classes & Smart Merge</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Class Sessions & Smart Merge Monitor</h1>
            <p>Live tracking of scheduled, running, completed, and merged class sessions</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="tabs">
        <a href="{{ route('admin.classes.index') }}" class="tab-item {{ !$status ? 'active' : '' }}">All Classes</a>
        <a href="{{ route('admin.classes.index', ['status' => 'SCHEDULED']) }}" class="tab-item {{ $status === 'SCHEDULED' ? 'active' : '' }}">Scheduled</a>
        <a href="{{ route('admin.classes.index', ['status' => 'COMPLETED']) }}" class="tab-item {{ $status === 'COMPLETED' ? 'active' : '' }}">Completed</a>
        <a href="{{ route('admin.classes.index', ['status' => 'CANCELLED']) }}" class="tab-item {{ $status === 'CANCELLED' ? 'active' : '' }}">Cancelled / Rescheduled</a>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject & Module</th>
                        <th>Batch / Merged Cohort</th>
                        <th>Assigned Teacher</th>
                        <th>Scheduled Date</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $c)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $c->timeline->subject->name ?? '—' }}</strong><br>
                            <span class="td-muted">Module: {{ $c->timeline->module->title ?? '—' }}</span>
                        </td>
                        <td>
                            @if($c->mergedGroups->count() > 1)
                                <span class="badge badge-rescheduled no-dot">⚡ Merged ({{ $c->mergedGroups->count() }} Batches)</span>
                            @else
                                <span class="badge badge-secondary no-dot">{{ $c->timeline->batch->name ?? 'Single Batch' }}</span>
                            @endif
                        </td>
                        <td>{{ $c->teacher->name ?? 'Unassigned' }}</td>
                        <td class="td-muted">{{ $c->timeline->scheduled_date ? \Carbon\Carbon::parse($c->timeline->scheduled_date)->format('d M Y') : '—' }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower($c->status) }}">{{ ucfirst(strtolower($c->status)) }}</span>
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('admin.classes.show', $c) }}" class="btn btn-outline btn-sm">Inspect Class →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No class sessions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
