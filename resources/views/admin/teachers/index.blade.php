<x-admin-layout>
    <x-slot name="title">Teachers</x-slot>

    <div class="page-header">
        <div>
            <h1>Teachers</h1>
            <p>Manage faculty members, job info, and subject assignments</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addTeacherModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Teacher
            </button>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div style="display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap">
        <div class="card" style="padding:16px 22px;flex:1;min-width:140px;display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(59,130,246,.1);display:flex;align-items:center;justify-content:center;color:#3b82f6">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div><div class="stat-value">{{ $teachers->count() }}</div><div class="stat-label">Total Teachers</div></div>
        </div>
        <div class="card" style="padding:16px 22px;flex:1;min-width:140px;display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(16,185,129,.1);display:flex;align-items:center;justify-content:center;color:#10b981">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="stat-value">{{ $teachers->where('is_active', true)->count() }}</div><div class="stat-label">Active</div></div>
        </div>
        <div class="card" style="padding:16px 22px;flex:1;min-width:140px;display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(239,68,68,.1);display:flex;align-items:center;justify-content:center;color:#ef4444">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="stat-value">{{ $teachers->where('is_active', false)->count() }}</div><div class="stat-label">Inactive</div></div>
        </div>
    </div>

    <div class="card" style="overflow:visible">
        {{-- Search Toolbar --}}
        <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <div class="search-box" style="flex:1;min-width:220px">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="teacherSearch" placeholder="Search by name, ID, designation, phone..." oninput="searchTeachers(this.value)">
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <select id="statusFilter" class="form-control" style="width:auto;font-size:13px;padding:8px 12px" onchange="searchTeachers(document.getElementById('teacherSearch').value)">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <span id="teacherCount" style="font-size:13px;color:var(--text-muted);white-space:nowrap"></span>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrapper" style="overflow:visible">
            <table id="teacherTable">
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
                    <tr data-searchable="{{ strtolower($teacher->employee_id.' '.$teacher->name.' '.$teacher->designation.' '.$teacher->phone.' '.$teacher->email.' '.$teacher->department) }}"
                        data-active="{{ $teacher->is_active ? '1' : '0' }}">
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
                            <div class="dropdown" style="display:inline-block">
                                <button class="btn btn-outline btn-sm" onclick="toggleDropdown('tact-{{ $teacher->id }}')" style="gap:4px">
                                    Actions
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="dropdown-menu" id="tact-{{ $teacher->id }}" style="right:0;min-width:160px">
                                    <a href="{{ route('admin.teachers.id-card', $teacher) }}" target="_blank" class="dropdown-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
                                        ID Card
                                    </a>
                                    <button class="dropdown-item" onclick="openEditTeacherModal({{ $teacher->id }});toggleDropdown('tact-{{ $teacher->id }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit
                                    </button>
                                    <button class="dropdown-item" onclick="openAssignModal({{ $teacher->id }}, '{{ $teacher->name }}');toggleDropdown('tact-{{ $teacher->id }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        Assign Subject
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('Delete teacher profile?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item danger" style="width:100%;border:none;background:none;text-align:left;color:var(--red)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
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
        <div class="modal modal-lg" style="max-height:90vh;overflow-y:auto;display:flex;flex-direction:column">
            <div class="modal-header">
                <span class="modal-title">Add New Teacher</span>
                <button class="modal-close" onclick="closeModal('addTeacherModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.teachers.store') }}">
                @csrf
                <div class="modal-body">

                    {{-- Basic Info --}}
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin-bottom:14px">👤 Basic Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. মাওলানা আবদুল্লাহ" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="+8801711...">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="teacher@iom.edu.bd">
                        </div>
                        <div class="form-group">
                            <label>Login Password</label>
                            <div style="position:relative;display:flex;align-items:center">
                                <input type="password" id="add_teacher_password" name="password" class="form-control" style="padding-right:44px" placeholder="Leave empty to use phone number">
                                <button type="button" onclick="togglePasswordVisibility('add_teacher_password', this)" style="position:absolute;right:8px;background:transparent;border:none;padding:6px;cursor:pointer;color:#64748b">
                                    <svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-hide" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.03 10.03 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Marital Status</label>
                            <select name="marital_status" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>National ID</label>
                            <input type="text" name="national_id" class="form-control" placeholder="NID Number">
                        </div>
                        <div class="form-group">
                            <label>Religion</label>
                            <select name="religion" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach(\App\Models\Religion::active()->get() as $rel)
                                <option value="{{ $rel->name }}">{{ $rel->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Job Info --}}
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin:20px 0 14px">💼 Job Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Designation</label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g. Senior Instructor">
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" name="department" class="form-control" placeholder="e.g. Fiqh Department">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Qualification</label>
                            <input type="text" name="qualification" class="form-control" placeholder="e.g. Dawra-e-Hadith, B.A Hons">
                        </div>
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select name="employment_type" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Volunteer">Volunteer</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monthly Salary (৳)</label>
                            <input type="number" name="salary" class="form-control" placeholder="0.00" min="0">
                        </div>
                        <div class="form-group">
                            <label>Joining Date</label>
                            <input type="date" name="joining_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Bio / Short Introduction</label>
                        <textarea name="bio" class="form-control" rows="2" placeholder="Brief background of the teacher..."></textarea>
                    </div>

                    {{-- Emergency Contact --}}
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin:20px 0 14px">🆘 Emergency Contact</div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control" placeholder="Full Name">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control" placeholder="Phone">
                        </div>
                        <div class="form-group">
                            <label>Relation</label>
                            <input type="text" name="emergency_contact_relation" class="form-control" placeholder="e.g. Wife, Father">
                        </div>
                    </div>

                    {{-- Present Address --}}
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin:20px 0 14px">🏠 Present Address</div>
                    <div class="form-group">
                        <label>House / Street / Village</label>
                        <input type="text" name="present_house" class="form-control" placeholder="House, Street, Village">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Post Office</label>
                            <input type="text" name="present_post_office" class="form-control" placeholder="Post Office">
                        </div>
                        <div class="form-group">
                            <label>Police Station (Thana)</label>
                            <input type="text" name="present_police_station" class="form-control" placeholder="Thana">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Division</label>
                            <select name="present_division_id" class="form-control" onchange="teacherLoadDistricts('present', this.value)">
                                <option value="">-- Select Division --</option>
                                @foreach(\App\Models\Division::orderBy('name')->get() as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>District</label>
                            <select name="present_district_id" id="teacher_present_district" class="form-control">
                                <option value="">-- Select District --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Permanent Address --}}
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin:20px 0 14px;display:flex;align-items:center;justify-content:space-between">
                        🏡 Permanent Address
                        <label class="form-check" style="text-transform:none;font-size:12px;font-weight:400;letter-spacing:0">
                            <input type="checkbox" id="teacher_same_address" onchange="teacherSameAddress(this)">
                            Same as Present
                        </label>
                    </div>
                    <div id="teacher_permanent_fields">
                        <div class="form-group">
                            <label>House / Street / Village</label>
                            <input type="text" name="permanent_house" id="t_perm_house" class="form-control" placeholder="House, Street, Village">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Post Office</label>
                                <input type="text" name="permanent_post_office" id="t_perm_po" class="form-control" placeholder="Post Office">
                            </div>
                            <div class="form-group">
                                <label>Police Station</label>
                                <input type="text" name="permanent_police_station" id="t_perm_ps" class="form-control" placeholder="Thana">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Division</label>
                                <select name="permanent_division_id" id="t_perm_div" class="form-control" onchange="teacherLoadDistricts('permanent', this.value)">
                                    <option value="">-- Select Division --</option>
                                    @foreach(\App\Models\Division::orderBy('name')->get() as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>District</label>
                                <select name="permanent_district_id" id="teacher_permanent_district" class="form-control">
                                    <option value="">-- Select District --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:16px">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" value="1" checked> Active Faculty
                        </label>
                    </div>
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

    <!-- Edit Teacher Modal -->
    <div class="modal-overlay" id="editTeacherModal">
        <div class="modal modal-lg" style="max-height:90vh;overflow-y:auto;display:flex;flex-direction:column">
            <div class="modal-header">
                <span class="modal-title" id="editTeacherTitle">Edit Teacher</span>
                <button class="modal-close" onclick="closeModal('editTeacherModal')">&times;</button>
            </div>
            <form method="POST" id="editTeacherForm">
                @csrf @method('PUT')
                <div class="modal-body">

                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin-bottom:14px">👤 Basic Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="name" id="et_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" id="et_phone" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" id="et_email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Reset / Change Password</label>
                            <div style="position:relative;display:flex;align-items:center">
                                <input type="password" id="edit_teacher_password" name="password" class="form-control" style="padding-right:44px" placeholder="Leave blank to keep current password">
                                <button type="button" onclick="togglePasswordVisibility('edit_teacher_password', this)" style="position:absolute;right:8px;background:transparent;border:none;padding:6px;cursor:pointer;color:#64748b">
                                    <svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-hide" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.03 10.03 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" id="et_gender" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Marital Status</label>
                            <select name="marital_status" id="et_marital" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>National ID</label>
                            <input type="text" name="national_id" id="et_nid" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Religion</label>
                            <select name="religion" id="et_religion" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach(\App\Models\Religion::active()->get() as $rel)
                                <option value="{{ $rel->name }}">{{ $rel->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin:20px 0 14px">💼 Job Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Designation</label>
                            <input type="text" name="designation" id="et_designation" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" name="department" id="et_department" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Qualification</label>
                            <input type="text" name="qualification" id="et_qualification" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select name="employment_type" id="et_employment" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Volunteer">Volunteer</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monthly Salary (৳)</label>
                            <input type="number" name="salary" id="et_salary" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label>Joining Date</label>
                            <input type="date" name="joining_date" id="et_joining" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Bio / Short Introduction</label>
                        <textarea name="bio" id="et_bio" class="form-control" rows="2"></textarea>
                    </div>

                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);letter-spacing:.05em;border-bottom:1px solid #dbeafe;padding-bottom:6px;margin:20px 0 14px">🆘 Emergency Contact</div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" name="emergency_contact_name" id="et_em_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" id="et_em_phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Relation</label>
                            <input type="text" name="emergency_contact_relation" id="et_em_rel" class="form-control">
                        </div>
                    </div>

                    <div style="margin-top:16px">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" id="et_active" value="1"> Active Faculty
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editTeacherModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Teacher</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    // Teacher data map for edit modal
    const teacherData = {
        @foreach($teachers as $t)
        {{ $t->id }}: {
            name: @json($t->name),
            phone: @json($t->phone),
            email: @json($t->email),
            date_of_birth: @json($t->date_of_birth ? \Carbon\Carbon::parse($t->date_of_birth)->format('Y-m-d') : ''),
            gender: @json($t->gender),
            marital_status: @json($t->marital_status),
            national_id: @json($t->national_id),
            religion: @json($t->religion),
            designation: @json($t->designation),
            department: @json($t->department),
            qualification: @json($t->qualification),
            employment_type: @json($t->employment_type),
            salary: @json($t->salary),
            joining_date: @json($t->joining_date ? \Carbon\Carbon::parse($t->joining_date)->format('Y-m-d') : ''),
            bio: @json($t->bio),
            emergency_contact_name: @json($t->emergency_contact_name),
            emergency_contact_phone: @json($t->emergency_contact_phone),
            emergency_contact_relation: @json($t->emergency_contact_relation),
            is_active: {{ $t->is_active ? 'true' : 'false' }},
        },
        @endforeach
    };

    function setVal(id, val) {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.type === 'checkbox') el.checked = !!val;
        else el.value = val ?? '';
    }

    function openEditTeacherModal(teacherId) {
        const t = teacherData[teacherId];
        if (!t) return;
        document.getElementById('editTeacherTitle').innerText = 'Edit — ' + t.name;
        document.getElementById('editTeacherForm').action = '/admin/teachers/' + teacherId;
        setVal('et_name', t.name);
        setVal('et_phone', t.phone);
        setVal('et_email', t.email);
        setVal('et_dob', t.date_of_birth);
        setVal('et_gender', t.gender);
        setVal('et_marital', t.marital_status);
        setVal('et_nid', t.national_id);
        setVal('et_religion', t.religion);
        setVal('et_designation', t.designation);
        setVal('et_department', t.department);
        setVal('et_qualification', t.qualification);
        setVal('et_employment', t.employment_type);
        setVal('et_salary', t.salary);
        setVal('et_joining', t.joining_date);
        setVal('et_bio', t.bio);
        setVal('et_em_name', t.emergency_contact_name);
        setVal('et_em_phone', t.emergency_contact_phone);
        setVal('et_em_rel', t.emergency_contact_relation);
        setVal('et_active', t.is_active);
        openModal('editTeacherModal');
    }

    function openAssignModal(teacherId, teacherName) {
        document.getElementById('assignTitle').innerText = 'Assign Subject to ' + teacherName;
        document.getElementById('assignForm').action = '/admin/teachers/' + teacherId + '/subjects';
        openModal('assignSubjectModal');
    }

    function teacherLoadDistricts(type, divisionId) {
        const selectId = type === 'present' ? 'teacher_present_district' : 'teacher_permanent_district';
        const sel = document.getElementById(selectId);
        sel.innerHTML = '<option value="">Loading...</option>';
        if (!divisionId) { sel.innerHTML = '<option value="">-- Select District --</option>'; return; }
        fetch('/api/districts?division_id=' + divisionId)
            .then(r => r.json())
            .then(data => {
                sel.innerHTML = '<option value="">-- Select District --</option>';
                data.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.name}</option>`);
            });
    }

    function teacherSameAddress(cb) {
        document.getElementById('teacher_permanent_fields').style.opacity = cb.checked ? '.4' : '1';
        document.getElementById('teacher_permanent_fields').style.pointerEvents = cb.checked ? 'none' : 'auto';
    }

    // Live Search + Filter
    function searchTeachers(q) {
        q = (q || '').toLowerCase().trim();
        const statusVal = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('#teacherTable tbody tr[data-searchable]');
        let visible = 0;
        rows.forEach(row => {
            const text    = row.dataset.searchable;
            const active  = row.dataset.active;
            const matchQ  = !q || text.includes(q);
            const matchS  = !statusVal
                || (statusVal === 'active'   && active === '1')
                || (statusVal === 'inactive' && active === '0');
            const show = matchQ && matchS;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const cnt = document.getElementById('teacherCount');
        cnt.textContent = (q || statusVal) ? `Showing ${visible} of ${rows.length}` : `${rows.length} teachers`;
    }
    document.addEventListener('DOMContentLoaded', () => searchTeachers(''));
    </script>
    @endpush
</x-admin-layout>
