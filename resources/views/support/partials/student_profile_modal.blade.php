{{-- Reusable Support Student Profile Modal --}}
<div class="modal-overlay" id="supportStudentProfileModal" style="display:none;align-items:center;justify-content:center;z-index:99999">
    <div class="modal" style="max-width:820px;width:95%;max-height:92vh;display:flex;flex-direction:column;padding:0;border-radius:12px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);font-family:'Kalpurush',sans-serif">
        
        {{-- Modal Header --}}
        <div style="background:linear-gradient(135deg,#064e3b 0%,#047857 50%,#0284c7 100%);color:#fff;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;position:relative">
            <div style="display:flex;align-items:center;gap:14px">
                <div id="spmAvatar" style="width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff;border:2px solid rgba(255,255,255,0.4);overflow:hidden">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <h3 id="spmName" style="margin:0;font-size:18px;font-weight:700;color:#fff">শিক্ষার্থীর নাম</h3>
                        <span id="spmStatusBadge" class="badge" style="background:#22c55e;color:#fff;font-size:11px;padding:2px 8px">ACTIVE</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:4px;font-size:13px;opacity:0.95">
                        <span>স্টুডেন্ট আইডি:</span>
                        <strong id="spmCode" style="font-family:monospace;letter-spacing:1px;background:rgba(0,0,0,0.25);padding:2px 8px;border-radius:4px;color:#fef08a"></strong>
                        <span id="spmGender" style="background:rgba(255,255,255,0.2);padding:1px 6px;border-radius:4px;font-size:11px"></span>
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:12px">
                <button type="button" onclick="closeStudentProfileModal()" style="background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:20px;width:34px;height:34px;border-radius:50%;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;line-height:1;transition:background 0.2s" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">&times;</button>
            </div>
        </div>

        {{-- Quick Search Bar inside modal --}}
        <div style="background:#f8fafc;padding:10px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:10px">
            <div style="font-size:12px;font-weight:700;color:#64748b;white-space:nowrap">
                <i class="fa-solid fa-magnifying-glass"></i> আইডি দিয়ে খুঁজুন:
            </div>
            <div style="display:flex;flex:1;gap:6px">
                <input type="text" id="spmSearchInput" placeholder="স্টুডেন্ট আইডি / ফোন / ইমেইল লিখুন..." style="flex:1;height:34px;padding:6px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;font-family:'Kalpurush',sans-serif;outline:none" onkeydown="if(event.key==='Enter'){event.preventDefault();searchStudentProfileFromModal();}">
                <button type="button" onclick="searchStudentProfileFromModal()" class="btn btn-primary" style="height:34px;padding:0 14px;background:#0284c7;border:none;border-radius:6px;color:#fff;font-weight:700;font-size:12px;cursor:pointer">
                    চেক করুন
                </button>
            </div>
        </div>

        {{-- Loading Spinner --}}
        <div id="spmLoading" style="display:none;padding:50px 20px;text-align:center;color:#0284c7">
            <i class="fa-solid fa-circle-notch fa-spin fa-2x"></i>
            <div style="margin-top:10px;font-size:14px;font-weight:600">শিক্ষার্থীর তথ্য লোড হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...</div>
        </div>

        {{-- Error Container --}}
        <div id="spmError" style="display:none;padding:24px;text-align:center;color:#b91c1c;background:#fef2f2;margin:16px 22px;border-radius:8px;border:1px solid #fecaca;font-weight:600">
        </div>

        {{-- Modal Content Area --}}
        <div id="spmBody" style="overflow-y:auto;flex:1;padding:18px 22px">
            {{-- Tabs Header --}}
            <div style="display:flex;border-bottom:2px solid #e2e8f0;margin-bottom:16px;gap:8px">
                <button type="button" class="spm-tab-btn active" onclick="switchSpmTab('tabOverview')" id="btnTabOverview" style="padding:8px 16px;border:none;background:transparent;font-weight:700;font-size:13px;color:#047857;border-bottom:3px solid #047857;cursor:pointer">
                    <i class="fa-solid fa-id-card"></i> সাধারণ ও একাডেমি
                </button>
                <button type="button" class="spm-tab-btn" onclick="switchSpmTab('tabFinancials')" id="btnTabFinancials" style="padding:8px 16px;border:none;background:transparent;font-weight:700;font-size:13px;color:#64748b;border-bottom:3px solid transparent;cursor:pointer">
                    <i class="fa-solid fa-wallet"></i> ফি ও একাউন্টস
                </button>
                <button type="button" class="spm-tab-btn" onclick="switchSpmTab('tabExams')" id="btnTabExams" style="padding:8px 16px;border:none;background:transparent;font-weight:700;font-size:13px;color:#64748b;border-bottom:3px solid transparent;cursor:pointer">
                    <i class="fa-solid fa-award"></i> পরীক্ষা ও ফলাফল
                </button>
                <button type="button" class="spm-tab-btn" onclick="switchSpmTab('tabAttendance')" id="btnTabAttendance" style="padding:8px 16px;border:none;background:transparent;font-weight:700;font-size:13px;color:#64748b;border-bottom:3px solid transparent;cursor:pointer">
                    <i class="fa-solid fa-calendar-check"></i> উপস্থিতি
                </button>
            </div>

            {{-- TAB 1: Overview & Academic --}}
            <div id="spmTabOverview" class="spm-tab-pane">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                    {{-- Contact Info Card --}}
                    <div style="background:#f8fafc;padding:14px 16px;border-radius:8px;border:1px solid #e2e8f0">
                        <div style="font-weight:700;font-size:13px;color:#0f172a;margin-bottom:10px;display:flex;align-items:center;gap:6px;border-bottom:1px solid #cbd5e1;padding-bottom:6px">
                            <i class="fa-solid fa-address-book" style="color:#0284c7"></i> যোগাযোগের তথ্য
                        </div>
                        <table style="width:100%;font-size:13px;line-height:1.8">
                            <tr>
                                <td style="color:#64748b;width:100px">ফোন নম্বর:</td>
                                <td><a id="spmPhoneLink" href="#" style="color:#0284c7;font-weight:700;text-decoration:none"><span id="spmPhone"></span></a></td>
                            </tr>
                            <tr>
                                <td style="color:#64748b">ইমেইল:</td>
                                <td><span id="spmEmail" style="word-break:break-all"></span></td>
                            </tr>
                            <tr>
                                <td style="color:#64748b">রক্তের গ্রুপ:</td>
                                <td><span id="spmBlood" class="badge" style="background:#fee2e2;color:#991b1b;font-weight:700"></span></td>
                            </tr>
                            <tr>
                                <td style="color:#64748b">অভিভাবক:</td>
                                <td><strong id="spmGuardian"></strong> <span id="spmGuardianPhone" style="color:#64748b;font-size:12px"></span></td>
                            </tr>
                            <tr>
                                <td style="color:#64748b">ঠিকানা:</td>
                                <td><span id="spmAddress" style="color:#334155"></span></td>
                            </tr>
                        </table>
                    </div>

                    {{-- Academic Info Card --}}
                    <div style="background:#f0fdf4;padding:14px 16px;border-radius:8px;border:1px solid #bbf7d0">
                        <div style="font-weight:700;font-size:13px;color:#166534;margin-bottom:10px;display:flex;align-items:center;gap:6px;border-bottom:1px solid #bbf7d0;padding-bottom:6px">
                            <i class="fa-solid fa-graduation-cap" style="color:#16a34a"></i> বর্তমান একাডেমিক স্ট্যাটাস
                        </div>
                        <table style="width:100%;font-size:13px;line-height:1.8">
                            <tr>
                                <td style="color:#166534;width:95px">বর্তমান কোর্স:</td>
                                <td><strong id="spmCourse" style="color:#065f46;font-size:13.5px"></strong></td>
                            </tr>
                            <tr>
                                <td style="color:#166534">ব্যাচ:</td>
                                <td><span id="spmBatch" class="badge" style="background:#dcfce7;color:#15803d;font-weight:700"></span></td>
                            </tr>
                            <tr>
                                <td style="color:#166534">সেমিস্টার:</td>
                                <td><span id="spmSemester" style="font-weight:700;color:#166534"></span></td>
                            </tr>
                        </table>

                        <div style="margin-top:10px;font-size:12px;font-weight:700;color:#166534">এনরোলমেন্ট হিস্ট্রি:</div>
                        <div id="spmEnrollmentList" style="display:flex;flex-direction:column;gap:4px;margin-top:4px;max-height:90px;overflow-y:auto">
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: Financials & Fees --}}
            <div id="spmTabFinancials" class="spm-tab-pane" style="display:none">
                {{-- KPI Cards --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px">
                    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px;text-align:center">
                        <div style="font-size:11px;color:#64748b;font-weight:700">মোট ধার্য ফি</div>
                        <div id="spmTotalBilled" style="font-size:18px;font-weight:800;color:#0f172a;margin-top:4px">৳ ০.০০</div>
                    </div>
                    <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:12px;text-align:center">
                        <div style="font-size:11px;color:#047857;font-weight:700">মোট পরিশোধিত</div>
                        <div id="spmTotalPaid" style="font-size:18px;font-weight:800;color:#047857;margin-top:4px">৳ ০.০০</div>
                    </div>
                    <div id="spmDueCard" style="background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;padding:12px;text-align:center">
                        <div style="font-size:11px;color:#991b1b;font-weight:700">বর্তমান বকেয়া</div>
                        <div id="spmTotalDue" style="font-size:18px;font-weight:800;color:#e11d48;margin-top:4px">৳ ০.০০</div>
                    </div>
                </div>

                <div style="font-weight:700;font-size:13px;color:#0f172a;margin-bottom:8px">সাম্প্রতিক ইনভয়েস ও পেমেন্ট স্ট্যাটাস:</div>
                <div style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden">
                    <table style="width:100%;border-collapse:collapse;font-size:12px">
                        <thead>
                            <tr style="background:#f1f5f9;color:#475569;text-align:left">
                                <th style="padding:8px 12px">ইনভয়েস নং</th>
                                <th style="padding:8px 12px">বিবরণ / মাস</th>
                                <th style="padding:8px 12px">পরিমাণ</th>
                                <th style="padding:8px 12px">পরিশোধ</th>
                                <th style="padding:8px 12px">বকেয়া</th>
                                <th style="padding:8px 12px">স্ট্যাটাস</th>
                            </tr>
                        </thead>
                        <tbody id="spmInvoicesTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 3: Results & Exams --}}
            <div id="spmTabExams" class="spm-tab-pane" style="display:none">
                <div style="font-weight:700;font-size:13px;color:#0f172a;margin-bottom:8px">সাম্প্রতিক পরীক্ষার ফলাফল:</div>
                <div style="border:1px solid #e2e8f0;border-radius:6px;overflow:hidden">
                    <table style="width:100%;border-collapse:collapse;font-size:12px">
                        <thead>
                            <tr style="background:#f1f5f9;color:#475569;text-align:left">
                                <th style="padding:8px 12px">পরীক্ষার নাম</th>
                                <th style="padding:8px 12px">প্রাপ্ত নম্বর</th>
                                <th style="padding:8px 12px">মোট নম্বর</th>
                                <th style="padding:8px 12px">গ্রেড</th>
                                <th style="padding:8px 12px">তারিখ</th>
                            </tr>
                        </thead>
                        <tbody id="spmResultsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB 4: Attendance --}}
            <div id="spmTabAttendance" class="spm-tab-pane" style="display:none">
                <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <span style="font-weight:700;font-size:13px;color:#334155">সামগ্রিক ক্লাসে উপস্থিতি হার:</span>
                        <strong id="spmAttendancePercent" style="font-size:16px;color:#0284c7">0%</strong>
                    </div>
                    <div style="background:#e2e8f0;height:10px;border-radius:5px;overflow:hidden">
                        <div id="spmAttendanceBar" style="background:#0284c7;height:100%;width:0%;transition:width 0.4s"></div>
                    </div>
                    <div style="display:flex;gap:20px;margin-top:10px;font-size:12px;color:#64748b">
                        <span>মোট ক্লাস: <strong id="spmTotalClasses" style="color:#0f172a">0</strong></span>
                        <span>উপস্থিতি: <strong id="spmPresentClasses" style="color:#047857">0</strong></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div style="background:#f8fafc;padding:12px 22px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
            <div id="spmTicketLinkAction">
                {{-- Dynamic Link to Ticket button if inside chat --}}
            </div>

            <div style="display:flex;align-items:center;gap:8px">
                <a id="spmAdminProfileLink" href="#" target="_blank" class="btn btn-outline btn-sm" style="display:none;color:#047857;border-color:#10b981;font-weight:700">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> সম্পূর্ণ প্রোফাইল (Admin) ↗
                </a>
                <a id="spmAdminAccountsLink" href="#" target="_blank" class="btn btn-outline btn-sm" style="display:none;color:#4f46e5;border-color:#818cf8;font-weight:700">
                    <i class="fa-solid fa-wallet"></i> লেজার ↗
                </a>
                <button type="button" onclick="closeStudentProfileModal()" class="btn btn-secondary btn-sm" style="font-weight:700">
                    বন্ধ করুন
                </button>
            </div>
        </div>

    </div>
</div>

<script>
let currentActiveStudentCode = null;

function switchSpmTab(tabId) {
    document.querySelectorAll('.spm-tab-pane').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.spm-tab-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.color = '#64748b';
        btn.style.borderBottomColor = 'transparent';
    });

    if (tabId === 'tabOverview') {
        document.getElementById('spmTabOverview').style.display = 'block';
        const b = document.getElementById('btnTabOverview');
        b.classList.add('active');
        b.style.color = '#047857';
        b.style.borderBottomColor = '#047857';
    } else if (tabId === 'tabFinancials') {
        document.getElementById('spmTabFinancials').style.display = 'block';
        const b = document.getElementById('btnTabFinancials');
        b.classList.add('active');
        b.style.color = '#047857';
        b.style.borderBottomColor = '#047857';
    } else if (tabId === 'tabExams') {
        document.getElementById('spmTabExams').style.display = 'block';
        const b = document.getElementById('btnTabExams');
        b.classList.add('active');
        b.style.color = '#047857';
        b.style.borderBottomColor = '#047857';
    } else if (tabId === 'tabAttendance') {
        document.getElementById('spmTabAttendance').style.display = 'block';
        const b = document.getElementById('btnTabAttendance');
        b.classList.add('active');
        b.style.color = '#047857';
        b.style.borderBottomColor = '#047857';
    }
}

function openStudentProfileModal(studentCode) {
    const modal = document.getElementById('supportStudentProfileModal');
    if (!modal) return;
    modal.style.display = 'flex';
    document.getElementById('spmSearchInput').value = studentCode || '';
    switchSpmTab('tabOverview');

    if (studentCode) {
        fetchStudentProfile(studentCode);
    } else {
        document.getElementById('spmLoading').style.display = 'none';
        document.getElementById('spmBody').style.display = 'none';
        document.getElementById('spmError').style.display = 'block';
        document.getElementById('spmError').innerText = 'অনুগ্রহ করে উপরের সার্চ বক্সে স্টুডেন্ট আইডি লিখুন এবং চেক করুন এ ক্লিক করুন।';
    }
}

function closeStudentProfileModal() {
    const modal = document.getElementById('supportStudentProfileModal');
    if (modal) modal.style.display = 'none';
}

function searchStudentProfileFromModal() {
    const query = document.getElementById('spmSearchInput').value.trim();
    if (!query) {
        alert('অনুগ্রহ করে স্টুডেন্ট আইডি লিখুন!');
        return;
    }
    fetchStudentProfile(query);
}

function fetchStudentProfile(codeOrQuery) {
    const loading = document.getElementById('spmLoading');
    const body = document.getElementById('spmBody');
    const errorEl = document.getElementById('spmError');

    loading.style.display = 'block';
    body.style.display = 'none';
    errorEl.style.display = 'none';

    fetch(`{{ route('support.api.student-lookup') }}?code=${encodeURIComponent(codeOrQuery)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body: res }) => {
        loading.style.display = 'none';
        if (status !== 200 || !res.success) {
            errorEl.style.display = 'block';
            errorEl.innerText = res.message || 'শিক্ষার্থীর কোনো তথ্য পাওয়া যায়নি।';
            return;
        }

        const s = res.student;
        currentActiveStudentCode = s.student_code;

        // Header
        document.getElementById('spmName').innerText = s.name;
        document.getElementById('spmCode').innerText = s.student_code;
        document.getElementById('spmGender').innerText = s.gender || '—';
        document.getElementById('spmStatusBadge').innerText = s.status || 'ACTIVE';

        // Avatar
        const avatarBox = document.getElementById('spmAvatar');
        if (s.photo_url) {
            avatarBox.innerHTML = `<img src="${s.photo_url}" style="width:100%;height:100%;object-fit:cover">`;
        } else {
            avatarBox.innerHTML = `<i class="fa-solid fa-user-graduate"></i>`;
        }

        // Contact Info
        document.getElementById('spmPhone').innerText = s.phone || '—';
        document.getElementById('spmPhoneLink').href = s.phone ? `tel:${s.phone}` : '#';
        document.getElementById('spmEmail').innerText = s.email || '—';
        document.getElementById('spmBlood').innerText = s.blood_group || '—';
        document.getElementById('spmGuardian').innerText = s.guardian_name || '—';
        document.getElementById('spmGuardianPhone').innerText = s.guardian_phone ? `(${s.guardian_phone})` : '';
        document.getElementById('spmAddress').innerText = s.address || '—';

        // Academic Info
        document.getElementById('spmCourse').innerText = s.active_course || '—';
        document.getElementById('spmBatch').innerText = s.active_batch || '—';
        document.getElementById('spmSemester').innerText = s.active_semester || '—';

        const enList = document.getElementById('spmEnrollmentList');
        if (s.enrollments && s.enrollments.length > 0) {
            enList.innerHTML = s.enrollments.map(e => `
                <div style="background:#fff;padding:6px 10px;border-radius:4px;border:1px solid #dcfce7;font-size:12px;display:flex;justify-content:space-between">
                    <span><strong>${e.course}</strong> (${e.batch})</span>
                    <span style="color:#047857;font-weight:700">${e.semester}</span>
                </div>
            `).join('');
        } else {
            enList.innerHTML = `<div style="color:#64748b;font-style:italic">কোনো এনরোলমেন্ট নেই</div>`;
        }

        // Financials
        document.getElementById('spmTotalBilled').innerText = `৳ ${s.financials.total_billed}`;
        document.getElementById('spmTotalPaid').innerText = `৳ ${s.financials.total_paid}`;
        document.getElementById('spmTotalDue').innerText = `৳ ${s.financials.total_due}`;

        const dueCard = document.getElementById('spmDueCard');
        if (s.financials.raw_due > 0) {
            dueCard.style.background = '#fff1f2';
            dueCard.style.borderColor = '#fecdd3';
            document.getElementById('spmTotalDue').style.color = '#e11d48';
        } else {
            dueCard.style.background = '#ecfdf5';
            dueCard.style.borderColor = '#a7f3d0';
            document.getElementById('spmTotalDue').style.color = '#047857';
        }

        const invTable = document.getElementById('spmInvoicesTableBody');
        if (s.financials.invoices && s.financials.invoices.length > 0) {
            invTable.innerHTML = s.financials.invoices.map(inv => {
                let badgeColor = inv.status === 'PAID' ? 'background:#dcfce7;color:#15803d' : (inv.status === 'PARTIAL' ? 'background:#fef9c3;color:#a16207' : 'background:#fee2e2;color:#b91c1c');
                return `
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:8px 12px;font-family:monospace;font-weight:700;color:#0284c7">${inv.invoice_no}</td>
                        <td style="padding:8px 12px">${inv.title} (${inv.month})</td>
                        <td style="padding:8px 12px;font-weight:700">৳ ${inv.total_amount}</td>
                        <td style="padding:8px 12px;color:#047857">৳ ${inv.paid_amount}</td>
                        <td style="padding:8px 12px;color:#b91c1c;font-weight:700">৳ ${inv.due_amount}</td>
                        <td style="padding:8px 12px"><span style="padding:2px 8px;border-radius:4px;font-size:10.5px;font-weight:700;${badgeColor}">${inv.status}</span></td>
                    </tr>
                `;
            }).join('');
        } else {
            invTable.innerHTML = `<tr><td colspan="6" style="padding:14px;text-align:center;color:#64748b">কোনো ইনভয়েস রেকর্ড পাওয়া যায়নি।</td></tr>`;
        }

        // Attendance
        document.getElementById('spmTotalClasses').innerText = s.attendance.total;
        document.getElementById('spmPresentClasses').innerText = s.attendance.present;
        document.getElementById('spmAttendancePercent').innerText = s.attendance.percentage;
        document.getElementById('spmAttendanceBar').style.width = s.attendance.percentage !== 'রেকর্ড নেই' ? s.attendance.percentage : '0%';

        // Results
        const resTable = document.getElementById('spmResultsTableBody');
        if (s.results && s.results.length > 0) {
            resTable.innerHTML = s.results.map(r => `
                <tr style="border-bottom:1px solid #f1f5f9">
                    <td style="padding:8px 12px;font-weight:700;color:#0f172a">${r.exam_name}</td>
                    <td style="padding:8px 12px;color:#0284c7;font-weight:700">${r.marks_obtained}</td>
                    <td style="padding:8px 12px">${r.total_marks}</td>
                    <td style="padding:8px 12px"><span class="badge" style="background:#e0e7ff;color:#3730a3;font-weight:700">${r.grade}</span></td>
                    <td style="padding:8px 12px;color:#64748b">${r.date}</td>
                </tr>
            `).join('');
        } else {
            resTable.innerHTML = `<tr><td colspan="5" style="padding:14px;text-align:center;color:#64748b">কোনো পরীক্ষার ফলাফল পাওয়া যায়নি।</td></tr>`;
        }

        // Admin Links
        const profLink = document.getElementById('spmAdminProfileLink');
        const accLink = document.getElementById('spmAdminAccountsLink');
        if (s.admin_urls.profile) {
            profLink.href = s.admin_urls.profile;
            profLink.style.display = 'inline-flex';
        } else {
            profLink.style.display = 'none';
        }
        if (s.admin_urls.accounts) {
            accLink.href = s.admin_urls.accounts;
            accLink.style.display = 'inline-flex';
        } else {
            accLink.style.display = 'none';
        }

        // Ticket Link Button if on Ticket Chat page
        const ticketActionBox = document.getElementById('spmTicketLinkAction');
        if (typeof window.currentTicketUuid !== 'undefined' && window.currentTicketUuid) {
            ticketActionBox.innerHTML = `
                <button type="button" onclick="linkActiveStudentToTicket('${s.student_code}')" class="btn btn-sm" style="background:#10b981;color:#fff;border:none;font-weight:700;padding:6px 14px;border-radius:6px;cursor:pointer">
                    <i class="fa-solid fa-link"></i> এই টিকিটে স্টুডেন্ট আইডি (${s.student_code}) যুক্ত করুন
                </button>
            `;
        } else {
            ticketActionBox.innerHTML = '';
        }

        body.style.display = 'block';
    })
    .catch(err => {
        loading.style.display = 'none';
        errorEl.style.display = 'block';
        errorEl.innerText = 'সার্ভারের সাথে যোগাযোগ করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।';
        console.error(err);
    });
}

function linkActiveStudentToTicket(code) {
    if (!window.currentTicketUuid) return;
    if (!confirm(`আপনি কি নিশ্চিত যে স্টুডেন্ট আইডি '${code}' এই টিকিটের সাথে যুক্ত করতে চান?`)) return;

    fetch(`/support/tickets/${window.currentTicketUuid}/link-student`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ student_code: code })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'লিংক করা সম্ভব হয়নি।');
        }
    })
    .catch(err => {
        alert('সমস্যা হয়েছে। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।');
        console.error(err);
    });
}
</script>
