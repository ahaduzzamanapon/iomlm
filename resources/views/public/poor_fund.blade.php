<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Online Poor Fund Application — Islamic Online Madrasah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    html, body { width: 100%; max-width: 100vw; overflow-x: hidden; }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
        --blue: #1a56db;
        --blue-dark: #1e3a5f;
        --green: #057a55;
        --red: #e02424;
        --card-bg: #ffffff;
        --border: #e2e8f0;
        --bg: #f8fafc;
        --text: #0f172a;
        --muted: #64748b;
    }
    body { font-family: 'Inter', 'Noto Sans Bengali', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; line-height: 1.6; }

    /* Top Nav */
    .nav-bar { background: var(--blue-dark); color: #fff; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid var(--blue); flex-wrap: wrap; gap: 10px; }
    .nav-title { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
    .btn-home { background: var(--blue); color: #fff; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: background .15s; white-space: nowrap; }
    .btn-home:hover { background: #2563eb; }

    .container { max-width: 920px; width: 100%; margin: 20px auto; padding: 0 14px 60px; }

    /* Instructions card */
    .instruction-card { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 20px; font-size: 13px; color: #0369a1; box-shadow: 0 1px 3px rgba(0,0,0,.03); word-break: break-word; }
    .instruction-card p { margin-bottom: 10px; }
    .instruction-card p:last-child { margin-bottom: 0; }
    .note-tag { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 8px; font-weight: 600; margin-top: 12px; font-size: 12px; }

    /* Form card */
    .form-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.04); overflow: hidden; width: 100%; }
    .form-header { background: #fff; padding: 20px 24px 16px; border-bottom: 1px solid var(--border); text-align: center; }
    .form-header h1 { font-size: 20px; font-weight: 700; color: var(--blue-dark); margin-bottom: 4px; }
    .form-header p { font-size: 12px; color: var(--muted); }

    .form-body { padding: 24px; }

    /* Section Fieldset */
    .fieldset-sec { border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px 14px; margin-bottom: 20px; background: #fff; }
    .legend-title { font-size: 13px; font-weight: 700; color: var(--blue); text-transform: uppercase; letter-spacing: .03em; padding: 0 6px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

    .form-group { margin-bottom: 14px; }
    label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; word-break: break-word; }
    label .req { color: var(--red); margin-left: 2px; }
    input[type="text"], input[type="email"], input[type="date"], input[type="number"], select, textarea {
        width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 8px;
        font-size: 13px; color: var(--text); background: #fff; outline: none; transition: border-color .15s; font-family: inherit;
        box-sizing: border-box;
    }
    input:focus, select:focus, textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(26,86,219,.08); }
    textarea { resize: vertical; min-height: 80px; }

    .radio-group { display: flex; align-items: center; gap: 14px; padding: 4px 0; flex-wrap: wrap; }
    .radio-label { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; cursor: pointer; }

    .form-footer { padding: 16px 24px; background: #f8fafc; border-top: 1px solid var(--border); text-align: right; }
    .btn-submit { background: var(--blue); color: #fff; padding: 12px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s; width: auto; }
    .btn-submit:hover { background: #1d4ed8; }

    .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }

    /* MOBILE RESPONSIVE OVERRIDES */
    @media(max-width:640px) {
        .form-row, .form-row-3 { grid-template-columns: 1fr !important; gap: 10px; }
        .form-body { padding: 14px 12px; }
        .form-header { padding: 16px 12px 12px; }
        .form-header h1 { font-size: 17px; }
        .fieldset-sec { padding: 14px 10px 10px; margin-bottom: 14px; }
        .form-footer { padding: 14px 12px; text-align: center; }
        .btn-submit { width: 100%; padding: 12px 16px; font-size: 13px; }
        .nav-bar { padding: 10px 12px; }
        .nav-title { font-size: 13px; }
        .btn-home { font-size: 11px; padding: 4px 10px; }
        .container { padding: 0 8px 40px; margin: 12px auto; }
    }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
</head>
<body>

<nav class="nav-bar">
    <div class="nav-title" style="display:flex;align-items:center;gap:10px">
        <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:34px;width:auto;object-fit:contain">
        <span>Online Poor Fund Application — IOM</span>
    </div>
    <a href="/apply" class="btn-home">← Online Admission Form</a>
</nav>

<div class="container">
    {{-- Instruction banner --}}
    <div class="instruction-card">
        <p><strong>IOM একটি সম্পূর্ন অলাভজনক প্রতিষ্ঠান।</strong> কিন্তু মাদ্রাসা পরিচালনা করার জন্য শিক্ষক/শিক্ষিকার হাদিয়া, স্টাফদের বেতন, অফিস ভাড়া, সার্ভার ভাড়া, ভার্চুয়াল ক্যাম্পাস ও লাইভ ক্লাস ইত্যাদি খাতে ব্যয় হয়ে থাকে।</p>
        <p>তবে আপনি যদি আর্থিকভাবে অসচ্ছল হওয়ায় ফি প্রদানে সক্ষম না হন, সেমিস্টার ফি / ভর্তি ফি পুওরফান্ডের (Waiver) জন্য আবেদন করলে ইনশাআল্লাহ আপনার বিষয়টি বিবেচনা করা হবে।</p>
        <p><em>উল্লেখ্য যে, পুওরফান্ডের সুবিধা বজায় রাখতে ক্লাসে ৯০% উপস্থিতি এবং সেমিস্টার পরীক্ষায় ৫০% মার্ক বজায় রাখা আবশ্যক।</em></p>
        <div class="note-tag">
            Note: পুওর ফান্ড শুধুমাত্র আলিম কোর্স ও স্কুল মক্তব কোর্সের জন্য প্রযোজ্য।
        </div>
    </div>

    @if($errors->any())
    <div class="alert-error">
        <strong>অনুরোধ: অনুগ্রহ করে নিচের ভুলগুলো সংশোধন করুন:</strong>
        <ul style="margin-top:6px;margin-left:18px">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="form-card">
        <div class="form-header">
            <h1>Poor Fund Apply Form (পুওর ফান্ড আবেদনপত্র)</h1>
            <p>সবগুলো তথ্যই আল্লাহর সন্তুষ্টির জন্য সঠিক ও নির্ভুলভাবে পূরণ করুন। আবেদন গোপন রাখা হবে।</p>
        </div>

        <form method="POST" action="{{ route('poor_fund.store') }}" id="poorFundForm">
            @csrf

            <div class="form-body">
                {{-- ── 1. Academic & Basic Info ── --}}
                <fieldset class="fieldset-sec">
                    <legend class="legend-title">১. মৌলিক তথ্য (Basic Info)</legend>

                    {{-- Course selection (optional but helps admin show correct packages) --}}
                    @if(isset($courses) && $courses->count())
                    <div class="form-group">
                        <label>আপনি কোন Course এ ভর্তি হতে চান?</label>
                        <select name="course_id">
                            <option value="">-- Select Course (Optional) --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name (সম্পূর্ণ নাম) <span class="req">*</span></label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="আপনার নাম লিখুন" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address (ইমেইল) <span class="req">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Mobile Number (মোবাইল) <span class="req">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth (জন্ম তারিখ) <span class="req">*</span></label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label>Father's Name (পিতার নাম) <span class="req">*</span></label>
                            <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="পিতার নাম" required>
                        </div>
                        <div class="form-group">
                            <label>National ID / Birth Reg No <span class="req">*</span></label>
                            <input type="text" name="national_id" value="{{ old('national_id') }}" placeholder="NID বা জন্ম নিবন্ধন নং" required>
                        </div>
                        <div class="form-group">
                            <label>Gender (লিঙ্গ)</label>
                            <select name="gender">
                                <option value="">-- Select Gender --</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="radio-label">
                                <input type="checkbox" name="is_abroad" id="is_abroad" value="1" onchange="toggleAbroad(this)" {{ old('is_abroad') ? 'checked' : '' }}>
                                আপনি কি প্রবাসী? (Abroad Resident)
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" id="division-group">
                            <label>Present Division (বর্তমান বিভাগ) <span class="req">*</span></label>
                            <select name="division_id" id="division_id">
                                <option value="">-- Select Division --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}" {{ old('division_id') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="country-group" style="display:none">
                            <label>Country (দেশের নাম) <span class="req">*</span></label>
                            <input type="text" name="country_name" id="country_name" value="{{ old('country_name') }}" placeholder="যেমন: Saudi Arabia, Malaysia">
                        </div>
                    </div>
                </fieldset>

                {{-- ── 2. Address ── --}}
                <fieldset class="fieldset-sec">
                    <legend class="legend-title">২. ঠিকানা (Address)</legend>

                    <div class="form-group">
                        <label>Present Address (বর্তমান ঠিকানা) <span class="req">*</span></label>
                        <textarea name="present_address" id="present_address" placeholder="বাসা/গ্রাম, ডাকঘর, থানা, জেলা..." required>{{ old('present_address') }}</textarea>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <label style="margin:0">Permanent Address (স্থায়ী ঠিকানা) <span class="req">*</span></label>
                        <label class="radio-label">
                            <input type="checkbox" name="same_as_present" id="same_as_present" value="1" onchange="toggleWaiverAddress(this)">
                            Same as Present Address
                        </label>
                    </div>

                    <div class="form-group">
                        <textarea name="permanent_address" id="permanent_address" placeholder="স্থায়ী ঠিকানা..." required>{{ old('permanent_address') }}</textarea>
                    </div>
                </fieldset>

                {{-- ── 3. Profession & Income ── --}}
                <fieldset class="fieldset-sec">
                    <legend class="legend-title">৩. পেশা ও আয় সংক্রান্ত (Profession & Income)</legend>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Present Occupation (বর্তমান পেশা)</label>
                            <select name="occupation">
                                <option value="">-- Select Occupation --</option>
                                @foreach(['Student','Service Holder','Business','Unemployed','Other'] as $occ)
                                    <option value="{{ $occ }}" {{ old('occupation') == $occ ? 'selected' : '' }}>{{ $occ }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>পড়াশোনার বিষয় ও প্রতিষ্ঠানের নাম / পদবী <span class="req">*</span></label>
                            <input type="text" name="institution_or_business" value="{{ old('institution_or_business') }}" placeholder="প্রতিষ্ঠানের নাম ও পদবী" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>আপনি কি বর্তমানে IOM এর স্টুডেন্ট?</label>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="is_present_iom_student" value="0" onchange="toggleRoll(false)" checked> না (No)</label>
                                <label class="radio-label"><input type="radio" name="is_present_iom_student" value="1" onchange="toggleRoll(true)" {{ old('is_present_iom_student') ? 'checked' : '' }}> হ্যাঁ (Yes)</label>
                            </div>
                        </div>
                        <div class="form-group" id="roll-group" style="display:none">
                            <label>Student Roll / ID No <span class="req">*</span></label>
                            <input type="text" name="student_roll" id="student_roll" value="{{ old('student_roll') }}" placeholder="যেমন: STD-2026-001">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Source of Income (আয়ের উৎস) <span class="req">*</span></label>
                            <select name="source_of_income" required>
                                <option value="">-- Select Income Source --</option>
                                <option value="Self Income" {{ old('source_of_income') == 'Self Income' ? 'selected' : '' }}>নিজের আয় (Self)</option>
                                <option value="Father/Guardian Income" {{ old('source_of_income') == 'Father/Guardian Income' ? 'selected' : '' }}>পিতা/অভিভাবকের আয়</option>
                                <option value="Husband Income" {{ old('source_of_income') == 'Husband Income' ? 'selected' : '' }}>স্বামীর আয়</option>
                                <option value="Help / Donation" {{ old('source_of_income') == 'Help / Donation' ? 'selected' : '' }}>সাহায্য / অনুদান</option>
                                <option value="Other" {{ old('source_of_income') == 'Other' ? 'selected' : '' }}>অন্যান্য</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>মাসিক আয় অথবা বেতন (Monthly Income in BDT) <span class="req">*</span></label>
                            <input type="number" name="monthly_income" value="{{ old('monthly_income') }}" placeholder="টাকার পরিমাণ (e.g. 5000)" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>পিতা / স্বামী / অভিভাবকের মোবাইল নম্বর</label>
                            <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="018XXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label>আপনি কি বিবাহিত? (Marital Status)</label>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="is_married" value="0" checked> অবিবাহিত (Unmarried)</label>
                                <label class="radio-label"><input type="radio" name="is_married" value="1" {{ old('is_married') ? 'checked' : '' }}> বিবাহিত (Married)</label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- ── 4. Family & Problem Details ── --}}
                <fieldset class="fieldset-sec">
                    <legend class="legend-title">৪. পরিবারের বিবরণ ও সমস্যা (Family Details)</legend>

                    <div class="form-group">
                        <label>ভাই-বোন বা ছেলে-মেয়েদের পড়াশোনা ও অবস্থানের বিবরণ <span class="req">*</span></label>
                        <textarea name="family_siblings_details" placeholder="কতজন ভাই-বোন বা সন্তান এবং তারা কী করে..." required>{{ old('family_siblings_details') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>যে সমস্যার কারণে পুওর ফান্ডে আবেদন করতে চাচ্ছেন (বিস্তারিত লিখুন) <span class="req">*</span></label>
                        <textarea name="financial_problem_description" placeholder="আপনার আর্থিক সমস্যার বিবরণ বিস্তারিতভাবে উল্লেখ করুন..." required>{{ old('financial_problem_description') }}</textarea>
                    </div>
                </fieldset>

                {{-- ── 5. Waiver Requested Amount ── --}}
                <fieldset class="fieldset-sec">
                    <legend class="legend-title">৫. পুওর ফান্ড আবেদনের বিবরণ (Waiver Amount)</legend>

                    <div class="form-group">
                        <label>আপনি যে জন্য পুওর ফান্ডে আবেদন করতে চাচ্ছেন <span class="req">*</span></label>
                        <select name="apply_reason_type" id="apply_reason_type" onchange="toggleFeeFields(this.value)" required>
                            <option value="Both" {{ old('apply_reason_type') == 'Both' ? 'selected' : '' }}>ভর্তি ফি ও মাসিক সেমিস্টার ফি উভয়ই (Both)</option>
                            <option value="Admission Fee" {{ old('apply_reason_type') == 'Admission Fee' ? 'selected' : '' }}>শুধুমাত্র ভর্তি ফি (Admission Fee Only)</option>
                            <option value="Monthly Fee" {{ old('apply_reason_type') == 'Monthly Fee' ? 'selected' : '' }}>শুধুমাত্র মাসিক ফি (Monthly Fee Only)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group" id="admission-fee-group">
                            <label>IOM এর ভর্তি ফি কত টাকা প্রদান করা সুবিধাজনক?</label>
                            <input type="number" name="convenient_admission_fee" value="{{ old('convenient_admission_fee', 0) }}" placeholder="e.g. 500">
                        </div>
                        <div class="form-group" id="monthly-fee-group">
                            <label>IOM এর মাসিক সেমিস্টার ফি কত টাকা প্রদান করা সুবিধাজনক?</label>
                            <input type="number" name="convenient_monthly_fee" value="{{ old('convenient_monthly_fee', 0) }}" placeholder="e.g. 300">
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn-submit">আবেদন সম্পন্ন করুন (Submit Waiver Application) →</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAbroad(cb) {
    const divGroup = document.getElementById('division-group');
    const countryGroup = document.getElementById('country-group');
    if (cb.checked) {
        divGroup.style.display = 'none';
        countryGroup.style.display = 'block';
    } else {
        divGroup.style.display = 'block';
        countryGroup.style.display = 'none';
    }
}

function toggleWaiverAddress(cb) {
    const perm = document.getElementById('permanent_address');
    if (cb.checked) {
        perm.value = document.getElementById('present_address').value;
        perm.readOnly = true;
        perm.style.opacity = '.6';
    } else {
        perm.readOnly = false;
        perm.style.opacity = '1';
    }
}

function toggleRoll(isStudent) {
    document.getElementById('roll-group').style.display = isStudent ? 'block' : 'none';
}

function toggleFeeFields(type) {
    const adm = document.getElementById('admission-fee-group');
    const mth = document.getElementById('monthly-fee-group');
    if (type === 'Admission Fee') {
        adm.style.display = 'block';
        mth.style.display = 'none';
    } else if (type === 'Monthly Fee') {
        adm.style.display = 'none';
        mth.style.display = 'block';
    } else {
        adm.style.display = 'block';
        mth.style.display = 'block';
    }
}
</script>
</body>
</html>
