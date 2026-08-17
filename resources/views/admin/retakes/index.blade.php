<x-admin-layout>
    <x-slot name="title">Subject Retakes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Subject Retake Engine</h1>
            <p>Register retakes for failed subjects. Admin sets the Retake Fee upon approval.</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addRetakeModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Subject Retake
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Retake Type</th>
                        <th>Retake Fee</th>
                        <th>Registered Date</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($retakes as $ret)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $ret->student->name ?? '—' }}</strong><br>
                            <span class="td-muted">{{ $ret->student->student_code ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $ret->subject->name ?? '—' }}</td>
                        <td>
                            @if($ret->retake_type === 'EXAM_ONLY')
                                <span class="badge badge-secondary no-dot">Exam Only</span>
                            @elseif($ret->retake_type === 'CLASS_EXAM')
                                <span class="badge badge-scheduled no-dot">Class + Exam</span>
                            @else
                                <span class="badge badge-cancelled no-dot">Full Subject Restart</span>
                            @endif
                        </td>
                        <td>
                            @if($ret->retake_fee)
                                <strong>৳{{ number_format($ret->retake_fee, 0) }}</strong>
                            @else
                                <span class="td-muted">Pending</span>
                            @endif
                        </td>
                        <td class="td-muted">{{ $ret->created_at->format('d M Y') }}</td>
                        <td><span class="badge badge-{{ strtolower($ret->status) }}">{{ ucfirst(strtolower($ret->status)) }}</span></td>
                        <td style="text-align:right">
                            @if($ret->status === 'PENDING')
                                <button class="btn btn-success btn-sm"
                                    onclick="openApproveRetake({{ $ret->id }}, @json($ret->student->name ?? ''), @json($ret->subject->name ?? ''))">
                                    ✓ Approve & Set Fee
                                </button>
                            @else
                                <span class="td-muted" style="font-size:12px">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No subject retakes registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Retake Modal -->
    <div class="modal-overlay" id="addRetakeModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Register Subject Retake</span>
                <button class="modal-close" onclick="closeModal('addRetakeModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.retakes.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Student <span class="required">*</span></label>
                        <select name="student_id" class="form-control" required>
                            <option value="">-- Choose Student --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}">{{ $st->student_code ?? 'N/A' }} — {{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Select Failed Subject <span class="required">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">-- Choose Subject --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->code }}: {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Retake Mode <span class="required">*</span></label>
                        <select name="retake_type" class="form-control" required>
                            <option value="EXAM_ONLY">EXAM_ONLY — Student sits exam only (no timeline change)</option>
                            <option value="CLASS_EXAM">CLASS_EXAM — Re-attend classes + sit exam</option>
                            <option value="FULL_RESTART">FULL_RESTART — Reset module progress + new timeline</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Admin notes for retake approval..."></textarea>
                    </div>
                    <div class="alert alert-info" style="margin-top:8px">
                        💡 Retake Fee will be set by admin at the time of approval.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addRetakeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Retake</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approve Retake Modal -->
    <div class="modal-overlay" id="approveRetakeModal">
        <div class="modal" style="max-width:450px">
            <div class="modal-header">
                <span class="modal-title">✓ Approve Retake & Set Fee</span>
                <button class="modal-close" onclick="closeModal('approveRetakeModal')">&times;</button>
            </div>
            <form method="POST" id="approveRetakeForm">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div style="background:#f8fafc;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px">
                        <strong id="ar_student"></strong> — <span id="ar_subject" style="color:var(--text-muted)"></span>
                    </div>
                    <div class="form-group">
                        <label>Retake Fee (৳ Taka) <span class="required">*</span></label>
                        <input type="number" name="retake_fee" class="form-control" min="0" placeholder="e.g. 1500" required>
                        <small style="color:var(--text-muted);font-size:12px">This amount will generate an invoice for the student.</small>
                    </div>
                    <div class="form-group">
                        <label>Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Approval notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveRetakeModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">✓ Approve & Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openApproveRetake(id, student, subject) {
        document.getElementById('approveRetakeForm').action = '/admin/retakes/' + id + '/approve';
        document.getElementById('ar_student').textContent = student;
        document.getElementById('ar_subject').textContent = subject;
        openModal('approveRetakeModal');
    }
    </script>
    @endpush
</x-admin-layout>
