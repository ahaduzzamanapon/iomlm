<x-admin-layout>
    <x-slot name="title">Edit Student — {{ $student->name }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.students.show', $student) }}">← Back to Student Profile</a>
            </div>
            <h1>Edit Profile: {{ $student->name }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.students.update', $student) }}">
        @csrf @method('PUT')
        <div class="card" style="max-width:700px">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Student Status <span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            @foreach(['ACTIVE','PENDING','APPROVED','ABSENT','DROPPED','CANCELLED','TRANSFERRED','COMPLETED','GRADUATED'] as $st)
                                <option value="{{ $st }}" {{ $student->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number <span class="required">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Blood Group</label>
                        <input type="text" name="blood_group" class="form-control" value="{{ old('blood_group', $student->blood_group) }}">
                    </div>
                    <div class="form-group">
                        <label>National ID / Birth Reg</label>
                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}">
                    </div>
                    <div class="form-group">
                        <label>Guardian Phone</label>
                        <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $student->guardian_phone) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control">{{ old('address', $student->address) }}</textarea>
                </div>
            </div>
            <div class="card-footer" style="text-align:right">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
</x-admin-layout>
