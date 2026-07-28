<x-admin-layout>
    <x-slot name="title">Courses & Setup</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Courses Management</h1>
            <p>Configure Subject-Based and Semester-Based academic courses</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addCourseModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Course
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Semesters / Structure</th>
                        <th>Mapped Subjects</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="td-primary">
                            <a href="{{ route('admin.courses.show', $course) }}" style="font-weight:600;color:var(--blue)">{{ $course->name }}</a>
                        </td>
                        <td>
                            @if($course->type === 'SEMESTER_BASED')
                                <span class="badge badge-scheduled no-dot">Semester Based</span>
                            @else
                                <span class="badge badge-secondary no-dot">Subject Based</span>
                            @endif
                        </td>
                        <td>{{ $course->duration_value }} {{ ucfirst(strtolower($course->duration_unit)) }}s</td>
                        <td>
                            @if($course->type === 'SEMESTER_BASED')
                                <span class="badge badge-secondary no-dot">{{ $course->semesters->count() }} Semesters</span>
                            @else
                                <span class="td-muted">Direct Subject Enrolled</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-active no-dot">{{ $course->courseSubjectMaps->count() }} Subjects</span>
                        </td>
                        <td>
                            @if($course->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-outline btn-sm">Configure →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No courses found. Click "New Course" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Course Modal -->
    <div class="modal-overlay" id="addCourseModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Academic Course</span>
                <button class="modal-close" onclick="closeModal('addCourseModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.courses.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Course Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. B.Sc. in Computer Science" required>
                    </div>

                    <div class="form-group">
                        <label>Course Type <span class="required">*</span></label>
                        <div class="type-selector">
                            <label class="type-option selected" id="opt-semester" onclick="selectType('SEMESTER_BASED')">
                                <input type="radio" name="type" value="SEMESTER_BASED" checked>
                                <div class="type-option-icon">🎓</div>
                                <div class="type-option-label">Semester Based</div>
                                <div class="type-option-desc">Subjects are bound to semesters</div>
                            </label>
                            <label class="type-option" id="opt-subject" onclick="selectType('SUBJECT_BASED')">
                                <input type="radio" name="type" value="SUBJECT_BASED">
                                <div class="type-option-icon">📖</div>
                                <div class="type-option-label">Subject Based</div>
                                <div class="type-option-desc">Direct subject selection</div>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Duration Value <span class="required">*</span></label>
                            <input type="number" name="duration_value" class="form-control" value="4" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Duration Unit <span class="required">*</span></label>
                            <select name="duration_unit" class="form-control" required>
                                <option value="YEAR">Years</option>
                                <option value="MONTH">Months</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="semestersCountGroup">
                        <label>Auto-create Semesters</label>
                        <input type="number" name="total_semesters" class="form-control" value="8" min="1" max="12" placeholder="e.g. 8">
                    </div>

                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked> Active Course
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addCourseModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Course</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function selectType(type) {
        document.querySelectorAll('.type-option').forEach(el => el.classList.remove('selected'));
        if (type === 'SEMESTER_BASED') {
            document.getElementById('opt-semester').classList.add('selected');
            document.getElementById('semestersCountGroup').style.display = 'block';
        } else {
            document.getElementById('opt-subject').classList.add('selected');
            document.getElementById('semestersCountGroup').style.display = 'none';
        }
    }
    </script>
    @endpush
</x-admin-layout>
