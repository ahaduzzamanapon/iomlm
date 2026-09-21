<x-student-layout>
    <x-slot name="title">আমার বিষয়সমূহ — My Subjects</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush', sans-serif">আমার অধ্যায়নরত বিষয়সমূহ (Enrolled Subjects)</h1>
            <p>আপনার রানিং কোর্সের বিষয়ভিত্তিক মডিউল, লেকচার নোট, ড্রাইভ ম্যাটেরিয়াল ও অ্যাসাইনমেন্ট</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('student.assignments.index') }}" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-file-signature"></i> আমার অ্যাসাইনমেন্টসমূহ
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid-2">
        @forelse($enrollments as $enr)
        @foreach($enr->course->subjects as $subj)
        @php
            $visibleModules = $subj->modules->where('is_hidden', false);
            $groupedMods = $visibleModules->groupBy(fn($m) => $m->folder_name ?: 'General');
        @endphp
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div class="card-header" style="background:#fafafa;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <span class="badge badge-secondary no-dot" style="font-weight:700">{{ $subj->code }}</span>
                        <a href="{{ route('student.subjects.show', $subj) }}" style="text-decoration:none;color:#0f172a">
                            <strong style="font-size:15px;margin-left:6px">{{ $subj->name }}</strong>
                        </a>
                        @if($subj->category)
                            <div style="font-size:11px;color:#6366f1;margin-top:2px">
                                <i class="fa-solid fa-tag"></i> {{ $subj->category->name }}
                            </div>
                        @endif
                    </div>
                    <span class="badge badge-active no-dot">{{ $subj->credit }} Credit</span>
                </div>

                <div class="card-body" style="padding:16px">
                    {{-- Modules Folders --}}
                    @if($visibleModules->count() > 0)
                        <div style="margin-bottom:12px">
                            @foreach($groupedMods as $folder => $mods)
                                <div style="margin-bottom:10px;border:1px solid #f1f5f9;border-radius:8px;overflow:hidden">
                                    <div style="background:#f8fafc;padding:6px 12px;font-size:12px;font-weight:700;color:#475569;display:flex;align-items:center;gap:6px">
                                        <i class="fa-solid fa-folder" style="color:#6366f1"></i> {{ $folder }}
                                        <span style="font-weight:normal;color:#94a3b8;font-size:11px">({{ $mods->count() }}টি মডিউল)</span>
                                    </div>
                                    <div style="padding:6px 12px;display:flex;flex-direction:column;gap:6px">
                                        @foreach($mods as $mod)
                                            <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;padding:4px 0">
                                                <div style="display:flex;align-items:center;gap:6px">
                                                    <span style="width:18px;height:18px;border-radius:50%;background:#e0e7ff;color:#4338ca;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700">
                                                        {{ $mod->sequence_no }}
                                                    </span>
                                                    <span style="font-weight:600;color:#1e293b">{{ $mod->title }}</span>
                                                </div>
                                                <div style="display:flex;gap:6px">
                                                    @if($mod->file_path)
                                                        <a href="{{ asset('storage/' . $mod->file_path) }}" target="_blank" style="color:#ef4444" title="PDF/Doc ফাইল">
                                                            <i class="fa-solid fa-file-pdf"></i>
                                                        </a>
                                                    @endif
                                                    @if($mod->drive_link)
                                                        <a href="{{ $mod->drive_link }}" target="_blank" style="color:#059669" title="Google Drive Link">
                                                            <i class="fa-brands fa-google-drive"></i>
                                                        </a>
                                                    @endif
                                                    @if($mod->recorded_url || $mod->embed_code)
                                                        <a href="{{ route('student.subjects.show', $subj) }}" style="color:#be185d" title="রেকর্ডেড ক্লাস দেখুন">
                                                            <i class="fa-solid fa-video"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 12px">বর্তমানে কোনো উন্মুক্ত মডিউল উপলব্ধ নেই।</p>
                    @endif

                    {{-- Assignments preview --}}
                    @if($subj->assignments->count() > 0)
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;font-size:12px">
                            <strong style="color:#166534;display:flex;align-items:center;gap:6px">
                                <i class="fa-solid fa-file-signature"></i> অ্যাসাইনমেন্ট: {{ $subj->assignments->count() }}টি
                            </strong>
                        </div>
                    @endif
                </div>
            </div>

            <div style="padding:12px 16px;background:#f8fafc;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">
                <a href="{{ route('student.subjects.show', $subj) }}" class="btn btn-outline btn-sm" style="font-size:12px">
                    <i class="fa-solid fa-book-open"></i> ক্লাসরুমে প্রবেশ করুন
                </a>
                <a href="{{ route('student.assignments.index') }}" class="btn btn-ghost btn-sm" style="font-size:12px;color:#6366f1">
                    হোমওয়ার্ক / অ্যাসাইনমেন্ট →
                </a>
            </div>
        </div>
        @endforeach
        @empty
        <div class="empty-state" style="grid-column:1/-1;padding:50px;text-align:center">
            <p>আপনার কোনো সক্রিয় বিষয়ের তালিকা পাওয়া যায়নি।</p>
        </div>
        @endforelse
    </div>
</x-student-layout>
