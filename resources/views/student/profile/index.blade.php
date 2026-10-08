<x-student-layout>
    <x-slot name="title">আমার প্রোফাইল (My Profile)</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush',sans-serif">আমার প্রোফাইল তথ্য</h1>
            <p style="font-family:'Kalpurush',sans-serif">ব্যক্তিগত, অভিভাবক, ঠিকানা ও শিক্ষাগত তথ্য হালনাগাদ করুন</p>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            @if($student->is_common_account || (auth()->user() && auth()->user()->is_common_account))
                <span class="badge" style="background:#2563eb;color:#fff;font-size:13px;padding:6px 14px;font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-users"></i> সাধারণ শেয়ার্ড আইডি (Common Account)
                </span>
            @else
                <a href="{{ route('student.course-transfers.index') }}" class="btn btn-outline btn-sm" style="font-size:12px;font-family:'Kalpurush',sans-serif;padding:6px 14px">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i> কোর্স পরিবর্তন
                </a>
                <a href="{{ route('student.readmissions.index') }}" class="btn btn-sm" style="background:#047857;color:#fff;border:none;font-size:12px;font-family:'Kalpurush',sans-serif;padding:6px 14px;border-radius:6px;font-weight:700">
                    <i class="fa-solid fa-user-graduate"></i> রি-এডমিশন আবেদন
                </a>
            @endif
            @if($student->is_common_account || (auth()->user() && auth()->user()->is_common_account) || $percent >= 95)
                <span class="badge badge-success no-dot" style="font-size:13px;padding:6px 14px;font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-circle-check"></i> সম্পন্ন ({{ ($student->is_common_account || (auth()->user() && auth()->user()->is_common_account)) ? '100' : $percent }}%)
                </span>
            @else
                <span class="badge badge-warning no-dot" style="font-size:13px;padding:6px 14px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-triangle-exclamation"></i> অসম্পূর্ণ ({{ $percent }}%)
                </span>
            @endif
        </div>
    </div>

    @if($student->is_common_account || (auth()->user() && auth()->user()->is_common_account))
    <div class="alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:flex-start;gap:14px;font-family:'Kalpurush',sans-serif">
        <i class="fa-solid fa-circle-info" style="font-size:24px;color:#2563eb;margin-top:2px"></i>
        <div>
            <h4 style="margin:0 0 4px;font-weight:700;font-size:16px;color:#1e3a8a">কমন / শেয়ার্ড স্টুডেন্ট অ্যাকাউন্ট (Common Shared Account)</h4>
            <p style="margin:0;font-size:13.5px;line-height:1.6;color:#1e40af">
                এই অ্যাকাউন্টটি স্পেশাল কোর্সের একটি সাধারণ (Common) শেয়ার্ড অ্যাকাউন্ট। একাধিক শিক্ষার্থী এই আইডি দিয়ে একযোগে লগইন করে ক্লাস ও রিসোর্স দেখার সুবিধা পান। এই অ্যাকাউন্টের তথ্যের সার্বজনীনতা রক্ষার স্বার্থে প্রোফাইল তথ্য, পাসওয়ার্ড পরিবর্তন বা কোর্স স্থানান্তর নিষিদ্ধ ও নিষ্ক্রিয় রাখা হয়েছে।
            </p>
        </div>
    </div>
    @endif

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
                        @php
                            $dobVal = old('date_of_birth');
                            if (!$dobVal && !empty($student->date_of_birth)) {
                                try {
                                    $dobVal = ($student->date_of_birth instanceof \DateTimeInterface)
                                        ? $student->date_of_birth->format('Y-m-d')
                                        : \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d');
                                } catch (\Throwable $e) {
                                    $dobVal = (string) $student->date_of_birth;
                                }
                            }
                        @endphp
                        <input type="date" name="date_of_birth" class="form-control" value="{{ $dobVal }}" required>
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
                </div>

                {{-- ── প্রোফাইল ছবি ও ইসলামিক অবতার ব্যবস্থাপনা ── --}}
                <div class="form-group" style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:18px 20px;margin-top:16px">
                    <label style="display:flex;align-items:center;gap:6px;font-size:14px;color:#064e3b;margin-bottom:4px;font-weight:700">
                        <i class="fa-solid fa-circle-user" style="color:var(--iom-green);font-size:18px"></i> প্রোফাইল ছবি ও মার্জিত ইসলামিক অবতার (Profile Photo & Islamic Avatar) <span class="text-danger">*</span>
                    </label>
                    <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px;line-height:1.5">
                        আপনি চাইলে পছন্দমতো মার্জিত ইসলামিক অবতার (হিজাব/টুপি) বেছে নিতে পারেন অথবা আপনার নিজস্ব মার্জিত ছবি আপলোড করতে পারেন।
                    </p>

                    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
                        {{-- Circular Avatar Preview --}}
                        <div style="position:relative;width:86px;height:86px;border-radius:50%;border:3px solid var(--iom-green);box-shadow:0 4px 14px rgba(4, 120, 87, 0.2);overflow:hidden;background:#fff;flex-shrink:0">
                            <img id="profile_avatar_preview" 
                                 src="{{ $student->photo_url ?? \App\Models\Student::defaultAvatarForGender($student->gender) }}" 
                                 alt="Profile Avatar" 
                                 style="width:100%;height:100%;object-fit:cover;">
                        </div>

                        {{-- Details & Action Buttons --}}
                        <div style="flex:1;min-width:260px">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px">
                                <span id="avatar_status_badge" style="display:inline-flex;align-items:center;gap:5px;font-size:12px;padding:4px 12px;border-radius:20px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:600">
                                    <i class="fa-solid fa-circle-check"></i> 
                                    <span id="avatar_status_text">
                                        @if(str_contains($student->photo_url ?? '', 'avatar') || empty($student->photo_url))
                                            ডিফল্ট ইসলামিক অবতার সেট করা আছে
                                        @else
                                            নিজস্ব ছবি আপলোডকৃত
                                        @endif
                                    </span>
                                </span>
                                <button type="button" id="avatar_reset_btn" onclick="resetStudentAvatarToDefault()" style="background:none;border:none;color:#dc2626;font-size:12px;cursor:pointer;text-decoration:underline">
                                    ডিফল্টে ফিরুন
                                </button>
                            </div>

                            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                                <button type="button" class="btn btn-outline btn-sm" onclick="openStudentAvatarModal()" style="font-size:12.5px;padding:7px 16px;border-radius:6px;font-family:'Kalpurush',sans-serif">
                                    <i class="fa-solid fa-icons" style="color:var(--iom-green)"></i> অবতার নির্বাচন করুন
                                </button>
                                <label for="profile_photo_file_input" class="btn btn-outline btn-sm" style="font-size:12.5px;padding:7px 16px;cursor:pointer;margin-bottom:0;border-radius:6px;font-family:'Kalpurush',sans-serif">
                                    <i class="fa-solid fa-cloud-arrow-up" style="color:var(--iom-green)"></i> ছবি আপলোড করুন
                                </label>
                                <input type="file" name="photo" id="profile_photo_file_input" accept="image/*" style="display:none" onchange="onStudentCustomPhotoSelected(this)">
                                <input type="hidden" name="avatar_preset" id="student_avatar_preset" value="{{ old('avatar_preset', $student->photo_url) }}">
                            </div>
                            <div id="student_file_name_display" style="font-size:11.5px;color:#047857;margin-top:6px;display:none;font-weight:600"></div>
                        </div>
                    </div>
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
            @if($student->is_common_account || (auth()->user() && auth()->user()->is_common_account))
                <button type="button" class="btn btn-secondary" disabled style="padding:12px 30px;font-size:15px;font-weight:700;font-family:'Kalpurush',sans-serif;cursor:not-allowed;background:#94a3b8;border-color:#94a3b8;color:#fff">
                    <i class="fa-solid fa-lock"></i> কমন অ্যাকাউন্টে প্রোফাইল তথ্য লক করা
                </button>
            @else
                <button type="submit" class="btn btn-primary" style="padding:12px 30px;font-size:15px;font-weight:700;font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-floppy-disk"></i> প্রোফাইল সংরক্ষণ করুন (Save Profile)
                </button>
            @endif
        </div>
    </form>

    {{-- Student Avatar Selection Modal --}}
    <div id="studentAvatarModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.7);backdrop-filter:blur(4px);z-index:999999;align-items:center;justify-content:center;padding:16px" onclick="handleStudentAvatarBackdropClick(event)">
        <div style="background:#fff;width:100%;max-width:580px;border-radius:14px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;max-height:90vh;display:flex;flex-direction:column;font-family:'Kalpurush',sans-serif">
            <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
                <div style="font-size:16px;font-weight:700;color:#064e3b;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-sparkles" style="color:#047857"></i>
                    <span>মার্জিত ইসলামিক অবতার নির্বাচন করুন</span>
                </div>
                <button type="button" onclick="closeStudentAvatarModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;padding:4px;line-height:1">&times;</button>
            </div>

            <div style="display:flex;border-bottom:1.5px solid #e2e8f0;background:#f1f5f9">
                <button type="button" id="tab_btn_female" onclick="switchStudentAvatarTab('female')" style="flex:1;padding:11px 16px;border:none;background:#fff;color:#047857;font-weight:700;font-size:13.5px;cursor:pointer;border-bottom:3px solid #047857;font-family:inherit">
                    <i class="fa-solid fa-venus"></i> বোন শাখা (মহিলা অবতার - ৩টি)
                </button>
                <button type="button" id="tab_btn_male" onclick="switchStudentAvatarTab('male')" style="flex:1;padding:11px 16px;border:none;background:transparent;color:#475569;font-weight:700;font-size:13.5px;cursor:pointer;font-family:inherit">
                    <i class="fa-solid fa-mars"></i> ভাই শাখা (পুরুষ অবতার - ২টি)
                </button>
            </div>

            {{-- Female Avatars Panel --}}
            <div id="panel_female" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:14px;padding:20px;overflow-y:auto;max-height:380px">
                @php
                    $femaleAvatars = [
                        ['url' => '/images/avatars/female_avatar_1.jpg', 'name' => 'মার্জিত হিজাব', 'is_default' => true],
                        ['url' => '/images/avatars/female_avatar_2.jpg', 'name' => 'গোলাপী হিজাব', 'is_default' => false],
                        ['url' => '/images/avatars/female_avatar_3.jpg', 'name' => 'লাল হিজাব', 'is_default' => false],
                    ];
                @endphp
                @foreach($femaleAvatars as $av)
                    <div class="avatar-card-item {{ (($student->photo_url ?? '') === $av['url']) ? 'active' : '' }}"
                         data-url="{{ $av['url'] }}" 
                         data-name="{{ $av['name'] }}"
                         onclick="selectStudentAvatar('{{ $av['url'] }}', '{{ $av['name'] }}', 'female')"
                         style="border:2px solid {{ (($student->photo_url ?? '') === $av['url']) ? '#047857' : '#e2e8f0' }};border-radius:10px;padding:12px 8px;text-align:center;cursor:pointer;transition:all .2s ease;background:{{ (($student->photo_url ?? '') === $av['url']) ? '#ecfdf5' : '#fff' }};position:relative">
                        <img src="{{ asset($av['url']) }}" alt="{{ $av['name'] }}" style="width:68px;height:68px;border-radius:50%;object-fit:cover;margin:0 auto 6px;display:block;border:2px solid #cbd5e1">
                        <div style="font-size:12px;font-weight:600;color:#1e293b">{{ $av['name'] }}</div>
                        @if($av['is_default'])
                            <span style="font-size:10px;color:#047857;background:#d1fae5;padding:1px 6px;border-radius:8px;display:inline-block;margin-top:3px">ডিফল্ট</span>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Male Avatars Panel --}}
            <div id="panel_male" style="display:none;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:14px;padding:20px;overflow-y:auto;max-height:380px">
                @php
                    $maleAvatars = [
                        ['url' => '/images/avatars/male_avatar_1.png', 'name' => 'সাদা টুপি', 'is_default' => true],
                        ['url' => '/images/avatars/male_avatar_2.jpg', 'name' => 'নকশা টুপি', 'is_default' => false],
                    ];
                @endphp
                @foreach($maleAvatars as $av)
                    <div class="avatar-card-item {{ (($student->photo_url ?? '') === $av['url']) ? 'active' : '' }}"
                         data-url="{{ $av['url'] }}" 
                         data-name="{{ $av['name'] }}"
                         onclick="selectStudentAvatar('{{ $av['url'] }}', '{{ $av['name'] }}', 'male')"
                         style="border:2px solid {{ (($student->photo_url ?? '') === $av['url']) ? '#047857' : '#e2e8f0' }};border-radius:10px;padding:12px 8px;text-align:center;cursor:pointer;transition:all .2s ease;background:{{ (($student->photo_url ?? '') === $av['url']) ? '#ecfdf5' : '#fff' }};position:relative">
                        <img src="{{ asset($av['url']) }}" alt="{{ $av['name'] }}" style="width:68px;height:68px;border-radius:50%;object-fit:cover;margin:0 auto 6px;display:block;border:2px solid #cbd5e1">
                        <div style="font-size:12px;font-weight:600;color:#1e293b">{{ $av['name'] }}</div>
                        @if($av['is_default'])
                            <span style="font-size:10px;color:#047857;background:#d1fae5;padding:1px 6px;border-radius:8px;display:inline-block;margin-top:3px">ডিফল্ট</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:12px;color:#64748b">যেকোনো অবতারে ক্লিক করলে তাৎক্ষণিকভাবে সেট হবে</span>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeStudentAvatarModal()">বন্ধ করুন</button>
            </div>
        </div>
    </div>

    <script>
    function copyAddress(checkbox) {
        const present = document.getElementById('present_address').value;
        const permanent = document.getElementById('permanent_address');
        if (checkbox.checked) {
            permanent.value = present;
        }
    }

    // ── Student Avatar Modal & Photo Selection Logic ──
    const STUDENT_GENDER = @json(strtolower($student->gender ?? 'female'));
    const DEFAULT_FEMALE_AVATAR = '/images/avatars/female_avatar_1.jpg';
    const DEFAULT_MALE_AVATAR   = '/images/avatars/male_avatar_1.png';

    function openStudentAvatarModal() {
        const modal = document.getElementById('studentAvatarModal');
        if (!modal) return;

        if (STUDENT_GENDER.includes('male') || STUDENT_GENDER.includes('পুরুষ') || STUDENT_GENDER.includes('ভাই')) {
            switchStudentAvatarTab('male');
        } else {
            switchStudentAvatarTab('female');
        }

        modal.style.display = 'flex';
    }

    function closeStudentAvatarModal() {
        const modal = document.getElementById('studentAvatarModal');
        if (modal) modal.style.display = 'none';
    }

    function handleStudentAvatarBackdropClick(e) {
        if (e.target.id === 'studentAvatarModal') {
            closeStudentAvatarModal();
        }
    }

    function switchStudentAvatarTab(tab) {
        const btnFemale = document.getElementById('tab_btn_female');
        const btnMale = document.getElementById('tab_btn_male');
        const panelFemale = document.getElementById('panel_female');
        const panelMale = document.getElementById('panel_male');

        if (tab === 'male') {
            btnMale.style.background = '#fff';
            btnMale.style.color = '#047857';
            btnMale.style.borderBottom = '3px solid #047857';

            btnFemale.style.background = 'transparent';
            btnFemale.style.color = '#475569';
            btnFemale.style.borderBottom = 'none';

            panelMale.style.display = 'grid';
            panelFemale.style.display = 'none';
        } else {
            btnFemale.style.background = '#fff';
            btnFemale.style.color = '#047857';
            btnFemale.style.borderBottom = '3px solid #047857';

            btnMale.style.background = 'transparent';
            btnMale.style.color = '#475569';
            btnMale.style.borderBottom = 'none';

            panelFemale.style.display = 'grid';
            panelMale.style.display = 'none';
        }
    }

    function selectStudentAvatar(url, name, gender) {
        const previewImg = document.getElementById('profile_avatar_preview');
        const presetInput = document.getElementById('student_avatar_preset');
        if (previewImg) previewImg.src = url;
        if (presetInput) presetInput.value = url;

        const fileInput = document.getElementById('profile_photo_file_input');
        if (fileInput) fileInput.value = '';
        const fileDisp = document.getElementById('student_file_name_display');
        if (fileDisp) { fileDisp.style.display = 'none'; fileDisp.textContent = ''; }

        document.querySelectorAll('.avatar-card-item').forEach(card => {
            if (card.getAttribute('data-url') === url) {
                card.style.borderColor = '#047857';
                card.style.backgroundColor = '#ecfdf5';
                card.style.boxShadow = '0 0 0 2px #047857';
            } else {
                card.style.borderColor = '#e2e8f0';
                card.style.backgroundColor = '#fff';
                card.style.boxShadow = 'none';
            }
        });

        const badgeText = document.getElementById('avatar_status_text');
        if (badgeText) badgeText.textContent = 'নির্বাচিত অবতার: ' + name;

        closeStudentAvatarModal();
    }

    function onStudentCustomPhotoSelected(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        if (file.size > 3 * 1024 * 1024) {
            alert('ছবির আকার সর্বোচ্চ ৩ মেগাবাইট হতে পারবে।');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile_avatar_preview').src = e.target.result;
            document.getElementById('student_avatar_preset').value = '';

            const badgeText = document.getElementById('avatar_status_text');
            if (badgeText) badgeText.textContent = '📷 নিজস্ব ছবি আপলোডকৃত';

            const fileDisp = document.getElementById('student_file_name_display');
            if (fileDisp) {
                fileDisp.textContent = '✓ ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                fileDisp.style.display = 'block';
            }

            document.querySelectorAll('.avatar-card-item').forEach(card => {
                card.style.borderColor = '#e2e8f0';
                card.style.backgroundColor = '#fff';
                card.style.boxShadow = 'none';
            });
        };
        reader.readAsDataURL(file);
    }

    function resetStudentAvatarToDefault() {
        const isMale = STUDENT_GENDER.includes('male') || STUDENT_GENDER.includes('পুরুষ') || STUDENT_GENDER.includes('ভাই');
        const defaultUrl = isMale ? DEFAULT_MALE_AVATAR : DEFAULT_FEMALE_AVATAR;
        const defaultName = isMale ? 'সাদা টুপি (ডিফল্ট)' : 'মার্জিত হিজাব (ডিফল্ট)';

        selectStudentAvatar(defaultUrl, defaultName, isMale ? 'male' : 'female');

        const badgeText = document.getElementById('avatar_status_text');
        if (badgeText) badgeText.textContent = 'ডিফল্ট ইসলামিক অবতার সেট করা আছে';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeStudentAvatarModal();
    });
    </script>
</x-student-layout>
