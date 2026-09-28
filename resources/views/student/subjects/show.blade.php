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
        $firstRecordedMod = $subject->modules->first(fn($m) => $m->has_recorded_videos);
        $firstVideo = $firstRecordedMod ? ($firstRecordedMod->videos[0] ?? null) : null;
    @endphp

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;font-family:'Kalpurush', sans-serif">

        {{-- Left: Modules & Recorded Video Player --}}
        <div>
            {{-- Recorded Class Video Player (if available) --}}
            @if($firstRecordedMod && $firstVideo)
                <div class="card" id="videoPlayerCard" style="margin-bottom:20px;overflow:hidden;border:1px solid #cbd5e1;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1)">
                    <div class="card-header" style="background:#0f172a;color:#fff;display:flex;justify-content:space-between;align-items:center;padding:12px 16px;flex-wrap:wrap;gap:8px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span style="display:inline-flex;width:10px;height:10px;border-radius:50%;background:#ef4444;box-shadow:0 0 8px #ef4444"></span>
                            <span style="font-weight:700;font-size:14px;color:#f8fafc">রেকর্ডেড ক্লাস প্লেয়ার (In-Portal Class Player)</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span class="badge" style="background:rgba(255,255,255,0.15);color:#fff;font-size:11px" id="nowPlayingBadge">রেকর্ডেড লেকচার</span>
                            <a href="#" id="externalLinkBtn" target="_blank" class="btn btn-sm" style="display:none;background:rgba(255,255,255,0.1);color:#cbd5e1;border:1px solid rgba(255,255,255,0.2);font-size:11px;padding:3px 8px" title="নতুন ট্যাবে ভিডিও খুলুন">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> নতুন ট্যাবে
                            </a>
                        </div>
                    </div>

                    {{-- Playlist / Class Switcher Bar (if active module has multiple videos) --}}
                    <div id="moduleVideoPlaylistBar" style="background:#1e293b;padding:8px 14px;border-bottom:1px solid #334155;display:flex;align-items:center;gap:8px;overflow-x:auto">
                        <span style="font-size:11.5px;color:#94a3b8;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:4px">
                            <i class="fa-solid fa-list-ul"></i> ক্লাসের তালিকা:
                        </span>
                        <div id="playlistPills" style="display:flex;gap:6px;align-items:center;flex-wrap:nowrap">
                            @foreach($firstRecordedMod->videos as $vIndex => $vid)
                                <button type="button" 
                                    class="playlist-pill-btn {{ $vIndex === 0 ? 'active' : '' }}" 
                                    onclick='playRecordedVideo(@json($vid), "{{ addslashes($firstRecordedMod->title) }}", @json($firstRecordedMod->videos))'
                                    style="white-space:nowrap;border-radius:20px;font-size:11.5px;padding:4px 12px;font-family:'Kalpurush',sans-serif;font-weight:600;border:1px solid {{ $vIndex === 0 ? '#f43f5e' : '#475569' }};background:{{ $vIndex === 0 ? '#e11d48' : '#334155' }};color:#ffffff;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all 0.2s">
                                    <i class="fa-solid {{ $vIndex === 0 ? 'fa-circle-play' : 'fa-play' }}"></i>
                                    {{ $vid['title'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Dynamic Video Player Viewport --}}
                    <div style="background:#000;text-align:center;padding:12px;min-height:300px;display:flex;align-items:center;justify-content:center" id="videoPlayerContainer">
                        @if(!empty($firstVideo['embed_code']))
                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;width:100%;border-radius:6px;background:#000">
                                {!! $firstVideo['embed_code'] !!}
                            </div>
                        @elseif(!empty($firstVideo['file_path']))
                            <div style="position:relative;background:#000;border-radius:6px;overflow:hidden;max-height:540px;width:100%">
                                <video controls autoplay muted style="width:100%;max-height:520px;display:block;margin:0 auto;border-radius:6px" controlsList="nodownload">
                                    <source src="{{ asset('storage/' . $firstVideo['file_path']) }}">
                                    আপনার ব্রাউজার এই ভিডিও প্লে করতে পারছে না।
                                </video>
                            </div>
                        @elseif(!empty($firstVideo['url']))
                            @php
                                $vUrl = $firstVideo['url'];
                                if (str_contains($vUrl, 'youtube.com/watch')) {
                                    preg_match('/[?&]v=([^&]+)/', $vUrl, $matches);
                                    $vidId = $matches[1] ?? '';
                                    $embedSrc = "https://www.youtube.com/embed/{$vidId}?autoplay=1&rel=0";
                                } elseif (str_contains($vUrl, 'youtu.be/')) {
                                    $vidId = explode('youtu.be/', $vUrl)[1] ?? '';
                                    $vidId = explode('?', $vidId)[0];
                                    $embedSrc = "https://www.youtube.com/embed/{$vidId}?autoplay=1&rel=0";
                                } elseif (str_contains($vUrl, 'vimeo.com/')) {
                                    $parts = explode('vimeo.com/', $vUrl);
                                    $vidId = trim(explode('?', $parts[1] ?? '')[0], '/');
                                    $embedSrc = "https://player.vimeo.com/video/{$vidId}?autoplay=1";
                                } elseif (str_contains($vUrl, 'drive.google.com/file/d/')) {
                                    $parts = explode('/file/d/', $vUrl);
                                    $vidId = explode('/', $parts[1] ?? '')[0];
                                    $embedSrc = "https://drive.google.com/file/d/{$vidId}/preview";
                                } else {
                                    $embedSrc = $vUrl;
                                }
                            @endphp
                            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;width:100%;border-radius:6px;background:#000">
                                <iframe src="{{ $embedSrc }}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        @endif
                    </div>

                    {{-- Playing Info & Controls --}}
                    <div style="padding:12px 16px;background:#f8fafc;font-size:13px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #e2e8f0;flex-wrap:wrap;gap:8px">
                        <div>
                            <div style="font-size:11px;color:#64748b;margin-bottom:2px">
                                এখন চলছে &middot; <span id="nowPlayingModule" style="font-weight:600;color:#334155">{{ $firstRecordedMod->title }}</span>
                            </div>
                            <strong id="nowPlayingTitle" style="color:#0f172a;font-size:14px">{{ $firstVideo['title'] ?? $firstRecordedMod->title }}</strong>
                        </div>
                        <div id="videoCountIndicator">
                            <span class="badge" style="background:#e0e7ff;color:#4338ca;font-size:11px">
                                <i class="fa-solid fa-video"></i> এই মডিউলে {{ $firstRecordedMod->video_count }}টি ক্লাস আছে
                            </span>
                        </div>
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
                            <div style="padding:10px 14px;display:flex;flex-direction:column;gap:12px">
                                @foreach($mods as $mod)
                                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px;box-shadow:0 1px 2px rgba(0,0,0,0.03)">
                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap">
                                        <div style="display:flex;align-items:flex-start;gap:10px;flex:1;min-width:240px">
                                            <div style="width:28px;height:28px;border-radius:50%;background:#e0e7ff;color:#4338ca;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0;margin-top:2px">
                                                {{ $mod->sequence_no }}
                                            </div>
                                            <div>
                                                <div style="font-weight:700;font-size:14.5px;color:#0f172a;line-height:1.4">{{ $mod->title }}</div>
                                                @if($mod->description)
                                                    <div style="font-size:12.5px;color:#64748b;margin-top:3px">{{ $mod->description }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;flex-wrap:wrap">
                                            @if($mod->file_path)
                                                <a href="{{ asset('storage/' . $mod->file_path) }}" target="_blank" class="btn btn-outline btn-sm" style="font-size:11.5px;color:#dc2626;border-color:#fca5a5;padding:4px 10px">
                                                    <i class="fa-solid fa-file-pdf"></i> লেকচার শিট / নোট
                                                </a>
                                            @endif

                                            @if($mod->drive_link)
                                                <a href="{{ $mod->drive_link }}" target="_blank" class="btn btn-outline btn-sm" style="font-size:11.5px;color:#047857;border-color:#a7f3d0;padding:4px 10px">
                                                    <i class="fa-brands fa-google-drive"></i> ড্রাইভ ফোল্ডার
                                                </a>
                                            @endif

                                            @if($mod->has_recorded_videos)
                                                <span class="badge" style="background:#ffe4e6;color:#be123c;font-size:11.5px;padding:5px 8px">
                                                    <i class="fa-solid fa-circle-play"></i> {{ $mod->video_count }}টি রেকর্ড
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- If module has recorded class videos, list them clearly with individual titles --}}
                                    @if($mod->has_recorded_videos)
                                        <div style="margin-top:12px;padding-top:10px;border-top:1px dashed #e2e8f0">
                                            <div style="font-size:11.5px;font-weight:700;color:#9d174d;margin-bottom:8px;display:flex;align-items:center;gap:5px">
                                                <i class="fa-solid fa-film"></i> এই মডিউলের রেকর্ডেড ক্লাসসমূহ (ক্লিক করে প্লে করুন):
                                            </div>
                                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                                @foreach($mod->videos as $vIdx => $v)
                                                    <button type="button" 
                                                        class="btn btn-sm"
                                                        onclick='playRecordedVideo(@json($v), "{{ addslashes($mod->title) }}", @json($mod->videos))'
                                                        style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;border-radius:6px;font-size:12px;font-family:'Kalpurush',sans-serif;font-weight:600;padding:5px 12px;display:inline-flex;align-items:center;gap:6px;cursor:pointer;transition:all 0.2s"
                                                        onmouseover="this.style.background='#f43f5e';this.style.color='#fff';this.style.borderColor='#f43f5e'"
                                                        onmouseout="this.style.background='#fff1f2';this.style.color='#be123c';this.style.borderColor='#fecdd3'">
                                                        <i class="fa-solid fa-circle-play"></i>
                                                        <span>{{ $v['title'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
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
    function parseVideoEmbedSrc(url) {
        if (!url) return '';
        url = url.trim();

        // YouTube watch URL
        if (url.includes('youtube.com/watch')) {
            let match = url.match(/[?&]v=([^&]+)/);
            if (match && match[1]) {
                return 'https://www.youtube.com/embed/' + match[1] + '?autoplay=1&rel=0';
            }
        }
        // YouTube short URL
        if (url.includes('youtu.be/')) {
            let vidId = url.split('youtu.be/')[1].split('?')[0].split('/')[0];
            return 'https://www.youtube.com/embed/' + vidId + '?autoplay=1&rel=0';
        }
        // YouTube live or embed
        if (url.includes('youtube.com/live/')) {
            let vidId = url.split('youtube.com/live/')[1].split('?')[0].split('/')[0];
            return 'https://www.youtube.com/embed/' + vidId + '?autoplay=1&rel=0';
        }
        if (url.includes('youtube.com/embed/')) {
            return url + (url.includes('?') ? '&autoplay=1&rel=0' : '?autoplay=1&rel=0');
        }
        // Vimeo
        if (url.includes('vimeo.com/')) {
            let parts = url.split('vimeo.com/')[1].split('?')[0].split('/');
            let vidId = parts[parts.length - 1];
            if (vidId) return 'https://player.vimeo.com/video/' + vidId + '?autoplay=1';
        }
        // Google Drive preview
        if (url.includes('drive.google.com/file/d/')) {
            let parts = url.split('/file/d/')[1];
            let vidId = parts.split('/')[0];
            return 'https://drive.google.com/file/d/' + vidId + '/preview';
        }

        return url;
    }

    function playRecordedVideo(video, moduleTitle, moduleVideos = []) {
        const playerCard  = document.getElementById('videoPlayerCard');
        const container   = document.getElementById('videoPlayerContainer');
        const titleEl     = document.getElementById('nowPlayingTitle');
        const modEl       = document.getElementById('nowPlayingModule');
        const extLinkBtn  = document.getElementById('externalLinkBtn');
        const countInd    = document.getElementById('videoCountIndicator');
        const pillsCont   = document.getElementById('playlistPills');

        if (!container) return;

        // Update titles
        if (titleEl) titleEl.innerText = video.title || 'ক্লাস রেকর্ড';
        if (modEl) modEl.innerText = moduleTitle || '';
        if (countInd && moduleVideos.length > 0) {
            countInd.innerHTML = `<span class="badge" style="background:#e0e7ff;color:#4338ca;font-size:11px"><i class="fa-solid fa-video"></i> এই মডিউলে ${moduleVideos.length}টি ক্লাস আছে</span>`;
        }

        // External link button
        if (extLinkBtn) {
            if (video.url) {
                extLinkBtn.href = video.url;
                extLinkBtn.style.display = 'inline-flex';
            } else {
                extLinkBtn.style.display = 'none';
            }
        }

        // Render playlist pills
        if (pillsCont && moduleVideos && moduleVideos.length > 0) {
            let pillsHtml = '';
            moduleVideos.forEach((v, idx) => {
                const isActive = (v.id && v.id === video.id) || (v.title === video.title);
                const bg = isActive ? '#e11d48' : '#334155';
                const bc = isActive ? '#f43f5e' : '#475569';
                const icon = isActive ? 'fa-circle-play' : 'fa-play';
                const vJson = JSON.stringify(v).replace(/"/g, '&quot;');
                const mJson = JSON.stringify(moduleVideos).replace(/"/g, '&quot;');
                const escTitle = (moduleTitle || '').replace(/'/g, "\\'");

                pillsHtml += `<button type="button" 
                    class="playlist-pill-btn ${isActive ? 'active' : ''}" 
                    onclick='playRecordedVideo(${vJson}, "${escTitle}", ${mJson})'
                    style="white-space:nowrap;border-radius:20px;font-size:11.5px;padding:4px 12px;font-family:'Kalpurush',sans-serif;font-weight:600;border:1px solid ${bc};background:${bg};color:#ffffff;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all 0.2s">
                    <i class="fa-solid ${icon}"></i>
                    ${v.title}
                </button>`;
            });
            pillsCont.innerHTML = pillsHtml;
        }

        // Render viewport
        if (video.embed_code && video.embed_code.trim()) {
            container.innerHTML = `<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;width:100%;border-radius:6px;background:#000">${video.embed_code}</div>`;
        } else if (video.file_path) {
            const fileSrc = '/storage/' + video.file_path;
            container.innerHTML = `<div style="position:relative;background:#000;border-radius:6px;overflow:hidden;max-height:540px;width:100%">
                <video controls autoplay style="width:100%;max-height:520px;display:block;margin:0 auto;border-radius:6px" controlsList="nodownload">
                    <source src="${fileSrc}">
                    আপনার ব্রাউজার এই ভিডিও প্লে করতে পারছে না।
                </video>
            </div>`;
        } else if (video.url) {
            const embedSrc = parseVideoEmbedSrc(video.url);
            container.innerHTML = `<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;width:100%;border-radius:6px;background:#000">
                <iframe src="${embedSrc}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>`;
        } else {
            container.innerHTML = `<p style="color:#94a3b8;padding:40px;text-align:center">ভিডিও লিংক অথবা ফাইল পাওয়া যায়নি।</p>`;
        }

        // Smooth scroll to top player
        if (playerCard) {
            playerCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    </script>
    @endpush
</x-student-layout>
