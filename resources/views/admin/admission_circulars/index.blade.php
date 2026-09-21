@extends('admin.layouts.app')

@section('title', 'ভর্তি সার্কুলার তালিকা | Admission Circular List')

@push('styles')
<style>
    .circular-header-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 20px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .circular-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border: 1px solid #e2e8f0;
        font-size: 13.5px;
    }
    .circular-table th {
        background: #f8fafc;
        color: #334155;
        font-weight: 700;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        text-align: left;
        white-space: nowrap;
    }
    .circular-table td {
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .circular-table tr:hover {
        background: #f8fafc;
    }
    .status-badge-curr {
        background: #dcfce7;
        color: #15803d;
        font-weight: 700;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .status-badge-exp {
        background: #fee2e2;
        color: #b91c1c;
        font-weight: 600;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .status-badge-up {
        background: #fef3c7;
        color: #b45309;
        font-weight: 600;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
    }
    .btn-act {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        transition: opacity 0.2s;
    }
    .btn-act:hover { opacity: 0.85; }
    .btn-edit { background: #0284c7; color: #fff; }
    .btn-clone { background: #f59e0b; color: #fff; }
    .btn-del { background: #ef4444; color: #fff; }

    /* Modal styling */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(2px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .circular-modal-box {
        background: #fff;
        border-radius: 8px;
        width: 100%;
        max-width: 900px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid #cbd5e1;
    }
    .modal-tab-nav {
        display: flex;
        border-bottom: 2px solid #e2e8f0;
        background: #f8fafc;
        padding: 8px 16px 0;
        gap: 8px;
    }
    .modal-tab-btn {
        padding: 8px 16px;
        border: 1px solid transparent;
        border-bottom: none;
        background: transparent;
        font-weight: 600;
        font-size: 14px;
        color: #64748b;
        cursor: pointer;
        border-radius: 6px 6px 0 0;
    }
    .modal-tab-btn.active {
        background: #fff;
        color: #0284c7;
        border-color: #e2e8f0;
        border-bottom: 2px solid #fff;
        margin-bottom: -2px;
    }
    .tab-pane { display: none; padding: 20px; }
    .tab-pane.active { display: block; }
    .form-grid-2 {
        display: grid;
        grid-template-columns: 200px 1fr 24px;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .form-grid-label {
        font-weight: 600;
        color: #334155;
        font-size: 13.5px;
        text-align: right;
    }
    .form-grid-label .req { color: #dc2626; margin-left: 2px; }
    .info-circle {
        color: #64748b;
        font-size: 14px;
        cursor: help;
    }

    /* Batch settings table */
    .batch-settings-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .batch-settings-table th {
        background: #64748b;
        color: #fff;
        padding: 8px 12px;
        font-size: 13px;
        text-align: left;
    }
    .batch-settings-table td {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        font-size: 13px;
    }
    .batch-campus-bar {
        background: #475569;
        color: #fff;
        padding: 8px 12px;
        font-weight: 700;
        text-align: center;
        font-size: 13.5px;
        margin-bottom: 6px;
    }

    /* Switch toggle */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 68px;
        height: 28px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 8px;
        font-size: 10px;
        font-weight: 800;
        color: #fff;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    input:checked + .slider {
        background-color: #7c3aed;
    }
    input:checked + .slider:before {
        transform: translateX(40px);
    }
    .slider .txt-yes { display: none; }
    .slider .txt-no { display: block; }
    input:checked + .slider .txt-yes { display: block; }
    input:checked + .slider .txt-no { display: none; }
</style>
@endpush

@section('content')
<div class="content-wrapper" style="font-family: 'Kalpurush', sans-serif;">
    {{-- Top Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success" style="padding:12px 16px;background:#dcfce7;color:#166534;border-radius:6px;margin-bottom:16px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="padding:12px 16px;background:#fee2e2;color:#991b1b;border-radius:6px;margin-bottom:16px;">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Breadcrumb & Title --}}
    <div style="margin-bottom: 12px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 4px;">
            Admission: Admission Circular List (ভর্তি সার্কুলার ব্যবস্থাপনা)
        </h2>
        <p style="color: #64748b; font-size: 13px; margin: 0;">
            Manage academic admission sessions, student ID prefix, circular status, and program-wise batch online admission toggle.
        </p>
    </div>

    {{-- Filter Card matching Screenshot 1 --}}
    <div class="circular-header-card">
        <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:280px;">
            <i class="fa-solid fa-folder-tree" style="color:#0284c7;"></i>
            <span style="font-weight:700;color:#334155;font-size:14px;">Manage Admission Circular</span>
        </div>

        <form method="GET" action="{{ route('admin.admission-circulars.index') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <label style="font-size:13px;color:#475569;font-weight:600;">Select Semester Duration</label>
            <select name="semester_duration" class="form-control" style="width:180px;height:34px;font-size:13px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:4px;" onchange="this.form.submit()">
                <option value="">--Any Duration--</option>
                @foreach($semesters as $sem)
                    <option value="{{ $sem }}" {{ request('semester_duration') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                @endforeach
            </select>

            <select name="status" class="form-control" style="width:130px;height:34px;font-size:13px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:4px;" onchange="this.form.submit()">
                <option value="">--All Status--</option>
                <option value="Current" {{ request('status') == 'Current' ? 'selected' : '' }}>Current</option>
                <option value="Upcoming" {{ request('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="Expired" {{ request('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
            </select>

            <div style="position:relative;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." style="height:34px;width:180px;padding:4px 10px 4px 28px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:9px;top:10px;color:#94a3b8;font-size:12px;"></i>
            </div>

            <button type="submit" class="btn btn-outline" style="height:34px;padding:0 12px;font-size:13px;border-radius:4px;">
                Search
            </button>
            <a href="{{ route('admin.admission-circulars.index') }}" class="btn btn-outline" style="height:34px;padding:0 12px;font-size:13px;border-radius:4px;display:inline-flex;align-items:center;background:#0284c7;color:#fff;border-color:#0284c7;" title="Refresh">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
            <button type="button" onclick="openAddCircularModal()" class="btn btn-primary" style="height:34px;padding:0 14px;font-size:13px;border-radius:4px;background:#16a34a;border-color:#16a34a;color:#fff;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </form>
    </div>

    {{-- Circular Table matching Screenshot 1 --}}
    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);overflow-x:auto;">
        <div style="padding:10px 16px;background:#e0f2fe;border-bottom:1px solid #bae6fd;font-size:13px;font-weight:700;color:#0369a1;display:flex;justify-content:space-between;align-items:center;">
            <span>Showing {{ $circulars->firstItem() ?? 0 }} to {{ $circulars->lastItem() ?? 0 }} of {{ $circulars->total() }}</span>
            <span>Total Circulars: {{ $circulars->total() }}</span>
        </div>
        <table class="circular-table">
            <thead>
                <tr>
                    <th style="width:40px;text-align:center;">SN</th>
                    <th>Name</th>
                    <th>Short Name</th>
                    <th>Session Name</th>
                    <th>Program Type</th>
                    <th>Circular Status</th>
                    <th>Exam Date</th>
                    <th>Admission Start Date</th>
                    <th style="text-align:center;">Admission Active</th>
                    <th style="width:120px;text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($circulars as $index => $circ)
                <tr>
                    <td style="text-align:center;font-weight:600;color:#64748b;">
                        {{ $circulars->firstItem() + $index }}
                    </td>
                    <td style="font-weight:700;color:#0f172a;">
                        {{ $circ->name }}
                        @if($circ->is_enabled && $circ->circular_status === 'Current')
                            <span style="display:inline-block;margin-left:6px;color:#16a34a;font-size:11px;background:#dcfce7;padding:1px 6px;border-radius:10px;font-weight:800;">
                                <i class="fa-solid fa-bolt"></i> Live Online
                            </span>
                        @endif
                    </td>
                    <td style="color:#475569;">{{ $circ->short_name ?: $circ->name }}</td>
                    <td style="color:#334155;font-weight:600;">{{ $circ->session_year }}</td>
                    <td style="color:#475569;">{{ $circ->program_type }}</td>
                    <td>
                        @if($circ->circular_status === 'Current')
                            <span class="status-badge-curr">Current</span>
                        @elseif($circ->circular_status === 'Upcoming')
                            <span class="status-badge-up">Upcoming</span>
                        @else
                            <span class="status-badge-exp">Expired</span>
                        @endif
                    </td>
                    <td style="color:#64748b;">
                        {{ $circ->exam_date ? $circ->exam_date->format('M d, Y') : 'Jan 1, 1753' }}
                    </td>
                    <td style="color:#64748b;">
                        {{ $circ->admission_start_date ? $circ->admission_start_date->format('M d, Y') : 'Jan 1, 1753' }}
                    </td>
                    <td style="text-align:center;">
                        <button type="button" onclick="toggleCircularStatus({{ $circ->id }}, this)" 
                                style="border:none;background:none;cursor:pointer;font-size:18px;color:{{ $circ->is_enabled ? '#16a34a' : '#94a3b8' }};"
                                title="{{ $circ->is_enabled ? 'Admission Enabled (ভর্তি চালু)' : 'Admission Disabled (ভর্তি বন্ধ)' }}">
                            <i class="fa-solid {{ $circ->is_enabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                        </button>
                    </td>
                    <td style="text-align:center;white-space:nowrap;">
                        <div style="display:inline-flex;gap:4px;">
                            <button type="button" class="btn-act btn-edit" title="Edit" onclick="openEditCircularModal({{ $circ->id }})">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.admission-circulars.clone', $circ) }}" style="display:inline;" onsubmit="return confirm('আপনি কি এই ভর্তি সার্কুলারটি ক্লোন/কপি করতে চান?');">
                                @csrf
                                <button type="submit" class="btn-act btn-clone" title="Clone / Copy">
                                    <i class="fa-solid fa-copy"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.admission-circulars.destroy', $circ) }}" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই সার্কুলারটি ডিলিট করতে চান?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-act btn-del" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:30px;color:#94a3b8;">
                        কোনো ভর্তি সার্কুলার পাওয়া যায়নি। "+ Add New" বাটনে ক্লিক করে নতুন সার্কুলার তৈরি করুন।
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($circulars->hasPages())
        <div style="padding:12px 16px;background:#fff;border-top:1px solid #e2e8f0;">
            {{ $circulars->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL: Add / Edit Admission Circular (General Settings & Batch Settings Tabs) --}}
<div id="circularModal" class="modal-overlay">
    <div class="circular-modal-box">
        <form id="circularForm" method="POST" action="{{ route('admin.admission-circulars.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" id="circularId" value="">

            {{-- Modal Header & Tabs --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px 0;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <h3 id="modalTitle" style="margin:0;font-size:16px;font-weight:700;color:#1e293b;">ভর্তি সার্কুলার ও সেশন তৈরি</h3>
                <button type="button" onclick="closeCircularModal()" style="border:none;background:none;font-size:20px;color:#94a3b8;cursor:pointer;">&times;</button>
            </div>

            <div class="modal-tab-nav">
                <button type="button" class="modal-tab-btn active" onclick="switchModalTab('generalTab', this)">General Settings</button>
                <button type="button" class="modal-tab-btn" onclick="switchModalTab('batchTab', this)">Batch Settings</button>
            </div>

            {{-- TAB 1: General Settings (Matching Screenshot 2) --}}
            <div id="generalTab" class="tab-pane active">
                <div class="form-grid-2">
                    <label class="form-grid-label">Semester</label>
                    <select name="semester_name" id="inp_semester_name" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 8px;">
                        <option value="">-- Select Semester --</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem }}">{{ $sem }}</option>
                        @endforeach
                    </select>
                    <i class="fa-regular fa-circle-question info-circle" title="Select the semester duration"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Session Name <span class="req">*</span></label>
                    <input type="text" name="name" id="inp_name" required placeholder="Adm Fall 2026 (Jul-Dec)" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 10px;">
                    <i class="fa-regular fa-circle-question info-circle" title="Full Admission Session Title"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Session Short Name <span class="req">*</span></label>
                    <input type="text" name="short_name" id="inp_short_name" placeholder="Adm Fall 2026 (Jul-Dec)" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 10px;">
                    <i class="fa-regular fa-circle-question info-circle" title="Short Name for quick display"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Student ID Prefix <span class="req">*</span></label>
                    <input type="text" name="student_id_prefix" id="inp_student_id_prefix" value="26" required placeholder="26" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 10px;">
                    <i class="fa-regular fa-circle-question info-circle" title="First 2 digits for generated Student IDs (Year/Session Code)"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">UGC Id Prefix</label>
                    <input type="text" name="ugc_id_prefix" id="inp_ugc_id_prefix" placeholder="Enter UGC ID Prefix" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 10px;">
                    <i class="fa-regular fa-circle-question info-circle" title="Optional UGC ID Prefix"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Student ID Suffix</label>
                    <input type="text" name="student_id_suffix" id="inp_student_id_suffix" placeholder="Enter Student ID Suffix" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 10px;">
                    <i class="fa-regular fa-circle-question info-circle" title="Optional suffix"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Session Name <span class="req">*</span></label>
                    <input type="text" name="session_year" id="inp_session_year" required value="2025-2026" placeholder="2025-2026" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 10px;">
                    <i class="fa-regular fa-circle-question info-circle" title="Academic session period (e.g. 2025-2026)"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Program Type</label>
                    <select name="program_type" id="inp_program_type" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 8px;">
                        <option value="Any">Any</option>
                        <option value="Undergraduate">Undergraduate</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Certificate">Certificate</option>
                    </select>
                    <i class="fa-regular fa-circle-question info-circle" title="Target program type"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Circular Status</label>
                    <select name="circular_status" id="inp_circular_status" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 8px;">
                        <option value="Current">Current</option>
                        <option value="Upcoming">Upcoming</option>
                        <option value="Expired">Expired</option>
                    </select>
                    <i class="fa-regular fa-circle-question info-circle" title="Status of this circular"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Is Enable</label>
                    <div>
                        <input type="checkbox" name="is_enabled" id="inp_is_enabled" value="1" checked style="width:18px;height:18px;accent-color:#0284c7;cursor:pointer;">
                        <span style="font-size:13px;color:#475569;margin-left:6px;">Master online admission open for this session</span>
                    </div>
                    <i class="fa-regular fa-circle-question info-circle" title="Check to enable admission for this circular"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Is Enable Program Wise Batch Map</label>
                    <div>
                        <input type="checkbox" name="is_program_batch_map_enabled" id="inp_is_program_batch_map_enabled" value="1" checked style="width:18px;height:18px;accent-color:#0284c7;cursor:pointer;">
                        <span style="font-size:13px;color:#475569;margin-left:6px;">Enable program &amp; batch restrictions from Batch Settings tab</span>
                    </div>
                    <i class="fa-regular fa-circle-question info-circle" title="Filter online courses according to Batch Settings"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Remark</label>
                    <input type="text" name="remark" id="inp_remark" placeholder="Enter Remark" class="form-control" style="height:36px;border:1px solid #cbd5e1;border-radius:4px;padding:4px 10px;">
                    <i class="fa-regular fa-circle-question info-circle" title="Internal admin remarks"></i>
                </div>

                <div class="form-grid-2">
                    <label class="form-grid-label">Admission Dates</label>
                    <div style="display:flex;gap:10px;">
                        <input type="date" name="admission_start_date" id="inp_admission_start_date" class="form-control" style="height:36px;flex:1;border:1px solid #cbd5e1;border-radius:4px;padding:4px 8px;" title="Start Date">
                        <input type="date" name="admission_end_date" id="inp_admission_end_date" class="form-control" style="height:36px;flex:1;border:1px solid #cbd5e1;border-radius:4px;padding:4px 8px;" title="End Date">
                    </div>
                    <i class="fa-regular fa-circle-question info-circle" title="Online admission start and deadline"></i>
                </div>
            </div>

            {{-- TAB 2: Batch Settings (Matching Screenshot 3) --}}
            <div id="batchTab" class="tab-pane">
                <div class="batch-campus-bar">Main Campus</div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="batch-settings-table">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;">SN</th>
                                <th style="width:40%;">Program</th>
                                <th style="width:35%;">Batch</th>
                                <th style="width:20%;text-align:center;">Is Online Admission Enabled</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $i => $c)
                            <tr>
                                <td style="text-align:center;color:#64748b;">{{ $i + 1 }}</td>
                                <td>
                                    <div style="font-weight:700;color:#1e293b;">{{ $c->name }}</div>
                                    <div style="font-size:11px;color:#64748b;">Code: {{ $c->formatted_code }} | {{ $c->department }}</div>
                                </td>
                                <td>
                                    <select name="batch_settings[{{ $c->id }}][batch_id]" id="batch_sel_{{ $c->id }}" class="form-control" style="height:34px;font-size:13px;border:1px solid #cbd5e1;border-radius:4px;width:100%;padding:2px 8px;">
                                        <option value="">---Select One---</option>
                                        @foreach($c->batches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="batch_settings[{{ $c->id }}][campus]" value="Main Campus">
                                </td>
                                <td style="text-align:center;">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="batch_settings[{{ $c->id }}][is_enabled]" id="batch_en_{{ $c->id }}" value="1">
                                        <span class="slider">
                                            <span class="txt-yes">YES</span>
                                            <span class="txt-no">NO</span>
                                        </span>
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeCircularModal()" class="btn btn-outline" style="padding:6px 16px;">বাতিল (Cancel)</button>
                <button type="submit" class="btn btn-primary" style="padding:6px 20px;background:#0284c7;color:#fff;border-color:#0284c7;font-weight:700;">
                    <i class="fa-solid fa-check"></i> সংরক্ষণ করুন (Save)
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function switchModalTab(tabId, btn) {
        document.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }

    function openAddCircularModal() {
        document.getElementById('modalTitle').innerText = 'নতুন ভর্তি সার্কুলার তৈরি করুন (Add Admission Circular)';
        document.getElementById('circularForm').action = "{{ route('admin.admission-circulars.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('circularId').value = '';

        // Reset inputs
        document.getElementById('inp_name').value = 'Adm Fall 2026 (Jul-Dec)';
        document.getElementById('inp_short_name').value = 'Adm Fall 2026 (Jul-Dec)';
        document.getElementById('inp_semester_name').value = 'Fall 2026 (Jul-Dec)';
        document.getElementById('inp_session_year').value = '2025-2026';
        document.getElementById('inp_student_id_prefix').value = '26';
        document.getElementById('inp_ugc_id_prefix').value = '';
        document.getElementById('inp_student_id_suffix').value = '';
        document.getElementById('inp_program_type').value = 'Any';
        document.getElementById('inp_circular_status').value = 'Current';
        document.getElementById('inp_is_enabled').checked = true;
        document.getElementById('inp_is_program_batch_map_enabled').checked = true;
        document.getElementById('inp_remark').value = '';
        document.getElementById('inp_admission_start_date').value = '';
        document.getElementById('inp_admission_end_date').value = '';

        // Reset batch settings
        document.querySelectorAll('input[id^="batch_en_"]').forEach(el => el.checked = false);
        document.querySelectorAll('select[id^="batch_sel_"]').forEach(el => el.value = '');

        // Switch to first tab
        switchModalTab('generalTab', document.querySelectorAll('.modal-tab-btn')[0]);

        document.getElementById('circularModal').classList.add('active');
    }

    function openEditCircularModal(id) {
        document.getElementById('modalTitle').innerText = 'ভর্তি সার্কুলার সম্পাদনা (Edit Admission Circular)';
        document.getElementById('circularForm').action = `/admin/admission-circulars/${id}`;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('circularId').value = id;

        fetch(`/admin/admission-circulars/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('inp_name').value = data.name || '';
                document.getElementById('inp_short_name').value = data.short_name || '';
                document.getElementById('inp_semester_name').value = data.semester_name || '';
                document.getElementById('inp_session_year').value = data.session_year || '';
                document.getElementById('inp_student_id_prefix').value = data.student_id_prefix || '26';
                document.getElementById('inp_ugc_id_prefix').value = data.ugc_id_prefix || '';
                document.getElementById('inp_student_id_suffix').value = data.student_id_suffix || '';
                document.getElementById('inp_program_type').value = data.program_type || 'Any';
                document.getElementById('inp_circular_status').value = data.circular_status || 'Current';
                document.getElementById('inp_is_enabled').checked = Boolean(data.is_enabled);
                document.getElementById('inp_is_program_batch_map_enabled').checked = Boolean(data.is_program_batch_map_enabled);
                document.getElementById('inp_remark').value = data.remark || '';
                if(data.admission_start_date) {
                    document.getElementById('inp_admission_start_date').value = data.admission_start_date.substring(0, 10);
                }
                if(data.admission_end_date) {
                    document.getElementById('inp_admission_end_date').value = data.admission_end_date.substring(0, 10);
                }

                // Reset all batch inputs first
                document.querySelectorAll('input[id^="batch_en_"]').forEach(el => el.checked = false);
                document.querySelectorAll('select[id^="batch_sel_"]').forEach(el => el.value = '');

                // Fill batches
                if (data.circular_batches && data.circular_batches.length > 0) {
                    data.circular_batches.forEach(cb => {
                        const batchSel = document.getElementById(`batch_sel_${cb.course_id}`);
                        const batchEn = document.getElementById(`batch_en_${cb.course_id}`);
                        if (batchSel && cb.batch_id) batchSel.value = cb.batch_id;
                        if (batchEn) batchEn.checked = Boolean(cb.is_online_admission_enabled);
                    });
                }

                switchModalTab('generalTab', document.querySelectorAll('.modal-tab-btn')[0]);
                document.getElementById('circularModal').classList.add('active');
            })
            .catch(err => {
                alert('ডেটা লোড করতে সমস্যা হয়েছে: ' + err);
            });
    }

    function closeCircularModal() {
        document.getElementById('circularModal').classList.remove('active');
    }

    function toggleCircularStatus(id, btn) {
        fetch(`/admin/admission-circulars/${id}/toggle`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const icon = btn.querySelector('i');
                if (data.is_enabled) {
                    btn.style.color = '#16a34a';
                    icon.className = 'fa-solid fa-toggle-on';
                    btn.title = 'Admission Enabled (ভর্তি চালু)';
                } else {
                    btn.style.color = '#94a3b8';
                    icon.className = 'fa-solid fa-toggle-off';
                    btn.title = 'Admission Disabled (ভর্তি বন্ধ)';
                }
            }
        })
        .catch(err => alert('স্ট্যাটাস পরিবর্তন ব্যর্থ হয়েছে: ' + err));
    }
</script>
@endpush
@endsection
