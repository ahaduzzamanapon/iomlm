<x-admin-layout>
    <x-slot name="title">Subjects & Modules</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Subjects & Modules Management</h1>
            <p>Define subjects, credits, pass marks, and sequential learning modules</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addSubjectModal')">
                New Subject
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Credit</th>
                        <th>Full / Pass Marks</th>
                        <th>Modules</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subj)
                    <tr>
                        <td><span class="badge badge-secondary no-dot"><strong>{{ $subj->code }}</strong></span></td>
                        <td class="td-primary">
                            <a href="{{ route('admin.subjects.show', $subj) }}" style="font-weight:600;color:var(--blue)">{{ $subj->name }}</a>
                        </td>
                        <td>{{ $subj->credit }} Credit</td>
                        <td class="td-muted">{{ $subj->full_marks }} / {{ $subj->pass_marks }}</td>
                        <td>
                            <span class="badge badge-scheduled no-dot">{{ $subj->modules_count }} Modules</span>
                        </td>
                        <td>
                            @if($subj->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-outline btn-sm" onclick='openEditSubjectModal(@json($subj))'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <a href="{{ route('admin.subjects.show', $subj) }}" class="btn btn-outline btn-sm">Manage Modules</a>
                            <form method="POST" action="{{ route('admin.subjects.destroy', $subj) }}" style="display:inline" onsubmit="return confirm('Delete this subject?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No subjects found. Click "New Subject" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Subject Modal -->
    <div class="modal-overlay" id="addSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Subject</span>
                <button class="modal-close" onclick="closeModal('addSubjectModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subjects.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject Code <span class="required">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. CSE-101" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Credit <span class="required">*</span></label>
                            <input type="number" name="credit" class="form-control" value="3" min="1" max="10" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subject Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Programming Fundamentals" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Marks <span class="required">*</span></label>
                            <input type="number" name="full_marks" class="form-control" value="100" required>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" class="form-control" value="40" required>
                        </div>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked> Active Subject
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSubjectModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal-overlay" id="editSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Subject</span>
                <button class="modal-close" onclick="closeModal('editSubjectModal')">&times;</button>
            </div>
            <form method="POST" id="editSubjectForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject Code <span class="required">*</span></label>
                            <input type="text" name="code" id="es_code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Credit <span class="required">*</span></label>
                            <input type="number" name="credit" id="es_credit" class="form-control" min="1" max="10" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subject Name <span class="required">*</span></label>
                        <input type="text" name="name" id="es_name" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Marks <span class="required">*</span></label>
                            <input type="number" name="full_marks" id="es_full_marks" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" id="es_pass_marks" class="form-control" required>
                        </div>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="is_active" id="es_is_active" value="1"> Active Subject
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editSubjectModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditSubjectModal(subj) {
        document.getElementById('editSubjectForm').action = '/admin/subjects/' + subj.id;
        document.getElementById('es_code').value = subj.code;
        document.getElementById('es_credit').value = subj.credit;
        document.getElementById('es_name').value = subj.name;
        document.getElementById('es_full_marks').value = subj.full_marks;
        document.getElementById('es_pass_marks').value = subj.pass_marks;
        document.getElementById('es_is_active').checked = !!subj.is_active;
        openModal('editSubjectModal');
    }
    </script>
    @endpush
</x-admin-layout>
