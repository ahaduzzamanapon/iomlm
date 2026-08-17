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
</x-admin-layout>
