<x-admin-layout>
    <x-slot name="title">Batches & Timelines</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush',sans-serif">ব্যাচ ব্যবস্থাপনা (Batches)</h1>
            <p style="font-family:'Kalpurush',sans-serif">কোর্স ও সেশন অনুযায়ী ব্যাচ তৈরি এবং নিয়মিত রুটিন অনুযায়ী ক্লাস সেশন পরিচালনা করুন</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addBatchModal')" style="font-family:'Kalpurush',sans-serif">
                <i class="fa-solid fa-plus" style="margin-right:6px"></i> নতুন ব্যাচ (New Batch)
            </button>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger" style="margin-bottom:18px;font-family:'Kalpurush',sans-serif;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 18px;border-radius:10px">
            <div style="font-weight:700;margin-bottom:6px;font-size:14px"><i class="fa-solid fa-triangle-exclamation"></i> ত্রুটি: ব্যাচ সংরক্ষণ করা যায়নি</div>
            <ul style="margin:0;padding-left:20px;font-size:13.5px">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter Bar: Course-wise and Session-wise Filtering --}}
    <form method="GET" action="{{ route('admin.batches.index') }}" class="card" style="padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-family:'Kalpurush',sans-serif">
        <div style="flex:1;min-width:200px;position:relative">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
            <input type="text" name="search" class="form-control" placeholder="ব্যাচের নাম বা কোড দিয়ে খুঁজুন..." value="{{ $search ?? '' }}" style="padding-left:36px;height:40px;border-radius:8px;font-family:'Kalpurush',sans-serif">
        </div>

        <div style="min-width:220px">
            <select name="course_id" class="form-control" style="height:40px;border-radius:8px;font-family:'Kalpurush',sans-serif" onchange="this.form.submit()">
                <option value="">-- সকল কোর্স (All Courses) --</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ ($courseId ?? '') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ str_replace('_', ' ', $c->type) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="min-width:200px">
            <select name="academic_year_id" class="form-control" style="height:40px;border-radius:8px;font-family:'Kalpurush',sans-serif" onchange="this.form.submit()">
                <option value="">-- সকল সেশন (All Sessions) --</option>
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}" {{ ($academicYearId ?? '') == $ay->id ? 'selected' : '' }}>
                        {{ $ay->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="min-width:140px">
            <select name="status" class="form-control" style="height:40px;border-radius:8px;font-family:'Kalpurush',sans-serif" onchange="this.form.submit()">
                <option value="">-- সকল স্ট্যাটাস --</option>
                <option value="ACTIVE" {{ ($status ?? '') === 'ACTIVE' ? 'selected' : '' }}>Active (সক্রিয়)</option>
                <option value="PLANNED" {{ ($status ?? '') === 'PLANNED' ? 'selected' : '' }}>Planned</option>
                <option value="COMPLETED" {{ ($status ?? '') === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                <option value="SUSPENDED" {{ ($status ?? '') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                <option value="CANCELLED" {{ ($status ?? '') === 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="height:40px;padding:0 16px;border-radius:8px;font-family:'Kalpurush',sans-serif">
            <i class="fa-solid fa-filter" style="margin-right:6px"></i> ফিল্টার
        </button>

        @if(!empty($courseId) || !empty($academicYearId) || !empty($status) || !empty($search))
            <a href="{{ route('admin.batches.index') }}" class="btn btn-outline" style="height:40px;padding:0 14px;border-radius:8px;color:#64748b;display:inline-flex;align-items:center;font-family:'Kalpurush',sans-serif">
                <i class="fa-solid fa-rotate-left" style="margin-right:6px"></i> রিসেট
            </a>
        @endif
    </form>

    <style>
        .table-wrapper {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            width: 100% !important;
        }
        .table-wrapper::-webkit-scrollbar {
            height: 7px;
        }
        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        tbody tr:last-child .dropdown-menu,
        tbody tr:nth-last-child(2) .dropdown-menu {
            top: auto !important;
            bottom: calc(100% + 4px) !important;
        }
        .dropdown-menu.open {
            display: block !important;
        }
    </style>

    <div class="card" style="font-family:'Kalpurush',sans-serif">
        <div style="padding:12px 18px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">
            <span style="font-weight:700;color:#334155;font-size:14px">
                <i class="fa-solid fa-layer-group" style="color:#6366f1;margin-right:6px"></i> ব্যাচ তালিকা
            </span>
            <span class="badge badge-info no-dot" style="font-size:12px">
                মোট {{ $batches->count() }}টি ব্যাচ পাওয়া গেছে
            </span>
        </div>
        <div class="table-wrapper" style="overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%;min-height:280px">
            <table style="min-width:1120px;width:100%;border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="white-space:nowrap;min-width:110px">Batch Code</th>
                        <th style="white-space:nowrap;min-width:120px">Batch Name</th>
                        <th style="min-width:180px">Course (কোর্স)</th>
                        <th style="white-space:nowrap;min-width:160px">Session (সেশন)</th>
                        <th style="white-space:nowrap;min-width:110px">Start Date</th>
                        <th style="white-space:nowrap;min-width:120px">Class Sessions</th>
                        <th style="white-space:nowrap;min-width:90px">Status</th>
                        <th style="white-space:nowrap;min-width:140px">Admission Status</th>
                        <th style="text-align:right;white-space:nowrap;min-width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr>
                        <td><span class="badge badge-active no-dot"><strong>{{ $batch->batch_code }}</strong></span></td>
                        <td class="td-primary">
                            <a href="{{ route('admin.batches.show', $batch) }}" style="font-weight:600;color:var(--blue)">{{ $batch->name }}</a>
                        </td>
                        <td>{{ $batch->course->name ?? '—' }}</td>
                        <td>
                            @if($batch->academicYear)
                                <span class="badge badge-info no-dot"><i class="fa-solid fa-calendar-days" style="margin-right:4px"></i> {{ $batch->academicYear->name }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="td-muted">
                                <strong>{{ \Carbon\Carbon::parse($batch->start_date)->format('d M Y') }}</strong>
                                @if($batch->expected_end_date)
                                    — {{ \Carbon\Carbon::parse($batch->expected_end_date)->format('d M Y') }}
                                @endif
                            </div>
                            <div style="font-size:11.5px;color:#047857;margin-top:2px" title="মেয়াদ সাইকেল">
                                <i class="fa-solid fa-calendar-days"></i> {{ $batch->duration_cycle_text }}
                            </div>
                            <div style="font-size:11px;color:#b45309;margin-top:2px" title="টিউশন ফি সাইকেল">
                                <i class="fa-solid fa-coins"></i> ফি: {{ $batch->fee_cycle_text }}
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-scheduled no-dot">{{ $batch->class_sessions_count ?? 0 }} Sessions</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ strtolower($batch->status) }}">{{ ucfirst(strtolower($batch->status)) }}</span>
                        </td>
                        <td>
                            @if($batch->is_admission_open)
                                <span class="badge badge-active no-dot"><i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:4px"></i> Admission Open</span>
                            @else
                                <span class="badge badge-secondary no-dot"><i class="fa-solid fa-circle-xmark" style="color:#ef4444;margin-right:4px"></i> Admission Closed</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="dropdown" style="display:inline-block">
                                <button class="btn btn-outline btn-sm" onclick="toggleDropdown('bact-{{ $batch->id }}')" style="gap:4px">
                                    Actions
                                </button>
                                <div class="dropdown-menu" id="bact-{{ $batch->id }}" style="right:0;min-width:165px">
                                    <a href="{{ route('admin.batches.show', $batch) }}" class="dropdown-item">
                                        <i class="fa-solid fa-eye" style="margin-right:6px"></i>
                                        View Details
                                    </a>
                                    <button class="dropdown-item" onclick="openEditBatchModal({{ $batch->id }});toggleDropdown('bact-{{ $batch->id }}')">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right:6px"></i>
                                        Edit Batch
                                    </button>
                                    <form method="POST" action="{{ route('admin.batches.generateTimeline', $batch) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="width:100%;border:none;background:none;text-align:left">
                                            Gen Sessions
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.batches.destroy', $batch) }}" onsubmit="return confirm('Delete this batch?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item danger" style="width:100%;border:none;background:none;text-align:left;color:var(--red)">
                                            <i class="fa-solid fa-trash" style="margin-right:6px"></i>
                                            Delete Batch
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted)">কোনো ব্যাচ পাওয়া যায়নি। ফিল্টার পরিবর্তন করুন অথবা নতুন ব্যাচ তৈরি করুন।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Batch Modal -->
    <div class="modal-overlay" id="addBatchModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Create New Batch</span>
                <button class="modal-close" onclick="closeModal('addBatchModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.batches.store') }}">
                @csrf
                <div class="modal-body">
                    @if(isset($errors) && $errors->any() && !old('_method'))
                        <div class="alert alert-danger" style="margin-bottom:14px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:13px;font-family:'Kalpurush',sans-serif">
                            <div style="font-weight:700;margin-bottom:4px"><i class="fa-solid fa-triangle-exclamation"></i> ব্যাচ তৈরি করা সম্ভব হয়নি:</div>
                            <ul style="margin:0;padding-left:18px">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Batch Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Batch 01 - Morning Shift" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Select Course <span class="required">*</span></label>
                        <select name="course_id" class="form-control" required>
                            <option value="">-- Choose Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ str_replace('_',' ',$c->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select name="academic_year_id" class="form-control">
                                <option value="">-- Select Year --</option>
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}" {{ old('academic_year_id') == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Batch Start Date (শুরুর তারিখ) <span class="required">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Batch End Date (সমাপ্তির তারিখ)</label>
                            <input type="date" name="expected_end_date" class="form-control" value="{{ old('expected_end_date') }}">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:10px">
                        <label class="form-check" style="cursor:pointer;font-weight:600">
                            <input type="checkbox" name="is_admission_open" value="1" checked> Open for Admission
                        </label>
                    </div>

                    <div class="alert alert-info" style="margin-top:8px">
                        <strong>Auto Sessions:</strong> After creating the batch, set up the Routine for this batch — sessions will be auto-generated daily from the routine schedule.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addBatchModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Batch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Batch Modal -->
    <div class="modal-overlay" id="editBatchModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title" id="editBatchTitle">Edit Batch</span>
                <button class="modal-close" onclick="closeModal('editBatchModal')">&times;</button>
            </div>
            <form method="POST" id="editBatchForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Batch Name <span class="required">*</span></label>
                        <input type="text" name="name" id="eb_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Select Course <span class="required">*</span></label>
                        <select name="course_id" id="eb_course_id" class="form-control" required>
                            <option value="">-- Choose Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ str_replace('_',' ',$c->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <select name="academic_year_id" id="eb_academic_year_id" class="form-control">
                                <option value="">-- Select Year --</option>
                                @foreach($academicYears as $y)
                                    <option value="{{ $y->id }}">{{ $y->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Batch Start Date (শুরুর তারিখ) <span class="required">*</span></label>
                            <input type="date" name="start_date" id="eb_start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Batch End Date (সমাপ্তির তারিখ)</label>
                            <input type="date" name="expected_end_date" id="eb_expected_end_date" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Batch Status <span class="required">*</span></label>
                        <select name="status" id="eb_status" class="form-control" required>
                            <option value="ACTIVE">Active</option>
                            <option value="COMPLETED">Completed</option>
                            <option value="SUSPENDED">Suspended</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-top:14px">
                        <label class="form-check" style="cursor:pointer;font-weight:600">
                            <input type="checkbox" name="is_admission_open" id="eb_is_admission_open" value="1"> Open for Admission
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editBatchModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Batch</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const batchesData = {
        @foreach($batches as $b)
        {{ $b->id }}: {
            name: @json($b->name),
            course_id: {{ $b->course_id }},
            academic_year_id: @json($b->academic_year_id),
            start_date: @json(\Carbon\Carbon::parse($b->start_date)->format('Y-m-d')),
            expected_end_date: @json($b->expected_end_date ? \Carbon\Carbon::parse($b->expected_end_date)->format('Y-m-d') : ''),
            status: @json($b->status),
            is_admission_open: {{ $b->is_admission_open ? 'true' : 'false' }}
        },
        @endforeach
    };

    function openEditBatchModal(id) {
        const b = batchesData[id];
        if (!b) return;
        document.getElementById('editBatchForm').action = '/admin/batches/' + id;
        document.getElementById('editBatchTitle').innerText = 'Edit Batch: ' + b.name;
        document.getElementById('eb_name').value = b.name;
        document.getElementById('eb_course_id').value = b.course_id;
        document.getElementById('eb_academic_year_id').value = b.academic_year_id || '';
        document.getElementById('eb_start_date').value = b.start_date;
        document.getElementById('eb_expected_end_date').value = b.expected_end_date || '';
        document.getElementById('eb_status').value = b.status;
        document.getElementById('eb_is_admission_open').checked = b.is_admission_open;
        openModal('editBatchModal');
    }

    @if(isset($errors) && $errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        @if(old('_method') === 'PUT')
            // Editing was in progress
        @else
            openModal('addBatchModal');
        @endif
    });
    @endif
    </script>
    @endpush
</x-admin-layout>
