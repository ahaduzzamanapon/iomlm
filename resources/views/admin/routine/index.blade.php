<x-admin-layout>
    <x-slot name="title">Class Routine</x-slot>

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
            <h1>📅 Class Routine</h1>
            <p>Weekly class schedule grid — manage time slots, assign classes, detect teacher conflicts</p>
        </div>
        <div class="page-header-actions" style="gap:8px">
            <a href="{{ route('admin.routine.unassigned') }}" class="btn btn-outline btn-sm">📋 Assign Class</a>
            <button class="btn btn-outline btn-sm" onclick="openModal('addSlotModal')">+ Add Time Slot</button>
        </div>
    </div>

    {{-- Batch Filter + Auto-Generate --}}
    <div class="card" style="margin-bottom:16px;padding:14px 16px">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <form method="GET" action="{{ route('admin.routine.index') }}" style="display:flex;gap:8px;align-items:center">
                <select name="batch_id" class="form-control" style="min-width:220px" onchange="this.form.submit()">
                    <option value="">All Active Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBatchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </form>
            @if($selectedBatchId)
            <form method="POST" action="{{ route('admin.routine.auto-generate', $selectedBatchId) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Auto-generate routine for this batch? Existing entries will be kept.')">⚡ Auto-Generate Routine</button>
            </form>
            @endif
            <a href="{{ route('admin.routine.index') }}" class="btn btn-ghost btn-sm">Clear Filter</a>
        </div>
    </div>

    {{-- Legend --}}
    <div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;font-size:12px;align-items:center">
        <span style="color:#64748b">Legend:</span>
        <span style="background:#3b82f6;color:#fff;border-radius:4px;padding:2px 8px">Normal Entry</span>
        <span style="background:#ef4444;color:#fff;border-radius:4px;padding:2px 8px;outline:2px solid #ef4444">🔴 Overlap Conflict (Batch / Teacher)</span>
        <span style="background:repeating-linear-gradient(45deg,#fef9c3,#fef9c3 4px,#fefce8 4px,#fefce8 8px);padding:2px 8px;border-radius:4px;border:1px solid #f59e0b">Weekend</span>
    </div>

    {{-- Routine Grid --}}
    @if($slots->isEmpty())
        <div class="card" style="padding:40px;text-align:center;color:var(--text-muted)">
            <p>No time slots configured yet.</p>
            <button class="btn btn-primary" onclick="openModal('addSlotModal')" style="margin-top:12px">+ Add First Time Slot</button>
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
                                         title="{{ $entry->is_override ? '⚠️ OVERLAP CONFLICT: ' . ($entry->conflict_type ?? 'Batch or Teacher schedule overlap in this slot!') : '' }}"
                                         draggable="true"
                                         data-entry-id="{{ $entry->id }}"
                                         data-batch-id="{{ $entry->batch_id }}"
                                         data-subject-id="{{ $entry->subject_id ?? '' }}"
                                         data-teacher-id="{{ $entry->teacher_id ?? '' }}"
                                         data-title="{{ addslashes($entry->title ?? '') }}"
                                         data-original-color="{{ $color }}"
                                         onclick="openEditModal({{ $entry->id }}, '{{ addslashes($entry->batch->name ?? '') }}', '{{ $entry->day_of_week }}', {{ $slot->id }}, {{ $entry->batch_id }}, {{ $entry->subject_id ?? 'null' }}, {{ $entry->teacher_id ?? 'null' }}, '{{ addslashes($entry->title ?? '') }}')">
                                        @if($entry->is_override)
                                            <span class="override-badge">⚠</span>
                                        @endif
                                        <span class="drag-handle">⠿</span>
                                        <div class="pill-title">{{ $entry->title ?: ($entry->subject?->code ?? $entry->batch?->name ?? '—') }}</div>
                                        <div class="pill-sub">{{ $entry->batch?->name ?? '' }}</div>
                                        @if($entry->teacher)
                                        <div class="pill-sub">👤 {{ $entry->teacher->name }}</div>
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
                        <button class="btn btn-ghost btn-sm" onclick="openEditSlotModal({{ $slot->id }}, '{{ addslashes($slot->name) }}', '{{ $slot->start_time }}', '{{ $slot->end_time }}', {{ $slot->sort_order }})" title="Edit Slot">✏️</button>
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
                                <option value="{{ $b->id }}" {{ $selectedBatchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
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
                            <label>Subject</label>
                            <select name="subject_id" id="add_subject_id" class="form-control" onchange="onSubjectSelect('add')">
                                <option value="">— Select Subject —</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->code }}: {{ $s->name }}</option>
                                @endforeach
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
                            <label>Subject</label>
                            <select name="subject_id" id="edit_subject_id" class="form-control" onchange="onSubjectSelect('edit')">
                                <option value="">— None —</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->code }}: {{ $s->name }}</option>
                                @endforeach
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
                        <label>Custom Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline btn-sm text-red" id="editDeleteBtn">🗑 Delete</button>
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
                <span class="modal-title">Add Time Slot</span>
                <button class="modal-close" onclick="closeModal('addSlotModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.routine.slots.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Slot Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. মাগরিবের পর" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Time <span class="required">*</span></label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>End Time <span class="required">*</span></label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $slots->count() + 1 }}" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSlotModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Slot</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT SLOT MODAL --}}
    <div class="modal-overlay" id="editSlotModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Time Slot</span>
                <button class="modal-close" onclick="closeModal('editSlotModal')">&times;</button>
            </div>
            <form method="POST" id="editSlotForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Slot Name <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_slot_name" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" id="edit_slot_start" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" id="edit_slot_end" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="edit_slot_order" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline btn-sm text-red" id="deleteSlotBtn">🗑 Delete Slot</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('editSlotModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Drop Toast --}}
    <div id="dropToast"></div>

    @push('scripts')
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
                batch.semesters.forEach(sem => {
                    const isRunning = (sem.id == batch.current_semester_id);
                    const opt = document.createElement('option');
                    opt.value = sem.id;
                    opt.textContent = sem.name + (isRunning ? ' (Running)' : '');
                    semSelect.appendChild(opt);
                });

                if (targetSemesterId) {
                    semSelect.value = targetSemesterId;
                } else if (batch.current_semester_id) {
                    semSelect.value = batch.current_semester_id;
                }
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

        if (batch && batch.subject_maps && batch.subject_maps.length > 0) {
            let filteredMaps = batch.subject_maps;
            if (semId) {
                filteredMaps = filteredMaps.filter(m => m.semester_id == semId);
            }

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
                opt.textContent = 'No mapped subjects for this semester';
                subjSelect.appendChild(opt);
            }
        } else {
            allSubjects.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.code + ': ' + s.name;
                subjSelect.appendChild(opt);
            });
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
                    toast('❌ Cannot drop on weekend!', '#ef4444');
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
                            badge.textContent = '⚠';
                            draggingPill.prepend(badge);
                        }
                        toast('⚠️ Moved — teacher conflict detected! Shown in red.', '#f59e0b');
                    } else {
                        const origColor = draggingPill.dataset.originalColor || '#3b82f6';
                        draggingPill.style.background = origColor;
                        draggingPill.classList.remove('override');
                        draggingPill.querySelector('.override-badge')?.remove();
                        toast('✅ Entry moved to ' + newDay + ' — conflict resolved!');
                    }
                    draggingPill.setAttribute('onclick',
                        draggingPill.getAttribute('onclick')
                            .replace(/(, '[A-Z]{3}',\s*)(\d+)/, `, '${newDay}', ${newSlot}`)
                    );
                } catch (err) {
                    const origAddBtn = sourceCell.querySelector('.add-btn');
                    if (origAddBtn) sourceCell.insertBefore(draggingPill, origAddBtn);
                    else sourceCell.appendChild(draggingPill);
                    toast('❌ Failed to save. Please try again.', '#ef4444');
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

        openModal('addEntryModal');
    }

    function openEditModal(id, batchName, day, slotId, batchId, subjectId, teacherId, title) {
        document.getElementById('editEntryForm').action = '/admin/routine/entries/' + id;
        document.getElementById('edit_day_of_week').value = day;
        document.getElementById('edit_slot_id').value = slotId;
        document.getElementById('edit_batch_id').value = batchId;

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

    function openEditSlotModal(id, name, start, end, order) {
        document.getElementById('editSlotForm').action = '/admin/routine/slots/' + id;
        document.getElementById('edit_slot_name').value = name;
        document.getElementById('edit_slot_start').value = start.substring(0,5);
        document.getElementById('edit_slot_end').value   = end.substring(0,5);
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
