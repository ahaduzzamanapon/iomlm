<x-admin-layout>
    <x-slot name="title">Courses & Setup</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Courses Management</h1>
            <p>Configure Subject-Based and Semester-Based academic courses</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addCourseModal')">
                <i class="fa-solid fa-plus"></i>
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
                            <button class="btn btn-outline btn-sm" onclick='openEditCourseModal(@json($course))'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-sliders"></i> Configure</a>
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
                                <div class="type-option-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                                <div class="type-option-label">Semester Based</div>
                                <div class="type-option-desc">Subjects are bound to semesters</div>
                            </label>
                            <label class="type-option" id="opt-subject" onclick="selectType('SUBJECT_BASED')">
                                <input type="radio" name="type" value="SUBJECT_BASED">
                                <div class="type-option-icon"><i class="fa-solid fa-book"></i></div>
                                <div class="type-option-label">Subject Based</div>
                                <div class="type-option-desc">Direct subject selection</div>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Duration Value <span class="required">*</span></label>
                            <input type="number" name="duration_value" class="form-control" value="1" min="0.5" step="0.5" required>
                            <small style="color:var(--text-muted);font-size:12px">Decimal allowed — e.g. 1.5 = 18 months</small>
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

                    <div class="form-group">
                        <label>Admission Fee (৳)</label>
                        <input type="number" name="admission_fee" class="form-control" value="0" min="0" step="0.01" placeholder="e.g. 5000.00">
                        <small style="color:var(--text-muted);font-size:12px">Admission fee for this course (overridable per batch)</small>
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

    <!-- Edit Course Modal -->
    <div class="modal-overlay" id="editCourseModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Academic Course</span>
                <button class="modal-close" onclick="closeModal('editCourseModal')">&times;</button>
            </div>
            <form method="POST" id="editCourseForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Course Name <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_course_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Course Type <span class="required">*</span></label>
                        <select name="type" id="edit_course_type" class="form-control" required>
                            <option value="SEMESTER_BASED">Semester Based</option>
                            <option value="SUBJECT_BASED">Subject Based</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Duration Value <span class="required">*</span></label>
                            <input type="number" name="duration_value" id="edit_course_duration_value" class="form-control" min="0.5" step="0.5" required>
                        </div>
                        <div class="form-group">
                            <label>Duration Unit <span class="required">*</span></label>
                            <select name="duration_unit" id="edit_course_duration_unit" class="form-control" required>
                                <option value="YEAR">Years</option>
                                <option value="MONTH">Months</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Admission Fee (৳)</label>
                        <input type="number" name="admission_fee" id="edit_course_admission_fee" class="form-control" min="0" step="0.01">
                    </div>

                    <label class="form-check">
                        <input type="checkbox" name="is_active" id="edit_course_is_active" value="1"> Active Course
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editCourseModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Course</button>
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

    function openEditCourseModal(course) {
        document.getElementById('editCourseForm').action = '/admin/courses/' + course.id;
        document.getElementById('edit_course_name').value = course.name;
        document.getElementById('edit_course_type').value = course.type;
        document.getElementById('edit_course_duration_value').value = course.duration_value;
        document.getElementById('edit_course_duration_unit').value = course.duration_unit;
        document.getElementById('edit_course_admission_fee').value = course.admission_fee || 0;
        document.getElementById('edit_course_is_active').checked = !!course.is_active;
        openModal('editCourseModal');
    }
    </script>
    @endpush
</x-admin-layout>
