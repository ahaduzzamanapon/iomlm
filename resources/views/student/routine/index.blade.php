<x-student-layout>
    <x-slot name="title">My Routine</x-slot>

    <style>
        .routine-grid { width:100%; border-collapse:collapse; table-layout:fixed; font-size:12px; }
        .routine-grid th, .routine-grid td { border:1px solid #e2e8f0; padding:0; vertical-align:top; }
        .routine-grid th { background:#f8fafc; font-weight:600; color:#64748b; padding:8px; text-align:center; }
        .slot-header { background:#1e1b4b !important; color:#fff !important; font-size:11px; min-width:120px; width:150px; }
        .weekend-header { background:#fef3c7 !important; color:#92400e !important; }
        .cell-wrapper { min-height:80px; padding:5px; display:flex; flex-direction:column; gap:4px; }
        .cell-weekend { background:repeating-linear-gradient(45deg,#fef9c3,#fef9c3 4px,#fefce8 4px,#fefce8 8px); }
        .entry-pill { border-radius:6px; padding:6px 8px; font-size:11px; font-weight:600; color:#fff; }
        .entry-pill .pill-title { font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .entry-pill .pill-sub { font-size:10px; opacity:.85; margin-top:2px; }
        .today-col { background:#eff6ff !important; }
    </style>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Weekly Class Routine</h1>
            <p>Your batch's scheduled weekly classes at a glance</p>
        </div>
    </div>

    @if($slots->isEmpty())
        <div class="card" style="padding:40px;text-align:center;color:var(--text-muted)">
            <p>No routine has been set up by admin yet. Check back soon!</p>
        </div>
    @else
    @php $todayDow = strtoupper(substr(now()->format('D'), 0, 3)); @endphp
    <div class="card" style="padding:0;overflow:hidden">
        <div style="overflow-x:auto">
            <table class="routine-grid">
                <thead>
                    <tr>
                        <th class="slot-header">সময়</th>
                        @foreach($days as $d)
                            <th class="{{ in_array($d, $weekends) ? 'weekend-header' : '' }} {{ $d === $todayDow ? 'today-col' : '' }}" style="{{ $d === $todayDow ? 'background:#dbeafe!important;color:#1d4ed8!important;' : '' }}">
                                {{ $d }}
                                @if($d === $todayDow)<span style="font-size:9px;display:block;font-weight:700">আজ</span>@endif
                                @if(in_array($d, $weekends))<span style="font-size:9px;display:block;opacity:.7">ছুটি</span>@endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($slots as $slot)
                    <tr>
                        <td style="background:#1e1b4b;padding:10px 12px;vertical-align:middle">
                            <div style="color:#fff;font-weight:600;font-size:12px">{{ $slot->name }}</div>
                            <div style="color:#a5b4fc;font-size:10px">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</div>
                        </td>
                        @foreach($days as $day)
                            @php
                                $cellEntries = $entries[$slot->id][$day] ?? collect();
                                $isWeekend   = in_array($day, $weekends);
                                $isToday     = $day === $todayDow;
                            @endphp
                            <td style="{{ $isToday ? 'background:#eff6ff;' : '' }}">
                                <div class="cell-wrapper {{ $isWeekend ? 'cell-weekend' : '' }}">
                                    @forelse($cellEntries as $entry)
                                        @php $color = $entry->color ?: '#8b5cf6'; @endphp
                                        <div class="entry-pill" style="background:{{ $color }}">
                                            <div class="pill-title">{{ $entry->title ?: ($entry->subject?->code ?? '—') }}</div>
                                            <div class="pill-sub">{{ $entry->teacher?->name ?? 'TBA' }}</div>
                                            @if($isToday && isset($todaySessions[$entry->id]))
                                            <div class="pill-sub" style="margin-top:4px">
                                                <a href="{{ $todaySessions[$entry->id]->meeting_link }}" target="_blank" style="color:#fff;background:rgba(255,255,255,.2);border-radius:3px;padding:2px 6px;text-decoration:none;font-size:10px">Join Class</a>
                                            </div>
                                            @endif
                                        </div>
                                    @empty
                                        @if(!$isWeekend)<div style="padding:8px;text-align:center;color:#d1d5db;font-size:20px">·</div>@endif
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
</x-student-layout>
