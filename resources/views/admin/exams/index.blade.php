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
                        <td class="td-muted">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }} ({{ $exam->duration_minutes ?? 90 }}m)</td>
                        <td>{{ $exam->full_marks }} / {{ $exam->pass_marks }}</td>
                        <td><span class="badge badge-{{ strtolower($exam->status) }}">{{ ucfirst(strtolower($exam->status)) }}</span></td>
                        <td style="text-align:right">
                            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline btn-sm">Inspect Exam</a>
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
                            <label>Exam Date <span class="required">*</span></label>
                            <input type="date" name="exam_date" class="form-control" value="{{ date('Y-m-d') }}" required>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addExamModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Exam</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
