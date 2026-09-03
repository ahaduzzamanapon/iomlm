<x-admin-layout>
    <x-slot name="title">Batches & Timelines</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Batches</h1>
            <p>Create cohorts and manage class sessions from routine</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addBatchModal')">
                New Batch
            </button>
        </div>
    </div>

    <div class="card" style="overflow:visible">
        <div class="table-wrapper" style="overflow:visible">
            <table>
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Batch Name</th>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>Sessions</th>
                        <th>Status</th>
                        <th>Admission Status</th>
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
                            <span class="badge badge-scheduled no-dot">{{ $batch->class_sessions_count ?? 0 }} Sessions</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ strtolower($batch->status) }}">{{ ucfirst(strtolower($batch->status)) }}</span>
                        </td>
                        <td>
                            @if($batch->is_admission_open)
                                <span class="badge badge-active no-dot"><i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:4px"></i> Admission Open</span>
                            @else
                                <span class="badge badge-secondary no-dot"><i class="fa-solid fa-circle-xmark" style="color:#ef4444;margin-right:4px"></i> Admission Closed</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="dropdown" style="display:inline-block">
                                <button class="btn btn-outline btn-sm" onclick="toggleDropdown('bact-{{ $batch->id }}')" style="gap:4px">
                                    Actions
                                    </button>
                                <div class="dropdown-menu" id="bact-{{ $batch->id }}" style="right:0;min-width:165px">
                                    <a href="{{ route('admin.batches.show', $batch) }}" class="dropdown-item">
                                        <i class="fa-solid fa-eye" style="margin-right:6px"></i>
                                        View Details
                                    </a>
                                    <button class="dropdown-item" onclick="openEditBatchModal({{ $batch->id }});toggleDropdown('bact-{{ $batch->id }}')">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right:6px"></i>
                                        Edit Batch
                                    </button>
                                    <form method="POST" action="{{ route('admin.batches.generateTimeline', $batch) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="width:100%;border:none;background:none;text-align:left">
                                            Gen Sessions
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.batches.destroy', $batch) }}" onsubmit="return confirm('Delete this batch?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item danger" style="width:100%;border:none;background:none;text-align:left;color:var(--red)">
                                            <i class="fa-solid fa-trash" style="margin-right:6px"></i>
                                            Delete Batch
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">No batches found. Click "New Batch" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Batch Modal -->
    <div class="modal-overlay" id="addBatchModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Create New Batch</span>
                <button class="modal-close" onclick="closeModal('addBatchModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.batches.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Batch Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Batch 01 - Morning Shift" required>
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

                    <div class="form-group" style="margin-top:10px">
                        <label class="form-check" style="cursor:pointer;font-weight:600">
                            <input type="checkbox" name="is_admission_open" value="1" checked> Open for Admission
                        </label>
                    </div>

                    <div class="alert alert-info" style="margin-top:8px">
                        <strong>Auto Sessions:</strong> After creating the batch, set up the Routine for this batch — sessions will be auto-generated daily from the routine schedule.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addBatchModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Batch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Batch Modal -->
    <div class="modal-overlay" id="editBatchModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title" id="editBatchTitle">Edit Batch</span>
                <button class="modal-close" onclick="closeModal('editBatchModal')">&times;</button>
            </div>
            <form method="POST" id="editBatchForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Batch Name <span class="required">*</span></label>
                        <input type="text" name="name" id="eb_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Select Course <span class="required">*</span></label>
                        <select name="course_id" id="eb_course_id" class="form-control" required>
                            <option value="">-- Choose Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ str_replace('_',' ',$c->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select name="academic_year_id" id="eb_academic_year_id" class="form-control">
                                <option value="">-- Select Year --</option>
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}">{{ $y->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Batch Start Date <span class="required">*</span></label>
                            <input type="date" name="start_date" id="eb_start_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Batch Status <span class="required">*</span></label>
                        <select name="status" id="eb_status" class="form-control" required>
                            <option value="ACTIVE">Active</option>
                            <option value="COMPLETED">Completed</option>
                            <option value="SUSPENDED">Suspended</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-top:14px">
                        <label class="form-check" style="cursor:pointer;font-weight:600">
                            <input type="checkbox" name="is_admission_open" id="eb_is_admission_open" value="1"> Open for Admission
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editBatchModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Batch</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const batchesData = {
        @foreach($batches as $b)
        {{ $b->id }}: {
            name: @json($b->name),
            course_id: {{ $b->course_id }},
            academic_year_id: @json($b->academic_year_id),
            start_date: @json(\Carbon\Carbon::parse($b->start_date)->format('Y-m-d')),
            status: @json($b->status),
            is_admission_open: {{ $b->is_admission_open ? 'true' : 'false' }}
        },
        @endforeach
    };

    function openEditBatchModal(id) {
        const b = batchesData[id];
        if (!b) return;
        document.getElementById('editBatchForm').action = '/admin/batches/' + id;
        document.getElementById('editBatchTitle').innerText = 'Edit Batch: ' + b.name;
        document.getElementById('eb_name').value = b.name;
        document.getElementById('eb_course_id').value = b.course_id;
        document.getElementById('eb_academic_year_id').value = b.academic_year_id || '';
        document.getElementById('eb_start_date').value = b.start_date;
        document.getElementById('eb_status').value = b.status;
        document.getElementById('eb_is_admission_open').checked = b.is_admission_open;
        openModal('editBatchModal');
    }
    </script>
    @endpush
</x-admin-layout>
