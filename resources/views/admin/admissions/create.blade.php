<x-admin-layout>
    <x-slot name="title">New Admission Form (Admin Entry)</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.admissions.index') }}">← Back to Admissions List</a>
            </div>
            <h1>New Student Admission Form</h1>
            <p>Admin manual entry for new applicant registration and lead intake</p>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:20px">
        <strong>Validation Errors:</strong>
        <ul style="margin-top:4px;margin-left:16px">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.admissions.store') }}">
        @csrf

        {{-- ── 1. Program & Lead Source ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <span class="card-title">1. Course & Admission Setup</span>
            </div>
            <div class="card-body">
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Interested Course / Program <span class="required">*</span></label>
                        <select name="interested_course_id" class="form-control" required>
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" {{ old('interested_course_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ str_replace('_',' ',$c->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Target Admission Batch</label>
                        <select name="batch_id" class="form-control">
                            <option value="">-- Select Open Batch --</option>
                            @foreach($activeBatches as $b)
                                @if($b->is_admission_open)
                                    <option value="{{ $b->id }}" {{ old('batch_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} ({{ $b->batch_code }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Academic Session</label>
                        <select name="academic_session_id" class="form-control">
                            <option value="">-- Select Session --</option>
                            @foreach($sessions as $s)
                                <option value="{{ $s->id }}" {{ old('academic_session_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Lead Source</label>
                        <select name="lead_source" class="form-control">
                            <option value="Direct" {{ old('lead_source') == 'Direct' ? 'selected' : '' }}>Direct / Campus Visit</option>
                            <option value="Website" {{ old('lead_source') == 'Website' ? 'selected' : '' }}>Website Form</option>
                            <option value="Social Media" {{ old('lead_source') == 'Social Media' ? 'selected' : '' }}>Facebook / Social</option>
                            <option value="Referral" {{ old('lead_source') == 'Referral' ? 'selected' : '' }}>Student Referral</option>
                            <option value="Call" {{ old('lead_source') == 'Call' ? 'selected' : '' }}>Phone Call Inquiry</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Scholarship / Waiver %</label>
                        <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', 0) }}" min="0" max="100">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 2. Personal Information ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <span class="card-title">2. Personal Information</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Applicant Name <span class="required">*</span></label>
                        <input type="text" name="applicant_name" class="form-control" value="{{ old('applicant_name') }}" placeholder="e.g. Tanvir Hossain" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="required">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="e.g. 01711000000" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="applicant@email.com">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">-- Select Gender --</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Blood Group</label>
                        <select name="blood_group_id" class="form-control">
                            <option value="">-- Select Blood Group --</option>
                            @foreach($bloodGroups as $bg)
                                <option value="{{ $bg->id }}" {{ old('blood_group_id') == $bg->id ? 'selected' : '' }}>{{ $bg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <select name="religion_id" class="form-control">
                            <option value="">-- Select Religion --</option>
                            @foreach($religions as $rel)
                                <option value="{{ $rel->id }}" {{ old('religion_id') == $rel->id ? 'selected' : '' }}>{{ $rel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>National ID No</label>
                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id') }}" placeholder="10/13/17 digit NID">
                    </div>
                    <div class="form-group">
                        <label>Passport No</label>
                        <input type="text" name="passport_no" class="form-control" value="{{ old('passport_no') }}" placeholder="Passport Number">
                    </div>
                    <div class="form-group">
                        <label>Birth Certificate No</label>
                        <input type="text" name="birth_certificate_no" class="form-control" value="{{ old('birth_certificate_no') }}" placeholder="17 digit No">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nationality</label>
                        <select name="nationality" class="form-control">
                            <option value="Bangladeshi" {{ old('nationality', 'Bangladeshi') == 'Bangladeshi' ? 'selected' : '' }}>Bangladeshi</option>
                            <option value="Other" {{ old('nationality') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Joining Device Type</label>
                        <select name="device_type" class="form-control">
                            <option value="">-- Select Device --</option>
                            <option value="Desktop/Laptop PC" {{ old('device_type') == 'Desktop/Laptop PC' ? 'selected' : '' }}>Desktop/Laptop PC</option>
                            <option value="Mobile" {{ old('device_type') == 'Mobile' ? 'selected' : '' }}>Mobile</option>
                            <option value="Tablet" {{ old('device_type') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. Education & Job Records ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <span class="card-title">3. Occupation & Education Records</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Present Occupation</label>
                        <select name="occupation" class="form-control">
                            <option value="">-- Select Occupation --</option>
                            @foreach(['Student','Service Holder','Business','Unemployed','Other'] as $occ)
                                <option value="{{ $occ }}" {{ old('occupation') == $occ ? 'selected' : '' }}>{{ $occ }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Highest Educational Qualification</label>
                        <select name="education_qualification" class="form-control">
                            <option value="">-- Select Qualification --</option>
                            @foreach(['Below SSC','SSC / Equivalent','HSC / Equivalent','Bachelor Equivalent','Master Equivalent','Other'] as $eq)
                                <option value="{{ $eq }}" {{ old('education_qualification') == $eq ? 'selected' : '' }}>{{ $eq }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SSC Section --}}
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin:16px 0 12px">SSC / Equivalent Record</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>SSC School Name</label>
                        <input type="text" name="ssc_school" class="form-control" value="{{ old('ssc_school') }}" placeholder="School Name">
                    </div>
                    <div class="form-group">
                        <label>SSC Board</label>
                        <select name="ssc_board" class="form-control">
                            <option value="">-- Select Board --</option>
                            @foreach(['Dhaka','Chittagong','Rajshahi','Jessore','Comilla','Barisal','Sylhet','Dinajpur','Mymensingh','Madrasah','Technical','Other'] as $b)
                                <option value="{{ $b }}" {{ old('ssc_board') == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>SSC Passing Year</label>
                        <input type="number" name="ssc_year" class="form-control" value="{{ old('ssc_year') }}" placeholder="e.g. 2018">
                    </div>
                    <div class="form-group">
                        <label>SSC GPA</label>
                        <input type="number" step="0.01" name="ssc_gpa" class="form-control" value="{{ old('ssc_gpa') }}" placeholder="5.00">
                    </div>
                </div>

                {{-- HSC Section --}}
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin:16px 0 12px">HSC / Equivalent Record</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>HSC College Name</label>
                        <input type="text" name="hsc_college" class="form-control" value="{{ old('hsc_college') }}" placeholder="College Name">
                    </div>
                    <div class="form-group">
                        <label>HSC Board</label>
                        <select name="hsc_board" class="form-control">
                            <option value="">-- Select Board --</option>
                            @foreach(['Dhaka','Chittagong','Rajshahi','Jessore','Comilla','Barisal','Sylhet','Dinajpur','Mymensingh','Madrasah','Technical','Other'] as $b)
                                <option value="{{ $b }}" {{ old('hsc_board') == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>HSC Passing Year</label>
                        <input type="number" name="hsc_year" class="form-control" value="{{ old('hsc_year') }}" placeholder="e.g. 2020">
                    </div>
                    <div class="form-group">
                        <label>HSC GPA</label>
                        <input type="number" step="0.01" name="hsc_gpa" class="form-control" value="{{ old('hsc_gpa') }}" placeholder="5.00">
                    </div>
                </div>

                {{-- Higher Ed Section --}}
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin:16px 0 12px">Higher Education (Optional)</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>University / Institute</label>
                        <input type="text" name="university_name" class="form-control" value="{{ old('university_name') }}" placeholder="University Name">
                    </div>
                    <div class="form-group">
                        <label>Department / Subject</label>
                        <input type="text" name="department_name" class="form-control" value="{{ old('department_name') }}" placeholder="e.g. CSE, Islamic Studies">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 4. Guardian Details ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <span class="card-title">4. Guardian Information</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}" placeholder="Father/Mother/Guardian Name">
                    </div>
                    <div class="form-group">
                        <label>Guardian Phone</label>
                        <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone') }}" placeholder="01811000000">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 5. Addresses ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <span class="card-title">5. Address Details</span>
            </div>
            <div class="card-body">
                {{-- Present Address --}}
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:12px">Present Address</div>
                <div class="form-group">
                    <label>House / Street / Village</label>
                    <input type="text" name="present_house" id="present_house" class="form-control" value="{{ old('present_house') }}" placeholder="House, Road, Village">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Post Office</label>
                        <input type="text" name="present_post_office" id="present_post_office" class="form-control" value="{{ old('present_post_office') }}" placeholder="Post Office">
                    </div>
                    <div class="form-group">
                        <label>Police Station (Thana)</label>
                        <input type="text" name="present_police_station" id="present_police_station" class="form-control" value="{{ old('present_police_station') }}" placeholder="Police Station">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Division</label>
                        <select name="present_division_id" id="present_division_id" class="form-control" onchange="loadAdminDistricts('present')">
                            <option value="">-- Select Division --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}" {{ old('present_division_id') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>District</label>
                        <select name="present_district_id" id="present_district_id" class="form-control">
                            <option value="">-- Select District --</option>
                        </select>
                    </div>
                </div>

                {{-- Permanent Address --}}
                <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #dbeafe;padding-bottom:4px;margin:20px 0 12px">
                    <span style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue)">Permanent Address</span>
                    <label class="form-check" style="font-size:13px;cursor:pointer">
                        <input type="checkbox" id="same_as_present" name="same_as_present" value="1" onchange="toggleAdminPermanent(this)">
                        Same as Present Address
                    </label>
                </div>

                <div id="permanent-fields">
                    <div class="form-group">
                        <label>House / Street / Village</label>
                        <input type="text" name="permanent_house" id="permanent_house" class="form-control" value="{{ old('permanent_house') }}" placeholder="House, Road, Village">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Post Office</label>
                            <input type="text" name="permanent_post_office" id="permanent_post_office" class="form-control" value="{{ old('permanent_post_office') }}" placeholder="Post Office">
                        </div>
                        <div class="form-group">
                            <label>Police Station (Thana)</label>
                            <input type="text" name="permanent_police_station" id="permanent_police_station" class="form-control" value="{{ old('permanent_police_station') }}" placeholder="Police Station">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Division</label>
                            <select name="permanent_division_id" id="permanent_division_id" class="form-control" onchange="loadAdminDistricts('permanent')">
                                <option value="">-- Select Division --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}" {{ old('permanent_division_id') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>District</label>
                            <select name="permanent_district_id" id="permanent_district_id" class="form-control">
                                <option value="">-- Select District --</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 6. Admin Review Notes ── --}}
        <div class="card" style="margin-bottom:24px">
            <div class="card-header">
                <span class="card-title">6. Admin Review Notes</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Reviewer / Intake Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Add internal review notes, intake comments, or special instructions...">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between">
                <a href="{{ route('admin.admissions.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">Submit & Create Admission →</button>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
    function loadAdminDistricts(prefix) {
        const divId = document.getElementById(prefix + '_division_id').value;
        const distSel = document.getElementById(prefix + '_district_id');
        distSel.innerHTML = '<option value="">Loading...</option>';
        if (!divId) { distSel.innerHTML = '<option value="">-- Select District --</option>'; return; }
        fetch('/api/districts?division_id=' + divId)
            .then(r => r.json())
            .then(data => {
                distSel.innerHTML = '<option value="">-- Select District --</option>';
                data.forEach(d => {
                    distSel.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                });
            });
    }

    function toggleAdminPermanent(cb) {
        const fields = document.getElementById('permanent-fields');
        if (cb.checked) {
            document.getElementById('permanent_house').value          = document.getElementById('present_house').value;
            document.getElementById('permanent_post_office').value    = document.getElementById('present_post_office').value;
            document.getElementById('permanent_police_station').value = document.getElementById('present_police_station').value;
            const presDiv  = document.getElementById('present_division_id').value;
            const presDist = document.getElementById('present_district_id').value;
            const perDiv   = document.getElementById('permanent_division_id');
            const perDist  = document.getElementById('permanent_district_id');
            perDiv.value = presDiv;
            perDist.innerHTML = document.getElementById('present_district_id').innerHTML;
            perDist.value = presDist;
            fields.style.opacity = '.4';
            fields.style.pointerEvents = 'none';
        } else {
            fields.style.opacity = '1';
            fields.style.pointerEvents = 'auto';
        }
    }
    </script>
    @endpush
</x-admin-layout>
