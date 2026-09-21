<x-admin-layout>
    <x-slot name="title">শিক্ষার্থী তালিকা ও ডিরেক্টরি (Students Directory)</x-slot>

    <style>
        /* Bangladeshi Context & Kalpurush Font */
        .student-roster, .student-roster * {
            font-family: 'Kalpurush', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .student-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .student-header-left h1 {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .student-header-left p {
            margin: 0;
            font-size: 13.5px;
            color: #64748b;
        }
        .student-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Stats Cards */
        .student-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .student-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }
        .student-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
        }
        .student-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .student-stat-info {
            display: flex;
            flex-direction: column;
        }
        .student-stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 2px;
        }
        .student-stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        /* Filter Card */
        .filter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }
        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .filter-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-title i {
            color: #047857;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }
        .filter-field label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }
        .filter-field select,
        .filter-field input {
            width: 100%;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 0 12px;
            font-size: 13.5px;
            color: #0f172a;
            background-color: #f8fafc;
            outline: none;
            transition: all 0.2s ease;
        }
        .filter-field select:focus,
        .filter-field input:focus {
            border-color: #047857;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.12);
        }

        .filter-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 14px;
            border-top: 1px solid #f1f5f9;
        }
        .filter-actions-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-actions-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Active Filter Chips */
        .active-filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            padding: 10px 14px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            color: #166534;
        }
        .active-filter-badge {
            background: #ffffff;
            border: 1px solid #86efac;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #15803d;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Table & Custom styles */
        .student-code-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap !important;
            letter-spacing: 0.5px;
        }
        .student-code-badge:hover {
            background: #047857;
            color: #ffffff;
            border-color: #047857;
        }
        .student-code-badge svg {
            stroke: #047857;
            transition: all 0.2s;
        }
        .student-code-badge:hover svg {
            stroke: #ffffff;
            transform: translateX(2px);
        }

        .gender-badge {
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            margin-top: 3px;
        }
        .gender-male {
            background: #e0f2fe;
            color: #0369a1;
        }
        .gender-female {
            background: #fce7f3;
            color: #be185d;
        }
        .gender-other {
            background: #f1f5f9;
            color: #475569;
        }

        .blood-badge {
            font-size: 11.5px;
            font-weight: 700;
            background: #fee2e2;
            color: #b91c1c;
            padding: 2px 8px;
            border-radius: 12px;
            border: 1px solid #fecaca;
            display: inline-block;
        }

        .course-info-box strong {
            color: #0f172a;
            font-size: 13.5px;
        }
        .course-info-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
    </style>

    <div class="student-roster">
        <!-- Header -->
        <div class="student-header">
            <div class="student-header-left">
                <h1>
                    <i class="fa-solid fa-users-gear" style="color:#047857"></i>
                    শিক্ষার্থী তালিকা ও ডিরেক্টরি (Students Directory)
                </h1>
                <p>সকল কোর্সের নিবন্ধিত শিক্ষার্থী ও আবেদনকারীদের তালিকা, অ্যাডভান্সড সার্চ এবং ডাটা এক্সপোর্ট</p>
            </div>
            <div class="student-header-right">
                <a href="{{ route('admin.students.export-csv', request()->query()) }}" class="btn btn-outline" style="border-color:#059669;color:#047857;font-weight:700" title="বর্তমান ফিল্টার অনুযায়ী CSV ফাইল ডাউনলোড করুন">
                    <i class="fa-solid fa-file-csv"></i> এক্সপোর্ট CSV
                </a>
                <a href="{{ route('admin.students.create') }}" class="btn btn-primary" style="background:#047857;border-color:#047857">
                    <i class="fa-solid fa-user-plus"></i> নতুন শিক্ষার্থী ভর্তি
                </a>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="student-stats-grid">
            <div class="student-stat-card">
                <div class="student-stat-icon" style="background:#e0f2fe;color:#0284c7">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="student-stat-info">
                    <span class="student-stat-label">সর্বমোট শিক্ষার্থী</span>
                    <span class="student-stat-value">{{ number_format($totalCount) }}</span>
                </div>
            </div>

            <div class="student-stat-card">
                <div class="student-stat-icon" style="background:#ecfdf5;color:#047857">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="student-stat-info">
                    <span class="student-stat-label">সক্রিয় শিক্ষার্থী (Active)</span>
                    <span class="student-stat-value">{{ number_format($activeCount) }}</span>
                </div>
            </div>

            <div class="student-stat-card">
                <div class="student-stat-icon" style="background:#fffbeb;color:#d97706">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div class="student-stat-info">
                    <span class="student-stat-label">অপেক্ষমান / লিড (Pending)</span>
                    <span class="student-stat-value">{{ number_format($pendingCount) }}</span>
                </div>
            </div>

            <div class="student-stat-card">
                <div class="student-stat-icon" style="background:#f5f3ff;color:#7c3aed">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div class="student-stat-info">
                    <span class="student-stat-label">উত্তীর্ণ শিক্ষার্থী (Graduated)</span>
                    <span class="student-stat-value">{{ number_format($graduatedCount) }}</span>
                </div>
            </div>
        </div>

        <!-- Status Quick Tabs -->
        <div class="tabs" style="margin-bottom:18px">
            <a href="{{ route('admin.students.index', request()->except(['status', 'page'])) }}"
               class="tab-item {{ !$status ? 'active' : '' }}">
                সকল শিক্ষার্থী (All)
            </a>
            <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['status' => 'ACTIVE'])) }}"
               class="tab-item {{ $status === 'ACTIVE' ? 'active' : '' }}">
                সক্রিয় (Active)
            </a>
            <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['status' => 'PENDING'])) }}"
               class="tab-item {{ $status === 'PENDING' ? 'active' : '' }}">
                অপেক্ষমান (Pending)
            </a>
            <a href="{{ route('admin.students.index', array_merge(request()->except('page'), ['status' => 'GRADUATED'])) }}"
               class="tab-item {{ $status === 'GRADUATED' ? 'active' : '' }}">
                উত্তীর্ণ (Graduated)
            </a>
        </div>

        <!-- Advanced Search & Filter Card -->
        <div class="filter-card">
            <div class="filter-header">
                <div class="filter-title">
                    <i class="fa-solid fa-sliders"></i>
                    <span>অ্যাডভান্সড সার্চ ও ফিল্টারিং (Advanced Search & Multi-Filter)</span>
                </div>
                <div style="font-size:12.5px;color:#64748b">
                    <i class="fa-solid fa-circle-info"></i> একাধিক ফিল্টার একসাথে প্রয়োগ করে নির্দিষ্ট শিক্ষার্থী খুঁজুন
                </div>
            </div>

            <form method="GET" action="{{ route('admin.students.index') }}" id="studentFilterForm">
                @if($status && !request()->filled('status'))
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <div class="filter-grid">
                    <!-- Keyword Search -->
                    <div class="filter-field" style="grid-column: 1 / -1">
                        <label>
                            <i class="fa-solid fa-magnifying-glass" style="color:#047857"></i>
                            সার্চ কীওয়ার্ড (নাম, কোড, ফোন, ইমেইল অথবা এনআইডি):
                        </label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="শিক্ষার্থীর পূর্ণ নাম, আইডি কোড (যেমন: 26-16-12-1-0001), মোবাইল নম্বর, ইমেইল বা NID লিখুন...">
                    </div>

                    <!-- Course Filter -->
                    <div class="filter-field">
                        <label><i class="fa-solid fa-book-open" style="color:#047857"></i> কোর্স (Course)</label>
                        <select name="course_id" id="course_filter">
                            <option value="">-- সকল কোর্স (All Courses) --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Batch Filter -->
                    <div class="filter-field">
                        <label><i class="fa-solid fa-layer-group" style="color:#047857"></i> ব্যাচ (Batch)</label>
                        <select name="batch_id" id="batch_filter">
                            <option value="">-- সকল ব্যাচ (All Batches) --</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}"
                                        data-course-id="{{ $b->course_id }}"
                                        {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Semester Filter -->
                    <div class="filter-field">
                        <label><i class="fa-solid fa-calendar-days" style="color:#047857"></i> সেমিস্টার (Semester)</label>
                        <select name="semester_id" id="semester_filter">
                            <option value="">-- সকল সেমিস্টার (All Semesters) --</option>
                            @foreach($semesters as $s)
                                <option value="{{ $s->id }}"
                                        data-course-id="{{ $s->course_id }}"
                                        {{ request('semester_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} @if($s->course) ({{ $s->course->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Gender Filter -->
                    <div class="filter-field">
                        <label><i class="fa-solid fa-venus-mars" style="color:#047857"></i> লিঙ্গ (Gender)</label>
                        <select name="gender">
                            <option value="">-- সকল লিঙ্গ (All Genders) --</option>
                            <option value="MALE" {{ strtoupper(request('gender')) === 'MALE' ? 'selected' : '' }}>পুরুষ (Male)</option>
                            <option value="FEMALE" {{ strtoupper(request('gender')) === 'FEMALE' ? 'selected' : '' }}>মহিলা (Female)</option>
                        </select>
                    </div>

                    <!-- Blood Group Filter -->
                    <div class="filter-field">
                        <label><i class="fa-solid fa-droplet" style="color:#e11d48"></i> রক্তের গ্রুপ (Blood Group)</label>
                        <select name="blood_group">
                            <option value="">-- সকল রক্তের গ্রুপ --</option>
                            @foreach($bloodGroups as $bg)
                                <option value="{{ $bg }}" {{ request('blood_group') === $bg ? 'selected' : '' }}>
                                    {{ $bg }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="filter-field">
                        <label><i class="fa-solid fa-circle-check" style="color:#047857"></i> স্ট্যাটাস (Status)</label>
                        <select name="status">
                            <option value="">-- সকল স্ট্যাটাস --</option>
                            <option value="ACTIVE" {{ strtoupper(request('status')) === 'ACTIVE' ? 'selected' : '' }}>সক্রিয় (Active)</option>
                            <option value="PENDING" {{ strtoupper(request('status')) === 'PENDING' ? 'selected' : '' }}>অপেক্ষমান (Pending)</option>
                            <option value="LEAD" {{ strtoupper(request('status')) === 'LEAD' ? 'selected' : '' }}>লিড (Lead)</option>
                            <option value="GRADUATED" {{ strtoupper(request('status')) === 'GRADUATED' ? 'selected' : '' }}>উত্তীর্ণ (Graduated)</option>
                            <option value="INACTIVE" {{ strtoupper(request('status')) === 'INACTIVE' ? 'selected' : '' }}>নিষ্ক্রিয় (Inactive)</option>
                        </select>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="filter-actions">
                    <div class="filter-actions-left">
                        <button type="submit" class="btn btn-primary" style="background:#047857;border-color:#047857;font-weight:700">
                            <i class="fa-solid fa-filter"></i> ফিল্টার প্রয়োগ করুন (Filter)
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline" style="border-color:#cbd5e1;color:#475569">
                            <i class="fa-solid fa-rotate-left"></i> রিসেট (Reset)
                        </a>
                    </div>
                    <div class="filter-actions-right">
                        <a href="{{ route('admin.students.export-csv', request()->query()) }}" class="btn btn-outline" style="border-color:#059669;color:#047857;font-weight:700">
                            <i class="fa-solid fa-file-excel"></i> ফিল্টারকৃত ডাটা এক্সপোর্ট CSV (Export)
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Active Filter Indicator Banner -->
        @if($hasFilters)
            <div class="active-filter-bar">
                <span><i class="fa-solid fa-filter-circle-xmark"></i> <strong>ফিল্টার প্রয়োগকৃত:</strong> মোট {{ $students->total() }} জন শিক্ষার্থী পাওয়া গেছে।</span>
                @if(request('search'))
                    <span class="active-filter-badge">সার্চ: "{{ request('search') }}"</span>
                @endif
                @if(request('course_id') && ($matchedCourse = $courses->firstWhere('id', request('course_id'))))
                    <span class="active-filter-badge">কোর্স: {{ $matchedCourse->name }}</span>
                @endif
                @if(request('batch_id') && ($matchedBatch = $batches->firstWhere('id', request('batch_id'))))
                    <span class="active-filter-badge">ব্যাচ: {{ $matchedBatch->name }}</span>
                @endif
                @if(request('semester_id') && ($matchedSem = $semesters->firstWhere('id', request('semester_id'))))
                    <span class="active-filter-badge">সেমিস্টার: {{ $matchedSem->name }}</span>
                @endif
                @if(request('gender'))
                    <span class="active-filter-badge">লিঙ্গ: {{ request('gender') === 'MALE' ? 'পুরুষ' : (request('gender') === 'FEMALE' ? 'মহিলা' : request('gender')) }}</span>
                @endif
                @if(request('blood_group'))
                    <span class="active-filter-badge">রক্তের গ্রুপ: {{ request('blood_group') }}</span>
                @endif
                @if(request('status'))
                    <span class="active-filter-badge">স্ট্যাটাস: {{ request('status') }}</span>
                @endif
                <a href="{{ route('admin.students.index') }}" style="margin-left:auto;color:#b91c1c;font-weight:700;font-size:12px;text-decoration:none">
                    <i class="fa-solid fa-xmark"></i> ফিল্টার মুছুন
                </a>
            </div>
        @endif

        <!-- Students Table -->
        <div class="card" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);border: 1px solid #e2e8f0;border-radius:14px;overflow:hidden">
            <div class="table-wrapper">
                <table style="width:100%;margin-bottom:0">
                    <thead style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                        <tr>
                            <th style="font-weight:700;color:#334155;padding:14px 16px;white-space:nowrap">স্টুডেন্ট আইডি</th>
                            <th style="font-weight:700;color:#334155;padding:14px 16px">শিক্ষার্থীর নাম ও লিঙ্গ</th>
                            <th style="font-weight:700;color:#334155;padding:14px 16px">ফোন ও ইমেইল</th>
                            <th style="font-weight:700;color:#334155;padding:14px 16px">বর্তমান কোর্স ও ব্যাচ / সেমিস্টার</th>
                            <th style="font-weight:700;color:#334155;padding:14px 16px">রক্তের গ্রুপ</th>
                            <th style="font-weight:700;color:#334155;padding:14px 16px">স্ট্যাটাস</th>
                            <th style="text-align:right;font-weight:700;color:#334155;padding:14px 16px">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $st)
                        <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s ease">
                            <!-- Student Code & Direct Login -->
                            <td style="padding:14px 16px;white-space:nowrap">
                                @if($st->student_code)
                                    <a href="{{ route('admin.students.impersonate', $st) }}"
                                       class="student-code-badge"
                                       title="শিক্ষার্থী পোর্টালে সরাসরি লগইন করুন (Click to login as student)">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
                                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                            <polyline points="10 17 15 12 10 7"></polyline>
                                            <line x1="15" y1="12" x2="3" y2="12"></line>
                                        </svg>
                                        <span>{{ str_replace('-', '', $st->student_code) }}</span>
                                    </a>
                                @else
                                    <span class="td-muted" style="color:#94a3b8;font-size:12.5px;font-style:italic">বরাদ্দ হয়নি (Unassigned)</span>
                                @endif
                            </td>

                            <!-- Name & Gender -->
                            <td style="padding:14px 16px">
                                <a href="{{ route('admin.students.show', $st) }}" style="font-weight:700;color:#047857;text-decoration:none;font-size:14.5px">
                                    {{ $st->name }}
                                </a>
                                @if($st->is_common_account)
                                    <span style="display:inline-block;font-size:11px;font-weight:700;background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;padding:1px 6px;border-radius:4px;margin-left:4px;vertical-align:middle" title="স্পেশাল কোর্স কমন/শেয়ার্ড অ্যাকাউন্ট">
                                        <i class="fa-solid fa-users"></i> কমন
                                    </span>
                                @endif
                                <div>
                                    @if(strtoupper($st->gender ?? '') === 'MALE')
                                        <span class="gender-badge gender-male"><i class="fa-solid fa-mars"></i> পুরুষ (Male)</span>
                                    @elseif(strtoupper($st->gender ?? '') === 'FEMALE')
                                        <span class="gender-badge gender-female"><i class="fa-solid fa-venus"></i> মহিলা (Female)</span>
                                    @elseif($st->gender)
                                        <span class="gender-badge gender-other">{{ $st->gender }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Phone / Email -->
                            <td style="padding:14px 16px">
                                <div style="font-weight:600;color:#1e293b;font-size:13px;display:flex;align-items:center;gap:6px">
                                    <i class="fa-solid fa-phone" style="font-size:11px;color:#047857"></i>
                                    <span>{{ $st->phone }}</span>
                                </div>
                                <div style="font-size:12px;color:#64748b;margin-top:2px;display:flex;align-items:center;gap:6px">
                                    <i class="fa-regular fa-envelope" style="font-size:11px;color:#94a3b8"></i>
                                    <span>{{ $st->email ?? '—' }}</span>
                                </div>
                            </td>

                            <!-- Active Course, Batch & Semester -->
                            <td style="padding:14px 16px">
                                @php
                                    $activeEnr = $st->enrollments->firstWhere('status', 'ACTIVE') ?? $st->enrollments->first();
                                @endphp
                                @if($activeEnr)
                                    <div class="course-info-box">
                                        <strong>{{ $activeEnr->batch->name ?? '—' }}</strong>
                                        @if($activeEnr->semester)
                                            <span style="font-size:11px;background:#f1f5f9;color:#475569;padding:1px 6px;border-radius:4px;margin-left:4px">
                                                {{ $activeEnr->semester->name }}
                                            </span>
                                        @endif
                                        <div class="course-info-sub">
                                            {{ $activeEnr->batch->course->title ?? $activeEnr->batch->course->name ?? $activeEnr->course->name ?? '—' }}
                                        </div>
                                    </div>
                                @else
                                    <span class="td-muted" style="color:#94a3b8;font-size:12.5px;font-style:italic">কোনো সক্রিয় এনরোলমেন্ট নেই</span>
                                @endif
                            </td>

                            <!-- Blood Group -->
                            <td style="padding:14px 16px">
                                @if($st->blood_group)
                                    <span class="blood-badge">
                                        <i class="fa-solid fa-droplet" style="font-size:10px"></i> {{ $st->blood_group }}
                                    </span>
                                @else
                                    <span style="color:#94a3b8;font-size:12px">—</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td style="padding:14px 16px">
                                <span class="badge badge-{{ strtolower($st->status ?? 'active') }}">
                                    {{ ucfirst(strtolower($st->status ?? 'active')) }}
                                </span>
                            </td>

                            <!-- Action Buttons -->
                            <td style="text-align:right;white-space:nowrap;padding:14px 16px">
                                <a href="{{ route('admin.students.impersonate', $st) }}"
                                   class="btn btn-outline btn-sm"
                                   style="color:#047857;border-color:#a7f3d0;margin-right:4px;display:inline-flex;align-items:center;gap:4px"
                                   title="শিক্ষার্থী হিসেবে লগইন">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                    <span>লগইন</span>
                                </a>
                                <a href="{{ route('admin.students.accounts', $st) }}"
                                   class="btn btn-outline btn-sm"
                                   style="color:#4f46e5;border-color:#c7d2fe;margin-right:4px"
                                   title="লেজার ও ফি হিসাব">
                                    <i class="fa-solid fa-wallet"></i> লেজার
                                </a>
                                <a href="{{ route('admin.students.show', $st) }}"
                                   class="btn btn-outline btn-sm"
                                   style="border-color:#cbd5e1">
                                    প্রোফাইল →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:50px 20px;color:#64748b">
                                <div style="font-size:40px;color:#cbd5e1;margin-bottom:12px">
                                    <i class="fa-solid fa-user-slash"></i>
                                </div>
                                <h3 style="margin:0 0 6px;font-size:16px;font-weight:700;color:#1e293b">কোনো শিক্ষার্থী পাওয়া যায়নি</h3>
                                <p style="margin:0 0 16px;font-size:13.5px;color:#64748b">
                                    আপনার প্রদত্ত সার্চ অথবা ফিল্টার ক্রাইটেরিয়ার সাথে মিলে এমন কোনো শিক্ষার্থী নেই।
                                </p>
                                <a href="{{ route('admin.students.index') }}" class="btn btn-outline btn-sm" style="border-color:#cbd5e1">
                                    <i class="fa-solid fa-rotate-left"></i> সকল ফিল্টার রিসেট করুন
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            @if($students->hasPages())
                <div style="padding:16px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                    <div style="font-size:13px;color:#64748b">
                        মোট <strong>{{ $students->total() }}</strong> জন শিক্ষার্থীর মধ্যে <strong>{{ $students->firstItem() }}</strong> থেকে <strong>{{ $students->lastItem() }}</strong> দেখানো হচ্ছে
                    </div>
                    <div>
                        {{ $students->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Dynamic Cascading Dropdowns for Course, Batch & Semester -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const courseSelect = document.getElementById('course_filter');
            const batchSelect = document.getElementById('batch_filter');
            const semesterSelect = document.getElementById('semester_filter');

            if (!courseSelect || !batchSelect || !semesterSelect) return;

            // Cache all original options
            const allBatchOptions = Array.from(batchSelect.options);
            const allSemesterOptions = Array.from(semesterSelect.options);

            function updateCascadingDropdowns() {
                const selectedCourseId = courseSelect.value;
                const currentBatchVal = batchSelect.value;
                const currentSemesterVal = semesterSelect.value;

                // Update Batch Dropdown
                batchSelect.innerHTML = '';
                allBatchOptions.forEach(opt => {
                    const courseId = opt.getAttribute('data-course-id');
                    if (!opt.value || !selectedCourseId || courseId === selectedCourseId) {
                        batchSelect.appendChild(opt.cloneNode(true));
                    }
                });

                // Restore batch value if still present in filtered list
                if (Array.from(batchSelect.options).some(o => o.value === currentBatchVal)) {
                    batchSelect.value = currentBatchVal;
                } else {
                    batchSelect.value = '';
                }

                // Update Semester Dropdown
                semesterSelect.innerHTML = '';
                allSemesterOptions.forEach(opt => {
                    const courseId = opt.getAttribute('data-course-id');
                    if (!opt.value || !selectedCourseId || courseId === selectedCourseId) {
                        semesterSelect.appendChild(opt.cloneNode(true));
                    }
                });

                // Restore semester value if still present in filtered list
                if (Array.from(semesterSelect.options).some(o => o.value === currentSemesterVal)) {
                    semesterSelect.value = currentSemesterVal;
                } else {
                    semesterSelect.value = '';
                }
            }

            courseSelect.addEventListener('change', updateCascadingDropdowns);

            // Run once on load if a course is pre-selected
            if (courseSelect.value) {
                updateCascadingDropdowns();
            }
        });
    </script>
</x-admin-layout>
