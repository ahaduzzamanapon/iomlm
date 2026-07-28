<x-admin-layout>
    <x-slot name="title">Batches & Timelines</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Batches & Module Timelines</h1>
            <p>Create cohorts and automatically generate module-driven learning timelines</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addBatchModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Batch
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Batch Name</th>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>Timeline Slots</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr>
                        <td><span class="badge badge-active no-dot"><strong>{{ $batch->batch_code }}</strong></span></td>
                        <td class="td-primary">
                            <a href="{{ route('admin.batches.show', $batch) }}" style="font-weight:600;color:var(--blue)">{{ $batch->name }}</a>
                        </td>
                        <td>{{ $batch->course->name ?? '—' }}</td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($batch->start_date)->format('d M Y') }}</td>
                        <td>
                            <span class="badge badge-scheduled no-dot">{{ $batch->timelines->count() }} Modules Scheduled</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ strtolower($batch->status) }}">{{ ucfirst(strtolower($batch->status)) }}</span>
                        </td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('admin.batches.generate-timeline', $batch) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm" title="Re-sync Timeline">⚡ Sync Timeline</button>
                            </form>
                            <a href="{{ route('admin.batches.show', $batch) }}" class="btn btn-outline btn-sm">View Timeline →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No batches found. Click "New Batch" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Batch Modal -->
    <div class="modal-overlay" id="addBatchModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Academic Batch</span>
                <button class="modal-close" onclick="closeModal('addBatchModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.batches.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Batch Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. CSE Spring 2026 Batch A" required>
                    </div>

                    <div class="form-group">
                        <label>Select Course <span class="required">*</span></label>
                        <select name="course_id" class="form-control" required>
                            <option value="">-- Choose Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ str_replace('_',' ',$c->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select name="academic_year_id" class="form-control">
                                <option value="">-- Select Year --</option>
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}">{{ $y->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Batch Start Date <span class="required">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="alert alert-info" style="margin-top:8px">
                        💡 <strong>Smart Timeline Generator:</strong> Creating this batch will automatically generate ordered module timeline slots for all mapped subjects!
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addBatchModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create & Generate Timeline</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
