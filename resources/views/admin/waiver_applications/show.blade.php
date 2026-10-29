<x-admin-layout>
    <x-slot name="title">Review Waiver Application — {{ $waiverApplication->full_name }}</x-slot>

    @php
        $applyFor = $waiverApplication->apply_for ?? '';
        $applyLabel = match($applyFor) {
            'ADMISSION_FEE' => 'Admission Fee Only (ভর্তি ফি কমানোর জন্য)',
            'TUITION_FEE'   => 'Tuition Fee Only (মাসিক ফি কমানোর জন্য)',
            'BOTH'          => 'Both (Admission + Tuition উভয়)',
            default         => $waiverApplication->apply_reason_type ?? '—',
        };
    @endphp

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.waiver-applications.index') }}">← Back to Waiver Applications</a>
            </div>
            <h1>Application: {{ $waiverApplication->full_name }}</h1>
            <p>App No: <strong>{{ $waiverApplication->application_no }}</strong> · Submitted {{ $waiverApplication->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="page-header-actions" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <button type="button" class="btn btn-outline btn-lg" onclick="openModal('editWaiverContactModal')" style="display:inline-flex;align-items:center;gap:6px;border-color:#0284c7;color:#0284c7;background:#f0f9ff;font-family:'Kalpurush',sans-serif;">
                <i class="fa-solid fa-user-pen"></i> ইমেইল/মোবাইল এডিট
            </button>
            @if($waiverApplication->status === 'APPROVED')
                <button type="button" class="btn btn-primary btn-lg" onclick="openModal('editApprovedWaiverModal')" style="display:inline-flex;align-items:center;gap:6px;background:#4f46e5;border-color:#4f46e5;font-family:'Kalpurush',sans-serif;">
                    <i class="fa-solid fa-pen-to-square"></i> অনুমোদিত ফি ও প্যাকেজ এডিট
                </button>
                <form method="POST" action="{{ route('admin.waiver-applications.send-update-email', $waiverApplication) }}" style="display:inline;margin:0" onsubmit="return confirm('{{ $waiverApplication->full_name }}-এর ইমেইলে ({{ $waiverApplication->email }}) সংশোধিত ফি ও কুপনের আপডেট নোটিশ মেইল পাঠাতে চান?')">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-lg" style="display:inline-flex;align-items:center;gap:6px;border-color:#0891b2;color:#0891b2;background:#ecfeff;font-family:'Kalpurush',sans-serif;">
                        <i class="fa-solid fa-envelope-circle-check"></i> আপডেট মেইল পাঠান
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.waiver-applications.resend-email', $waiverApplication) }}" style="display:inline;margin:0" onsubmit="return confirm('{{ $waiverApplication->full_name }}-এর ইমেইলে ({{ $waiverApplication->email }}) পুওর ফান্ড কনফার্মেশন ও কুপন কোড পুনরায় পাঠাতে চান?')">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-lg" style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border-color:#059669;color:#059669;font-family:'Kalpurush',sans-serif;">
                        <i class="fa-solid fa-paper-plane"></i> কনফার্মেশন মেইল (Resend)
                    </button>
                </form>
            @endif
            @if($waiverApplication->status === 'PENDING')
                <button class="btn btn-success btn-lg" onclick="openModal('approveWaiverModal')">Approve Waiver</button>
                <button class="btn btn-danger btn-lg" onclick="openModal('rejectWaiverModal')">Reject Application</button>
            @else
                <span class="badge badge-{{ strtolower($waiverApplication->status) }}" style="font-size:14px;padding:8px 16px">
                    Status: {{ ucfirst(strtolower($waiverApplication->status)) }}
                </span>
            @endif
        </div>
    </div>

    <div class="grid-2">
        <!-- Application Full Details -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Applicant Financial & Personal Details</span>
            </div>
            <div class="card-body">
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">1. Personal & Contact</div>
                <table class="table" style="font-size:13px;margin-bottom:16px">
                    <tr><th style="width:160px;color:var(--text-muted)">Full Name:</th><td><strong>{{ $waiverApplication->full_name }}</strong></td></tr>
                    <tr>
                        <th style="color:var(--text-muted)">Mobile Number:</th>
                        <td>
                            <strong>{{ $waiverApplication->phone }}</strong>
                            <button type="button" onclick="openModal('editWaiverContactModal')" class="btn btn-outline btn-sm" style="margin-left:8px;padding:1px 8px;font-size:11px;color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th style="color:var(--text-muted)">Email Address:</th>
                        <td>
                            <strong>{{ $waiverApplication->email }}</strong>
                            <button type="button" onclick="openModal('editWaiverContactModal')" class="btn btn-outline btn-sm" style="margin-left:8px;padding:1px 8px;font-size:11px;color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <tr><th style="color:var(--text-muted)">Date of Birth / Gender:</th><td>{{ $waiverApplication->date_of_birth ? $waiverApplication->date_of_birth->format('d M Y') : '—' }} ({{ $waiverApplication->gender ?? 'N/A' }})</td></tr>
                    <tr><th style="color:var(--text-muted)">Father's Name:</th><td>{{ $waiverApplication->father_name }}</td></tr>
                    <tr><th style="color:var(--text-muted)">National ID / Birth Reg:</th><td>{{ $waiverApplication->national_id }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Division / Country:</th><td>{{ $waiverApplication->is_abroad ? ('Abroad ('.$waiverApplication->country_name.')') : ($waiverApplication->division->name ?? '—') }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Present Address:</th><td>{{ $waiverApplication->present_address }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Permanent Address:</th><td>{{ $waiverApplication->same_as_present ? 'Same as present address' : $waiverApplication->permanent_address }}</td></tr>
                </table>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">2. Profession & Financial Status</div>
                <table class="table" style="font-size:13px;margin-bottom:16px">
                    <tr><th style="width:160px;color:var(--text-muted)">Occupation:</th><td>{{ $waiverApplication->occupation ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Institution / Business:</th><td>{{ $waiverApplication->institution_or_business }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Current IOM Student?</th><td>{{ $waiverApplication->is_present_iom_student ? ('Yes (Roll: '.$waiverApplication->student_roll.')') : 'No' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Source of Income:</th><td>{{ $waiverApplication->source_of_income }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Monthly Income:</th><td><strong>৳ {{ number_format($waiverApplication->monthly_income, 0) }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Guardian Mobile:</th><td>{{ $waiverApplication->guardian_phone ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Marital Status:</th><td>{{ $waiverApplication->is_married ? 'Married' : 'Unmarried' }}</td></tr>
                </table>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">3. Family & Financial Problem Statement</div>
                <div style="font-size:13px;margin-bottom:16px">
                    <strong>Siblings / Children Details:</strong>
                    <div style="background:#f8fafc;padding:10px 14px;border-radius:6px;margin:6px 0 12px;border:1px solid #e2e8f0;white-space:pre-line">{{ $waiverApplication->family_siblings_details }}</div>

                    <strong>Financial Hardship Description:</strong>
                    <div style="background:#fefce8;padding:12px 14px;border-radius:6px;margin-top:6px;border:1px solid #fde047;color:#713f12;white-space:pre-line">{{ $waiverApplication->financial_problem_description }}</div>
                </div>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">4. Waiver Request Details</div>
                <table class="table" style="font-size:13px">
                    <tr><th style="width:160px;color:var(--text-muted)">Applying For:</th><td><span class="badge badge-secondary no-dot">{{ $applyLabel }}</span></td></tr>
                    @if(in_array($applyFor, ['ADMISSION_FEE', 'BOTH', '']))
                        <tr><th style="color:var(--text-muted)">Convenient Admission Fee:</th><td>৳ {{ number_format($waiverApplication->convenient_admission_fee, 0) }}</td></tr>
                    @endif
                    @if(in_array($applyFor, ['TUITION_FEE', 'BOTH', '']))
                        <tr><th style="color:var(--text-muted)">Convenient Monthly Fee:</th><td>৳ {{ number_format($waiverApplication->convenient_monthly_fee, 0) }}</td></tr>
                    @endif
                    @if($waiverApplication->course)
                        <tr><th style="color:var(--text-muted)">Course Applied For:</th><td><strong>{{ $waiverApplication->course->name }}</strong></td></tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Committee Audit & Decision -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Committee Audit & Decision</span>
            </div>
            <div class="card-body">
                @if($waiverApplication->status === 'APPROVED')
                    <div class="alert alert-success">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                            <strong style="font-size:16px">Waiver Approved</strong>
                            <button type="button" class="btn btn-sm btn-outline" onclick="openModal('editApprovedWaiverModal')" style="font-family:'Kalpurush',sans-serif;border-color:#059669;color:#059669;background:#ecfdf5;font-size:12px;padding:3px 10px;cursor:pointer">
                                <i class="fa-solid fa-pen-to-square"></i> ফি বা প্যাকেজ পরিবর্তন করুন
                            </button>
                        </div>
                        <div style="margin-top:8px;font-size:13px">

                            @if(in_array($applyFor, ['ADMISSION_FEE', 'BOTH']) && $waiverApplication->approved_admission_fee !== null)
                                <div style="margin-bottom:6px">
                                    <strong>Admission Fee Set:</strong>
                                    <span class="badge badge-active no-dot" style="margin-left:6px">৳ {{ number_format($waiverApplication->approved_admission_fee, 0) }}</span>
                                </div>
                            @endif

                            @if(in_array($applyFor, ['TUITION_FEE', 'BOTH']) && $waiverApplication->approvedPackage)
                                <div style="margin-bottom:6px">
                                    <strong>Approved Fee Package:</strong>
                                    <span class="badge badge-scheduled no-dot" style="margin-left:6px">{{ $waiverApplication->approvedPackage->name }}</span>
                                    <span style="color:var(--text-muted);font-size:12px"> — Total: ৳{{ number_format($waiverApplication->approvedPackage->total, 0) }}</span>
                                </div>
                            @endif

                        </div>
                        @if($waiverApplication->reviewer_notes)
                            <div style="margin-top:8px;font-size:12px"><em>Notes: {{ $waiverApplication->reviewer_notes }}</em></div>
                        @endif
                        <div style="margin-top:8px;font-size:12px;color:var(--text-muted)">
                            Reviewed by {{ $waiverApplication->reviewer->name ?? 'Admin' }} on {{ $waiverApplication->reviewed_at ? $waiverApplication->reviewed_at->format('d M Y, h:i A') : '—' }}.
                        </div>
                    </div>
                @elseif($waiverApplication->status === 'REJECTED')
                    <div class="alert alert-danger">
                        <strong style="font-size:16px">Application Rejected</strong><br>
                        <div style="margin-top:6px">Reason: {{ $waiverApplication->reviewer_notes ?? 'Not specified' }}</div>
                        <div style="margin-top:8px;font-size:12px">
                            Reviewed by {{ $waiverApplication->reviewer->name ?? 'Admin' }} on {{ $waiverApplication->reviewed_at ? $waiverApplication->reviewed_at->format('d M Y, h:i A') : '—' }}.
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <strong>⏳ Pending Review</strong><br>
                        Verify the applicant's financial hardship details and convenient fee amounts, then click <strong>Approve Waiver</strong> to proceed.
                    </div>

                    {{-- Waiver Type Summary Box --}}
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:14px 16px;margin-top:12px;font-size:13px">
                        <div style="font-weight:700;color:#0369a1;margin-bottom:8px">Approval Required:</div>
                        @if(in_array($applyFor, ['ADMISSION_FEE', 'BOTH']))
                            <div style="margin-bottom:4px">→ Set the <strong>Admission Fee</strong> student will pay (applicant suggests: ৳{{ number_format($waiverApplication->convenient_admission_fee, 0) }})</div>
                        @endif
                        @if(in_array($applyFor, ['TUITION_FEE', 'BOTH']))
                            <div>→ Select an approved <strong>Fee Package</strong> for monthly tuition (applicant suggests: ৳{{ number_format($waiverApplication->convenient_monthly_fee, 0) }}/month)</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ APPROVE MODAL (Dynamic per type) ═══ --}}
    <div class="modal-overlay" id="approveWaiverModal">
        <div class="modal" style="max-width:520px">
            <div class="modal-header">
                <span class="modal-title">Approve Waiver — {{ $applyLabel }}</span>
                <button class="modal-close" onclick="closeModal('approveWaiverModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.waiver-applications.approve', $waiverApplication) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই পুওর ফান্ড ওয়েভার আবেদনটি অনুমোদন (Approve) করতে চান? (Are you sure you want to approve this waiver application?)')">
                @csrf @method('PATCH')
                <div class="modal-body">

                    {{-- ── ADMISSION FEE section ── --}}
                    @if(in_array($applyFor, ['ADMISSION_FEE', 'BOTH']))
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 16px;margin-bottom:16px">
                            <div style="font-weight:700;color:#166534;margin-bottom:10px;font-size:13px">Admission Fee Setting</div>
                            <div class="form-group" style="margin-bottom:0">
                                <label>Student ভর্তি ফি দেবে (৳ Taka) <span class="required">*</span></label>
                                <input type="number" name="approved_admission_fee" class="form-control"
                                    value="{{ old('approved_admission_fee', $waiverApplication->convenient_admission_fee ?? 0) }}"
                                    min="0" step="1" placeholder="e.g. 2000" required>
                                <small style="color:var(--text-muted);font-size:12px">
                                    Applicant requested: ৳{{ number_format($waiverApplication->convenient_admission_fee, 0) }}
                                </small>
                            </div>
                        </div>
                    @endif

                    {{-- ── TUITION FEE / PACKAGE section ── --}}
                    @if(in_array($applyFor, ['TUITION_FEE', 'BOTH']))
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;margin-bottom:16px">
                            <div style="font-weight:700;color:#92400e;margin-bottom:10px;font-size:13px">Fee Package Selection (Tuition)</div>

                            @if($coursePackages->isEmpty())
                                <div style="color:#dc2626;font-size:13px;background:#fef2f2;padding:10px;border-radius:6px;border:1px solid #fecaca">
                                    এই course এ কোনো Fee Package তৈরি করা হয়নি।
                                    <a href="{{ route('admin.courses.show', $waiverApplication->course_id) }}" style="color:#dc2626;font-weight:600" target="_blank">Course এ গিয়ে Package যোগ করুন →</a>
                                </div>
                            @else
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Select Fee Package <span class="required">*</span></label>
                                    <select name="approved_package_id" class="form-control" required onchange="showPackageDetail(this)">
                                        <option value="">-- Select Package --</option>
                                        @foreach($coursePackages as $pkg)
                                            <option value="{{ $pkg->id }}"
                                                data-total="{{ $pkg->total }}"
                                                data-items="{{ $pkg->items->map(fn($i) => ($i->label ?: $i->feeHead?->name).': ৳'.number_format($i->total_amount,0))->join(' | ') }}"
                                                {{ $pkg->is_default ? 'selected' : '' }}>
                                                {{ $pkg->course?->name ? ($pkg->course->name.' — ') : '' }}{{ $pkg->name }}
                                                @if($pkg->is_default) Default @endif
                                                — ৳{{ number_format($pkg->total, 0) }} total
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="pkgDetailBox" style="display:none;margin-top:8px;font-size:12px;color:var(--text-muted);background:#f8fafc;padding:8px 12px;border-radius:6px;border:1px solid #e2e8f0"></div>
                                    <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block">
                                        Applicant requested ≈ ৳{{ number_format($waiverApplication->convenient_monthly_fee, 0) }}/month
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ── Notes ── --}}
                    <div class="form-group" style="margin-bottom:0">
                        <label>Committee Review Notes</label>
                        <textarea name="reviewer_notes" class="form-control" rows="3" placeholder="Approval notes, zakat fund approval, or special conditions...">{{ old('reviewer_notes') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveWaiverModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm & Approve Waiver</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ REJECT MODAL ═══ --}}
    <div class="modal-overlay" id="rejectWaiverModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Reject Waiver Application</span>
                <button class="modal-close" onclick="closeModal('rejectWaiverModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.waiver-applications.reject', $waiverApplication) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই পুওর ফান্ড ওয়েভার আবেদনটি বাতিল (Reject) করতে চান?')">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason / Notes <span class="required">*</span></label>
                        <textarea name="reviewer_notes" class="form-control" rows="3" placeholder="e.g. Monthly income exceeds eligibility threshold, incomplete information..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('rejectWaiverModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ EDIT CONTACT MODAL ═══ --}}
    <div class="modal-overlay" id="editWaiverContactModal">
        <div class="modal" style="max-width:480px;font-family:'Kalpurush',sans-serif;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff">
                <span class="modal-title" style="color:#fff;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-user-pen"></i> ইমেইল ও মোবাইল নম্বর সংশোধন
                </span>
                <button class="modal-close" onclick="closeModal('editWaiverContactModal')" style="color:#fff">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.waiver-applications.update-contact', $waiverApplication) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;padding:22px">
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:13px;font-weight:600;color:#334155;margin-bottom:6px;display:block">
                            <i class="fa-solid fa-envelope" style="color:#0284c7;margin-right:4px"></i> ইমেইল অ্যাড্রেস <span class="required" style="color:#ef4444">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $waiverApplication->email) }}" class="form-control" required style="width:100%;padding:10px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px">
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:13px;font-weight:600;color:#334155;margin-bottom:6px;display:block">
                            <i class="fa-solid fa-phone" style="color:#0284c7;margin-right:4px"></i> মোবাইল নম্বর <span class="required" style="color:#ef4444">*</span>
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $waiverApplication->phone) }}" class="form-control" required style="width:100%;padding:10px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px">
                    </div>
                    <div style="font-size:12px;color:#64748b;background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0">
                        💡 <em>ইমেইল বা ফোন নম্বর সেভ করার পর আপনি সরাসরি কনফার্মেশন মেইল পুনরায় পাঠাতে পারবেন।</em>
                    </div>
                </div>
                <div class="modal-footer" style="padding:14px 22px;background:#f8fafc">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editWaiverContactModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#0284c7;border-color:#0284c7;font-weight:600">
                        <i class="fa-solid fa-check"></i> সংরক্ষণ করুন (Save)
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ EDIT APPROVED WAIVER MODAL ═══ --}}
    <div class="modal-overlay" id="editApprovedWaiverModal">
        <div class="modal" style="max-width:560px;font-family:'Kalpurush',sans-serif;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4f46e5,#4338ca);color:#fff">
                <span class="modal-title" style="color:#fff;display:flex;align-items:center;gap:8px;font-size:16px">
                    <i class="fa-solid fa-pen-to-square"></i> অনুমোদিত ফি ও প্যাকেজ এডিট (Edit Approved Waiver)
                </span>
                <button class="modal-close" onclick="closeModal('editApprovedWaiverModal')" style="color:#fff">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.waiver-applications.update-approval', $waiverApplication) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body" style="display:flex;flex-direction:column;gap:16px;padding:22px">
                    {{-- 1. Waiver Type Selection --}}
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;display:block">
                            ছাড়ের আওতা / Waiver Type <span class="required" style="color:#ef4444">*</span>
                        </label>
                        <select name="apply_for" id="edit_apply_for" class="form-control" required onchange="toggleEditWaiverSections(this.value)" style="width:100%;padding:9px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px">
                            <option value="BOTH" {{ old('apply_for', $waiverApplication->apply_for) === 'BOTH' ? 'selected' : '' }}>ভর্তি ও মাসিক ফি উভয়ই (Both Admission & Tuition)</option>
                            <option value="ADMISSION_FEE" {{ old('apply_for', $waiverApplication->apply_for) === 'ADMISSION_FEE' ? 'selected' : '' }}>শুধুমাত্র ভর্তি ফি (Admission Fee Only)</option>
                            <option value="TUITION_FEE" {{ old('apply_for', $waiverApplication->apply_for) === 'TUITION_FEE' ? 'selected' : '' }}>শুধুমাত্র মাসিক ফি / প্যাকেজ (Tuition Fee / Package Only)</option>
                        </select>
                    </div>

                    {{-- 2. Admission Fee Section --}}
                    <div id="edit_admission_fee_section" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 16px;">
                        <div style="font-weight:700;color:#166534;margin-bottom:8px;font-size:13.5px;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-money-bill-wave"></i> অনুমোদিত ভর্তি ফি (Admission Fee) <span class="required" style="color:#ef4444">*</span>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <input type="number" name="approved_admission_fee" id="edit_approved_admission_fee" class="form-control"
                                value="{{ old('approved_admission_fee', $waiverApplication->approved_admission_fee ?? $waiverApplication->convenient_admission_fee ?? 0) }}"
                                min="0" step="1" placeholder="e.g. 700" style="width:100%;padding:10px 12px;border:1.5px solid #86efac;border-radius:8px;font-size:15px;font-weight:700;color:#166534">
                            <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:12px;color:#64748b">
                                <span>আবেদনকারী চেয়েছেন: <strong>৳{{ number_format($waiverApplication->convenient_admission_fee ?? 0, 0) }}</strong></span>
                                <span>কোর্স রেগুলার ফি: <strong>৳{{ number_format($waiverApplication->course?->admission_fee ?? 1500, 0) }}</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Fee Package / Structure Section --}}
                    <div id="edit_package_section" style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;">
                        <div style="font-weight:700;color:#92400e;margin-bottom:8px;font-size:13.5px;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-box-open"></i> অনুমোদিত ফি প্যাকেজ / স্ট্রাকচার (Fee Package) <span class="required" style="color:#ef4444">*</span>
                        </div>
                        @if($coursePackages->isEmpty())
                            <div style="color:#dc2626;font-size:12.5px;background:#fef2f2;padding:10px;border-radius:6px;border:1px solid #fecaca">
                                এই কোর্সে কোনো Fee Package পাওয়া যায়নি।
                            </div>
                        @else
                            <div class="form-group" style="margin-bottom:0">
                                <select name="approved_package_id" id="edit_approved_package_id" class="form-control" onchange="showEditPackageDetail(this)" style="width:100%;padding:9px 12px;border:1.5px solid #fcd34d;border-radius:8px;font-size:14px">
                                    <option value="">-- প্যাকেজ নির্বাচন করুন --</option>
                                    @foreach($coursePackages as $pkg)
                                        <option value="{{ $pkg->id }}"
                                            data-total="{{ $pkg->total }}"
                                            data-items="{{ $pkg->items->map(fn($i) => ($i->label ?: $i->feeHead?->name).': ৳'.number_format($i->total_amount,0))->join(' | ') }}"
                                            {{ (string)old('approved_package_id', $waiverApplication->approved_package_id) === (string)$pkg->id ? 'selected' : '' }}>
                                            {{ $pkg->course?->name ? ($pkg->course->name.' — ') : '' }}{{ $pkg->name }}
                                            — ৳{{ number_format($pkg->total, 0) }} total
                                        </option>
                                    @endforeach
                                </select>
                                <div id="editPkgDetailBox" style="display:none;margin-top:8px;font-size:12px;color:var(--text-muted);background:#f8fafc;padding:8px 12px;border-radius:6px;border:1px solid #e2e8f0"></div>
                                <div style="margin-top:6px;font-size:12px;color:#64748b">
                                    আবেদনকারীর প্রস্তাবিত মাসিক ফি: <strong>৳{{ number_format($waiverApplication->convenient_monthly_fee ?? 0, 0) }}/মাস</strong>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 4. Reviewer Notes --}}
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:13px;font-weight:600;color:#334155;margin-bottom:6px;display:block">
                            কমিটি নোট / মন্তব্য (Reviewer Notes)
                        </label>
                        <textarea name="reviewer_notes" class="form-control" rows="2" placeholder="ফি বা প্যাকেজ পরিবর্তনের কারণ / মন্তব্য..." style="width:100%;padding:10px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13.5px">{{ old('reviewer_notes', $waiverApplication->reviewer_notes) }}</textarea>
                    </div>

                    {{-- 5. Send Email Checkbox --}}
                    <label style="display:flex;align-items:center;gap:10px;font-size:13.5px;cursor:pointer;margin-top:2px;padding:12px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
                        <input type="checkbox" name="send_email" value="1" checked style="width:18px;height:18px;accent-color:#16a34a;cursor:pointer">
                        <span style="color:#166534">
                            <strong>আবেদনকারীকে সংশোধিত ফির আপডেট ইমেইল পাঠান</strong><br>
                            <small style="color:#475569">ইমেইল: {{ $waiverApplication->email }} (সংশোধিত ভর্তি ফি ও প্যাকেজের বিবরণসহ আপডেট নোটিশ যাবে)</small>
                        </span>
                    </label>
                </div>
                <div class="modal-footer" style="padding:14px 22px;background:#f8fafc;display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editApprovedWaiverModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5;font-weight:600">
                        <i class="fa-solid fa-check"></i> পরিবর্তন সংরক্ষণ করুন (Save & Update)
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function showPackageDetail(sel) {
        if (!sel) return;
        const opt = sel.options[sel.selectedIndex];
        const box = document.getElementById('pkgDetailBox');
        if (!box) return;
        const items = opt ? opt.dataset.items : null;
        const total = opt ? opt.dataset.total : null;
        if (sel.value && items) {
            box.style.display = 'block';
            box.innerHTML = '<strong>Fee Items:</strong> ' + items + '<br><strong>Total Package Price:</strong> <span style="color:#047857;font-weight:700">৳' + Number(total || 0).toLocaleString() + '</span>';
        } else {
            box.style.display = 'none';
        }
    }

    function showEditPackageDetail(sel) {
        if (!sel) return;
        const opt = sel.options[sel.selectedIndex];
        const box = document.getElementById('editPkgDetailBox');
        if (!box) return;
        const items = opt ? opt.dataset.items : null;
        const total = opt ? opt.dataset.total : null;
        if (sel.value && items) {
            box.style.display = 'block';
            box.innerHTML = '<strong>Fee Items:</strong> ' + items + '<br><strong>Total Package Price:</strong> <span style="color:#047857;font-weight:700">৳' + Number(total || 0).toLocaleString() + '</span>';
        } else {
            box.style.display = 'none';
        }
    }

    function toggleEditWaiverSections(val) {
        const admSec = document.getElementById('edit_admission_fee_section');
        const pkgSec = document.getElementById('edit_package_section');
        const admInput = document.getElementById('edit_approved_admission_fee');
        const pkgSelect = document.getElementById('edit_approved_package_id');

        if (val === 'ADMISSION_FEE') {
            if (admSec) admSec.style.display = 'block';
            if (admInput) admInput.required = true;
            if (pkgSec) pkgSec.style.display = 'none';
            if (pkgSelect) pkgSelect.required = false;
        } else if (val === 'TUITION_FEE') {
            if (admSec) admSec.style.display = 'none';
            if (admInput) admInput.required = false;
            if (pkgSec) pkgSec.style.display = 'block';
            if (pkgSelect) pkgSelect.required = true;
        } else { // BOTH
            if (admSec) admSec.style.display = 'block';
            if (admInput) admInput.required = true;
            if (pkgSec) pkgSec.style.display = 'block';
            if (pkgSelect) pkgSelect.required = true;
        }
    }

    // Trigger on load for default selected
    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.querySelector('#approveWaiverModal select[name="approved_package_id"]');
        if (sel) showPackageDetail(sel);

        const editSel = document.getElementById('edit_approved_package_id');
        if (editSel) showEditPackageDetail(editSel);

        const editApplyFor = document.getElementById('edit_apply_for');
        if (editApplyFor) toggleEditWaiverSections(editApplyFor.value);
    });
    </script>
    @endpush

</x-admin-layout>
