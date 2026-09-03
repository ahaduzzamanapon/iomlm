<x-teacher-layout>
    <x-slot name="title">Exams Management</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>📝 Examinations &amp; Question Builder</h1>
            <p>Create &amp; manage 4 types of exams: Class Quiz, Class Test, Half-Term, &amp; Final Exam</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('createExamModal')">
                + Schedule New Exam
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px">
            <strong>⚠️ কিছু ত্রুটি পাওয়া গেছে:</strong>
            <ul style="margin:6px 0 0 16px;padding:0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Exams Table --}}
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title &amp; Subject</th>
                        <th>Exam Type</th>
                        <th>Marks / Negative</th>
                        <th>Duration</th>
                        <th>Questions</th>
                        <th>Status</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $ex)
                    @php
                        $typeBadge = match($ex->type) {
                            'QUIZ' => 'badge-secondary',
                            'CLASS_TEST' => 'badge-info',
                            'HALF_TERM' => 'badge-warning',
                            'FINAL' => 'badge-danger',
                            default => 'badge-primary'
                        };
                    @endphp
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $ex->title }}</strong><br>
                            <span style="color:#64748b;font-size:12px">🎯 {{ $ex->subject?->name ?? '—' }} ({{ $ex->subject?->code }})</span>
                        </td>
                        <td>
                            <span class="badge {{ $typeBadge }} no-dot">{{ $ex->type }}</span>
                            @if($ex->type === 'QUIZ')
                                <div style="font-size:10px;color:#94a3b8;margin-top:2px">No GPA effect</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $ex->full_marks }} Marks</strong><br>
                            <small style="color:#e11d48">-{{ $ex->negative_marking }} per wrong</small>
                        </td>
                        <td>⏱️ {{ $ex->duration_minutes }} Mins</td>
                        <td>
                            <span class="badge badge-info no-dot">{{ $ex->examQuestions->count() }} Questions</span>
                        </td>
                        <td><span class="badge badge-success no-dot">{{ $ex->status }}</span></td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
                                <a href="{{ route('teacher.exams.show', $ex) }}" class="btn btn-outline btn-sm">
                                    🛠️ Paper Builder
                                </a>
                                <button type="button" class="btn btn-outline btn-sm"
                                    onclick="openEditExamModal({{ json_encode($ex) }})">
                                    ✏️ Edit
                                </button>
                                @if($ex->examQuestions->contains(fn($eq) => $eq->question?->question_type === 'WRITTEN'))
                                    <a href="{{ route('teacher.exams.grade', $ex) }}" class="btn btn-outline btn-sm" style="color:#9d174d;border-color:#f9a8d4">
                                        ✏️ Grade
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">
                            No exams scheduled yet. Click "+ Schedule New Exam" above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Exam Modal --}}
    <div class="modal-overlay" id="createExamModal">
        <div class="modal" style="max-width:600px">
            <div class="modal-header">
                <span class="modal-title">+ Schedule Exam (4-Tier Architecture)</span>
                <button class="modal-close" onclick="closeModal('createExamModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('teacher.exams.store') }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Subject <span class="required">*</span></label>
                            <select name="subject_id" class="form-control" required>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Exam Type <span class="required">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="QUIZ">1. Class Quiz (অনিয়মিত কুইজ - No GPA effect)</option>
                                <option value="CLASS_TEST">2. Class Test (বিষয়ভিত্তিক ক্লাস টেস্ট)</option>
                                <option value="HALF_TERM">3. Half-Term Exam (মিডটার্ম পরীক্ষা)</option>
                                <option value="FINAL">4. Final Exam (ফাইনাল পরীক্ষা)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Exam Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 1 Class Test / Midterm 2026" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Exam Date <span class="required">*</span></label>
                            <input type="date" name="exam_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Start Date &amp; Time (Online Active)</label>
                            <input type="datetime-local" name="start_datetime" class="form-control">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Full Marks <span class="required">*</span></label>
                            <input type="number" name="full_marks" class="form-control" value="20" required>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" class="form-control" value="8" required>
                        </div>
                        <div class="form-group">
                            <label>Duration (Mins) <span class="required">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control" value="30" required>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Negative Marking Rate</label>
                            <input type="number" step="0.05" name="negative_marking" class="form-control" value="0.25" placeholder="0.25 per wrong answer">
                        </div>
                        <div class="form-group" style="display:flex;align-items:center;margin-top:24px">
                            <label class="form-check">
                                <input type="checkbox" name="is_anti_cheating" value="1" checked>
                                Enable Live Anti-Cheating 🔒
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createExamModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">💾 Create Exam &amp; Build Paper</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Exam Modal --}}
    <div class="modal-overlay" id="editExamModal">
        <div class="modal" style="max-width:600px">
            <div class="modal-header">
                <span class="modal-title">✏️ Edit Examination Details</span>
                <button class="modal-close" onclick="closeModal('editExamModal')">&times;</button>
            </div>
            <form method="POST" id="editExamForm">
                @csrf
                @method('PUT')
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Subject <span class="required">*</span></label>
                            <select name="subject_id" id="edit_exam_subject_id" class="form-control" required>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Exam Type <span class="required">*</span></label>
                            <select name="type" id="edit_exam_type" class="form-control" required>
                                <option value="QUIZ">1. Class Quiz (অনিয়মিত কুইজ)</option>
                                <option value="CLASS_TEST">2. Class Test (ক্লাস টেস্ট)</option>
                                <option value="HALF_TERM">3. Half-Term Exam (মিডটার্ম)</option>
                                <option value="FINAL">4. Final Exam (ফাইনাল)</option>
                                <option value="RETAKE">5. Retake Exam (পুনঃপরীক্ষা)</option>
                                <option value="PRACTICAL">6. Practical Exam (ব্যবহারিক)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Exam Title <span class="required">*</span></label>
                        <input type="text" name="title" id="edit_exam_title" class="form-control" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Exam Date <span class="required">*</span></label>
                            <input type="date" name="exam_date" id="edit_exam_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Start Date &amp; Time</label>
                            <input type="datetime-local" name="start_datetime" id="edit_exam_start_datetime" class="form-control">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Full Marks <span class="required">*</span></label>
                            <input type="number" name="full_marks" id="edit_exam_full_marks" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" id="edit_exam_pass_marks" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Duration (Mins) <span class="required">*</span></label>
                            <input type="number" name="duration_minutes" id="edit_exam_duration" class="form-control" required>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Negative Marking</label>
                            <input type="number" step="0.05" name="negative_marking" id="edit_exam_negative_marking" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="edit_exam_status" class="form-control">
                                <option value="SCHEDULED">SCHEDULED</option>
                                <option value="RUNNING">RUNNING</option>
                                <option value="COMPLETED">COMPLETED</option>
                                <option value="CANCELLED">CANCELLED</option>
                            </select>
                        </div>
                        <div class="form-group" style="display:flex;align-items:center;margin-top:24px">
                            <label class="form-check">
                                <input type="checkbox" name="is_anti_cheating" id="edit_exam_anti_cheating" value="1">
                                Anti-Cheating 🔒
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editExamModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditExamModal(exam) {
        document.getElementById('editExamForm').action = '/teacher/exams/' + exam.id;
        document.getElementById('edit_exam_subject_id').value = exam.subject_id;
        document.getElementById('edit_exam_type').value = exam.type;
        document.getElementById('edit_exam_title').value = exam.title;
        document.getElementById('edit_exam_date').value = exam.exam_date ? exam.exam_date.substring(0, 10) : '';
        document.getElementById('edit_exam_start_datetime').value = exam.start_datetime ? exam.start_datetime.substring(0, 16) : '';
        document.getElementById('edit_exam_full_marks').value = exam.full_marks;
        document.getElementById('edit_exam_pass_marks').value = exam.pass_marks;
        document.getElementById('edit_exam_duration').value = exam.duration_minutes;
        document.getElementById('edit_exam_negative_marking').value = exam.negative_marking || 0;
        document.getElementById('edit_exam_status').value = exam.status || 'SCHEDULED';
        document.getElementById('edit_exam_anti_cheating').checked = Boolean(exam.is_anti_cheating);

        openModal('editExamModal');
    }
    </script>
    @endpush
</x-teacher-layout>
