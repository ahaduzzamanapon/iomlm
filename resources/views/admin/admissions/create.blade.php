<x-admin-layout>
    <x-slot name="title">New Admission Form</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.admissions.index') }}">← Back to Admissions</a>
            </div>
            <h1>New Student Admission Form</h1>
            <p>Register applicant details and create initial admission attempt</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.admissions.store') }}">
        @csrf
        <div class="grid-2">
            <!-- Applicant Personal Information -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">1. Personal Information</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Tanvir Hossain" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="+8801711..." required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="applicant@email.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Blood Group</label>
                            <select name="blood_group" class="form-control">
                                <option value="">-- Select --</option>
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>National ID / Birth Reg</label>
                            <input type="text" name="national_id" class="form-control" placeholder="19 or 17 digit NID">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" placeholder="Full residential address..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Guardian & Academic Background -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">2. Course Selection & Academic Record</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Interested Course <span class="required">*</span></label>
                        <select name="interested_course_id" class="form-control" required>
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ str_replace('_',' ',$c->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Lead Source</label>
                            <select name="lead_source" class="form-control">
                                <option value="Direct">Direct / Campus Visit</option>
                                <option value="Website">Website Form</option>
                                <option value="Social Media">Facebook / Social</option>
                                <option value="Referral">Student Referral</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Discount / Waiver %</label>
                            <input type="number" name="discount_percent" class="form-control" value="0" min="0" max="100">
                        </div>
                    </div>

                    <div class="divider"></div>
                    <div style="font-size:13px;font-weight:600;margin-bottom:12px">Guardian Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Guardian Name</label>
                            <input type="text" name="guardian_name" class="form-control" placeholder="Father/Mother/Guardian">
                        </div>
                        <div class="form-group">
                            <label>Guardian Phone</label>
                            <input type="text" name="guardian_phone" class="form-control" placeholder="+8801811...">
                        </div>
                    </div>

                    <div class="divider"></div>
                    <div style="font-size:13px;font-weight:600;margin-bottom:12px">Academic Record (Optional)</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>SSC GPA</label>
                            <input type="number" step="0.01" name="ssc_gpa" class="form-control" placeholder="5.00">
                        </div>
                        <div class="form-group">
                            <label>HSC GPA</label>
                            <input type="number" step="0.01" name="hsc_gpa" class="form-control" placeholder="5.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Review Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Notes for review committee..."></textarea>
                    </div>
                </div>
                <div class="card-footer" style="text-align:right">
                    <button type="submit" class="btn btn-primary btn-lg">Submit Application →</button>
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>
