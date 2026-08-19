<x-admin-layout>
    <x-slot name="title">{{ $course->name }} — Configuration</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.courses.index') }}">← Back to Courses</a>
            </div>
            <h1>{{ $course->name }}</h1>
            <p>
                Type: <strong>{{ str_replace('_', ' ', $course->type) }}</strong> · 
                Duration: {{ $course->duration_value }} {{ strtolower($course->duration_unit) }}s
            </p>
        </div>
        <div class="page-header-actions">
            @if($course->type === 'SEMESTER_BASED')
                <button class="btn btn-outline" onclick="openModal('addSemesterModal')">+ New Semester</button>
            @endif
            <button class="btn btn-primary" onclick="openModal('mapSubjectModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Map Subject to Course
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         SEMESTER BASED → প্রতিটি Semester আলাদা Card এ
    ════════════════════════════════════════════════════════ --}}
    @if($course->type === 'SEMESTER_BASED')

        {{-- Header action row --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <div style="font-size:13px;color:var(--text-muted)">
                মোট <strong>{{ $course->semesters->count() }}</strong> টি Semester · <strong>{{ $course->courseSubjectMaps->count() }}</strong> টি Subject ম্যাপ করা হয়েছে
            </div>
        </div>

        @forelse($course->semesters->sortBy('sequence_no') as $sem)
        @php
            $semSubjects = $course->courseSubjectMaps->where('semester_id', $sem->id);
            $totalCredit = $semSubjects->sum(fn($m) => $m->subject->credit ?? 0);
        @endphp
        <div class="card" style="margin-bottom:16px">
            <div class="card-header" style="background:linear-gradient(135deg,rgba(59,130,246,.06),rgba(99,102,241,.04));border-bottom:1px solid var(--card-border)">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff">
                        {{ $sem->sequence_no }}
                    </div>
                    <div>
                        <span class="card-title" style="font-size:14px">{{ $sem->name }}</span>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:1px">Sequence: {{ $sem->sequence_no }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="badge badge-secondary no-dot">{{ $semSubjects->count() }} Subjects</span>
                    <span class="badge badge-scheduled no-dot">{{ $totalCredit }} Credits</span>
                    <form method="POST" action="{{ route('admin.courses.semesters.destroy', [$course, $sem]) }}" style="display:inline" onsubmit="return confirm('Delete semester?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm text-red" title="Delete Semester">&times;</button>
                    </form>
                </div>
            </div>

            @if($semSubjects->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Credit</th>
                            <th>Full Marks</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semSubjects as $i => $map)
                        <tr>
                            <td style="font-size:12px;color:var(--text-muted);width:32px">{{ $i + 1 }}</td>
                            <td class="td-primary">
                                <strong>{{ $map->subject->code ?? '—' }}</strong>
                                <div class="td-muted" style="font-size:12px">{{ $map->subject->name ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-secondary no-dot">{{ $map->subject->credit ?? 0 }} Cr</span>
                            </td>
                            <td style="font-size:13px;color:var(--text-muted)">{{ $map->subject->full_marks ?? '—' }} / {{ $map->subject->pass_marks ?? '—' }}</td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('admin.courses.subjects.remove', [$course, $map]) }}" style="display:inline" onsubmit="return confirm('Remove subject?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-red">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="padding:20px 24px;color:var(--text-muted);font-size:13px;text-align:center">
                এই Semester এ এখনো কোনো Subject ম্যাপ করা হয়নি।
                <a href="#" onclick="openModal('mapSubjectModal')" style="margin-left:6px;color:var(--blue)">+ Map Subject</a>
            </div>
            @endif
        </div>
        @empty
        <div class="card">
            <div class="empty-state">
                <p>কোনো Semester তৈরি করা হয়নি। উপরে <strong>+ New Semester</strong> বাটনে ক্লিক করুন।</p>
            </div>
        </div>
        @endforelse

    {{-- ════════════════════════════════════════════════════════
         SUBJECT BASED → একটি সিম্পল Subject card
    ════════════════════════════════════════════════════════ --}}
    @else
    <div class="card">
        <div class="card-header">
            <span class="card-title">Mapped Subject</span>
            <span class="badge badge-secondary no-dot">{{ $course->courseSubjectMaps->count() }} Subject</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Credit</th>
                        <th>Full Marks</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($course->courseSubjectMaps as $map)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $map->subject->code ?? '—' }}</strong>
                            <div class="td-muted" style="font-size:12px">{{ $map->subject->name ?? '—' }}</div>
                        </td>
                        <td><span class="badge badge-secondary no-dot">{{ $map->subject->credit ?? 0 }} Cr</span></td>
                        <td style="color:var(--text-muted);font-size:13px">{{ $map->subject->full_marks ?? '—' }} / {{ $map->subject->pass_marks ?? '—' }}</td>
                        <td style="text-align:right">
                            <form method="POST" action="{{ route('admin.courses.subjects.remove', [$course, $map]) }}" style="display:inline" onsubmit="return confirm('Remove subject?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">কোনো Subject ম্যাপ করা হয়নি।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Map Subject Modal -->
    <div class="modal-overlay" id="mapSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Map Subject to {{ $course->name }}</span>
                <button class="modal-close" onclick="closeModal('mapSubjectModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.courses.subjects.assign', $course) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Subject <span class="required">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">-- Choose Subject --</option>
                            @foreach($availableSubjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->code }}: {{ $subj->name }} ({{ $subj->credit }} Cr)</option>
                            @endforeach
                        </select>
                    </div>

                    @if($course->type === 'SEMESTER_BASED')
                    <div class="form-group">
                        <label>Select Semester <span class="required">*</span></label>
                        <select name="semester_id" class="form-control" required>
                            <option value="">-- Choose Semester --</option>
                            @foreach($course->semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('mapSubjectModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Map Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Semester Modal -->
    @if($course->type === 'SEMESTER_BASED')
    <div class="modal-overlay" id="addSemesterModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Add New Semester to {{ $course->name }}</span>
                <button class="modal-close" onclick="closeModal('addSemesterModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.courses.semesters.store', $course) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Sequence No. <span class="required">*</span></label>
                            <input type="number" name="sequence_no" class="form-control" value="{{ $course->semesters->count() + 1 }}" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Semester Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. 5th Semester" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSemesterModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Semester</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════════════════
         FEE PACKAGES SECTION
    ════════════════════════════════════════════════════════ --}}
    <div class="card" style="margin-top:24px">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div>
                <span class="card-title">💰 Fee Packages</span>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                    Admission Fee (Course): <strong>৳{{ number_format($course->admission_fee, 0) }}</strong>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <button class="btn btn-outline btn-sm" style="border-color:var(--indigo,#6366f1);color:var(--indigo,#6366f1);display:flex;align-items:center;gap:5px" onclick="openModal('templateModal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6M4 6h16M4 10h16M4 14h7M4 18h7"/></svg>
                    Use Template
                </button>
                <button class="btn btn-primary btn-sm" onclick="openModal('addPackageModal')">+ New Package</button>
            </div>
        </div>

        <div class="card-body" style="padding:0">
            @forelse($course->feePackages as $pkg)
            <div style="border-bottom:1px solid var(--card-border);padding:18px 20px">

                {{-- Package Header Row --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap">
                    {{-- LEFT: Package name + badges --}}
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <strong style="font-size:15px">{{ $pkg->name }}</strong>
                        @if($pkg->is_default)
                            <span class="badge badge-active no-dot" style="font-size:11px">Default</span>
                        @endif
                        @if($pkg->description)
                            <span style="font-size:12px;color:var(--text-muted)">— {{ $pkg->description }}</span>
                        @endif
                    </div>
                    {{-- RIGHT: Total + Actions --}}
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-size:14px;font-weight:700;color:var(--blue);background:rgba(59,130,246,.08);padding:4px 12px;border-radius:20px;white-space:nowrap">
                            Package Total: ৳{{ number_format($pkg->items->sum('total_amount'), 0) }}
                        </span>
                        @if(!$pkg->is_default)
                        <form method="POST" action="{{ route('admin.courses.packages.set-default', [$course, $pkg]) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline btn-sm" style="font-size:11px">Set Default</button>
                        </form>
                        @endif
                        <button class="btn btn-outline btn-sm" onclick="openAddItemModal({{ $pkg->id }})">+ Add Item</button>
                        <form method="POST" action="{{ route('admin.courses.packages.destroy', $pkg) }}" onsubmit="return confirm('Delete this package?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red);font-size:11px">Delete</button>
                        </form>
                    </div>
                </div>

                @if($pkg->items->count())
                <div class="table-wrapper" style="border-radius:10px;overflow:hidden;border:1px solid var(--card-border)">
                    <table style="font-size:13px;margin:0">
                        <thead>
                            <tr style="background:rgba(99,102,241,.04)">
                                <th style="padding:10px 14px">Fee Head</th>
                                <th style="padding:10px 14px">Rate (৳)</th>
                                <th style="padding:10px 14px">Months / Units</th>
                                <th style="padding:10px 14px">Total</th>
                                <th style="padding:10px 14px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pkg->items as $item)
                            <tr>
                                <td style="padding:10px 14px"><strong>{{ $item->label ?: $item->feeHead->name }}</strong></td>
                                <td style="padding:10px 14px">৳{{ number_format($item->amount_per_unit, 0) }}</td>
                                <td style="padding:10px 14px;color:var(--blue)">{{ $item->quantity }} {{ str_contains(strtolower($item->feeHead?->slug ?? ''), 'monthly') ? 'months' : 'units' }}</td>
                                <td style="padding:10px 14px"><strong>৳{{ number_format($item->total_amount, 0) }}</strong></td>
                                <td style="padding:10px 14px;text-align:right">
                                    <form method="POST" action="{{ route('admin.courses.packages.items.destroy', $item) }}" onsubmit="return confirm('Remove this fee item?')" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-red">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(59,130,246,.04)">
                                <td colspan="3" style="padding:10px 14px;text-align:right;font-weight:700">Package Total:</td>
                                <td colspan="2" style="padding:10px 14px"><strong style="color:var(--blue);font-size:15px">৳{{ number_format($pkg->items->sum('total_amount'), 0) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                    <div style="background:rgba(59,130,246,.04);border:1px dashed rgba(59,130,246,.25);border-radius:10px;padding:16px;text-align:center;font-size:13px;color:var(--text-muted)">
                        কোনো fee item নেই। <a href="#" onclick="openAddItemModal({{ $pkg->id }})" style="color:var(--blue)">+ Add Item</a> করুন।
                    </div>
                @endif
            </div>
            @empty
            <div style="padding:36px;text-align:center;color:var(--text-muted)">
                <div style="font-size:32px;margin-bottom:10px">💰</div>
                <div style="font-weight:600;margin-bottom:4px">কোনো Fee Package নেই</div>
                <div style="font-size:13px;margin-bottom:16px">Template ব্যবহার করে সব fee head সহ একটি package auto-তৈরি করুন, অথবা নিজে তৈরি করুন।</div>
                <div style="display:flex;gap:8px;justify-content:center">
                    <button class="btn btn-outline" onclick="openModal('templateModal')" style="border-color:var(--indigo,#6366f1);color:var(--indigo,#6366f1)">📋 Use Template</button>
                    <button class="btn btn-primary" onclick="openModal('addPackageModal')">+ New Package</button>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal-overlay" id="addPackageModal">
        <div class="modal" style="max-width:440px">
            <div class="modal-header">
                <span class="modal-title">New Fee Package</span>
                <button class="modal-close" onclick="closeModal('addPackageModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.courses.packages.store', $course) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Package Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Category 50, Category 100, Standard Package" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Optional description">
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <label class="form-check" style="cursor:pointer">
                            <input type="checkbox" name="is_default" value="1"> Set as Default Package
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addPackageModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Package</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Template Modal -->
    <div class="modal-overlay" id="templateModal">
        <div class="modal" style="max-width:500px">
            <div class="modal-header" style="background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(59,130,246,.05))">
                <div>
                    <span class="modal-title">📋 Template থেকে Package তৈরি করুন</span>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px">সব active fee heads সহ package auto-তৈরি হবে</div>
                </div>
                <button class="modal-close" onclick="closeModal('templateModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.courses.packages.from-template', $course) }}">
                @csrf
                <div class="modal-body">
                    {{-- Preview of what will be created --}}
                    <div style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:14px;margin-bottom:16px">
                        <div style="font-size:12px;font-weight:600;color:#6366f1;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">Auto-generated fee items:</div>
                        @foreach($feeHeads as $fh)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(99,102,241,.1)">
                            <span style="font-size:13px">{{ $fh->name }}</span>
                            <span style="font-size:11px;color:var(--text-muted);background:rgba(99,102,241,.1);padding:2px 8px;border-radius:20px">
                                @if(str_contains(strtolower($fh->slug ?? ''), 'monthly') || str_contains(strtolower($fh->slug ?? ''), 'tuition'))
                                    {{ $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value }} months × ৳0
                                @else
                                    1 unit × ৳0
                                @endif
                            </span>
                        </div>
                        @endforeach
                        <div style="font-size:11px;color:var(--text-muted);margin-top:8px">
                            ⚠️ সব amount ৳0 দিয়ে তৈরি হবে — পরে edit করুন।
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Package Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Standard Package 2026" value="Standard Package" required>
                    </div>
                    <div class="form-group">
                        <label class="form-check" style="cursor:pointer">
                            <input type="checkbox" name="is_default" value="1"> Set as Default Package
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('templateModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#6366f1,#3b82f6)">
                        📋 Create from Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Package Item Modal -->
    <div class="modal-overlay" id="addItemModal">
        <div class="modal" style="max-width:500px">
            <div class="modal-header">
                <span class="modal-title">Add Fee Item to Package</span>
                <button class="modal-close" onclick="closeModal('addItemModal')">&times;</button>
            </div>
            <form method="POST" id="addItemForm">
                @csrf
                <div class="modal-body">
                    {{-- Course duration info badge --}}
                    <div style="background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.2);border-radius:8px;padding:8px 14px;margin-bottom:14px;font-size:12px;color:var(--text-secondary)">
                        📐 Course Duration: <strong>{{ $course->duration_value }} {{ ucfirst(strtolower($course->duration_unit)) }}(s)</strong>
                        &nbsp;→&nbsp; Total months: <strong id="course_total_months">{{ $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value }}</strong>
                    </div>

                    <div class="form-group">
                        <label>Fee Head <span class="required">*</span></label>
                        <select name="fee_head_id" id="fee_head_select" class="form-control" required onchange="onFeeHeadChange(this)">
                            <option value="">-- Select Fee Head --</option>
                            @foreach($feeHeads as $fh)
                                <option value="{{ $fh->id }}" data-slug="{{ $fh->slug }}">{{ $fh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Label <small style="color:var(--text-muted)">(optional override)</small></label>
                        <input type="text" name="label" class="form-control" placeholder="Leave blank to use fee head name">
                    </div>

                    {{-- Toggle: Monthly-based vs Fixed --}}
                    <div style="display:flex;gap:8px;margin-bottom:14px">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;padding:6px 14px;border-radius:20px;border:1px solid var(--blue);background:rgba(59,130,246,.1);color:var(--blue)">
                            <input type="radio" name="fee_mode" value="monthly" checked onchange="toggleFeeMode('monthly')"> Monthly Fee
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;padding:6px 14px;border-radius:20px;border:1px solid var(--card-border);color:var(--text-secondary)">
                            <input type="radio" name="fee_mode" value="fixed" onchange="toggleFeeMode('fixed')"> Fixed Amount
                        </label>
                    </div>

                    {{-- Monthly Mode --}}
                    <div id="mode_monthly">
                        <div style="background:rgba(59,130,246,.05);border:1px solid rgba(59,130,246,.15);border-radius:10px;padding:14px">
                            <div style="font-size:11px;font-weight:600;color:var(--blue);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">
                                Formula: ৳/month × total months = Total
                            </div>
                            <div class="form-row" style="gap:12px">
                                <div class="form-group" style="flex:1">
                                    <label>৳ Per Month <span class="required">*</span></label>
                                    <input type="number" id="pkg_monthly" class="form-control" min="0" value="0" step="0.01" oninput="calcMonthly()">
                                    <small style="color:var(--text-muted);font-size:11px">e.g. 500</small>
                                </div>
                                <div class="form-group" style="flex:1">
                                    <label>Total Months</label>
                                    <input type="number" id="pkg_total_months_input" class="form-control" min="1"
                                        value="{{ $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value }}"
                                        oninput="calcMonthly()" style="background:rgba(99,102,241,.04)">
                                    <small style="color:var(--text-muted);font-size:11px">Auto from course. Edit if needed.</small>
                                </div>
                            </div>
                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:8px 14px;border-radius:8px;font-size:13px;text-align:center">
                                <span id="monthly_formula" style="color:var(--text-muted)">0 × {{ $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value }}</span>
                                &nbsp;=&nbsp;
                                <strong id="monthly_total" style="color:#166534;font-size:16px">৳0</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Fixed Mode --}}
                    <div id="mode_fixed" style="display:none">
                        <div class="form-row" style="gap:12px">
                            <div class="form-group" style="flex:1">
                                <label>Quantity <span class="required">*</span></label>
                                <input type="number" id="pkg_qty_fixed" class="form-control" min="1" value="1" oninput="calcFixed()">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label>Amount / Unit (৳) <span class="required">*</span></label>
                                <input type="number" id="pkg_amt_fixed" class="form-control" min="0" value="0" step="0.01" oninput="calcFixed()">
                            </div>
                        </div>
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:8px 14px;border-radius:8px;font-size:13px;text-align:center">
                            Total: <strong id="fixed_total" style="color:#166534;font-size:16px">৳0</strong>
                        </div>
                    </div>

                    {{-- Hidden real inputs submitted to server --}}
                    <input type="hidden" name="quantity" id="real_quantity" value="{{ $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value }}">
                    <input type="hidden" name="amount_per_unit" id="real_amount" value="0">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addItemModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const COURSE_TOTAL_MONTHS = {{ $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value }};

    function openAddItemModal(packageId) {
        document.getElementById('addItemForm').action = '/admin/courses/packages/' + packageId + '/items';
        document.getElementById('fee_head_select').value = '';
        document.getElementById('pkg_monthly').value = 0;
        document.getElementById('pkg_total_months_input').value = COURSE_TOTAL_MONTHS;
        document.getElementById('pkg_qty_fixed').value = 1;
        document.getElementById('pkg_amt_fixed').value = 0;
        setMode('fixed');
        openModal('addItemModal');
    }

    function onFeeHeadChange(sel) {
        const slug = sel.options[sel.selectedIndex]?.dataset?.slug ?? '';
        const isMonthly = slug.toLowerCase().includes('tuition') || slug.toLowerCase().includes('monthly');
        setMode(isMonthly ? 'monthly' : 'fixed');
    }

    function setMode(mode) {
        document.querySelector('input[value="monthly"]').checked = mode === 'monthly';
        document.querySelector('input[value="fixed"]').checked   = mode === 'fixed';
        toggleFeeMode(mode);
    }

    function toggleFeeMode(mode) {
        document.getElementById('mode_monthly').style.display = mode === 'monthly' ? '' : 'none';
        document.getElementById('mode_fixed').style.display   = mode === 'fixed'   ? '' : 'none';
        if (mode === 'monthly') calcMonthly();
        else calcFixed();
    }

    function calcMonthly() {
        const monthly = parseFloat(document.getElementById('pkg_monthly').value) || 0;
        const months  = parseInt(document.getElementById('pkg_total_months_input').value) || COURSE_TOTAL_MONTHS;
        const total   = monthly * months;
        document.getElementById('monthly_formula').textContent = monthly + ' × ' + months;
        document.getElementById('monthly_total').textContent   = '৳' + total.toLocaleString('en-BD');
        document.getElementById('real_quantity').value = months;
        document.getElementById('real_amount').value   = monthly;
    }

    function calcFixed() {
        const qty   = parseFloat(document.getElementById('pkg_qty_fixed').value)  || 0;
        const amt   = parseFloat(document.getElementById('pkg_amt_fixed').value)  || 0;
        const total = qty * amt;
        document.getElementById('fixed_total').textContent = '৳' + total.toLocaleString('en-BD');
        document.getElementById('real_quantity').value = qty;
        document.getElementById('real_amount').value   = amt;
    }
    </script>
    @endpush
</x-admin-layout>
