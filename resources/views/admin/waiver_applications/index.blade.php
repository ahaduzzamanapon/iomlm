<x-admin-layout>
    <x-slot name="title">Poor Fund & Waiver Applications</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Poor Fund / Waiver Applications</h1>
            <p>Review financial assistance requests and poor fund applications submitted by applicants</p>
        </div>
        <div class="page-header-actions" style="display:flex;gap:10px;align-items:center">
            <button type="button" class="btn btn-primary" onclick="openShareLinksModal()" style="display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-share-nodes"></i> আবেদন লিংকসমূহ (Copy Links)
            </button>
            <a href="{{ route('poor_fund.show') }}" target="_blank" class="btn btn-outline">View Public Form ↗</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid-3" style="margin-bottom:24px">
        <div class="stat-card">
            <div class="stat-label">Pending Review</div>
            <div class="stat-value" style="color:var(--yellow)">{{ $pendingCount }}</div>
            <div class="stat-sub">Requires committee decision</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Approved Waivers</div>
            <div class="stat-value" style="color:var(--green)">{{ $approvedCount }}</div>
            <div class="stat-sub">Financial assistance granted</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Rejected</div>
            <div class="stat-value" style="color:var(--red)">{{ $rejectedCount }}</div>
            <div class="stat-sub">Applications declined</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:20px;padding:16px">
        <form method="GET" action="{{ route('admin.waiver-applications.index') }}" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <input type="text" name="search" class="form-control" style="max-width:280px" value="{{ $search }}" placeholder="Search name, phone, APP no...">
            <select name="status" class="form-control" style="max-width:180px" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>Pending Only</option>
                <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>Approved Only</option>
                <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>Rejected Only</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if($status || $search)
                <a href="{{ route('admin.waiver-applications.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-wrapper" style="overflow-x:auto;-webkit-overflow-scrolling:touch">
            <table style="min-width:1050px;width:100%">
                <thead>
                    <tr>
                        <th style="white-space:nowrap">App No</th>
                        <th style="white-space:nowrap">Applicant Name</th>
                        <th style="white-space:nowrap">Phone / Email</th>
                        <th style="white-space:nowrap">Monthly Income</th>
                        <th style="white-space:nowrap">Reason / Fee Convenience</th>
                        <th style="white-space:nowrap">Status</th>
                        <th style="text-align:right;white-space:nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td style="white-space:nowrap"><span class="badge badge-scheduled no-dot"><strong>{{ $app->application_no }}</strong></span></td>
                        <td class="td-primary">
                            <a href="{{ route('admin.waiver-applications.show', $app) }}" style="font-weight:600;color:var(--blue)">{{ $app->full_name }}</a>
                            @if($app->is_abroad) <span class="badge badge-secondary no-dot" style="font-size:10px">Abroad</span> @endif
                        </td>
                        <td>
                            <div>{{ $app->phone }}</div>
                            <div class="td-muted" style="font-size:11px">{{ $app->email }}</div>
                        </td>
                        <td style="white-space:nowrap"><strong>৳ {{ number_format($app->monthly_income, 0) }}</strong></td>
                        <td>
                            @php
                                $applyLabel = match($app->apply_for ?? '') {
                                    'ADMISSION_FEE' => 'Admission Fee Only',
                                    'TUITION_FEE'   => 'Tuition Fee Only',
                                    'BOTH'          => 'Both (Admission + Tuition)',
                                    default         => $app->apply_reason_type ?? '—',
                                };
                            @endphp
                            <span class="badge badge-secondary no-dot">{{ $applyLabel }}</span>
                            <div class="td-muted" style="font-size:11px">
                                @if($app->convenient_admission_fee) Adm: ৳{{ $app->convenient_admission_fee }} @endif
                                @if($app->convenient_monthly_fee) Monthly: ৳{{ $app->convenient_monthly_fee }} @endif
                            </div>
                        </td>
                        <td style="white-space:nowrap">
                            @if($app->status === 'PENDING')
                                <span class="badge badge-pending">⏳ Pending Review</span>
                            @elseif($app->status === 'APPROVED')
                                @php
                                    $parts = [];
                                    if ($app->approved_admission_fee !== null) $parts[] = 'Adm: ৳'.number_format($app->approved_admission_fee, 0);
                                    if ($app->approved_package_id) $parts[] = 'Pkg: #'.$app->approved_package_id;
                                    if (empty($parts) && $app->approved_discount_value > 0) {
                                        $parts[] = ($app->discount_type === 'FIXED' ? '৳'.number_format($app->approved_discount_value,0).' Fixed' : $app->approved_discount_value.'%');
                                    }
                                    $discDisplay = implode(' | ', $parts) ?: '—';
                                @endphp
                                <span class="badge badge-active">Approved ({{ $discDisplay }})</span>
                            @else
                                <span class="badge badge-danger">Rejected</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <a href="{{ route('admin.waiver-applications.show', $app) }}" class="btn btn-outline btn-sm">Review App →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No poor fund applications found matching filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:16px">
            {{ $applications->links() }}
        </div>
    </div>

    {{-- Direct Links Modal for Admin --}}
    <div id="shareLinksModal" class="modal-overlay" onclick="if(event.target===this) closeShareLinksModal()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:9999;box-sizing:border-box">
        <div class="modal" style="background:#fff;border-radius:16px;max-width:560px;width:100%;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);animation:modalSlideUp .25s ease">
            <div style="background:linear-gradient(135deg,#047857,#065f46);color:#fff;padding:18px 22px;display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-weight:700;font-size:16px;display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-link"></i> স্পেশাল ডিসকাউন্ট ও ওয়েভার আবেদন লিংকসমূহ
                    </div>
                    <div style="font-size:12px;opacity:.9;margin-top:2px">পাবলিক পেজে এই লিংকগুলো লুকানো থাকে; প্রয়োজন অনুযায়ী শিক্ষার্থীকে ইনবক্সে পাঠান</div>
                </div>
                <button type="button" onclick="closeShareLinksModal()" style="background:none;border:none;color:#fff;font-size:24px;cursor:pointer;line-height:1">&times;</button>
            </div>

            {{-- Prefix Switcher (SD / PF) --}}
            <div style="background:#f1f5f9;padding:10px 22px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0">
                <span style="font-size:12.5px;font-weight:600;color:#475569">
                    <i class="fa-solid fa-sliders" style="margin-right:4px"></i> লিংকের ধরন (Prefix):
                </span>
                <div style="display:inline-flex;background:#e2e8f0;padding:2px;border-radius:8px;gap:2px">
                    <button type="button" id="btnPrefixSd" onclick="switchLinkPrefix('sd')" style="padding:4px 14px;border:none;border-radius:6px;font-size:12px;font-weight:700;background:#047857;color:#fff;cursor:pointer;transition:all .15s">
                        /sd (Special Discount)
                    </button>
                    <button type="button" id="btnPrefixPf" onclick="switchLinkPrefix('pf')" style="padding:4px 14px;border:none;border-radius:6px;font-size:12px;font-weight:700;background:transparent;color:#475569;cursor:pointer;transition:all .15s">
                        /pf (PF)
                    </button>
                </div>
            </div>

            <div style="padding:22px;display:flex;flex-direction:column;gap:14px">
                
                {{-- Link 1: Admission Fee --}}
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <span style="font-size:13px;font-weight:700;color:#0f172a">
                            <i class="fa-solid fa-graduation-cap" style="color:#047857"></i> ১. শুধুমাত্র ভর্তি ফি কমানো
                        </span>
                        <button type="button" class="btn btn-sm btn-primary copy-btn" onclick="copyLink(document.getElementById('inputLinkAdmission').value, this)">
                            <i class="fa-regular fa-copy"></i> কপি করুন
                        </button>
                    </div>
                    <input type="text" id="inputLinkAdmission" readonly value="{{ route('poor_fund.admission') }}" style="width:100%;font-size:12px;background:#fff;color:#64748b;padding:8px 10px;border-radius:6px;border:1px solid #cbd5e1" onclick="this.select()">
                </div>

                {{-- Link 2: Tuition Fee --}}
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <span style="font-size:13px;font-weight:700;color:#0f172a">
                            <i class="fa-solid fa-book-open-reader" style="color:#047857"></i> ২. শুধুমাত্র টিউশন ফি কমানো
                        </span>
                        <button type="button" class="btn btn-sm btn-primary copy-btn" onclick="copyLink(document.getElementById('inputLinkTuition').value, this)">
                            <i class="fa-regular fa-copy"></i> কপি করুন
                        </button>
                    </div>
                    <input type="text" id="inputLinkTuition" readonly value="{{ route('poor_fund.tuition') }}" style="width:100%;font-size:12px;background:#fff;color:#64748b;padding:8px 10px;border-radius:6px;border:1px solid #cbd5e1" onclick="this.select()">
                </div>

                {{-- Link 3: Both Fees --}}
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <span style="font-size:13px;font-weight:700;color:#0f172a">
                            <i class="fa-solid fa-layer-group" style="color:#047857"></i> ৩. উভয় ফি কমানোর আবেদন (সকল)
                        </span>
                        <button type="button" class="btn btn-sm btn-primary copy-btn" onclick="copyLink(document.getElementById('inputLinkBoth').value, this)">
                            <i class="fa-regular fa-copy"></i> কপি করুন
                        </button>
                    </div>
                    <input type="text" id="inputLinkBoth" readonly value="{{ route('poor_fund.both') }}" style="width:100%;font-size:12px;background:#fff;color:#64748b;padding:8px 10px;border-radius:6px;border:1px solid #cbd5e1" onclick="this.select()">
                </div>

            </div>
            <div style="padding:14px 22px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:12px;color:#64748b">লিংক কপি করে সরাসরি WhatsApp / Email / Messenger এ পাঠান</span>
                <button type="button" onclick="closeShareLinksModal()" class="btn btn-outline btn-sm">বন্ধ করুন</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const linksData = {
        sd: {
            admission: "{{ route('poor_fund.admission') }}",
            tuition: "{{ route('poor_fund.tuition') }}",
            both: "{{ route('poor_fund.both') }}"
        },
        pf: {
            admission: "{{ route('pf.admission') }}",
            tuition: "{{ route('pf.tuition') }}",
            both: "{{ route('pf.both') }}"
        }
    };

    function switchLinkPrefix(type) {
        const btnSd = document.getElementById('btnPrefixSd');
        const btnPf = document.getElementById('btnPrefixPf');
        if (type === 'sd') {
            btnSd.style.background = '#047857';
            btnSd.style.color = '#fff';
            btnPf.style.background = 'transparent';
            btnPf.style.color = '#475569';
        } else {
            btnPf.style.background = '#047857';
            btnPf.style.color = '#fff';
            btnSd.style.background = 'transparent';
            btnSd.style.color = '#475569';
        }

        document.getElementById('inputLinkAdmission').value = linksData[type].admission;
        document.getElementById('inputLinkTuition').value = linksData[type].tuition;
        document.getElementById('inputLinkBoth').value = linksData[type].both;
    }

    function openShareLinksModal() {
        document.getElementById('shareLinksModal').style.display = 'flex';
    }
    function closeShareLinksModal() {
        document.getElementById('shareLinksModal').style.display = 'none';
    }
    function copyLink(url, btn) {
        navigator.clipboard.writeText(url).then(function() {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> কপি হয়েছে!';
            btn.style.background = '#15803d';
            btn.style.color = '#fff';
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        }).catch(function() {
            prompt('লিংকটি কপি করুন:', url);
        });
    }
    </script>
    @endpush
</x-admin-layout>
