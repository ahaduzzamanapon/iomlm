<x-admin-layout>
    <x-slot name="title">কোর্স পরিবর্তন ও স্থানান্তর (Course Transfers)</x-slot>

    <style>
        .ct-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px; font-family: 'Kalpurush', sans-serif; }
        .ct-title { display: flex; align-items: center; gap: 12px; font-size: 22px; font-weight: 700; color: #1e293b; }
        .ct-title i { width: 40px; height: 40px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: inline-flex; align-items: center; justify-content: center; font-size: 19px; }
        .ct-subtitle { font-size: 13px; color: #64748b; margin-top: 3px; }

        .stat-grid-ct { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 22px; }
        .stat-card-ct { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; font-family: 'Kalpurush', sans-serif; }
        .stat-icon-ct { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-val-ct  { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-lbl-ct  { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 4px; }

        .filter-card-ct { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; font-family: 'Kalpurush', sans-serif; }
        .search-wrap-ct { position: relative; flex: 1; min-width: 220px; }
        .search-wrap-ct i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .search-wrap-ct input { width: 100%; padding-left: 36px; padding-right: 12px; height: 40px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; }
        .search-wrap-ct input:focus { border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }

        .table-card-ct { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; font-family: 'Kalpurush', sans-serif; }
        .table-ct { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-ct th { background: #f8fafc; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b; padding: 13px 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table-ct td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: top; font-size: 13px; }
        .table-ct tr:last-child td { border-bottom: none; }

        .badge-pending { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #92400e; }
        .badge-payment { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #e0f2fe; color: #0369a1; }
        .badge-completed { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #dcfce7; color: #166534; }
        .badge-rejected { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #fee2e2; color: #991b1b; }

        .tab-btn-ct { padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; color: #334155; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .tab-btn-ct.active { background: #0284c7; color: #fff; border-color: #0284c7; }
    </style>

    {{-- Header --}}
    <div class="ct-header">
        <div>
            <div class="ct-title">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                কোর্স পরিবর্তন ও স্থানান্তর ব্যবস্থাপনা (Course Transfers)
            </div>
            <div class="ct-subtitle">
                শিক্ষার্থীদের কোর্স পরিবর্তনের আবেদন পর্যালোচনা, প্রযোজ্য ফি নির্ধারণ, নতুন ব্যাচ বরাদ্দ ও স্থানান্তর অনুমোদন
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:18px;font-family:'Kalpurush',sans-serif;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info" style="margin-bottom:18px;font-family:'Kalpurush',sans-serif;background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1">
            <i class="fa-solid fa-circle-info"></i> {{ session('info') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom:18px;font-family:'Kalpurush',sans-serif;background:#fef2f2;border:1px solid #fecaca;color:#991b1b">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="stat-grid-ct">
        <div class="stat-card-ct">
            <div class="stat-icon-ct" style="background:#e0f2fe;color:#0284c7">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <div class="stat-val-ct">{{ $totalCount }}</div>
                <div class="stat-lbl-ct">মোট আবেদন</div>
            </div>
        </div>
        <div class="stat-card-ct">
            <div class="stat-icon-ct" style="background:#fef3c7;color:#d97706">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <div class="stat-val-ct">{{ $pendingCount }}</div>
                <div class="stat-lbl-ct">অপেক্ষমাণ আবেদন</div>
            </div>
        </div>
        <div class="stat-card-ct">
            <div class="stat-icon-ct" style="background:#f0fdf4;color:#16a34a">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <div class="stat-val-ct">{{ $waitingPaymentCount }}</div>
                <div class="stat-lbl-ct">অনুমোদিত - ফি বকেয়া</div>
            </div>
        </div>
        <div class="stat-card-ct">
            <div class="stat-icon-ct" style="background:#ecfdf5;color:#059669">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-val-ct">{{ $completedCount }}</div>
                <div class="stat-lbl-ct">স্থানান্তর সম্পন্ন</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card-ct">
        <div style="display:flex;gap:6px;flex-wrap:wrap">
            <a href="{{ route('admin.course-transfers.index') }}" class="tab-btn-ct {{ empty($statusFilter) ? 'active' : '' }}">
                সকল ({{ $totalCount }})
            </a>
            <a href="{{ route('admin.course-transfers.index', ['status' => 'PENDING', 'to_course_id' => $courseFilter, 'search' => $search]) }}" class="tab-btn-ct {{ $statusFilter === 'PENDING' ? 'active' : '' }}">
                <i class="fa-solid fa-clock"></i> অপেক্ষমাণ ({{ $pendingCount }})
            </a>
            <a href="{{ route('admin.course-transfers.index', ['status' => 'APPROVED_PENDING_PAYMENT', 'to_course_id' => $courseFilter, 'search' => $search]) }}" class="tab-btn-ct {{ $statusFilter === 'APPROVED_PENDING_PAYMENT' ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-wave"></i> ফি বকেয়া ({{ $waitingPaymentCount }})
            </a>
            <a href="{{ route('admin.course-transfers.index', ['status' => 'COMPLETED', 'to_course_id' => $courseFilter, 'search' => $search]) }}" class="tab-btn-ct {{ $statusFilter === 'COMPLETED' ? 'active' : '' }}">
                <i class="fa-solid fa-check-double"></i> সম্পন্ন ({{ $completedCount }})
            </a>
            <a href="{{ route('admin.course-transfers.index', ['status' => 'REJECTED', 'to_course_id' => $courseFilter, 'search' => $search]) }}" class="tab-btn-ct {{ $statusFilter === 'REJECTED' ? 'active' : '' }}">
                বাতিলকৃত ({{ $rejectedCount }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.course-transfers.index') }}" style="display:flex;gap:8px;flex:1;min-width:280px">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif
            <select name="to_course_id" class="form-control" style="width:200px" onchange="this.form.submit()">
                <option value="">সকল টার্গেট কোর্স</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ $courseFilter == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <div class="search-wrap-ct">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="শিক্ষার্থীর নাম, আইডি বা মোবাইল...">
            </div>
            <button type="submit" class="btn btn-outline btn-sm">খুঁজুন</button>
        </form>
    </div>

    {{-- Main Table --}}
    <div class="table-card-ct">
        <table class="table-ct">
            <thead>
                <tr>
                    <th>শিক্ষার্থী (Student)</th>
                    <th>বর্তমান কোর্স (From Course)</th>
                    <th>প্রার্থিত কোর্স (To Course)</th>
                    <th>আবেদনের কারণ</th>
                    <th>ট্রান্সফার ফি ও ইনভয়েস</th>
                    <th>স্ট্যাটাস</th>
                    <th style="text-align:right">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $tr)
                <tr>
                    <td>
                        <div style="font-weight:700;color:#0f172a">{{ $tr->student?->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#64748b;font-family:monospace">{{ $tr->student?->student_code ?? 'ID: N/A' }}</div>
                        <div style="font-size:12px;color:#0284c7"><i class="fa-solid fa-phone"></i> {{ $tr->student?->phone ?? '—' }}</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:3px">আবেদন: {{ $tr->created_at->format('d M Y, h:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#334155">{{ $tr->fromCourse?->name ?? '—' }}</div>
                        @if($tr->fromBatch)
                            <div style="font-size:12px;color:#64748b"><i class="fa-solid fa-layer-group"></i> ব্যাচ: {{ $tr->fromBatch->name }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700;color:#0369a1">{{ $tr->toCourse?->name ?? '—' }}</div>
                        @if($tr->toBatch)
                            <div style="font-size:12px;color:#059669;font-weight:600">
                                <i class="fa-solid fa-arrow-right"></i> ব্যাচ: {{ $tr->toBatch->name }}
                            </div>
                        @else
                            <div style="font-size:12px;color:#d97706;font-style:italic">ব্যাচ নির্ধারিত হয়নি</div>
                        @endif
                    </td>
                    <td style="max-width:240px">
                        <div style="font-size:12px;color:#334155;background:#f8fafc;padding:8px 10px;border-radius:6px;border:1px solid #f1f5f9;line-height:1.4">
                            {{ $tr->reason ?: 'কোনো কারণ উল্লেখ নেই' }}
                        </div>
                        @if($tr->admin_notes)
                            <div style="font-size:11px;color:#0284c7;margin-top:4px">
                                <strong>নোট:</strong> {{ $tr->admin_notes }}
                            </div>
                        @endif
                        @if($tr->rejection_reason)
                            <div style="font-size:11px;color:#dc2626;margin-top:4px">
                                <strong>বাতিলের কারণ:</strong> {{ $tr->rejection_reason }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:14px;font-weight:800;color:#0f172a">
                            ৳{{ number_format($tr->transfer_fee, 2) }}
                        </div>
                        @if($tr->invoice)
                            <div style="margin-top:3px">
                                <span style="font-size:11px;padding:2px 6px;border-radius:4px;font-weight:700;background:{{ $tr->invoice->status === 'PAID' ? '#dcfce7;color:#166534' : '#fef3c7;color:#92400e' }}">
                                    {{ $tr->invoice->status === 'PAID' ? 'ফি পরিশোধিত' : 'বকেয়া' }}
                                </span>
                                <div style="font-size:11px;color:#64748b;font-family:monospace;margin-top:2px">
                                    {{ $tr->invoice->invoice_no }}
                                </div>
                            </div>
                        @else
                            <span style="font-size:11px;color:#94a3b8">—</span>
                        @endif
                    </td>
                    <td>
                        @if($tr->status === 'PENDING')
                            <span class="badge-pending"><i class="fa-solid fa-clock"></i> অপেক্ষমাণ</span>
                        @elseif($tr->status === 'APPROVED_PENDING_PAYMENT')
                            <span class="badge-payment"><i class="fa-solid fa-hourglass-start"></i> ফি বকেয়া</span>
                        @elseif($tr->status === 'COMPLETED')
                            <span class="badge-completed"><i class="fa-solid fa-check-double"></i> স্থানান্তরিত</span>
                            <div style="font-size:10px;color:#15803d;margin-top:2px">{{ $tr->completed_at?->format('d M Y') }}</div>
                        @elseif($tr->status === 'REJECTED')
                            <span class="badge-rejected"><i class="fa-solid fa-xmark"></i> বাতিলকৃত</span>
                        @else
                            <span class="badge badge-secondary no-dot">{{ $tr->status }}</span>
                        @endif
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        @if($tr->status === 'PENDING')
                            <button class="btn btn-primary btn-sm" onclick='openApproveModal(@json($tr))' title="অনুমোদন ও ফি নির্ধারণ">
                                <i class="fa-solid fa-check"></i> অনুমোদন
                            </button>
                            <button class="btn btn-outline btn-sm" style="color:#dc2626;border-color:#fecaca" onclick='openRejectModal(@json($tr))' title="আবেদন বাতিল">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @elseif($tr->status === 'APPROVED_PENDING_PAYMENT')
                            <form method="POST" action="{{ route('admin.course-transfers.mark-paid', $tr) }}" style="display:inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে শিক্ষার্থী ফি প্রদান করেছেন এবং তাকে অবিলম্বে নতুন কোর্সে স্থানান্তর করতে চান?');">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="background:#059669;color:#fff;font-weight:600" title="ম্যানুয়াল ক্যাশ কালেকশন ও তাৎক্ষণিক কার্যকর">
                                    <i class="fa-solid fa-money-bill-check"></i> মার্ক পেইড ও স্থানান্তর
                                </button>
                            </form>
                            <button class="btn btn-outline btn-sm" style="color:#dc2626;border-color:#fecaca" onclick='openRejectModal(@json($tr))' title="বাতিল">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @elseif($tr->status === 'COMPLETED')
                            <span style="font-size:12px;color:#059669;font-weight:700">
                                <i class="fa-solid fa-circle-check"></i> সম্পূর্ণ
                            </span>
                        @else
                            <span style="font-size:12px;color:#94a3b8">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px 20px;color:#94a3b8">
                        <i class="fa-solid fa-arrow-right-arrow-left" style="font-size:32px;color:#cbd5e1;margin-bottom:8px;display:block"></i>
                        কোনো কোর্স পরিবর্তনের আবেদন পাওয়া যায়নি।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($transfers->hasPages())
            <div style="padding:16px 20px;border-top:1px solid #e2e8f0">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

    {{-- ── Approve Modal ── --}}
    <div class="modal-overlay" id="approveModal">
        <div class="modal" style="max-width:540px">
            <div class="modal-header">
                <span class="modal-title" style="font-family:'Kalpurush',sans-serif">
                    <i class="fa-solid fa-circle-check" style="color:#0284c7"></i> কোর্স পরিবর্তন অনুমোদন ও ফি নির্ধারণ
                </span>
                <button class="modal-close" onclick="closeModal('approveModal')">&times;</button>
            </div>
            <form method="POST" id="approveForm">
                @csrf
                <div class="modal-body" style="font-family:'Kalpurush',sans-serif">
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px 14px;margin-bottom:16px">
                        <div style="font-size:12px;color:#0369a1;font-weight:700">শিক্ষার্থীর তথ্য:</div>
                        <div style="font-size:14px;font-weight:700;color:#0f172a" id="modalStudentName"></div>
                        <div style="font-size:12px;color:#334155;margin-top:4px">
                            <span id="modalFromCourse"></span> &rarr; <strong id="modalToCourse" style="color:#0284c7"></strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>টার্গেট কোর্সের ব্যাচ বরাদ্দ <span class="required">*</span></label>
                        <select name="to_batch_id" id="modalTargetBatch" class="form-control" required>
                            <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                        </select>
                        <small style="color:#64748b;font-size:12px">শিক্ষার্থীকে নতুন কোর্সের যে ব্যাচে অন্তর্ভুক্ত করা হবে।</small>
                    </div>

                    <div class="form-group" id="modalSemesterGroup">
                        <label>শুরুর সেমিস্টার (Semester)</label>
                        <select name="to_semester_id" id="modalTargetSemester" class="form-control">
                            <option value="">-- ১ম সেমিস্টার (ডিফল্ট) --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>কোর্স স্থানান্তর ফি (Transfer Fee ৳) <span class="required">*</span></label>
                        <input type="number" name="transfer_fee" id="modalTransferFee" class="form-control" value="0.00" min="0" step="0.01" required>
                        <small style="color:#64748b;font-size:12px">
                            ফি ০ (ফ্রি) দিলে সাথে সাথে স্থানান্তর হবে; ফি ১+ টাকা দিলে শিক্ষার্থীর ইনভয়েস তৈরি হবে এবং ফি পরিশোধের পর কার্যকর হবে।
                        </small>
                    </div>

                    <div class="form-group">
                        <label>অ্যাডমিন নোট (ঐচ্ছিক)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="স্থানান্তর সংক্রান্ত বিশেষ কোনো মন্তব্য..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#0284c7">অনুমোদন নিশ্চিত করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Reject Modal ── --}}
    <div class="modal-overlay" id="rejectModal">
        <div class="modal" style="max-width:480px">
            <div class="modal-header">
                <span class="modal-title" style="font-family:'Kalpurush',sans-serif;color:#dc2626">
                    <i class="fa-solid fa-triangle-exclamation"></i> আবেদন বাতিল করুন
                </span>
                <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
            </div>
            <form method="POST" id="rejectForm">
                @csrf
                <div class="modal-body" style="font-family:'Kalpurush',sans-serif">
                    <p style="font-size:13px;color:#64748b;margin-bottom:14px">
                        আপনি কি নিশ্চিত যে <strong id="rejectStudentName"></strong>-এর কোর্স পরিবর্তনের আবেদনটি বাতিল করতে চান? বাতিলের কারণ নিচে উল্লেখ করুন:
                    </p>
                    <div class="form-group">
                        <label>বাতিলের কারণ <span class="required">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="যেমন: নতুন কোর্সে আসন খালি নেই বা ন্যূনতম শিক্ষাগত যোগ্যতা মেলেনি..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('rejectModal')">না</button>
                    <button type="submit" class="btn btn-primary" style="background:#dc2626">হ্যাঁ, বাতিল করুন</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const allCourses = @json($courses);

    function openApproveModal(tr) {
        document.getElementById('approveForm').action = '/admin/course-transfers/' + tr.id + '/approve';
        document.getElementById('modalStudentName').innerText = (tr.student ? tr.student.name : 'Student') + ' (' + (tr.student ? tr.student.student_code : '') + ')';
        document.getElementById('modalFromCourse').innerText = tr.from_course ? tr.from_course.name : '—';
        document.getElementById('modalToCourse').innerText = tr.to_course ? tr.to_course.name : '—';
        document.getElementById('modalTransferFee').value = tr.transfer_fee || 0;

        // Populate batches for the target course
        const targetCourse = allCourses.find(c => c.id === tr.to_course_id);
        const batchSelect = document.getElementById('modalTargetBatch');
        batchSelect.innerHTML = '<option value="">-- ব্যাচ নির্বাচন করুন --</option>';

        if (targetCourse && targetCourse.batches) {
            targetCourse.batches.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.text = b.name + ' (' + (b.batch_code || 'ID:' + b.id) + ')';
                if (tr.to_batch_id && tr.to_batch_id === b.id) {
                    opt.selected = true;
                }
                batchSelect.appendChild(opt);
            });
        }

        // Populate semesters if semester-based
        const semGroup = document.getElementById('modalSemesterGroup');
        const semSelect = document.getElementById('modalTargetSemester');
        semSelect.innerHTML = '<option value="">-- ১ম সেমিস্টার (ডিফল্ট) --</option>';

        if (targetCourse && targetCourse.type === 'SEMESTER_BASED' && targetCourse.semesters) {
            semGroup.style.display = 'block';
            targetCourse.semesters.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.text = s.name + ' (সেমিস্টার ' + s.sequence_no + ')';
                if (tr.to_semester_id && tr.to_semester_id === s.id) {
                    opt.selected = true;
                }
                semSelect.appendChild(opt);
            });
        } else {
            semGroup.style.display = 'none';
        }

        openModal('approveModal');
    }

    function openRejectModal(tr) {
        document.getElementById('rejectForm').action = '/admin/course-transfers/' + tr.id + '/reject';
        document.getElementById('rejectStudentName').innerText = tr.student ? tr.student.name : 'শিক্ষার্থী';
        openModal('rejectModal');
    }
    </script>
    @endpush
</x-admin-layout>
