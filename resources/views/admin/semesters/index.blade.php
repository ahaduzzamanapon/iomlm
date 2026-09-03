<x-admin-layout>
    <x-slot name="title">Semesters</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Semester Management</h1>
            <p>Define semester sequences for Semester-Based courses</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addSemesterModal')">
                <i class="fa-solid fa-plus"></i>
                New Semester
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Semester Name</th>
                        <th>Sequence No.</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semesters as $sem)
                    <tr>
                        <td class="td-primary"><strong>{{ $sem->course->name ?? '—' }}</strong></td>
                        <td>{{ $sem->name }}</td>
                        <td><span class="badge badge-secondary no-dot">Semester #{{ $sem->sequence_no }}</span></td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('admin.semesters.destroy', $sem) }}" style="display:inline" onsubmit="return confirm('Delete this semester?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">No semesters created yet. Click "New Semester" to add one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Semester Modal -->
    <div class="modal-overlay" id="addSemesterModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Course Semester</span>
                <button class="modal-close" onclick="closeModal('addSemesterModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.semesters.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Semester-Based Course <span class="required">*</span></label>
                        <select name="course_id" class="form-control" required>
                            <option value="">-- Choose Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Sequence No. <span class="required">*</span></label>
                            <input type="number" name="sequence_no" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Semester Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. 1st Semester (প্রথম সেমিস্টার)" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSemesterModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Semester</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
