<x-admin-layout>
    <x-slot name="title">নতুন ভর্তি ফর্ম (New Admission)</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.admissions.index') }}">← ভর্তি তালিকায় ফিরে যান</a>
            </div>
            <h1 style="font-family:'Kalpurush',sans-serif">নতুন শিক্ষার্থী ভর্তি ফর্ম</h1>
            <p style="font-family:'Kalpurush',sans-serif">সংক্ষিপ্ত প্রাথমিক ভর্তি ফর্ম — মৌলিক তথ্য এন্ট্রি ও ভর্তি অনুমোদন প্রক্রিয়া</p>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:20px">
        <strong>ত্রুটিসমূহ সংশোধন করুন:</strong>
        <ul style="margin-top:4px;margin-left:16px">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="alert alert-info" style="margin-bottom:20px;font-family:'Kalpurush',sans-serif;line-height:1.7;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46">
        <i class="fa-solid fa-circle-info"></i> <strong>প্রোফাইল নীতি:</strong> ভর্তির সময় শুধুমাত্র মৌলিক আবশ্যকীয় তথ্যগুলো পূরণ করুন। ভর্তি অনুমোদনের পর শিক্ষার্থী তার স্টুডেন্ট পোর্টালে লগইন করে বিস্তারিত ঠিকানা, অভিভাবক ও পূর্ববর্তী শিক্ষাগত তথ্য দিয়ে প্রোফাইল <strong>৯৫% সম্পন্ন</strong> করবেন।
    </div>

    <form method="POST" action="{{ route('admin.admissions.store') }}">
        @csrf

        {{-- ── 1. Course & Admission Setup ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header" style="background:#f8fafc">
                <span class="card-title" style="font-family:'Kalpurush',sans-serif"><i class="fa-solid fa-graduation-cap"></i> ১. কোর্স ও ব্যাচ নির্বাচন</span>
            </div>
            <div class="card-body">
                <div class="form-row-3">
                    <div class="form-group">
                        <label>কোর্স / প্রোগ্রাম <span class="required">*</span></label>
                        <select name="interested_course_id" id="interested_course_id" class="form-control" required onchange="filterBatches(this.value)">
                            <option value="">-- কোর্স নির্বাচন করুন --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" {{ old('interested_course_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ str_replace('_',' ',$c->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>টার্গেট ব্যাচ</label>
                        <select name="batch_id" id="batch_id" class="form-control">
                            <option value="">-- ব্যাচ নির্বাচন করুন (ঐচ্ছিক) --</option>
                            @foreach($activeBatches as $b)
                                <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}" {{ old('batch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->batch_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>একাডেমিক সেশন</label>
                        <select name="academic_session_id" class="form-control">
                            <option value="">-- সেশন নির্বাচন করুন --</option>
                            @foreach($sessions as $s)
                                <option value="{{ $s->id }}" {{ old('academic_session_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row" style="margin-top:12px">
                    <div class="form-group">
                        <label>লিড সোর্স (Lead Source)</label>
                        <select name="lead_source" class="form-control">
                            <option value="Direct" {{ old('lead_source') == 'Direct' ? 'selected' : '' }}>সরাসরি / অফিস ভিজিট (Direct)</option>
                            <option value="Website" {{ old('lead_source') == 'Website' ? 'selected' : '' }}>ওয়েবসাইট ফর্ম (Website)</option>
                            <option value="Social Media" {{ old('lead_source') == 'Social Media' ? 'selected' : '' }}>ফেসবুক / সোশ্যাল মিডিয়া (Social)</option>
                            <option value="Referral" {{ old('lead_source') == 'Referral' ? 'selected' : '' }}>শিক্ষার্থী রেফারেল (Referral)</option>
                            <option value="Call" {{ old('lead_source') == 'Call' ? 'selected' : '' }}>ফোন কল ইনকোয়ারি (Phone Call)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>স্কলারশিপ / ফি ছাড় % (Waiver %)</label>
                        <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', 0) }}" min="0" max="100" placeholder="0">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 2. Basic Student Information ── --}}
        <div class="card" style="margin-bottom:20px">
            <div class="card-header" style="background:#f8fafc">
                <span class="card-title" style="font-family:'Kalpurush',sans-serif"><i class="fa-solid fa-user"></i> ২. শিক্ষার্থীর মৌলিক আবশ্যকীয় তথ্য</span>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>শিক্ষার্থীর পূর্ণ নাম <span class="required">*</span></label>
                        <input type="text" name="applicant_name" class="form-control" value="{{ old('applicant_name') }}" placeholder="নাম লিখুন" required>
                    </div>

                    <div class="form-group">
                        <label>মোবাইল নম্বর <span class="required">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                    </div>
                </div>

                <div class="form-row" style="margin-top:12px">
                    <div class="form-group">
                        <label>ইমেইল ঠিকানা <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="student@example.com" required>
                        <small style="color:var(--text-muted);font-size:11px">স্টুডেন্ট পোর্টাল লগইন ও পাসওয়ার্ড প্রেরণের জন্য ব্যবহৃত হবে</small>
                    </div>

                    <div class="form-group">
                        <label>লিঙ্গ / শাখা <span class="required">*</span></label>
                        <select name="gender" class="form-control" required>
                            <option value="">-- শাখা নির্বাচন করুন --</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>ভাই শাখা (পুরুষ)</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>বোন শাখা (মহিলা)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="margin-top:12px">
                    <div class="form-group">
                        <label>জন্ম তারিখ (ঐচ্ছিক)</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                    </div>

                    <div class="form-group">
                        <label>ভর্তি সংক্রান্ত মন্তব্য / নোট (ঐচ্ছিক)</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="প্রয়োজনীয় কোনো মন্তব্য থাকলে লিখুন">
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:30px">
            <a href="{{ route('admin.admissions.index') }}" class="btn btn-outline">বাতিল</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:14px;font-weight:700">
                <i class="fa-solid fa-check"></i> ভর্তি আবেদন সংরক্ষণ করুন
            </button>
        </div>
    </form>

    <script>
    function filterBatches(courseId) {
        const batchSelect = document.getElementById('batch_id');
        const options = batchSelect.querySelectorAll('option');
        let hasMatch = false;

        options.forEach(opt => {
            if (!opt.value) { opt.style.display = ''; return; }
            const cId = opt.getAttribute('data-course-id');
            if (!courseId || cId === courseId) {
                opt.style.display = '';
                if (!hasMatch && opt.value) { opt.selected = true; hasMatch = true; }
            } else {
                opt.style.display = 'none';
            }
        });

        if (!hasMatch) batchSelect.value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const courseSelect = document.getElementById('interested_course_id');
        if (courseSelect && courseSelect.value) filterBatches(courseSelect.value);
    });
    </script>
</x-admin-layout>
