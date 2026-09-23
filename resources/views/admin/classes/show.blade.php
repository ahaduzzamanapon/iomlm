<x-admin-layout>
    <x-slot name="title">Session: {{ $class->subject?->name ?? 'Class' }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.classes.index') }}">← Back to Classes</a>
            </div>
            <h1>{{ $class->subject?->name ?? '—' }}</h1>
            <p>
                {{ $class->batch?->name ?? '—' }} &middot;
                {{ $class->session_date?->format('d M Y (D)') ?? 'Date TBA' }} &middot;
                {{ $class->routineEntry?->slot?->name ?? '' }}
                @if($class->start_time) · {{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }} @endif
            </p>
        </div>
        <div class="page-header-actions" style="gap:6px;display:flex">
            @if($class->status !== 'COMPLETED' && $class->status !== 'CANCELLED')
            <button class="btn btn-outline btn-sm" onclick="openModal('scheduleModal')">Edit Schedule</button>
            <form method="POST" action="{{ route('admin.classes.cancel', $class) }}" onsubmit="return confirm('Cancel this session?')">
                @csrf
                <button class="btn btn-outline btn-sm" style="color:#ef4444">Cancel Session</button>
            </form>
            @endif
            @if($class->status === 'SCHEDULED' || $class->status === 'RUNNING')
            <button class="btn btn-primary btn-sm" onclick="openModal('completeModal')">Mark Complete</button>
            @endif
            <a href="#attendanceSection" class="btn btn-outline btn-sm" style="color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:700">
                <i class="fa-solid fa-clipboard-user"></i> উপস্থিতি পরিবর্তন (Attendance)
            </a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

        {{-- LEFT: Session Info + Attendance --}}
        <div style="display:flex;flex-direction:column;gap:16px">

            {{-- Session Details --}}
            <div class="card">
                <div class="card-header"><span class="card-title">Session Details</span></div>
                <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px">
                    <div><div style="color:var(--text-muted);font-size:11px">Subject</div><strong>{{ $class->subject?->name ?? '—' }}</strong><br><small style="color:#64748b">{{ $class->subject?->code }}</small></div>
                    <div><div style="color:var(--text-muted);font-size:11px">Batch</div><strong>{{ $class->batch?->name ?? '—' }}</strong></div>
                    <div><div style="color:var(--text-muted);font-size:11px">Teacher</div>{{ $class->teacher?->name ?? 'Unassigned' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Slot</div>{{ $class->routineEntry?->slot?->name ?? '—' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Session Date</div>{{ $class->session_date?->format('d M Y (D)') ?? 'TBA' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Start Time</div>{{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('h:i A') : 'TBA' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Module Covered</div>{{ $class->moduleCovered?->title ?? '—' }}</div>
                    <div><div style="color:var(--text-muted);font-size:11px">Status</div>
                        @php $badge = match($class->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                        <span class="badge {{ $badge }} no-dot">{{ $class->status }}</span>
                    </div>
                    @if($class->meeting_link)
                    <div style="grid-column:span 2"><div style="color:var(--text-muted);font-size:11px">Meeting Link</div>
                        <a href="{{ $class->meeting_link }}" target="_blank" style="color:#3b82f6;font-size:12px">{{ $class->meeting_link }}</a>
                    </div>
                    @endif
                    @if($class->notes)
                    <div style="grid-column:span 2"><div style="color:var(--text-muted);font-size:11px">Notes</div>{{ $class->notes }}</div>
                    @endif
                </div>
            </div>

            {{-- Attendance Table --}}
            <div class="card" id="attendanceSection" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
                @php
                    $attMap        = $class->attendances->keyBy('student_id');
                    $presentCount  = $class->attendances->where('status','PRESENT')->count();
                    $absentCount   = $class->attendances->where('status','ABSENT')->count();
                    $lateCount     = $class->attendances->where('status','LATE')->count();
                    $excusedCount  = $class->attendances->where('status','EXCUSED')->count();
                    $totalStudents = $batchStudents->count();
                @endphp
                <div class="card-header" style="background:#f8fafc;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;border-bottom:1px solid #e2e8f0">
                    <div>
                        <span class="card-title" style="display:flex;align-items:center;gap:8px;font-size:15px;font-weight:800;color:#0f172a;margin:0">
                            <i class="fa-solid fa-clipboard-user" style="color:#047857;font-size:16px"></i>
                            হাজিরা ও উপস্থিতি (Student Attendance)
                        </span>
                        <div style="font-size:11.5px;color:#64748b;margin-top:2px">
                            এডমিন যেকোনো শিক্ষার্থীর উপস্থিতি পরিবর্তন করতে পারবেন। বাটনে ক্লিক করলেই স্বয়ংক্রিয়ভাবে সেভ হবে।
                        </div>
                    </div>

                    {{-- Badges & Quick Bulk Actions --}}
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span id="badgePresent" style="display:inline-flex;align-items:center;gap:4px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px">
                            <i class="fa-solid fa-circle-check" style="font-size:11px"></i> উপস্থিত: <strong id="cntPresent">{{ $presentCount }}</strong>
                        </span>
                        <span id="badgeAbsent" style="display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px">
                            <i class="fa-solid fa-circle-xmark" style="font-size:11px"></i> অনুপস্থিত: <strong id="cntAbsent">{{ $absentCount }}</strong>
                        </span>
                        <span style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px">
                            মোট: <strong>{{ $totalStudents }}</strong> জন
                        </span>

                        <div style="display:flex;gap:6px;margin-left:6px">
                            <button type="button" class="btn btn-sm" onclick="markAllAttendance('PRESENT')" 
                                    style="background:#047857;color:#fff;border:none;font-size:11px;font-weight:700;padding:5px 10px;border-radius:6px;cursor:pointer"
                                    title="সকল শিক্ষার্থীকে এক ক্লিকে উপস্থিত চিহ্নিত করুন">
                                <i class="fa-solid fa-check-double"></i> সবাই উপস্থিত
                            </button>
                            <button type="button" class="btn btn-sm" onclick="markAllAttendance('ABSENT')" 
                                    style="background:#dc2626;color:#fff;border:none;font-size:11px;font-weight:700;padding:5px 10px;border-radius:6px;cursor:pointer"
                                    title="সকল শিক্ষার্থীকে এক ক্লিকে অনুপস্থিত চিহ্নিত করুন">
                                <i class="fa-solid fa-xmark"></i> সবাই অনুপস্থিত
                            </button>
                            <button type="button" class="btn btn-sm" onclick="markAllAttendance('NONE')" 
                                    style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:11px;font-weight:700;padding:5px 9px;border-radius:6px;cursor:pointer"
                                    title="সকল উপস্থিতি খালি/রিসেট করুন">
                                <i class="fa-solid fa-rotate-left"></i> রিসেট
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Quick Filter bar --}}
                <div style="background:#fff;padding:8px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
                    <div style="position:relative;width:280px">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12px"></i>
                        <input type="text" id="attSearchInput" placeholder="শিক্ষার্থীর নাম বা রোল দিয়ে ফিল্টার..." oninput="filterAttendanceTable(this.value)"
                               style="width:100%;height:32px;padding-left:30px;padding-right:10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;font-family:'Kalpurush',sans-serif">
                    </div>
                    <div id="attLiveNotice" style="font-size:12px;font-weight:700;color:#047857;display:none;align-items:center;gap:6px">
                        <i class="fa-solid fa-circle-check"></i> <span id="attLiveNoticeText">হাজিরা সংরক্ষিত হয়েছে!</span>
                    </div>
                </div>

                @if($batchStudents->isEmpty())
                    <div style="padding:24px;text-align:center;color:var(--text-muted)">এই ব্যাচে কোনো শিক্ষার্থী ভর্তি নেই।</div>
                @else
                <form id="attendanceBulkForm" method="POST" action="{{ route('admin.classes.updateAttendance', $class) }}">
                    @csrf
                    <div class="table-wrapper" style="margin:0;max-height:550px;overflow-y:auto">
                        <table style="width:100%;border-collapse:collapse" id="attendanceTable">
                            <thead style="position:sticky;top:0;background:#f8fafc;z-index:2;box-shadow:0 1px 2px rgba(0,0,0,0.06)">
                                <tr>
                                    <th style="width:40px;text-align:center;padding:10px 8px;font-size:12px">#</th>
                                    <th style="padding:10px 12px;font-size:12px">শিক্ষার্থীর নাম (Student)</th>
                                    <th style="width:130px;padding:10px 8px;font-size:12px">কোড / আইডি</th>
                                    <th style="padding:10px 12px;font-size:12px;text-align:left">উপস্থিতি পরিবর্তন (Mark Attendance)</th>
                                    <th style="width:110px;text-align:center;padding:10px 8px;font-size:12px">বর্তমান অবস্থা</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batchStudents as $idx => $en)
                                @php
                                    $att = $attMap->get($en->student_id);
                                    $statusVal = $att?->status ?? 'NONE';
                                @endphp
                                <tr class="att-student-row" data-name="{{ strtolower($en->student->name) }}" data-code="{{ strtolower($en->student->student_code ?? '') }}" style="border-bottom:1px solid #f1f5f9;transition:background 0.15s">
                                    <td style="text-align:center;color:#64748b;font-size:12px;padding:10px 8px">{{ $idx + 1 }}</td>
                                    <td class="td-primary" style="padding:10px 12px">
                                        <div style="font-weight:700;font-size:13px;color:#0f172a">{{ $en->student->name }}</div>
                                        <div id="rowNotice_{{ $en->student_id }}" style="font-size:10px;color:#047857;font-weight:700;display:none">✓ সংরক্ষিত হয়েছে</div>
                                    </td>
                                    <td style="font-size:12px;color:#0284c7;font-weight:600;padding:10px 8px">
                                        {{ $en->student->student_code ?? '—' }}
                                    </td>
                                    <td style="padding:8px 12px">
                                        {{-- Segmented Pill Buttons --}}
                                        <div class="att-pills-group" data-student-id="{{ $en->student_id }}" style="display:inline-flex;gap:4px;background:#f1f5f9;padding:3px;border-radius:8px">
                                            {{-- Present Button --}}
                                            <button type="button" class="att-pill-btn btn-present {{ $statusVal === 'PRESENT' ? 'active-present' : '' }}" 
                                                    onclick="selectAttStatus({{ $en->student_id }}, 'PRESENT')"
                                                    title="উপস্থিত চিহ্নিত করুন"
                                                    style="border:none;border-radius:6px;padding:4px 10px;font-size:11.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                                <i class="fa-solid fa-check"></i> উপস্থিত
                                            </button>

                                            {{-- Absent Button --}}
                                            <button type="button" class="att-pill-btn btn-absent {{ $statusVal === 'ABSENT' ? 'active-absent' : '' }}" 
                                                    onclick="selectAttStatus({{ $en->student_id }}, 'ABSENT')"
                                                    title="অনুপস্থিত চিহ্নিত করুন"
                                                    style="border:none;border-radius:6px;padding:4px 10px;font-size:11.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                                <i class="fa-solid fa-xmark"></i> অনুপস্থিত
                                            </button>

                                            {{-- Late Button --}}
                                            <button type="button" class="att-pill-btn btn-late {{ $statusVal === 'LATE' ? 'active-late' : '' }}" 
                                                    onclick="selectAttStatus({{ $en->student_id }}, 'LATE')"
                                                    title="দেরি চিহ্নিত করুন"
                                                    style="border:none;border-radius:6px;padding:4px 8px;font-size:11.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                                <i class="fa-solid fa-clock"></i> দেরি
                                            </button>

                                            {{-- Excused Button --}}
                                            <button type="button" class="att-pill-btn btn-excused {{ $statusVal === 'EXCUSED' ? 'active-excused' : '' }}" 
                                                    onclick="selectAttStatus({{ $en->student_id }}, 'EXCUSED')"
                                                    title="ছুটি চিহ্নিত করুন"
                                                    style="border:none;border-radius:6px;padding:4px 8px;font-size:11.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                                <i class="fa-solid fa-envelope-open-text"></i> ছুটি
                                            </button>

                                            {{-- Unmark / None Button --}}
                                            <button type="button" class="att-pill-btn btn-none {{ $statusVal === 'NONE' ? 'active-none' : '' }}" 
                                                    onclick="selectAttStatus({{ $en->student_id }}, 'NONE')"
                                                    title="খালি / কোনো মার্ক নেই"
                                                    style="border:none;border-radius:6px;padding:4px 7px;font-size:11.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:3px">
                                                —
                                            </button>
                                        </div>

                                        {{-- Hidden input for bulk form submit fallback --}}
                                        <input type="hidden" name="attendance[{{ $en->student_id }}]" id="attInput_{{ $en->student_id }}" value="{{ $statusVal }}">
                                    </td>
                                    <td style="text-align:center;padding:10px 8px">
                                        <span id="attBadge_{{ $en->student_id }}">
                                            @if($statusVal === 'PRESENT')
                                                <span class="badge badge-success no-dot" style="font-size:11px;font-weight:700">উপস্থিত</span>
                                            @elseif($statusVal === 'ABSENT')
                                                <span class="badge badge-danger no-dot" style="font-size:11px;font-weight:700">অনুপস্থিত</span>
                                            @elseif($statusVal === 'LATE')
                                                <span class="badge badge-warning no-dot" style="font-size:11px;font-weight:700">দেরি</span>
                                            @elseif($statusVal === 'EXCUSED')
                                                <span class="badge badge-secondary no-dot" style="font-size:11px;font-weight:700">ছুটি</span>
                                            @else
                                                <span style="color:#94a3b8;font-size:12px;font-weight:600">— খালি —</span>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Card Footer Action Bar --}}
                    <div style="padding:12px 18px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                        <div style="font-size:12px;color:#64748b">
                            <i class="fa-solid fa-info-circle" style="color:#0284c7"></i> প্রতিটি বাটনে ক্লিক করলেই পরিবর্তন সাথে সাথে লাইভ সেভ হয়ে যায়।
                        </div>
                        <button type="submit" class="btn btn-primary" style="background:#047857;border-color:#047857;padding:8px 22px;font-weight:800;border-radius:8px">
                            <i class="fa-solid fa-floppy-disk"></i> সকল পরিবর্তন সেভ করুন (Save All)
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>

        {{-- RIGHT: Quick Actions --}}
        <div>
            <div class="card">
                <div class="card-header"><span class="card-title">Quick Actions</span></div>
                <div style="padding:12px;display:flex;flex-direction:column;gap:8px">
                    @if(!$class->meeting_link)
                    <form method="POST" action="{{ route('admin.classes.updateSchedule', $class) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="session_date" value="{{ $class->session_date?->toDateString() ?? now()->toDateString() }}">
                        <button class="btn btn-outline" style="width:100%">Auto-Generate Meeting Link</button>
                    </form>
                    @else
                    <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-primary" style="width:100%;text-align:center">Join Class</a>
                    @endif

                    @if($class->status !== 'COMPLETED' && $class->status !== 'CANCELLED')
                    <button class="btn btn-outline" style="width:100%" onclick="openModal('scheduleModal')">Edit Date / Link</button>
                    @endif

                    <a href="#attendanceSection" class="btn btn-outline" style="width:100%;text-align:center;display:flex;align-items:center;justify-content:center;gap:6px;color:#047857;border-color:#a7f3d0;background:#ecfdf5;font-weight:700">
                        <i class="fa-solid fa-clipboard-user"></i> উপস্থিতি পরিবর্তন (Manage Attendance)
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Schedule Modal --}}
    <div class="modal-overlay" id="scheduleModal">
        <div class="modal" style="max-width:440px">
            <div class="modal-header">
                <span class="modal-title">Edit Session Schedule</span>
                <button class="modal-close" onclick="closeModal('scheduleModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.classes.updateSchedule', $class) }}">
                @csrf @method('PUT')
                <div class="modal-body" style="display:flex;flex-direction:column;gap:12px">
                    <div class="form-group">
                        <label class="form-label">Session Date</label>
                        <input type="date" name="session_date" class="form-control" value="{{ $class->session_date?->toDateString() ?? now()->toDateString() }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ $class->start_time }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-control">
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $class->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meeting Link <small style="color:#9ca3af">(blank = auto-generate)</small></label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..." value="{{ $class->meeting_link }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Module Covered (optional)</label>
                        <select name="module_covered_id" class="form-control">
                            <option value="">— Not specified —</option>
                            @foreach($modules as $mod)
                            <option value="{{ $mod->id }}" {{ $class->module_covered_id == $mod->id ? 'selected' : '' }}>{{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('scheduleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Complete Modal --}}
    <div class="modal-overlay" id="completeModal">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <span class="modal-title">Mark Class Complete</span>
                <button class="modal-close" onclick="closeModal('completeModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.classes.complete', $class) }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:12px;max-height:70vh;overflow-y:auto">
                    <div class="form-group">
                        <label class="form-label">Module Covered Today (optional)</label>
                        <select name="module_covered_id" class="form-control">
                            <option value="">— Not specified —</option>
                            @foreach($modules as $mod)
                            <option value="{{ $mod->id }}" {{ $class->module_covered_id == $mod->id ? 'selected' : '' }}>{{ $mod->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($batchStudents->count() > 0)
                    <div class="form-group">
                        <label class="form-label" style="margin-bottom:8px">Attendance</label>
                        @foreach($batchStudents as $en)
                        @php $att = $class->attendances->firstWhere('student_id', $en->student_id); @endphp
                        <div style="display:flex;gap:10px;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9">
                            <span style="flex:1;font-size:12px">{{ $en->student->name }}</span>
                            @foreach(['PRESENT','ABSENT','LATE','EXCUSED'] as $s)
                            <label style="font-size:11px;display:flex;align-items:center;gap:3px;cursor:pointer">
                                <input type="radio" name="attendance[{{ $en->student_id }}]" value="{{ $s }}" {{ ($att?->status===$s || (!$att && $s==='PRESENT')) ? 'checked' : '' }}>{{ $s }}
                            </label>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Class notes...">{{ $class->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('completeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .att-pill-btn {
        background: transparent;
        color: #64748b;
        transition: all 0.15s ease;
    }
    .att-pill-btn:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .att-pill-btn.active-present {
        background: #047857 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(4, 120, 87, 0.35);
    }
    .att-pill-btn.active-absent {
        background: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(220, 38, 38, 0.35);
    }
    .att-pill-btn.active-late {
        background: #d97706 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(217, 119, 6, 0.35);
    }
    .att-pill-btn.active-excused {
        background: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.35);
    }
    .att-pill-btn.active-none {
        background: #94a3b8 !important;
        color: #ffffff !important;
    }
    .att-student-row:hover {
        background: #f8fafc !important;
    }
    </style>

    <script>
    function selectAttStatus(studentId, status) {
        // 1. Update UI active buttons
        const group = document.querySelector(`.att-pills-group[data-student-id="${studentId}"]`);
        if (group) {
            group.querySelectorAll('.att-pill-btn').forEach(btn => {
                btn.classList.remove('active-present', 'active-absent', 'active-late', 'active-excused', 'active-none');
            });
            const targetBtn = group.querySelector(`.btn-${status.toLowerCase()}`);
            if (targetBtn) {
                targetBtn.classList.add(`active-${status.toLowerCase()}`);
            }
        }

        // 2. Update hidden form input
        const input = document.getElementById(`attInput_${studentId}`);
        if (input) input.value = status;

        // 3. Update status badge
        const badgeCell = document.getElementById(`attBadge_${studentId}`);
        if (badgeCell) {
            let badgeHtml = '';
            if (status === 'PRESENT') {
                badgeHtml = '<span class="badge badge-success no-dot" style="font-size:11px;font-weight:700">উপস্থিত</span>';
            } else if (status === 'ABSENT') {
                badgeHtml = '<span class="badge badge-danger no-dot" style="font-size:11px;font-weight:700">অনুপস্থিত</span>';
            } else if (status === 'LATE') {
                badgeHtml = '<span class="badge badge-warning no-dot" style="font-size:11px;font-weight:700">দেরি</span>';
            } else if (status === 'EXCUSED') {
                badgeHtml = '<span class="badge badge-secondary no-dot" style="font-size:11px;font-weight:700">ছুটি</span>';
            } else {
                badgeHtml = '<span style="color:#94a3b8;font-size:12px;font-weight:600">— খালি —</span>';
            }
            badgeCell.innerHTML = badgeHtml;
        }

        // 4. Send background AJAX request to save immediately
        fetch('{{ route("admin.classes.updateAttendance", $class) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                student_id: studentId,
                status: status
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.stats) {
                updateLiveStats(data.stats);
                showRowSaved(studentId);
            }
        })
        .catch(err => {
            console.error('Attendance sync error:', err);
        });
    }

    function markAllAttendance(status) {
        const confirmMsg = status === 'PRESENT' 
            ? 'সকল শিক্ষার্থীকে উপস্থিত চিহ্নিত করতে চান?' 
            : (status === 'ABSENT' ? 'সকল শিক্ষার্থীকে অনুপস্থিত চিহ্নিত করতে চান?' : 'সকল উপস্থিতি খালি/রিসেট করতে চান?');
            
        if (!confirm(confirmMsg)) return;

        // Update all visible and hidden controls
        document.querySelectorAll('.att-pills-group').forEach(group => {
            const studentId = group.getAttribute('data-student-id');
            group.querySelectorAll('.att-pill-btn').forEach(btn => {
                btn.classList.remove('active-present', 'active-absent', 'active-late', 'active-excused', 'active-none');
            });
            const targetBtn = group.querySelector(`.btn-${status.toLowerCase()}`);
            if (targetBtn) {
                targetBtn.classList.add(`active-${status.toLowerCase()}`);
            }
            const input = document.getElementById(`attInput_${studentId}`);
            if (input) input.value = status;

            const badgeCell = document.getElementById(`attBadge_${studentId}`);
            if (badgeCell) {
                let badgeHtml = '';
                if (status === 'PRESENT') {
                    badgeHtml = '<span class="badge badge-success no-dot" style="font-size:11px;font-weight:700">উপস্থিত</span>';
                } else if (status === 'ABSENT') {
                    badgeHtml = '<span class="badge badge-danger no-dot" style="font-size:11px;font-weight:700">অনুপস্থিত</span>';
                } else if (status === 'LATE') {
                    badgeHtml = '<span class="badge badge-warning no-dot" style="font-size:11px;font-weight:700">দেরি</span>';
                } else if (status === 'EXCUSED') {
                    badgeHtml = '<span class="badge badge-secondary no-dot" style="font-size:11px;font-weight:700">ছুটি</span>';
                } else {
                    badgeHtml = '<span style="color:#94a3b8;font-size:12px;font-weight:600">— খালি —</span>';
                }
                badgeCell.innerHTML = badgeHtml;
            }
        });

        // Send bulk update via AJAX
        const form = document.getElementById('attendanceBulkForm');
        const formData = new FormData(form);

        fetch('{{ route("admin.classes.updateAttendance", $class) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.stats) {
                updateLiveStats(data.stats);
                showGlobalNotice(data.message || 'সকল শিক্ষার্থীর হাজিরা সংরক্ষিত হয়েছে!');
            }
        })
        .catch(err => {
            form.submit();
        });
    }

    function filterAttendanceTable(query) {
        const q = (query || '').toLowerCase().trim();
        document.querySelectorAll('.att-student-row').forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const code = row.getAttribute('data-code') || '';
            if (!q || name.includes(q) || code.includes(q)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function updateLiveStats(stats) {
        const elP = document.getElementById('cntPresent');
        const elA = document.getElementById('cntAbsent');
        if (elP) elP.textContent = stats.present ?? 0;
        if (elA) elA.textContent = stats.absent ?? 0;
    }

    function showRowSaved(studentId) {
        const notice = document.getElementById(`rowNotice_${studentId}`);
        if (notice) {
            notice.style.display = 'block';
            setTimeout(() => { notice.style.display = 'none'; }, 2000);
        }
    }

    function showGlobalNotice(msg) {
        const notice = document.getElementById('attLiveNotice');
        const text = document.getElementById('attLiveNoticeText');
        if (notice && text) {
            text.textContent = msg;
            notice.style.display = 'inline-flex';
            setTimeout(() => { notice.style.display = 'none'; }, 3000);
        }
    }
    </script>
</x-admin-layout>
