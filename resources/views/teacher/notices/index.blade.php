<x-teacher-layout>
    <x-slot name="title">Notice Board — Teacher Portal</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Notice Board & Announcements</h1>
            <p>Official notices, schedules, exam circulars, and announcements for teachers</p>
        </div>
    </div>

    {{-- Notices List --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        @forelse($notices as $n)
        @php
            $priorityStyle = match($n->priority) {
                'URGENT'    => 'border-left:5px solid #e11d48;background:#fff5f5',
                'IMPORTANT' => 'border-left:5px solid #f59e0b;background:#fffbeb',
                default     => 'border-left:5px solid #10b981;background:#fff'
            };
        @endphp
        <div class="card" style="{{ $priorityStyle }}">
            <div style="padding:20px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px">
                    <div>
                        <span class="badge {{ $n->priority === 'URGENT' ? 'badge-danger' : ($n->priority === 'IMPORTANT' ? 'badge-warning' : 'badge-active') }} no-dot" style="font-size:10px">
                            {{ $n->priority }}
                        </span>
                        <span class="badge badge-secondary no-dot" style="font-size:10px;margin-left:4px">
                            Audience: {{ $n->target_audience }}
                        </span>
                        @if($n->batch)
                            <span class="badge badge-primary no-dot" style="font-size:10px;margin-left:4px">
                                Batch: {{ $n->batch->name }}
                            </span>
                        @endif
                        @if($n->semester)
                            <span class="badge badge-active no-dot" style="font-size:10px;margin-left:4px;background:#10b981">
                                Semester: {{ $n->semester->name }}
                            </span>
                        @endif
                        <h3 style="font-size:16px;font-weight:700;margin:8px 0 3px;color:#0f172a">{{ $n->title }}</h3>
                        <div style="font-size:11px;color:#64748b">
                            Published {{ $n->created_at->diffForHumans() }} ({{ $n->created_at->format('d M Y, h:i A') }}) by {{ $n->creator->name ?? 'Authority' }}
                        </div>
                    </div>
                </div>
                <div style="font-size:14px;color:#334155;line-height:1.6;white-space:pre-line;margin-top:10px;border-top:1px solid rgba(0,0,0,0.05);padding-top:10px">
                    {{ $n->content }}
                </div>
            </div>
        </div>
        @empty
        <div class="card" style="padding:40px;text-align:center;color:#94a3b8">
            <div style="font-size:32px;margin-bottom:8px"></div>
            কোনো নোটিশ পাওয়া যায়নি।
        </div>
        @endforelse
    </div>

    <div style="margin-top:20px">
        {{ $notices->links() }}
    </div>
</x-teacher-layout>
