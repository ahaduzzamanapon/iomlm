<x-teacher-layout>
    <x-slot name="title">Conduct: {{ $class->subject?->name ?? 'Class' }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('teacher.classes.index') }}">← Back to Classes</a>
            </div>
            <h1>Conduct Class: {{ $class->subject?->name ?? '—' }}</h1>
            <p>
                {{ $class->batch?->name ?? '—' }} &middot;
                {{ $class->session_date?->format('d M Y (D)') ?? 'TBA' }} &middot;
                {{ $class->routineEntry?->slot?->name ?? '' }}
                @if($class->start_time) · {{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }} @endif
            </p>
        </div>
        <div class="page-header-actions">
            @if($class->meeting_link)
                <a href="{{ $class->meeting_link }}" target="_blank" class="btn btn-primary btn-sm" style="font-weight:600">
                    <i class="fa-solid fa-video"></i> ক্লাসে যুক্ত হোন (Join Live)
                </a>
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('linkFormTop').style.display = document.getElementById('linkFormTop').style.display === 'none' ? 'flex' : 'none'">
                    <i class="fa-solid fa-pen"></i> লিংক পরিবর্তন
                </button>
            @else
                <form method="POST" action="{{ route('teacher.classes.setLink', $class) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-primary btn-sm" title="স্বয়ংক্রিয় জুম মিটিং রুম তৈরি">
                        <i class="fa-solid fa-bolt"></i> Auto Zoom Generate
                    </button>
                </form>
                <button type="button" class="btn btn-outline btn-sm" style="color:#f59e0b"
                    onclick="document.getElementById('linkFormTop').style.display = document.getElementById('linkFormTop').style.display === 'none' ? 'flex' : 'none'">
                    <i class="fa-solid fa-link"></i> লিংক পেস্ট করুন
                </button>
            @endif
            <form id="linkFormTop" method="POST" action="{{ route('teacher.classes.setLink', $class) }}"
                style="display:none;gap:6px;align-items:center">
                @csrf
                <input type="text" name="meeting_link" class="form-control" style="min-width:240px;font-size:12px"
                    value="{{ $class->meeting_link }}"
                    placeholder="Zoom বা Google Meet লিংক পেস্ট করুন (যেমন zoom.us/j/...)"
                    required>
                <button type="submit" class="btn btn-primary btn-sm">সংরক্ষণ</button>
            </form>
            <form method="POST" action="{{ route('teacher.classes.cancel', $class) }}" onsubmit="return confirm('Cancel this class?')">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444">Cancel Session</button>
            </form>
        </div>
    </div>

    <div class="alert alert-info" style="margin-bottom:16px;font-size:12px;display:flex;align-items:flex-start;gap:10px;font-family:'Kalpurush',sans-serif;background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;border-radius:8px;padding:12px 16px;">
        <i class="fa-solid fa-circle-info" style="font-size:16px;margin-top:2px;color:#0284c7"></i>
        <div>
            <strong>লাইভ ক্লাস পরিচালনা ও জুম লিংক নির্দেশিকা:</strong>
            <ul style="margin:4px 0 0 16px;padding:0;line-height:1.6">
                <li><strong>পদ্ধতি ১ (অটো জেনারেট):</strong> "Auto Zoom Generate" বাটনে ক্লিক করলে সিস্টেম স্বয়ংক্রিয়ভাবে জুম মিটিং রুম তৈরি করে লিংক যুক্ত করে দেবে।</li>
                <li><strong>পদ্ধতি ২ (ম্যানুয়ালি পেস্ট):</strong> নিজস্ব জুম অ্যাপ বা গুগল মিটের লিংক "লিংক পেস্ট করুন" বক্সে বসিয়ে সংরক্ষণ করতে পারেন (লিংকের শুরুতে https না থাকলেও সিস্টেম নিজে ঠিক করে নেবে)।</li>
                <li>শিক্ষার্থীরা তাদের পোর্টাল থেকে "Join Class" এ ক্লিক করা মাত্রই তাদের উপস্থিতি (Present) স্বয়ংক্রিয়ভাবে রেকর্ড হয়ে যাবে।</li>
            </ul>
        </div>
    </div>

    @if($class->meeting_link || $class->zoom_meeting_id)
    <form id="syncZoomForm" method="POST" action="{{ route('teacher.classes.syncZoomAttendance', $class) }}" style="display:none">
        @csrf
    </form>
    @endif

    <form method="POST" action="{{ route('teacher.classes.complete', $class) }}" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start">

            {{-- Attendance Roster --}}
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <span class="card-title">Attendance Roster</span>
                        <span class="badge badge-info no-dot">{{ $batchStudents->count() }} Students</span>
                        <span class="badge badge-success no-dot" style="font-size:11px;margin-left:8px;font-family:'Kalpurush',sans-serif">
                            ⚡ ক্লাসে জয়েন করলেই অটো প্রেজেন্ট
                        </span>
                    </div>
                    @if($class->meeting_link || $class->zoom_meeting_id)
                        <button type="submit" form="syncZoomForm" class="btn btn-sm btn-ghost" style="font-size:11px;color:var(--text-muted)" title="Zoom Participants API sync (optional)">
                            Sync from Zoom
                        </button>
                    @endif
                </div>

                @if($batchStudents->isEmpty())
                    <div style="padding:30px;text-align:center;color:var(--text-muted)">No students enrolled in this batch.</div>
                @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Code</th>
                                <th>Name</th>
                                <th style="text-align:center">Present</th>
                                <th style="text-align:center">Absent</th>
                                <th style="text-align:center">Late</th>
                                <th style="text-align:center">Excused</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batchStudents as $i => $en)
                            @php $st = $en->student; $existing = $class->attendances->firstWhere('student_id', $st->id); @endphp
                            <tr>
                                <td class="td-muted">{{ $i + 1 }}</td>
                                <td style="font-size:11px;color:#3b82f6;font-weight:600">{{ $st->student_code }}</td>
                                <td class="td-primary">
                                    {{ $st->name }}
                                    @if($existing?->status === 'PRESENT')
                                        <span style="font-size:10px;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;padding:1px 6px;border-radius:10px;margin-left:6px;font-family:'Kalpurush',sans-serif">
                                            ✓ জয়েন করেছে
                                        </span>
                                    @endif
                                </td>
                                @foreach(['PRESENT','ABSENT','LATE','EXCUSED'] as $status)
                                <td style="text-align:center">
                                    <input type="radio"
                                        name="attendance[{{ $st->id }}]"
                                        value="{{ $status }}"
                                        {{ ($existing?->status === $status || (!$existing && $status === 'ABSENT')) ? 'checked' : '' }}>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Right Panel --}}
            <div style="display:flex;flex-direction:column;gap:16px">
                {{-- Module Covered --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">Module Covered</span></div>
                    <div style="padding:12px">
                        <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px">Optional — which module did you cover today?</p>
                        <select name="module_covered_id" class="form-control">
                            <option value="">— Not specified —</option>
                            @foreach($modules as $mod)
                            <option value="{{ $mod->id }}" {{ $class->module_covered_id == $mod->id ? 'selected' : '' }}>
                                {{ $mod->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">Session Notes</span></div>
                    <div style="padding:12px">
                        <textarea name="notes" class="form-control" rows="4"
                            placeholder="Topics covered, student questions, announcements...">{{ $class->notes }}</textarea>
                    </div>
                </div>

                {{-- Class Recording (Optional) --}}
                <div class="card" style="border:1px solid #cbd5e1;font-family:'Kalpurush',sans-serif">
                    <div class="card-header" style="background:#eff6ff">
                        <span class="card-title" style="color:#1e40af;font-size:13px;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-circle-play"></i> ক্লাস রেকর্ড আপলোড (ঐচ্ছিক)
                        </span>
                    </div>
                    <div style="padding:12px;display:flex;flex-direction:column;gap:10px">
                        <div class="form-group" style="margin-bottom:0">
                            <label style="font-size:11.5px;font-weight:700">ভিডিও লিংক (YouTube / Drive URL)</label>
                            <input type="text" name="videos[0][url]" class="form-control" style="font-size:12px" placeholder="https://..." value="{{ $class->recording_url }}">
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label style="font-size:11.5px;font-weight:700">অথবা ভিডিও ফাইল (MP4)</label>
                            <input type="file" name="videos[0][file]" class="form-control" style="font-size:11px" accept="video/*">
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:15px">
                    Complete Class & Save Attendance
                </button>
            </div>
        </div>
    </form>
</x-teacher-layout>
