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
            <h1 style="font-family:'Kalpurush',sans-serif">আমার সাপ্তাহিক রুটিন (My Weekly Routine)</h1>
            <p style="font-family:'Kalpurush',sans-serif">ব্যক্তিগত সাপ্তাহিক পাঠদান রুটিন ও রুটিনের বাইরের অতিরিক্ত ক্লাস</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('teacher.classes.index') }}" class="btn btn-primary btn-sm" style="background:#be123c;border-color:#be123c;font-family:'Kalpurush',sans-serif;font-weight:700">
                <i class="fa-solid fa-calendar-plus"></i> + এক্সট্রা ক্লাস শিডিউল করুন
            </a>
        </div>
    </div>

    {{-- Upcoming Extra Classes for Teacher --}}
    @if(isset($upcomingExtraClasses) && $upcomingExtraClasses->isNotEmpty())
        <div class="card" style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;padding:16px;margin-bottom:20px;font-family:'Kalpurush',sans-serif">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;flex-wrap:wrap">
                <div style="font-weight:700;font-size:14px;color:#9f1239;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-star" style="color:#e11d48"></i> আপনার নির্ধারিত এক্সট্রা ক্লাসসমূহ (Upcoming Extra Classes)
                </div>
                <span class="badge" style="background:#ffe4e6;color:#9f1239;font-size:11px">
                    {{ $upcomingExtraClasses->count() }}টি এক্সট্রা ক্লাস
                </span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:10px">
                @foreach($upcomingExtraClasses as $ec)
                <div style="background:#ffffff;border:1px solid #fbcfe8;border-radius:8px;padding:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;box-shadow:0 1px 2px rgba(0,0,0,0.03)">
                    <div>
                        <div style="font-weight:700;font-size:13.5px;color:#1e293b">{{ $ec->subject?->name ?? '—' }}</div>
                        <div style="font-size:12px;color:#be123c;font-weight:600;margin-top:2px">
                            <i class="fa-regular fa-calendar-check"></i> {{ $ec->session_date ? $ec->session_date->format('d M Y (D)') : '' }}
                            &middot; {{ $ec->start_time ? \Carbon\Carbon::parse($ec->start_time)->format('h:i A') : '' }}
                        </div>
                        <div style="font-size:11.5px;color:#64748b;margin-top:2px">{{ $ec->batch?->name }} ({{ $ec->group_label }})</div>
                        @if($ec->title)
                            <div style="font-size:11px;color:#9d174d;font-weight:600">{{ $ec->title }}</div>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('teacher.classes.conduct', $ec) }}" class="btn btn-sm btn-primary" style="background:#be123c;border-color:#be123c;font-size:11.5px;padding:5px 12px;white-space:nowrap">
                            ক্লাস পরিচালনা →
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif

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
                                            @if($entry->group_tag === 'MALE')
                                                <div style="margin-bottom:2px"><span style="background:#0284c7;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">ভাই শাখা</span></div>
                                            @elseif($entry->group_tag === 'FEMALE')
                                                <div style="margin-bottom:2px"><span style="background:#ec4899;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">বোন শাখা</span></div>
                                            @elseif($entry->group_tag === 'GROUP_A')
                                                <div style="margin-bottom:2px"><span style="background:#8b5cf6;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">গ্রুপ ক</span></div>
                                            @elseif($entry->group_tag === 'GROUP_B')
                                                <div style="margin-bottom:2px"><span style="background:#f59e0b;color:#fff;border-radius:3px;padding:1px 5px;font-size:9px;font-weight:700">গ্রুপ খ</span></div>
                                            @endif
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
