<x-admin-layout>
    <x-slot name="title">Semester Promotions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Semester Promotion & Progression</h1>
            <p>Review student academic standing and record promotions or force promotions</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addPromotionModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Promote Student
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>From Batch</th>
                        <th>To Batch</th>
                        <th>Decision</th>
                        <th>Promoted On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $prom)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $prom->student->name ?? '—' }}</strong><br>
                            <span class="td-muted">{{ $prom->student->student_code ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $prom->fromBatch->name ?? '—' }}</td>
                        <td><strong>{{ $prom->toBatch->name ?? '—' }}</strong></td>
                        <td>
                            @if($prom->decision === 'PROMOTED')
                                <span class="badge badge-active">Promoted</span>
                            @elseif($prom->decision === 'FORCE_PROMOTED')
                                <span class="badge badge-rescheduled">⚡ Force Promoted</span>
                            @else
                                <span class="badge badge-cancelled">Held Back</span>
                            @endif
                        </td>
                        <td class="td-muted">{{ $prom->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No promotion records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Promotion Modal -->
    <div class="modal-overlay" id="addPromotionModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Record Student Promotion</span>
                <button class="modal-close" onclick="closeModal('addPromotionModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.promotions.store') }}">
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

                    <div class="form-row">
                        <div class="form-group">
                            <label>From Batch <span class="required">*</span></label>
                            <select name="from_batch_id" class="form-control" required>
                                <option value="">-- Current Batch --</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>To Batch / Next Semester <span class="required">*</span></label>
                            <select name="to_batch_id" class="form-control" required>
                                <option value="">-- Target Batch --</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Promotion Decision <span class="required">*</span></label>
                        <select name="decision" class="form-control" required>
                            <option value="PROMOTED">PROMOTED — Passed all prerequisites</option>
                            <option value="FORCE_PROMOTED">FORCE_PROMOTED — Admin override pass</option>
                            <option value="HELD_BACK">HELD_BACK — Must repeat semester</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Audit notes for promotion decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addPromotionModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Promotion</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
