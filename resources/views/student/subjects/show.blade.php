<x-student-layout>
    <x-slot name="title">{{ $subject->name }} — Classroom</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('student.subjects.index') }}"><i class="fa-solid fa-arrow-left"></i> আমার সকল বিষয়ে ফিরে যান</a>
            </div>
            <h1 style="font-family:'Kalpurush', sans-serif">{{ $subject->name }}</h1>
            <p>
                কোড: <strong>{{ $subject->code }}</strong> &middot; ক্রেডিট: <strong>{{ $subject->credit }}</strong> &middot; পূর্ণমান: <strong>{{ $subject->full_marks }}</strong>
                @if($subject->category)
                    &middot; <span class="badge badge-secondary no-dot">{{ $subject->category->name }}</span>
                @endif
            </p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('student.assignments.index') }}" class="btn btn-outline">
                <i class="fa-solid fa-file-signature"></i> অ্যাসাইনমেন্ট দেখুন
            </a>
        </div>
    </div>

    @php
        $groupedMods = $subject->modules->groupBy(fn($m) => $m->folder_name ?: 'General');
        $firstRecordedMod = $subject->modules->first(fn($m) => !empty($m->embed_code) || !empty($m->recorded_url));
    @endphp

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px">

        {{-- Left: Modules & Recorded Video Player --}}
        <div>
            {{-- Recorded Class Video Player (if available) --}}
            @if($firstRecordedMod)
                <div class="card" style="margin-bottom:20px;overflow:hidden">
                    <div class="card-header" style="background:#0f172a;color:#fff;display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-circle-play" style="color:#f43f5e"></i>
                        <span style="font-weight:700">রেকর্ডেড ক্লাস প্লেয়ার (Recorded Class Video)</span>
                    </div>
                    <div style="background:#000;text-align:center;padding:10px" id="videoPlayerContainer">
                        @if($firstRecordedMod->embed_code)
                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden">
                                {!! $firstRecordedMod->embed_code !!}
                            </div>
                        @elseif($firstRecordedMod->recorded_url)
                            @php
                                $vidUrl = $firstRecordedMod->recorded_url;
                                if (str_contains($vidUrl, 'youtube.com/watch?v=')) {
                                    $vidId = explode('v=', $vidUrl)[1] ?? '';
                                    $vidId = explode('&', $vidId)[0];
                                    $embedSrc = "https://www.youtube.com/embed/{$vidId}";
                                } elseif (str_contains($vidUrl, 'youtu.be/')) {
                                    $vidId = explode('youtu.be/', $vidUrl)[1] ?? '';
                                    $embedSrc = "https://www.youtube.com/embed/{$vidId}";
                                } else {
                                    $embedSrc = $vidUrl;
                                }
                            @endphp
                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden">
                                <iframe src="{{ $embedSrc }}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe>
                            </div>
                        @endif
                    </div>
                    <div style="padding:12px 16px;background:#f8fafc;font-size:13px;display:flex;justify-content:space-between;align-items:center">
                        <strong id="nowPlayingTitle" style="color:#1e293b">{{ $firstRecordedMod->title }}</strong>
                        <span class="badge badge-secondary no-dot">রেকর্ডেড লেকচার</span>
                    </div>
                </div>
            @endif

            {{-- Sequential Learning Modules by Folder --}}
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fa-solid fa-layer-group" style="color:#6366f1"></i> শিক্ষাক্রম ও ধারাবাহিক মডিউল (Curriculum Modules)</span>
                    <span class="badge badge-secondary no-dot">{{ $subject->modules->count() }}টি মডিউল</span>
                </div>
                <div style="padding:16px">
                    @forelse($groupedMods as $folder => $mods)
                        <div style="margin-bottom:20px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
                            <div style="background:#f1f5f9;padding:10px 14px;font-weight:700;font-size:13px;color:#334155;display:flex;align-items:center;gap:6px">
                                <i class="fa-solid fa-folder-open" style="color:#6366f1"></i> {{ $folder }}
                            </div>
                            <div style="padding:8px 12px;display:flex;flex-direction:column;gap:10px">
                                @foreach($mods as $mod)
                                <div style="background:#fff;border:1px solid #f1f5f9;border-radius:8px;padding:12px;display:flex;justify-content:space-between;align-items:center;gap:12px">
                                    <div style="display:flex;align-items:flex-start;gap:10px;flex:1">
                                        <div style="width:28px;height:28px;border-radius:50%;background:#e0e7ff;color:#4338ca;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0">
                                            {{ $mod->sequence_no }}
                                        </div>
                                        <div>
                                            <div style="font-weight:700;font-size:14px;color:#0f172a">{{ $mod->title }}</div>
                                            @if($mod->description)
                                                <div style="font-size:12px;color:#64748b;margin-top:2px">{{ $mod->description }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                                        @if($mod->file_path)
                                            <a href="{{ asset('storage/' . $mod->file_path) }}" target="_blank" class="btn btn-outline btn-sm" style="font-size:11px">
                                                <i class="fa-solid fa-file-pdf" style="color:#ef4444"></i> লেকচার ফাইল
                                            </a>
                                        @endif

                                        @if($mod->drive_link)
                                            <a href="{{ $mod->drive_link }}" target="_blank" class="btn btn-outline btn-sm" style="font-size:11px;color:#047857;border-color:#a7f3d0">
                                                <i class="fa-brands fa-google-drive"></i> ড্রাইভ
                                            </a>
                                        @endif

                                        @if($mod->recorded_url || $mod->embed_code)
                                            <button type="button" class="btn btn-primary btn-sm" style="font-size:11px;background:#e11d48;border-color:#e11d48" onclick='playVideo(@json($mod))'>
                                                <i class="fa-solid fa-play"></i> ভিডিও দেখুন
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p style="text-align:center;color:#94a3b8;padding:20px">কোনো মডিউল পাওয়া যায়নি।</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Subject Assignments --}}
        <div>
            <div class="card">
                <div class="card-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                    <span class="card-title" style="font-size:13px"><i class="fa-solid fa-file-signature" style="color:#059669"></i> বিষয়ের অ্যাসাইনমেন্টসমূহ</span>
                </div>
                <div style="padding:14px;display:flex;flex-direction:column;gap:12px">
                    @forelse($subject->assignments as $asgn)
                    @php
                        $subm = $asgn->submissions->first();
                    @endphp
                    <div style="background:#fafafa;border:1px solid #e2e8f0;border-radius:8px;padding:12px">
                        <div style="font-weight:700;font-size:13px;color:#1e293b;margin-bottom:4px">
                            <a href="{{ route('student.assignments.show', $asgn) }}" style="text-decoration:none;color:#1e293b">
                                {{ $asgn->title }}
                            </a>
                        </div>
                        <div style="font-size:11px;color:#64748b;margin-bottom:8px">
                            পূর্ণমান: <strong>{{ $asgn->total_marks }}</strong> নম্বর &middot; শেষ সময়: {{ \Carbon\Carbon::parse($asgn->due_datetime)->format('d M') }}
                        </div>

                        <div style="display:flex;justify-content:space-between;align-items:center">
                            @if($subm)
                                @if($subm->status === 'GRADED')
                                    <span class="badge badge-active no-dot" style="font-size:11px">প্রাপ্ত: {{ $subm->obtained_marks }}/{{ $asgn->total_marks }}</span>
                                @elseif($subm->status === 'LATE')
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:11px">Late Submitted</span>
                                @else
                                    <span class="badge badge-scheduled no-dot" style="font-size:11px">জমাকৃত</span>
                                @endif
                            @else
                                <span class="badge" style="background:#fef3c7;color:#92400e;font-size:11px">জমা বাকি</span>
                            @endif

                            <a href="{{ route('student.assignments.show', $asgn) }}" class="btn btn-primary btn-sm" style="font-size:11px;padding:3px 8px">
                                {{ $subm ? 'বিস্তারিত দেখুন' : 'জমা দিন' }}
                            </a>
                        </div>
                    </div>
                    @empty
                        <p style="font-size:12px;color:#94a3b8;text-align:center;padding:10px">এই বিষয়ের জন্য কোনো অ্যাসাইনমেন্ট নেই।</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function playVideo(mod) {
        const container = document.getElementById('videoPlayerContainer');
        const titleEl   = document.getElementById('nowPlayingTitle');
        if (!container) return;

        if (titleEl) titleEl.innerText = mod.title;

        if (mod.embed_code) {
            container.innerHTML = '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden">' + mod.embed_code + '</div>';
        } else if (mod.recorded_url) {
            let src = mod.recorded_url;
            if (src.includes('youtube.com/watch?v=')) {
                let vidId = src.split('v=')[1].split('&')[0];
                src = 'https://www.youtube.com/embed/' + vidId;
            } else if (src.includes('youtu.be/')) {
                let vidId = src.split('youtu.be/')[1].split('?')[0];
                src = 'https://www.youtube.com/embed/' + vidId;
            }
            container.innerHTML = '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden"><iframe src="' + src + '" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen></iframe></div>';
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    </script>
    @endpush
</x-student-layout>
