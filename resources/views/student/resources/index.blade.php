<x-student-layout>
    <x-slot name="title">Learning Materials & Resources</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush',sans-serif">লার্নিং রিসোর্স ও কোর্স মডিউল (Learning Resources)</h1>
            <p style="font-family:'Kalpurush',sans-serif;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span>সিলেবাস, ক্লাস লেকচার ফাইল, ড্রাইভ লিংক ও সহায়ক পাঠ্যসামগ্রী</span>
                @if(isset($runningSemesterNames) && $runningSemesterNames->isNotEmpty())
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 12px;background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;border-radius:20px;font-size:12px;font-weight:700">
                        <i class="fa-solid fa-graduation-cap"></i> চলতি সেমিস্টার: {{ $runningSemesterNames->implode(', ') }}
                    </span>
                @endif
            </p>
        </div>
    </div>

    {{-- Subject Filter Bar --}}
    @if(isset($subjects) && $subjects->isNotEmpty())
        <div class="card" style="margin-bottom:20px;padding:14px 20px;font-family:'Kalpurush',sans-serif">
            <form method="GET" action="{{ route('student.resources.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:0">
                <label for="subject_filter" style="font-weight:700;color:#334155;font-size:13px;display:flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-filter" style="color:#047857"></i> বিষয় নির্বাচন করুন:
                </label>
                <select name="subject_id" id="subject_filter" onchange="this.form.submit()" style="padding:6px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:#fff;font-family:'Kalpurush',sans-serif;cursor:pointer">
                    <option value="">-- চলতি সেমিস্টারের সকল বিষয় (All Current Subjects) --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ ($selectedSubjectId == $sub->id) ? 'selected' : '' }}>
                            {{ $sub->name }} ({{ $sub->code }})
                        </option>
                    @endforeach
                </select>
                @if($selectedSubjectId)
                    <a href="{{ route('student.resources.index') }}" class="btn btn-outline btn-sm" style="font-size:12px;padding:5px 12px">
                        <i class="fa-solid fa-rotate-left"></i> সকল বিষয় দেখুন
                    </a>
                @endif
            </form>
        </div>
    @endif

    {{-- Folder-Based Learning Modules Display --}}
    <div style="font-family:'Kalpurush',sans-serif">
        @php
            $hasAnyModules = false;
        @endphp

        @forelse($subjectsWithModules as $subject)
            @php
                $groupedModules = $subject->modules->groupBy(fn($m) => $m->folder_name ?: 'কোর্স মডিউল');
                if ($subject->modules->isNotEmpty()) {
                    $hasAnyModules = true;
                }
            @endphp

            <div class="card" style="margin-bottom:24px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
                {{-- Subject Card Header --}}
                <div style="background:#f8fafc;padding:12px 18px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="font-size:18px;color:#047857">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </span>
                        <div>
                            <h2 style="margin:0;font-size:15px;font-weight:700;color:#0f172a;font-family:'Kalpurush',sans-serif">
                                {{ $subject->name }} <span style="font-weight:600;color:#64748b;font-size:13px">({{ $subject->code }})</span>
                            </h2>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-secondary no-dot" style="font-size:11.5px;font-weight:700;padding:4px 10px">
                            {{ $subject->modules->count() }}টি মডিউল
                        </span>
                    </div>
                </div>

                {{-- Folders inside Subject --}}
                <div style="padding:14px">
                    @forelse($groupedModules as $folder => $mods)
                        <div style="margin-bottom:18px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#ffffff">
                            {{-- Folder Header --}}
                            <div style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:10px 16px;display:flex;align-items:center;justify-content:space-between">
                                <div style="font-weight:700;font-size:13.5px;color:#334155;display:flex;align-items:center;gap:8px">
                                    <i class="fa-solid fa-folder-open" style="color:#059669;font-size:14px"></i>
                                    <span>{{ $folder }}</span>
                                    <span class="badge badge-secondary no-dot" style="font-size:11px;background:#e2e8f0;color:#475569">
                                        {{ $mods->count() }}টি মডিউল
                                    </span>
                                </div>
                            </div>

                            {{-- Module Items list inside folder (Strictly sorted by sequence_no ASC: 1, 2, 3...) --}}
                            <div style="padding:4px 12px">
                                @foreach($mods as $mod)
                                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 4px;border-bottom:1px solid #f1f5f9;gap:12px;flex-wrap:wrap">
                                        {{-- Sequence badge & Title/Category/Description --}}
                                        <div style="display:flex;align-items:flex-start;gap:12px;flex:1;min-width:240px">
                                            <div style="width:34px;height:34px;border-radius:8px;background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;margin-top:2px" title="সিরিয়াল নম্বর {{ $mod->sequence_no }}">
                                                {{ $mod->sequence_no }}
                                            </div>
                                            <div style="min-width:0">
                                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                                    @if($mod->category)
                                                        <span class="badge badge-secondary no-dot" style="font-size:11px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569">
                                                            {{ $mod->category }}
                                                        </span>
                                                    @endif
                                                    <span style="font-size:14px;font-weight:700;color:#0f172a">
                                                        {{ $mod->title }}
                                                    </span>
                                                </div>
                                                @if($mod->description)
                                                    <div style="font-size:12px;color:#64748b;margin-top:3px;line-height:1.4">
                                                        {{ $mod->description }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                            @if($mod->file_path)
                                                @php
                                                    $ext = strtolower(pathinfo($mod->file_path, PATHINFO_EXTENSION));
                                                    $isPdf = in_array($ext, ['pdf']);
                                                @endphp
                                                <a href="{{ asset('storage/' . $mod->file_path) }}" target="_blank" rel="noopener noreferrer" 
                                                   class="btn btn-outline btn-sm" 
                                                   style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:12px;border-color:{{ $isPdf ? '#dc2626' : '#059669' }};color:{{ $isPdf ? '#dc2626' : '#059669' }};background:{{ $isPdf ? '#fef2f2' : '#ecfdf5' }};padding:5px 12px">
                                                    <i class="fa-solid {{ $isPdf ? 'fa-file-pdf' : 'fa-download' }}"></i>
                                                    ওপেন / ডাউনলোড ↗
                                                </a>
                                            @endif

                                            @if($mod->drive_link)
                                                <a href="{{ $mod->drive_link }}" target="_blank" rel="noopener noreferrer" 
                                                   class="btn btn-outline btn-sm" 
                                                   style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:12px;border-color:#2563eb;color:#2563eb;background:#eff6ff;padding:5px 12px">
                                                    <i class="fa-brands fa-google-drive"></i>
                                                    ড্রাইভ লিংক ↗
                                                </a>
                                            @endif

                                            @if(!empty($mod->learningResources))
                                                @foreach($mod->learningResources as $lr)
                                                    <a href="{{ $lr->url }}" target="_blank" rel="noopener noreferrer" 
                                                       class="btn btn-outline btn-sm" 
                                                       style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:12px;border-color:#059669;color:#059669;padding:5px 12px">
                                                        <i class="fa-solid fa-paperclip"></i>
                                                        {{ $lr->title }} ↗
                                                    </a>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:30px 15px;color:#64748b">
                            <i class="fa-solid fa-folder-open" style="font-size:28px;color:#cbd5e1;margin-bottom:6px"></i>
                            <p style="margin:0;font-size:13px">এই বিষয়ে এখনো কোনো মডিউল বা স্টাডি মেটেরিয়াল যুক্ত করা হয়নি।</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="card" style="text-align:center;padding:50px 20px;color:var(--text-muted);font-family:'Kalpurush',sans-serif">
                <div style="font-size:42px;color:#cbd5e1;margin-bottom:10px">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <h3 style="margin:0 0 6px;font-size:16px;font-weight:700;color:#334155">কোনো বিষয় বা লার্নিং রিসোর্স পাওয়া যায়নি</h3>
                <p style="margin:0;font-size:13px;color:#64748b">আপনার সেমিস্টারের এনরোলকৃত বিষয়ে এখনো কোনো স্টাডি মেটেরিয়াল আপলোড করা হয়নি।</p>
            </div>
        @endforelse

        @if($subjectsWithModules->isNotEmpty() && !$hasAnyModules)
            <div class="card" style="text-align:center;padding:40px 20px;color:var(--text-muted);font-family:'Kalpurush',sans-serif">
                <div style="font-size:36px;color:#cbd5e1;margin-bottom:8px">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h3 style="margin:0 0 4px;font-size:15px;font-weight:700;color:#334155">কোর্স মডিউল প্রস্তুত হচ্ছে</h3>
                <p style="margin:0;font-size:13px;color:#64748b">অ্যাকাডেমিক বিভাগ থেকে শিগগিরই সিলেবাস ও পাঠ্যসামগ্রী আপলোড করা হবে।</p>
            </div>
        @endif
    </div>
</x-student-layout>
