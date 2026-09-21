<x-admin-layout>
    <x-slot name="title">Program Activity Settings</x-slot>

    <style>
        .settings-nav-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 0;
            border-bottom: 2px solid #e2e8f0;
            font-family: 'Kalpurush', sans-serif;
        }
        .settings-nav-tab {
            padding: 10px 22px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            color: #475569;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s;
        }
        .settings-nav-tab:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .settings-nav-tab.active {
            background: #fff;
            color: #0f172a;
            border-top: 3px solid #ef4444;
            border-left: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            margin-bottom: -2px;
            border-bottom: 2px solid #fff;
        }

        .activity-card {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            font-family: 'Kalpurush', sans-serif;
        }
        .activity-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 18px;
            border-bottom: 2px solid #0284c7;
            background: #f8fafc;
        }
        .activity-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-add-activity {
            background: #16a34a;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .2s;
        }
        .btn-add-activity:hover {
            background: #15803d;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .activity-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .activity-table td {
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .activity-table tr:hover {
            background: #f8fafc;
        }

        .btn-edit-act {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-edit-act:hover {
            background: #1d4ed8;
        }
        .btn-del-act {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-del-act:hover {
            background: #b91c1c;
        }

        .form-label-with-tip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            color: #334155;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .tip-icon {
            color: #64748b;
            font-size: 13px;
            cursor: help;
        }
    </style>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;font-family:'Kalpurush',sans-serif">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom:16px;font-family:'Kalpurush',sans-serif">
            <ul style="margin:0;padding-left:18px">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top Navigation Tabs (matching Screenshot 2) --}}
    <div class="settings-nav-tabs">
        <a href="{{ route('admin.semesters.index') }}" class="settings-nav-tab">
            Semester Details
        </a>
        <a href="{{ route('admin.semesters.index', ['tab' => 'additional']) }}" class="settings-nav-tab">
            Additional Settings
        </a>
        <a href="{{ route('admin.program-activities.index') }}" class="settings-nav-tab active">
            Program Activity Settings
        </a>
    </div>

    {{-- Activity Card & Table (matching Screenshot 2) --}}
    <div class="activity-card">
        <div class="activity-card-header">
            <div class="activity-card-title">
                <i class="fa-solid fa-bars-staggered"></i> Manage Program Activity Settings
            </div>
            <button type="button" class="btn-add-activity" onclick="openModal('addProgramActivityModal')">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>

        <div style="overflow-x:auto">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th style="width:50px;text-align:center">SN</th>
                        <th>Program Name</th>
                        <th style="width:130px">Batch No</th>
                        <th style="width:130px">Starting Month</th>
                        <th style="width:120px">Start Date</th>
                        <th style="width:120px">End Date</th>
                        <th style="width:140px;text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $index => $act)
                    <tr>
                        <td style="text-align:center;font-weight:700;color:#64748b">{{ $index + 1 }}</td>
                        <td style="font-weight:600;color:#0f172a">
                            {{ $act->course ? $act->course->name : 'Any Program' }}
                        </td>
                        <td>
                            <span class="badge badge-secondary no-dot" style="font-size:12px">
                                {{ $act->batch ? $act->batch->name : 'Any' }}
                            </span>
                        </td>
                        <td style="font-weight:600;color:#0284c7">{{ $act->starting_month }}</td>
                        <td>{{ $act->start_date ? $act->start_date->format('d-m-Y') : '—' }}</td>
                        <td>{{ $act->end_date ? $act->end_date->format('d-m-Y') : '—' }}</td>
                        <td style="text-align:center">
                            <button type="button" class="btn-edit-act" onclick="openModal('editProgramActivityModal_{{ $act->id }}')">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <form method="POST" action="{{ route('admin.program-activities.destroy', $act) }}" style="display:inline" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই প্রোগ্রাম অ্যাক্টিভিটি কনফিগারেশনটি মুছে ফেলতে চান?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del-act">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal (matching Screenshot 3) -->
                    <div class="modal-overlay" id="editProgramActivityModal_{{ $act->id }}">
                        <div class="modal" style="max-width:560px;font-family:'Kalpurush',sans-serif">
                            <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px">
                                <span class="modal-title" style="font-size:16px;font-weight:700;color:#1e293b">Add/Edit Program Activity</span>
                                <button type="button" class="modal-close" onclick="closeModal('editProgramActivityModal_{{ $act->id }}')">&times;</button>
                            </div>
                            <form method="POST" action="{{ route('admin.program-activities.update', $act) }}">
                                @csrf @method('PUT')
                                <div class="modal-body" style="padding:20px;display:flex;flex-direction:column;gap:14px">
                                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                                        <label style="font-weight:700;color:#1e293b;text-align:right">Semester</label>
                                        <input type="text" name="semester_name" class="form-control" value="{{ $act->semester_name }}" required>
                                        <i class="fa-solid fa-circle-question tip-icon" title="সেমিস্টার বা সেশনের নাম"></i>
                                    </div>

                                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                                        <label style="font-weight:700;color:#1e293b;text-align:right">Program</label>
                                        <select name="course_id" class="form-control">
                                            <option value="">---Any Program---</option>
                                            @foreach($courses as $c)
                                                <option value="{{ $c->id }}" {{ $act->course_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fa-solid fa-circle-question tip-icon" title="কোর্স নির্বাচন করুন"></i>
                                    </div>

                                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                                        <label style="font-weight:700;color:#1e293b;text-align:right">Dept Batch</label>
                                        <select name="batch_id" class="form-control">
                                            <option value="">---Any Batch---</option>
                                            @foreach($batches as $b)
                                                <option value="{{ $b->id }}" {{ $act->batch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fa-solid fa-circle-question tip-icon" title="নির্দিষ্ট ব্যাচ বা Any"></i>
                                    </div>

                                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                                        <label style="font-weight:700;color:#1e293b;text-align:right">Starting Month</label>
                                        <select name="starting_month" class="form-control" required>
                                            @foreach($months as $m)
                                                <option value="{{ $m }}" {{ $act->starting_month == $m ? 'selected' : '' }}>{{ $m }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fa-solid fa-circle-question tip-icon" title="ক্লাস বা ফি শুরু হওয়ার প্রথম মাস"></i>
                                    </div>

                                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                                        <label style="font-weight:700;color:#1e293b;text-align:right">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ $act->start_date ? $act->start_date->format('Y-m-d') : '' }}">
                                        <i class="fa-solid fa-circle-question tip-icon" title="কার্যক্রম শুরুর তারিখ"></i>
                                    </div>

                                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                                        <label style="font-weight:700;color:#1e293b;text-align:right">End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ $act->end_date ? $act->end_date->format('Y-m-d') : '' }}">
                                        <i class="fa-solid fa-circle-question tip-icon" title="কার্যক্রম সমাপ্তির তারিখ"></i>
                                    </div>
                                </div>

                                <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0">
                                    <button type="submit" class="btn btn-success" style="background:#16a34a;border:none;padding:7px 18px;border-radius:5px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                                        <i class="fa-solid fa-floppy-disk"></i> Save
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProgramActivityModal_{{ $act->id }}')" style="background:#0284c7;border:none;color:#fff;padding:7px 16px;border-radius:5px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                                        <i class="fa-solid fa-xmark"></i> Close
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:#94a3b8">
                            <i class="fa-solid fa-circle-info" style="font-size:24px;margin-bottom:8px;display:block"></i>
                            কোনো প্রোগ্রাম অ্যাক্টিভিটি পাওয়া যায়নি। "Add New" বাটনে ক্লিক করে নতুন অ্যাক্টিভিটি যোগ করুন।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add New Modal (matching Screenshot 3) -->
    <div class="modal-overlay" id="addProgramActivityModal">
        <div class="modal" style="max-width:560px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:14px 20px">
                <span class="modal-title" style="font-size:16px;font-weight:700;color:#1e293b">Add/Edit Program Activity</span>
                <button type="button" class="modal-close" onclick="closeModal('addProgramActivityModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.program-activities.store') }}">
                @csrf
                <div class="modal-body" style="padding:20px;display:flex;flex-direction:column;gap:14px">
                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                        <label style="font-weight:700;color:#1e293b;text-align:right">Semester</label>
                        <input type="text" name="semester_name" class="form-control" value="Fall 2026 (Jul-Dec)" required>
                        <i class="fa-solid fa-circle-question tip-icon" title="সেমিস্টার বা সেশনের নাম"></i>
                    </div>

                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                        <label style="font-weight:700;color:#1e293b;text-align:right">Program</label>
                        <select name="course_id" class="form-control">
                            <option value="">---Any Program---</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-circle-question tip-icon" title="কোর্স নির্বাচন করুন"></i>
                    </div>

                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                        <label style="font-weight:700;color:#1e293b;text-align:right">Dept Batch</label>
                        <select name="batch_id" class="form-control">
                            <option value="">---Any Batch---</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-circle-question tip-icon" title="নির্দিষ্ট ব্যাচ বা Any"></i>
                    </div>

                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                        <label style="font-weight:700;color:#1e293b;text-align:right">Starting Month</label>
                        <select name="starting_month" class="form-control" required>
                            @foreach($months as $m)
                                <option value="{{ $m }}" {{ $m === 'September' ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-circle-question tip-icon" title="ক্লাস বা ফি শুরু হওয়ার প্রথম মাস"></i>
                    </div>

                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                        <label style="font-weight:700;color:#1e293b;text-align:right">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}">
                        <i class="fa-solid fa-circle-question tip-icon" title="কার্যক্রম শুরুর তারিখ"></i>
                    </div>

                    <div class="form-group" style="display:grid;grid-template-columns:140px 1fr 24px;align-items:center;gap:10px">
                        <label style="font-weight:700;color:#1e293b;text-align:right">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-t', strtotime('+5 months')) }}">
                        <i class="fa-solid fa-circle-question tip-icon" title="কার্যক্রম সমাপ্তির তারিখ"></i>
                    </div>
                </div>

                <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0">
                    <button type="submit" class="btn btn-success" style="background:#16a34a;border:none;padding:7px 18px;border-radius:5px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addProgramActivityModal')" style="background:#0284c7;border:none;color:#fff;padding:7px 16px;border-radius:5px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-xmark"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
