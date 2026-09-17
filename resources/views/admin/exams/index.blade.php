<x-admin-layout>
    <x-slot name="title">Exams & Results</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Exams & Evaluation Management</h1>
            <p>Schedule subject examinations and review student results</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addExamModal')">
                Schedule Exam
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Title</th>
                        <th>Type</th>
                        <th>Date & Duration</th>
                        <th>Marks (Full/Pass)</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                    <tr>
                        <td class="td-primary"><strong>{{ $exam->subject->name ?? '—' }}</strong></td>
                        <td>{{ $exam->title }}</td>
                        <td><span class="badge badge-secondary no-dot">{{ $exam->type }}</span></td>
                        <td class="td-muted">
                            <div><i class="fa-solid fa-calendar-day"></i> {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                                @if($exam->end_date && $exam->end_date != $exam->exam_date)
                                    - {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}
                                @endif
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                @if($exam->start_time)
                                    <i class="fa-regular fa-clock"></i> {{ date('h:i A', strtotime($exam->start_time)) }}
                                    @if($exam->end_time) - {{ date('h:i A', strtotime($exam->end_time)) }} @endif
                                    &middot;
                                @endif
                                ⏱️ {{ $exam->duration_minutes ?? 90 }} মি.
                            </div>
                        </td>
                        <td>{{ $exam->full_marks }} / {{ $exam->pass_marks }}</td>
                        <td><span class="badge badge-{{ strtolower($exam->status) }}">{{ ucfirst(strtolower($exam->status)) }}</span></td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <a href="{{ route('admin.exams.builder', $exam) }}" class="btn btn-outline btn-sm" style="color:#4f46e5;border-color:#c7d2fe;font-weight:600">
                                    <i class="fa-solid fa-puzzle-piece"></i> Paper Builder
                                </a>
                                <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline btn-sm">Inspect Exam</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No exams scheduled yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Exam Modal -->
    <div class="modal-overlay" id="addExamModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Schedule New Exam</span>
                <button class="modal-close" onclick="closeModal('addExamModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.exams.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Subject <span class="required">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">-- Choose Subject --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->code }}: {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Exam Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Midterm Examination 2026" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Exam Type <span class="required">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="FINAL">FINAL</option>
                                <option value="MIDTERM">MIDTERM</option>
                                <option value="RETAKE">RETAKE</option>
                                <option value="QUIZ">QUIZ</option>
                                <option value="PRACTICAL">PRACTICAL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>শুরুর তারিখ (Start Date) <span class="required">*</span></label>
                            <input type="date" name="exam_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>শেষের তারিখ (End Date)</label>
                            <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>সময়কাল (মিনিট) <span class="required">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control" value="60" min="5" max="360" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>শুরুর সময় (Start Time)</label>
                            <input type="time" name="start_time" class="form-control" value="10:00">
                        </div>
                        <div class="form-group">
                            <label>শেষের সময় (End Time)</label>
                            <input type="time" name="end_time" class="form-control" value="12:00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Marks <span class="required">*</span></label>
                            <input type="number" name="full_marks" class="form-control" value="100" required>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" class="form-control" value="40" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>নেগেটিভ মার্কিং (MCQ প্রতি ভুল উত্তরের জন্য কর্তন)</label>
                        <input type="number" step="0.25" name="negative_marking" class="form-control" value="0.00" min="0" max="5">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addExamModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Exam</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
