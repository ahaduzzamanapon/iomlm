<x-admin-layout>
    <x-slot name="title">Class Routine</x-slot>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ═══ Flatpickr Time Picker Customization for IOM Emerald Theme ═══ */
        .flatpickr-calendar.hasTime.noCalendar {
            width: auto;
            border-radius: 12px;
            box-shadow: 0 12px 36px rgba(2, 44, 34, 0.22);
            border: 1px solid #a7f3d0;
            background: #ffffff;
            padding: 10px 14px;
            z-index: 999999 !important;
            font-family: 'Kalpurush', 'Plus Jakarta Sans', sans-serif;
        }
        .flatpickr-time {
            height: 48px;
            line-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .flatpickr-time input {
            font-size: 18px;
            font-weight: 700;
            color: #064e3b;
            font-family: 'Kalpurush', 'Plus Jakarta Sans', sans-serif;
            border-radius: 6px;
        }
        .flatpickr-time input:hover, .flatpickr-time input:focus {
            background: #ecfdf5;
        }
        .flatpickr-time .flatpickr-time-separator {
            font-size: 20px;
            font-weight: 800;
            color: #047857;
        }
        .flatpickr-time .flatpickr-am-pm {
            font-size: 14px;
            font-weight: 800;
            color: #ffffff;
            background: #047857;
            border-radius: 8px;
            height: 36px;
            line-height: 36px;
            margin: auto 6px;
            padding: 0 10px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(4, 120, 87, 0.3);
            transition: all 0.2s ease;
        }
        .flatpickr-time .flatpickr-am-pm:hover {
            background: #064e3b;
            transform: scale(1.05);
        }
        .time-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .time-input-wrap .form-control {
            padding-right: 36px;
            cursor: pointer;
            font-weight: 600;
        }
        .time-input-wrap .time-icon {
            position: absolute;
            right: 12px;
            color: #047857;
            pointer-events: none;
            font-size: 14px;
        }
        .time-picker-helper-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .time-preset-pill {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #064e3b;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Kalpurush', sans-serif;
            transition: all .15s ease;
        }
        .time-preset-pill:hover {
            background: #047857;
            color: #ffffff;
            border-color: #047857;
        }
    </style>
    @endpush

    <style>
        .routine-grid { width:100%; border-collapse:collapse; table-layout:fixed; font-size:12px; }
        .routine-grid th, .routine-grid td { border:1px solid #e2e8f0; padding:0; vertical-align:top; }
        .routine-grid th { background:#f8fafc; font-weight:600; color:#64748b; padding:8px; text-align:center; }
        .slot-header { background:#1e293b !important; color:#fff !important; font-size:11px; min-width:120px; width:140px; }
        .day-header { text-align:center; font-size:12px; }
        .weekend-header { background:#fef3c7 !important; color:#92400e !important; }
        .cell-wrapper { min-height:80px; padding:4px; display:flex; flex-direction:column; gap:3px; position:relative; transition: background .15s; }
        .cell-weekend { background:repeating-linear-gradient(45deg,#fef9c3,#fef9c3 4px,#fefce8 4px,#fefce8 8px); opacity:.7; }
        /* Drag & Drop states */
        .cell-wrapper.drag-over { background:#dbeafe !important; outline:2px dashed #3b82f6; outline-offset:-2px; }
        .cell-wrapper.drop-reject { background:#fee2e2 !important; outline:2px dashed #ef4444; outline-offset:-2px; }
        .entry-pill { border-radius:5px; padding:4px 6px; font-size:11px; font-weight:600; color:#fff; position:relative;
            cursor:grab; user-select:none; transition: opacity .15s, transform .15s; }
        .entry-pill:active { cursor:grabbing; }
        .entry-pill.dragging { opacity:.45; transform:scale(.97); }
        .entry-pill.override { outline:2px solid #ef4444; box-shadow:0 0 0 1px #ef4444; }
        .entry-pill .override-badge { position:absolute; top:-5px; right:-5px; background:#ef4444; color:#fff; border-radius:9px; font-size:9px; font-weight:700; padding:1px 4px; }
        .entry-pill .pill-title { font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .entry-pill .pill-sub { font-size:10px; opacity:.85; margin-top:1px; }
        .entry-pill .drag-handle { position:absolute; top:3px; right:3px; opacity:.5; font-size:9px; pointer-events:none; }
        .add-btn { display:block; width:100%; text-align:center; padding:4px; color:#94a3b8; font-size:18px; cursor:pointer; border:none; background:transparent; border-radius:4px; }
        .add-btn:hover { background:#f1f5f9; color:#3b82f6; }
        .slot-time { font-size:10px; opacity:.7; font-weight:400; }
        /* Drop toast */
        #dropToast { position:fixed; bottom:24px; right:24px; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; color:#fff; z-index:9999; opacity:0; transition:opacity .3s; pointer-events:none; }
        #dropToast.show { opacity:1; }
    </style>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Class Routine</h1>
            <p>Weekly class schedule grid — manage time slots, assign classes, detect teacher conflicts</p>
        </div>
        <div class="page-header-actions" style="gap:8px">
            <a href="{{ route('admin.routine.unassigned') }}" class="btn btn-outline btn-sm">Assign Class</a>
            <button class="btn btn-outline btn-sm" onclick="openAddSlotModal()">+ Add Time Slot</button>
        </div>
    </div>

    {{-- Batch Filter + Group Filter + Auto-Generate --}}
    <div class="card" style="margin-bottom:16px;padding:14px 16px">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <form method="GET" action="{{ route('admin.routine.index') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <select name="batch_id" class="form-control" style="min-width:220px" onchange="this.form.submit()">
                    <option value="">সকল সক্রিয় ব্যাচ (All Batches)</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ ($selectedBatchId ?? null) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>

                <select name="group" class="form-control" style="min-width:180px" onchange="this.form.submit()">
                    <option value="">সকল শাখা / গ্রুপ (All Groups)</option>
                    <option value="ALL" {{ ($selectedGroup ?? null) === 'ALL' ? 'selected' : '' }}>যৌথ / সাধারণ (Common / All)</option>
                    <option value="MALE" {{ ($selectedGroup ?? null) === 'MALE' ? 'selected' : '' }}>ভাই শাখা (Male)</option>
                    <option value="FEMALE" {{ ($selectedGroup ?? null) === 'FEMALE' ? 'selected' : '' }}>বোন শাখা (Female)</option>
                    <option value="GROUP_A" {{ ($selectedGroup ?? null) === 'GROUP_A' ? 'selected' : '' }}>গ্রুপ ক (Group A)</option>
                    <option value="GROUP_B" {{ ($selectedGroup ?? null) === 'GROUP_B' ? 'selected' : '' }}>গ্রুপ খ (Group B)</option>
                </select>
            </form>
            @if(!empty($selectedBatchId))
            <form method="POST" action="{{ route('admin.routine.auto-generate', $selectedBatchId ?? 0) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Auto-generate routine for this batch? Existing entries will be kept.')"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate Routine</button>
            </form>
            @endif
            <a href="{{ route('admin.routine.index') }}" class="btn btn-ghost btn-sm">Clear Filter</a>
        </div>
    </div>

    {{-- Legend --}}
    <div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;font-size:12px;align-items:center">
        <span style="color:#64748b">শাখা ও সূচক:</span>
        <span style="background:#0284c7;color:#fff;border-radius:4px;padding:2px 8px;font-size:11px">ভাই শাখা (Brothers)</span>
        <span style="background:#ec4899;color:#fff;border-radius:4px;padding:2px 8px;font-size:11px">বোন শাখা (Sisters)</span>
        <span style="background:#8b5cf6;color:#fff;border-radius:4px;padding:2px 8px;font-size:11px">গ্রুপ ক (A)</span>
        <span style="background:#f59e0b;color:#fff;border-radius:4px;padding:2px 8px;font-size:11px">গ্রুপ খ (B)</span>
        <span style="background:#ef4444;color:#fff;border-radius:4px;padding:2px 8px;outline:2px solid #ef4444;font-size:11px">কনফ্লিক্ট (Overlap)</span>
        <span style="background:repeating-linear-gradient(45deg,#fef9c3,#fef9c3 4px,#fefce8 4px,#fefce8 8px);padding:2px 8px;border-radius:4px;border:1px solid #f59e0b;font-size:11px">ছুটির দিন (Weekend)</span>
    </div>

    {{-- Routine Grid --}}
    @if($slots->isEmpty())
        <div class="card" style="padding:40px;text-align:center;color:var(--text-muted)">
            <p>No time slots configured yet.</p>
            <button class="btn btn-primary" onclick="openAddSlotModal()" style="margin-top:12px">+ Add First Time Slot</button>
        </div>
    @else
    <div style="overflow-x:auto">
        <table class="routine-grid">
            <thead>
                <tr>
                    <th class="slot-header">Time Slot</th>
                    @foreach($days as $d)
                        <th class="day-header {{ in_array($d, $weekends) ? 'weekend-header' : '' }}">
                            {{ $d }}
                            @if(in_array($d, $weekends)) <span style="font-size:9px;display:block;opacity:.7">Weekend</span>@endif
                        </th>
                    @endforeach
                    <th style="width:50px;background:#f8fafc;font-size:11px;color:#94a3b8">Edit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slots as $slot)
                <tr>
                    <td style="background:#1e293b;padding:10px 12px;vertical-align:middle">
                        <div style="color:#fff;font-weight:600;font-size:12px">{{ $slot->name }}</div>
                        <div class="slot-time" style="color:#94a3b8">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</div>
                    </td>
                    @foreach($days as $day)
                        @php
                            $cellEntries = $entries[$slot->id][$day] ?? collect();
                            $isWeekend   = in_array($day, $weekends);
                        @endphp
                        <td>
                            <div class="cell-wrapper {{ $isWeekend ? 'cell-weekend' : '' }}"
                                 data-slot-id="{{ $slot->id }}"
                                 data-day="{{ $day }}"
                                 data-is-weekend="{{ $isWeekend ? '1' : '0' }}">
                                @forelse($cellEntries as $entry)
                                    @php $color = $entry->color ?: ($batchColors[$entry->batch_id] ?? '#3b82f6'); @endphp
                                    <div class="entry-pill {{ $entry->is_override ? 'override' : '' }}"
                                         id="pill-{{ $entry->id }}"
                                         style="background:{{ $entry->is_override ? '#ef4444' : $color }}"
                                         title="{{ $entry->is_override ? 'OVERLAP CONFLICT: ' . ($entry->conflict_type ?? 'Batch or Teacher schedule overlap in this slot!') : '' }}"
                                         draggable="true"
                                         data-entry-id="{{ $entry->id }}"
                                         data-batch-id="{{ $entry->batch_id }}"
                                         data-subject-id="{{ $entry->subject_id ?? '' }}"
                                         data-teacher-id="{{ $entry->teacher_id ?? '' }}"
                                         data-group-tag="{{ $entry->group_tag ?? 'ALL' }}"
                                         data-title="{{ addslashes($entry->title ?? '') }}"
                                         data-original-color="{{ $color }}"
                                         onclick="openEditModal({{ $entry->id }}, '{{ addslashes($entry->batch->name ?? '') }}', '{{ $entry->day_of_week }}', {{ $slot->id }}, {{ $entry->batch_id }}, {{ $entry->subject_id ?? 'null' }}, {{ $entry->teacher_id ?? 'null' }}, '{{ addslashes($entry->title ?? '') }}', '{{ $entry->group_tag ?? 'ALL' }}')">
                                        @if($entry->is_override)
                                            <span class="override-badge"></span>
                                        @endif
                                        <span class="drag-handle">⠿</span>
                                        @if($entry->group_tag === 'MALE')
                                            <div style="margin-bottom:2px"><span style="background:#0284c7;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">ভাই শাখা</span></div>
                                        @elseif($entry->group_tag === 'FEMALE')
                                            <div style="margin-bottom:2px"><span style="background:#ec4899;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">বোন শাখা</span></div>
                                        @elseif($entry->group_tag === 'GROUP_A')
                                            <div style="margin-bottom:2px"><span style="background:#8b5cf6;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">গ্রুপ ক</span></div>
                                        @elseif($entry->group_tag === 'GROUP_B')
                                            <div style="margin-bottom:2px"><span style="background:#f59e0b;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">গ্রুপ খ</span></div>
                                        @endif
                                        <div class="pill-title">{{ $entry->title ?: ($entry->subject?->code ?? $entry->batch?->name ?? '—') }}</div>
                                        <div class="pill-sub">{{ $entry->batch?->name ?? '' }}</div>
                                        @if($entry->teacher)
                                        <div class="pill-sub">{{ $entry->teacher->name }}</div>
                                        @endif
                                    </div>
                                @empty
                                    @if(!$isWeekend)
                                    <button class="add-btn" onclick="openAddModal('{{ $day }}', {{ $slot->id }})">+</button>
                                    @endif
                                @endforelse
                                @if($cellEntries->isNotEmpty() && !$isWeekend)
                                    <button class="add-btn" onclick="openAddModal('{{ $day }}', {{ $slot->id }})" style="font-size:13px;padding:2px">+ add</button>
                                @endif
                            </div>
                        </td>
                    @endforeach
                    <td style="text-align:center;vertical-align:middle">
                        <button class="btn btn-ghost btn-sm" onclick="openEditSlotModal({{ $slot->id }}, '{{ addslashes($slot->name) }}', '{{ $slot->start_time }}', '{{ $slot->end_time }}', {{ $slot->sort_order }})" title="Edit Slot"><i class="fa-solid fa-pen-to-square"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ADD ENTRY MODAL --}}
    <div class="modal-overlay" id="addEntryModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Add Routine Entry</span>
                <button class="modal-close" onclick="closeModal('addEntryModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.routine.entries.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="day_of_week" id="add_day">
                    <input type="hidden" name="slot_id" id="add_slot_id">

                    <div class="form-group">
                        <label>Batch <span class="required">*</span></label>
                        <select name="batch_id" id="add_batch_id" class="form-control" required onchange="onBatchSelect(this.value, 'add')">
                            <option value="">Select Batch</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}" {{ ($selectedBatchId ?? null) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Semester (Running Semester)</label>
                        <select name="semester_id" id="add_semester_id" class="form-control" onchange="onSemesterSelect('add')">
                            <option value="">— Select Semester —</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject (রানিং সেমিস্টার বিষয়) <span class="required">*</span></label>
                            <select name="subject_id" id="add_subject_id" class="form-control" onchange="onSubjectSelect('add')" required>
                                <option value="">— Select Subject —</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Teacher</label>
                            <select name="teacher_id" id="add_teacher_id" class="form-control">
                                <option value="">— Assign Teacher —</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>গ্রুপ / শাখা (Group / Section) <span class="required">*</span></label>
                        <select name="group_tag" id="add_group_tag" class="form-control">
                            <option value="ALL">যৌথ / সকল শিক্ষার্থী (Common / All)</option>
                            <option value="MALE">ভাই শাখা (Brothers / Male)</option>
                            <option value="FEMALE">বোন শাখা (Sisters / Female)</option>
                            <option value="GROUP_A">গ্রুপ ক (Group A)</option>
                            <option value="GROUP_B">গ্রুপ খ (Group B)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Custom Title (optional)</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Aqeedah Class — Group 1">
                    </div>
                    <div class="form-group">
                        <label>Custom Color (optional)</label>
                        <input type="color" name="color" class="form-control" style="height:36px;width:60px">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addEntryModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Entry</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT ENTRY MODAL --}}
    <div class="modal-overlay" id="editEntryModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Routine Entry</span>
                <button class="modal-close" onclick="closeModal('editEntryModal')">&times;</button>
            </div>
            <form method="POST" id="editEntryForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Batch</label>
                        <select name="batch_id" id="edit_batch_id" class="form-control" onchange="onBatchSelect(this.value, 'edit')">
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Semester (Running Semester)</label>
                        <select name="semester_id" id="edit_semester_id" class="form-control" onchange="onSemesterSelect('edit')">
                            <option value="">— Select Semester —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Day</label>
                        <select name="day_of_week" id="edit_day_of_week" class="form-control">
                            @foreach(['SAT','SUN','MON','TUE','WED','THU','FRI'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="slot_id" id="edit_slot_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject (রানিং সেমিস্টার বিষয়)</label>
                            <select name="subject_id" id="edit_subject_id" class="form-control" onchange="onSubjectSelect('edit')">
                                <option value="">— None —</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Teacher</label>
                            <select name="teacher_id" id="edit_teacher_id" class="form-control">
                                <option value="">— None —</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>গ্রুপ / শাখা (Group / Section) <span class="required">*</span></label>
                        <select name="group_tag" id="edit_group_tag" class="form-control">
                            <option value="ALL">যৌথ / সকল শিক্ষার্থী (Common / All)</option>
                            <option value="MALE">ভাই শাখা (Brothers / Male)</option>
                            <option value="FEMALE">বোন শাখা (Sisters / Female)</option>
                            <option value="GROUP_A">গ্রুপ ক (Group A)</option>
                            <option value="GROUP_B">গ্রুপ খ (Group B)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Custom Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline btn-sm text-red" id="editDeleteBtn"><i class="fa-solid fa-trash"></i> Delete</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('editEntryModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD SLOT MODAL --}}
    <div class="modal-overlay" id="addSlotModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title"><i class="fa-solid fa-clock" style="color:var(--iom-green);margin-right:6px"></i> Add Time Slot</span>
                <button class="modal-close" onclick="closeModal('addSlotModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.routine.slots.store') }}" id="addSlotForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Slot Name (স্লটের নাম) <span class="required">*</span></label>
                        <input type="text" name="name" id="add_slot_name" class="form-control" placeholder="e.g. সকাল ১ম শিফট / মাগরিবের পর" required>
                    </div>

                    {{-- Quick Preset Slots (Bangladeshi Islamic & Academic Hours) --}}
                    <div style="margin-bottom:14px">
                        <label style="font-size:11px;color:var(--iom-muted);margin-bottom:6px;display:block">
                            <i class="fa-solid fa-bolt" style="color:var(--iom-gold)"></i> দ্রুত সময় নির্বাচন করুন (Quick Presets):
                        </label>
                        <div class="time-picker-helper-btns">
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('add', '09:00', '10:30', 'সকাল ০৯:০০ - ১০:৩০')">সকাল ০৯:০০ - ১০:৩০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('add', '10:30', '12:00', 'সকাল ১০:৩০ - ১২:০০')">সকাল ১০:৩০ - ১২:০০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('add', '14:00', '15:30', 'দুপুর ০২:০০ - ০৩:৩০')">দুপুর ০২:০০ - ০৩:৩০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('add', '16:00', '17:30', 'আসর ০৪:০০ - ০৫:৩০')">আসর ০৪:০০ - ০৫:৩০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('add', '18:45', '20:00', 'মাগরিবের পর')">মাগরিব ১৮:৪৫ - ২০:০০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('add', '20:30', '22:00', 'রাত ০৮:৩০ - ১০:০০')">রাত ০৮:৩০ - ১০:০০</button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fa-regular fa-clock" style="color:var(--iom-green);margin-right:4px"></i> Start Time (শুরুর সময় - AM/PM) <span class="required">*</span></label>
                            <div class="time-input-wrap">
                                <input type="text" name="start_time" id="add_slot_start" class="form-control timepicker" placeholder="Select Start Time" required>
                                <i class="fa-regular fa-clock time-icon"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-regular fa-clock" style="color:var(--iom-green);margin-right:4px"></i> End Time (শেষের সময় - AM/PM) <span class="required">*</span></label>
                            <div class="time-input-wrap">
                                <input type="text" name="end_time" id="add_slot_end" class="form-control timepicker" placeholder="Select End Time" required>
                                <i class="fa-regular fa-clock time-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sort Order (ক্রমিক নম্বর)</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $slots->count() + 1 }}" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSlotModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Slot</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT SLOT MODAL --}}
    <div class="modal-overlay" id="editSlotModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title"><i class="fa-solid fa-pen-to-square" style="color:var(--iom-green);margin-right:6px"></i> Edit Time Slot</span>
                <button class="modal-close" onclick="closeModal('editSlotModal')">&times;</button>
            </div>
            <form method="POST" id="editSlotForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Slot Name (স্লটের নাম) <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_slot_name" class="form-control" required>
                    </div>

                    {{-- Quick Preset Slots for Edit --}}
                    <div style="margin-bottom:14px">
                        <label style="font-size:11px;color:var(--iom-muted);margin-bottom:6px;display:block">
                            <i class="fa-solid fa-bolt" style="color:var(--iom-gold)"></i> দ্রুত সময় পরিবর্তন (Quick Presets):
                        </label>
                        <div class="time-picker-helper-btns">
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('edit', '09:00', '10:30')">সকাল ০৯:০০ - ১০:৩০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('edit', '10:30', '12:00')">সকাল ১০:৩০ - ১২:০০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('edit', '14:00', '15:30')">দুপুর ০২:০০ - ০৩:৩০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('edit', '16:00', '17:30')">আসর ০৪:০০ - ০৫:৩০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('edit', '18:45', '20:00')">মাগরিব ১৮:৪৫ - ২০:০০</button>
                            <button type="button" class="time-preset-pill" onclick="applyTimePreset('edit', '20:30', '22:00')">রাত ০৮:৩০ - ১০:০০</button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fa-regular fa-clock" style="color:var(--iom-green);margin-right:4px"></i> Start Time (শুরুর সময় - AM/PM) <span class="required">*</span></label>
                            <div class="time-input-wrap">
                                <input type="text" name="start_time" id="edit_slot_start" class="form-control timepicker" placeholder="Select Start Time" required>
                                <i class="fa-regular fa-clock time-icon"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-regular fa-clock" style="color:var(--iom-green);margin-right:4px"></i> End Time (শেষের সময় - AM/PM) <span class="required">*</span></label>
                            <div class="time-input-wrap">
                                <input type="text" name="end_time" id="edit_slot_end" class="form-control timepicker" placeholder="Select End Time" required>
                                <i class="fa-regular fa-clock time-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sort Order (ক্রমিক নম্বর)</label>
                        <input type="number" name="sort_order" id="edit_slot_order" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline btn-sm text-red" id="deleteSlotBtn"><i class="fa-solid fa-trash"></i> Delete Slot</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('editSlotModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Drop Toast --}}
    <div id="dropToast"></div>

    @push('scripts')
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script>
    if (typeof flatpickr === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/flatpickr"><\/script>');
    }
    </script>
    <script>
    const CSRF = '{{ csrf_token() }}';

    // Batch details map for dynamic semester & subject filtering
    const batchDetails = @json($batchData);

    const allSubjects = [
        @foreach($subjects as $s)
        { id: {{ $s->id }}, code: @json($s->code), name: @json($s->name) },
        @endforeach
    ];

    const allTeachers = [
        @foreach($teachers as $t)
        { id: {{ $t->id }}, name: @json($t->name) },
        @endforeach
    ];

    const subjectTeacherMap = @json($subjectTeachers ?? []);

    function onSubjectSelect(prefix = 'add', targetTeacherId = null) {
        const subjSelect  = document.getElementById(prefix + '_subject_id');
        const teachSelect = document.getElementById(prefix + '_teacher_id');
        if (!teachSelect) return;

        const subjId = subjSelect ? subjSelect.value : null;
        teachSelect.innerHTML = '<option value="">— Assign Teacher —</option>';

        const assigned = (subjId && subjectTeacherMap[subjId]) ? subjectTeacherMap[subjId] : [];
        const assignedIds = assigned.map(t => parseInt(t.id));

        if (assigned.length > 0) {
            const grpAssigned = document.createElement('optgroup');
            grpAssigned.label = 'বিষয়ভিত্তিক শিক্ষক (Assigned Subject Teachers)';
            assigned.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name + ' ⭐';
                grpAssigned.appendChild(opt);
            });
            teachSelect.appendChild(grpAssigned);

            const otherTeachers = allTeachers.filter(t => !assignedIds.includes(parseInt(t.id)));
            if (otherTeachers.length > 0) {
                const grpOthers = document.createElement('optgroup');
                grpOthers.label = 'অন্যান্য শিক্ষক (Other Teachers)';
                otherTeachers.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    grpOthers.appendChild(opt);
                });
                teachSelect.appendChild(grpOthers);
            }
        } else {
            allTeachers.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                teachSelect.appendChild(opt);
            });
        }

        if (targetTeacherId) {
            teachSelect.value = targetTeacherId;
        } else if (assigned.length === 1 && prefix === 'add') {
            teachSelect.value = assigned[0].id;
        }
    }

    function onBatchSelect(batchId, prefix = 'add', targetSemesterId = null, targetSubjectId = null, targetTeacherId = null) {
        const semSelect = document.getElementById(prefix + '_semester_id');
        if (!semSelect) return;

        semSelect.innerHTML = '<option value="">— Select Semester —</option>';

        const batch = batchDetails[batchId];
        if (batch) {
            if (batch.course_type === 'SEMESTER_BASED' && batch.semesters && batch.semesters.length > 0) {
                const runningSemId = batch.current_semester_id || batch.semesters[0].id;
                const activeSemId  = targetSemesterId || runningSemId;

                batch.semesters.forEach(sem => {
                    const isRunning = (sem.id == runningSemId);
                    const opt = document.createElement('option');
                    opt.value = sem.id;
                    opt.textContent = sem.name + (isRunning ? ' ★ (Running Semester)' : '');
                    if (sem.id == activeSemId) {
                        opt.selected = true;
                    }
                    semSelect.appendChild(opt);
                });

                semSelect.value = activeSemId;
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'Direct Subject Enrolled (No Semester)';
                semSelect.appendChild(opt);
            }
        }

        onSemesterSelect(prefix, targetSubjectId, targetTeacherId);
    }

    function onSemesterSelect(prefix = 'add', targetSubjectId = null, targetTeacherId = null) {
        const batchSelect = document.getElementById(prefix + '_batch_id');
        const semSelect   = document.getElementById(prefix + '_semester_id');
        const subjSelect  = document.getElementById(prefix + '_subject_id');

        if (!subjSelect) return;

        const batchId = batchSelect ? batchSelect.value : null;
        const semId   = semSelect ? semSelect.value : null;
        const batch   = batchId ? batchDetails[batchId] : null;

        subjSelect.innerHTML = '<option value="">— Select Subject —</option>';

        if (batch) {
            if (batch.course_type === 'SEMESTER_BASED' && batch.semesters && batch.semesters.length > 0) {
                // Strictly filter to running / chosen semester
                const activeSemId = semId || batch.current_semester_id || batch.semesters[0].id;
                let filteredMaps = (batch.subject_maps || []).filter(m => m.semester_id == activeSemId);

                if (filteredMaps.length > 0) {
                    filteredMaps.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.subject_id;
                        opt.textContent = (m.code ? m.code + ': ' : '') + m.name;
                        subjSelect.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No mapped subjects for this running semester';
                    subjSelect.appendChild(opt);
                }
            } else {
                // Non-semester course
                if (batch.subject_maps && batch.subject_maps.length > 0) {
                    batch.subject_maps.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.subject_id;
                        opt.textContent = (m.code ? m.code + ': ' : '') + m.name;
                        subjSelect.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No mapped subjects for this course';
                    subjSelect.appendChild(opt);
                }
            }
        } else {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '— Please select a batch first —';
            subjSelect.appendChild(opt);
        }

        if (targetSubjectId) {
            subjSelect.value = targetSubjectId;
        }

        onSubjectSelect(prefix, targetTeacherId);
    }

    // ══════════════════════════════════════════════════════
    // DRAG & DROP
    // ══════════════════════════════════════════════════════
    let draggingPill  = null;   // the pill DOM element
    let sourceCell    = null;   // the cell it came from

    function toast(msg, color='#10b981') {
        const t = document.getElementById('dropToast');
        t.textContent = msg;
        t.style.background = color;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    function initDragDrop() {
        // ── Pills (drag sources)
        document.querySelectorAll('.entry-pill[draggable]').forEach(pill => {
            pill.addEventListener('dragstart', e => {
                draggingPill = pill;
                sourceCell   = pill.closest('.cell-wrapper');
                pill.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', pill.dataset.entryId);
            });
            pill.addEventListener('dragend', () => {
                draggingPill?.classList.remove('dragging');
                draggingPill = null;
                sourceCell   = null;
                document.querySelectorAll('.cell-wrapper').forEach(c => {
                    c.classList.remove('drag-over', 'drop-reject');
                });
            });
        });

        // ── Cells (drop targets)
        document.querySelectorAll('.cell-wrapper').forEach(cell => {
            cell.addEventListener('dragover', e => {
                if (!draggingPill) return;
                e.preventDefault();
                if (cell === sourceCell) return;
                const isWeekend = cell.dataset.isWeekend === '1';
                cell.classList.toggle('drag-over',   !isWeekend);
                cell.classList.toggle('drop-reject',  isWeekend);
                e.dataTransfer.dropEffect = isWeekend ? 'none' : 'move';
            });
            cell.addEventListener('dragleave', e => {
                if (!cell.contains(e.relatedTarget)) {
                    cell.classList.remove('drag-over', 'drop-reject');
                }
            });
            cell.addEventListener('drop', async e => {
                e.preventDefault();
                cell.classList.remove('drag-over', 'drop-reject');
                if (!draggingPill || cell === sourceCell) return;
                if (cell.dataset.isWeekend === '1') {
                    toast('Cannot drop on weekend!', '#ef4444');
                    return;
                }

                const entryId  = draggingPill.dataset.entryId;
                const newSlot  = cell.dataset.slotId;
                const newDay   = cell.dataset.day;
                const batchId  = draggingPill.dataset.batchId;
                const subjId   = draggingPill.dataset.subjectId || null;
                const teachId  = draggingPill.dataset.teacherId || null;
                const title    = draggingPill.dataset.title || null;

                const addBtn = cell.querySelector('.add-btn');
                if (addBtn) cell.insertBefore(draggingPill, addBtn);
                else cell.appendChild(draggingPill);

                const remainingPills = sourceCell.querySelectorAll('.entry-pill');
                if (remainingPills.length === 0 && !sourceCell.querySelector('.add-btn')) {
                    const btn = document.createElement('button');
                    btn.className = 'add-btn';
                    btn.textContent = '+';
                    const srcDay  = sourceCell.dataset.day;
                    const srcSlot = sourceCell.dataset.slotId;
                    btn.setAttribute('onclick', `openAddModal('${srcDay}', ${srcSlot})`);
                    sourceCell.appendChild(btn);
                }

                try {
                    const body = new URLSearchParams({
                        _method:     'PUT',
                        _token:      CSRF,
                        batch_id:    batchId,
                        slot_id:     newSlot,
                        day_of_week: newDay,
                        subject_id:  subjId ?? '',
                        teacher_id:  teachId ?? '',
                        title:       title ?? '',
                    });
                    const res  = await fetch(`/admin/routine/entries/${entryId}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body
                    });
                    const json = await res.json();

                    if (json.is_override) {
                        draggingPill.style.background = '#ef4444';
                        draggingPill.classList.add('override');
                        if (!draggingPill.querySelector('.override-badge')) {
                            const badge = document.createElement('span');
                            badge.className = 'override-badge';
                            badge.textContent = '';
                            draggingPill.prepend(badge);
                        }
                        toast('Moved — teacher conflict detected! Shown in red.', '#f59e0b');
                    } else {
                        const origColor = draggingPill.dataset.originalColor || '#3b82f6';
                        draggingPill.style.background = origColor;
                        draggingPill.classList.remove('override');
                        draggingPill.querySelector('.override-badge')?.remove();
                        toast('Entry moved to ' + newDay + ' — conflict resolved!');
                    }
                    draggingPill.setAttribute('onclick',
                        draggingPill.getAttribute('onclick')
                            .replace(/(, '[A-Z]{3}',\s*)(\d+)/, `, '${newDay}', ${newSlot}`)
                    );
                } catch (err) {
                    const origAddBtn = sourceCell.querySelector('.add-btn');
                    if (origAddBtn) sourceCell.insertBefore(draggingPill, origAddBtn);
                    else sourceCell.appendChild(draggingPill);
                    toast('Failed to save. Please try again.', '#ef4444');
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initDragDrop();
        const initialBatch = document.getElementById('add_batch_id')?.value;
        if (initialBatch) onBatchSelect(initialBatch, 'add');
    });

    // ── Modal helpers ──────────────────────────
    function openAddModal(day, slotId) {
        document.getElementById('add_day').value = day;
        document.getElementById('add_slot_id').value = slotId;

        const batchSelect = document.getElementById('add_batch_id');
        if (batchSelect && batchSelect.value) {
            onBatchSelect(batchSelect.value, 'add');
        } else {
            onBatchSelect('', 'add');
        }

        if (document.getElementById('add_group_tag')) {
            document.getElementById('add_group_tag').value = 'ALL';
        }

        openModal('addEntryModal');
    }

    function openEditModal(id, batchName, day, slotId, batchId, subjectId, teacherId, title, groupTag = 'ALL') {
        document.getElementById('editEntryForm').action = '/admin/routine/entries/' + id;
        document.getElementById('edit_day_of_week').value = day;
        document.getElementById('edit_slot_id').value = slotId;
        document.getElementById('edit_batch_id').value = batchId;
        if (document.getElementById('edit_group_tag')) {
            document.getElementById('edit_group_tag').value = groupTag || 'ALL';
        }

        let foundSemId = null;
        const b = batchDetails[batchId];
        if (b && b.subject_maps && subjectId) {
            const m = b.subject_maps.find(map => map.subject_id == subjectId);
            if (m) foundSemId = m.semester_id;
        }

        onBatchSelect(batchId, 'edit', foundSemId, subjectId, teacherId);

        if (teacherId) document.getElementById('edit_teacher_id').value = teacherId;
        document.getElementById('edit_title').value = title;

        document.getElementById('editDeleteBtn').onclick = function() {
            if (confirm('Delete this routine entry?')) {
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = '/admin/routine/entries/' + id;
                f.innerHTML = '<input name="_token" value="{{ csrf_token() }}"><input name="_method" value="DELETE">';
                document.body.appendChild(f); f.submit();
            }
        };

        openModal('editEntryModal');
    }

    // ═══ Flatpickr Timepicker Integration for Routine Slots (12-Hour AM/PM) ═══
    let fpAddStart = null, fpAddEnd = null, fpEditStart = null, fpEditEnd = null;

    function initSlotTimepickers() {
        if (typeof flatpickr === 'undefined') {
            setTimeout(initSlotTimepickers, 100);
            return;
        }

        const commonTimeConfig = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",      // Value submitted to server in 24-hour SQL format (e.g. 09:30, 14:15)
            altInput: true,          // Human-readable formatted input visible to user
            altFormat: "h:i K",      // 12-hour format with AM / PM: e.g. 09:30 AM, 02:15 PM
            altInputClass: "form-control timepicker",
            time_24hr: false,        // 12-hour AM/PM picker with dedicated toggle button
            minuteIncrement: 5,      // 5-minute steps for smooth time selection
            allowInput: true,        // Allow manual typing or picker selection
        };

        if (document.getElementById('add_slot_start') && !fpAddStart) {
            fpAddStart = flatpickr("#add_slot_start", {
                ...commonTimeConfig,
                defaultDate: "09:00",
            });
        }

        if (document.getElementById('add_slot_end') && !fpAddEnd) {
            fpAddEnd = flatpickr("#add_slot_end", {
                ...commonTimeConfig,
                defaultDate: "10:30",
            });
        }

        if (document.getElementById('edit_slot_start') && !fpEditStart) {
            fpEditStart = flatpickr("#edit_slot_start", commonTimeConfig);
        }

        if (document.getElementById('edit_slot_end') && !fpEditEnd) {
            fpEditEnd = flatpickr("#edit_slot_end", commonTimeConfig);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSlotTimepickers);
    } else {
        initSlotTimepickers();
    }

    function applyTimePreset(modalType, start, end, slotName) {
        if (modalType === 'add') {
            if (fpAddStart) fpAddStart.setDate(start, true, "H:i");
            if (fpAddEnd)   fpAddEnd.setDate(end, true, "H:i");
            if (slotName && !document.getElementById('add_slot_name').value) {
                document.getElementById('add_slot_name').value = slotName;
            }
        } else if (modalType === 'edit') {
            if (fpEditStart) fpEditStart.setDate(start, true, "H:i");
            if (fpEditEnd)   fpEditEnd.setDate(end, true, "H:i");
        }
    }

    function openAddSlotModal() {
        if (!fpAddStart || !fpAddEnd) {
            initSlotTimepickers();
        }
        if (fpAddStart && !fpAddStart.input.value) {
            fpAddStart.setDate("09:00", true, "H:i");
        }
        if (fpAddEnd && !fpAddEnd.input.value) {
            fpAddEnd.setDate("10:30", true, "H:i");
        }
        openModal('addSlotModal');
    }

    function openEditSlotModal(id, name, start, end, order) {
        document.getElementById('editSlotForm').action = '/admin/routine/slots/' + id;
        document.getElementById('edit_slot_name').value = name;

        if (!fpEditStart || !fpEditEnd) {
            initSlotTimepickers();
        }

        const cleanStart = (start || '').substring(0, 5);
        const cleanEnd   = (end || '').substring(0, 5);

        if (fpEditStart) {
            fpEditStart.setDate(cleanStart, true, "H:i");
        } else {
            document.getElementById('edit_slot_start').value = cleanStart;
        }

        if (fpEditEnd) {
            fpEditEnd.setDate(cleanEnd, true, "H:i");
        } else {
            document.getElementById('edit_slot_end').value = cleanEnd;
        }

        document.getElementById('edit_slot_order').value = order;

        document.getElementById('deleteSlotBtn').onclick = function() {
            if (confirm('Delete this time slot and all its entries?')) {
                const f = document.createElement('form');
                f.method = 'POST'; f.action = '/admin/routine/slots/' + id;
                f.innerHTML = '<input name="_token" value="{{ csrf_token() }}"><input name="_method" value="DELETE">';
                document.body.appendChild(f); f.submit();
            }
        };

        openModal('editSlotModal');
    }
    </script>
    @endpush

</x-admin-layout>
