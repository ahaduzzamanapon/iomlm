<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Online Admission Form — IOM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
        --blue: #1a56db;
        --blue-dark: #1e429f;
        --green: #057a55;
        --red: #e02424;
        --yellow-bg: #fefce8;
        --yellow-border: #fde047;
        --card-bg: #fff;
        --border: #e5e7eb;
        --text: #111827;
        --muted: #6b7280;
        --bg: #f1f5f9;
    }
    body { font-family: 'Inter', 'Noto Sans Bengali', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    /* Header */
    .site-header { background: #fff; border-bottom: 1px solid var(--border); padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 64px; }
    .site-logo { display: flex; align-items: center; gap: 12px; }
    .site-logo-icon { width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, #1a56db, #7c3aed); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 16px; }
    .site-logo-name { font-size: 15px; font-weight: 700; color: #1a56db; line-height: 1.2; }
    .site-logo-sub  { font-size: 11px; color: var(--muted); }
    .header-actions { display: flex; gap: 8px; }
    .btn-outline-sm { padding: 6px 14px; border: 1.5px solid var(--blue); border-radius: 6px; color: var(--blue); font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; background: #fff; transition: all .15s; }
    .btn-outline-sm:hover { background: var(--blue); color: #fff; }
    .btn-primary-sm { padding: 6px 14px; background: var(--blue); border: none; border-radius: 6px; color: #fff; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; transition: all .15s; }
    .btn-primary-sm:hover { background: var(--blue-dark); }

    /* Steps bar */
    .steps-bar { background: #1e3a5f; padding: 0 24px; }
    .steps-inner { display: flex; align-items: center; gap: 0; max-width: 900px; margin: 0 auto; }
    .step-item { display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-size: 12px; font-weight: 500; color: rgba(255,255,255,.5); position: relative; cursor: default; white-space: nowrap; }
    .step-item.active { color: #fff; }
    .step-item.done { color: #34d399; }
    .step-num { width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.3); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
    .step-item.active .step-num { background: var(--blue); border-color: var(--blue); color: #fff; }
    .step-item.done .step-num { background: #059669; border-color: #059669; color: #fff; }
    .step-sep { color: rgba(255,255,255,.2); font-size: 16px; margin: 0 4px; }

    /* Notice banner */
    .notice-banner { background: var(--yellow-bg); border: 1px solid var(--yellow-border); border-radius: 0; padding: 12px 24px; font-size: 13px; line-height: 1.7; color: #713f12; }

    /* Main container */
    .apply-container { max-width: 780px; margin: 0 auto; padding: 24px 16px 60px; }

    /* Form card */
    .form-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,.05); overflow: hidden; }
    .form-card-header { padding: 20px 28px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .form-card-title { font-size: 17px; font-weight: 700; }
    .form-card-step  { font-size: 13px; color: var(--blue); font-weight: 600; }
    .form-card-body  { padding: 28px; }
    .form-card-footer { padding: 16px 28px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }

    /* Form elements */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    @media(max-width:600px) { .form-row, .form-row-3 { grid-template-columns: 1fr; } }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px; }
    label .req { color: var(--red); margin-left: 2px; }
    input[type="text"], input[type="email"], input[type="date"], input[type="number"],
    select, textarea {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 7px;
        font-size: 13px; color: var(--text); background: #fff; outline: none;
        transition: border-color .15s;
        font-family: inherit;
    }
    input:focus, select:focus, textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(26,86,219,.08); }
    textarea { resize: vertical; min-height: 80px; }
    .form-check { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; }
    .form-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--blue); cursor: pointer; }

    /* Section divider */
    .section-title { font-size: 13px; font-weight: 700; color: var(--blue); border-bottom: 1.5px solid #dbeafe; padding-bottom: 6px; margin: 20px 0 16px; letter-spacing: .03em; text-transform: uppercase; }

    /* Terms box */
    .terms-box { background: var(--yellow-bg); border: 1px solid var(--yellow-border); border-radius: 8px; padding: 14px 16px; font-size: 13px; line-height: 1.9; color: #713f12; white-space: pre-line; }

    /* Step panel visibility */
    .step-panel { display: none; }
    .step-panel.active { display: block; }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all .15s; }
    .btn-primary { background: var(--blue); color: #fff; }
    .btn-primary:hover { background: var(--blue-dark); }
    .btn-secondary { background: #f3f4f6; color: #374151; }
    .btn-secondary:hover { background: #e5e7eb; }

    /* Error messages */
    .error-msg { font-size: 12px; color: var(--red); margin-top: 4px; }
    @if(count($errors))
    .has-error input, .has-error select { border-color: var(--red); }
    @endif

    /* Address copy button */
    .copy-btn { font-size: 12px; color: var(--blue); cursor: pointer; text-decoration: underline; }

    /* Footer */
    .site-footer { background: #1e3a5f; color: rgba(255,255,255,.7); text-align: center; padding: 18px; font-size: 12px; margin-top: 32px; }
    </style>
</head>
<body>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
{{-- Header --}}
<header class="site-header">
    <div class="site-logo">
        <img src="{{ asset('images/logo.png') }}" alt="IOM Logo" style="height:48px;width:auto;object-fit:contain">
        <div>
            <div class="site-logo-name">ISLAMIC ONLINE MADRASAH</div>
            <div class="site-logo-sub">Through Knowledge, Towards Jannah</div>
        </div>
    </div>
    <div class="header-actions">
        <a href="/" class="btn-outline-sm">Home</a>
        <a href="#" class="btn-primary-sm">Website</a>
    </div>
</header>

{{-- Steps bar --}}
<div class="steps-bar">
    <div class="steps-inner">
        <div class="step-item active" id="step-tab-1"><span class="step-num">1</span> Course Choice</div>
        <span class="step-sep">›</span>
        <div class="step-item" id="step-tab-2"><span class="step-num">2</span> Education Info</div>
        <span class="step-sep">›</span>
        <div class="step-item" id="step-tab-3"><span class="step-num">3</span> Personal Information</div>
        <span class="step-sep">›</span>
        <div class="step-item" id="step-tab-4"><span class="step-num">4</span> Application Download</div>
    </div>
</div>

{{-- Notice banner --}}
@php $terms = \App\Models\AppSetting::get('admission_terms', ''); @endphp
<div class="notice-banner" style="text-align:center;font-weight:500">
    ফর্ম পূরণ করার আগে নির্দেশিকা মনোযোগ দিয়ে পড়ুন। সকল তারকা চিহ্নিত (<span style="color:#dc2626">*</span>) তথ্য পূরণ বাধ্যতামূলক।
</div>

<div class="apply-container">
    <form method="POST" action="{{ route('apply.store') }}" id="applyForm">
        @csrf

        {{-- ══════════════════════════════════════
             STEP 1: Course Choice
        ══════════════════════════════════════ --}}
        <div class="step-panel active" id="step-1">
            <div class="form-card">
                <div class="form-card-header">
                    <span class="form-card-title">Online Application Form</span>
                    <span class="form-card-step">Step 1: Course Choice</span>
                </div>
                <div class="form-card-body">

                    @if($errors->any())
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991b1b">
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin-top:6px;margin-left:16px">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Poor Fund / Waiver Code Box --}}
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;padding:16px;border-radius:10px;margin-bottom:24px">
                        <label style="color:#0369a1;font-weight:700;margin-bottom:6px;display:block">
                            Have a Poor Fund / Waiver Code? (পুওর ফান্ড কোড আছে?)
                        </label>
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                            <input type="text" id="waiver_code_input" name="waiver_code" value="{{ old('waiver_code') }}" placeholder="e.g. POOR-2026-0001" style="text-transform:uppercase;max-width:240px">
                            <button type="button" class="btn btn-primary" onclick="applyWaiverCode()" style="padding:10px 18px;white-space:nowrap;font-weight:700">Apply Waiver Code</button>
                            <a href="/poor-fund" target="_blank" style="font-size:12px;color:#0284c7;text-decoration:underline">আবেদন করুন (Apply Poor Fund) ↗</a>
                        </div>
                        <div id="waiver-status-msg" style="font-size:13px;margin-top:8px;font-weight:600"></div>
                    </div>

                    <div class="form-group">
                        <label>Program / Course <span class="req">*</span></label>
                        <select name="course_id" id="course_id" required onchange="filterBatchesByCourse(this.value)">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }} ({{ $course->duration_value }} {{ strtolower($course->duration_unit) }}s)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Admission Batch (Target Batch)</label>
                        <select name="batch_id" id="batch_id">
                            <option value="">-- Select Open Batch --</option>
                            @foreach($activeBatches as $batch)
                                @if($batch->is_admission_open)
                                    <option value="{{ $batch->id }}" data-course-id="{{ $batch->course_id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                                        {{ $batch->name }} ({{ $batch->batch_code }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    @if($terms)
                    <div class="form-group">
                        <div class="section-title">ভর্তির শর্তাবলী</div>
                        <div class="terms-box">{{ $terms }}</div>
                    </div>
                    @endif

                </div>
                <div class="form-card-footer">
                    <div></div>
                    <button type="button" class="btn btn-primary" onclick="goStep(2)">Next →</button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             STEP 2: Education Info
        ══════════════════════════════════════ --}}
        <div class="step-panel" id="step-2">
            <div class="form-card">
                <div class="form-card-header">
                    <span class="form-card-title">Step-2: Job and Education</span>
                    <span class="form-card-step">Step 2 of 3</span>
                </div>
                <div class="form-card-body">

                    <div class="form-row">
                        <div class="form-group">
                            <label>Applicant Name <span class="req">*</span></label>
                            <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" placeholder="Enter full name" required>
                        </div>
                        <div class="form-group">
                            <label>Student Cell Phone <span class="req">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="form-group">
                            <label>You will join class using <span class="req">*</span></label>
                            <select name="device_type">
                                <option value="">-- Select Device --</option>
                                <option value="Desktop/Laptop PC" {{ old('device_type') == 'Desktop/Laptop PC' ? 'selected' : '' }}>Desktop/Laptop PC</option>
                                <option value="Mobile" {{ old('device_type') == 'Mobile' ? 'selected' : '' }}>Mobile</option>
                                <option value="Tablet" {{ old('device_type') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Present Occupation</label>
                            <select name="occupation">
                                <option value="">-- Select --</option>
                                @foreach(['Student','Service Holder','Business','Unemployed','Other'] as $occ)
                                <option value="{{ $occ }}" {{ old('occupation') == $occ ? 'selected' : '' }}>{{ $occ }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Educational Qualification</label>
                            <select name="education_qualification">
                                <option value="">-- Select --</option>
                                @foreach(['Below SSC','SSC / Equivalent','HSC / Equivalent','Bachelor Equivalent','Master Equivalent','Other'] as $eq)
                                <option value="{{ $eq }}" {{ old('education_qualification') == $eq ? 'selected' : '' }}>{{ $eq }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Academic Records</div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label>SSC/Equivalent School Name</label>
                            <input type="text" name="ssc_school" value="{{ old('ssc_school') }}" placeholder="School name">
                        </div>
                        <div class="form-group">
                            <label>SSC Board</label>
                            <select name="ssc_board">
                                <option value="">-- Select Board --</option>
                                @foreach(['Dhaka','Chittagong','Rajshahi','Jessore','Comilla','Barisal','Sylhet','Dinajpur','Mymensingh','Madrasah','Technical','Other'] as $b)
                                <option value="{{ $b }}" {{ old('ssc_board') == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>SSC Year</label>
                            <input type="number" name="ssc_year" value="{{ old('ssc_year') }}" placeholder="e.g. 2018" min="1990" max="{{ now()->year }}">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label>HSC/Equivalent College Name</label>
                            <input type="text" name="hsc_college" value="{{ old('hsc_college') }}" placeholder="College name">
                        </div>
                        <div class="form-group">
                            <label>HSC Board</label>
                            <select name="hsc_board">
                                <option value="">-- Select Board --</option>
                                @foreach(['Dhaka','Chittagong','Rajshahi','Jessore','Comilla','Barisal','Sylhet','Dinajpur','Mymensingh','Madrasah','Technical','Other'] as $b)
                                <option value="{{ $b }}" {{ old('hsc_board') == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>HSC Year</label>
                            <input type="number" name="hsc_year" value="{{ old('hsc_year') }}" placeholder="e.g. 2020" min="1990" max="{{ now()->year }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>University/Equivalent Institute Name</label>
                            <input type="text" name="university_name" value="{{ old('university_name') }}" placeholder="University name">
                        </div>
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" name="department_name" value="{{ old('department_name') }}" placeholder="e.g. CSE, BBA, EE">
                        </div>
                    </div>

                </div>
                <div class="form-card-footer">
                    <button type="button" class="btn btn-secondary" onclick="goStep(1)">← Back</button>
                    <button type="button" class="btn btn-primary" onclick="goStep(3)">I Agree, Next →</button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════
             STEP 3: Personal Information
        ══════════════════════════════════════ --}}
        <div class="step-panel" id="step-3">
            <div class="form-card">
                <div class="form-card-header">
                    <span class="form-card-title">Step-3: Personal Information</span>
                    <span class="form-card-step">Step 3 of 3</span>
                </div>
                <div class="form-card-body">

                    <div class="section-title">Basic Information</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="">-- Select Gender --</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Student Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Blood Group</label>
                            <select name="blood_group_id">
                                <option value="">-- Select --</option>
                                @foreach($bloodGroups as $bg)
                                <option value="{{ $bg->id }}" {{ old('blood_group_id') == $bg->id ? 'selected' : '' }}>{{ $bg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>National ID Card No</label>
                            <input type="text" name="national_id" value="{{ old('national_id') }}" placeholder="NID Number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Nationality</label>
                            <select name="nationality">
                                <option value="Bangladeshi" {{ old('nationality','Bangladeshi') == 'Bangladeshi' ? 'selected' : '' }}>Bangladeshi</option>
                                <option value="Other" {{ old('nationality') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Passport No</label>
                            <input type="text" name="passport_no" value="{{ old('passport_no') }}" placeholder="Passport Number">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Religion</label>
                            <select name="religion_id">
                                <option value="">-- Select Religion --</option>
                                @foreach($religions as $rel)
                                <option value="{{ $rel->id }}" {{ old('religion_id') == $rel->id ? 'selected' : '' }}>{{ $rel->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Birth Certificate No</label>
                            <input type="text" name="birth_certificate_no" value="{{ old('birth_certificate_no') }}" placeholder="Birth Certificate No">
                        </div>
                    </div>

                    {{-- Present Address --}}
                    <div class="section-title">Present Address</div>

                    <div class="form-group">
                        <label>House/Street/Village</label>
                        <input type="text" name="present_house" id="present_house" value="{{ old('present_house') }}" placeholder="House/Street/Village">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Post Office</label>
                            <input type="text" name="present_post_office" id="present_post_office" value="{{ old('present_post_office') }}" placeholder="Post Office">
                        </div>
                        <div class="form-group">
                            <label>Police Station (Thana)</label>
                            <input type="text" name="present_police_station" id="present_police_station" value="{{ old('present_police_station') }}" placeholder="Police Station">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Division</label>
                            <select name="present_division_id" id="present_division_id" onchange="loadDistricts('present')">
                                <option value="">-- Select Division --</option>
                                @foreach($divisions as $div)
                                <option value="{{ $div->id }}" {{ old('present_division_id') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>District</label>
                            <select name="present_district_id" id="present_district_id">
                                <option value="">-- Select District --</option>
                            </select>
                        </div>
                    </div>

                    {{-- Permanent Address --}}
                    <div class="section-title" style="display:flex;align-items:center;justify-content:space-between">
                        Permanent Address
                        <label class="form-check" style="text-transform:none;font-size:12px;font-weight:400">
                            <input type="checkbox" id="same_as_present" name="same_as_present" value="1" onchange="togglePermanent(this)">
                            Same as Present Address
                        </label>
                    </div>

                    <div id="permanent-fields">
                        <div class="form-group">
                            <label>House/Street/Village</label>
                            <input type="text" name="permanent_house" id="permanent_house" value="{{ old('permanent_house') }}" placeholder="House/Street/Village">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Post Office</label>
                                <input type="text" name="permanent_post_office" id="permanent_post_office" value="{{ old('permanent_post_office') }}" placeholder="Post Office">
                            </div>
                            <div class="form-group">
                                <label>Police Station (Thana)</label>
                                <input type="text" name="permanent_police_station" id="permanent_police_station" value="{{ old('permanent_police_station') }}" placeholder="Police Station">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Division</label>
                                <select name="permanent_division_id" id="permanent_division_id" onchange="loadDistricts('permanent')">
                                    <option value="">-- Select Division --</option>
                                    @foreach($divisions as $div)
                                    <option value="{{ $div->id }}" {{ old('permanent_division_id') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>District</label>
                                <select name="permanent_district_id" id="permanent_district_id">
                                    <option value="">-- Select District --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="form-card-footer">
                    <button type="button" class="btn btn-secondary" onclick="goStep(2)">← Back</button>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </div>
        </div>

    </form>
</div>

{{-- Footer --}}
<footer class="site-footer">
    Copyright © Islamic Online Madrasah, All Rights Reserved.
</footer>

<script>
let currentStep = 1;

function goStep(step) {
    // Mark old step done
    document.getElementById('step-tab-' + currentStep).classList.remove('active');
    document.getElementById('step-tab-' + currentStep).classList.add('done');
    document.getElementById('step-' + currentStep).classList.remove('active');

    currentStep = step;

    // Activate new step
    document.querySelectorAll('.step-item').forEach((el, i) => {
        el.classList.remove('active');
        if (i + 1 < step) el.classList.add('done');
        else el.classList.remove('done');
    });
    document.getElementById('step-tab-' + step).classList.add('active');
    document.getElementById('step-' + step).classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function applyWaiverCode() {
    const codeInput = document.getElementById('waiver_code_input');
    const code = codeInput ? codeInput.value.trim() : '';
    const msgDiv = document.getElementById('waiver-status-msg');
    if (!code) {
        msgDiv.style.color = '#dc2626';
        msgDiv.innerHTML = 'অনুগ্রহ করে আপনার পুওর ফান্ড কোডটি লিখুন।';
        return;
    }
    msgDiv.style.color = '#0284c7';
    msgDiv.innerHTML = 'Searching waiver code...';

    fetch('/api/waiver-lookup?code=' + encodeURIComponent(code))
        .then(r => r.json())
        .then(data => {
            if (data.valid) {
                msgDiv.style.color = '#059669';
                msgDiv.innerHTML = data.message;
                
                // Auto fill fields
                if (data.full_name) {
                    const el = document.querySelector('input[name="applicant_name"]');
                    if (el) el.value = data.full_name;
                }
                if (data.phone) {
                    const el = document.querySelector('input[name="phone"]');
                    if (el) el.value = data.phone;
                }
                if (data.email) {
                    const el = document.querySelector('input[name="email"]');
                    if (el) el.value = data.email;
                }
                if (data.date_of_birth) {
                    const el = document.querySelector('input[name="date_of_birth"]');
                    if (el) el.value = data.date_of_birth;
                }
                if (data.national_id) {
                    const el = document.querySelector('input[name="national_id"]');
                    if (el) el.value = data.national_id;
                }
                if (data.gender) {
                    const el = document.querySelector('select[name="gender"]');
                    if (el) el.value = data.gender;
                }
                if (data.present_address) {
                    const el = document.getElementById('present_house');
                    if (el) el.value = data.present_address;
                }
                if (data.permanent_address) {
                    const el = document.getElementById('permanent_house');
                    if (el) el.value = data.permanent_address;
                }
                if (data.occupation) {
                    const el = document.querySelector('select[name="occupation"]');
                    if (el) el.value = data.occupation;
                }
                if (data.division_id) {
                    const el = document.getElementById('present_division_id');
                    if (el) {
                        el.value = data.division_id;
                        loadDistricts('present');
                    }
                }
            } else {
                msgDiv.style.color = '#dc2626';
                msgDiv.innerHTML = data.message;
            }
        })
        .catch(err => {
            msgDiv.style.color = '#dc2626';
            msgDiv.innerHTML = 'কোড সার্চ করতে ত্রুটি হয়েছে।';
        });
}

function filterBatchesByCourse(courseId) {
    const batchSel = document.getElementById('batch_id');
    if (!batchSel) return;
    const options = batchSel.querySelectorAll('option');
    let firstValid = null;
    options.forEach(opt => {
        if (!opt.value) return; // skip default option
        const match = !courseId || opt.dataset.courseId === courseId;
        opt.style.display = match ? '' : 'none';
        if (match && !firstValid) firstValid = opt.value;
    });
    if (courseId && firstValid) {
        batchSel.value = firstValid;
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const cId = document.getElementById('course_id').value;
    if (cId) filterBatchesByCourse(cId);
});

// AJAX load districts
function loadDistricts(prefix) {
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

// Same as present address toggle
function togglePermanent(cb) {
    const fields = document.getElementById('permanent-fields');
    if (cb.checked) {
        // Copy present to permanent
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
        fields.style.opacity = '.5';
        fields.style.pointerEvents = 'none';
    } else {
        fields.style.opacity = '1';
        fields.style.pointerEvents = '';
    }
}

// If form has errors, jump to correct step
@if($errors->hasAny(['course_id','academic_session_id']))
    goStep(1);
@elseif($errors->hasAny(['applicant_name','phone','date_of_birth','occupation','education_qualification','ssc_school','ssc_board','ssc_year','hsc_college','hsc_board','hsc_year','university_name','department_name','device_type']))
    goStep(1); goStep(2);
@elseif($errors->any())
    goStep(1); goStep(2); goStep(3);
@endif
</script>
</body>
</html>
