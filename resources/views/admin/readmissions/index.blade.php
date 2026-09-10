<x-admin-layout>
    <x-slot name="title">রি-এডমিশন ব্যবস্থাপনা (Re-admission Engine)</x-slot>

    <style>
        .ra-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 14px; font-family: 'Kalpurush', sans-serif; }
        .ra-title { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 700; color: #1e293b; }
        .ra-title i { width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
        .ra-subtitle { font-size: 13px; color: #64748b; margin-top: 3px; }
        .ra-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

        .btn-detect { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #fff; border: none; border-radius: 8px; padding: 9px 16px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; text-decoration: none; }
        .btn-detect:hover { background: #3730a3; color: #fff; }

        .stat-grid-ra { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 22px; }
        .stat-card-ra { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; font-family: 'Kalpurush', sans-serif; }
        .stat-icon-ra { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-val-ra  { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-lbl-ra  { font-size: 12px; color: #64748b; font-weight: 600; margin-top: 4px; }

        .filter-card-ra { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; font-family: 'Kalpurush', sans-serif; }
        .search-wrap-ra { position: relative; flex: 1; min-width: 220px; }
        .search-wrap-ra i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .search-wrap-ra input { width: 100%; padding-left: 36px; padding-right: 12px; height: 40px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; }
        .search-wrap-ra input:focus { border-color: #047857; box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.15); }

        .table-card-ra { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; font-family: 'Kalpurush', sans-serif; }
        .table-ra { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-ra th { background: #f8fafc; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b; padding: 13px 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table-ra td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: top; font-size: 13px; }
        .table-ra tr:last-child td { border-bottom: none; }

        .badge-fail-count { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #fee2e2; color: #991b1b; }
        .badge-pending    { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #92400e; }
        .badge-approved   { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #dcfce7; color: #166534; }
        .badge-retake     { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 14px; font-size: 11px; font-weight: 700; background: #e0e7ff; color: #3730a3; }

        .tab-btn-ra { padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; color: #334155; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .tab-btn-ra.active { background: #047857; color: #fff; border-color: #047857; }
    </style>

    {{-- Page Header --}}
    <div class="ra-header">
        <div>
            <div class="ra-title">
                <i class="fa-solid fa-user-clock"></i>
                রি-এডমিশন ব্যবস্থাপনা (Re-admission Engine)
            </div>
            <div class="ra-subtitle">
                সেমিস্টারে ২টির বেশি বিষয়ে অনুত্তীর্ণ (Failed &gt; 2 Subjects) শিক্ষার্থীদের রি-এডমিশন, ব্যাচ পরিবর্তন ও রিটেক কনটিনিউ সিদ্ধান্ত
            </div>
        </div>
        <div class="ra-actions">
            <form method="POST" action="{{ route('admin.readmissions.auto-detect') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-detect" title="ফেল করা শিক্ষার্থীদের রি-এডমিশন তালিকায় সনাক্ত করুন">
                    <i class="fa-solid fa-radar"></i> অটো-ডিটেক্ট ফেল শিক্ষার্থী (&gt;২ বিষয়)
                </button>
            </form>
            <button class="btn btn-primary" onclick="openModal('addReadmissionModal')">
                <i class="fa-solid fa-plus"></i> নতুন রি-এডমিশন এন্ট্রি
            </button>
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

    {{-- Summary Cards --}}
    <div class="stat-grid-ra">
        <div class="stat-card-ra">
            <div class="stat-icon-ra" style="background:#fee2e2;color:#dc2626">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="stat-val-ra">{{ $totalCount }}</div>
                <div class="stat-lbl-ra">মোট রি-এডমিশন তালিকাভুক্ত</div>
            </div>
        </div>
        <div class="stat-card-ra">
            <div class="stat-icon-ra" style="background:#fef3c7;color:#d97706">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <div class="stat-val-ra">{{ $pendingCount }}</div>
                <div class="stat-lbl-ra">সিদ্ধান্ত অপেক্ষমান</div>
            </div>
        </div>
        <div class="stat-card-ra">
            <div class="stat-icon-ra" style="background:#dcfce7;color:#15803d">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div class="stat-val-ra">{{ $approvedCount }}</div>
                <div class="stat-lbl-ra">অনুমোদিত (নতুন ব্যাচ স্থানান্তরিত)</div>
            </div>
        </div>
        <div class="stat-card-ra">
            <div class="stat-icon-ra" style="background:#e0e7ff;color:#4338ca">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div>
                <div class="stat-val-ra">{{ $retakeCount }}</div>
                <div class="stat-lbl-ra">রিটেক নিয়ে কন্টিনিউ</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.readmissions.index') }}" class="filter-card-ra">
        <div class="search-wrap-ra">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" placeholder="শিক্ষার্থীর নাম, রোল বা মোবাইল দিয়ে খুঁজুন..." value="{{ $search }}">
        </div>

        <select name="course_id" class="form-control" style="width:220px;height:40px;border-radius:8px;font-size:13px" onchange="this.form.submit()">
            <option value="">সকল কোর্স</option>
            @foreach($courses as $c)
                <option value="{{ $c->id }}" {{ $courseFilter == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>

        <div style="display:flex;gap:6px;flex-wrap:wrap">
            <a href="{{ route('admin.readmissions.index', array_merge(request()->except('status'), ['status' => ''])) }}"
               class="tab-btn-ra {{ !$statusFilter ? 'active' : '' }}">সব</a>
            <a href="{{ route('admin.readmissions.index', array_merge(request()->except('status'), ['status' => 'PENDING'])) }}"
               class="tab-btn-ra {{ $statusFilter === 'PENDING' ? 'active' : '' }}">অপেক্ষমান ({{ $pendingCount }})</a>
            <a href="{{ route('admin.readmissions.index', array_merge(request()->except('status'), ['status' => 'APPROVED'])) }}"
               class="tab-btn-ra {{ $statusFilter === 'APPROVED' ? 'active' : '' }}">অনুমোদিত ({{ $approvedCount }})</a>
            <a href="{{ route('admin.readmissions.index', array_merge(request()->except('status'), ['status' => 'CONTINUED_WITH_RETAKE'])) }}"
               class="tab-btn-ra {{ $statusFilter === 'CONTINUED_WITH_RETAKE' ? 'active' : '' }}">রিটেক অনুমতি ({{ $retakeCount }})</a>
        </div>

        <button type="submit" class="btn btn-primary" style="height:40px;padding:0 16px">ফিল্টার</button>
        @if($search || $courseFilter || $statusFilter)
            <a href="{{ route('admin.readmissions.index') }}" class="btn btn-outline" style="height:40px;display:inline-flex;align-items:center">রিসেট</a>
        @endif
    </form>

    {{-- Re-admissions Table --}}
    <div class="table-card-ra">
        <table class="table-ra">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>শিক্ষার্থী (Student)</th>
                    <th>কোর্স ও সেমিস্টার</th>
                    <th>ফেল করা বিষয়সমূহ</th>
                    <th>ব্যাচ পরিবর্তন</th>
                    <th>রি-এডমিশন ফি</th>
                    <th>স্ট্যাটাস</th>
                    <th style="text-align:right">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($readmissions as $ra)
                <tr>
                    <td style="color:#64748b;font-weight:700">{{ $ra->id }}</td>
                    <td>
                        <strong>{{ $ra->student->name ?? '—' }}</strong><br>
                        <span style="color:#64748b;font-size:11px">
                            রোল: {{ $ra->student->student_code ?? 'N/A' }} | ফোন: {{ $ra->student->phone ?? '—' }}
                        </span>
                        @if($ra->fromBatch)
                            <div style="font-size:11px;color:#047857;margin-top:2px">
                                বর্তমান ব্যাচ: <strong>{{ $ra->fromBatch->name }}</strong>
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $ra->course->name ?? '—' }}</strong>
                        @if($ra->semester)
                            <div style="color:#64748b;font-size:12px">
                                সেমিস্টার: {{ $ra->semester->name }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge-fail-count">
                            <i class="fa-solid fa-circle-xmark"></i> {{ $ra->failed_subjects_count }}টি বিষয়ে অনুত্তীর্ণ
                        </span>
                        @if(!empty($ra->failed_subjects) && $ra->failed_subjects->isNotEmpty())
                            <div style="margin-top:5px;display:flex;gap:4px;flex-wrap:wrap">
                                @foreach($ra->failed_subjects as $fs)
                                    <span style="font-size:10px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;padding:1px 6px;border-radius:4px" title="{{ $fs->name }}">
                                        {{ $fs->code }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($ra->status === 'APPROVED' && $ra->toBatch)
                            <span style="color:#15803d;font-weight:700">
                                <i class="fa-solid fa-arrow-right"></i> {{ $ra->toBatch->name }}
                            </span>
                            <div style="font-size:11px;color:#64748b">সেমিস্টার রিপিট সক্রিয়</div>
                        @elseif($ra->status === 'CONTINUED_WITH_RETAKE')
                            <span style="color:#3730a3;font-weight:600">
                                <i class="fa-solid fa-check-double"></i> একই ব্যাচে চলমান (রিটেক)
                            </span>
                        @else
                            <span style="color:#94a3b8;font-style:italic">নতুন ব্যাচ নির্ধারণ অপেক্ষমান</span>
                        @endif
                    </td>
                    <td>
                        @if($ra->readmission_fee > 0)
                            <strong style="color:#0f172a">৳{{ number_format($ra->readmission_fee, 0) }}</strong>
                            @if($ra->invoice)
                                <div style="font-size:11px;margin-top:2px">
                                    <span class="badge badge-{{ strtolower($ra->invoice->status) }} no-dot" style="font-size:10px;padding:1px 6px">
                                        {{ $ra->invoice->status }}
                                    </span>
                                </div>
                            @endif
                        @else
                            <span style="color:#64748b;font-size:12px">নির্ধারিত হয়নি</span>
                        @endif
                    </td>
                    <td>
                        @if($ra->status === 'PENDING')
                            <span class="badge-pending"><i class="fa-solid fa-clock"></i> অপেক্ষমান</span>
                        @elseif($ra->status === 'APPROVED')
                            <span class="badge-approved"><i class="fa-solid fa-circle-check"></i> অনুমোদিত</span>
                        @elseif($ra->status === 'CONTINUED_WITH_RETAKE')
                            <span class="badge-retake"><i class="fa-solid fa-rotate-left"></i> রিটেক অনুমতি</span>
                        @else
                            <span class="badge badge-secondary no-dot">{{ $ra->status }}</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if($ra->status === 'PENDING')
                            <div style="display:inline-flex;gap:6px">
                                <button class="btn btn-sm btn-success" 
                                    onclick='openApproveReadmissionModal(@json($ra))'
                                    title="রি-এডমিশন অনুমোদন করে নতুন ব্যাচে স্থানান্তর করুন">
                                    <i class="fa-solid fa-check"></i> রি-এডমিশন ও ব্যাচ
                                </button>
                                <button class="btn btn-sm btn-outline" style="border-color:#6366f1;color:#6366f1"
                                    onclick='openContinueRetakeModal(@json($ra))'
                                    title="রি-এডমিশন বাদ দিয়ে রিটেক নিয়ে কন্টিনিউ করার অনুমতি দিন">
                                    <i class="fa-solid fa-rotate-left"></i> রিটেক অনুমতি
                                </button>
                            </div>
                        @elseif($ra->status === 'CONTINUED_WITH_RETAKE')
                            <a href="{{ route('admin.retakes.index') }}" class="btn btn-sm btn-outline" style="font-size:11px">
                                <i class="fa-solid fa-eye"></i> রিটেক তালিকা
                            </a>
                        @else
                            <span style="color:#15803d;font-size:12px;font-weight:600">✓ সম্পন্ন</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:36px;color:#64748b">
                        <i class="fa-solid fa-user-check" style="font-size:32px;color:#cbd5e1;margin-bottom:8px;display:block"></i>
                        বর্তমানে কোনো রি-এডমিশন তালিকাভুক্ত নেই। উপরে "অটো-ডিটেক্ট" বাটনে ক্লিক করে ফেল করা শিক্ষার্থীদের স্ক্যান করতে পারেন।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($readmissions, 'links'))
        <div style="margin-top:20px">{{ $readmissions->links() }}</div>
    @endif

    {{-- Modal 1: Manual New Re-admission --}}
    <div class="modal-overlay" id="addReadmissionModal">
        <div class="modal" style="max-width:600px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title"><i class="fa-solid fa-user-plus" style="color:#dc2626"></i> নতুন রি-এডমিশন এন্ট্রি যোগ করুন</span>
                <button class="modal-close" onclick="closeModal('addReadmissionModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.readmissions.store') }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>শিক্ষার্থী নির্বাচন করুন <span class="required">*</span></label>
                        <select name="student_id" class="form-control" required id="manual_student_select" onchange="onStudentSelected(this)">
                            <option value="">-- শিক্ষার্থী নির্বাচন করুন --</option>
                            @foreach($students as $st)
                                @php
                                    $actEnr = $st->enrollments->where('status', 'ACTIVE')->first();
                                @endphp
                                <option value="{{ $st->id }}" 
                                    data-course-id="{{ $actEnr?->course_id }}" 
                                    data-batch-id="{{ $actEnr?->batch_id }}"
                                    data-semester-id="{{ $actEnr?->semester_id }}">
                                    {{ $st->student_code ?? 'N/A' }} — {{ $st->name }} ({{ $actEnr?->course?->name ?? 'No Course' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>কোর্স <span class="required">*</span></label>
                            <select name="course_id" id="manual_course_select" class="form-control" required onchange="filterSemestersAndBatches(this.value)">
                                <option value="">-- কোর্স নির্বাচন করুন --</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" data-fee="{{ $c->readmission_fee ?: $c->admission_fee }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>অনুত্তীর্ণ সেমিস্টার</label>
                            <select name="semester_id" id="manual_semester_select" class="form-control">
                                <option value="">-- সেমিস্টার নির্বাচন করুন --</option>
                                @foreach($semesters as $sem)
                                    <option value="{{ $sem->id }}" data-course-id="{{ $sem->course_id }}">{{ $sem->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>বর্তমান/পূর্ববর্তী ব্যাচ</label>
                            <select name="from_batch_id" id="manual_from_batch_select" class="form-control">
                                <option value="">-- বর্তমান ব্যাচ --</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>রি-এডমিশন ফি (৳ Taka)</label>
                            <input type="number" name="readmission_fee" id="manual_fee_input" class="form-control" placeholder="e.g. 3000" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>ফেল করা বিষয়সমূহ নির্বাচন করুন</label>
                        <select name="failed_subject_ids[]" class="form-control" multiple style="height:90px">
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->code }}: {{ $sub->name }}</option>
                            @endforeach
                        </select>
                        <small style="color:var(--text-muted);font-size:11px">কন্ট্রোল (Ctrl) চেপে একাধিক বিষয় সিলেক্ট করা যাবে</small>
                    </div>

                    <div class="form-group">
                        <label>নোট / মন্তব্য</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="রি-এডমিশন সংক্রান্ত কোনো বিশেষ নোট..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addReadmissionModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">রি-এডমিশন এন্ট্রি সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal 2: Approve Re-admission & Assign Target Batch --}}
    <div class="modal-overlay" id="approveReadmissionModal">
        <div class="modal" style="max-width:540px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title"><i class="fa-solid fa-circle-check" style="color:#15803d"></i> রি-এডমিশন অনুমোদন ও ব্যাচ স্থানান্তর</span>
                <button class="modal-close" onclick="closeModal('approveReadmissionModal')">&times;</button>
            </div>
            <form method="POST" id="approveReadmissionForm">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;font-size:13px;color:#166534;line-height:1.6">
                        শিক্ষার্থী: <strong id="appr_student_name"></strong><br>
                        কোর্স: <span id="appr_course_name"></span> | অনুত্তীর্ণ সেমিস্টার: <strong id="appr_semester_name"></strong><br>
                        ফেল করা বিষয়: <span id="appr_fail_count" style="font-weight:700"></span>টি
                    </div>

                    <div class="form-group">
                        <label>নতুন যে ব্যাচে রি-এডমিশন পাবে <span class="required">*</span></label>
                        <select name="to_batch_id" id="appr_to_batch_select" class="form-control" required>
                            <option value="">-- নতুন ব্যাচ নির্বাচন করুন --</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}">{{ $b->name }} ({{ $b->course->name ?? '' }})</option>
                            @endforeach
                        </select>
                        <small style="color:var(--text-muted);font-size:12px">শিক্ষার্থী এই ব্যাচে গিয়ে উক্ত সেমিস্টার পুনরায় ক্লাস ও পরীক্ষা সম্পন্ন করবে।</small>
                    </div>

                    <div class="form-group">
                        <label>রি-এডমিশন ফি (৳ Taka) <span class="required">*</span></label>
                        <input type="number" name="readmission_fee" id="appr_fee_input" class="form-control" min="0" required placeholder="e.g. 3000">
                        <small style="color:var(--text-muted);font-size:12px">অনুমোদনের সাথে সাথে শিক্ষার্থীর নামে এই পরিমাণ টাকার ইনভয়েস তৈরি হবে।</small>
                    </div>

                    <div class="form-group">
                        <label>অনুমোদনের নোট (ঐচ্ছিক)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="অনুমোদন সংক্রান্ত নোট..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveReadmissionModal')">বাতিল</button>
                    <button type="submit" class="btn btn-success">অনুমোদন ও ইনভয়েস জেনারেট করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal 3: Continue with Retake Override --}}
    <div class="modal-overlay" id="continueRetakeModal">
        <div class="modal" style="max-width:520px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title"><i class="fa-solid fa-rotate-left" style="color:#4f46e5"></i> রিটেক নিয়ে কন্টিনিউ করার অনুমতি</span>
                <button class="modal-close" onclick="closeModal('continueRetakeModal')">&times;</button>
            </div>
            <form method="POST" id="continueRetakeForm">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:12px 16px;font-size:13px;color:#3730a3;line-height:1.6">
                        <strong>শিক্ষার্থী:</strong> <span id="cr_student_name"></span><br>
                        <strong>কোর্স:</strong> <span id="cr_course_name"></span><br>
                        <strong>ফেল করা বিষয়:</strong> <span id="cr_fail_count"></span>টি বিষয়
                    </div>

                    <p style="font-size:13px;color:#334155;line-height:1.6;margin:0">
                        ⚠️ এই শিক্ষার্থীকে রি-এডমিশন বা সেমিস্টার রিপিট না করিয়ে <strong>চলমান রাখা হবে</strong> এবং অনুত্তীর্ণ বিষয়গুলোতে <strong>রিটেক (Subject Retake)</strong> এন্ট্রি তৈরি করা হবে। শিক্ষার্থী রিটেক পরীক্ষা দিয়ে ক্রেডিট ক্লিয়ার করবে।
                    </p>

                    <div class="form-group">
                        <label>সিদ্ধান্তের কারণ / নোট (ঐচ্ছিক)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="কেন রিটেক নিয়ে কন্টিনিউ করার অনুমতি দেওয়া হলো..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('continueRetakeModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#4f46e5;border-color:#4f46e5">রিটেক অনুমতি নিশ্চিত করুন</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function filterSemestersAndBatches(courseId) {
        // Filter Semesters
        const semSelect = document.getElementById('manual_semester_select');
        Array.from(semSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!courseId || opt.getAttribute('data-course-id') === courseId) ? '' : 'none';
        });

        // Filter Batches
        const batchSelect = document.getElementById('manual_from_batch_select');
        Array.from(batchSelect.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!courseId || opt.getAttribute('data-course-id') === courseId) ? '' : 'none';
        });

        // Set default fee from course
        const courseSelect = document.getElementById('manual_course_select');
        const selectedOpt = courseSelect.options[courseSelect.selectedIndex];
        if (selectedOpt && selectedOpt.getAttribute('data-fee')) {
            document.getElementById('manual_fee_input').value = selectedOpt.getAttribute('data-fee');
        }
    }

    function onStudentSelected(sel) {
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;
        const cId = opt.getAttribute('data-course-id');
        const bId = opt.getAttribute('data-batch-id');
        const sId = opt.getAttribute('data-semester-id');

        if (cId) {
            document.getElementById('manual_course_select').value = cId;
            filterSemestersAndBatches(cId);
        }
        if (bId) document.getElementById('manual_from_batch_select').value = bId;
        if (sId) document.getElementById('manual_semester_select').value = sId;
    }

    function openApproveReadmissionModal(ra) {
        document.getElementById('approveReadmissionForm').action = '/admin/readmissions/' + ra.id + '/approve';
        document.getElementById('appr_student_name').innerText = ra.student ? ra.student.name : '—';
        document.getElementById('appr_course_name').innerText = ra.course ? ra.course.name : '—';
        document.getElementById('appr_semester_name').innerText = ra.semester ? ra.semester.name : 'সেমিস্টার';
        document.getElementById('appr_fail_count').innerText = ra.failed_subjects_count || 0;
        document.getElementById('appr_fee_input').value = ra.readmission_fee || (ra.course ? (ra.course.readmission_fee || ra.course.admission_fee) : 0) || 0;

        // Filter target batch options to only show batches of this course
        const toBatchSelect = document.getElementById('appr_to_batch_select');
        Array.from(toBatchSelect.options).forEach(opt => {
            if (!opt.value) return;
            const cId = opt.getAttribute('data-course-id');
            // Hide current batch from options
            if (ra.from_batch_id && String(ra.from_batch_id) === opt.value) {
                opt.style.display = 'none';
            } else if (!cId || String(cId) === String(ra.course_id)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
        toBatchSelect.value = '';

        openModal('approveReadmissionModal');
    }

    function openContinueRetakeModal(ra) {
        document.getElementById('continueRetakeForm').action = '/admin/readmissions/' + ra.id + '/continue-retake';
        document.getElementById('cr_student_name').innerText = ra.student ? ra.student.name : '—';
        document.getElementById('cr_course_name').innerText = ra.course ? ra.course.name : '—';
        document.getElementById('cr_fail_count').innerText = ra.failed_subjects_count || 0;

        openModal('continueRetakeModal');
    }
    </script>
    @endpush
</x-admin-layout>
