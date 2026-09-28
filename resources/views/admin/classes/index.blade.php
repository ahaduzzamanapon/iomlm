<x-admin-layout>
    <x-slot name="title">Class Sessions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush',sans-serif">ক্লাস সেশন ও লাইভ ক্লাস (Class Sessions)</h1>
            <p style="font-family:'Kalpurush',sans-serif">রুটিনভিত্তিক নিয়মিত ক্লাস ও রুটিনের বাইরের এক্সট্রা ক্লাস শিডিউলিং — মিটিং লিংক ও হাজিরা ব্যবস্থাপনা</p>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn btn-primary" onclick="openExtraClassModal()" style="background:#be123c;border-color:#be123c;font-family:'Kalpurush',sans-serif;font-weight:700">
                <i class="fa-solid fa-calendar-plus"></i> + এক্সট্রা ক্লাস যোগ করুন
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card" style="padding:14px 16px;margin-bottom:16px;font-family:'Kalpurush',sans-serif">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between">
            <form method="GET" action="{{ route('admin.classes.index') }}" id="filterForm" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <select name="batch_id" class="form-control" style="min-width:200px" onchange="document.getElementById('filterForm').submit()">
                    <option value="">সকল ব্যাচ (All Batches)</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ $batchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="date" class="form-control" value="{{ $dateFilter }}" style="min-width:150px" onchange="document.getElementById('filterForm').submit()" title="Filter by date">
                @if($dateFilter)
                    <a href="{{ route('admin.classes.index', array_filter(['status' => $status ?: null, 'batch_id' => $batchId ?: null, 'type' => $type ?: null])) }}" class="btn btn-sm btn-ghost" title="Clear date">তারিখ মুছুন</a>
                @endif
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="type" value="{{ $type }}">
            </form>

            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                {{-- Class Type Filter (All, Regular, Extra) --}}
                <div style="display:flex;gap:4px;background:#f1f5f9;padding:3px;border-radius:6px">
                    <a href="{{ route('admin.classes.index', array_filter(['status' => $status ?: null, 'batch_id' => $batchId ?: null, 'date' => $dateFilter ?: null])) }}"
                       class="btn btn-sm {{ empty($type) ? 'btn-primary' : 'btn-ghost' }}" style="font-size:12px;padding:3px 10px">
                       সকল ক্লাস
                    </a>
                    <a href="{{ route('admin.classes.index', array_filter(['status' => $status ?: null, 'batch_id' => $batchId ?: null, 'date' => $dateFilter ?: null, 'type' => 'extra'])) }}"
                       class="btn btn-sm {{ ($type ?? '') === 'extra' ? 'btn-primary' : 'btn-ghost' }}" style="font-size:12px;padding:3px 10px;{{ ($type ?? '') === 'extra' ? 'background:#be123c;border-color:#be123c;' : 'color:#be123c;' }}">
                       <i class="fa-solid fa-star"></i> এক্সট্রা ক্লাস
                    </a>
                    <a href="{{ route('admin.classes.index', array_filter(['status' => $status ?: null, 'batch_id' => $batchId ?: null, 'date' => $dateFilter ?: null, 'type' => 'regular'])) }}"
                       class="btn btn-sm {{ ($type ?? '') === 'regular' ? 'btn-primary' : 'btn-ghost' }}" style="font-size:12px;padding:3px 10px">
                       নিয়মিত ক্লাস
                    </a>
                </div>

                {{-- Status tabs --}}
                <div style="display:flex;gap:4px">
                    @foreach(['' => 'সব স্ট্যাটাস', 'SCHEDULED' => 'Scheduled', 'COMPLETED' => 'Completed', 'CANCELLED' => 'Cancelled'] as $val => $label)
                        <a href="{{ route('admin.classes.index', array_filter(['status' => $val ?: null, 'batch_id' => $batchId ?: null, 'date' => $dateFilter ?: null, 'type' => $type ?: null])) }}"
                           class="btn btn-sm {{ ($status ?? '') === $val ? 'btn-primary' : 'btn-outline' }}" style="font-size:12px;padding:3px 8px">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="font-family:'Kalpurush',sans-serif">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span class="card-title">
                @if($dateFilter)
                    {{ \Carbon\Carbon::parse($dateFilter)->format('l, d M Y') }}
                    — {{ $classes->count() }}টি সেশন
                @else
                    মোট {{ $classes->count() }}টি সেশন
                    @if($type === 'extra') <span class="badge" style="background:#fef3c7;color:#92400e;margin-left:6px"><i class="fa-solid fa-star"></i> শুধুমাত্র এক্সট্রা ক্লাস</span> @endif
                    @if($status) — {{ $status }} @endif
                @endif
            </span>
        </div>

        @if($classes->isEmpty())
            <div style="padding:40px;text-align:center;color:var(--text-muted)">
                <p>কোনো ক্লাস সেশন পাওয়া যায়নি। নতুন এক্সট্রা ক্লাস শিডিউল করতে উপরের "+ এক্সট্রা ক্লাস যোগ করুন" বাটনে ক্লিক করুন।</p>
            </div>
        @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>বিষয় ও ক্লাসের ধরন</th>
                        <th>ব্যাচ ও শাখা</th>
                        <th>শিক্ষক</th>
                        <th>তারিখ ও সময়</th>
                        <th>মিটিং লিংক</th>
                        <th>কভার্ড মডিউল</th>
                        <th>হাজিরা</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $cs)
                    @php
                        $isPast = $cs->session_date && $cs->session_date->isPast();
                        $isToday = $cs->session_date && $cs->session_date->isToday();
                        $isExtra = $cs->is_extra;
                    @endphp
                    <tr style="{{ $isToday ? 'background:#eff6ff;' : '' }}">
                        <td class="td-primary">
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                <strong>{{ $cs->subject?->name ?? '—' }}</strong>
                                @if($isExtra)
                                    <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:10px;font-weight:700">
                                        <i class="fa-solid fa-star"></i> এক্সট্রা ক্লাস
                                    </span>
                                @endif
                            </div>
                            @if($cs->title)
                                <div style="font-size:11px;color:#be123c;font-weight:600;margin-top:2px">{{ $cs->title }}</div>
                            @endif
                            @if($cs->reason)
                                <div style="font-size:10.5px;color:#64748b">{{ $cs->reason }}</div>
                            @endif
                            <div class="td-muted" style="font-size:10px">{{ $cs->subject?->code }}</div>
                        </td>
                        <td class="td-muted">
                            <div>{{ $cs->batch?->name ?? '—' }}</div>
                            <span class="badge badge-secondary no-dot" style="font-size:10px">{{ $cs->group_label }}</span>
                        </td>
                        <td class="td-muted">{{ $cs->teacher?->name ?? '—' }}</td>
                        <td>
                            <strong style="font-size:12px">
                                {{ $cs->session_date ? $cs->session_date->format('D, d M Y') : 'TBA' }}
                                @if($isToday) <span class="badge badge-success no-dot" style="font-size:9px">TODAY</span> @endif
                            </strong>
                            <div class="td-muted" style="font-size:10.5px">
                                @if($cs->start_time)
                                    {{ \Carbon\Carbon::parse($cs->start_time)->format('h:i A') }}
                                    @if($cs->end_time) – {{ \Carbon\Carbon::parse($cs->end_time)->format('h:i A') }} @endif
                                @endif
                                @if($cs->routineEntry?->slot?->name)
                                    &middot; {{ $cs->routineEntry->slot->name }}
                                @elseif($isExtra)
                                    &middot; <span style="color:#b45309">রুটিন বহির্ভূত</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($cs->meeting_link)
                                <a href="{{ $cs->meeting_link }}" target="_blank" class="btn btn-sm btn-outline" style="font-size:11px">Join</a>
                            @else
                                <form method="POST" action="{{ route('admin.classes.generateZoom', $cs) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline" style="font-size:11px;color:#2563eb" title="Generate Real Zoom Link">
                                        Zoom Link
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="td-muted" style="font-size:11px">
                            {{ $cs->moduleCovered?->title ?? '—' }}
                        </td>
                        <td style="text-align:center">
                            @php $attended = $cs->attendances->where('status','PRESENT')->count(); $total = $cs->attendances->count(); @endphp
                            @if($total > 0)
                                <span style="font-size:12px;font-weight:700;color:#10b981">{{ $attended }}/{{ $total }}</span>
                            @else
                                <span style="color:#d1d5db;font-size:12px">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match($cs->status) {
                                    'COMPLETED'  => 'badge-success',
                                    'SCHEDULED'  => 'badge-info',
                                    'CANCELLED'  => 'badge-danger',
                                    'UPCOMING'   => 'badge-warning',
                                    default      => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }} no-dot">{{ $cs->status }}</span>
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <a href="{{ route('admin.classes.show', $cs) }}" class="btn btn-sm btn-outline" style="font-size:11px">ম্যানেজ →</a>
                            @if($isExtra)
                                <form method="POST" action="{{ route('admin.classes.destroy', $cs) }}" style="display:inline" onsubmit="return confirm('এই এক্সট্রা ক্লাসটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-red" title="এক্সট্রা ক্লাস মুছুন">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Add Extra Class Modal ── --}}
    <div class="modal-overlay" id="addExtraClassModal">
        <div class="modal" style="max-width:620px;width:95%;font-family:'Kalpurush',sans-serif">
            <div class="modal-header" style="background:#be123c;color:#fff">
                <span class="modal-title" style="color:#fff;font-size:15px;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-calendar-plus"></i> রুটিনের বাইরে এক্সট্রা ক্লাস শিডিউল করুন (Extra Class)
                </span>
                <button class="modal-close" style="color:#fff" onclick="closeModal('addExtraClassModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.classes.store') }}">
                @csrf
                <div class="modal-body" style="padding:20px">
                    <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#9f1239">
                        <i class="fa-solid fa-circle-info"></i> <strong>এক্সট্রা ক্লাস:</strong> নিয়মিত সাপ্তাহিক রুটিনের বাইরে যেকোনো দিন ও সময়ে মেকআপ, রিভিশন বা অতিরিক্ত ক্লাস শিডিউল করতে এই ফর্ম ব্যবহার করুন। শিক্ষার্থী ও শিক্ষকের পোর্টালে এটি দৃশ্যমান হবে।
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
                            <label>ক্লাস শিক্ষক (Teacher)</label>
                            <select name="teacher_id" class="form-control">
                                <option value="">-- শিক্ষক নির্বাচন করুন (ঐচ্ছিক) --</option>
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>শাখা / গ্রুপ (Branch / Group)</label>
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
                            <label>ক্লাসের তারিখ (Date) <span class="required" style="color:#e11d48">*</span></label>
                            <input type="date" name="session_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
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
                    <button type="button" class="btn btn-outline" onclick="closeModal('addExtraClassModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#be123c;border-color:#be123c;font-weight:700">
                        <i class="fa-solid fa-check"></i> এক্সট্রা ক্লাস শিডিউল করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openExtraClassModal() {
        openModal('addExtraClassModal');
    }
    </script>
    @endpush
</x-admin-layout>
