@extends('admin.layouts.app')

@section('title', 'নতুন ইমেইল টেমপ্লেট যুক্ত করুন')

@section('content')
<div class="content-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0f172a;margin-bottom:4px;">
            <i class="fa-solid fa-plus-circle" style="color:#047857;margin-right:8px;"></i>
            নতুন ইমেইল টেমপ্লেট যুক্ত করুন
        </h1>
        <p style="font-size:13.5px;color:#64748b;margin:0;">
            কোর্স, ব্যাচ ও জেন্ডার অনুযায়ী নির্দিষ্ট ভর্তি নিশ্চিতকরণ বা নোটিশ টেমপ্লেট তৈরি করুন।
        </p>
    </div>
    <div>
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;font-size:13.5px;font-weight:600;border-radius:8px;border:1px solid #cbd5e1;text-decoration:none;color:#475569;background:#fff;">
            <i class="fa-solid fa-arrow-left"></i> টেমপ্লেট তালিকায় ফিরে যান
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.email-templates.store') }}">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">
        {{-- Main Form Card --}}
        <div class="card" style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                    টেমপ্লেটের নাম <span style="color:#dc2626;">*</span>
                    <span style="font-size:12px;font-weight:400;color:#64748b;">(ড্রপডাউনে এই নামটি প্রদর্শিত হবে, যেমন: <code>Alim2717Male</code>, <code>SM2817Female</code>)</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="উদা: Alim2717Male" 
                       style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;font-weight:600;color:#0f172a;">
                @error('name')
                    <span style="color:#dc2626;font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                        ক্যাটেগরি <span style="color:#dc2626;">*</span>
                    </label>
                    <select name="category" required style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;">
                        <option value="ADMISSION" {{ old('category', 'ADMISSION') === 'ADMISSION' ? 'selected' : '' }}>ভর্তি সংক্রান্ত (ADMISSION)</option>
                        <option value="FEES" {{ old('category') === 'FEES' ? 'selected' : '' }}>ফি ও একাউন্টস (FEES)</option>
                        <option value="EXAM" {{ old('category') === 'EXAM' ? 'selected' : '' }}>পরীক্ষা সংক্রান্ত (EXAM)</option>
                        <option value="CLASS" {{ old('category') === 'CLASS' ? 'selected' : '' }}>ক্লাস ও রুটিন (CLASS)</option>
                        <option value="HOLIDAY" {{ old('category') === 'HOLIDAY' ? 'selected' : '' }}>ছুটি সংক্রান্ত (HOLIDAY)</option>
                        <option value="GENERAL" {{ old('category') === 'GENERAL' ? 'selected' : '' }}>সাধারণ বিজ্ঞপ্তি (GENERAL)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                        শাখা / জেন্ডার <span style="color:#dc2626;">*</span>
                    </label>
                    <select name="gender" required style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;">
                        <option value="Male" {{ old('gender', 'Male') === 'Male' ? 'selected' : '' }}>ভাইদের শাখা (পুরুষ - Male)</option>
                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>বোনদের শাখা (মহিলা - Female)</option>
                        <option value="All" {{ old('gender') === 'All' ? 'selected' : '' }}>উভয় শাখা (সকলের জন্য - All)</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                        নির্দিষ্ট কোর্স <span style="font-size:12px;font-weight:400;color:#64748b;">(ঐচ্ছিক)</span>
                    </label>
                    <select name="course_id" id="course_id" onchange="filterBatches()" style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;">
                        <option value="">— সকল কোর্স (সাধারণ) —</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->code ? "[{$c->code}] " : '' }}{{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                        নির্দিষ্ট ব্যাচ <span style="font-size:12px;font-weight:400;color:#64748b;">(ঐচ্ছিক)</span>
                    </label>
                    <select name="batch_id" id="batch_id" style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;">
                        <option value="">— সকল ব্যাচ —</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" data-course="{{ $b->course_id }}" {{ old('batch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->course?->name ?? 'Course' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px;">
                    ইমেইল বিষয় (Email Subject) <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="subject" value="{{ old('subject', '🎉 অভিনন্দন: {course} ({batch}) কোর্সে আপনার ভর্তি নিশ্চিত হয়েছে') }}" required 
                       placeholder="ইমেইলের বিষয়..." 
                       style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;">
                @error('subject')
                    <span style="color:#dc2626;font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <label style="font-size:13px;font-weight:700;color:#1e293b;margin:0;">
                        ইমেইল মেসেজ / বিষয়বস্তু (Content) <span style="color:#dc2626;">*</span>
                    </label>
                    <span style="font-size:12px;color:#64748b;">ডানপাশের ভ্যারিয়েবল ট্যাগগুলো ক্লিক করে যুক্ত করতে পারেন</span>
                </div>
                <textarea name="content" id="templateContent" rows="12" required 
                          style="width:100%;padding:12px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;line-height:1.7;font-family:inherit;">{{ old('content', "আসসালামু আলাইকুম {name},\n\nআলহামদুলিল্লাহ! ইসলামিক অনলাইন মাদ্রাসায় \"{course}\" ({batch}) কোর্সে আপনার ভর্তি সফলভাবে অনুমোদিত ও নিশ্চিত হয়েছে। আপনাকে আইওএম পরিবারে আন্তরিক অভিনন্দন ও মোবারকবাদ!\n\nআপনার অফিসিয়াল লগইন তথ্য:\n----------------------------------------\n• স্টুডেন্ট আইডি: {student_id}\n• লগইন পাসওয়ার্ড: {password}\n• পোর্টাল লিংক: {login_url}\n----------------------------------------\n\nআপনি আপনার স্টুডেন্ট আইডি অথবা ইমেইল এবং পাসওয়ার্ড দিয়ে স্টুডেন্ট পোর্টালে লগইন করতে পারবেন। নিয়মিত লাইভ ক্লাসে অংশ নিন এবং পোর্টাল থেকে শিক্ষণ সামগ্রী সংগ্রহ করুন।\n\nআপনার ইলমি সফর সুন্দর ও বরকতময় হোক। আমীন।\n\nবিনীত,\nভর্তি শাখা,\nইসলামিক অনলাইন মাদ্রাসা (IOM)") }}</textarea>
                @error('content')
                    <span style="color:#dc2626;font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                <label for="is_active" style="font-size:13.5px;font-weight:600;color:#334155;cursor:pointer;margin:0;">
                    টেমপ্লেটটি সক্রিয় (Active) রাখুন — ভর্তি প্রক্রিয়া ও এপ্রুভালের সময় ব্যবহারের জন্য
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline" style="padding:10px 20px;border-radius:8px;font-size:14px;text-decoration:none;border:1px solid #cbd5e1;color:#475569;">
                    বাতিল
                </a>
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;border-radius:8px;font-size:14px;font-weight:700;">
                    <i class="fa-solid fa-save"></i> টেমপ্লেট সংরক্ষণ করুন
                </button>
            </div>
        </div>

        {{-- Dynamic Tags Sidebar --}}
        <div>
            <div class="card" style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);margin-bottom:20px;">
                <h3 style="font-size:14.5px;font-weight:700;color:#0f172a;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-tags" style="color:#047857;"></i> ডায়নামিক ভ্যারিয়েবল
                </h3>
                <p style="font-size:12.5px;color:#64748b;margin-bottom:14px;line-height:1.5;">
                    নিচের ট্যাগে ক্লিক করলে তা মেসেজে যুক্ত হবে। ইমেইল প্রেরণের সময় তা শিক্ষার্থীর সঠিক তথ্য দ্বারা স্বয়ংক্রিয়ভাবে প্রতিস্থাপিত হবে:
                </p>

                <div style="display:flex;flex-direction:column;gap:8px;">
                    @php
                        $tags = [
                            '{name}'       => 'শিক্ষার্থীর পুরো নাম',
                            '{student_id}' => 'অফিসিয়াল স্টুডেন্ট আইডি',
                            '{roll}'       => 'রোল নম্বর',
                            '{password}'   => 'লগইন পাসওয়ার্ড',
                            '{course}'     => 'কোর্সের নাম',
                            '{batch}'      => 'ব্যাচের নাম',
                            '{login_url}'  => 'স্টুডেন্ট পোর্টাল লিংক',
                        ];
                    @endphp

                    @foreach($tags as $tag => $desc)
                        <div onclick="insertTag('{{ $tag }}')" style="padding:8px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;transition:all .15s;" onmouseover="this.style.background='#ecfdf5';this.style.borderColor='#a7f3d0'" onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e2e8f0'">
                            <code style="color:#047857;font-weight:700;font-size:13px;">{{ $tag }}</code>
                            <span style="font-size:11.5px;color:#64748b;">{{ $desc }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card" style="background:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;padding:18px;">
                <h4 style="font-size:13.5px;font-weight:700;color:#166534;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-circle-info"></i> ব্যবহারের নিয়ম
                </h4>
                <ul style="margin:0;padding-left:18px;font-size:12px;color:#14532d;line-height:1.7;">
                    <li><strong>অনলাইন পেমেন্ট:</strong> শিক্ষার্থী সরাসরি পেমেন্ট করলে তার কোর্স, ব্যাচ ও জেন্ডার অনুযায়ী এই টেমপ্লেট স্বয়ংক্রিয়ভাবে ইমেইল হবে।</li>
                    <li><strong>এডমিন এপ্রুভাল:</strong> পেন্ডিং আবেদন ম্যানুয়ালি এপ্রুভ করার সময় ড্রপডাউন থেকে এই টেমপ্লেট সিলেক্ট করে পাঠানো যাবে।</li>
                    <li><strong>নামকরণ:</strong> সহজ চেনার জন্য সংক্ষিপ্ত নাম ব্যবহার করুন (উদা: <code>Alim2717Male</code>, <code>SM2817Female</code>)।</li>
                </ul>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function insertTag(tag) {
        const textarea = document.getElementById('templateContent');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.focus();
        textarea.selectionEnd = start + tag.length;
    }

    function filterBatches() {
        const courseId = document.getElementById('course_id').value;
        const batchSelect = document.getElementById('batch_id');
        const options = batchSelect.querySelectorAll('option');

        options.forEach(opt => {
            if (!opt.value) return; // "সকল ব্যাচ" option
            const bCourse = opt.getAttribute('data-course');
            if (!courseId || bCourse === courseId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });

        // If currently selected batch doesn't match, reset
        const currentSelected = batchSelect.options[batchSelect.selectedIndex];
        if (currentSelected && currentSelected.value && currentSelected.getAttribute('data-course') !== courseId && courseId) {
            batchSelect.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', filterBatches);
</script>
@endpush
@endsection
