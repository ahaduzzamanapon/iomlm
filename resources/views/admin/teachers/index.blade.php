<x-admin-layout>
    <x-slot name="title">Teachers</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Teachers & Subject Assignments</h1>
            <p>Manage faculty members and assign subjects (global or batch overrides)</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addTeacherModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Teacher
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Teacher Name</th>
                        <th>Designation</th>
                        <th>Contact</th>
                        <th>Assigned Subjects</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                    <tr>
                        <td><span class="badge badge-secondary no-dot"><strong>{{ $teacher->employee_id ?? 'N/A' }}</strong></span></td>
                        <td class="td-primary"><strong>{{ $teacher->name }}</strong></td>
                        <td class="td-muted">{{ $teacher->designation ?? 'Faculty' }}</td>
                        <td class="td-muted">{{ $teacher->phone ?? '—' }}<br>{{ $teacher->email ?? '—' }}</td>
                        <td>
                            @forelse($teacher->assignments as $asgn)
                                <div style="display:inline-flex;align-items:center;gap:4px;margin-bottom:2px">
                                    <span class="badge badge-scheduled no-dot">{{ $asgn->subject->name ?? '—' }}</span>
                                    <form method="POST" action="{{ route('admin.teachers.subjects.remove', [$teacher, $asgn]) }}" style="display:inline" onsubmit="return confirm('Remove assignment?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="border:none;background:none;color:var(--red);cursor:pointer;font-size:10px">&times;</button>
                                    </form>
                                </div>
                            @empty
                                <span class="td-muted">No subjects assigned</span>
                            @endforelse
                        </td>
                        <td>
                            @if($teacher->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-outline btn-sm" onclick="openAssignModal({{ $teacher->id }}, '{{ $teacher->name }}')">+ Assign Subject</button>
                            <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" style="display:inline" onsubmit="return confirm('Delete teacher profile?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No teachers found. Click "New Teacher" to add faculty.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Teacher Modal -->
    <div class="modal-overlay" id="addTeacherModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Add New Teacher</span>
                <button class="modal-close" onclick="closeModal('addTeacherModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.teachers.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Alan Turing" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="teacher@learningplus.com">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+8801711...">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Designation</label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g. Professor & Head">
                        </div>
                        <div class="form-group">
                            <label>Qualification</label>
                            <input type="text" name="qualification" class="form-control" placeholder="e.g. Ph.D. in CS">
                        </div>
                    </div>

                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked> Active Faculty
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addTeacherModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Teacher</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Subject Modal -->
    <div class="modal-overlay" id="assignSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title" id="assignTitle">Assign Subject</span>
                <button class="modal-close" onclick="closeModal('assignSubjectModal')">&times;</button>
            </div>
            <form method="POST" id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Subject <span class="required">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">-- Choose Subject --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->code }}: {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('assignSubjectModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Subject</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openAssignModal(teacherId, teacherName) {
        document.getElementById('assignTitle').innerText = 'Assign Subject to ' + teacherName;
        document.getElementById('assignForm').action = '/admin/teachers/' + teacherId + '/subjects';
        openModal('assignSubjectModal');
    }
    </script>
    @endpush
</x-admin-layout>
