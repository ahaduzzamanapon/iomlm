<x-admin-layout>
    <x-slot name="title">Assign Unscheduled Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px"><a href="{{ route('admin.routine.index') }}">← Back to Routine</a></div>
            <h1>Assign Classes to Routine</h1>
            <p>Select a batch to see unassigned class sessions, then assign them to a routine slot</p>
        </div>
    </div>

    {{-- Batch Filter --}}
    <div class="card" style="padding:14px 16px;margin-bottom:16px">
        <form method="GET" action="{{ route('admin.routine.unassigned') }}" style="display:flex;gap:8px;align-items:center">
            <select name="batch_id" class="form-control" style="min-width:240px">
                <option value="">Select a Batch</option>
                @foreach($batches as $b)
                    <option value="{{ $b->id }}" {{ $batchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </form>
    </div>

    @if($batchId)
        @if($unassigned->isEmpty())
            <div class="card" style="padding:30px;text-align:center;color:var(--text-muted)">
                All scheduled classes for this batch are already assigned to routine slots.
            </div>
        @else
        <div class="card">
            <div class="card-header">
                <span class="card-title">Unassigned Classes ({{ $unassigned->count() }})</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Subject & Module</th>
                            <th>Teacher</th>
                            <th>Current Date</th>
                            <th>Status</th>
                            <th>Assign to Slot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unassigned as $cs)
                        <tr>
                            <td class="td-primary">
                                <strong>{{ $cs->subject?->name ?? '—' }}</strong><br>
                                <span class="td-muted">{{ $cs->subject?->code ?? '' }}</span>
                            </td>
                            <td class="td-muted">{{ $cs->teacher?->name ?? '—' }}</td>
                            <td class="td-muted">{{ $cs->session_date?->format('d M Y') ?? 'TBA' }}</td>
                            <td><span class="badge badge-secondary no-dot">{{ $cs->status }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.routine.entries.store') }}" style="display:flex;gap:6px;align-items:center">
                                    @csrf
                                    <input type="hidden" name="batch_id" value="{{ $batchId }}">
                                    <input type="hidden" name="class_session_id" value="{{ $cs->id }}">
                                    <input type="hidden" name="subject_id" value="{{ $cs->subject_id }}">
                                    <input type="hidden" name="teacher_id" value="{{ $cs->teacher_id }}">
                                    <select name="slot_id" class="form-control" style="min-width:140px" required>
                                        <option value="">Slot</option>
                                        @foreach($slots as $sl)
                                            <option value="{{ $sl->id }}">{{ $sl->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="day_of_week" class="form-control" style="min-width:80px" required>
                                        @foreach($days as $d)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endif
</x-admin-layout>
