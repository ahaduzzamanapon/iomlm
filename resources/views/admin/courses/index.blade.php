<x-admin-layout>
    <x-slot name="title">Courses & Setup</x-slot>

    <style>
        .dropdown { position: relative; display: inline-block; }
        .dropdown-menu {
            position: absolute;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            min-width: 175px;
            z-index: 9999;
            display: none;
            overflow: hidden;
            padding: 4px 0;
        }
        .dropdown-menu.open { display: block !important; }
        .table-wrapper:has(.dropdown-menu.open),
        .card:has(.dropdown-menu.open),
        td:has(.dropdown-menu.open) {
            overflow: visible !important;
        }
    </style>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Courses Management</h1>
            <p>Configure Subject-Based and Semester-Based academic courses</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addCourseModal')">
                New Course
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:70px">Code</th>
                        <th>Course Name</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Semesters</th>
                        <th>Fee (ভর্তি / রি-এডমিশন)</th>
                        <th>Poor Fund</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td>
                            <span class="badge" style="background:#f1f5f9;color:#0f172a;border:1px solid #cbd5e1;font-family:monospace;font-size:13px;font-weight:800;letter-spacing:1px">
                                {{ $course->formatted_code }}
                            </span>
                        </td>
                        <td class="td-primary">
                            <a href="{{ route('admin.courses.show', $course) }}" style="font-weight:600;color:var(--blue)">{{ $course->name }}</a>
                        </td>
                        <td>
                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;font-weight:600">
                                {{ $course->department ?: 'BA in Dawah and Islamic Studies' }}
                            </span>
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
                            <div style="font-size:13px;line-height:1.4">
                                <span title="ভর্তি ফি">ভর্তি: <strong>৳{{ number_format($course->admission_fee, 2) }}</strong></span><br>
                                <span title="রি-এডমিশন ফি" style="color:var(--warning, #b45309)">রি-এডমিশন: <strong>৳{{ number_format($course->readmission_fee ?? 0, 2) }}</strong></span>
                            </div>
                        </td>
                        <td>
                            @if($course->is_poor_fund_applicable)
                                <span class="badge badge-active no-dot" title="পুওর ফান্ড স্কলারশিপ প্রযোজ্য"><i class="fa-solid fa-hand-holding-heart"></i> প্রযোজ্য</span>
                            @else
                                <span class="badge badge-secondary no-dot" title="পুওর ফান্ড প্রযোজ্য নয়"><i class="fa-solid fa-ban"></i> প্রযোজ্য নয়</span>
                            @endif
                        </td>
                        <td>
                            @if($course->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="dropdown" style="display:inline-block;position:relative">
                                <button type="button" class="btn btn-outline btn-sm" onclick="toggleDropdown('cact-{{ $course->id }}')" style="gap:6px;display:inline-flex;align-items:center;font-family:'Kalpurush',sans-serif">
                                    অ্যাকশন (Actions) <i class="fa-solid fa-chevron-down" style="font-size:10px"></i>
                                </button>
                                <div class="dropdown-menu" id="cact-{{ $course->id }}" style="right:0;min-width:170px;text-align:left;font-family:'Kalpurush',sans-serif">
                                    <a href="{{ route('admin.courses.show', $course) }}" class="dropdown-item">
                                        <i class="fa-solid fa-sliders" style="color:#047857;width:16px"></i>
                                        Configure (কনফিগার)
                                    </a>
                                    <button type="button" class="dropdown-item" onclick='openEditCourseModal(@json($course));toggleDropdown("cact-{{ $course->id }}")'>
                                        <i class="fa-solid fa-pen-to-square" style="color:#2563eb;width:16px"></i>
                                        Edit (এডিট)
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই কোর্সটি মুছে ফেলতে চান?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item danger" style="width:100%;border:none;background:none;text-align:left;color:#dc2626">
                                            <i class="fa-solid fa-trash" style="color:#dc2626;width:16px"></i>
                                            Delete (ডিলিট)
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">No courses found. Click "New Course" to create one.</td></tr>
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
                        <input type="text" name="name" class="form-control" placeholder="e.g. Alim Preparatory Course" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Course Code (২ ডিজিট কোড) <span class="required">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. 01, 02, 15" maxlength="4" style="font-family:monospace;font-weight:700">
                            <small style="color:var(--text-muted);font-size:12px">এই কোডটি শিক্ষার্থীর আইডির ৫ম ও ৬ষ্ঠ ডিজিটে যুক্ত হবে।</small>
                        </div>
                        <div class="form-group">
                            <label>Department (বিভাগ) <span class="required">*</span></label>
                            <select name="department" class="form-control">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
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

                    <div class="form-row">
                        <div class="form-group">
                            <label>Admission Fee (৳)</label>
                            <input type="number" name="admission_fee" class="form-control" value="0" min="0" step="0.01" placeholder="e.g. 5000.00">
                            <small style="color:var(--text-muted);font-size:12px">ভর্তি ফি (Default admission fee)</small>
                        </div>
                        <div class="form-group">
                            <label>Re-admission Fee (৳)</label>
                            <input type="number" name="readmission_fee" class="form-control" value="0" min="0" step="0.01" placeholder="e.g. 3000.00">
                            <small style="color:var(--text-muted);font-size:12px">রি-এডমিশন ফি (>২ বিষয়ে ফেল করলে)</small>
                        </div>
                    </div>

                    <label class="form-check" style="margin-bottom:8px">
                        <input type="checkbox" name="is_poor_fund_applicable" value="1" checked>
                        <strong>পুওর ফান্ড প্রযোজ্য (Poor Fund Applicable)</strong>
                    </label>
                    <small style="display:block;color:var(--text-muted);font-size:12px;margin-bottom:14px">
                        অন থাকলে এই কোর্সের জন্য শিক্ষার্থীরা পুওর ফান্ড / স্কলারশিপ আবেদন করতে পারবে।
                    </small>

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

                    <div class="form-row">
                        <div class="form-group">
                            <label>Course Code (২ ডিজিট কোড) <span class="required">*</span></label>
                            <input type="text" name="code" id="edit_course_code" class="form-control" placeholder="e.g. 01, 02, 15" maxlength="4" style="font-family:monospace;font-weight:700">
                            <small style="color:var(--text-muted);font-size:12px">শিক্ষার্থীর আইডির ৫ম ও ৬ষ্ঠ ডিজিট।</small>
                        </div>
                        <div class="form-group">
                            <label>Department (বিভাগ) <span class="required">*</span></label>
                            <select name="department" id="edit_course_department" class="form-control">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
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

                    <div class="form-row">
                        <div class="form-group">
                            <label>Admission Fee (৳)</label>
                            <input type="number" name="admission_fee" id="edit_course_admission_fee" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Re-admission Fee (৳)</label>
                            <input type="number" name="readmission_fee" id="edit_course_readmission_fee" class="form-control" min="0" step="0.01">
                        </div>
                    </div>

                    <label class="form-check" style="margin-bottom:8px">
                        <input type="checkbox" name="is_poor_fund_applicable" id="edit_course_is_poor_fund_applicable" value="1">
                        <strong>পুওর ফান্ড প্রযোজ্য (Poor Fund Applicable)</strong>
                    </label>
                    <small style="display:block;color:var(--text-muted);font-size:12px;margin-bottom:14px">
                        অন থাকলে এই কোর্সের জন্য শিক্ষার্থীরা পুওর ফান্ড / স্কলারশিপ আবেদন করতে পারবে।
                    </small>

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
        document.getElementById('edit_course_code').value = course.code || ('0' + (course.id % 100)).slice(-2);
        document.getElementById('edit_course_department').value = course.department || 'BA in Dawah and Islamic Studies';
        document.getElementById('edit_course_type').value = course.type;
        document.getElementById('edit_course_duration_value').value = course.duration_value;
        document.getElementById('edit_course_duration_unit').value = course.duration_unit;
        document.getElementById('edit_course_admission_fee').value = course.admission_fee || 0;
        document.getElementById('edit_course_readmission_fee').value = course.readmission_fee || 0;
        document.getElementById('edit_course_is_poor_fund_applicable').checked = (course.is_poor_fund_applicable !== false && course.is_poor_fund_applicable !== 0);
        document.getElementById('edit_course_is_active').checked = !!course.is_active;
        openModal('editCourseModal');
    }
    </script>
    @endpush
</x-admin-layout>
