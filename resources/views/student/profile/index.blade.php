<x-student-layout>
    <x-slot name="title">আমার প্রোফাইল (My Profile)</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush',sans-serif">আমার প্রোফাইল তথ্য</h1>
            <p style="font-family:'Kalpurush',sans-serif">ব্যক্তিগত, অভিভাবক, ঠিকানা ও শিক্ষাগত তথ্য হালনাগাদ করুন</p>
        </div>
        <div>
            @if($percent >= 95)
                <span class="badge badge-success no-dot" style="font-size:13px;padding:6px 14px;font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-circle-check"></i> প্রোফাইল সম্পন্ন ({{ $percent }}%)
                </span>
            @else
                <span class="badge badge-warning no-dot" style="font-size:13px;padding:6px 14px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-triangle-exclamation"></i> অসম্পূর্ণ: {{ $percent }}% (প্রয়োজন ৯৫%)
                </span>
            @endif
        </div>
    </div>

    {{-- Progress Card --}}
    <div class="card" style="margin-bottom:20px;padding:20px;border-left:5px solid {{ $percent >= 95 ? '#047857' : '#f59e0b' }}">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div>
                <h3 style="font-size:16px;font-weight:700;color:{{ $percent >= 95 ? '#064e3b' : '#92400e' }};font-family:'Kalpurush',sans-serif">
                    @if($percent >= 95)
                        🎉 মাশাআল্লাহ! আপনার প্রোফাইল সফলভাবে সম্পন্ন হয়েছে।
                    @else
                        ⚠️ প্রোফাইল সম্পন্ন করা আবশ্যক (ন্যূনতম ৯৫%)
                    @endif
                </h3>
                <p style="font-size:13px;color:var(--text-muted);margin-top:4px;font-family:'Kalpurush',sans-serif">
                    @if($percent >= 95)
                        আপনার প্রোফাইলের সকল প্রয়োজনীয় তথ্য ডাটাবেজে সংরক্ষিত রয়েছে। আপনি পোর্টালের সকল ফিচার বাধাহীনভাবে ব্যবহার করতে পারবেন।
                    @else
                        ভর্তির পর মাদ্রাসার একাডেমিক ক্লাস, রুটিন ও পরীক্ষার অ্যাক্সেস পেতে প্রোফাইল ন্যূনতম <strong>৯৫% সম্পন্ন</strong> করা বাধ্যতামূলক।
                    @endif
                </p>
            </div>
            <div style="text-align:right;min-width:120px">
                <span style="font-size:26px;font-weight:800;color:{{ $percent >= 95 ? '#047857' : '#d97706' }}">{{ $percent }}%</span>
                <div style="font-size:11px;color:var(--text-muted)">অগ্রগতি</div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div style="width:100%;height:10px;background:#e2e8f0;border-radius:10px;margin-top:14px;overflow:hidden">
            <div style="width:{{ $percent }}%;height:100%;background:{{ $percent >= 95 ? 'linear-gradient(90deg, #10b981, #047857)' : 'linear-gradient(90deg, #fbbf24, #f59e0b)' }};border-radius:10px;transition:width 0.4s"></div>
        </div>

        @if($percent < 95 && count($missing) > 0)
        <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9">
            <div style="font-size:12px;font-weight:700;color:#b45309;margin-bottom:6px;font-family:'Kalpurush',sans-serif">
                যে তথ্যগুলো এখনো পূরণ করা হয়নি (পূরণ করলে ৯৫% হয়ে যাবে):
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px">
                @foreach($missing as $key => $lbl)
                    <span style="font-size:11px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:2px 8px;border-radius:12px;font-family:'Kalpurush',sans-serif">
                        • {{ $lbl }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:20px">
        <strong>তথ্য সংরক্ষণে ত্রুটি:</strong>
        <ul style="margin-top:4px;margin-left:16px">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- ── ১. ব্যক্তিগত তথ্য ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header" style="background:#f8fafc">
                <span class="card-title" style="font-family:'Kalpurush',sans-serif"><i class="fa-solid fa-user" style="color:var(--iom-green)"></i> ১. ব্যক্তিগত তথ্য (Personal Information)</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>পূর্ণ নাম (ভর্তি অনুযায়ী)</label>
                        <input type="text" class="form-control" value="{{ $student->name }}" readonly style="background:#f8fafc;cursor:not-allowed">
                    </div>
                    <div class="form-group">
                        <label>শিক্ষার্থী আইডি (Student Code)</label>
                        <input type="text" class="form-control" value="{{ $student->student_code ?? 'TBA' }}" readonly style="background:#f8fafc;cursor:not-allowed;font-weight:700;color:var(--iom-green)">
                    </div>
                </div>

                <div class="form-row" style="margin-top:12px">
                    <div class="form-group">
                        <label>মোবাইল নম্বর</label>
                        <input type="text" class="form-control" value="{{ $student->phone }}" readonly style="background:#f8fafc;cursor:not-allowed">
                    </div>
                    <div class="form-group">
                        <label>ইমেইল ঠিকানা</label>
                        <input type="email" class="form-control" value="{{ $student->email }}" readonly style="background:#f8fafc;cursor:not-allowed">
                    </div>
                </div>

                <div class="form-row-3" style="margin-top:12px">
                    <div class="form-group">
                        <label>জন্ম তারিখ <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d') ?? $student->date_of_birth) }}" required>
                    </div>
                    <div class="form-group">
                        <label>রক্তের গ্রুপ <span class="text-danger">*</span></label>
                        <select name="blood_group" class="form-control" required>
                            <option value="">-- নির্বাচন করুন --</option>
                            @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>জাতীয় পরিচয়পত্র / জন্ম নিবন্ধন <span class="text-danger">*</span></label>
                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}" placeholder="NID বা জন্ম সনদ নম্বর" required>
                    </div>
                </div>

                <div class="form-row" style="margin-top:12px">
                    <div class="form-group">
                        <label>জাতীয়তা <span class="text-danger">*</span></label>
                        <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $student->nationality ?? 'Bangladeshi') }}" required>
                    </div>
                    <div class="form-group">
                        <label>ধর্ম <span class="text-danger">*</span></label>
                        <input type="text" name="religion" class="form-control" value="{{ old('religion', $student->religion ?? 'Islam') }}" required>
                    </div>
                </div>

                <div class="form-row" style="margin-top:12px;align-items:center">
                    <div class="form-group">
                        <label>প্রোফাইল ছবি (Profile Photo) <span class="text-danger">*</span></label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <small style="color:var(--text-muted);font-size:11px">পাসপোর্ট সাইজ মার্জিত ছবি আপলোড করুন (সর্বোচ্চ ২ মেগাবাইট)</small>
                    </div>
                    @if($student->photo_url)
                    <div style="display:flex;align-items:center;gap:10px">
                        <img src="{{ $student->photo_url }}" alt="Photo" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--iom-green)">
                        <span style="font-size:12px;color:#047857;font-weight:600">ছবি আপলোড করা আছে</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── ২. অভিভাবকের তথ্য ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header" style="background:#f8fafc">
                <span class="card-title" style="font-family:'Kalpurush',sans-serif"><i class="fa-solid fa-users" style="color:var(--iom-green)"></i> ২. অভিভাবকের তথ্য (Guardian Information)</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>পিতার নাম <span class="text-danger">*</span></label>
                        <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}" placeholder="পিতার পূর্ণ নাম" required>
                    </div>
                    <div class="form-group">
                        <label>মাতার নাম <span class="text-danger">*</span></label>
                        <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}" placeholder="মাতার পূর্ণ নাম" required>
                    </div>
                </div>

                <div class="form-row-3" style="margin-top:12px">
                    <div class="form-group">
                        <label>স্থানীয় অভিভাবকের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}" placeholder="অভিভাবকের নাম" required>
                    </div>
                    <div class="form-group">
                        <label>অভিভাবকের মোবাইল নম্বর <span class="text-danger">*</span></label>
                        <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $student->guardian_phone) }}" placeholder="01XXXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label>সম্পর্ক <span class="text-danger">*</span></label>
                        <select name="guardian_relation" class="form-control" required>
                            <option value="">-- সম্পর্ক নির্বাচন --</option>
                            @foreach(['পিতা'=>'পিতা', 'মাতা'=>'মাতা', 'ভাই'=>'ভাই', 'বোন'=>'বোন', 'চাচা'=>'চাচা', 'মামা'=>'মামা', 'স্বামী/স্ত্রী'=>'স্বামী/স্ত্রী', 'অন্যান্য'=>'অন্যান্য'] as $k=>$v)
                                <option value="{{ $k }}" {{ old('guardian_relation', $student->guardian_relation) == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── ৩. ঠিকানা ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header" style="background:#f8fafc">
                <span class="card-title" style="font-family:'Kalpurush',sans-serif"><i class="fa-solid fa-location-dot" style="color:var(--iom-green)"></i> ৩. যোগাযোগের ঠিকানা (Address)</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>বর্তমান ঠিকানা (বাড়ি/গ্রাম, ডাকঘর, থানা, জেলা) <span class="text-danger">*</span></label>
                    <textarea name="address" id="present_address" class="form-control" rows="2" placeholder="বর্তমান বসবাসের বিস্তারিত ঠিকানা" required>{{ old('address', $student->address) }}</textarea>
                </div>

                <div style="margin:8px 0 12px">
                    <label style="display:flex;align-items:center;gap:6px;font-weight:normal;font-size:13px;cursor:pointer">
                        <input type="checkbox" id="same_address_check" onchange="copyAddress(this)" style="width:16px;height:16px;accent-color:var(--iom-green)">
                        <span>স্থায়ী ঠিকানা বর্তমান ঠিকানার অনুরূপ</span>
                    </label>
                </div>

                <div class="form-group">
                    <label>স্থায়ী ঠিকানা <span class="text-danger">*</span></label>
                    <textarea name="permanent_address" id="permanent_address" class="form-control" rows="2" placeholder="স্থায়ী বিস্তারিত ঠিকানা" required>{{ old('permanent_address', $student->permanent_address) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── ৪. শিক্ষাগত যোগ্যতা ও পেশা ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header" style="background:#f8fafc">
                <span class="card-title" style="font-family:'Kalpurush',sans-serif"><i class="fa-solid fa-graduation-cap" style="color:var(--iom-green)"></i> ৪. শিক্ষাগত যোগ্যতা ও পেশা (Education & Occupation)</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>বর্তমান পেশা <span class="text-danger">*</span></label>
                        <select name="occupation" class="form-control" required>
                            <option value="">-- পেশা নির্বাচন করুন --</option>
                            @foreach(['Student'=>'শিক্ষার্থী', 'Service Holder'=>'চাকরিজীবী', 'Business'=>'ব্যবসায়ী', 'Teacher'=>'শিক্ষক', 'Homemaker'=>'গৃহিণী', 'Other'=>'অন্যান্য'] as $k=>$v)
                                <option value="{{ $k }}" {{ old('occupation', $student->occupation) == $k ? 'selected' : '' }}>{{ $v }} ({{ $k }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>সর্বোচ্চ শিক্ষাগত যোগ্যতা <span class="text-danger">*</span></label>
                        <select name="education_qualification" class="form-control" required>
                            <option value="">-- যোগ্যতা নির্বাচন করুন --</option>
                            @foreach(['SSC / Equivalent'=>'এসএসসি / দাখিল বা সমমান', 'HSC / Equivalent'=>'এইচএসসি / আলিম বা সমমান', 'Bachelor / Fazil'=>'স্নাতক / ফাযিল বা সমমান', 'Masters / Kamil'=>'মাস্টার্স / কামিল বা সমমান', 'Hafez'=>'হিফজুল কুরআন', 'Other'=>'অন্যান্য'] as $k=>$v)
                                <option value="{{ $k }}" {{ old('education_qualification', $student->education_qualification) == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="section-title" style="font-size:13px;font-weight:700;color:#047857;margin:16px 0 10px;border-bottom:1px solid #e2e8f0;padding-bottom:4px">
                    এসএসসি / দাখিল বা সমমান পরীক্ষা
                </div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label>শিক্ষা প্রতিষ্ঠান</label>
                        <input type="text" name="ssc_school" class="form-control" value="{{ old('ssc_school', $student->ssc_school) }}" placeholder="মাদ্রাসা / স্কুলের নাম">
                    </div>
                    <div class="form-group">
                        <label>শিক্ষা বোর্ড <span class="text-danger">*</span></label>
                        <select name="ssc_board" class="form-control" required>
                            <option value="">-- বোর্ড --</option>
                            @foreach(['Madrasah'=>'বাংলাদেশ মাদ্রাসা শিক্ষা বোর্ড', 'Dhaka'=>'ঢাকা বোর্ড', 'Chittagong'=>'চট্টগ্রাম বোর্ড', 'Rajshahi'=>'রাজশাহী বোর্ড', 'Jessore'=>'যশোর বোর্ড', 'Comilla'=>'কুমিল্লা বোর্ড', 'Barisal'=>'বরিশাল বোর্ড', 'Sylhet'=>'সিলেট বোর্ড', 'Dinajpur'=>'দিনাজপুর বোর্ড', 'Mymensingh'=>'ময়মনসিংহ বোর্ড', 'Technical'=>'কারিগরি বোর্ড', 'Other'=>'অন্যান্য'] as $k=>$v)
                                <option value="{{ $k }}" {{ old('ssc_board', $student->ssc_board) == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>পাসের সন <span class="text-danger">*</span></label>
                        <input type="number" name="ssc_year" class="form-control" value="{{ old('ssc_year', $student->ssc_year) }}" placeholder="যেমন: 2020" min="1980" max="{{ now()->year }}" required>
                    </div>
                </div>

                <div class="section-title" style="font-size:13px;font-weight:700;color:#047857;margin:16px 0 10px;border-bottom:1px solid #e2e8f0;padding-bottom:4px">
                    এইচএসসি / আলিম বা সমমান পরীক্ষা (প্রযোজ্য ক্ষেত্রে)
                </div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label>কলেজ / মাদ্রাসা</label>
                        <input type="text" name="hsc_college" class="form-control" value="{{ old('hsc_college', $student->hsc_college) }}" placeholder="কলেজ বা মাদ্রাসার নাম">
                    </div>
                    <div class="form-group">
                        <label>শিক্ষা বোর্ড</label>
                        <select name="hsc_board" class="form-control">
                            <option value="">-- বোর্ড --</option>
                            @foreach(['Madrasah'=>'মাদ্রাসা বোর্ড', 'Dhaka'=>'ঢাকা', 'Chittagong'=>'চট্টগ্রাম', 'Rajshahi'=>'রাজশাহী', 'Jessore'=>'যশোর', 'Comilla'=>'কুমিল্লা', 'Barisal'=>'বরিশাল', 'Sylhet'=>'সিলেট', 'Dinajpur'=>'দিনাজপুর', 'Mymensingh'=>'ময়মনসিংহ', 'Technical'=>'কারিগরি', 'Other'=>'অন্যান্য / নজরে নেই'] as $k=>$v)
                                <option value="{{ $k }}" {{ old('hsc_board', $student->hsc_board) == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>পাসের সন</label>
                        <input type="number" name="hsc_year" class="form-control" value="{{ old('hsc_year', $student->hsc_year) }}" placeholder="যেমন: 2022" min="1980" max="{{ now()->year }}">
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:30px">
            <button type="submit" class="btn btn-primary" style="padding:12px 30px;font-size:15px;font-weight:700;font-family:'Kalpurush',sans-serif">
                <i class="fa-solid fa-floppy-disk"></i> প্রোফাইল সংরক্ষণ করুন (Save Profile)
            </button>
        </div>
    </form>

    <script>
    function copyAddress(checkbox) {
        const present = document.getElementById('present_address').value;
        const permanent = document.getElementById('permanent_address');
        if (checkbox.checked) {
            permanent.value = present;
        }
    }
    </script>
</x-student-layout>
