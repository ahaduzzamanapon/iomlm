<x-teacher-layout>
    <x-slot name="title">My Classes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>My Classes</h1>
            <p>Routine-based weekly schedule — add meeting links & take attendance</p>
        </div>
        <div class="page-header-actions" style="gap:8px">
            <button type="button" class="btn btn-sm" onclick="openTeacherExtraClassModal()" style="background:#be123c;color:#fff;border-color:#be123c;font-family:'Kalpurush',sans-serif;font-weight:700">
                <i class="fa-solid fa-calendar-plus"></i> + এক্সট্রা ক্লাস শিডিউল করুন
            </button>
            <a href="{{ route('teacher.classes.today') }}" class="btn btn-primary btn-sm">Today's Classes</a>
        </div>
    </div>

    @if($sessions->isEmpty())
        <div class="card" style="padding:40px;text-align:center;color:var(--text-muted)">
            <p>No classes assigned to you yet. Contact admin to set up your routine.</p>
        </div>
    @else
    @php $today = \Carbon\Carbon::today()->toDateString(); @endphp
    @foreach($sessions as $weekKey => $weekSessions)
    @php
        $firstDate = $weekSessions->first()->session_date;
        $weekLabel = $firstDate ? 'Week of ' . $firstDate->startOfWeek()->format('d M Y') : 'Unscheduled';
    @endphp
    <div class="card" style="margin-bottom:16px">
        <div class="card-header" style="background:#f8fafc">
            <span class="card-title" style="font-size:13px;color:#64748b">{{ $weekLabel }}</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Batch</th>
                        <th>Slot</th>
                        <th>Meeting Link</th>
                        <th>Module Covered</th>
                        <th>Attendance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weekSessions->sortBy('session_date') as $cs)
                    @php
                        $isToday   = $cs->session_date?->toDateString() === $today;
                        $attended  = $cs->attendances->where('status','PRESENT')->count();
                        $atTotal   = $cs->attendances->count();
                    @endphp
                    <tr style="{{ $isToday ? 'background:#eff6ff;' : '' }}">
                        <td>
                            <strong style="font-size:12px">{{ $cs->session_date?->format('d M (D)') ?? 'TBA' }}</strong>
                            @if($isToday)<div><span class="badge badge-success no-dot" style="font-size:9px">TODAY</span></div>@endif
                        </td>
                        <td class="td-primary">
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                <strong>{{ $cs->subject?->name ?? '—' }}</strong>
                                @if($cs->is_extra)
                                    <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:10px;font-weight:700">
                                        <i class="fa-solid fa-star"></i> এক্সট্রা ক্লাস
                                    </span>
                                @endif
                            </div>
                            @if($cs->title)
                                <div style="font-size:11px;color:#be123c;font-weight:600">{{ $cs->title }}</div>
                            @endif
                            @if($cs->teacher)<div style="font-size:11px;color:var(--text-muted)">{{ $cs->teacher->name }}</div>@endif
                        </td>
                        <td class="td-muted" style="font-size:11px">
                            {{ $cs->batch?->name ?? '—' }}
                            @if($cs->group_tag && $cs->group_tag !== 'ALL')
                                <div><span class="badge badge-secondary no-dot" style="font-size:9.5px">{{ $cs->group_label }}</span></div>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:11px">
                            @if($cs->routineEntry?->slot?->name)
                                {{ $cs->routineEntry->slot->name }}
                            @elseif($cs->is_extra)
                                <span style="color:#b45309;font-weight:600">রুটিন বহির্ভূত</span>
                            @else
                                —
                            @endif
                            @if($cs->start_time)<br><small>{{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }}</small>@endif
                        </td>
                        <td>
                            @if($cs->meeting_link)
                                <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-sm btn-outline" style="font-size:11px">Join</a>
                            @elseif($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                                @if($meetingProvider === 'zoom')
                                    <form method="POST" action="{{ route('teacher.classes.setLink', $cs) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline" style="font-size:11px;color:#2563eb">Zoom</button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-outline" style="font-size:11px;color:#f59e0b"
                                        onclick="document.getElementById('lf{{$cs->id}}').style.display='flex'">
                                        Add Link
                                    </button>
                                    <form id="lf{{$cs->id}}" method="POST"
                                        action="{{ route('teacher.classes.setLink', $cs) }}"
                                        style="display:none;gap:4px;align-items:center;margin-top:4px">
                                        @csrf
                                        <input type="url" name="meeting_link" class="form-control"
                                            style="font-size:11px;min-width:180px"
                                            placeholder="{{ $meetingProvider === 'google_meet' ? 'meet.google.com/…' : 'Meeting URL…' }}"
                                            required>
                                        <button type="submit" class="btn btn-primary btn-sm" style="font-size:10px">Save</button>
                                    </form>
                                @endif
                            @else
                                <span style="color:#d1d5db;font-size:11px">—</span>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:11px">{{ $cs->moduleCovered?->title ?? '—' }}</td>
                        <td style="text-align:center">
                            @if($atTotal > 0)
                                <span style="font-weight:700;font-size:12px;color:{{ $attended==$atTotal?'#10b981':'#f59e0b' }}">{{ $attended }}/{{ $atTotal }}</span>
                            @else
                                <span style="color:#d1d5db">—</span>
                            @endif
                        </td>
                        <td>
                            @php $badge = match($cs->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                            <span class="badge {{ $badge }} no-dot">{{ $cs->status }}</span>
                        </td>
                        <td style="white-space:nowrap">
                            @if($cs->status !== 'COMPLETED' && $cs->status !== 'CANCELLED')
                                <a href="{{ route('teacher.classes.conduct', $cs) }}" class="btn btn-sm btn-primary" style="font-size:11px">Conduct →</a>
                            @elseif($cs->status === 'COMPLETED')
                                <a href="{{ route('teacher.attendance.mark', $cs) }}" class="btn btn-sm btn-ghost" style="font-size:11px">Attendance</a>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline" style="font-size:11px;padding:3px 8px;margin-left:4px;{{ $cs->has_recorded_videos ? 'color:#047857;border-color:#a7f3d0;background:#ecfdf5;' : 'color:#2563eb;border-color:#bfdbfe;' }}"
                                onclick="openTeacherRecordingModal('{{ $cs->id }}', '{{ addslashes($cs->subject?->name ?? 'Class') }}', '{{ addslashes($cs->title ?? '') }}', '{{ addslashes($cs->recording_url ?? '') }}')">
                                <i class="fa-solid fa-circle-play"></i> {{ $cs->has_recorded_videos ? 'রেকর্ড এডিট' : 'রেকর্ড আপলোড' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
    @endif

    {{-- ── Teacher Add Extra Class Modal ── --}}
    <div class="modal-overlay" id="teacherExtraClassModal">
        <div class="modal" style="max-width:580px;width:95%;font-family:'Kalpurush',sans-serif">
            <div class="modal-header" style="background:#be123c;color:#fff">
                <span class="modal-title" style="color:#fff;font-size:15px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-calendar-plus"></i> এক্সট্রা ক্লাস শিডিউল করুন (Extra Class)
                </span>
                <button class="modal-close" style="color:#fff" onclick="closeModal('teacherExtraClassModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('teacher.classes.extra.store') }}">
                @csrf
                <div class="modal-body" style="padding:20px">
                    <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#9f1239">
                        <i class="fa-solid fa-circle-info"></i> <strong>এক্সট্রা ক্লাস:</strong> নিয়মিত সাপ্তাহিক রুটিনের বাইরে যেকোনো দিন ও সময়ে মেকআপ বা অতিরিক্ত ক্লাস শিডিউল করুন। শিক্ষার্থীরা তাদের রুটিন ও ড্যাশবোর্ডে এটি দেখতে পাবে।
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>টার্গেট ব্যাচ <span class="required" style="color:#e11d48">*</span></label>
                            <select name="batch_id" class="form-control" required>
                                <option value="">-- ব্যাচ নির্বাচন করুন --</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>বিষয় (Subject) <span class="required" style="color:#e11d48">*</span></label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">-- বিষয় নির্বাচন করুন --</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>ক্লাসের তারিখ (Date) <span class="required" style="color:#e11d48">*</span></label>
                            <input type="date" name="session_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="form-group">
                            <label>শাখা / গ্রুপ</label>
                            <select name="group_tag" class="form-control">
                                <option value="ALL">যৌথ / উভয় শাখা (All)</option>
                                <option value="MALE">ভাই শাখা (Male)</option>
                                <option value="FEMALE">বোন শাখা (Female)</option>
                                <option value="GROUP_A">গ্রুপ ক (Group A)</option>
                                <option value="GROUP_B">গ্রুপ খ (Group B)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>শুরুর সময় (Start Time) <span class="required" style="color:#e11d48">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="19:00" required>
                        </div>
                        <div class="form-group">
                            <label>শেষের সময় (End Time)</label>
                            <input type="time" name="end_time" class="form-control" value="20:30">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>ক্লাসের শিরোনাম বা টপিক (Title)</label>
                            <input type="text" name="title" class="form-control" placeholder="যেমনঃ বিশেষ মেকআপ ক্লাস / রিভিশন লেকচার" value="বিশেষ এক্সট্রা ক্লাস">
                        </div>
                        <div class="form-group">
                            <label>এক্সট্রা ক্লাসের কারণ (Reason)</label>
                            <input type="text" name="reason" class="form-control" placeholder="যেমনঃ ছুটির দিনের ক্ষতিপূরণ / পরীক্ষা পূর্ব প্রস্তুতি">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>অনলাইন মিটিং লিংক (Zoom / Google Meet URL)</label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xxx-xxxx-xxx বা https://zoom.us/j/...">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label>অতিরিক্ত নোট / শিক্ষার্থীদের জন্য নির্দেশনা</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="ক্লাসে অংশগ্রহণের জন্য কোনো পূর্বশর্ত বা নোট থাকলে লিখুন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding:14px 20px;background:#f8fafc">
                    <button type="button" class="btn btn-outline" onclick="closeModal('teacherExtraClassModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#be123c;border-color:#be123c;font-weight:700">
                        <i class="fa-solid fa-check"></i> এক্সট্রা ক্লাস শিডিউল করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Teacher Upload Recording Modal ── --}}
    <div class="modal-overlay" id="teacherRecordingModal">
        <div class="modal" style="max-width:580px;width:95%;font-family:'Kalpurush',sans-serif">
            <div class="modal-header" style="background:#2563eb;color:#fff">
                <span class="modal-title" style="color:#fff;font-size:15px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-circle-play"></i>
                    <span id="tRecModalTitle">ক্লাস রেকর্ড ভিডিও আপলোড</span>
                </span>
                <button class="modal-close" style="color:#fff" onclick="closeModal('teacherRecordingModal')">&times;</button>
            </div>
            <form id="teacherRecordingForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="padding:20px;display:flex;flex-direction:column;gap:14px;max-height:75vh;overflow-y:auto">
                    <div style="background:#eff6ff;padding:10px 14px;border-radius:8px;border:1px solid #bfdbfe;font-size:12px;color:#1e40af">
                        <i class="fa-solid fa-info-circle"></i> ক্লাসের রেকর্ডিং লিংক (YouTube/Google Drive/Vimeo) অথবা সরাসরি ভিডিও ফাইল (MP4) আপলোড করুন। শিক্ষার্থীরা এটি তাদের পোর্টাল থেকে সরাসরি দেখতে পারবে।
                    </div>

                    <div class="form-group">
                        <label style="font-size:12.5px;font-weight:700">ভিডিও শিরোনাম</label>
                        <input type="text" name="videos[0][title]" id="tRecVideoTitle" class="form-control" value="লেকচার রেকর্ড - ০১" placeholder="যেমন: লেকচার ০১ - ক্লাস আলোচনা">
                    </div>

                    <div class="form-group">
                        <label style="font-size:12.5px;font-weight:700">ভিডিও লিংক (YouTube / Google Drive / Vimeo URL)</label>
                        <input type="text" name="videos[0][url]" id="tRecVideoUrl" class="form-control" placeholder="https://www.youtube.com/watch?v=... বা Google Drive Link">
                    </div>

                    <div class="form-group">
                        <label style="font-size:12.5px;font-weight:700">অথবা ভিডিও ফাইল আপলোড (MP4)</label>
                        <input type="file" name="videos[0][file]" class="form-control" accept="video/*">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:12.5px;font-weight:700">অথবা iframe Embed Code (ঐচ্ছিক)</label>
                        <textarea name="videos[0][embed_code]" class="form-control" rows="2" placeholder="<iframe src='...'></iframe>"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding:14px 20px;background:#f8fafc">
                    <button type="button" class="btn btn-outline" onclick="closeModal('teacherRecordingModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#2563eb;border-color:#2563eb;font-weight:700">
                        <i class="fa-solid fa-cloud-arrow-up"></i> রেকর্ড সেভ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openTeacherExtraClassModal() {
        openModal('teacherExtraClassModal');
    }

    function openTeacherRecordingModal(classId, subjectName, classTitle, existingUrl) {
        const form = document.getElementById('teacherRecordingForm');
        form.action = `/teacher/classes/${classId}/recording`;

        const titleSpan = document.getElementById('tRecModalTitle');
        if (titleSpan) titleSpan.textContent = `ক্লাস রেকর্ড: ${subjectName}`;

        const vTitle = document.getElementById('tRecVideoTitle');
        if (vTitle) vTitle.value = classTitle ? `${classTitle} - রেকর্ড` : 'লেকচার রেকর্ড - ০১';

        const vUrl = document.getElementById('tRecVideoUrl');
        if (vUrl) vUrl.value = existingUrl || '';

        openModal('teacherRecordingModal');
    }
    </script>
    @endpush
</x-teacher-layout>
