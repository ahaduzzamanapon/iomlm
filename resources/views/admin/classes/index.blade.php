<x-admin-layout>
    <x-slot name="title">Class Sessions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Class Sessions</h1>
            <p>Routine-based class schedule — per-class meeting links & attendance</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:14px 16px;margin-bottom:16px">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <form method="GET" action="{{ route('admin.classes.index') }}" id="filterForm" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <select name="batch_id" class="form-control" style="min-width:200px" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ $batchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" class="form-control" value="{{ $dateFilter }}" style="min-width:150px" onchange="document.getElementById('filterForm').submit()" title="Filter by date">
                @if($dateFilter)
                    <a href="{{ route('admin.classes.index', array_filter(['status' => $status ?: null, 'batch_id' => $batchId ?: null])) }}" class="btn btn-sm btn-ghost" title="Clear date">Clear Date</a>
                @endif
                <input type="hidden" name="status" value="{{ $status }}">
            </form>

            {{-- Status tabs --}}
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                @foreach(['' => 'All', 'SCHEDULED' => 'Scheduled', 'COMPLETED' => 'Completed', 'CANCELLED' => 'Cancelled'] as $val => $label)
                    <a href="{{ route('admin.classes.index', array_filter(['status' => $val ?: null, 'batch_id' => $batchId ?: null, 'date' => $dateFilter ?: null])) }}"
                       class="btn btn-sm {{ ($status ?? '') === $val ? 'btn-primary' : 'btn-outline' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">
                @if($dateFilter)
                    {{ \Carbon\Carbon::parse($dateFilter)->format('l, d M Y') }}
                    — {{ $classes->count() }} Session{{ $classes->count() !== 1 ? 's' : '' }}
                @else
                    {{ $classes->count() }} Session{{ $classes->count() !== 1 ? 's' : '' }}
                    @if($status) — {{ $status }} @endif
                @endif
            </span>
        </div>

        @if($classes->isEmpty())
            <div style="padding:40px;text-align:center;color:var(--text-muted)">
                <p>No class sessions found. <a href="{{ route('admin.batches.index') }}">Go to Batches</a> to generate sessions from routine.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Batch</th>
                        <th>Teacher</th>
                        <th>Date & Slot</th>
                        <th>Meeting Link</th>
                        <th>Module Covered</th>
                        <th>Attendance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $cs)
                    @php
                        $isPast = $cs->session_date && $cs->session_date->isPast();
                        $isToday = $cs->session_date && $cs->session_date->isToday();
                    @endphp
                    <tr style="{{ $isToday ? 'background:#eff6ff;' : '' }}">
                        <td class="td-primary">
                            <strong>{{ $cs->subject?->name ?? '—' }}</strong>
                            <div class="td-muted" style="font-size:10px">{{ $cs->subject?->code }}</div>
                        </td>
                        <td class="td-muted">{{ $cs->batch?->name ?? '—' }}</td>
                        <td class="td-muted">{{ $cs->teacher?->name ?? '—' }}</td>
                        <td>
                            <strong style="font-size:12px">
                                {{ $cs->session_date ? $cs->session_date->format('D, d M Y') : 'TBA' }}
                                @if($isToday) <span class="badge badge-success no-dot" style="font-size:9px">TODAY</span> @endif
                            </strong>
                            <div class="td-muted" style="font-size:10px">
                                {{ $cs->routineEntry?->slot?->name ?? '' }}
                                @if($cs->start_time) · {{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }} @endif
                            </div>
                        </td>
                        <td>
                            @if($cs->meeting_link)
                                <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-sm btn-outline" style="font-size:11px">Join</a>
                            @else
                                <form method="POST" action="{{ route('admin.classes.generateZoom', $cs) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline" style="font-size:11px;color:#2563eb" title="Generate Real Zoom Link">
                                        Generate Zoom Link
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:11px">
                            {{ $cs->moduleCovered?->title ?? '—' }}
                        </td>
                        <td style="text-align:center">
                            @php $attended = $cs->attendances->where('status','PRESENT')->count(); $total = $cs->attendances->count(); @endphp
                            @if($total > 0)
                                <span style="font-size:12px;font-weight:700;color:#10b981">{{ $attended }}/{{ $total }}</span>
                            @else
                                <span style="color:#d1d5db;font-size:12px">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match($cs->status) {
                                    'COMPLETED'  => 'badge-success',
                                    'SCHEDULED'  => 'badge-info',
                                    'CANCELLED'  => 'badge-danger',
                                    'UPCOMING'   => 'badge-warning',
                                    default      => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }} no-dot">{{ $cs->status }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.classes.show', $cs) }}" class="btn btn-sm btn-outline">Manage →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-admin-layout>
