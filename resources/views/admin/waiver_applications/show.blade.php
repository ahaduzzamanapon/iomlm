<x-admin-layout>
    <x-slot name="title">Review Waiver Application — {{ $waiverApplication->full_name }}</x-slot>

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
                <button class="btn btn-success btn-lg" onclick="openModal('approveWaiverModal')">✓ Approve Waiver & Set Discount</button>
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
                    @php
                        $applyLabel = match($waiverApplication->apply_for ?? '') {
                            'ADMISSION_FEE' => 'Admission Fee Only (ভর্তি ফি কমানোর জন্য)',
                            'TUITION_FEE'   => 'Tuition Fee Only (মাসিক ফি কমানোর জন্য)',
                            'BOTH'          => 'Both (Admission + Tuition উভয়)',
                            default         => $waiverApplication->apply_reason_type ?? '—',
                        };
                    @endphp
                    <tr><th style="width:160px;color:var(--text-muted)">Applying For:</th><td><span class="badge badge-secondary no-dot">{{ $applyLabel }}</span></td></tr>
                    <tr><th style="color:var(--text-muted)">Convenient Admission Fee:</th><td>৳ {{ number_format($waiverApplication->convenient_admission_fee, 0) }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Convenient Monthly Fee:</th><td>৳ {{ number_format($waiverApplication->convenient_monthly_fee, 0) }}</td></tr>
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
                        <div style="margin-top:6px;font-size:14px">
                            Approved Discount / Waiver:
                            <strong>
                                @if($waiverApplication->discount_type === 'FIXED')
                                    ৳ {{ number_format($waiverApplication->approved_discount_value, 0) }} Fixed Taka
                                @else
                                    {{ $waiverApplication->approved_discount_value > 0 ? $waiverApplication->approved_discount_value : $waiverApplication->approved_discount_percent }}% Percentage
                                @endif
                            </strong>
                        </div>
                        @if($waiverApplication->reviewer_notes)
                            <div style="margin-top:8px"><em>Notes: {{ $waiverApplication->reviewer_notes }}</em></div>
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
                        Verify the applicant's financial hardship details, convenient fee amounts, and click Approve to set percentage or fixed taka waiver amount.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Waiver Modal -->
    <div class="modal-overlay" id="approveWaiverModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Approve Waiver & Set Discount</span>
                <button class="modal-close" onclick="closeModal('approveWaiverModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.waiver-applications.approve', $waiverApplication) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Discount / Waiver Type <span class="required">*</span></label>
                        <select name="discount_type" id="approve_discount_type" class="form-control" onchange="toggleDiscountLabel(this.value)" required>
                            <option value="PERCENTAGE">Percentage Discount (%)</option>
                            <option value="FIXED">Fixed Amount Discount (৳ Taka)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label id="discount_val_label">Approved Discount Percentage (%) <span class="required">*</span></label>
                        <input type="number" name="approved_discount_value" id="approved_discount_value" class="form-control" value="50" min="0" step="0.5" placeholder="e.g. 50 for 50% or 1000 for ৳1000" required>
                        <small style="color:var(--text-muted);font-size:12px" id="discount_val_help">Enter percentage discount to be applied on student's fee.</small>
                    </div>

                    <div class="form-group">
                        <label>Committee Review Notes</label>
                        <textarea name="reviewer_notes" class="form-control" rows="3" placeholder="Approval notes, zakat fund approval, or special conditions..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveWaiverModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">✓ Confirm & Approve Waiver</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleDiscountLabel(type) {
        const lbl = document.getElementById('discount_val_label');
        const help = document.getElementById('discount_val_help');
        if (type === 'FIXED') {
            lbl.innerHTML = 'Approved Fixed Discount Amount (৳ Taka) <span class="required">*</span>';
            help.innerHTML = 'Enter fixed amount discount in Taka (e.g. 1000 Tk off).';
        } else {
            lbl.innerHTML = 'Approved Discount Percentage (%) <span class="required">*</span>';
            help.innerHTML = 'Enter percentage discount to be applied on student fee (e.g. 50% off).';
        }
    }
    </script>

    <!-- Reject Waiver Modal -->
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
</x-admin-layout>
