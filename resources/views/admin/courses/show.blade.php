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

    <div class="grid-2">
        <!-- Mapped Subjects Table -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Mapped Subjects</span>
                <span class="badge badge-secondary no-dot">{{ $course->courseSubjectMaps->count() }} Subjects</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            @if($course->type === 'SEMESTER_BASED') <th>Semester</th> @endif
                            <th>Credit</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($course->courseSubjectMaps as $map)
                        <tr>
                            <td class="td-primary">
                                {{ $map->subject->code ?? '—' }}: {{ $map->subject->name ?? '—' }}
                            </td>
                            @if($course->type === 'SEMESTER_BASED')
                            <td>
                                <span class="badge badge-scheduled no-dot">{{ $map->semester->name ?? 'Unassigned' }}</span>
                            </td>
                            @endif
                            <td>{{ $map->subject->credit ?? 0 }} Cr</td>
                            <td style="text-align:right">
                                <form method="POST" action="{{ route('admin.courses.subjects.remove', [$course, $map]) }}" style="display:inline" onsubmit="return confirm('Remove subject mapping?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-red">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">No subjects mapped to this course yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Course Semesters (if semester-based) -->
        @if($course->type === 'SEMESTER_BASED')
        <div class="card">
            <div class="card-header">
                <span class="card-title">Course Semesters</span>
                <span class="badge badge-secondary no-dot">{{ $course->semesters->count() }} Semesters</span>
            </div>
            <div style="padding:0">
                @forelse($course->semesters as $sem)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--card-border)">
                    <div>
                        <strong style="font-size:13px">{{ $sem->name }}</strong><br>
                        <span class="td-muted" style="font-size:11px">Sequence No: {{ $sem->sequence_no }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="badge badge-secondary no-dot">
                            {{ $course->courseSubjectMaps->where('semester_id', $sem->id)->count() }} Subjects
                        </span>
                        <form method="POST" action="{{ route('admin.courses.semesters.destroy', [$course, $sem]) }}" style="display:inline" onsubmit="return confirm('Delete semester?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-red">&times;</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="empty-state"><p>No semesters defined. Click "+ New Semester" to create one.</p></div>
                @endforelse
            </div>
        </div>
        @endif
    </div>

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
