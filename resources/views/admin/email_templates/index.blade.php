<x-admin-layout>
    <x-slot name="title">ইমেইল টেমপ্লেট ব্যবস্থাপনা</x-slot>

<div class="content-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0f172a;margin-bottom:4px;">
            <i class="fa-solid fa-envelope-open-text" style="color:#047857;margin-right:8px;"></i>
            ইমেইল টেমপ্লেট ব্যবস্থাপনা
        </h1>
        <p style="font-size:13.5px;color:#64748b;margin:0;">
            কোর্স, ব্যাচ ও জেন্ডার ভিত্তিক ভর্তি নিশ্চিতকরণ এবং অন্যান্য প্রাতিষ্ঠানিক ইমেইল টেমপ্লেট পরিচালনা করুন।
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;font-size:13.5px;font-weight:600;border-radius:8px;">
            <i class="fa-solid fa-plus"></i> নতুন টেমপ্লেট যুক্ত করুন
        </a>
    </div>
</div>

{{-- Filters Card --}}
<div class="card" style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <form method="GET" action="{{ route('admin.email-templates.index') }}" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)) 120px;gap:14px;align-items:end;">
        <div>
            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:6px;">খুঁজুন (নাম বা বিষয়)</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="টেমপ্লেট নাম..." 
                   style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
        </div>

        <div>
            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:6px;">কোর্স</label>
            <select name="course_id" id="filter_course_id" onchange="filterIndexBatches()" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                <option value="">— সকল কোর্স —</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ (string)request('course_id') === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->code ? "[{$c->code}] " : '' }}{{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:6px;">ব্যাচ</label>
            <select name="batch_id" id="filter_batch_id" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                <option value="">— সকল ব্যাচ —</option>
                @foreach($batches as $b)
                    @if(!request('course_id') || (string)$b->course_id === (string)request('course_id'))
                        <option value="{{ $b->id }}" data-course="{{ $b->course_id }}" {{ (string)request('batch_id') === (string)$b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:6px;">শাখা / জেন্ডার</label>
            <select name="gender" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                <option value="ALL_GENDERS">— সকল জেন্ডার —</option>
                <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>ভাইদের শাখা (পুরুষ)</option>
                <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>বোনদের শাখা (মহিলা)</option>
                <option value="All" {{ request('gender') === 'All' ? 'selected' : '' }}>উভয় (সকলের জন্য)</option>
            </select>
        </div>

        <div>
            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:6px;">ক্যাটেগরি</label>
            <select name="category" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                <option value="ALL_CATEGORIES">— সকল ক্যাটেগরি —</option>
                <option value="ADMISSION" {{ request('category') === 'ADMISSION' ? 'selected' : '' }}>ভর্তি সংক্রান্ত (ADMISSION)</option>
                <option value="FEES" {{ request('category') === 'FEES' ? 'selected' : '' }}>ফি ও একাউন্টস</option>
                <option value="EXAM" {{ request('category') === 'EXAM' ? 'selected' : '' }}>পরীক্ষা সংক্রান্ত</option>
                <option value="CLASS" {{ request('category') === 'CLASS' ? 'selected' : '' }}>ক্লাস ও রুটিন</option>
                <option value="HOLIDAY" {{ request('category') === 'HOLIDAY' ? 'selected' : '' }}>ছুটি সংক্রান্ত</option>
                <option value="GENERAL" {{ request('category') === 'GENERAL' ? 'selected' : '' }}>সাধারণ বিজ্ঞপ্তি</option>
            </select>
        </div>

        <div style="display:flex;gap:6px;">
            <button type="submit" class="btn btn-primary" style="flex:1;padding:8px;font-size:13px;border-radius:6px;">
                <i class="fa-solid fa-filter"></i> ফিল্টার
            </button>
            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline" style="padding:8px 10px;font-size:13px;border-radius:6px;border:1px solid #cbd5e1;text-decoration:none;color:#64748b;display:inline-flex;align-items:center;justify-content:center;" title="রিসেট">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

{{-- Templates Table Card --}}
<div class="card" style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fafbfc;">
        <span style="font-weight:700;font-size:14px;color:#334155;">
            মোট টেমপ্লেট: {{ $templates->total() }}টি
        </span>
        <span style="font-size:12px;color:#64748b;">
            <i class="fa-solid fa-circle-info" style="color:#0284c7"></i> ভর্তি সম্পন্ন বা এপ্রুভের সময় স্বয়ংক্রিয়ভাবে কোর্স, ব্যাচ ও জেন্ডার অনুযায়ী টেমপ্লেট সিলেক্ট হবে।
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13.5px;text-align:left;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:600;font-size:12.5px;text-transform:uppercase;letter-spacing:0.5px;">
                    <th style="padding:12px 18px;">টেমপ্লেটের নাম</th>
                    <th style="padding:12px 14px;">ক্যাটেগরি</th>
                    <th style="padding:12px 14px;">কোর্স ও ব্যাচ</th>
                    <th style="padding:12px 14px;">শাখা / জেন্ডার</th>
                    <th style="padding:12px 14px;">ইমেইল বিষয় (Subject)</th>
                    <th style="padding:12px 14px;text-align:center;">অবস্থা</th>
                    <th style="padding:12px 18px;text-align:right;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $tpl)
                    <tr style="border-bottom:1px solid #f1f5f9;transition:background .15s;" onmouseover="this.style.background='#fcfdfd'" onmouseout="this.style.background='transparent'">
                        <td style="padding:14px 18px;font-weight:600;color:#0f172a;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="color:#047857;font-family:monospace;font-size:14px;background:#ecfdf5;padding:3px 8px;border-radius:6px;border:1px solid #a7f3d0;">
                                    {{ $tpl->name }}
                                </span>
                                @if($tpl->is_system)
                                    <span style="font-size:10.5px;background:#f1f5f9;color:#64748b;padding:2px 6px;border-radius:4px;font-weight:600;">সিস্টেম</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding:14px 14px;">
                            @php
                                $badgeColor = match($tpl->category) {
                                    'ADMISSION' => 'background:#dbeafe;color:#1e40af;',
                                    'FEES'      => 'background:#fef3c7;color:#92400e;',
                                    'EXAM'      => 'background:#fce7f3;color:#9d174d;',
                                    'CLASS'     => 'background:#e0e7ff;color:#3730a3;',
                                    'HOLIDAY'   => 'background:#fee2e2;color:#991b1b;',
                                    default     => 'background:#f1f5f9;color:#475569;',
                                };
                            @endphp
                            <span style="display:inline-block;padding:3px 8px;border-radius:20px;font-size:11.5px;font-weight:600;{!! $badgeColor !!}">
                                {{ $tpl->category_label }}
                            </span>
                        </td>
                        <td style="padding:14px 14px;">
                            @if($tpl->course)
                                <div style="font-weight:600;color:#1e293b;font-size:13px;">
                                    {{ $tpl->course->name }}
                                </div>
                                <div style="font-size:11.5px;color:#64748b;">
                                    ব্যাচ: <strong>{{ $tpl->batch?->name ?? 'সকল ব্যাচ' }}</strong>
                                </div>
                            @else
                                <span style="color:#94a3b8;font-size:12px;">(সাধারণ / সকল কোর্স)</span>
                            @endif
                        </td>
                        <td style="padding:14px 14px;">
                            @if($tpl->gender === 'Male')
                                <span style="background:#eff6ff;color:#1d4ed8;padding:3px 8px;border-radius:6px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                    <i class="fa-solid fa-mars"></i> ভাইদের শাখা
                                </span>
                            @elseif($tpl->gender === 'Female')
                                <span style="background:#fdf2f8;color:#be185d;padding:3px 8px;border-radius:6px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                    <i class="fa-solid fa-venus"></i> বোনদের শাখা
                                </span>
                            @else
                                <span style="background:#f8fafc;color:#64748b;padding:3px 8px;border-radius:6px;font-size:12px;font-weight:600;">
                                    উভয় শাখা
                                </span>
                            @endif
                        </td>
                        <td style="padding:14px 14px;color:#334155;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $tpl->subject }}">
                            {{ $tpl->subject ?: '—' }}
                        </td>
                        <td style="padding:14px 14px;text-align:center;">
                            @if($tpl->is_active)
                                <span style="color:#15803d;background:#dcfce7;padding:2px 8px;border-radius:12px;font-size:11.5px;font-weight:600;">
                                    সক্রিয়
                                </span>
                            @else
                                <span style="color:#991b1b;background:#fee2e2;padding:2px 8px;border-radius:12px;font-size:11.5px;font-weight:600;">
                                    নিষ্ক্রিয়
                                </span>
                            @endif
                        </td>
                        <td style="padding:14px 18px;text-align:right;">
                            <div style="display:inline-flex;gap:6px;align-items:center;">
                                <button type="button" class="btn btn-outline" onclick="previewTemplate({{ $tpl->id }})" 
                                        style="padding:5px 9px;font-size:12px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;color:#0284c7;cursor:pointer;" title="প্রিভিউ দেখুন">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <a href="{{ route('admin.email-templates.edit', $tpl->id) }}" class="btn btn-outline" 
                                   style="padding:5px 9px;font-size:12px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;color:#047857;text-decoration:none;" title="এডিট করুন">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if(!$tpl->is_system)
                                    <form method="POST" action="{{ route('admin.email-templates.destroy', $tpl->id) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই টেমপ্লেটটি মুছে ফেলতে চান?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline" style="padding:5px 9px;font-size:12px;border-radius:6px;border:1px solid #fecaca;background:#fff;color:#dc2626;cursor:pointer;" title="মুছে ফেলুন">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:36px;text-align:center;color:#64748b;">
                            <i class="fa-solid fa-folder-open" style="font-size:36px;color:#cbd5e1;margin-bottom:12px;display:block;"></i>
                            কোনো ইমেইল টেমপ্লেট পাওয়া যায়নি।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($templates->hasPages())
        <div style="padding:16px 20px;border-top:1px solid #f1f5f9;">
            {{ $templates->links() }}
        </div>
    @endif
</div>

{{-- Preview Modal --}}
<div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:99999;backdrop-filter:blur(3px);align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:12px;max-width:650px;width:100%;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;display:flex;flex-direction:column;max-height:85vh;">
        <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;background:#fafbfc;">
            <h3 id="previewTitle" style="font-size:16px;font-weight:700;color:#0f172a;margin:0;">টেমপ্লেট প্রিভিউ</h3>
            <button type="button" onclick="closePreviewModal()" style="background:none;border:none;font-size:20px;color:#64748b;cursor:pointer;">&times;</button>
        </div>
        <div style="padding:20px;overflow-y:auto;flex:1;">
            <div style="margin-bottom:12px;">
                <span style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px;">বিষয় (Subject):</span>
                <div id="previewSubject" style="font-weight:700;font-size:14px;color:#1e293b;padding:8px 12px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;"></div>
            </div>
            <div>
                <span style="font-size:12px;font-weight:600;color:#64748b;display:block;margin-bottom:4px;">বার্তা (Message Content):</span>
                <div id="previewContent" style="white-space:pre-wrap;font-size:13.5px;color:#334155;line-height:1.7;padding:12px 14px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;font-family:inherit;"></div>
            </div>
        </div>
        <div style="padding:12px 20px;border-top:1px solid #e2e8f0;background:#fafbfc;text-align:right;">
            <button type="button" onclick="closePreviewModal()" class="btn btn-secondary" style="padding:7px 18px;border-radius:6px;font-size:13px;">বন্ধ করুন</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const allTemplates = @json($templates->items());
    const allBatches = {!! json_encode($batches->map(fn($b) => [
        'id' => $b->id,
        'name' => $b->name,
        'course_id' => $b->course_id,
    ])) !!};

    function filterIndexBatches() {
        const courseSelect = document.getElementById('filter_course_id');
        const batchSelect = document.getElementById('filter_batch_id');
        if (!courseSelect || !batchSelect) return;

        const courseId = courseSelect.value;
        const currentSelected = batchSelect.value;

        batchSelect.innerHTML = '<option value="">— সকল ব্যাচ —</option>';

        const filtered = courseId 
            ? allBatches.filter(b => String(b.course_id) === String(courseId))
            : allBatches;

        filtered.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = b.name;
            opt.setAttribute('data-course', b.course_id);
            if (String(b.id) === String(currentSelected)) {
                opt.selected = true;
            }
            batchSelect.appendChild(opt);
        });
    }

    function previewTemplate(id) {
        const tpl = allTemplates.find(t => t.id === id);
        if (!tpl) return;
        document.getElementById('previewTitle').innerText = tpl.name + ' (' + (tpl.gender_label || '') + ')';
        document.getElementById('previewSubject').innerText = tpl.subject || '—';
        document.getElementById('previewContent').innerText = tpl.content || '';
        document.getElementById('previewModal').style.display = 'flex';
    }

    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
    }
</script>
@endpush
</x-admin-layout>
