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
        <div class="page-header-actions">
            @if($waiverApplication->status === 'PENDING')
                <button class="btn btn-success btn-lg" onclick="openModal('approveWaiverModal')">✓ Approve Waiver</button>
                <button class="btn btn-danger btn-lg" onclick="openModal('rejectWaiverModal')">✕ Reject Application</button>
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
                    <tr><th style="color:var(--text-muted)">Mobile Number:</th><td>{{ $waiverApplication->phone }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Email Address:</th><td>{{ $waiverApplication->email }}</td></tr>
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
                    <tr><th style="color:var(--text-muted)">Convenient Admission Fee:</th><td>৳ {{ number_format($waiverApplication->convenient_admission_fee, 0) }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Convenient Monthly Fee:</th><td>৳ {{ number_format($waiverApplication->convenient_monthly_fee, 0) }}</td></tr>
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
                        <strong style="font-size:16px">✓ Waiver Approved</strong><br>
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
                        <strong style="font-size:16px">✕ Application Rejected</strong><br>
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
                        <div style="font-weight:700;color:#0369a1;margin-bottom:8px">📋 Approval Required:</div>
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
            <form method="POST" action="{{ route('admin.waiver-applications.approve', $waiverApplication) }}">
                @csrf @method('PATCH')
                <div class="modal-body">

                    {{-- ── ADMISSION FEE section ── --}}
                    @if(in_array($applyFor, ['ADMISSION_FEE', 'BOTH']))
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 16px;margin-bottom:16px">
                            <div style="font-weight:700;color:#166534;margin-bottom:10px;font-size:13px">🏫 Admission Fee Setting</div>
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
                            <div style="font-weight:700;color:#92400e;margin-bottom:10px;font-size:13px">📦 Fee Package Selection (Tuition)</div>

                            @if($coursePackages->isEmpty())
                                <div style="color:#dc2626;font-size:13px;background:#fef2f2;padding:10px;border-radius:6px;border:1px solid #fecaca">
                                    ⚠ এই course এ কোনো Fee Package তৈরি করা হয়নি।
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
                                                @if($pkg->is_default) ★ Default @endif
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
                    <button type="submit" class="btn btn-success">✓ Confirm & Approve Waiver</button>
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
            <form method="POST" action="{{ route('admin.waiver-applications.reject', $waiverApplication) }}">
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

    @push('scripts')
    <script>
    function showPackageDetail(sel) {
        const opt = sel.options[sel.selectedIndex];
        const box = document.getElementById('pkgDetailBox');
        if (!box) return;
        const items = opt.dataset.items;
        const total = opt.dataset.total;
        if (sel.value && items) {
            box.style.display = 'block';
            box.innerHTML = '<strong>Items:</strong> ' + items;
        } else {
            box.style.display = 'none';
        }
    }
    // Trigger on load for default selected
    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.querySelector('select[name="approved_package_id"]');
        if (sel) showPackageDetail(sel);
    });
    </script>
    @endpush

</x-admin-layout>
