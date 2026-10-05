<x-admin-layout>
    <x-slot name="title">Review Admission — {{ $admission->student->name ?? 'Application' }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.admissions.index') }}">← Back to Admissions</a>
            </div>
            <h1>Application: {{ $admission->student->name ?? '—' }}</h1>
            <p>Attempt #{{ $admission->attempt_no }} · Submitted {{ $admission->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="page-header-actions">
            @if($admission->status === 'PENDING')
                <button class="btn btn-success btn-lg" onclick="openModal('approveModal')">Approve & Activate Student</button>
                <button class="btn btn-danger btn-lg" onclick="openModal('rejectModal')">Reject Application</button>
            @else
                <span class="badge badge-{{ strtolower($admission->status) }}" style="font-size:14px;padding:8px 16px">
                    Status: {{ ucfirst(strtolower($admission->status)) }}
                </span>
            @endif
        </div>
    </div>

    <div class="grid-2">
        <!-- Application Details -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Applicant & Application Details</span>
                <div>
                    <span class="badge badge-{{ $admission->source === 'PUBLIC' ? 'scheduled' : 'active' }} no-dot">Source: {{ $admission->source }}</span>
                    @if($admission->student && $admission->student->student_code)
                        <a href="{{ route('admin.students.impersonate', $admission->student) }}" 
                           class="badge badge-active no-dot" 
                           style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;background:#ecfdf5;border:1px solid #10b981;color:#047857;text-decoration:none;padding:5px 12px;border-radius:20px;font-weight:700;transition:all 0.2s;"
                           title="শিক্ষার্থী হিসেবে সরাসরি লগইন করুন (Click to login as this student)"
                           onmouseover="this.style.background='#047857';this.style.color='#fff';"
                           onmouseout="this.style.background='#ecfdf5';this.style.color='#047857';">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>Student ID: <strong>{{ $admission->student->student_code }}</strong></span>
                            <span style="font-size:11px;background:rgba(4,120,87,0.15);padding:1px 6px;border-radius:10px;">লগইন ↗</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">Basic Information</div>
                <table class="table" style="font-size:13px;margin-bottom:16px">
                    <tr><th style="width:140px;color:var(--text-muted)">Application No:</th><td><strong>{{ $admission->application_no ?? 'APP-'.$admission->id }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Full Name:</th><td><strong>{{ $admission->applicant_name ?? $admission->student->name }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Phone:</th><td>{{ $admission->phone ?? $admission->student->phone }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Email:</th><td>{{ $admission->email ?? $admission->student->email ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Date of Birth:</th><td>{{ $admission->date_of_birth ?? $admission->student->date_of_birth ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Gender / Device:</th><td>{{ $admission->gender ?? $admission->student->gender ?? '—' }} ({{ $admission->device_type ?? 'N/A' }})</td></tr>
                    <tr><th style="color:var(--text-muted)">Course Interested:</th><td><strong>{{ $admission->interestedCourse->name ?? '—' }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Course Admission Fee:</th><td><strong style="color:#047857;font-size:14px">৳ {{ number_format($admission->interestedCourse->admission_fee ?? 0, 0) }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Academic Session:</th><td>{{ $admission->session->name ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Lead Source / Waiver:</th><td>{{ $admission->lead_source ?? 'Direct' }} (Waiver: {{ $admission->discount_percent ?? 0 }}%)</td></tr>
                </table>

                @if($admission->waiver_code)
                @php $waiverApp = \App\Models\WaiverApplication::where('application_no', $admission->waiver_code)->first(); @endphp
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:14px 16px;border-radius:10px;margin-bottom:16px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <strong style="color:#166534;font-size:14px">Applied Poor Fund Waiver Code: {{ $admission->waiver_code }}</strong>
                        <span class="badge badge-active no-dot">
                            {{ $admission->discount_type === 'FIXED' ? ('৳'.$admission->discount_amount.' Fixed Discount') : (($admission->discount_percent ?? 0).'% Approved Discount') }}
                        </span>
                    </div>
                    @if($waiverApp)
                        <div style="font-size:12px;color:#15803d;margin-bottom:8px">
                            Applied by {{ $waiverApp->full_name }} · Convenient Adm Fee: ৳{{ $waiverApp->convenient_admission_fee }} · Monthly: ৳{{ $waiverApp->convenient_monthly_fee }}
                        </div>
                        <a href="{{ route('admin.waiver-applications.show', $waiverApp) }}" target="_blank" class="btn btn-outline btn-sm" style="font-size:12px">
                            View Full Poor Fund Application Details ↗
                        </a>
                    @endif
                </div>
                @endif

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">Accounts & Fee Summary</div>
                @php 
                    $courseFee = (float)($admission->interestedCourse->admission_fee ?? 0);
                    $batchFee = ($admission->batch && $admission->batch->admission_fee !== null) ? (float)$admission->batch->admission_fee : $courseFee;
                    $waiverApp = $admission->waiver_code ? \App\Models\WaiverApplication::where('application_no', $admission->waiver_code)->first() : null;
                    if ($waiverApp && $waiverApp->approved_admission_fee !== null && in_array($waiverApp->apply_for, ['ADMISSION_FEE', 'BOTH'])) {
                        $netPayable = min($batchFee, (float) $waiverApp->approved_admission_fee);
                        $discVal = max(0, $batchFee - $netPayable);
                    } else {
                        $discVal = ($admission->discount_type === 'FIXED') 
                            ? (float)($admission->discount_amount > 0 ? $admission->discount_amount : $admission->discount_percent) 
                            : round($batchFee * (((float)($admission->discount_percent ?? 0)) / 100), 2);
                        $netPayable = max(0, $batchFee - $discVal);
                    }
                    $invoice = \App\Models\Invoice::where('source_type', \App\Models\AdmissionForm::class)->where('source_id', $admission->id)->first();
                @endphp
                <table class="table" style="font-size:13px;margin-bottom:16px">
                    <tr><th style="width:160px;color:var(--text-muted)">Course/Batch Admission Fee:</th><td>৳ {{ number_format($batchFee, 0) }}</td></tr>
                    <tr>
                        <th style="color:var(--text-muted)">Applied Waiver / Discount:</th>
                        <td>
                            -৳ {{ number_format($discVal, 0) }} 
                            @if($waiverApp && $waiverApp->approved_admission_fee !== null)
                                <span class="badge badge-active no-dot" style="font-size:11px">Approved Waiver Fee</span>
                            @else
                                <span class="badge badge-secondary no-dot" style="font-size:11px">
                                    ({{ $admission->discount_type === 'FIXED' ? 'Fixed Taka Waiver' : ($admission->discount_percent.'% Percentage Waiver') }})
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr><th style="color:var(--text-muted)">Net Payable Fee:</th><td><strong style="color:var(--blue);font-size:15px">৳ {{ number_format($netPayable, 0) }}</strong></td></tr>
                    @if($waiverApp && $waiverApp->approvedPackage)
                    <tr>
                        <th style="color:var(--text-muted)">Approved Tuition Package:</th>
                        <td>
                            <strong>{{ $waiverApp->approvedPackage->name }}</strong>
                            <span class="badge badge-active no-dot" style="margin-left:6px">৳ {{ number_format($waiverApp->approvedPackage->total, 0) }}</span>
                            @if($waiverApp->convenient_monthly_fee)
                                <div style="font-size:11px;color:#166534;margin-top:2px">Applicant requested Monthly Fee: ৳{{ number_format($waiverApp->convenient_monthly_fee, 0) }}</div>
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th style="color:var(--text-muted)">Accounts Due Status:</th>
                        <td>
                            @if($invoice)
                                @if($invoice->status === 'PAID')
                                    <span class="badge badge-active">PAID (Invoice: {{ $invoice->invoice_no }})</span>
                                @elseif($invoice->status === 'PARTIAL')
                                    <span class="badge badge-pending">PARTIAL (Paid: ৳{{ number_format($invoice->paid_amount,0) }}, Due: ৳{{ number_format($invoice->due_amount,0) }})</span>
                                @else
                                    <span class="badge badge-danger">UNPAID DUE (Due Amount: ৳{{ number_format($invoice->due_amount,0) }})</span>
                                @endif
                            @else
                                <span class="badge badge-pending">⏳ Invoice generated upon Approval</span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if($admission->manual_trx_id)
                <div style="background:#ecfdf5;border:1.5px solid #10b981;border-radius:10px;padding:14px;margin-bottom:16px;font-family:'Kalpurush',sans-serif">
                    <div style="font-weight:700;color:#065f46;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between">
                        <span><i class="fa-solid fa-money-bill-transfer"></i> মার্চেন্ট নাম্বারে পূর্বেই পরিশোধিত পেমেন্ট তথ্য (Manual Merchant Payment)</span>
                        <span class="badge" style="background:#047857;color:#fff">যাচাই আবশ্যক</span>
                    </div>
                    <table class="table" style="font-size:13px;margin:0;background:transparent">
                        <tr><th style="width:170px;color:#065f46">পেমেন্ট মাধ্যম:</th><td><strong>{{ $admission->manual_payment_method }}</strong></td></tr>
                        <tr><th style="color:#065f46">ট্রাঞ্জেকশন আইডি (TrxID):</th><td><strong style="font-family:monospace;font-size:15px;color:#047857;letter-spacing:1px">{{ $admission->manual_trx_id }}</strong></td></tr>
                        @if($admission->manual_sender_phone)
                        <tr><th style="color:#065f46">প্রেরকের মোবাইল নম্বর:</th><td><strong>{{ $admission->manual_sender_phone }}</strong></td></tr>
                        @endif
                        @if($admission->manual_payment_date)
                        <tr><th style="color:#065f46">পেমেন্টের সময়:</th><td>{{ \Carbon\Carbon::parse($admission->manual_payment_date)->format('d M Y, h:i A') }}</td></tr>
                        @endif
                        @if($admission->manual_payment_notes)
                        <tr><th style="color:#065f46">মন্তব্য / বিবরণ:</th><td>{{ $admission->manual_payment_notes }}</td></tr>
                        @endif
                    </table>
                </div>
                @endif

                @php
                    $gwTrx = \App\Models\GatewayTransaction::where('admission_form_id', $admission->id)->latest()->first();
                @endphp
                @if($gwTrx)
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px;margin-bottom:16px">
                    <div style="font-weight:700;color:#1e40af;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between">
                        <span><i class="fa-solid fa-credit-card"></i> অনলাইন পেমেন্ট গেটওয়ে অডিট (Online Payment Audit)</span>
                        <span class="badge {{ $gwTrx->status === 'SUCCESS' ? 'badge-active' : 'badge-pending' }}">{{ $gwTrx->status }}</span>
                    </div>
                    <table class="table" style="font-size:12.5px;margin:0">
                        <tr><th style="width:140px">পেমেন্ট মাধ্যম:</th><td>{{ strtoupper($gwTrx->gateway) }} ({{ strtoupper($gwTrx->gateway_mode) }})</td></tr>
                        <tr><th>ট্রানজেকশন আইডি:</th><td><code>{{ $gwTrx->tran_id }}</code></td></tr>
                        @if($gwTrx->gateway_trx_id)
                        <tr><th>গেটওয়ে TrxID / Val ID:</th><td><code>{{ $gwTrx->gateway_trx_id }}</code> {{ $gwTrx->val_id ? " (Val: {$gwTrx->val_id})" : "" }}</td></tr>
                        @endif
                        @if($gwTrx->card_type || $gwTrx->card_brand)
                        <tr><th>কার্ড / চ্যানেল:</th><td>{{ $gwTrx->card_type }} {{ $gwTrx->card_brand ? "({$gwTrx->card_brand})" : "" }}</td></tr>
                        @endif
                        <tr><th>টাকার পরিমাণ:</th><td><strong style="color:#047857">৳ {{ number_format($gwTrx->amount, 2) }} {{ $gwTrx->currency }}</strong></td></tr>
                        @if($gwTrx->verified_at)
                        <tr><th>সার্ভার যাচাই সময়:</th><td>{{ $gwTrx->verified_at->format('d M Y, h:i A') }}</td></tr>
                        @endif
                    </table>
                </div>
                @endif

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">Personal & Identification</div>
                <table class="table" style="font-size:13px;margin-bottom:16px">
                    <tr><th style="width:140px;color:var(--text-muted)">Blood Group:</th><td>{{ $admission->bloodGroup->name ?? $admission->student->blood_group ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Nationality (জাতীয়তা):</th><td>{{ $admission->nationality ?? 'Bangladeshi' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">National ID (NID):</th><td>{{ $admission->national_id ?? $admission->student->national_id ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Passport / Birth Cert:</th><td>{{ $admission->passport_no ?? '—' }} / {{ $admission->birth_certificate_no ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Guardian Info:</th><td>{{ $admission->guardian_name ?? $admission->student->guardian_name ?? '—' }} ({{ $admission->guardian_phone ?? $admission->student->guardian_phone ?? '—' }})</td></tr>
                </table>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">Education Records</div>
                <table class="table" style="font-size:13px;margin-bottom:16px">
                    <tr><th style="width:140px;color:var(--text-muted)">Occupation / Qualification:</th><td>{{ $admission->occupation ?? '—' }} / {{ $admission->education_qualification ?? '—' }}</td></tr>
                    <tr><th style="color:var(--text-muted)">SSC Record:</th><td>{{ $admission->ssc_school ?? '—' }} (Board: {{ $admission->ssc_board ?? '—' }}, Year: {{ $admission->ssc_year ?? '—' }}, GPA: {{ $admission->student->ssc_gpa ?? '—' }})</td></tr>
                    <tr><th style="color:var(--text-muted)">HSC Record:</th><td>{{ $admission->hsc_college ?? '—' }} (Board: {{ $admission->hsc_board ?? '—' }}, Year: {{ $admission->hsc_year ?? '—' }}, GPA: {{ $admission->student->hsc_gpa ?? '—' }})</td></tr>
                    <tr><th style="color:var(--text-muted)">Higher Education:</th><td>{{ $admission->university_name ?? '—' }} ({{ $admission->department_name ?? '—' }})</td></tr>
                </table>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);border-bottom:1px solid #dbeafe;padding-bottom:4px;margin-bottom:10px">Addresses</div>
                <table class="table" style="font-size:13px">
                    <tr>
                        <th style="width:140px;color:var(--text-muted)">Present Address:</th>
                        <td>
                            {{ $admission->present_house ?? '—' }}
                            @if($admission->present_post_office), PO: {{ $admission->present_post_office }}@endif
                            @if($admission->present_police_station), Thana: {{ $admission->present_police_station }}@endif
                            @if($admission->presentDistrict), District: {{ $admission->presentDistrict->name }}@endif
                            @if($admission->presentDivision), Division: {{ $admission->presentDivision->name }}@endif
                        </td>
                    </tr>
                    <tr>
                        <th style="color:var(--text-muted)">Permanent Address:</th>
                        <td>
                            @if($admission->same_as_present)
                                <em>Same as present address</em>
                            @else
                                {{ $admission->permanent_house ?? '—' }}
                                @if($admission->permanent_post_office), PO: {{ $admission->permanent_post_office }}@endif
                                @if($admission->permanent_police_station), Thana: {{ $admission->permanent_police_station }}@endif
                                @if($admission->permanentDistrict), District: {{ $admission->permanentDistrict->name }}@endif
                                @if($admission->permanentDivision), Division: {{ $admission->permanentDivision->name }}@endif
                            @endif
                        </td>
                    </tr>
                    @if($admission->notes)
                    <tr><th style="color:var(--text-muted)">Review Notes:</th><td>{{ $admission->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Review Decision & Log -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Review Audit Log (ভেরিফিকেশন ও অনুমোদন লগ)</span>
            </div>
            <div class="card-body">
                @if($admission->status === 'APPROVED')
                    <div class="alert alert-success" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 14px;border-radius:8px;">
                        <div style="font-size:14px;font-weight:700;"><i class="fa-solid fa-circle-check"></i> ভর্তি অনুমোদিত (Application Approved)</div>
                        <div style="font-size:13px;margin-top:4px;">
                            <strong>অনুমোদনকারী (Approved By):</strong> {{ $admission->reviewer->name ?? 'এডমিন' }}
                        </div>
                        <div style="font-size:12px;color:#15803d;">
                            <strong>অনুমোদনের তারিখ:</strong> {{ $admission->reviewed_at ? \Carbon\Carbon::parse($admission->reviewed_at)->format('d M Y, h:i A') : '—' }}
                        </div>
                        @if($admission->approved_admission_fee !== null)
                        <div style="font-size:12px;color:#15803d;margin-top:2px;">
                            <strong>অনুমোদিত ভর্তি ফি:</strong> ৳{{ number_format($admission->approved_admission_fee, 2) }}
                        </div>
                        @endif
                    </div>
                    @php $studentUser = $admission->student->user; @endphp
                    @if($studentUser)
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:12px 14px;border-radius:8px;font-size:13px;margin-top:10px">
                            <strong style="color:#166534">Student Login Account</strong><br>
                            <span style="color:#15803d">Login Email:</span> <code>{{ $studentUser->email }}</code><br>
                            <span style="color:#15803d">Role:</span> <span class="badge badge-active no-dot">{{ ucfirst($studentUser->role) }}</span>
                            <div style="margin-top:6px;font-size:11px;color:#6b7280">Password was dispatched via batch-specific email/SMS template. Student can reset via admin if needed.</div>
                        </div>
                    @else
                        <div style="background:#fef9c3;border:1px solid #fde047;padding:10px 14px;border-radius:8px;font-size:12px;margin-top:10px;color:#713f12">
                            No user account linked yet. Re-run approval or contact admin.
                        </div>
                    @endif
                @elseif($admission->status === 'REJECTED')
                    <div class="alert alert-danger" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:8px;">
                        <div style="font-size:14px;font-weight:700;"><i class="fa-solid fa-circle-xmark"></i> আবেদন প্রত্যাখ্যাত (Application Rejected)</div>
                        <div style="font-size:13px;margin-top:4px;">
                            <strong>বাতিলের কারণ:</strong> {{ $admission->rejection_reason ?? 'Not specified' }}
                        </div>
                        <div style="font-size:12px;color:#7f1d1d;margin-top:2px;">
                            <strong>পর্যালোচনাকারী:</strong> {{ $admission->reviewer->name ?? 'এডমিন' }} ({{ $admission->reviewed_at ? \Carbon\Carbon::parse($admission->reviewed_at)->format('d M Y, h:i A') : '—' }})
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <strong>⏳ Pending Committee Review (ভেরিফিকেশন অপেক্ষমান)</strong><br>
                        Verify applicant documents, adjust course/batch or fee structure if necessary, and click Approve to generate Student Code & enroll.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Modal (Course Change, Fee Structure Setup & Batch Template Dispatch) -->
    <div class="modal-overlay" id="approveModal">
        <div class="modal" style="max-width:750px;max-height:92vh;display:flex;flex-direction:column;font-family:'Kalpurush',sans-serif;padding:0;overflow:hidden;border-radius:12px;">
            <div class="modal-header" style="padding:16px 22px;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
                <span class="modal-title" style="display:flex;align-items:center;gap:10px;font-size:16px;font-weight:700;color:#0f172a;">
                    <i class="fa-solid fa-user-check" style="color:#047857;font-size:18px;"></i>
                    <span>ভর্তি অনুমোদন ও ব্যাচ নির্ধারণ (Approve Admission)</span>
                </span>
                <button class="modal-close" onclick="closeModal('approveModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.admissions.approve', $admission) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ভর্তি আবেদনটি অনুমোদন করতে চান?')" style="display:flex;flex-direction:column;flex:1;overflow:hidden;margin-bottom:0;">
                @csrf @method('PATCH')
                <div class="modal-body" style="padding:18px 24px;overflow-y:auto;flex:1;">
                    {{-- 1. Course Change Option --}}
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-weight:700;color:#1e293b;font-size:13px;">
                            কোর্স নির্বাচন / পরিবর্তন (Change Course if Applied by Mistake)
                        </label>
                        <select name="course_id" id="approve_course_id" class="form-control" onchange="onApproveCourseChange(this)" style="height:38px;border-radius:6px;font-family:'Kalpurush',sans-serif;">
                            @foreach($allCourses ?? [] as $c)
                                <option value="{{ $c->id }}" {{ $c->id == $admission->interested_course_id ? 'selected' : '' }}>
                                    [{{ $c->formatted_code }}] {{ $c->name }} ({{ $c->department }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color:#64748b;font-size:11px;">আবেদনকারী ভুলে অন্য কোর্স নির্বাচন করে থাকলে এখান থেকে পরিবর্তন করে দিন।</small>
                    </div>

                    {{-- 2. Batch Selection --}}
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-weight:700;color:#1e293b;font-size:13px;">
                            নির্ধারিত ব্যাচ নির্বাচন (Assign Active Batch) <span class="required">*</span>
                        </label>
                        <select name="batch_id" id="approve_batch_id" class="form-control" required onchange="onApproveBatchChange()" style="height:38px;border-radius:6px;font-family:'Kalpurush',sans-serif;">
                            <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                            @foreach($allCourses ?? [] as $c)
                                @foreach($c->batches as $b)
                                    <option value="{{ $b->id }}" data-course-id="{{ $c->id }}" {{ ($admission->batch_id == $b->id || ($c->id == $admission->interested_course_id && $loop->first)) ? 'selected' : '' }}>
                                        {{ $c->name }} → {{ $b->name }} (Code: {{ $b->batch_code ?? 'N/A' }})
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Custom Initial Password --}}
                    <div class="form-group" style="margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <label style="font-weight:700;color:#1e293b;margin-bottom:0;font-size:13px;">
                                লগইন পাসওয়ার্ড নির্ধারণ (Portal Login Password)
                            </label>
                            <button type="button" class="btn btn-sm btn-outline" onclick="generateApprovePassword()" style="font-size:11.5px;padding:2px 8px;border-color:#047857;color:#047857;font-weight:700;">
                                <i class="fa-solid fa-dice"></i> র্যান্ডম জেনারেট
                            </button>
                        </div>
                        <input type="text" id="approve_custom_password" name="custom_password" class="form-control" placeholder="খালি রাখলে স্বয়ংক্রিয়ভাবে ৮ অক্ষরের র্যান্ডম পাসওয়ার্ড তৈরি হবে" oninput="onCustomPasswordInput()" style="height:36px;font-size:13px;font-family:monospace;">
                        <small style="color:#64748b;font-size:11px;">খালি রাখলে সিস্টেম স্বয়ংক্রিয়ভাবে শক্তিশালী র্যান্ডম পাসওয়ার্ড জেনারেট করবে। নিজস্ব পাসওয়ার্ড দিলে তা নোটিফিকেশনে বসানো হবে।</small>
                    </div>

                    {{-- 4. Notification Section (Email & SMS) --}}
                    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:16px;margin-top:6px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                            <div>
                                <h4 style="margin:0;font-size:13.5px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:6px;">
                                    <i class="fa-solid fa-paper-plane" style="color:#047857;"></i>
                                    <span>শিক্ষার্থীকে প্রেরিতব্য নোটিফিকেশন (Email &amp; SMS)</span>
                                </h4>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                    অনুমোদনের সাথে সাথে এই বার্তাগুলো পাঠানো হবে। আপনার প্রয়োজনমত এডিট করতে পারেন।
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm" onclick="populateTemplateForSelectedBatch(true)" style="background:#fff;border:1px solid #cbd5e1;color:#475569;font-size:11px;padding:3px 10px;border-radius:5px;cursor:pointer;" title="নির্বাচিত ব্যাচের ডিফল্ট টেমপ্লেটে রিসেট করুন">
                                <i class="fa-solid fa-rotate-left"></i> টেমপ্লেট রিসেট
                            </button>
                        </div>

                        {{-- Nav Tabs --}}
                        <div style="display:flex;gap:8px;border-bottom:2px solid #e2e8f0;margin-bottom:14px;">
                            <button type="button" id="tabBtnEmail" onclick="switchNotificationTab('email')" style="padding:7px 16px;border:none;border-bottom:2px solid #047857;background:transparent;color:#047857;font-weight:700;font-size:12.5px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;margin-bottom:-2px;">
                                <i class="fa-solid fa-envelope"></i> ইমেইল বার্তা (Email)
                                @if(!empty($admission->email || $admission->student?->email))
                                    <span style="font-size:10px;background:#dcfce7;color:#15803d;padding:1px 6px;border-radius:10px;font-weight:600;">ইমেইল প্রাপক প্রস্তুত</span>
                                @else
                                    <span style="font-size:10px;background:#fee2e2;color:#b91c1c;padding:1px 6px;border-radius:10px;font-weight:600;">ইমেইল নেই</span>
                                @endif
                            </button>
                            <button type="button" id="tabBtnSms" onclick="switchNotificationTab('sms')" style="padding:7px 16px;border:none;border-bottom:2px solid transparent;background:transparent;color:#64748b;font-weight:600;font-size:12.5px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;margin-bottom:-2px;">
                                <i class="fa-solid fa-comment-sms"></i> এসএমএস বার্তা (SMS)
                                @if(!empty($admission->phone || $admission->student?->phone))
                                    <span style="font-size:10px;background:#dcfce7;color:#15803d;padding:1px 6px;border-radius:10px;font-weight:600;">ফোন প্রস্তুত</span>
                                @else
                                    <span style="font-size:10px;background:#fee2e2;color:#b91c1c;padding:1px 6px;border-radius:10px;font-weight:600;">ফোন নেই</span>
                                @endif
                            </button>
                        </div>

                        {{-- Email Tab Content --}}
                        <div id="tabContentEmail">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <label class="form-check" style="cursor:pointer;margin-bottom:0;display:inline-flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="send_email" id="approve_send_email" value="1" {{ !empty($admission->email || $admission->student?->email) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#047857;">
                                    <span style="font-size:12px;font-weight:600;color:#0f172a;">অনুমোদনের সাথে সাথে ইমেইল পাঠান</span>
                                </label>
                                <span style="font-size:11px;color:#64748b;">প্রাপক: <strong>{{ $admission->email ?: ($admission->student?->email ?: 'নেই') }}</strong></span>
                            </div>

                            <div class="form-group" style="margin-bottom:10px;">
                                <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:3px;display:block;">ইমেইল বিষয় (Subject)</label>
                                <input type="text" name="email_subject" id="approve_email_subject" class="form-control" oninput="this.dataset.edited='1'" style="height:34px;font-size:12.5px;font-family:'Kalpurush',sans-serif;">
                            </div>

                            <div class="form-group" style="margin-bottom:8px;">
                                <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:3px;display:block;">ইমেইল বিস্তারিত বার্তা (Email Body)</label>
                                <textarea name="email_body" id="approve_email_body" class="form-control" rows="8" oninput="this.dataset.edited='1'" style="font-size:12px;line-height:1.5;font-family:'Kalpurush',monospace;"></textarea>
                            </div>

                            {{-- Variable Insertion Badges --}}
                            <div style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <span>ট্যাগ যুক্ত করুন:</span>
                                <span class="badge" onclick="insertTagIntoActive('email', '{name}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{name}</span>
                                <span class="badge" onclick="insertTagIntoActive('email', '{course}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{course}</span>
                                <span class="badge" onclick="insertTagIntoActive('email', '{batch}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{batch}</span>
                                <span class="badge" onclick="insertTagIntoActive('email', '{roll}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{roll}</span>
                                <span class="badge" onclick="insertTagIntoActive('email', '{password}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{password}</span>
                                <span class="badge" onclick="insertTagIntoActive('email', '{login_url}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{login_url}</span>
                            </div>
                        </div>

                        {{-- SMS Tab Content --}}
                        <div id="tabContentSms" style="display:none;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <label class="form-check" style="cursor:pointer;margin-bottom:0;display:inline-flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="send_sms" id="approve_send_sms" value="1" {{ !empty($admission->phone || $admission->student?->phone) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#047857;">
                                    <span style="font-size:12px;font-weight:600;color:#0f172a;">অনুমোদনের সাথে সাথে এসএমএস পাঠান</span>
                                </label>
                                <span style="font-size:11px;color:#64748b;">প্রাপক: <strong>{{ $admission->phone ?: ($admission->student?->phone ?: 'নেই') }}</strong></span>
                            </div>

                            <div class="form-group" style="margin-bottom:8px;">
                                <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:3px;display:block;">এসএমএস বার্তা (SMS Body)</label>
                                <textarea name="sms_body" id="approve_sms_body" class="form-control" rows="4" oninput="this.dataset.edited='1';updateSmsCharCounter()" style="font-size:12px;line-height:1.5;font-family:'Kalpurush',monospace;"></textarea>
                            </div>

                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                                <div style="font-size:11px;color:#64748b;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span>ট্যাগ যুক্ত করুন:</span>
                                    <span class="badge" onclick="insertTagIntoActive('sms', '{name}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{name}</span>
                                    <span class="badge" onclick="insertTagIntoActive('sms', '{course}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{course}</span>
                                    <span class="badge" onclick="insertTagIntoActive('sms', '{batch}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{batch}</span>
                                    <span class="badge" onclick="insertTagIntoActive('sms', '{roll}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{roll}</span>
                                    <span class="badge" onclick="insertTagIntoActive('sms', '{password}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{password}</span>
                                    <span class="badge" onclick="insertTagIntoActive('sms', '{login_url}')" style="cursor:pointer;background:#e2e8f0;color:#0f172a;font-family:monospace;padding:2px 6px;">+{login_url}</span>
                                </div>
                                <div id="smsCharCounter" style="font-size:11.5px;color:#047857;font-weight:600;">
                                    ০ অক্ষর | ১ SMS
                                </div>
                            </div>
                        </div>

                        {{-- Save As Template Checkbox --}}
                        <div style="margin-top:14px;padding:10px 14px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;">
                            <label class="form-check" style="cursor:pointer;display:flex;align-items:flex-start;gap:8px;margin-bottom:0;">
                                <input type="checkbox" name="save_template" id="approve_save_template" value="1" checked style="width:16px;height:16px;accent-color:#047857;margin-top:2px;">
                                <div>
                                    <strong style="color:#065f46;font-size:12.5px;">পরবর্তী ব্যবহারের জন্য এই টেমপ্লেটটি ব্যাচে সংরক্ষণ করুন (Save Template for Future Use)</strong>
                                    <div style="font-size:11px;color:#047857;margin-top:1px;">
                                        সক্রিয় রাখলে পরবর্তীতে এই ব্যাচের সকল নতুন শিক্ষার্থী ভর্তির ক্ষেত্রে আপনার বর্তমান এডিট করা নোটিফিকেশনটি স্বয়ংক্রিয়ভাবে ডিফল্ট টেমপ্লেট হিসেবে সংরক্ষিত থাকবে।
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding:14px 22px;border-top:1px solid #e2e8f0;background:#f8fafc;display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveModal')">বাতিল (Cancel)</button>
                    <button type="submit" class="btn btn-success" style="background:#047857;border-color:#047857;">
                        <i class="fa-solid fa-check"></i> অনুমোদন ও সক্রিয় করুন (Confirm &amp; Approve)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const BATCH_TEMPLATES = @json($batchTemplates ?? []);
    const STUDENT_NAME = @json($admission->student?->name ?? $admission->applicant_name ?? 'সম্মানিত শিক্ষার্থী');
    const STUDENT_PHONE = @json($admission->phone ?? $admission->student?->phone ?? '');
    const STUDENT_EMAIL = @json($admission->email ?? $admission->student?->email ?? '');
    const LOGIN_URL = @json(url('/login'));

    let currentNotificationTab = 'email';

    function switchNotificationTab(tab) {
        currentNotificationTab = tab;
        const tabBtnEmail = document.getElementById('tabBtnEmail');
        const tabBtnSms = document.getElementById('tabBtnSms');
        const tabContentEmail = document.getElementById('tabContentEmail');
        const tabContentSms = document.getElementById('tabContentSms');

        if (tab === 'email') {
            if (tabContentEmail) tabContentEmail.style.display = 'block';
            if (tabContentSms) tabContentSms.style.display = 'none';
            if (tabBtnEmail) {
                tabBtnEmail.style.borderColor = '#047857';
                tabBtnEmail.style.color = '#047857';
                tabBtnEmail.style.fontWeight = '700';
            }
            if (tabBtnSms) {
                tabBtnSms.style.borderColor = 'transparent';
                tabBtnSms.style.color = '#64748b';
                tabBtnSms.style.fontWeight = '600';
            }
        } else {
            if (tabContentEmail) tabContentEmail.style.display = 'none';
            if (tabContentSms) tabContentSms.style.display = 'block';
            if (tabBtnSms) {
                tabBtnSms.style.borderColor = '#047857';
                tabBtnSms.style.color = '#047857';
                tabBtnSms.style.fontWeight = '700';
            }
            if (tabBtnEmail) {
                tabBtnEmail.style.borderColor = 'transparent';
                tabBtnEmail.style.color = '#64748b';
                tabBtnEmail.style.fontWeight = '600';
            }
            updateSmsCharCounter();
        }
    }

    function insertTagIntoActive(target, tag) {
        const textarea = target === 'email'
            ? document.getElementById('approve_email_body')
            : document.getElementById('approve_sms_body');
        if (!textarea) return;

        textarea.dataset.edited = '1';
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        if (target === 'sms') {
            updateSmsCharCounter();
        }
    }

    function updateSmsCharCounter() {
        const textarea = document.getElementById('approve_sms_body');
        const counter = document.getElementById('smsCharCounter');
        if (!textarea || !counter) return;

        const val = textarea.value;
        const len = val.length;
        const isNonAscii = /[^\u0000-\u007F]/.test(val);
        const limitPerSms = isNonAscii ? 70 : 160;
        const parts = len > 0 ? Math.ceil(len / (isNonAscii && len > 70 ? 67 : limitPerSms)) : 1;

        counter.innerHTML = `${len} অক্ষর | <strong>${parts} SMS</strong> (${isNonAscii ? 'বাংলা / ইউনিকোড' : 'ইংরেজি'})`;
    }

    function populateTemplateForSelectedBatch(forceReset = false) {
        const batchSelect = document.getElementById('approve_batch_id');
        const batchId = batchSelect ? batchSelect.value : null;
        if (!batchId || !BATCH_TEMPLATES[batchId]) return;

        const b = BATCH_TEMPLATES[batchId];
        const customPassword = document.getElementById('approve_custom_password')?.value.trim();

        let emailTpl = b.email_template || '';
        let smsTpl = b.sms_template || '';

        // Replace placeholders with real student & course data
        const replaceMap = {
            '{name}': STUDENT_NAME,
            '{course}': b.course_name,
            '{batch}': b.name,
            '{login_url}': LOGIN_URL,
        };

        for (const [key, val] of Object.entries(replaceMap)) {
            emailTpl = emailTpl.split(key).join(val);
            smsTpl = smsTpl.split(key).join(val);
        }

        if (customPassword) {
            emailTpl = emailTpl.split('{password}').join(customPassword);
            smsTpl = smsTpl.split('{password}').join(customPassword);
        }

        const emailSubjectInput = document.getElementById('approve_email_subject');
        const emailBodyInput = document.getElementById('approve_email_body');
        const smsBodyInput = document.getElementById('approve_sms_body');

        if (emailSubjectInput && (forceReset || !emailSubjectInput.dataset.edited)) {
            emailSubjectInput.value = `🎉 ভর্তি নিশ্চিতকরণ ও অফিসিয়াল রোল নম্বর — ${STUDENT_NAME} (${b.course_name})`;
            if (forceReset) delete emailSubjectInput.dataset.edited;
        }
        if (emailBodyInput && (forceReset || !emailBodyInput.dataset.edited)) {
            emailBodyInput.value = emailTpl;
            if (forceReset) delete emailBodyInput.dataset.edited;
        }
        if (smsBodyInput && (forceReset || !smsBodyInput.dataset.edited)) {
            smsBodyInput.value = smsTpl;
            if (forceReset) delete smsBodyInput.dataset.edited;
            updateSmsCharCounter();
        }
    }

    function onApproveBatchChange() {
        populateTemplateForSelectedBatch(false);
    }

    function onCustomPasswordInput() {
        // Dynamic template update if custom password changes and not manually overwritten
    }

    function generateApprovePassword() {
        const chars = 'abcdefghjkmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        let res = '';
        for (let i = 0; i < 8; i++) {
            res += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const input = document.getElementById('approve_custom_password');
        if (input) {
            input.value = res;
            input.select();
        }
        populateTemplateForSelectedBatch(false);
    }

    function onApproveCourseChange(courseSelect) {
        const courseId = courseSelect.value;
        const batchSelect = document.getElementById('approve_batch_id');
        const options = batchSelect.querySelectorAll('option');
        let firstMatch = null;

        options.forEach(opt => {
            if (!opt.value) return;
            const cId = opt.getAttribute('data-course-id');
            if (cId === courseId) {
                opt.style.display = '';
                if (!firstMatch) firstMatch = opt;
            } else {
                opt.style.display = 'none';
            }
        });

        if (firstMatch) {
            batchSelect.value = firstMatch.value;
        } else {
            batchSelect.value = '';
        }
        populateTemplateForSelectedBatch(false);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const courseSelect = document.getElementById('approve_course_id');
        if (courseSelect) {
            onApproveCourseChange(courseSelect);
        }
        populateTemplateForSelectedBatch(false);
        updateSmsCharCounter();
    });
    </script>

    <!-- Reject Modal -->
    <div class="modal-overlay" id="rejectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Reject Admission Application</span>
                <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.admissions.reject', $admission) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ভর্তি আবেদনটি বাতিল (Reject) করতে চান?')">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Rejection Reason <span class="required">*</span></label>
                        <textarea name="rejection_reason" class="form-control" placeholder="e.g. Incomplete HSC certificates, GPA below course requirement..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('rejectModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
