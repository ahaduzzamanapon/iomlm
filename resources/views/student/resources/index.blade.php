<x-student-layout>
    <x-slot name="title">Learning Materials & Resources</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush',sans-serif">লার্নিং রিসোর্স ও স্টাডি ম্যাটেরিয়াল (Learning Resources)</h1>
            <p style="font-family:'Kalpurush',sans-serif;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span>ক্লাস লেকচার নোটস, পিডিএফ বই, ড্রাইভ লিংক ও সম্পূরক শিক্ষাসামগ্রী</span>
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
        <div class="card" style="margin-bottom:18px;padding:14px 20px;font-family:'Kalpurush',sans-serif">
            <form method="GET" action="{{ route('student.resources.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:0">
                <label for="subject_filter" style="font-weight:700;color:#334155;font-size:13px;display:flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-filter" style="color:#047857"></i> চলতি সেমিস্টারের বিষয় অনুযায়ী ফিল্টার:
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
                        <i class="fa-solid fa-rotate-left"></i> রিসেট
                    </a>
                @endif
            </form>
        </div>
    @endif

    <div class="card" style="font-family:'Kalpurush',sans-serif">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="font-weight:700">রিসোর্স ও ম্যাটেরিয়াল নাম</th>
                        <th style="font-weight:700">বিষয় ও মডিউল</th>
                        <th style="font-weight:700">ধরন (Type)</th>
                        <th style="font-weight:700">তারিখ</th>
                        <th style="text-align:right;font-weight:700">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resources as $res)
                    @php
                        $type = strtoupper($res['type'] ?? 'LINK');
                        $typeConfig = match($type) {
                            'PDF'        => ['icon' => 'fa-file-pdf', 'color' => '#dc2626', 'badge' => 'badge-danger', 'label' => 'PDF ডকুমেন্ট'],
                            'SLIDES'     => ['icon' => 'fa-file-powerpoint', 'color' => '#ea580c', 'badge' => 'badge-warning', 'label' => 'স্লাইড / নোট'],
                            'DRIVE'      => ['icon' => 'fa-google-drive', 'color' => '#2563eb', 'badge' => 'badge-info', 'label' => 'Google Drive'],
                            'VIDEO'      => ['icon' => 'fa-video', 'color' => '#7c3aed', 'badge' => 'badge-primary', 'label' => 'ভিডিও লেকচার'],
                            'ATTACHMENT' => ['icon' => 'fa-paperclip', 'color' => '#059669', 'badge' => 'badge-success', 'label' => 'ফাইল অ্যাটাচমেন্ট'],
                            default      => ['icon' => 'fa-link', 'color' => '#475569', 'badge' => 'badge-secondary', 'label' => $type],
                        };
                    @endphp
                    <tr>
                        <td class="td-primary">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:36px;height:36px;border-radius:8px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;color:{{ $typeConfig['color'] }};font-size:16px;flex-shrink:0">
                                    <i class="fa-solid {{ $typeConfig['icon'] }}"></i>
                                </div>
                                <div>
                                    <strong style="color:#0f172a;font-size:14px">{{ $res['title'] }}</strong>
                                    @if(!empty($res['description']))
                                        <div style="font-size:12px;color:#64748b;margin-top:2px;max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                            {{ $res['description'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong style="color:#1e293b;font-size:13px">{{ $res['subject_name'] }}</strong><br>
                            <span class="td-muted" style="font-size:12px">মডিউল: {{ $res['module_title'] }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $typeConfig['badge'] }} no-dot" style="font-size:11.5px;font-weight:700">
                                {{ $typeConfig['label'] }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:#64748b">
                            {{ $res['created_at'] ? \Carbon\Carbon::parse($res['created_at'])->format('d M Y') : '—' }}
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            @if(!empty($res['url']) && $res['url'] !== '#')
                                <a href="{{ $res['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:12px;border-color:#047857;color:#047857">
                                    @if($type === 'VIDEO')
                                        <i class="fa-solid fa-play" style="font-size:11px"></i> দেখুন / চালান
                                    @elseif($type === 'DRIVE')
                                        <i class="fa-brands fa-google-drive"></i> ড্রাইভ লিংক ↗
                                    @else
                                        <i class="fa-solid fa-download"></i> ওপেন / ডাউনলোড ↗
                                    @endif
                                </a>
                            @else
                                <span class="td-muted" style="font-size:12px">নোট উপলব্ধ</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:45px 20px;color:var(--text-muted)">
                            <div style="font-size:36px;color:#cbd5e1;margin-bottom:8px">
                                <i class="fa-solid fa-folder-open"></i>
                            </div>
                            <h3 style="margin:0 0 4px;font-size:15px;font-weight:700;color:#334155">কোনো লার্নিং রিসোর্স পাওয়া যায়নি</h3>
                            <p style="margin:0;font-size:13px;color:#64748b">আপনার এনরোলকৃত বিষয়ে এখনো কোনো রিসোর্স বা ম্যাটেরিয়াল যুক্ত করা হয়নি।</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-student-layout>
