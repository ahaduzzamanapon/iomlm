<x-student-layout>
    <x-slot name="title">{{ $class->subject?->name ?? 'ক্লাস রেকর্ড' }} — {{ $activeVideo['title'] ?? 'ভিডিও প্লেয়ার' }}</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <div style="font-size:12.5px;color:var(--text-muted);margin-bottom:6px">
                <a href="{{ route('student.recordings.index') }}" style="color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-weight:700">
                    <i class="fa-solid fa-arrow-left"></i> সকল ক্লাস রেকর্ডে ফিরে যান
                </a>
            </div>
            <h1 style="display:flex;align-items:center;gap:10px;font-size:22px;margin:0">
                <i class="fa-solid fa-circle-play" style="color:#2563eb"></i>
                {{ $class->subject?->name ?? 'ক্লাস রেকর্ড' }}
                @if($class->is_extra)
                    <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:11px;font-weight:700">
                        <i class="fa-solid fa-star"></i> এক্সট্রা ক্লাস
                    </span>
                @endif
            </h1>
            <p style="margin-top:4px;color:#64748b;font-size:13px">
                {{ $class->batch?->name }} &middot;
                {{ $class->session_date?->format('l, d M Y') ?? 'TBA' }} &middot;
                {{ $class->start_time ? \Carbon\Carbon::parse($class->start_time)->format('h:i A') : '' }}
                @if($class->teacher) &middot; শিক্ষক: <strong>{{ $class->teacher->name }}</strong> @endif
            </p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:22px;align-items:start;font-family:'Kalpurush',sans-serif">

        {{-- LEFT COLUMN: In-Portal Video Player & Multi-Video Switcher --}}
        <div style="display:flex;flex-direction:column;gap:18px">

            {{-- Video Player Container --}}
            <div class="card" style="padding:0;overflow:hidden;border-radius:14px;border:1px solid #cbd5e1;background:#000;box-shadow:0 4px 15px rgba(0,0,0,0.15)">
                <div style="position:relative;width:100%;padding-top:56.25%;background:#090d16">
                    @if($activeVideo)
                        <div style="position:absolute;top:0;left:0;bottom:0;right:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                            @if($activeVideo['type'] === 'youtube')
                                <iframe src="{{ $activeVideo['embed_url'] }}" 
                                        title="{{ $activeVideo['title'] }}"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen 
                                        style="width:100%;height:100%;border:none">
                                </iframe>
                            @elseif($activeVideo['type'] === 'drive')
                                <iframe src="{{ $activeVideo['embed_url'] }}" 
                                        title="{{ $activeVideo['title'] }}"
                                        allow="autoplay" 
                                        allowfullscreen 
                                        style="width:100%;height:100%;border:none">
                                </iframe>
                            @elseif($activeVideo['type'] === 'vimeo')
                                <iframe src="{{ $activeVideo['embed_url'] }}" 
                                        title="{{ $activeVideo['title'] }}"
                                        allow="autoplay; fullscreen; picture-in-picture" 
                                        allowfullscreen 
                                        style="width:100%;height:100%;border:none">
                                </iframe>
                            @elseif($activeVideo['type'] === 'file')
                                <video controls 
                                       controlsList="nodownload" 
                                       preload="metadata" 
                                       playsinline 
                                       style="width:100%;height:100%;background:#000;outline:none">
                                    <source src="{{ $activeVideo['file_url'] ?? $activeVideo['url'] }}">
                                    আপনার ব্রাউজার সরাসরি এই ভিডিও প্লে সমর্থন করে না।
                                </video>
                            @elseif($activeVideo['type'] === 'embed' && !empty($activeVideo['embed_code']))
                                <div class="custom-embed-wrapper" style="width:100%;height:100%">
                                    {!! $activeVideo['embed_code'] !!}
                                </div>
                            @elseif(!empty($activeVideo['url']))
                                <div style="text-align:center;padding:30px;color:#fff">
                                    <i class="fa-solid fa-video" style="font-size:42px;color:#3b82f6;margin-bottom:12px;display:block"></i>
                                    <h4 style="color:#fff;margin-bottom:12px">{{ $activeVideo['title'] }}</h4>
                                    <a href="{{ $activeVideo['url'] }}" target="_blank" class="btn btn-primary" style="font-weight:700">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> নতুন উইন্ডোতে ভিডিওটি চালু করুন
                                    </a>
                                </div>
                            @else
                                <div style="text-align:center;color:#94a3b8;padding:30px">
                                    <i class="fa-solid fa-triangle-exclamation" style="font-size:36px;color:#f59e0b;margin-bottom:10px"></i>
                                    <p>এই ভিডিও পার্টের কোনো সক্রিয় লিংক পাওয়া যায়নি।</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div style="position:absolute;top:0;left:0;bottom:0;right:0;display:flex;align-items:center;justify-content:center;color:#fff">
                            ভিডিও লোড করা যায়নি।
                        </div>
                    @endif
                </div>

                {{-- Player Underbar --}}
                <div style="background:#0f172a;color:#fff;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span class="badge" style="background:#2563eb;color:#fff;font-weight:700;font-size:11px">
                            চলছে: পার্ট {{ $part + 1 }}/{{ count($videos) }}
                        </span>
                        <strong style="font-size:14px;color:#f8fafc">{{ $activeVideo['title'] ?? 'ভিডিও রেকর্ড' }}</strong>
                    </div>
                    <div style="font-size:11.5px;color:#94a3b8;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-shield-halved" style="color:#10b981"></i> অন-পোর্টাল নিরাপদ প্লেয়ার
                    </div>
                </div>
            </div>

            {{-- Multi-Video Playlist (if more than 1 video) --}}
            @if(count($videos) > 1)
            <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;padding:16px 20px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <span style="font-size:14px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-list-ol" style="color:#2563eb"></i>
                        এই ক্লাসের সকল ভিডিও পার্টসমূহ ({{ count($videos) }} টি পার্ট)
                    </span>
                    <span style="font-size:11.5px;color:#64748b">যেকোনো পার্টে ক্লিক করে দেখতে পারেন</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @foreach($videos as $vIdx => $v)
                        @php $isActive = ($vIdx === $part); @endphp
                        <a href="{{ route('student.recordings.show', [$class, 'part' => $vIdx]) }}" 
                           style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:8px;text-decoration:none;transition:all 0.15s;{{ $isActive ? 'background:#eff6ff;border:1.5px solid #3b82f6;' : 'background:#f8fafc;border:1px solid #e2e8f0;' }}">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;{{ $isActive ? 'background:#2563eb;color:#fff;' : 'background:#e2e8f0;color:#475569;' }}">
                                    {{ $vIdx + 1 }}
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:{{ $isActive ? '#1d4ed8' : '#1e293b' }}">
                                        {{ $v['title'] }}
                                    </div>
                                    <span style="font-size:11px;color:#64748b;text-transform:uppercase">
                                        {{ $v['type'] }} সোর্স
                                    </span>
                                </div>
                            </div>
                            <div>
                                @if($isActive)
                                    <span class="badge" style="background:#2563eb;color:#fff;font-size:10px;font-weight:700">
                                        <i class="fa-solid fa-play"></i> এখন চলছে
                                    </span>
                                @else
                                    <span class="btn btn-ghost btn-sm" style="font-size:11px;color:#475569">
                                        প্লে করুন →
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Session Notes / Details --}}
            @if($class->notes)
            <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;padding:18px 20px">
                <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                    <i class="fa-regular fa-note-sticky" style="color:#f59e0b"></i> ক্লাস নোট ও নির্দেশনা (Class Notes)
                </div>
                <div style="font-size:13px;color:#334155;line-height:1.6;white-space:pre-line;background:#fefce8;padding:12px 14px;border-radius:8px;border:1px solid #fef08a">
                    {{ $class->notes }}
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: Session Information Card --}}
        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
                <div class="card-header" style="background:#f8fafc;padding:14px 18px;border-bottom:1px solid #e2e8f0">
                    <span class="card-title" style="display:flex;align-items:center;gap:8px;font-size:14.5px;color:#0f172a;font-weight:800">
                        <i class="fa-solid fa-circle-info" style="color:#2563eb"></i>
                        ক্লাসের তথ্যাবলি (Session Info)
                    </span>
                </div>
                <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;font-size:13px">
                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">বিষয় (Subject)</div>
                        <strong style="color:#0f172a;font-size:14px">{{ $class->subject?->name ?? '—' }}</strong>
                        <div style="font-size:11px;color:#64748b">{{ $class->subject?->code }}</div>
                    </div>

                    @if($class->title)
                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">ক্লাস টপিক / শিরোনাম</div>
                        <div style="color:#be123c;font-weight:700">{{ $class->title }}</div>
                    </div>
                    @endif

                    @if($class->moduleCovered)
                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">কভার্ড মডিউল (Covered Module)</div>
                        <strong style="color:#2563eb">{{ $class->moduleCovered->title }}</strong>
                        @if($class->moduleCovered->description)
                            <p style="font-size:11.5px;color:#64748b;margin:4px 0 0 0">{{ $class->moduleCovered->description }}</p>
                        @endif
                    </div>
                    @endif

                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">ব্যাচ (Batch)</div>
                        <div>{{ $class->batch?->name ?? '—' }}</div>
                    </div>

                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">শিক্ষক (Teacher)</div>
                        <div>{{ $class->teacher?->name ?? '—' }}</div>
                    </div>

                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">ক্লাসের তারিখ ও সময়</div>
                        <div>
                            {{ $class->session_date?->format('d M Y (D)') ?? 'TBA' }}
                            @if($class->start_time)
                                &middot; {{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">শাখা / গ্রুপ</div>
                        <div><span class="badge badge-secondary no-dot" style="font-size:10.5px">{{ $class->group_label }}</span></div>
                    </div>

                    <div>
                        <div style="font-size:11px;color:#64748b;font-weight:700">ক্লাসের ধরন</div>
                        <div><span class="badge {{ $class->is_extra ? 'badge-warning' : 'badge-info' }} no-dot" style="font-size:10.5px">{{ $class->class_type_label }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Quick Back / Navigation Button --}}
            <a href="{{ route('student.recordings.index') }}" class="btn btn-outline" style="width:100%;text-align:center;font-weight:700;padding:10px;border-radius:8px">
                <i class="fa-solid fa-list"></i> সকল ক্লাস রেকর্ড তালিকা
            </a>
        </div>
    </div>

    <style>
    .custom-embed-wrapper iframe {
        width: 100% !important;
        height: 100% !important;
        border: none !important;
    }
    </style>
</x-student-layout>
