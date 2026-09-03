<x-teacher-layout>
    <x-slot name="title">My Routine</x-slot>

    <style>
        .routine-grid { width:100%; border-collapse:collapse; table-layout:fixed; font-size:12px; }
        .routine-grid th, .routine-grid td { border:1px solid #e2e8f0; padding:0; vertical-align:top; }
        .routine-grid th { background:#f8fafc; font-weight:600; color:#64748b; padding:8px; text-align:center; }
        .slot-header { background:#064e3b !important; color:#fff !important; font-size:11px; min-width:120px; width:150px; }
        .weekend-header { background:#fef3c7 !important; color:#92400e !important; }
        .cell-wrapper { min-height:80px; padding:5px; display:flex; flex-direction:column; gap:4px; }
        .cell-weekend { background:repeating-linear-gradient(45deg,#fef9c3,#fef9c3 4px,#fefce8 4px,#fefce8 8px); }
        .entry-pill { border-radius:5px; padding:5px 8px; font-size:11px; font-weight:600; color:#fff; background:#10b981; }
        .entry-pill.override { outline:2px solid #ef4444; background:#ef4444 !important; }
        .entry-pill .pill-title { font-weight:700; }
        .entry-pill .pill-sub { font-size:10px; opacity:.85; margin-top:2px; }
    </style>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Weekly Routine</h1>
            <p>Your personal class schedule for the week</p>
        </div>
    </div>

    @if($slots->isEmpty())
        <div class="card" style="padding:40px;text-align:center;color:var(--text-muted)">
            <p>No time slots have been configured by admin yet.</p>
        </div>
    @else
    <div class="card" style="padding:0;overflow:hidden">
        <div style="overflow-x:auto">
            <table class="routine-grid">
                <thead>
                    <tr>
                        <th class="slot-header">Time Slot</th>
                        @foreach($days as $d)
                            <th class="{{ in_array($d, $weekends) ? 'weekend-header' : '' }}">
                                {{ $d }}
                                @if(in_array($d, $weekends))<span style="font-size:9px;display:block;opacity:.7">Weekend</span>@endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($slots as $slot)
                    <tr>
                        <td style="background:#064e3b;padding:10px 12px;vertical-align:middle">
                            <div style="color:#fff;font-weight:600;font-size:12px">{{ $slot->name }}</div>
                            <div style="color:#6ee7b7;font-size:10px">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</div>
                        </td>
                        @foreach($days as $day)
                            @php $cellEntries = $entries[$slot->id][$day] ?? collect(); $isWeekend = in_array($day, $weekends); @endphp
                            <td>
                                <div class="cell-wrapper {{ $isWeekend ? 'cell-weekend' : '' }}">
                                    @forelse($cellEntries as $entry)
                                        <div class="entry-pill {{ $entry->is_override ? 'override' : '' }}">
                                            @if($entry->is_override)<span style="font-size:10px">Override &nbsp;</span>@endif
                                            <div class="pill-title">{{ $entry->title ?: ($entry->subject?->code ?? '—') }}</div>
                                            <div class="pill-sub">{{ $entry->batch?->name ?? '—' }}</div>
                                            @if(isset($todaySessions[$entry->id]))
                                            <div class="pill-sub"><a href="{{ $todaySessions[$entry->id]->meeting_link }}" target="_blank" style="color:#a7f3d0;text-decoration:none">Join Meet</a></div>
                                            @endif
                                        </div>
                                    @empty
                                        @if(!$isWeekend)<div style="padding:8px;text-align:center;color:#d1d5db;font-size:18px">·</div>@endif
                                    @endforelse
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-teacher-layout>
