<x-admin-layout>
    <x-slot name="title">শিক্ষার্থী তথ্য সম্পাদনা — {{ $student->name }}</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.students.show', $student) }}">← শিক্ষার্থী প্রোফাইলে ফিরে যান</a>
            </div>
            <h1>তথ্য সম্পাদনা: {{ $student->name }}</h1>
            <p>আইডি: <strong>{{ str_replace('-', '', $student->student_code ?? '—') }}</strong></p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.students.update', $student) }}" style="font-family:'Kalpurush',sans-serif">
        @csrf @method('PUT')
        <div class="card" style="max-width:850px;border-radius:12px">
            <div class="card-body">
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                    <div class="form-group">
                        <label style="font-weight:600">পূর্ণ নাম <span class="required" style="color:#dc2626">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600">শিক্ষার্থীর স্ট্যাটাস <span class="required" style="color:#dc2626">*</span></label>
                        <select name="status" class="form-control" required>
                            @foreach(['ACTIVE','PENDING','LEAD','ABSENT','DROPPED','CANCELLED','TRANSFERRED','COMPLETED','GRADUATED'] as $st)
                                <option value="{{ $st }}" {{ $student->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:16px;background:#f0fdf4;padding:12px 16px;border-radius:8px;border:1px solid #bbf7d0">
                    <label style="font-weight:700;color:#166534;display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:0">
                        <input type="checkbox" name="is_common_account" value="1" {{ old('is_common_account', $student->is_common_account) ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer">
                        <span>স্পেশাল কোর্স কমন/শেয়ার্ড অ্যাকাউন্ট (Common Shared Account)</span>
                    </label>
                    <p style="margin:4px 0 0 28px;font-size:12px;color:#15803d">
                        চিহ্নিত থাকলে একাধিক শিক্ষার্থী এই আইডি দিয়ে লগইন করে ক্লাস ও সামগ্রী দেখতে পারবে, কিন্তু তাদের পক্ষে প্রোফাইল তথ্য, পাসওয়ার্ড বা কোর্স পরিবর্তন করা নিষিদ্ধ থাকবে।
                    </p>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                    <div class="form-group">
                        <label style="font-weight:600">মোবাইল নম্বর <span class="required" style="color:#dc2626">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}" required>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600">ইমেইল ঠিকানা</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                    </div>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:14px">
                    <div class="form-group">
                        <label style="font-weight:600">লিঙ্গ</label>
                        <select name="gender" class="form-control">
                            <option value="MALE" {{ strtoupper($student->gender ?? '') === 'MALE' ? 'selected' : '' }}>পুরুষ (Male)</option>
                            <option value="FEMALE" {{ strtoupper($student->gender ?? '') === 'FEMALE' ? 'selected' : '' }}>মহিলা (Female)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600">জন্ম তারিখ</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600">রক্তের গ্রুপ</label>
                        <select name="blood_group" class="form-control">
                            <option value="">নির্বাচন করুন</option>
                            @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                <option value="{{ $bg }}" {{ $student->blood_group === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                    <div class="form-group">
                        <label style="font-weight:600">জাতীয় পরিচয়পত্র / জন্ম নিবন্ধন (NID)</label>
                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600">বর্তমান পেশা</label>
                        <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $student->occupation) }}">
                    </div>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                    <div class="form-group">
                        <label style="font-weight:600">পিতার নাম</label>
                        <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600">মাতার নাম</label>
                        <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}">
                    </div>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                    <div class="form-group">
                        <label style="font-weight:600">অভিভাবকের নাম</label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600">অভিভাবকের মোবাইল নম্বর</label>
                        <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $student->guardian_phone) }}">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:14px">
                    <label style="font-weight:600">শিক্ষাগত যোগ্যতা</label>
                    <input type="text" name="education_qualification" class="form-control" value="{{ old('education_qualification', $student->education_qualification) }}">
                </div>

                <div class="form-group" style="margin-bottom:14px">
                    <label style="font-weight:600">বর্তমান ঠিকানা</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $student->address) }}</textarea>
                </div>

                <div class="form-group">
                    <label style="font-weight:600">স্থায়ী ঠিকানা</label>
                    <textarea name="permanent_address" class="form-control" rows="2">{{ old('permanent_address', $student->permanent_address) }}</textarea>
                </div>
            </div>
            <div class="card-footer" style="text-align:right;background:#f8fafc;padding:14px 20px;border-top:1px solid #e2e8f0">
                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary" style="margin-right:8px">বাতিল</a>
                <button type="submit" class="btn btn-primary">পরিবর্তন সংরক্ষণ করুন</button>
            </div>
        </div>
    </form>
</x-admin-layout>
