<x-admin-layout>
    <x-slot name="title">{{ $batch->name }}</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.batches.index') }}">← Back to Batches</a>
            </div>
            <h1>{{ $batch->name }}</h1>
            <p>Course: {{ $batch->course->name }} &middot; Code: {{ $batch->batch_code }} &middot; Start: {{ \Carbon\Carbon::parse($batch->start_date)->format('d M Y') }}</p>
        </div>
        <div class="page-header-actions">
            <form method="POST" action="{{ route('admin.batches.generateTimeline', $batch) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Generate Sessions (8 Weeks)</button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

        {{-- LEFT: Class Sessions --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Class Sessions ({{ $sessions->count() }} total)</span>
                    <span style="font-size:11px;color:var(--text-muted)">Routine-based · per class date</span>
                </div>

                @if($sessions->isEmpty())
                    <div style="padding:30px;text-align:center;color:var(--text-muted)">
                        No sessions yet. Click "Generate Sessions" to create from routine.
                    </div>
                @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Slot</th>
                                <th>Teacher</th>
                                <th>Module Covered</th>
                                <th>Attendance</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $cs)
                            @php
                                $isToday  = $cs->session_date?->isToday();
                                $isPast   = $cs->session_date?->isPast() && !$isToday;
                                $present  = $cs->attendances->where('status','PRESENT')->count();
                                $total    = $cs->attendances->count();
                            @endphp
                            <tr style="{{ $isToday ? 'background:#eff6ff;' : ($isPast && $cs->status !== 'COMPLETED' ? 'opacity:.7;' : '') }}">
                                <td>
                                    <strong style="font-size:12px">
                                        {{ $cs->session_date?->format('d M') ?? 'TBA' }}
                                    </strong>
                                    <div class="td-muted" style="font-size:10px">
                                        {{ $cs->session_date?->format('D Y') ?? '' }}
                                        @if($isToday)<span class="badge badge-success no-dot" style="font-size:9px">TODAY</span>@endif
                                    </div>
                                </td>
                                <td class="td-primary" style="font-size:12px">
                                    {{ $cs->subject?->name ?? '—' }}
                                    @if($cs->group_tag === 'MALE')
                                        <span class="badge" style="background:#0284c7;color:#fff;font-size:9px;padding:1px 5px">ভাই শাখা</span>
                                    @elseif($cs->group_tag === 'FEMALE')
                                        <span class="badge" style="background:#ec4899;color:#fff;font-size:9px;padding:1px 5px">বোন শাখা</span>
                                    @elseif($cs->group_tag === 'GROUP_A')
                                        <span class="badge" style="background:#8b5cf6;color:#fff;font-size:9px;padding:1px 5px">গ্রুপ ক</span>
                                    @elseif($cs->group_tag === 'GROUP_B')
                                        <span class="badge" style="background:#f59e0b;color:#fff;font-size:9px;padding:1px 5px">গ্রুপ খ</span>
                                    @endif
                                </td>
                                <td class="td-muted" style="font-size:11px">
                                    {{ $cs->routineEntry?->slot?->name ?? '—' }}<br>
                                    @if($cs->start_time)<small>{{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }}</small>@endif
                                </td>
                                <td class="td-muted" style="font-size:11px">{{ $cs->teacher?->name ?? '—' }}</td>
                                <td class="td-muted" style="font-size:11px">{{ $cs->moduleCovered?->title ?? '—' }}</td>
                                <td style="text-align:center">
                                    @if($total > 0)
                                        <span style="font-size:12px;font-weight:700;color:{{ $present==$total ? '#10b981' : '#f59e0b' }}">{{ $present }}/{{ $total }}</span>
                                    @else
                                        <span style="color:#d1d5db">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cs->meeting_link)
                                        <a href="{{ $cs->meeting_link }}" target="_blank" style="font-size:11px;color:#3b82f6"><i class="fa-solid fa-video"></i></a>
                                    @else
                                        <span style="color:#d1d5db;font-size:11px">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php $badge = match($cs->status) { 'COMPLETED'=>'badge-success','SCHEDULED'=>'badge-info','CANCELLED'=>'badge-danger',default=>'badge-warning' }; @endphp
                                    <span class="badge {{ $badge }} no-dot" style="font-size:10px">{{ $cs->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.classes.show', $cs) }}" class="btn btn-ghost btn-sm">→</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Curriculum Syllabus (informational) --}}
            @if($subjects->isNotEmpty())
            <div class="card" style="margin-top:16px">
                <div class="card-header">
                    <span class="card-title">Curriculum Syllabus</span>
                    <span style="font-size:11px;color:var(--text-muted)">Module coverage tracked via class sessions</span>
                </div>
                @foreach($subjects as $subject)
                <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
                    <div style="font-weight:600;font-size:13px;margin-bottom:6px">{{ $subject->name }} ({{ $subject->code }})</div>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        @foreach($subject->modules as $mod)
                        @php $covered = in_array($mod->id, $coveredModuleIds); @endphp
                        <div style="display:flex;align-items:center;gap:8px;font-size:12px;padding:4px 8px;background:{{ $covered ? '#f0fdf4' : '#f8fafc' }};border-radius:4px;border:1px solid {{ $covered ? '#bbf7d0' : '#e2e8f0' }}">
                            <span style="color:{{ $covered ? '#10b981' : '#9ca3af' }};font-size:14px">{{ $covered ? '✓' : '○' }}</span>
                            <span style="{{ $covered ? '' : 'color:#64748b' }}">{{ $mod->title }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- RIGHT: Enrolled Students --}}
        <div>
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                    <div>
                        <span class="card-title">Enrolled Students</span>
                        <span class="badge badge-info no-dot">{{ $batch->enrollments->count() }}</span>
                    </div>
                    @if($batch->enrollments->count() > 1)
                    <form method="POST" action="{{ route('admin.batches.split-students', $batch) }}" style="display:inline" onsubmit="return confirm('সক্রিয় শিক্ষার্থীদের গ্রুপ ক এবং গ্রুপ খ-তে ৫০/৫০ বিভাজন করবেন?')">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="font-size:11px;padding:2px 8px" title="অটো ৫০/৫০ স্প্লিট">
                            <i class="fa-solid fa-users-viewfinder"></i> Auto-Split 50/50
                        </button>
                    </form>
                    @endif
                </div>
                @if($batch->enrollments->isEmpty())
                    <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">No students enrolled yet.</div>
                @else
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Group / শাখা</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->enrollments as $en)
                            <tr>
                                <td style="font-size:11px;color:#3b82f6;font-weight:600">{{ $en->student->student_code }}</td>
                                <td class="td-primary" style="font-size:12px">
                                    {{ $en->student->name }}
                                    <div class="td-muted" style="font-size:10px">
                                        @if(strtoupper($en->student->gender ?? '') === 'MALE')
                                            <span style="color:#0284c7">ভাই / পুরুষ</span>
                                        @elseif(strtoupper($en->student->gender ?? '') === 'FEMALE')
                                            <span style="color:#ec4899">বোন / মহিলা</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.enrollments.set-group', $en) }}" style="margin:0">
                                        @csrf
                                        <select name="group_tag" class="form-control" style="font-size:11px;padding:2px 6px;height:26px" onchange="this.form.submit()">
                                            <option value="">— অটো / ডিফল্ট —</option>
                                            <option value="MALE" {{ $en->group_tag === 'MALE' ? 'selected' : '' }}>ভাই শাখা (জুম)</option>
                                            <option value="FEMALE" {{ $en->group_tag === 'FEMALE' ? 'selected' : '' }}>বোন শাখা (জুম)</option>
                                            <option value="GROUP_A" {{ $en->group_tag === 'GROUP_A' ? 'selected' : '' }}>গ্রুপ ক (Group A)</option>
                                            <option value="GROUP_B" {{ $en->group_tag === 'GROUP_B' ? 'selected' : '' }}>গ্রুপ খ (Group B)</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Zoom Limit Overflow Modal -->
    <div id="zoomOverflowModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:99999;align-items:center;justify-content:center;backdrop-filter:blur(3px);">
        <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);font-family:'Kalpurush',sans-serif">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px">
                <h4 style="margin:0;font-size:16px;color:#1e293b;font-weight:700">
                    <i class="fa-solid fa-arrows-split-up-and-left" style="color:#2563eb"></i> জুম লিমিট ওভারফ্লো সমন্বয় (Zoom Limit Overflow)
                </h4>
                <button type="button" onclick="document.getElementById('zoomOverflowModal').style.display='none'" style="background:none;border:none;font-size:18px;color:#64748b;cursor:pointer">&times;</button>
            </div>
            <p style="font-size:12px;color:#64748b;margin-bottom:16px;line-height:1.5">
                বোন শাখায় জুম পার্টিসিপেন্ট লিমিট শেষ হয়ে গেলে শেষের রোলের বোনদের ভাইদের জুম রুমে স্থানান্তর করতে এই অপশনটি ব্যবহার করুন।
            </p>
            <form method="POST" action="{{ route('admin.batches.bulk-allocate-group', $batch) }}">
                @csrf
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">শিক্ষার্থী ফিল্টার:</label>
                    <select name="gender" class="form-control" style="width:100%;font-size:12px;font-family:'Kalpurush',sans-serif">
                        <option value="FEMALE" selected>শুধুমাত্র বোন শিক্ষার্থী (Female)</option>
                        <option value="MALE">শুধুমাত্র ভাই শিক্ষার্থী (Male)</option>
                        <option value="ALL">সকল শিক্ষার্থী (All)</option>
                    </select>
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">টার্গেট শাখা / গ্রুপ:</label>
                    <select name="target_group" class="form-control" style="width:100%;font-size:12px;font-family:'Kalpurush',sans-serif" required>
                        <option value="MALE" selected>ভাই শাখা (MALE - ভাইদের জুম ক্লাস)</option>
                        <option value="FEMALE">বোন শাখা (FEMALE)</option>
                        <option value="GROUP_A">গ্রুপ ক (Group A)</option>
                        <option value="GROUP_B">গ্রুপ খ (Group B)</option>
                        <option value="RESET">রিসেট করুন (পূর্বের ডিফল্ট অবস্থায় ফেরত)</option>
                    </select>
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">বরাদ্দ পদ্ধতি (Mode):</label>
                    <div style="display:flex;gap:16px;font-size:12px;margin-bottom:8px">
                        <label style="cursor:pointer;display:flex;align-items:center;gap:4px">
                            <input type="radio" name="mode" value="last_count" checked onchange="toggleOverflowMode('last_count')"> শেষের N জন শিক্ষার্থী
                        </label>
                        <label style="cursor:pointer;display:flex;align-items:center;gap:4px">
                            <input type="radio" name="mode" value="roll_range" onchange="toggleOverflowMode('roll_range')"> রোল নম্বর রেঞ্জ
                        </label>
                    </div>
                </div>
                <div id="modeLastCountSection" style="margin-bottom:16px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">সর্বশেষ কতজন শিক্ষার্থী স্থানান্তর করবেন?</label>
                    <input type="number" name="last_count" class="form-control" placeholder="যেমন: 30 বা 50" value="30" min="1" style="width:100%;font-size:12px">
                </div>
                <div id="modeRollRangeSection" style="display:none;margin-bottom:16px">
                    <label style="display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:#334155">রোল রেঞ্জ (Roll From - To):</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" name="roll_from" class="form-control" placeholder="রোল হতে (যেমন 150)" style="width:50%;font-size:12px">
                        <input type="text" name="roll_to" class="form-control" placeholder="রোল পর্যন্ত (যেমন 220)" style="width:50%;font-size:12px">
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:8px">
                    <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('zoomOverflowModal').style.display='none'">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-family:'Kalpurush',sans-serif">সমন্বয় নিশ্চিত করুন</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function toggleOverflowMode(mode) {
            document.getElementById('modeLastCountSection').style.display = mode === 'last_count' ? 'block' : 'none';
            document.getElementById('modeRollRangeSection').style.display = mode === 'roll_range' ? 'block' : 'none';
        }
    </script>
</x-admin-layout>
