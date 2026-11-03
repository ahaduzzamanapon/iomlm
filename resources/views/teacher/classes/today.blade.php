<x-teacher-layout>
    <x-slot name="title">Today's Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Today's Classes</h1>
            <p>{{ \Carbon\Carbon::parse($today)->format('l, d F Y') }}</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('teacher.classes.index') }}" class="btn btn-outline btn-sm">← All Classes</a>
        </div>
    </div>

    @if($sessions->isEmpty())
        <div class="card" style="padding:40px;text-align:center">
            <div style="font-size:40px;margin-bottom:12px"></div>
            <h3 style="color:var(--text-muted)">No classes scheduled for today.</h3>
            <p style="color:var(--text-muted);font-size:13px">Enjoy your day off!</p>
        </div>
    @else
    <div style="display:flex;flex-direction:column;gap:12px">
        @foreach($sessions as $cs)
        <div class="card" style="border-left:4px solid {{ $cs->routineEntry?->color ?? '#3b82f6' }}">
            <div style="padding:16px 20px">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
                    <div>
                        <div style="font-size:16px;font-weight:700">{{ $cs->subject?->name ?? '—' }}</div>
                        <div style="color:var(--text-muted);font-size:12px;margin-top:2px">
                            {{ $cs->batch?->name ?? '—' }} &middot;
                            {{ $cs->routineEntry?->slot?->name ?? '' }}
                            @if($cs->start_time) · {{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }} @endif
                            &middot; {{ $cs->teacher?->name ?? 'শিক্ষক' }}
                        </div>
                        @if($cs->moduleCovered)
                        <div style="font-size:11px;color:#6366f1;margin-top:4px">Module: {{ $cs->moduleCovered->title }}</div>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">

                        {{-- Meeting link area --}}
                        @if($cs->meeting_link)
                            <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">Join Class</a>
                            <button type="button" class="btn btn-outline btn-sm" style="font-size:11px"
                                onclick="document.getElementById('linkForm{{ $cs->id }}').style.display = document.getElementById('linkForm{{ $cs->id }}').style.display === 'none' ? 'flex' : 'none'">
                                লিংক এডিট
                            </button>
                        @elseif($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                            <form method="POST" action="{{ route('teacher.classes.setLink', $cs) }}" style="display:inline">
                                @csrf
                                <button class="btn btn-outline btn-sm" style="color:#2563eb" title="Auto Generate Zoom">
                                    <i class="fa-solid fa-bolt"></i> Auto Zoom
                                </button>
                            </form>
                            <button class="btn btn-outline btn-sm" style="color:#f59e0b"
                                onclick="document.getElementById('linkForm{{ $cs->id }}').style.display = document.getElementById('linkForm{{ $cs->id }}').style.display === 'none' ? 'flex' : 'none'">
                                লিংক পেস্ট
                            </button>
                        @endif

                        @if($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                        <form id="linkForm{{ $cs->id }}" method="POST"
                            action="{{ route('teacher.classes.setLink', $cs) }}"
                            style="display:none;gap:6px;align-items:center">
                            @csrf
                            <input type="text" name="meeting_link" class="form-control"
                                style="min-width:220px;font-size:11px"
                                value="{{ $cs->meeting_link }}"
                                placeholder="Zoom বা Google Meet লিংক..."
                                required>
                            <button type="submit" class="btn btn-primary btn-sm" style="font-size:11px">Save</button>
                        </form>
                        @endif

                        @if($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                            <a href="{{ route('teacher.classes.conduct', $cs) }}" class="btn btn-primary btn-sm">Conduct Class →</a>
                        @else
                            <span class="badge badge-success no-dot">COMPLETED</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-teacher-layout>
