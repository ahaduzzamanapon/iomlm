<x-admin-layout>
    <x-slot name="title">Subject Retakes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Subject Retake Engine</h1>
            <p>Register retakes for failed subjects (EXAM_ONLY, CLASS_EXAM, FULL_RESTART)</p>
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
                        <th>Registered Date</th>
                        <th>Status</th>
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
                        <td class="td-muted">{{ $ret->created_at->format('d M Y') }}</td>
                        <td><span class="badge badge-{{ strtolower($ret->status) }}">{{ ucfirst(strtolower($ret->status)) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No subject retakes registered yet.</td></tr>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addRetakeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Retake</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
