<x-admin-layout>
    <x-slot name="title">শিক্ষার্থী প্রোফাইল — {{ $student->name }} ({{ $student->student_code }})</x-slot>

    <style>
        .student-profile-container {
            font-family: 'Kalpurush', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
        }
        .profile-hero-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        .hero-student-meta {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .hero-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
            overflow: hidden;
        }
        .hero-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .hero-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 6px;
            letter-spacing: -0.01em;
        }
        .hero-badge-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .hero-code-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            white-space: nowrap !important;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .access-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .access-active {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }
        .access-disabled {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }
        .hero-actions-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .btn-hero {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-hero-primary {
            background: #2563eb;
            color: #fff;
        }
        .btn-hero-primary:hover {
            background: #1d4ed8;
            color: #fff;
        }
        .btn-hero-emerald {
            background: #059669;
            color: #fff;
        }
        .btn-hero-emerald:hover {
            background: #047857;
            color: #fff;
        }
        .btn-hero-indigo {
            background: #4f46e5;
            color: #fff;
        }
        .btn-hero-indigo:hover {
            background: #4338ca;
            color: #fff;
        }
        .btn-hero-outline {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-hero-outline:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .btn-hero-danger {
            background: rgba(220, 38, 38, 0.85);
            color: #fff;
        }
        .btn-hero-danger:hover {
            background: #b91c1c;
            color: #fff;
        }

        /* Profile Tabs */
        .profile-tabs-nav {
            display: flex;
            gap: 6px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 24px;
            overflow-x: auto;
            padding-bottom: 2px;
        }
        .profile-tab-btn {
            background: transparent;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
        }
        .profile-tab-btn.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: #eff6ff;
        }
        .profile-tab-btn:hover:not(.active) {
            color: #1e293b;
            background: #f8fafc;
        }

        /* Content panels */
        .tab-panel {
            display: none;
        }
        .tab-panel.active {
            display: block;
        }

        /* Stat Cards */
        .stat-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-mini-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-mini-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* Modal Overlay */
        .admin-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .admin-modal-overlay.active {
            display: flex;
        }
        .admin-modal-box {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
            border: 1px solid #e2e8f0;
        }
        .admin-modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border-radius: 16px 16px 0 0;
        }
        .admin-modal-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .admin-modal-body {
            padding: 24px;
        }
        .admin-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
        }

        /* Detail table rows */
        .detail-row {
            display: flex;
            padding: 12px 18px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            width: 170px;
            color: #64748b;
            font-weight: 500;
            flex-shrink: 0;
        }
        .detail-value {
            font-weight: 600;
            color: #0f172a;
            flex-grow: 1;
        }
    </style>

    <div class="student-profile-container">

        {{-- Top Back Nav --}}
        <div style="font-size:13px;color:#64748b;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between">
            <a href="{{ route('admin.students.index') }}" style="text-decoration:none;color:#2563eb;font-weight:600">
                ← শিক্ষার্থীদের তালিকায় ফিরে যান (Back to Students)
            </a>
            <span style="font-size:12px;color:#94a3b8">নিবন্ধন: {{ $student->created_at ? $student->created_at->format('d M Y, h:i A') : '—' }}</span>
        </div>

        {{-- Hero Header Profile Card --}}
        <div class="profile-hero-card">
            <div class="hero-student-meta">
                <div class="hero-avatar">
                    @if($student->photo_url)
                        <img src="{{ asset($student->photo_url) }}" alt="{{ $student->name }}">
                    @else
                        {{ mb_substr($student->name, 0, 1, 'UTF-8') }}
                    @endif
                </div>
                <div>
                    <h1 class="hero-title">{{ $student->name }}</h1>
                    <div class="hero-badge-group">
                        <span class="hero-code-badge" title="স্টুডেন্ট আইডি (Student Code)">
                            🆔 {{ str_replace('-', '', $student->student_code ?? 'N/A') }}
                        </span>

                        {{-- Academic Status Badge --}}
                        @php
                            $statusStyle = match(strtoupper($student->status ?? '')) {
                                'ACTIVE'      => 'background:#059669;color:#fff;',
                                'CANCELLED'   => 'background:#dc2626;color:#fff;',
                                'DROPPED'     => 'background:#ea580c;color:#fff;',
                                'GRADUATED',
                                'COMPLETED'   => 'background:#2563eb;color:#fff;',
                                default       => 'background:#d97706;color:#fff;',
                            };
                        @endphp
                        <span class="access-badge" style="{{ $statusStyle }}">
                            {{ $student->status ?? 'ACTIVE' }}
                        </span>

                        {{-- Course Access Toggle Badge --}}
                        @if($student->has_course_access ?? true)
                            <span class="access-badge access-active" title="শিক্ষার্থী কোর্স সামগ্রী ও পরীক্ষায় অংশ নিতে পারছে">
                                <i class="fa-solid fa-circle-check"></i> কোর্স অ্যাক্সেস চালু (Active)
                            </span>
                        @else
                            <span class="access-badge access-disabled" title="কোর্স সামগ্রী সাময়িকভাবে স্থগিত">
                                <i class="fa-solid fa-circle-xmark"></i> কোর্স অ্যাক্সেস বন্ধ (Disabled)
                            </span>
                        @endif

                        {{-- Common Shared Account Badge --}}
                        @if($student->is_common_account)
                            <span class="access-badge" style="background:#2563eb;color:#fff;border:1px solid #60a5fa" title="স্পেশাল কোর্স সাধারণ শেয়ার্ড অ্যাকাউন্ট (প্রোফাইল তথ্য ও পাসওয়ার্ড লক করা)">
                                <i class="fa-solid fa-users"></i> কমন শেয়ার্ড অ্যাকাউন্ট
                            </span>
                        @endif

                        @if($student->gender)
                            <span class="hero-code-badge" style="background:rgba(255,255,255,0.1)">
                                {{ $student->gender === 'MALE' ? 'পুরুষ' : ($student->gender === 'FEMALE' ? 'মহিলা' : $student->gender) }}
                            </span>
                        @endif

                        @if($student->blood_group)
                            <span class="hero-code-badge" style="background:rgba(239,68,68,0.25);color:#fca5a5;border-color:rgba(239,68,68,0.4)">
                                🩸 {{ $student->blood_group }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Hero Actions Bar --}}
            <div class="hero-actions-bar">
                {{-- Course Access Toggle Action --}}
                <form method="POST" action="{{ route('admin.students.toggle-course-access', $student) }}" style="display:inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই শিক্ষার্থীর কোর্স অ্যাক্সেস পরিবর্তন করতে চান?');">
                    @csrf
                    <input type="hidden" name="has_course_access" value="{{ ($student->has_course_access ?? true) ? 0 : 1 }}">
                    <button type="submit" class="btn-hero {{ ($student->has_course_access ?? true) ? 'btn-hero-outline' : 'btn-hero-emerald' }}" title="কোর্স অ্যাক্সেস অন/অফ করুন">
                        <i class="fa-solid fa-power-off"></i> 
                        {{ ($student->has_course_access ?? true) ? 'অ্যাক্সেস বন্ধ করুন' : 'অ্যাক্সেস চালু করুন' }}
                    </button>
                </form>

                {{-- Impersonate Student --}}
                <a href="{{ route('admin.students.impersonate', $student) }}" class="btn-hero btn-hero-emerald" title="শিক্ষার্থী হিসেবে সরাসরি লগইন করুন">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> শিক্ষার্থী হিসেবে লগইন
                </a>

                {{-- Password Reset Modal Trigger --}}
                <button type="button" class="btn-hero btn-hero-outline" onclick="openModal('passwordResetModal')" title="পাসওয়ার্ড দেখুন বা রিসেট করুন">
                    <i class="fa-solid fa-key"></i> পাসওয়ার্ড রিসেট
                </button>

                {{-- Cancel Admission Modal Trigger --}}
                @if($student->status !== 'CANCELLED')
                <button type="button" class="btn-hero btn-hero-danger" onclick="openModal('cancelAdmissionModal')" title="ভর্তি সম্পূর্ণভাবে বাতিল করুন">
                    <i class="fa-solid fa-user-xmark"></i> ভর্তি বাতিল
                </button>
                @endif
            </div>
        </div>

        {{-- Profile Navigation Tabs --}}
        <div class="profile-tabs-nav">
            <button type="button" class="profile-tab-btn active" onclick="switchTab('tab-personal', this)">
                <i class="fa-solid fa-user"></i> ব্যক্তিগত ও পারিবারিক বিবরণ
            </button>
            <button type="button" class="profile-tab-btn" onclick="switchTab('tab-finance', this)">
                <i class="fa-solid fa-hand-holding-dollar"></i> ফি ও পুওর ফান্ড কাঠামো
            </button>
            <button type="button" class="profile-tab-btn" onclick="switchTab('tab-academic', this)">
                <i class="fa-solid fa-graduation-cap"></i> কোর্স ও ফলাফল
            </button>
            <button type="button" class="profile-tab-btn" onclick="switchTab('tab-audit', this)">
                <i class="fa-solid fa-shield-halved"></i> অ্যাডমিন অডিট হিস্ট্রি ({{ $student->auditLogs->count() }})
            </button>
            <button type="button" class="profile-tab-btn" onclick="switchTab('tab-logins', this)">
                <i class="fa-solid fa-clock-rotate-left"></i> লগইন হিস্ট্রি ({{ $student->loginHistories->count() }})
            </button>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: PERSONAL & FAMILY DETAILS                                   --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-personal" class="tab-panel active">
            <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
                <button type="button" class="btn btn-primary" style="font-weight:600;display:inline-flex;align-items:center;gap:6px" onclick="openModal('editProfileModal')">
                    <i class="fa-solid fa-pen-to-square"></i> তথ্য সম্পাদনা করুন (Edit Details)
                </button>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(360px, 1fr));gap:20px">
                {{-- Personal & Contact Info Card --}}
                <div class="card" style="box-shadow:0 1px 3px rgba(0,0,0,0.05);border-radius:12px">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px">
                        <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                            <i class="fa-solid fa-id-badge" style="color:#2563eb;margin-right:8px"></i> ব্যক্তিগত ও যোগাযোগের তথ্য
                        </span>
                    </div>
                    <div>
                        <div class="detail-row">
                            <span class="detail-label">পূর্ণ নাম:</span>
                            <span class="detail-value">{{ $student->name }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">স্টুডেন্ট আইডি:</span>
                            <span class="detail-value" style="color:#2563eb;font-weight:700">{{ str_replace('-', '', $student->student_code ?? '—') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">মোবাইল নম্বর:</span>
                            <span class="detail-value">{{ $student->phone ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ইমেইল ঠিকানা:</span>
                            <span class="detail-value">{{ $student->email ?? ($student->user?->email ?? '—') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">জন্ম তারিখ:</span>
                            <span class="detail-value">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">লিঙ্গ:</span>
                            <span class="detail-value">{{ $student->gender ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">রক্তের গ্রুপ:</span>
                            <span class="detail-value">{{ $student->blood_group ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">জাতীয় পরিচয়পত্র / NID:</span>
                            <span class="detail-value">{{ $student->national_id ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">বর্তমান পেশা:</span>
                            <span class="detail-value">{{ $student->occupation ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Family Info Card --}}
                <div class="card" style="box-shadow:0 1px 3px rgba(0,0,0,0.05);border-radius:12px">
                    <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px">
                        <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                            <i class="fa-solid fa-users" style="color:#059669;margin-right:8px"></i> অভিভাবক ও পারিবারিক তথ্য
                        </span>
                    </div>
                    <div>
                        <div class="detail-row">
                            <span class="detail-label">পিতার নাম:</span>
                            <span class="detail-value">{{ $student->father_name ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">মাতার নাম:</span>
                            <span class="detail-value">{{ $student->mother_name ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">অভিভাবকের নাম:</span>
                            <span class="detail-value">{{ $student->guardian_name ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">অভিভাবকের ফোন:</span>
                            <span class="detail-value">{{ $student->guardian_phone ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">অভিভাবকের সম্পর্ক:</span>
                            <span class="detail-value">{{ $student->guardian_relation ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">বর্তমান ঠিকানা:</span>
                            <span class="detail-value">{{ $student->address ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">স্থায়ী ঠিকানা:</span>
                            <span class="detail-value">{{ $student->permanent_address ?? ($student->address ?? '—') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">শিক্ষাগত যোগ্যতা:</span>
                            <span class="detail-value">{{ $student->education_qualification ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: FINANCIAL & POOR FUND LEDGER                                --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-finance" class="tab-panel">
            @php
                $totalInvoiced = $student->invoices->sum('amount');
                $totalPaid = $student->invoices->sum('paid_amount');
                $totalDue = $totalInvoiced - $totalPaid;
            @endphp

            {{-- Stat Cards --}}
            <div class="stat-grid-3">
                <div class="stat-mini-card">
                    <div class="stat-mini-icon" style="background:#eff6ff;color:#2563eb">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#64748b;font-weight:600">মোট ধার্যকৃত ফি (Total Invoiced)</div>
                        <div style="font-size:22px;font-weight:700;color:#0f172a">৳{{ number_format($totalInvoiced, 0) }}</div>
                    </div>
                </div>

                <div class="stat-mini-card">
                    <div class="stat-mini-icon" style="background:#ecfdf5;color:#059669">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#64748b;font-weight:600">মোট পরিশোধিত (Total Paid)</div>
                        <div style="font-size:22px;font-weight:700;color:#059669">৳{{ number_format($totalPaid, 0) }}</div>
                    </div>
                </div>

                <div class="stat-mini-card">
                    <div class="stat-mini-icon" style="background:{{ $totalDue > 0 ? '#fef2f2' : '#f8fafc' }};color:{{ $totalDue > 0 ? '#dc2626' : '#64748b' }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <div style="font-size:12px;color:#64748b;font-weight:600">মোট বকেয়া (Total Due)</div>
                        <div style="font-size:22px;font-weight:700;color:{{ $totalDue > 0 ? '#dc2626' : '#059669' }}">৳{{ number_format($totalDue, 0) }}</div>
                    </div>
                </div>
            </div>

            {{-- Poor Fund & Fee Package Adjustment Card --}}
            <div class="card" style="margin-bottom:24px;border-radius:12px;border:1px solid #e2e8f0">
                <div class="card-header" style="background:#f8fafc;padding:14px 20px;display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                            <i class="fa-solid fa-hand-holding-heart" style="color:#d97706;margin-right:8px"></i> ফি কাঠামো ও পুওর ফান্ড সমন্বয় (Fee Structure & Poor Fund)
                        </span>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">শিক্ষার্থীর জন্য প্রযোজ্য মাসিক ফি প্যাকেজ ও স্কলারশিপ বা ছাড়</div>
                    </div>
                    <button type="button" class="btn btn-outline" style="color:#d97706;border-color:#f59e0b;font-weight:700" onclick="openModal('adjustFeeModal')">
                        <i class="fa-solid fa-sliders"></i> কাঠামো পরিবর্তন করুন
                    </button>
                </div>
                <div style="padding:20px">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px">
                        <div style="background:#f8fafc;padding:16px;border-radius:10px;border:1px solid #e2e8f0">
                            <div style="font-size:12px;color:#64748b;margin-bottom:4px">নির্ধারিত ফি প্যাকেজ:</div>
                            <div style="font-size:16px;font-weight:700;color:#0f172a">
                                {{ $student->feePackage->name ?? 'ডিফল্ট কোর্স ফি প্যাকেজ' }}
                            </div>
                            @if($student->feePackage)
                            <div style="font-size:12px;color:#059669;margin-top:4px">মাসিক ফি: ৳{{ number_format($student->feePackage->monthly_fee ?? 0, 0) }}</div>
                            @endif
                        </div>

                        <div style="background:#f8fafc;padding:16px;border-radius:10px;border:1px solid #e2e8f0">
                            <div style="font-size:12px;color:#64748b;margin-bottom:4px">মাসিক পুওর ফান্ড ছাড় / ওয়েভার:</div>
                            <div style="font-size:16px;font-weight:700;color:{{ ($student->monthly_discount ?? 0) > 0 ? '#059669' : '#64748b' }}">
                                @if(($student->monthly_discount ?? 0) > 0)
                                    {{ $student->discount_type === 'PERCENT' ? ($student->monthly_discount . '%') : ('৳' . number_format($student->monthly_discount, 0)) }}
                                    <span style="font-size:11px;font-weight:normal;color:#64748b">({{ $student->discount_type === 'PERCENT' ? 'শতাংশ' : 'নির্দিষ্ট ছাড়' }})</span>
                                @else
                                    কোনো ছাড় প্রযোজ্য নয় (৳0)
                                @endif
                            </div>
                        </div>

                        <div style="background:#f8fafc;padding:16px;border-radius:10px;border:1px solid #e2e8f0">
                            <div style="font-size:12px;color:#64748b;margin-bottom:4px">পুওর ফান্ড মন্তব্য / রেফারেন্স:</div>
                            <div style="font-size:13px;color:#334155;font-style:italic">
                                {{ $student->poor_fund_remarks ?: 'কোনো বিশেষ নির্দেশনা নেই।' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Invoices Table --}}
            <div class="card" style="border-radius:12px;border:1px solid #e2e8f0">
                <div class="card-header" style="background:#f8fafc;padding:14px 20px;display:flex;justify-content:space-between;align-items:center">
                    <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                        <i class="fa-solid fa-receipt" style="color:#2563eb;margin-right:8px"></i> ফি ইনভয়েস ও রসিদ তালিকা
                    </span>
                    <a href="{{ route('admin.students.accounts', $student) }}" class="btn btn-outline" style="color:#2563eb;border-color:#93c5fd;font-weight:700">
                        <i class="fa-solid fa-wallet"></i> পূর্ণ একাউন্টস লেজার খুলুন
                    </a>
                </div>
                <div style="padding:0;overflow-x:auto">
                    <table class="table" style="width:100%;margin:0;font-size:13px">
                        <thead>
                            <tr style="background:#f8fafc;color:#64748b">
                                <th style="padding:10px 16px">ইনভয়েস বিবরণ</th>
                                <th style="padding:10px 16px">ক্যাটাগরি</th>
                                <th style="padding:10px 16px;text-align:right">মোট ফি</th>
                                <th style="padding:10px 16px;text-align:right">পরিশোধ</th>
                                <th style="padding:10px 16px;text-align:right">বকেয়া</th>
                                <th style="padding:10px 16px;text-align:center">স্ট্যাটাস</th>
                                <th style="padding:10px 16px;text-align:center">তারিখ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($student->invoices as $inv)
                            <tr style="border-bottom:1px solid #f1f5f9">
                                <td style="padding:10px 16px;font-weight:600">{{ $inv->title ?? $inv->fee_type }}</td>
                                <td style="padding:10px 16px;color:#64748b">{{ $inv->category ?? 'TUITION' }}</td>
                                <td style="padding:10px 16px;text-align:right;font-weight:600">৳{{ number_format($inv->amount, 0) }}</td>
                                <td style="padding:10px 16px;text-align:right;color:#059669;font-weight:600">৳{{ number_format($inv->paid_amount, 0) }}</td>
                                <td style="padding:10px 16px;text-align:right;color:{{ ($inv->amount - $inv->paid_amount) > 0 ? '#dc2626' : '#64748b' }};font-weight:600">
                                    ৳{{ number_format($inv->amount - $inv->paid_amount, 0) }}
                                </td>
                                <td style="padding:10px 16px;text-align:center">
                                    <span class="badge badge-{{ strtolower($inv->status) }} no-dot" style="font-size:11px">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px;text-align:center;color:#64748b">
                                    {{ $inv->created_at ? $inv->created_at->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" style="padding:24px;text-align:center;color:#94a3b8">
                                    কোনো ইনভয়েস পাওয়া যায়নি।
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: COURSES & ACADEMIC RESULTS                                  --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-academic" class="tab-panel">
            <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
                <a href="{{ route('admin.students.transcript', $student) }}" target="_blank" class="btn btn-outline" style="color:#4f46e5;border-color:#818cf8;font-weight:700">
                    <i class="fa-solid fa-scroll"></i> ৬-সেমিস্টার কনসলিডেটেড ট্রান্সক্রিপ্ট (Academic Transcript)
                </a>
            </div>

            {{-- Course Enrollments --}}
            <div class="card" style="margin-bottom:24px;border-radius:12px;border:1px solid #e2e8f0">
                <div class="card-header" style="background:#f8fafc;padding:14px 20px">
                    <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                        <i class="fa-solid fa-book-open-reader" style="color:#2563eb;margin-right:8px"></i> এনরোল্ড কোর্স ও ব্যাচ
                    </span>
                </div>
                <div style="padding:0">
                    @forelse($student->enrollments as $enr)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #f1f5f9">
                        <div>
                            <div style="font-size:15px;font-weight:700;color:#0f172a">
                                {{ $enr->batch->course->name ?? '—' }}
                            </div>
                            <div style="font-size:13px;color:#64748b;margin-top:2px">
                                ব্যাচ: <strong>{{ $enr->batch->name ?? '—' }}</strong> · 
                                সেমিস্টার: <strong>{{ $enr->semester->name ?? '১ম সেমিস্টার' }}</strong> · 
                                এনরোলমেন্ট তারিখ: {{ $enr->enrolled_at ? \Carbon\Carbon::parse($enr->enrolled_at)->format('d M Y') : '—' }}
                            </div>
                        </div>
                        <div>
                            <span class="badge badge-{{ strtolower($enr->status) }}">
                                {{ $enr->status }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div style="padding:24px;text-align:center;color:#94a3b8">
                        কোনো সক্রিয় কোর্স এনরোলমেন্ট নেই।
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Final Exam Marks & Results --}}
            <div class="card" style="border-radius:12px;border:1px solid #e2e8f0">
                <div class="card-header" style="background:#f8fafc;padding:14px 20px">
                    <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                        <i class="fa-solid fa-award" style="color:#059669;margin-right:8px"></i> বিষয়ভিত্তিক চূড়ান্ত ফলাফল ও মেরিট পজিশন
                    </span>
                </div>
                <div style="padding:0;overflow-x:auto">
                    <table class="table" style="width:100%;margin:0;font-size:13px">
                        <thead>
                            <tr style="background:#f8fafc;color:#64748b">
                                <th style="padding:10px 16px">বিষয় (Subject)</th>
                                <th style="padding:10px 16px">সেমিস্টার</th>
                                <th style="padding:10px 16px;text-align:center">সিটি/মিড/ফাইনাল</th>
                                <th style="padding:10px 16px;text-align:center">উপস্থিতি ও তামরিন</th>
                                <th style="padding:10px 16px;text-align:center">মোট মার্ক</th>
                                <th style="padding:10px 16px;text-align:center">গ্রেড ও জিপিএ</th>
                                <th style="padding:10px 16px;text-align:center">কওমি মান</th>
                                <th style="padding:10px 16px;text-align:center">মেরিট পজিশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($student->finalMarks as $fm)
                            @php
                                $qawmi = \App\Models\FinalMark::calculateQawmiGrade($fm->total_mark, $fm->gpa);
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9">
                                <td style="padding:10px 16px;font-weight:600">
                                    {{ $fm->subject->name ?? '—' }}
                                    <div style="font-size:11px;color:#64748b">{{ $fm->subject->code ?? '' }}</div>
                                </td>
                                <td style="padding:10px 16px;color:#64748b">{{ $fm->semester->name ?? '—' }}</td>
                                <td style="padding:10px 16px;text-align:center">
                                    {{ $fm->class_test_converted }} + {{ $fm->midterm_converted }} + {{ $fm->final_converted }}
                                </td>
                                <td style="padding:10px 16px;text-align:center">
                                    {{ $fm->attendance_converted }} + {{ ($fm->tamrin_mark ?? 0) + ($fm->tajweed_mark ?? 0) + ($fm->dns_mark ?? 0) }}
                                </td>
                                <td style="padding:10px 16px;text-align:center;font-weight:700;color:#2563eb">
                                    {{ number_format($fm->total_mark, 1) }}
                                </td>
                                <td style="padding:10px 16px;text-align:center">
                                    <span class="badge no-dot" style="background:#eff6ff;color:#2563eb;font-weight:700">
                                        {{ $fm->grade }} ({{ number_format($fm->gpa, 2) }})
                                    </span>
                                </td>
                                <td style="padding:10px 16px;text-align:center;font-size:12px;font-weight:600;color:#059669">
                                    {{ $qawmi['name_bn'] ?? '—' }}
                                </td>
                                <td style="padding:10px 16px;text-align:center">
                                    @if($fm->merit_position)
                                    <span style="font-weight:700;color:#d97706;background:#fef3c7;padding:2px 8px;border-radius:12px">
                                        {{ $fm->merit_rank_bengali }}
                                    </span>
                                    @else
                                    <span style="color:#94a3b8">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="padding:24px;text-align:center;color:#94a3b8">
                                    কোনো চূড়ান্ত ফলাফল এখনও প্রকাশিত হয়নি।
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 4: ADMIN AUDIT HISTORY                                         --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-audit" class="tab-panel">
            <div class="card" style="border-radius:12px;border:1px solid #e2e8f0">
                <div class="card-header" style="background:#f8fafc;padding:14px 20px">
                    <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                        <i class="fa-solid fa-shield-halved" style="color:#2563eb;margin-right:8px"></i> অ্যাডমিন অডিট হিস্ট্রি ও পরিবর্তনের তালিকা
                    </span>
                </div>
                <div style="padding:0;overflow-x:auto">
                    <table class="table" style="width:100%;margin:0;font-size:13px">
                        <thead>
                            <tr style="background:#f8fafc;color:#64748b">
                                <th style="padding:10px 16px">তারিখ ও সময়</th>
                                <th style="padding:10px 16px">অ্যাডমিনের নাম</th>
                                <th style="padding:10px 16px">ইভেন্ট / অ্যাকশন</th>
                                <th style="padding:10px 16px">বিবরণ / পরিবর্তন</th>
                                <th style="padding:10px 16px;text-align:center">আইপি অ্যাড্রেস</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($student->auditLogs as $log)
                            <tr style="border-bottom:1px solid #f1f5f9">
                                <td style="padding:10px 16px;white-space:nowrap;color:#64748b">
                                    {{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : '—' }}
                                </td>
                                <td style="padding:10px 16px;font-weight:600;color:#0f172a">
                                    {{ $log->user->name ?? 'সিস্টেম / স্বয়ংক্রিয়' }}
                                    @if($log->user?->designation)
                                    <div style="font-size:11px;color:#64748b">{{ $log->user->designation }}</div>
                                    @endif
                                </td>
                                <td style="padding:10px 16px">
                                    @php
                                        $badgeBg = match($log->event) {
                                            'course_access_toggled'  => '#fef3c7;color:#92400e',
                                            'admission_cancelled'    => '#fee2e2;color:#991b1b',
                                            'password_reset'         => '#f3e8ff;color:#6b21a8',
                                            'fee_structure_adjusted' => '#e0e7ff;color:#3730a3',
                                            'student_impersonated'   => '#ecfdf5;color:#065f46',
                                            default                  => '#eff6ff;color:#1e40af',
                                        };
                                    @endphp
                                    <span class="badge no-dot" style="background:{{ $badgeBg }};font-weight:700">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px;color:#334155">
                                    {{ $log->description ?: 'পরিবর্তন রেকর্ড করা হয়েছে' }}
                                    @if(!empty($log->new_values))
                                    <details style="margin-top:4px;font-size:11px;color:#64748b">
                                        <summary style="cursor:pointer;color:#2563eb">বিস্তারিত ডেটা দেখুন</summary>
                                        <pre style="background:#f1f5f9;padding:6px;border-radius:6px;margin-top:4px;white-space:pre-wrap">{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                    @endif
                                </td>
                                <td style="padding:10px 16px;text-align:center;font-family:monospace;color:#64748b">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding:24px;text-align:center;color:#94a3b8">
                                    কোনো অডিট রেকর্ড এখনও পাওয়া যায়নি।
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 5: LOGIN HISTORY                                               --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-logins" class="tab-panel">
            <div class="card" style="border-radius:12px;border:1px solid #e2e8f0">
                <div class="card-header" style="background:#f8fafc;padding:14px 20px">
                    <span class="card-title" style="font-size:15px;font-weight:700;color:#0f172a">
                        <i class="fa-solid fa-clock-rotate-left" style="color:#059669;margin-right:8px"></i> শিক্ষার্থীর পোর্টাল লগইন হিস্ট্রি
                    </span>
                </div>
                <div style="padding:0;overflow-x:auto">
                    <table class="table" style="width:100%;margin:0;font-size:13px">
                        <thead>
                            <tr style="background:#f8fafc;color:#64748b">
                                <th style="padding:10px 16px">লগইনের সময়</th>
                                <th style="padding:10px 16px">আইপি অ্যাড্রেস</th>
                                <th style="padding:10px 16px">ডিভাইস</th>
                                <th style="padding:10px 16px">ব্রাউজার</th>
                                <th style="padding:10px 16px">অপারেটিং সিস্টেম</th>
                                <th style="padding:10px 16px;text-align:center">লগইন ধরন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($student->loginHistories as $lh)
                            <tr style="border-bottom:1px solid #f1f5f9">
                                <td style="padding:10px 16px;white-space:nowrap;font-weight:600;color:#0f172a">
                                    {{ $lh->login_at ? $lh->login_at->format('d M Y, h:i A') : $lh->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td style="padding:10px 16px;font-family:monospace;color:#64748b">
                                    {{ $lh->ip_address ?? '—' }}
                                </td>
                                <td style="padding:10px 16px">
                                    @if($lh->device === 'Mobile')
                                        <i class="fa-solid fa-mobile-screen" style="color:#2563eb;margin-right:4px"></i> মোবাইল
                                    @elseif($lh->device === 'Tablet')
                                        <i class="fa-solid fa-tablet-screen-button" style="color:#059669;margin-right:4px"></i> ট্যাবলেট
                                    @else
                                        <i class="fa-solid fa-laptop" style="color:#4f46e5;margin-right:4px"></i> ডেক্সটপ / পিসি
                                    @endif
                                </td>
                                <td style="padding:10px 16px;color:#334155">{{ $lh->browser ?? '—' }}</td>
                                <td style="padding:10px 16px;color:#334155">{{ $lh->platform ?? '—' }}</td>
                                <td style="padding:10px 16px;text-align:center">
                                    @if($lh->is_impersonated)
                                        <span class="badge no-dot" style="background:#fef3c7;color:#92400e;font-weight:700">
                                            ইম্পার্সনেটেড (অ্যাডমিন: {{ $lh->impersonator->name ?? 'Admin' }})
                                        </span>
                                    @else
                                        <span class="badge no-dot" style="background:#ecfdf5;color:#065f46;font-weight:700">
                                            স্বাভাবিক লগইন
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="padding:24px;text-align:center;color:#94a3b8">
                                    কোনো লগইন রেকর্ড এখনও সংরক্ষণ করা হয়নি।
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- MODALS                                                             --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}

    {{-- 1. Edit Profile Modal --}}
    <div id="editProfileModal" class="admin-modal-overlay">
        <div class="admin-modal-box">
            <form method="POST" action="{{ route('admin.students.update', $student) }}">
                @csrf @method('PUT')
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">
                        <i class="fa-solid fa-user-pen" style="color:#2563eb"></i> শিক্ষার্থীর তথ্য সম্পাদনা করুন
                    </h3>
                    <button type="button" class="btn btn-outline" style="border:none;font-size:18px;cursor:pointer" onclick="closeModal('editProfileModal')">&times;</button>
                </div>
                <div class="admin-modal-body">
                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">পূর্ণ নাম <span style="color:#dc2626">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">স্ট্যাটাস <span style="color:#dc2626">*</span></label>
                            <select name="status" class="form-control" required>
                                @foreach(['ACTIVE','PENDING','LEAD','ABSENT','DROPPED','CANCELLED','TRANSFERRED','COMPLETED','GRADUATED'] as $st)
                                    <option value="{{ $st }}" {{ $student->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">মোবাইল নম্বর <span style="color:#dc2626">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}" required>
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">ইমেইল ঠিকানা</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $student->email) }}">
                        </div>
                    </div>

                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:14px">
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">লিঙ্গ</label>
                            <select name="gender" class="form-control">
                                <option value="MALE" {{ strtoupper($student->gender ?? '') === 'MALE' ? 'selected' : '' }}>পুরুষ (Male)</option>
                                <option value="FEMALE" {{ strtoupper($student->gender ?? '') === 'FEMALE' ? 'selected' : '' }}>মহিলা (Female)</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">জন্ম তারিখ</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '' }}">
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">রক্তের গ্রুপ</label>
                            <select name="blood_group" class="form-control">
                                <option value="">নির্বাচন করুন</option>
                                @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                    <option value="{{ $bg }}" {{ $student->blood_group === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">জাতীয় পরিচয়পত্র / NID</label>
                            <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $student->national_id) }}">
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">বর্তমান পেশা</label>
                            <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $student->occupation) }}">
                        </div>
                    </div>

                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">পিতার নাম</label>
                            <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}">
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">মাতার নাম</label>
                            <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}">
                        </div>
                    </div>

                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px">
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">অভিভাবকের নাম</label>
                            <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}">
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">অভিভাবকের মোবাইল</label>
                            <input type="text" name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $student->guardian_phone) }}">
                        </div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">শিক্ষাগত যোগ্যতা</label>
                        <input type="text" name="education_qualification" class="form-control" value="{{ old('education_qualification', $student->education_qualification) }}">
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">বর্তমান ঠিকানা</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $student->address) }}</textarea>
                    </div>

                    <div>
                        <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">স্থায়ী ঠিকানা</label>
                        <textarea name="permanent_address" class="form-control" rows="2">{{ old('permanent_address', $student->permanent_address) }}</textarea>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">সংরক্ষণ করুন (Save Changes)</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. Password View & Reset Modal --}}
    <div id="passwordResetModal" class="admin-modal-overlay">
        <div class="admin-modal-box" style="max-width:480px">
            <form method="POST" action="{{ route('admin.students.reset-password', $student) }}">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">
                        <i class="fa-solid fa-key" style="color:#d97706"></i> পাসওয়ার্ড রিসেট করুন
                    </h3>
                    <button type="button" class="btn btn-outline" style="border:none;font-size:18px;cursor:pointer" onclick="closeModal('passwordResetModal')">&times;</button>
                </div>
                <div class="admin-modal-body">
                    <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:16px;font-size:13px">
                        <div>স্টুডেন্ট আইডি: <strong>{{ str_replace('-', '', $student->student_code ?? '—') }}</strong></div>
                        <div>লগইন ইউজারনেম/ইমেইল: <strong>{{ $student->email ?: ($student->student_code . '@iom.student') }}</strong></div>
                    </div>

                    <div style="margin-bottom:16px">
                        <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">
                            নতুন পাসওয়ার্ড লিখুন (New Password):
                        </label>
                        <input type="text" name="new_password" class="form-control" placeholder="খালি রাখলে ফোন নম্বর পাসওয়ার্ড হবে" minlength="6">
                        <div style="font-size:12px;color:#64748b;margin-top:4px">
                            পাসওয়ার্ড ফাঁকা রেখে সংরক্ষণ করলে শিক্ষার্থীর মোবাইল নম্বর (অথবা ডিফল্ট 'iom@1234') পাসওয়ার্ড হিসেবে সেট হবে।
                        </div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('passwordResetModal')">বন্ধ করুন</button>
                    <button type="submit" class="btn btn-primary" style="background:#d97706;border-color:#b45309">
                        <i class="fa-solid fa-check"></i> পাসওয়ার্ড পরিবর্তন করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. Cancel Admission Modal --}}
    <div id="cancelAdmissionModal" class="admin-modal-overlay">
        <div class="admin-modal-box" style="max-width:500px">
            <form method="POST" action="{{ route('admin.students.cancel-admission', $student) }}">
                @csrf
                <div class="admin-modal-header" style="background:#fef2f2">
                    <h3 class="admin-modal-title" style="color:#991b1b">
                        <i class="fa-solid fa-triangle-exclamation"></i> ভর্তি বাতিল নিশ্চিতকরণ
                    </h3>
                    <button type="button" class="btn btn-outline" style="border:none;font-size:18px;cursor:pointer" onclick="closeModal('cancelAdmissionModal')">&times;</button>
                </div>
                <div class="admin-modal-body">
                    <p style="font-size:14px;color:#334155;line-height:1.6;margin-top:0">
                        আপনি কি নিশ্চিত যে শিক্ষার্থী <strong>{{ $student->name }}</strong> (আইডি: {{ $student->student_code }})-এর ভর্তি বাতিল করতে চান?
                    </p>
                    <div style="background:#fff1f2;padding:12px;border-radius:8px;border-left:4px solid #e11d48;font-size:13px;color:#9f1239;margin-bottom:16px">
                        <strong>সতর্কতা:</strong> ভর্তি বাতিল করলে শিক্ষার্থীর কোর্স অ্যাক্সেস স্বয়ংক্রিয়ভাবে বন্ধ হবে এবং সমস্ত সক্রিয় এনরোলমেন্ট বাতিল হবে।
                    </div>

                    <div>
                        <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">ভর্তি বাতিলের কারণ বা নোট:</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="বাতিলের প্রশাসনিক কারণ লিখুন..." required></textarea>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('cancelAdmissionModal')">বাতিল</button>
                    <button type="submit" class="btn btn-danger" style="background:#dc2626;border-color:#b91c1c">
                        <i class="fa-solid fa-user-xmark"></i> হ্যাঁ, ভর্তি বাতিল করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. Adjust Fee Structure / Poor Fund Modal --}}
    <div id="adjustFeeModal" class="admin-modal-overlay">
        <div class="admin-modal-box">
            <form method="POST" action="{{ route('admin.students.adjust-fee-structure', $student) }}">
                @csrf
                <div class="admin-modal-header">
                    <h3 class="admin-modal-title">
                        <i class="fa-solid fa-hand-holding-dollar" style="color:#d97706"></i> ফি কাঠামো ও পুওর ফান্ড সমন্বয়
                    </h3>
                    <button type="button" class="btn btn-outline" style="border:none;font-size:18px;cursor:pointer" onclick="closeModal('adjustFeeModal')">&times;</button>
                </div>
                <div class="admin-modal-body">
                    <div style="margin-bottom:16px">
                        <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">কোর্স ফি প্যাকেজ নির্বাচন করুন:</label>
                        <select name="fee_package_id" class="form-control">
                            <option value="">-- ডিফল্ট কোর্স ফি প্যাকেজ --</option>
                            @foreach($feePackages as $pkg)
                                <option value="{{ $pkg->id }}" {{ $student->fee_package_id == $pkg->id ? 'selected' : '' }}>
                                    {{ $pkg->name }} (মাসিক: ৳{{ number_format($pkg->monthly_fee, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">ছাড়ের ধরন (Discount Type):</label>
                            <select name="discount_type" class="form-control">
                                <option value="FIXED" {{ ($student->discount_type ?? 'FIXED') === 'FIXED' ? 'selected' : '' }}>নির্দিষ্ট টাকা (৳ Fixed)</option>
                                <option value="PERCENT" {{ ($student->discount_type ?? '') === 'PERCENT' ? 'selected' : '' }}>শতাংশ (% Percent)</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">ছাড়ের পরিমাণ (Amount / %):</label>
                            <input type="number" step="0.01" min="0" name="monthly_discount" class="form-control" value="{{ old('monthly_discount', $student->monthly_discount ?? 0) }}" placeholder="যেমন: 200 বা 50">
                        </div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px">পুওর ফান্ড রেফারেন্স / আবেদন নম্বর / কারণ:</label>
                        <textarea name="poor_fund_remarks" class="form-control" rows="2" placeholder="যেমন: পুওর ফান্ড আবেদন PF-2026-0012 অনুমোদিত">{{ old('poor_fund_remarks', $student->poor_fund_remarks) }}</textarea>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('adjustFeeModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#d97706;border-color:#b45309">
                        <i class="fa-solid fa-save"></i> সমন্বয় সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs & Modal Script --}}
    <script>
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.profile-tab-btn').forEach(b => b.classList.remove('active'));
            
            const target = document.getElementById(tabId);
            if (target) {
                target.classList.add('active');
            }
            if (btn) {
                btn.classList.add('active');
            }
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Close modal on click outside box
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('admin-modal-overlay')) {
                e.target.classList.remove('active');
            }
        });
    </script>
</x-admin-layout>
