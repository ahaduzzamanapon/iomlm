<x-student-layout>
    <x-slot name="title">ক্লাস রেকর্ড (Class Records)</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <h1 style="display:flex;align-items:center;gap:10px;font-size:24px">
                <i class="fa-solid fa-circle-play" style="color:#2563eb"></i>
                ক্লাস রেকর্ড (Class Records)
            </h1>
            <p>আপনার ব্যাচ ও বিষয়ের সকল রেকর্ডেড ক্লাস এবং ভিডিও লেকচার</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card" style="margin-bottom:20px;padding:16px 20px;border-radius:12px;font-family:'Kalpurush',sans-serif;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
        <form method="GET" action="{{ route('student.recordings.index') }}" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) 120px;gap:12px;align-items:flex-end">
            <div>
                <label style="font-size:12px;font-weight:700;color:#475569;margin-bottom:4px;display:block">বিষয় দিয়ে ফিল্টার</label>
                <select name="subject_id" class="form-control" style="font-size:13px;border-radius:8px">
                    <option value="">— সকল বিষয় —</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $subjectId == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }} ({{ $sub->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if($enrolledBatches->count() > 1)
            <div>
                <label style="font-size:12px;font-weight:700;color:#475569;margin-bottom:4px;display:block">ব্যাচ দিয়ে ফিল্টার</label>
                <select name="batch_id" class="form-control" style="font-size:13px;border-radius:8px">
                    <option value="">— সকল ব্যাচ —</option>
                    @foreach($enrolledBatches as $b)
                        <option value="{{ $b->id }}" {{ $batchId == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label style="font-size:12px;font-weight:700;color:#475569;margin-bottom:4px;display:block">খুঁজুন (Search)</label>
                <input type="text" name="search" class="form-control" placeholder="ক্লাসের নাম, টপিক বা শিক্ষক..." value="{{ $search }}" style="font-size:13px;border-radius:8px">
            </div>

            <div style="display:flex;gap:6px">
                <button type="submit" class="btn btn-primary" style="flex:1;font-weight:700;border-radius:8px;padding:9px 14px">
                    <i class="fa-solid fa-filter"></i> খুঁজুন
                </button>
                @if($subjectId || $batchId || $search)
                    <a href="{{ route('student.recordings.index') }}" class="btn btn-outline" style="border-radius:8px;padding:9px 12px" title="ফিল্টার রিসেট">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Classes Grid --}}
    <div style="font-family:'Kalpurush',sans-serif">
        @if($classes->isEmpty())
            <div class="card" style="padding:48px 20px;text-align:center;border-radius:12px">
                <div style="width:64px;height:64px;border-radius:50%;background:#eff6ff;color:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px auto">
                    <i class="fa-solid fa-video-slash"></i>
                </div>
                <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin-bottom:6px">কোনো ক্লাস রেকর্ড পাওয়া যায়নি</h3>
                <p style="font-size:13.5px;color:#64748b;max-width:480px;margin:0 auto">
                    @if($subjectId || $batchId || $search)
                        আপনার প্রদত্ত ফিল্টারে কোনো রেকর্ডেড ক্লাস খুঁজে পাওয়া যায়নি। ফিল্টার রিসেট করে পুনরায় চেষ্টা করুন।
                    @else
                        আপনার এনরোলকৃত ব্যাচে শিক্ষকমণ্ডলী বা এডমিন কর্তৃক এখনও কোনো ক্লাসের ভিডিও রেকর্ড আপলোড করা হয়নি। ক্লাস সম্পন্ন হওয়ার পর এখানে রেকর্ড পাওয়া যাবে।
                    @endif
                </p>
                @if($subjectId || $batchId || $search)
                    <div style="margin-top:16px">
                        <a href="{{ route('student.recordings.index') }}" class="btn btn-outline btn-sm">ফিল্টার রিসেট করুন</a>
                    </div>
                @endif
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:18px">
                @foreach($classes as $cs)
                    @php
                        $vids = $cs->videos;
                        $firstVid = $vids[0] ?? null;
                    @endphp
                    <div class="card" style="display:flex;flex-direction:column;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 2px 5px rgba(0,0,0,0.03);transition:transform 0.15s, box-shadow 0.15s">
                        {{-- Card Header / Banner Thumbnail --}}
                        <div style="background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);color:#fff;padding:16px 18px;position:relative">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px">
                                <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:10.5px;font-weight:700">
                                    {{ $cs->subject?->code ?? 'SUB' }}
                                </span>
                                <div style="display:flex;gap:6px">
                                    @if($cs->is_extra)
                                        <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:10px;font-weight:700">
                                            <i class="fa-solid fa-star"></i> এক্সট্রা ক্লাস
                                        </span>
                                    @endif
                                    <span class="badge" style="background:#10b981;color:#fff;font-size:10.5px;font-weight:700">
                                        <i class="fa-solid fa-circle-play"></i> {{ count($vids) }} টি ভিডিও
                                    </span>
                                </div>
                            </div>
                            <h3 style="font-size:16px;font-weight:800;color:#fff;margin:10px 0 4px 0;line-height:1.3">
                                {{ $cs->subject?->name ?? '—' }}
                            </h3>
                            <div style="font-size:12px;color:#bfdbfe">
                                {{ $cs->batch?->name }} &middot; {{ $cs->session_date?->format('d M Y (D)') ?? 'TBA' }}
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div style="padding:16px 18px;flex:1;display:flex;flex-direction:column;justify-content:space-between;gap:12px">
                            <div>
                                @if($cs->title)
                                    <div style="font-size:13.5px;font-weight:700;color:#0f172a;margin-bottom:6px">
                                        <i class="fa-regular fa-bookmark" style="color:#2563eb"></i> {{ $cs->title }}
                                    </div>
                                @endif

                                @if($cs->moduleCovered)
                                    <div style="font-size:12px;color:#475569;margin-bottom:6px;display:flex;align-items:center;gap:6px">
                                        <i class="fa-solid fa-book-open" style="color:#64748b;font-size:11px"></i>
                                        <strong>মডিউল:</strong> {{ $cs->moduleCovered->title }}
                                    </div>
                                @endif

                                <div style="font-size:12px;color:#64748b;display:flex;align-items:center;gap:6px">
                                    <i class="fa-solid fa-chalkboard-user" style="color:#64748b;font-size:11px"></i>
                                    <strong>শিক্ষক:</strong> {{ $cs->teacher?->name ?? '—' }}
                                </div>

                                {{-- Video Parts Summary --}}
                                @if(count($vids) > 1)
                                    <div style="margin-top:10px;background:#f8fafc;padding:6px 10px;border-radius:6px;border:1px solid #f1f5f9;font-size:11px;color:#64748b">
                                        <i class="fa-solid fa-list-check" style="color:#3b82f6"></i> পার্টসমূহ:
                                        @foreach($vids as $idx => $v)
                                            <span style="font-weight:600;color:#1e293b">{{ $v['title'] }}</span>{{ !$loop->last ? ' • ' : '' }}
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Card Action --}}
                            <div style="border-top:1px solid #f1f5f9;padding-top:12px;display:flex;justify-content:space-between;align-items:center">
                                <span style="font-size:11.5px;color:#64748b">
                                    <i class="fa-regular fa-clock"></i> {{ $cs->start_time ? \Carbon\Carbon::parse($cs->start_time)->format('h:i A') : 'TBA' }}
                                </span>
                                <a href="{{ route('student.recordings.show', $cs) }}" class="btn btn-primary btn-sm" style="padding:6px 16px;border-radius:8px;font-weight:800;display:inline-flex;align-items:center;gap:6px">
                                    <i class="fa-solid fa-play"></i> ভিডিও দেখুন
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div style="margin-top:24px">
                {{ $classes->links() }}
            </div>
        @endif
    </div>
</x-student-layout>
