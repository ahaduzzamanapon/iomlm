<x-admin-layout>
    <x-slot name="title">Re-Exam Appeals (পুনরায় পরীক্ষার আপিল)</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush',sans-serif">পুনরায় পরীক্ষার আপিল আবেদনসমূহ (Re-Exam Appeals)</h1>
            <p style="font-family:'Kalpurush',sans-serif">অনিবার্য কারণে পরীক্ষা ব্যাহত হওয়া শিক্ষার্থীদের পুনরায় পরীক্ষার আবেদন পর্যালোচনা ও অনুমোদন</p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;font-family:'Kalpurush',sans-serif">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;font-family:'Kalpurush',sans-serif">
            <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;font-family:'Kalpurush',sans-serif">
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'PENDING']) }}" class="btn btn-sm {{ $status === 'PENDING' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fa-regular fa-clock"></i> অপেক্ষমাণ (Pending)
        </a>
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'APPROVED']) }}" class="btn btn-sm {{ $status === 'APPROVED' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fa-solid fa-check"></i> অনুমোদিত (Approved)
        </a>
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'REJECTED']) }}" class="btn btn-sm {{ $status === 'REJECTED' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fa-solid fa-xmark"></i> বাতিল (Rejected)
        </a>
        <a href="{{ route('admin.exams.appeals.index', ['status' => 'ALL']) }}" class="btn btn-sm {{ $status === 'ALL' ? 'btn-primary' : 'btn-outline' }}">
            সকল আবেদন (All)
        </a>
    </div>

    <div class="card" style="font-family:'Kalpurush',sans-serif">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>শিক্ষার্থী</th>
                        <th>পরীক্ষা ও বিষয়</th>
                        <th style="min-width:240px">আবেদনের কারণ</th>
                        <th>জমার তারিখ</th>
                        <th>স্ট্যাটাস ও ফি</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appeals as $appeal)
                    @php
                        $exam = $appeal->exam;
                        $isExpired = $exam ? $exam->isExpired() : false;
                        $endDt = $exam ? $exam->getEffectiveEndDatetime()->format('d M Y, h:i A') : '—';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $appeal->student?->name ?? '—' }}</strong><br>
                            <span style="font-family:monospace;font-size:12px;color:#64748b">ID: {{ $appeal->student?->student_code ?? $appeal->student?->student_id ?? '—' }}</span>
                        </td>
                        <td>
                            <strong>{{ $appeal->exam?->title ?? '—' }}</strong><br>
                            <span style="font-size:12px;color:#64748b">{{ $appeal->exam?->subject?->name ?? '—' }}</span>
                            <div style="margin-top:4px">
                                @if($isExpired)
                                    <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5;font-size:10.5px;padding:2px 7px;font-weight:700">
                                        <i class="fa-solid fa-hourglass-end"></i> সময় অতিক্রান্ত (Expired: {{ $endDt }})
                                    </span>
                                @else
                                    <span class="badge" style="background:#f0fdf4;color:#15803d;border:1px solid #86efac;font-size:10.5px;padding:2px 7px;font-weight:700">
                                        <i class="fa-regular fa-clock"></i> সময় বাকি (শেষ: {{ $endDt }})
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13px;color:#1e293b;line-height:1.4">"{{ $appeal->reason }}"</div>
                            @if($appeal->admin_remarks)
                                <div style="font-size:11px;color:#64748b;margin-top:3px">
                                    <i class="fa-solid fa-comment-dots"></i> মন্তব্য: {{ $appeal->admin_remarks }}
                                </div>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:12px">
                            {{ $appeal->created_at ? $appeal->created_at->format('d M Y, h:i A') : '—' }}
                        </td>
                        <td>
                            @if($appeal->isPending())
                                <span class="badge badge-warning no-dot" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-weight:700">
                                    <i class="fa-regular fa-clock"></i> PENDING
                                </span>
                            @elseif($appeal->isApproved())
                                <span class="badge badge-success no-dot" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-weight:700">
                                    <i class="fa-solid fa-check-circle"></i> APPROVED
                                </span>
                                @if(($appeal->fee_amount ?? 0) > 0)
                                    <div style="margin-top:4px">
                                        <strong style="font-size:11px;color:#1e293b">ফি: ৳{{ number_format($appeal->fee_amount, 0) }}</strong>
                                        @if($appeal->isPaid())
                                            <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:10px;padding:1px 6px;margin-left:3px">
                                                <i class="fa-solid fa-circle-check"></i> পরিশোধিত
                                            </span>
                                        @else
                                            <span class="badge" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:10px;padding:1px 6px;margin-left:3px">
                                                <i class="fa-solid fa-clock"></i> বকেয়া (Unpaid)
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div style="font-size:10px;color:#059669;margin-top:2px">
                                        <i class="fa-solid fa-gift"></i> ফ্রি (No Fee)
                                    </div>
                                @endif
                                <div style="font-size:10px;color:#64748b;margin-top:2px">অনুমোদন: {{ $appeal->reviewer?->name ?? 'Admin' }}</div>
                            @else
                                <span class="badge badge-danger no-dot" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;font-weight:700">
                                    <i class="fa-solid fa-xmark"></i> REJECTED
                                </span>
                                <div style="font-size:10px;color:#991b1b;margin-top:2px">পর্যালোচনা: {{ $appeal->reviewer?->name ?? 'Admin' }}</div>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            @if($appeal->isPending())
                                <button type="button"
                                        class="btn btn-sm btn-primary"
                                        style="background:#059669;border-color:#047857;color:#fff;font-size:12px;font-weight:700"
                                        onclick="openApproveModal({{ $appeal->id }}, '{{ addslashes($appeal->student?->name ?? 'শিক্ষার্থী') }}', '{{ addslashes($appeal->exam?->title ?? 'পরীক্ষা') }}', {{ $isExpired ? 'true' : 'false' }}, '{{ $endDt }}')"
                                        title="অনুমোদন ও ফি নির্ধারণ করুন">
                                    <i class="fa-solid fa-check"></i> অনুমোদন (Approve)
                                </button>
                                <form method="POST" action="{{ route('admin.exams.appeals.reject', $appeal) }}" style="display:inline" onsubmit="return confirm('এই আপিলটি বাতিল করতে চান?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline" style="color:#e11d48;border-color:#fecdd3;font-size:12px" title="আপিল বাতিল করুন">
                                        <i class="fa-solid fa-xmark"></i> বাতিল
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px;color:#94a3b8">প্রক্রিয়াজাত</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:36px;color:var(--text-muted)">
                            কোনো পুনরায় পরীক্ষার আবেদন পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($appeals->hasPages())
            <div style="padding:16px">
                {{ $appeals->appends(['status' => $status])->links() }}
            </div>
        @endif
    </div>

    {{-- Approve Modal with Fee Setting --}}
    <div id="approveModal" class="modal-overlay" onclick="if(event.target===this) closeApproveModal()" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.65);z-index:9999;font-family:'Kalpurush',sans-serif">
        <div class="modal" style="background:#fff;border-radius:14px;max-width:520px;width:100%;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden">
            <div style="background:linear-gradient(135deg, #064e3b 0%, #047857 100%);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center">
                <div style="font-size:16px;font-weight:700;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-clipboard-check"></i> পুনরায় পরীক্ষার আপিল অনুমোদন
                </div>
                <button type="button" onclick="closeApproveModal()" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;line-height:1">&times;</button>
            </div>
            <form id="approveForm" method="POST" action="" style="padding:20px">
                @csrf
                <div style="margin-bottom:14px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;line-height:1.6">
                    <div><strong>শিক্ষার্থী:</strong> <span id="modalStudentName">—</span></div>
                    <div><strong>পরীক্ষা:</strong> <span id="modalExamTitle">—</span></div>
                    <div><strong>শেষ সময়:</strong> <span id="modalExamEndDt">—</span></div>
                </div>

                <div id="modalExpiredAlert" style="display:none;background:#fffbeb;border:1px solid #fde68a;padding:10px 14px;border-radius:8px;font-size:12.5px;color:#92400e;margin-bottom:14px;line-height:1.5">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#d97706"></i>
                    <strong>পরীক্ষার নির্ধারিত শেষ সময় অতিবাহিত হয়েছে:</strong> নির্ধারিত সময় পার হওয়ায় পুনরায় পরীক্ষা দিতে শিক্ষার্থীকে ফি পরিশোধ করতে হবে। অনুগ্রহ করে প্রযোজ্য ফি নির্ধারণ করুন।
                </div>

                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        আপিল / রি-এক্সাম ফি (৳ BDT) <span style="color:#ef4444">*</span>
                    </label>
                    <input type="number"
                           name="fee_amount"
                           id="modalFeeInput"
                           min="0"
                           step="1"
                           value="0"
                           class="form-control"
                           style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:15px;font-weight:700;color:#047857"
                           required>
                    <small style="display:block;font-size:11.5px;color:#64748b;margin-top:4px">
                        💡 ফি ০ (শূন্য) হলে শিক্ষার্থী সরাসরি পরীক্ষা দিতে পারবে। ফি নির্ধারিত থাকলে শিক্ষার্থী ফি পরিশোধের পর পরীক্ষা দিতে পারবে।
                    </small>
                </div>

                <div style="margin-bottom:20px">
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px">
                        অ্যাডমিন মন্তব্য / রিমার্কস (ঐচ্ছিক)
                    </label>
                    <textarea name="remarks"
                              rows="2"
                              class="form-control"
                              placeholder="অনুমোদনের শর্ত বা কোনো মন্তব্য থাকলে লিখুন..."
                              style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px"></textarea>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px">
                    <button type="button" class="btn btn-outline" onclick="closeApproveModal()" style="font-size:13px">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#047857;border-color:#064e3b;color:#fff;font-weight:700;font-size:13px">
                        <i class="fa-solid fa-check"></i> অনুমোদন নিশ্চিত করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openApproveModal(appealId, studentName, examTitle, isExpired, endDt) {
        document.getElementById('approveForm').action = "{{ url('admin/exam-appeals') }}/" + appealId + "/approve";
        document.getElementById('modalStudentName').textContent = studentName;
        document.getElementById('modalExamTitle').textContent = examTitle;
        document.getElementById('modalExamEndDt').textContent = endDt;

        const alertBox = document.getElementById('modalExpiredAlert');
        const feeInput = document.getElementById('modalFeeInput');

        if (isExpired) {
            alertBox.style.display = 'block';
            feeInput.value = 500; // Default re-exam appeal fee for expired exam
        } else {
            alertBox.style.display = 'none';
            feeInput.value = 0;
        }

        document.getElementById('approveModal').style.display = 'flex';
    }

    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }
    </script>
</x-admin-layout>
