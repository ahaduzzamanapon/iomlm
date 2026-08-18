<x-admin-layout>
    <x-slot name="title">Fee Structure Setup & Master Rates</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>⚙️ Fee Structure Setup</h1>
            <p>Configure default rate templates for Admission, Semester Tuition, Subject Retakes &amp; Certificates</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('createRateModal')">
                + Set New Fee Rate
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Fee Rates Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fee Head Name</th>
                        <th>Category</th>
                        <th>Course Target</th>
                        <th>Amount (৳)</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($structures as $i => $fee)
                    <tr>
                        <td class="td-muted">{{ $i + 1 }}</td>
                        <td class="td-primary"><strong>{{ $fee->name }}</strong></td>
                        <td><span class="badge badge-secondary no-dot">{{ $fee->category }}</span></td>
                        <td>{{ $fee->course->name ?? 'All Courses (Default)' }}</td>
                        <td><strong style="color:#10b981;font-size:15px">৳{{ number_format($fee->amount, 2) }}</strong></td>
                        <td class="td-muted">{{ $fee->description ?? '—' }}</td>
                        <td>
                            @if($fee->is_active)
                                <span class="badge badge-success no-dot">ACTIVE</span>
                            @else
                                <span class="badge badge-secondary no-dot">INACTIVE</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:#94a3b8">
                            No master fee rates configured yet. Click "+ Set New Fee Rate" above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Rate Modal --}}
    <div class="modal-overlay" id="createRateModal">
        <div class="modal" style="max-width:550px">
            <div class="modal-header">
                <span class="modal-title">+ Set Master Fee Structure Rate</span>
                <button class="modal-close" onclick="closeModal('createRateModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.accounts.fee-structures.store') }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>Fee Head Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Standard Admission Fee / CSE Semester Fee" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="ADMISSION">ADMISSION (ভর্তি ফি)</option>
                                <option value="SEMESTER">SEMESTER (সেমিস্টার টিউশন ফি)</option>
                                <option value="RETAKE">RETAKE (বিষয় রিটেক ফি)</option>
                                <option value="EXAM">EXAM (পরীক্ষা ফি)</option>
                                <option value="DOCUMENT">DOCUMENT (সার্টিফিকেট ফি)</option>
                                <option value="OTHER">OTHER (অন্যান্য)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Course (Optional)</label>
                            <select name="course_id" class="form-control">
                                <option value="">All Courses (Global)</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Default Rate Amount (৳) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="5000.00" required>
                    </div>

                    <div class="form-group">
                        <label>Description / Notes</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createRateModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">💾 Save Fee Rate</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
