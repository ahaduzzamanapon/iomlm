<x-teacher-layout>
    <x-slot name="title">{{ $assignment->title }} — Submissions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.assignments.index') }}"><i class="fa-solid fa-arrow-left"></i> সকল অ্যাসাইনমেন্টে ফিরে যান</a>
            </div>
            <h1 style="font-family:'Kalpurush', sans-serif">{{ $assignment->title }}</h1>
            <p>
                বিষয়: <strong>{{ $assignment->subject?->name }} ({{ $assignment->subject?->code }})</strong> &middot;
                ব্যাচ: <strong>{{ $assignment->batch?->name ?? 'সকল ব্যাচ' }}</strong> &middot;
                পূর্ণমান: <strong>{{ $assignment->total_marks }}</strong> নম্বর &middot;
                ডেডলাইন: <strong>{{ \Carbon\Carbon::parse($assignment->due_datetime)->format('d M Y, h:i A') }}</strong>
            </p>
        </div>
        <div class="page-header-actions">
            @if($assignment->file_path)
                <a href="{{ asset('storage/' . $assignment->file_path) }}" target="_blank" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-download"></i> প্রশ্ন ফাইল ডাউনলোড
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;margin-bottom:20px">
        <div class="card" style="padding:16px;text-align:center">
            <span style="font-size:12px;color:#64748b">মোট সাবমিশন</span>
            <div style="font-size:24px;font-weight:800;color:var(--blue);margin-top:4px">{{ $assignment->submissions->count() }}টি</div>
        </div>
        <div class="card" style="padding:16px;text-align:center">
            <span style="font-size:12px;color:#64748b">মূল্যায়িত (Graded)</span>
            <div style="font-size:24px;font-weight:800;color:#059669;margin-top:4px">{{ $assignment->submissions->where('status', 'GRADED')->count() }}টি</div>
        </div>
        <div class="card" style="padding:16px;text-align:center">
            <span style="font-size:12px;color:#64748b">মূল্যায়ন বাকি (Pending)</span>
            <div style="font-size:24px;font-weight:800;color:#d97706;margin-top:4px">{{ $assignment->submissions->whereIn('status', ['SUBMITTED', 'LATE'])->count() }}টি</div>
        </div>
    </div>

    {{-- Submissions Table --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span class="card-title">শিক্ষার্থীদের জমাকৃত খাতা</span>
            <span class="badge badge-secondary no-dot">{{ $assignment->submissions->count() }} জন জমা দিয়েছেন</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>শিক্ষার্থী</th>
                        <th>আইডি</th>
                        <th>জমাকৃত খাতা</th>
                        <th>নোট</th>
                        <th>জমার সময়</th>
                        <th>প্রাপ্ত নম্বর</th>
                        <th>ফিডব্যাক</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignment->submissions as $sub)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $sub->student?->name }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-secondary no-dot" style="font-family:monospace;font-weight:700">
                                {{ $sub->student?->student_code }}
                            </span>
                        </td>
                        <td>
                            @if($sub->submission_file)
                                <a href="{{ asset('storage/' . $sub->submission_file) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:4px 8px;font-size:11px">
                                    <i class="fa-solid fa-file-arrow-down" style="color:#059669"></i> খাতা দেখুন
                                </a>
                            @else
                                <span class="td-muted">ফাইল নেই</span>
                            @endif
                        </td>
                        <td style="max-width:180px;font-size:12px;color:#475569">
                            {{ $sub->student_note ?: '—' }}
                        </td>
                        <td style="font-size:12px" class="td-muted">
                            {{ \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y, h:i A') }}
                        </td>
                        <td>
                            @if($sub->status === 'GRADED')
                                <strong style="font-size:14px;color:#059669">{{ $sub->obtained_marks }}</strong> / {{ $assignment->total_marks }}
                            @else
                                <span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px">বাকি</span>
                            @endif
                        </td>
                        <td style="max-width:160px;font-size:12px;color:#64748b">
                            {{ $sub->teacher_feedback ?: '—' }}
                        </td>
                        <td>
                            @if($sub->status === 'GRADED')
                                <span class="badge badge-active">Graded</span>
                            @elseif($sub->status === 'LATE')
                                <span class="badge" style="background:#fee2e2;color:#991b1b">Late</span>
                            @else
                                <span class="badge badge-scheduled">Submitted</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <button type="button" class="btn btn-primary btn-sm" onclick='openGradeModal(@json($sub))' title="নম্বর দিন">
                                <i class="fa-solid fa-check"></i> মার্কিং
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick='openOverrideModal(@json($sub))' title="ওভাররাইড" style="color:#6366f1;border-color:#c7d2fe">
                                <i class="fa-solid fa-pen-nib"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">
                            এখনো কোনো শিক্ষার্থী জমা দেয়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Grade Modal --}}
    <div class="modal-overlay" id="gradeModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">খাতা মূল্যায়ন ও নম্বর প্রদান</span>
                <button class="modal-close" onclick="closeModal('gradeModal')">&times;</button>
            </div>
            <form method="POST" id="gradeForm" action="">
                @csrf
                <div class="modal-body">
                    <div style="background:#f8fafc;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px">
                        শিক্ষার্থী: <strong id="gm_student_name"></strong> &middot; পূর্ণমান: <strong>{{ $assignment->total_marks }}</strong>
                    </div>
                    <div class="form-group">
                        <label>প্রাপ্ত নম্বর <span class="required">*</span></label>
                        <input type="number" step="0.5" name="obtained_marks" id="gm_obtained_marks" class="form-control" max="{{ $assignment->total_marks }}" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>মন্তব্য ও ফিডব্যাক</label>
                        <textarea name="teacher_feedback" id="gm_teacher_feedback" class="form-control" rows="3" placeholder="শিক্ষার্থীর জন্য নির্দেশনা..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('gradeModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">নম্বর সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Override Modal --}}
    <div class="modal-overlay" id="overrideModal">
        <div class="modal" style="max-width:550px">
            <div class="modal-header">
                <span class="modal-title">সাবমিশন ওভাররাইড</span>
                <button class="modal-close" onclick="closeModal('overrideModal')">&times;</button>
            </div>
            <form method="POST" id="overrideForm" action="" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>স্ট্যাটাস</label>
                        <select name="status" id="om_status" class="form-control">
                            <option value="SUBMITTED">SUBMITTED</option>
                            <option value="GRADED">GRADED</option>
                            <option value="LATE">LATE</option>
                            <option value="REJECTED">REJECTED</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>প্রাপ্ত নম্বর</label>
                        <input type="number" step="0.5" name="obtained_marks" id="om_obtained_marks" class="form-control" max="{{ $assignment->total_marks }}" min="0">
                    </div>
                    <div class="form-group">
                        <label>নতুন খাতা ফাইল (ঐচ্ছিক)</label>
                        <input type="file" name="submission_file" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>নোট</label>
                        <textarea name="student_note" id="om_student_note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>ফিডব্যাক</label>
                        <textarea name="teacher_feedback" id="om_teacher_feedback" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('overrideModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">ওভাররাইড সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openGradeModal(sub) {
        document.getElementById('gradeForm').action = '/teacher/assignment-submissions/' + sub.id + '/grade';
        document.getElementById('gm_student_name').innerText = sub.student ? sub.student.name : 'শিক্ষার্থী';
        document.getElementById('gm_obtained_marks').value = sub.obtained_marks || '';
        document.getElementById('gm_teacher_feedback').value = sub.teacher_feedback || '';
        openModal('gradeModal');
    }

    function openOverrideModal(sub) {
        document.getElementById('overrideForm').action = '/teacher/assignment-submissions/' + sub.id + '/override';
        document.getElementById('om_status').value = sub.status;
        document.getElementById('om_obtained_marks').value = sub.obtained_marks || '';
        document.getElementById('om_student_note').value = sub.student_note || '';
        document.getElementById('om_teacher_feedback').value = sub.teacher_feedback || '';
        openModal('overrideModal');
    }
    </script>
    @endpush
</x-teacher-layout>
