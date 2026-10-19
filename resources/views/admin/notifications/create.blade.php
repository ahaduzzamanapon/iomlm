<x-admin-layout>
    <x-slot name="title">Send Notification Broadcast</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <a href="{{ route('admin.notifications.index') }}" style="color:#64748b;text-decoration:none;font-weight:600;font-size:13px">← Back to History</a>
            <h1 style="margin-top:4px">Compose &amp; Send Broadcast Notification</h1>
            <p style="font-family:'Kalpurush',sans-serif;">নির্দিষ্ট শিক্ষার্থী, ব্যাচ বা সেমিস্টারকে পুশ নোটিফিকেশন, ইমেইল এবং মোবাইল এসএমএস এর মাধ্যমে বার্তা পাঠান (Push Notification, Email &amp; SMS Broadcast)</p>
        </div>
    </div>

    <style>
        .channel-card {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            user-select: none;
        }
        .channel-card:hover {
            border-color: #0284c7 !important;
            background: #f0f9ff !important;
        }
        .channel-card.active {
            border-color: #0284c7 !important;
            background: #f0f9ff !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.12);
        }
        .preview-tab-btn {
            flex: 1;
            padding: 7px 12px;
            font-size: 12.5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Kalpurush', sans-serif;
            transition: all 0.2s ease;
        }
    </style>

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;font-family:'Kalpurush',sans-serif;">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.notifications.send') }}" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns: 1fr 340px;gap:24px">

            {{-- MAIN FORM --}}
            <div style="display:flex;flex-direction:column;gap:20px">

                <!-- 1. CHANNEL & TARGET AUDIENCE -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">1. Broadcast Channel &amp; Target Audience</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label style="font-family:'Kalpurush',sans-serif;font-weight:700;color:#0f172a;font-size:13.5px;">
                                Delivery Channel / Medium (প্রেরণ মাধ্যম) <span class="required">*</span>
                            </label>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;margin-top:8px">
                                
                                <!-- ALL CHANNELS -->
                                <label class="channel-card active" id="card_ch_ALL">
                                    <input type="radio" name="channel" value="ALL" checked onchange="handleChannelChange(this.value)" style="margin-top:3px;accent-color:#0284c7;">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;">
                                            <i class="fa-solid fa-layer-group" style="color:#0284c7;"></i> Push + Email + SMS
                                        </div>
                                        <div style="font-size:11px;color:#0284c7;margin-top:2px;font-family:'Kalpurush',sans-serif;font-weight:600;">সবগুলো মাধ্যম (সর্বোচ্চ রিচ)</div>
                                        <div style="font-size:10px;color:#64748b;margin-top:1px;">অ্যাপ, ইনবক্স ও সরাসরি মোবাইল</div>
                                    </div>
                                </label>

                                <!-- SMS ONLY -->
                                <label class="channel-card" id="card_ch_SMS">
                                    <input type="radio" name="channel" value="SMS" onchange="handleChannelChange(this.value)" style="margin-top:3px;accent-color:#a21caf;">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;">
                                            <i class="fa-solid fa-comment-sms" style="color:#a21caf;"></i> SMS Only
                                        </div>
                                        <div style="font-size:11px;color:#a21caf;margin-top:2px;font-family:'Kalpurush',sans-serif;font-weight:600;">শুধুমাত্র মোবাইল এসএমএস</div>
                                        <div style="font-size:10px;color:#64748b;margin-top:1px;">MiM SMS গেটওয়ে দ্বারা সরাসরি বার্তা</div>
                                    </div>
                                </label>

                                <!-- PUSH + EMAIL -->
                                <label class="channel-card" id="card_ch_BOTH">
                                    <input type="radio" name="channel" value="BOTH" onchange="handleChannelChange(this.value)" style="margin-top:3px;accent-color:#2563eb;">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;">
                                            <i class="fa-solid fa-bell" style="color:#2563eb;"></i> Push + Email
                                        </div>
                                        <div style="font-size:11px;color:#2563eb;margin-top:2px;font-family:'Kalpurush',sans-serif;font-weight:600;">পুশ ও ইমেইল</div>
                                        <div style="font-size:10px;color:#64748b;margin-top:1px;">ডিভাইস নোটিফিকেশন ও ইনবক্স</div>
                                    </div>
                                </label>

                                <!-- PUSH + SMS -->
                                <label class="channel-card" id="card_ch_PUSH_SMS">
                                    <input type="radio" name="channel" value="PUSH_SMS" onchange="handleChannelChange(this.value)" style="margin-top:3px;accent-color:#7e22ce;">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;">
                                            <i class="fa-solid fa-mobile-screen" style="color:#7e22ce;"></i> Push + SMS
                                        </div>
                                        <div style="font-size:11px;color:#7e22ce;margin-top:2px;font-family:'Kalpurush',sans-serif;font-weight:600;">পুশ ও মোবাইল এসএমএস</div>
                                        <div style="font-size:10px;color:#64748b;margin-top:1px;">অ্যাপ ও বেসিক ফোন উভয়েই</div>
                                    </div>
                                </label>

                                <!-- EMAIL + SMS -->
                                <label class="channel-card" id="card_ch_EMAIL_SMS">
                                    <input type="radio" name="channel" value="EMAIL_SMS" onchange="handleChannelChange(this.value)" style="margin-top:3px;accent-color:#0e7490;">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;">
                                            <i class="fa-solid fa-envelope-circle-check" style="color:#0e7490;"></i> Email + SMS
                                        </div>
                                        <div style="font-size:11px;color:#0e7490;margin-top:2px;font-family:'Kalpurush',sans-serif;font-weight:600;">ইমেইল ও মোবাইল এসএমএস</div>
                                        <div style="font-size:10px;color:#64748b;margin-top:1px;">অফিসিয়াল ইনবক্স ও এসএমএস অ্যালার্ট</div>
                                    </div>
                                </label>

                                <!-- PUSH ONLY -->
                                <label class="channel-card" id="card_ch_PUSH">
                                    <input type="radio" name="channel" value="PUSH" onchange="handleChannelChange(this.value)" style="margin-top:3px;accent-color:#d97706;">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;">
                                            <i class="fa-solid fa-bell" style="color:#d97706;"></i> Push Only
                                        </div>
                                        <div style="font-size:11px;color:#d97706;margin-top:2px;font-family:'Kalpurush',sans-serif;font-weight:600;">শুধুমাত্র পুশ নোটিফিকেশন</div>
                                        <div style="font-size:10px;color:#64748b;margin-top:1px;">Firebase পুশ নোটিফিকেশন</div>
                                    </div>
                                </label>

                                <!-- EMAIL ONLY -->
                                <label class="channel-card" id="card_ch_EMAIL">
                                    <input type="radio" name="channel" value="EMAIL" onchange="handleChannelChange(this.value)" style="margin-top:3px;accent-color:#16a34a;">
                                    <div>
                                        <div style="font-weight:700;color:#0f172a;font-size:13px;display:flex;align-items:center;gap:6px;">
                                            <i class="fa-solid fa-envelope" style="color:#16a34a;"></i> Email Only
                                        </div>
                                        <div style="font-size:11px;color:#16a34a;margin-top:2px;font-family:'Kalpurush',sans-serif;font-weight:600;">শুধুমাত্র ইমেইল</div>
                                        <div style="font-size:10px;color:#64748b;margin-top:1px;">SMTP ইমেইল ব্রডকাস্ট</div>
                                    </div>
                                </label>

                            </div>
                        </div>

                        <div class="form-group">
                            <label>Target Audience Filter <span class="required">*</span></label>
                            <select name="recipient_type" id="recipient_type" class="form-control" onchange="toggleRecipientFilters(this.value)">
                                <option value="ALL_STUDENTS">All Active Students</option>
                                <option value="ALL_TEACHERS">All Faculty Members / Teachers</option>
                                <option value="SPECIFIC_STUDENT">Specific Student (Select individual student)</option>
                                <option value="BATCH_WISE">Batch Wise (Select specific batch)</option>
                                <option value="SEMESTER_WISE">Semester Wise (Select course &amp; semester)</option>
                            </select>
                        </div>

                        {{-- DYNAMIC FILTER: SPECIFIC STUDENT --}}
                        <div id="filter_specific_student" style="display:none">
                            <div class="form-group">
                                <label>Select Student <span class="required">*</span></label>
                                <select name="specific_student_id" class="form-control">
                                    <option value="">— Choose Student —</option>
                                    @foreach($students as $st)
                                        <option value="{{ $st->user_id }}">{{ $st->name }} (Roll: {{ $st->roll_no ?? 'N/A' }} · {{ $st->user->email ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- DYNAMIC FILTER: BATCH WISE --}}
                        <div id="filter_batch_wise" style="display:none">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                                <div class="form-group">
                                    <label>Select Course (কোর্স নির্বাচন করুন)</label>
                                    <select id="broadcast_course_select" class="form-control" onchange="filterBroadcastBatches(this.value)">
                                        <option value="">— Choose Course (সকল কোর্স) —</option>
                                        @foreach($courses as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Select Batch <span class="required">*</span></label>
                                    <select name="batch_id" id="broadcast_batch_select" class="form-control">
                                        <option value="">— Choose Batch —</option>
                                        @foreach($batches as $b)
                                            <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}">
                                                {{ $b->name }} @if($b->course) ({{ $b->course->name }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- DYNAMIC FILTER: SEMESTER WISE --}}
                        <div id="filter_semester_wise" style="display:none">
                            <div class="form-group">
                                <label>Select Course &amp; Semester <span class="required">*</span></label>
                                <select name="semester_id" class="form-control">
                                    <option value="">— Choose Semester —</option>
                                    @foreach($courses as $c)
                                        <optgroup label="{{ $c->name }}">
                                            @foreach($c->semesters as $sem)
                                                <option value="{{ $sem->id }}">{{ $c->name }} — {{ $sem->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. MESSAGE CONTENT -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">2. Notification Message &amp; Media</span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Message Title / Subject <span class="required">*</span></label>
                            <input type="text" name="title" id="title_input" class="form-control"
                                placeholder="e.g. Important Notice: Mid-Term Examination Schedule Released"
                                required oninput="updateLivePreview()">
                        </div>

                        <div class="form-group">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:10px;">
                                <label style="margin-bottom:0; font-weight:700; color:#0f172a; font-family:'Kalpurush',sans-serif; font-size:14px;">
                                    Message Body / Content <span class="required">*</span>
                                </label>
                                
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    {{-- Quick Template Selector Dropdown --}}
                                    <select id="quick_template_select" class="form-control" 
                                        style="width:auto; min-width:240px; height:36px; padding:4px 10px; font-size:13px; font-family:'Kalpurush',sans-serif; background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:6px; cursor:pointer;"
                                        onchange="applyQuickTemplate(this)">
                                        <option value="">📋 দ্রুত টেমপ্লেট নির্বাচন করুন...</option>
                                        @foreach($emailTemplates as $tpl)
                                            <option value="{{ $tpl->id }}" 
                                                data-subject="{{ $tpl->subject }}" 
                                                data-content="{{ $tpl->content }}">
                                                {{ $tpl->name }} ({{ $tpl->category_label }})
                                            </option>
                                        @endforeach
                                    </select>

                                    {{-- Button to open Email Template Library Modal --}}
                                    <button type="button" class="btn btn-outline-primary" onclick="openEmailTemplateModal()"
                                        style="height:36px; display:inline-flex; align-items:center; gap:6px; padding:6px 14px; font-size:13px; font-weight:600; border-radius:6px; font-family:'Kalpurush',sans-serif; border:1.5px solid #0284c7; color:#0284c7; background:#f0f9ff; cursor:pointer;">
                                        <i class="fa-solid fa-envelope-open-text"></i> ইমেইল টেমপ্লেট লাইব্রেরি
                                    </button>

                                    {{-- Button to save current typed message as a new template --}}
                                    <button type="button" class="btn btn-outline-secondary" onclick="openSaveAsTemplateModal()"
                                        title="বর্তমান মেসেজটি নতুন টেমপ্লেট হিসেবে সেভ করুন"
                                        style="height:36px; display:inline-flex; align-items:center; gap:6px; padding:6px 12px; font-size:13px; font-weight:600; border-radius:6px; font-family:'Kalpurush',sans-serif; border:1.5px solid #94a3b8; color:#475569; background:#ffffff; cursor:pointer;">
                                        <i class="fa-solid fa-bookmark"></i> টেমপ্লেট সেভ
                                    </button>
                                </div>
                            </div>

                            <textarea name="message" id="message_input" class="form-control" rows="8"
                                placeholder="Write your announcement or notification details here... (বা উপরের টেমপ্লেট বাটন/ড্রপডাউন থেকে রেডিমেড টেমপ্লেট বেছে নিন)"
                                required oninput="updateLivePreview()" style="font-family:'Kalpurush',sans-serif; line-height:1.6; font-size:14px;"></textarea>

                            {{-- Helper Variables Bar --}}
                            <div style="display:flex; align-items:center; gap:6px; margin-top:8px; flex-wrap:wrap; font-size:12px; color:#64748b; font-family:'Kalpurush',sans-serif;">
                                <span style="font-weight:600;"><i class="fa-solid fa-code"></i> ভ্যারিয়েবল ট্যাগ (ক্লিক করলে যুক্ত হবে):</span>
                                <button type="button" onclick="insertVariableTag('{name}')" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:4px; padding:2px 8px; font-size:11px; cursor:pointer;">{name} (নাম)</button>
                                <button type="button" onclick="insertVariableTag('{roll}')" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:4px; padding:2px 8px; font-size:11px; cursor:pointer;">{roll} (রোল/আইডি)</button>
                                <button type="button" onclick="insertVariableTag('{course}')" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:4px; padding:2px 8px; font-size:11px; cursor:pointer;">{course} (কোর্স)</button>
                                <button type="button" onclick="insertVariableTag('{batch}')" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:4px; padding:2px 8px; font-size:11px; cursor:pointer;">{batch} (ব্যাচ)</button>
                                <button type="button" onclick="insertVariableTag('{date}')" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; border-radius:4px; padding:2px 8px; font-size:11px; cursor:pointer;">{date} (আজকের তারিখ)</button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Image Banner Upload (optional)</label>
                                <input type="file" name="image_file" class="form-control" accept="image/*">
                                <span class="form-help">Upload image file (JPG/PNG, max 3MB)</span>
                            </div>
                            <div class="form-group">
                                <label>OR Image URL (optional)</label>
                                <input type="url" name="image_url" id="image_url_input" class="form-control"
                                    placeholder="https://..." oninput="updateLivePreview()">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Action Button Link / URL (optional)</label>
                            <input type="url" name="action_url" class="form-control"
                                placeholder="e.g. https://iom.edu.bd/student/exams or route link">
                            <span class="form-help">Clicking the push notification or email CTA button opens this page.</span>
                        </div>
                    </div>
                </div>

                <!-- 3. SMS BROADCAST CONTENT -->
                <div class="card" id="sms_custom_section" style="border:2px solid #a21caf;transition:all 0.3s ease;">
                    <div class="card-header" style="background:#fdf4ff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                        <span class="card-title" style="display:flex;align-items:center;gap:8px;font-family:'Kalpurush',sans-serif;font-size:15px;color:#701a75;">
                            <i class="fa-solid fa-comment-sms" style="color:#a21caf;font-size:17px;"></i> 3. মোবাইল এসএমএস বার্তা কনফিগারেশন (SMS Broadcast Content)
                        </span>
                        <span id="sms_channel_indicator" class="badge" style="background:#fdf4ff;color:#a21caf;border:1px solid #f0abfc;font-size:11px;font-family:'Kalpurush',sans-serif;padding:4px 10px;">
                            <i class="fa-solid fa-circle-check"></i> এসএমএস চ্যানেল সক্রিয়
                        </span>
                    </div>
                    <div class="card-body">
                        <div style="background:#eff6ff;border-left:4px solid #0284c7;padding:10px 14px;border-radius:0 8px 8px 0;margin-bottom:14px;font-family:'Kalpurush',sans-serif;font-size:13px;color:#0369a1;line-height:1.5;">
                            <i class="fa-solid fa-circle-info"></i> <strong>পরামর্শ:</strong> নির্বাচিত মাধ্যমে মোবাইল এসএমএস অন্তর্ভুক্ত রয়েছে। আপনি চাইলে নিচে পৃথক সংক্ষিপ্ত এসএমএস টেক্সট লিখতে পারেন। যদি এটি ফাঁকা রাখেন, তবে উপরের মূল মেসেজ বডির টেক্সট স্বয়ংক্রিয়ভাবে ক্লিন ফরম্যাটে এসএমএস হিসেবে প্রেরিত হবে।
                        </div>

                        <div class="form-group" style="margin-bottom:12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;flex-wrap:wrap;gap:6px;">
                                <label style="margin-bottom:0;font-weight:700;color:#0f172a;font-family:'Kalpurush',sans-serif;font-size:13.5px;">
                                    এসএমএস টেক্সট (Custom SMS Text - ঐচ্ছিক)
                                </label>
                                <div style="display:flex;gap:6px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyFromMainMessage()" style="font-family:'Kalpurush',sans-serif;font-size:12px;padding:3px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;">
                                        <i class="fa-solid fa-copy"></i> মূল মেসেজ থেকে কপি করুন
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSmsMessage()" style="font-family:'Kalpurush',sans-serif;font-size:12px;padding:3px 8px;border-radius:6px;">
                                        মুছুন
                                    </button>
                                </div>
                            </div>

                            <textarea name="sms_message" id="sms_message_input" class="form-control" rows="4"
                                placeholder="যেমন: প্রিয় {name}, আপনার অবগতির জন্য জানানো যাচ্ছে যে... (এখানে এসএমএস এর জন্য নির্দিষ্ট টেক্সট লিখুন)"
                                oninput="updateSmsCharCounter()" style="font-family:'Kalpurush',sans-serif;line-height:1.6;font-size:14px;"></textarea>

                            {{-- Helper Variables Bar for SMS --}}
                            <div style="display:flex;align-items:center;gap:6px;margin-top:8px;flex-wrap:wrap;font-size:12px;color:#64748b;font-family:'Kalpurush',sans-serif;">
                                <span style="font-weight:600;"><i class="fa-solid fa-code"></i> ভ্যারিয়েবল ট্যাগ:</span>
                                <button type="button" onclick="insertSmsVariableTag('{name}')" style="background:#fdf4ff;color:#86198f;border:1px solid #f0abfc;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">{name} (নাম)</button>
                                <button type="button" onclick="insertSmsVariableTag('{roll}')" style="background:#fdf4ff;color:#86198f;border:1px solid #f0abfc;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">{roll} (রোল)</button>
                                <button type="button" onclick="insertSmsVariableTag('{course}')" style="background:#fdf4ff;color:#86198f;border:1px solid #f0abfc;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">{course} (কোর্স)</button>
                                <button type="button" onclick="insertSmsVariableTag('{batch}')" style="background:#fdf4ff;color:#86198f;border:1px solid #f0abfc;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">{batch} (ব্যাচ)</button>
                                <button type="button" onclick="insertSmsVariableTag('{date}')" style="background:#fdf4ff;color:#86198f;border:1px solid #f0abfc;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">{date} (তারিখ)</button>
                            </div>

                            {{-- Live Counter Status Bar --}}
                            <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;margin-top:10px;font-family:'Kalpurush',sans-serif;font-size:12.5px;flex-wrap:wrap;gap:8px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span id="sms_lang_badge" class="badge" style="background:#fdf4ff;color:#a21caf;font-weight:700;">বাংলা (Unicode)</span>
                                    <span id="sms_char_count" style="font-weight:600;color:#0f172a;">০ অক্ষর</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span id="sms_parts_count" class="badge" style="background:#dcfce7;color:#166534;font-weight:700;font-size:12px;">১ টি এসএমএস</span>
                                    <span style="color:#64748b;font-size:11.5px;">(বাংলা: ৭০ অক্ষর/পার্ট, ইংরেজি: ১৬০ অক্ষর/পার্ট)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. SCHEDULE & DISPATCH -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title" style="font-family:'Kalpurush',sans-serif;font-size:15px;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-clock" style="color:#0284c7;"></i> ৪. শিডিউল ও প্রেরণ (Schedule &amp; Dispatch)
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-weight:600;color:#1e293b;display:flex;align-items:center;gap:6px;font-family:'Kalpurush',sans-serif;">
                                ভবিষ্যতে প্রেরণের জন্য শিডিউল করুন (Schedule Broadcast - ঐচ্ছিক)
                            </label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" style="margin-top:6px;">
                            <span class="form-help" style="font-size:11px;color:#64748b;font-family:'Kalpurush',sans-serif;">
                                নির্দিষ্ট তারিখ ও সময় নির্বাচন করলে সেই সময়ে নোটিফিকেশনটি স্বয়ংক্রিয়ভাবে প্রেরিত হবে। অবিলম্বে পাঠাতে চাইলে এটি ফাঁকা রাখুন।
                            </span>
                        </div>
                    </div>
                    <div class="card-footer" style="text-align:right;background:#f8fafc;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:12px;color:#64748b;font-family:'Kalpurush',sans-serif;">
                            <i class="fa-solid fa-shield-halved" style="color:#10b981;"></i> নিরাপদ ব্রডকাস্ট ইঞ্জিন সক্রিয়
                        </span>
                        <button type="submit" class="btn btn-primary btn-lg" style="font-family:'Kalpurush',sans-serif;font-weight:700;padding:10px 24px;">
                            <i class="fa-solid fa-paper-plane" style="margin-right:6px;"></i> ব্রডকাস্ট সম্পন্ন করুন (Dispatch Now)
                        </button>
                    </div>
                </div>

            </div>

            {{-- LIVE PREVIEW CARD --}}
            <div>
                <div style="position:sticky;top:20px">
                    <div class="card">
                        <div class="card-header" style="padding:12px 16px;">
                            <span class="card-title" style="font-family:'Kalpurush',sans-serif;font-size:14px;display:flex;align-items:center;gap:6px;">
                                <i class="fa-solid fa-eye" style="color:#0284c7;"></i> লাইভ প্রিভিউ (Live Preview)
                            </span>
                        </div>
                        <div class="card-body" style="background:#f8fafc;padding:14px;">
                            
                            {{-- Tab Switcher --}}
                            <div style="display:flex;gap:4px;background:#e2e8f0;padding:3px;border-radius:8px;margin-bottom:14px;">
                                <button type="button" id="tab_preview_push" class="preview-tab-btn" onclick="switchPreviewTab('push')" style="background:#ffffff;color:#0284c7;font-weight:700;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                                    <i class="fa-solid fa-bell"></i> পুশ প্রিভিউ
                                </button>
                                <button type="button" id="tab_preview_sms" class="preview-tab-btn" onclick="switchPreviewTab('sms')" style="background:transparent;color:#64748b;font-weight:500;">
                                    <i class="fa-solid fa-comment-sms"></i> এসএমএস প্রিভিউ
                                </button>
                            </div>

                            {{-- 1. PUSH PREVIEW --}}
                            <div id="push_preview_view" style="display:block;">
                                <div style="background:#ffffff;border-radius:12px;padding:16px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border:1px solid #e2e8f0">
                                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                                        <img src="{{ asset('images/logo.png') }}" style="width:24px;height:24px;object-fit:contain">
                                        <span style="font-size:12px;font-weight:700;color:#1e293b">IOM Learning Plus</span>
                                        <span style="margin-left:auto;font-size:10px;color:#94a3b8">now</span>
                                    </div>
                                    <div id="preview_title" style="font-weight:700;font-size:14px;color:#0f172a;margin-bottom:6px">
                                        Notification Title...
                                    </div>
                                    <div id="preview_body" style="font-size:12px;color:#475569;line-height:1.4;white-space:pre-line">
                                        Notification body text preview will appear here in real-time as you type.
                                    </div>
                                    <div id="preview_img_box" style="display:none;margin-top:10px">
                                        <img id="preview_img" src="" style="width:100%;border-radius:8px;max-height:140px;object-fit:cover">
                                    </div>
                                </div>
                            </div>

                            {{-- 2. SMS PREVIEW (MOBILE PHONE BUBBLE) --}}
                            <div id="sms_preview_view" style="display:none;font-family:'Kalpurush',sans-serif;">
                                <div style="background:#ffffff;border-radius:12px;padding:14px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border:1px solid #e2e8f0">
                                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;border-bottom:1px solid #f1f5f9;padding-bottom:10px;">
                                        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg, #a21caf, #701a75);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;">
                                            <i class="fa-solid fa-comment-sms"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight:700;font-size:13px;color:#0f172a;">IOM LMS (MiM SMS)</div>
                                            <div style="font-size:11px;color:#64748b;">আজকের তারিখ · সরাসরি ইনবক্স</div>
                                        </div>
                                    </div>
                                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:12px 14px;position:relative;">
                                        <div id="preview_sms_bubble_text" style="font-size:13px;color:#1e293b;line-height:1.6;white-space:pre-wrap;">
                                            প্রিয় আব্দুল্লাহ (রোল: ২০২৬০০১), নোটিফিকেশন এসএমএস প্রিভিউ এখানে প্রদর্শিত হবে।
                                        </div>
                                        <div style="text-align:right;margin-top:6px;font-size:10px;color:#94a3b8;">
                                            এখন • SMS
                                        </div>
                                    </div>
                                    <div style="margin-top:12px;background:#f1f5f9;border-radius:6px;padding:6px 10px;font-size:11.5px;color:#475569;display:flex;justify-content:space-between;align-items:center;">
                                        <span id="preview_sms_calc_chars">০ অক্ষর</span>
                                        <span id="preview_sms_calc_parts" style="font-weight:700;color:#0f172a;">১ টি এসএমএস পার্ট</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    {{-- ══════════════════════════════════════════════════════════════════════
         MODAL 1: EMAIL TEMPLATE LIBRARY
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="modal-overlay" id="emailTemplateModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:99999; backdrop-filter:blur(4px); align-items:flex-start; justify-content:center; overflow-y:auto; padding:30px 15px;" onclick="if(event.target===this) closeEmailTemplateModal()">
        <div class="modal-dialog" style="background:#ffffff; border-radius:14px; max-width:880px; width:100%; margin:0 auto !important; max-height:calc(100vh - 60px) !important; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); font-family:'Kalpurush',sans-serif;">
            <!-- Modal Header -->
            <div style="background:linear-gradient(135deg, #1e293b, #0f172a); color:#ffffff; padding:18px 24px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; font-size:18px; font-weight:700; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-envelope-open-text" style="color:#38bdf8;"></i> ইমেইল টেমপ্লেট লাইব্রেরি
                    </h3>
                    <p style="margin:4px 0 0; font-size:12px; color:#94a3b8;">
                        পছন্দের টেমপ্লেট নির্বাচন করুন অথবা যেকোনো সময় ব্যবহারের জন্য নতুন টেমপ্লেট তৈরি করুন
                    </p>
                </div>
                <button type="button" onclick="closeEmailTemplateModal()" style="background:transparent; border:none; color:#cbd5e1; font-size:24px; cursor:pointer; line-height:1;">&times;</button>
            </div>

            <!-- Filter & Search Toolbar -->
            <div style="padding:14px 24px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;">
                <!-- Category Pills -->
                <div style="display:flex; gap:6px; flex-wrap:wrap;" id="templateCategoryPills">
                    <button type="button" class="btn btn-sm btn-primary tpl-cat-btn" data-cat="ALL" onclick="filterTemplateCategory('ALL', this)" style="border-radius:20px; font-size:12px; padding:4px 12px;">সবগুলো</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary tpl-cat-btn" data-cat="EXAM" onclick="filterTemplateCategory('EXAM', this)" style="border-radius:20px; font-size:12px; padding:4px 12px;">পরীক্ষা</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary tpl-cat-btn" data-cat="FEES" onclick="filterTemplateCategory('FEES', this)" style="border-radius:20px; font-size:12px; padding:4px 12px;">ফি ও একাউন্টস</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary tpl-cat-btn" data-cat="CLASS" onclick="filterTemplateCategory('CLASS', this)" style="border-radius:20px; font-size:12px; padding:4px 12px;">ক্লাস ও রুটিন</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary tpl-cat-btn" data-cat="HOLIDAY" onclick="filterTemplateCategory('HOLIDAY', this)" style="border-radius:20px; font-size:12px; padding:4px 12px;">ছুটি</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary tpl-cat-btn" data-cat="ADMISSION" onclick="filterTemplateCategory('ADMISSION', this)" style="border-radius:20px; font-size:12px; padding:4px 12px;">ভর্তি</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary tpl-cat-btn" data-cat="GENERAL" onclick="filterTemplateCategory('GENERAL', this)" style="border-radius:20px; font-size:12px; padding:4px 12px;">সাধারণ</button>
                </div>

                <div style="display:flex; gap:8px; align-items:center;">
                    <input type="text" id="template_search_input" placeholder="🔍 টেমপ্লেট খুঁজুন..." 
                        oninput="searchTemplates(this.value)"
                        style="height:32px; padding:4px 10px; font-size:12px; border:1px solid #cbd5e1; border-radius:6px; width:180px;">
                    <button type="button" class="btn btn-sm btn-success" onclick="toggleNewTemplateForm()" style="height:32px; display:inline-flex; align-items:center; gap:5px; font-size:12px; border-radius:6px;">
                        <i class="fa-solid fa-plus"></i> নতুন টেমপ্লেট
                    </button>
                </div>
            </div>

            <!-- Collapsible New Template Form inside Modal -->
            <div id="newTemplateFormContainer" style="display:none; padding:16px 24px; background:#f0fdf4; border-bottom:2px solid #bbf7d0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h4 style="margin:0; font-size:14px; font-weight:700; color:#166534;">➕ নতুন কাস্টম ইমেইল টেমপ্লেট তৈরি করুন</h4>
                    <button type="button" onclick="toggleNewTemplateForm()" style="background:none; border:none; color:#166534; cursor:pointer; font-size:18px;">&times;</button>
                </div>
                <form id="ajaxCreateTemplateForm" onsubmit="submitNewTemplate(event)">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:10px;">
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">টেমপ্লেটের নাম <span style="color:#dc2626;">*</span></label>
                            <input type="text" id="tpl_new_name" class="form-control" style="font-size:13px; height:36px;" placeholder="যেমন: বিশেষ কুইজ সংক্রান্ত নোটিশ" required>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">ক্যাটেগরি <span style="color:#dc2626;">*</span></label>
                            <select id="tpl_new_category" class="form-control" style="font-size:13px; height:36px;" required>
                                <option value="GENERAL">সাধারণ বিজ্ঞপ্তি</option>
                                <option value="EXAM">পরীক্ষা সংক্রান্ত</option>
                                <option value="FEES">ফি ও একাউন্টস</option>
                                <option value="CLASS">ক্লাস ও রুটিন</option>
                                <option value="HOLIDAY">ছুটি সংক্রান্ত</option>
                                <option value="ADMISSION">ভর্তি সংক্রান্ত</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="font-size:12px; font-weight:600; color:#374151;">ডিফল্ট সাবজেক্ট / শিরোনাম (ঐচ্ছিক)</label>
                        <input type="text" id="tpl_new_subject" class="form-control" style="font-size:13px; height:36px;" placeholder="যেমন: বিজ্ঞপ্তি: বিশেষ কুইজ পরীক্ষা অনুষ্ঠিত হওয়া প্রসঙ্গে">
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px; font-weight:600; color:#374151;">টেমপ্লেটের মূল বার্তা / কনটেন্ট <span style="color:#dc2626;">*</span></label>
                        <textarea id="tpl_new_content" class="form-control" rows="4" style="font-size:13px;" placeholder="আসসালামু আলাইকুম... এখানে টেমপ্লেটের বডি লিখুন..." required></textarea>
                    </div>
                    <div style="text-align:right; display:flex; justify-content:flex-end; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleNewTemplateForm()">বাতিল</button>
                        <button type="submit" class="btn btn-sm btn-success" id="tpl_submit_btn">
                            <i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>

            <!-- Templates List Area -->
            <div style="padding:20px 24px; overflow-y:auto; flex:1; max-height:480px;" id="templatesListContainer">
                <div style="display:grid; grid-template-columns:1fr; gap:14px;" id="templatesCardsGrid">
                    @foreach($emailTemplates as $tpl)
                        <div class="template-card" data-id="{{ $tpl->id }}" data-category="{{ $tpl->category }}" data-name="{{ strtolower($tpl->name) }}" data-subject="{{ strtolower($tpl->subject) }}" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:16px; transition:all 0.2s ease; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; gap:10px;">
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span style="font-weight:700; font-size:15px; color:#0f172a;">{{ $tpl->name }}</span>
                                        <span style="background:#e2e8f0; color:#334155; font-size:11px; font-weight:600; padding:2px 8px; border-radius:12px;">{{ $tpl->category_label }}</span>
                                        @if($tpl->is_system)
                                            <span style="background:#dbeafe; color:#1e40af; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px;">সিস্টেম টেমপ্লেট</span>
                                        @else
                                            <span style="background:#dcfce7; color:#166534; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px;">কাস্টম</span>
                                        @endif
                                    </div>
                                    @if($tpl->subject)
                                        <div style="font-size:12px; color:#0369a1; font-weight:600; margin-top:4px;">
                                            <i class="fa-solid fa-heading"></i> সাবজেক্ট: {{ $tpl->subject }}
                                        </div>
                                    @endif
                                </div>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    @if(!$tpl->is_system)
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCustomTemplate({{ $tpl->id }})" title="টেমপ্লেট মুছে ফেলুন" style="padding:4px 8px; font-size:11px; border-radius:6px;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-primary" onclick="selectTemplate('{{ addslashes($tpl->subject) }}', {{ json_encode($tpl->content) }})" style="padding:6px 14px; font-size:13px; font-weight:600; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                                        <i class="fa-solid fa-check"></i> নির্বাচন করুন
                                    </button>
                                </div>
                            </div>
                            <div style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:8px; padding:10px 12px; font-size:13px; color:#475569; line-height:1.5; white-space:pre-line; max-height:90px; overflow-y:auto;">
                                {{ $tpl->content }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div id="noTemplatesFound" style="display:none; text-align:center; padding:40px 20px; color:#94a3b8;">
                    <i class="fa-solid fa-folder-open" style="font-size:36px; margin-bottom:10px; color:#cbd5e1;"></i>
                    <p style="font-size:14px; margin:0;">কোনো টেমপ্লেট পাওয়া যায়নি।</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding:12px 24px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#64748b;">
                <span><i class="fa-solid fa-circle-info"></i> টেমপ্লেট নির্বাচন করলেই তা স্বয়ংক্রিয়ভাবে মেসেজ বডিতে যুক্ত হবে</span>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeEmailTemplateModal()" style="border-radius:6px; padding:5px 16px;">বন্ধ করুন</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         MODAL 2: SAVE CURRENT MESSAGE AS TEMPLATE
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="modal-overlay" id="saveAsTemplateModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:99999; backdrop-filter:blur(4px); align-items:flex-start; justify-content:center; overflow-y:auto; padding:30px 15px;" onclick="if(event.target===this) closeSaveAsTemplateModal()">
        <div class="modal-dialog" style="background:#ffffff; border-radius:14px; max-width:550px; width:100%; margin:0 auto !important; max-height:calc(100vh - 60px) !important; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); font-family:'Kalpurush',sans-serif;">
            <div style="background:linear-gradient(135deg, #1e293b, #0f172a); color:#ffffff; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-bookmark" style="color:#38bdf8;"></i> বর্তমান মেসেজ টেমপ্লেট হিসেবে সংরক্ষণ
                </h3>
                <button type="button" onclick="closeSaveAsTemplateModal()" style="background:transparent; border:none; color:#cbd5e1; font-size:22px; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <form id="saveCurrentAsTemplateForm" onsubmit="submitSaveCurrentAsTemplate(event)" style="padding:20px;">
                <div style="margin-bottom:12px;">
                    <label style="font-size:13px; font-weight:600; color:#374151;">টেমপ্লেটের নাম <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="save_tpl_name" class="form-control" placeholder="যেমন: বিশেষ পরীক্ষার নোটিশ" required>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:13px; font-weight:600; color:#374151;">ক্যাটেগরি <span style="color:#dc2626;">*</span></label>
                    <select id="save_tpl_category" class="form-control" required>
                        <option value="GENERAL">সাধারণ বিজ্ঞপ্তি</option>
                        <option value="EXAM">পরীক্ষা সংক্রান্ত</option>
                        <option value="FEES">ফি ও একাউন্টস</option>
                        <option value="CLASS">ক্লাস ও রুটিন</option>
                        <option value="HOLIDAY">ছুটি সংক্রান্ত</option>
                        <option value="ADMISSION">ভর্তি সংক্রান্ত</option>
                    </select>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:13px; font-weight:600; color:#374151;">সাবজেক্ট / বিষয়</label>
                    <input type="text" id="save_tpl_subject" class="form-control" placeholder="বিষয়...">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:600; color:#374151;">মেসেজ কনটেন্ট <span style="color:#dc2626;">*</span></label>
                    <textarea id="save_tpl_content" class="form-control" rows="5" required></textarea>
                </div>
                <div style="text-align:right; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeSaveAsTemplateModal()">বাতিল</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="save_tpl_submit_btn">
                        <i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div id="templateToastNotification" style="display:none; position:fixed; bottom:30px; right:30px; background:#0f172a; color:#ffffff; padding:12px 20px; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.2); z-index:100000; font-family:'Kalpurush',sans-serif; font-size:14px; align-items:center; gap:10px;">
        <i class="fa-solid fa-circle-check" style="color:#10b981; font-size:18px;"></i>
        <span id="templateToastMessage">টেমপ্লেট সফলভাবে যুক্ত করা হয়েছে!</span>
    </div>

    <script>
    function toggleRecipientFilters(val) {
        document.getElementById('filter_specific_student').style.display = val === 'SPECIFIC_STUDENT' ? 'block' : 'none';
        document.getElementById('filter_batch_wise').style.display       = val === 'BATCH_WISE'        ? 'block' : 'none';
        document.getElementById('filter_semester_wise').style.display    = val === 'SEMESTER_WISE'     ? 'block' : 'none';
    }

    function filterBroadcastBatches(courseId) {
        const batchSelect = document.getElementById('broadcast_batch_select');
        if (!batchSelect) return;
        const currentBatch = batchSelect.value;
        let hasSelectedValid = false;

        Array.from(batchSelect.options).forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                opt.text = courseId ? '— Choose Batch —' : '— Choose Batch (বা কোর্স নির্বাচন করুন) —';
                return;
            }
            const bCourseId = opt.getAttribute('data-course-id');
            if (!courseId || bCourseId === courseId) {
                opt.hidden = false;
                if (opt.value === currentBatch) hasSelectedValid = true;
            } else {
                opt.hidden = true;
            }
        });

        if (!hasSelectedValid) {
            batchSelect.value = '';
        }
    }

    function handleChannelChange(val) {
        document.querySelectorAll('.channel-card').forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.classList.add('active');
                card.style.borderColor = '#0284c7';
                card.style.background = '#f0f9ff';
            } else {
                card.classList.remove('active');
                card.style.borderColor = '#e2e8f0';
                card.style.background = '#f8fafc';
            }
        });

        const smsSection = document.getElementById('sms_custom_section');
        const smsIndicator = document.getElementById('sms_channel_indicator');
        const smsTabBtn = document.getElementById('tab_preview_sms');
        const hasSms = ['ALL', 'SMS', 'PUSH_SMS', 'EMAIL_SMS'].includes(val);

        if (hasSms) {
            if (smsSection) {
                smsSection.style.opacity = '1';
                smsSection.style.borderColor = '#a21caf';
                smsSection.style.pointerEvents = 'auto';
            }
            if (smsIndicator) {
                smsIndicator.style.background = '#fdf4ff';
                smsIndicator.style.color = '#a21caf';
                smsIndicator.style.borderColor = '#f0abfc';
                smsIndicator.innerHTML = '<i class="fa-solid fa-circle-check"></i> এসএমএস চ্যানেল সক্রিয়';
            }
            if (smsTabBtn) smsTabBtn.style.display = 'inline-flex';
            if (val === 'SMS') {
                switchPreviewTab('sms');
            }
        } else {
            if (smsSection) {
                smsSection.style.opacity = '0.55';
                smsSection.style.borderColor = '#cbd5e1';
                smsSection.style.pointerEvents = 'none';
            }
            if (smsIndicator) {
                smsIndicator.style.background = '#f1f5f9';
                smsIndicator.style.color = '#64748b';
                smsIndicator.style.borderColor = '#cbd5e1';
                smsIndicator.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> এই চ্যানেলে এসএমএস নিষ্ক্রিয়';
            }
            switchPreviewTab('push');
        }

        updateSmsCharCounter();
    }

    function switchPreviewTab(tab) {
        const pushView = document.getElementById('push_preview_view');
        const smsView  = document.getElementById('sms_preview_view');
        const pushTab  = document.getElementById('tab_preview_push');
        const smsTab   = document.getElementById('tab_preview_sms');

        if (tab === 'sms') {
            if (pushView) pushView.style.display = 'none';
            if (smsView)  smsView.style.display  = 'block';
            if (pushTab) {
                pushTab.style.background = 'transparent';
                pushTab.style.color = '#64748b';
                pushTab.style.fontWeight = '500';
                pushTab.style.boxShadow = 'none';
            }
            if (smsTab) {
                smsTab.style.background = '#ffffff';
                smsTab.style.color = '#0284c7';
                smsTab.style.fontWeight = '700';
                smsTab.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
            }
        } else {
            if (pushView) pushView.style.display = 'block';
            if (smsView)  smsView.style.display  = 'none';
            if (pushTab) {
                pushTab.style.background = '#ffffff';
                pushTab.style.color = '#0284c7';
                pushTab.style.fontWeight = '700';
                pushTab.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
            }
            if (smsTab) {
                smsTab.style.background = 'transparent';
                smsTab.style.color = '#64748b';
                smsTab.style.fontWeight = '500';
                smsTab.style.boxShadow = 'none';
            }
        }
    }

    function updateSmsCharCounter() {
        const customSms = document.getElementById('sms_message_input')?.value || '';
        const mainMessage = document.getElementById('message_input')?.value || '';
        
        // Fallback: if custom sms is empty, use main message without html tags
        const activeText = customSms.trim() ? customSms : mainMessage.replace(/<[^>]*>/g, '').trim();

        // Check if Unicode (contains non-ASCII characters such as Bengali)
        const isUnicode = /[^\x00-\x7F]/.test(activeText);
        const charCount = activeText.length;

        let parts = 1;
        let limitPerPart = isUnicode ? 70 : 160;

        if (isUnicode) {
            if (charCount > 70) {
                parts = Math.ceil(charCount / 67);
                limitPerPart = 67;
            } else {
                parts = charCount === 0 ? 1 : Math.ceil(charCount / 70);
            }
        } else {
            if (charCount > 160) {
                parts = Math.ceil(charCount / 153);
                limitPerPart = 153;
            } else {
                parts = charCount === 0 ? 1 : Math.ceil(charCount / 160);
            }
        }

        const langBadge = document.getElementById('sms_lang_badge');
        const charCountEl = document.getElementById('sms_char_count');
        const partsCountEl = document.getElementById('sms_parts_count');

        if (langBadge) {
            langBadge.textContent = isUnicode ? 'বাংলা (Unicode)' : 'ইংরেজি (GSM-7)';
            langBadge.style.background = isUnicode ? '#fdf4ff' : '#eff6ff';
            langBadge.style.color = isUnicode ? '#a21caf' : '#1d4ed8';
        }

        if (charCountEl) {
            charCountEl.textContent = `${charCount} অক্ষর ${charCount > 0 ? '(প্রতি পার্ট ' + limitPerPart + ')' : ''}`;
        }

        if (partsCountEl) {
            partsCountEl.textContent = `${parts} টি এসএমএস`;
            if (parts === 1) {
                partsCountEl.style.background = '#dcfce7';
                partsCountEl.style.color = '#166534';
            } else if (parts <= 3) {
                partsCountEl.style.background = '#fef3c7';
                partsCountEl.style.color = '#92400e';
            } else {
                partsCountEl.style.background = '#fee2e2';
                partsCountEl.style.color = '#b91c1c';
            }
        }

        // Update Live SMS Preview Bubble
        const previewBubble = document.getElementById('preview_sms_bubble_text');
        if (previewBubble) {
            if (!activeText) {
                previewBubble.textContent = 'প্রিয় আব্দুল্লাহ (রোল: ২০২৬০০১), নোটিফিকেশন এসএমএস প্রিভিউ এখানে প্রদর্শিত হবে।';
            } else {
                const today = new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' });
                let previewText = activeText
                    .replace(/{name}/g, 'আব্দুল্লাহ')
                    .replace(/{roll}/g, '২০২৬০০১')
                    .replace(/{course}/g, 'আলিম ১ম বর্ষ')
                    .replace(/{batch}/g, 'ব্যাচ-০১')
                    .replace(/{date}/g, today);
                previewBubble.textContent = previewText;
            }
        }

        const previewCalcChars = document.getElementById('preview_sms_calc_chars');
        const previewCalcParts = document.getElementById('preview_sms_calc_parts');
        if (previewCalcChars) previewCalcChars.textContent = `${charCount} অক্ষর (${isUnicode ? 'বাংলা' : 'ইংরেজি'})`;
        if (previewCalcParts) previewCalcParts.textContent = `${parts} টি এসএমএস পার্ট`;
    }

    function copyFromMainMessage() {
        const mainMessage = document.getElementById('message_input')?.value || '';
        const cleanText = mainMessage.replace(/<[^>]*>/g, '').trim();
        if (!cleanText) {
            alert('মূল মেসেজ বডিতে কোনো টেক্সট পাওয়া যায়নি।');
            return;
        }
        const smsInput = document.getElementById('sms_message_input');
        smsInput.value = cleanText;
        smsInput.focus();
        updateSmsCharCounter();
        showTemplateToast('মূল মেসেজ থেকে টেক্সট এসএমএস বক্সে কপি করা হয়েছে!');
    }

    function clearSmsMessage() {
        const smsInput = document.getElementById('sms_message_input');
        smsInput.value = '';
        updateSmsCharCounter();
    }

    function insertSmsVariableTag(tag) {
        const smsInput = document.getElementById('sms_message_input');
        const start = smsInput.selectionStart || 0;
        const end   = smsInput.selectionEnd || 0;
        const val   = smsInput.value;

        smsInput.value = val.substring(0, start) + tag + val.substring(end);
        smsInput.focus();
        smsInput.selectionStart = smsInput.selectionEnd = start + tag.length;
        updateSmsCharCounter();
    }

    function updateLivePreview() {
        const title = document.getElementById('title_input').value;
        const body  = document.getElementById('message_input').value;
        const img   = document.getElementById('image_url_input').value;

        document.getElementById('preview_title').textContent = title || 'Notification Title...';
        document.getElementById('preview_body').textContent  = body  || 'Notification body text preview will appear here in real-time as you type.';

        const imgBox = document.getElementById('preview_img_box');
        const previewImg = document.getElementById('preview_img');
        if (img) {
            previewImg.src = img;
            imgBox.style.display = 'block';
        } else {
            imgBox.style.display = 'none';
        }

        // Also refresh SMS counter & SMS preview
        updateSmsCharCounter();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="channel"]:checked');
        if (checkedRadio) {
            handleChannelChange(checkedRadio.value);
        }
    });

    /* ════ Email Template System Handlers ════ */
    function showTemplateToast(msg) {
        const toast = document.getElementById('templateToastNotification');
        const text  = document.getElementById('templateToastMessage');
        text.textContent = msg;
        toast.style.display = 'flex';
        setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    function applyQuickTemplate(selectEl) {
        const option = selectEl.options[selectEl.selectedIndex];
        if (!option || !option.value) return;

        const subject = option.getAttribute('data-subject') || '';
        const content = option.getAttribute('data-content') || '';
        selectTemplate(subject, content);
    }

    function selectTemplate(subject, content) {
        const messageInput = document.getElementById('message_input');
        const titleInput   = document.getElementById('title_input');

        messageInput.value = content;

        if (subject) {
            if (!titleInput.value) {
                titleInput.value = subject;
            } else {
                if (confirm('মেসেজের সাবজেক্টও কি টেমপ্লেটের সাথে আপডেট করতে চান? ("' + subject + '")')) {
                    titleInput.value = subject;
                }
            }
        }

        updateLivePreview();
        closeEmailTemplateModal();
        showTemplateToast('ইমেইল টেমপ্লেট মেসেজ বডিতে সফলভাবে যুক্ত করা হয়েছে!');
    }

    function insertVariableTag(tag) {
        const messageInput = document.getElementById('message_input');
        const start = messageInput.selectionStart || 0;
        const end   = messageInput.selectionEnd || 0;
        const val   = messageInput.value;

        messageInput.value = val.substring(0, start) + tag + val.substring(end);
        messageInput.focus();
        messageInput.selectionStart = messageInput.selectionEnd = start + tag.length;
        updateLivePreview();
    }

    function openEmailTemplateModal() {
        const m = document.getElementById('emailTemplateModal');
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeEmailTemplateModal() {
        const m = document.getElementById('emailTemplateModal');
        m.style.display = 'none';
        document.body.style.overflow = '';
    }

    function openSaveAsTemplateModal() {
        const titleVal   = document.getElementById('title_input').value;
        const messageVal = document.getElementById('message_input').value;

        if (!messageVal.trim()) {
            alert('টেমপ্লেট হিসেবে সংরক্ষণ করার জন্য প্রথমে মেসেজ বডিতে কিছু লিখুন।');
            return;
        }

        document.getElementById('save_tpl_subject').value = titleVal;
        document.getElementById('save_tpl_content').value = messageVal;
        document.getElementById('save_tpl_name').value = titleVal ? titleVal.substring(0, 50) : '';

        const m = document.getElementById('saveAsTemplateModal');
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeSaveAsTemplateModal() {
        const m = document.getElementById('saveAsTemplateModal');
        m.style.display = 'none';
        document.body.style.overflow = '';
    }

    let currentCategoryFilter = 'ALL';

    function filterTemplateCategory(cat, btn) {
        currentCategoryFilter = cat;
        document.querySelectorAll('.tpl-cat-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-outline-secondary');
        });
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary');
        applyFilters();
    }

    function searchTemplates(term) {
        applyFilters();
    }

    function applyFilters() {
        const term = (document.getElementById('template_search_input').value || '').trim().toLowerCase();
        const cards = document.querySelectorAll('#templatesCardsGrid .template-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            const cardName = card.getAttribute('data-name') || '';
            const cardSubject = card.getAttribute('data-subject') || '';

            const matchesCategory = currentCategoryFilter === 'ALL' || cardCat === currentCategoryFilter;
            const matchesSearch = !term || cardName.includes(term) || cardSubject.includes(term);

            if (matchesCategory && matchesSearch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('noTemplatesFound').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function toggleNewTemplateForm() {
        const formBox = document.getElementById('newTemplateFormContainer');
        formBox.style.display = formBox.style.display === 'none' ? 'block' : 'none';
    }

    function submitNewTemplate(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('tpl_submit_btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> সংরক্ষণ হচ্ছে...';

        const payload = {
            name: document.getElementById('tpl_new_name').value,
            category: document.getElementById('tpl_new_category').value,
            subject: document.getElementById('tpl_new_subject').value,
            content: document.getElementById('tpl_new_content').value,
            _token: '{{ csrf_token() }}'
        };

        fetch('{{ route("admin.email-templates.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন';

            if (data.success) {
                // Add to quick select dropdown
                const quickSelect = document.getElementById('quick_template_select');
                const opt = document.createElement('option');
                opt.value = data.template.id;
                opt.setAttribute('data-subject', data.template.subject || '');
                opt.setAttribute('data-content', data.template.content);
                opt.textContent = data.template.name + ' (' + (data.template.category_label || data.template.category) + ')';
                quickSelect.appendChild(opt);

                // Reset form & hide
                document.getElementById('ajaxCreateTemplateForm').reset();
                toggleNewTemplateForm();

                // Select this new template right away!
                selectTemplate(data.template.subject || '', data.template.content);
                showTemplateToast('নতুন টেমপ্লেট সংরক্ষিত ও মেসেজে যুক্ত হয়েছে!');
            } else {
                alert(data.message || 'টেমপ্লেট সংরক্ষণ করতে ব্যর্থ হয়েছে।');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন';
            alert('টেমপ্লেট সংরক্ষণে ত্রুটি দেখা দিয়েছে।');
        });
    }

    function submitSaveCurrentAsTemplate(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('save_tpl_submit_btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> সংরক্ষণ হচ্ছে...';

        const payload = {
            name: document.getElementById('save_tpl_name').value,
            category: document.getElementById('save_tpl_category').value,
            subject: document.getElementById('save_tpl_subject').value,
            content: document.getElementById('save_tpl_content').value,
            _token: '{{ csrf_token() }}'
        };

        fetch('{{ route("admin.email-templates.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন';

            if (data.success) {
                // Add to quick select dropdown
                const quickSelect = document.getElementById('quick_template_select');
                const opt = document.createElement('option');
                opt.value = data.template.id;
                opt.setAttribute('data-subject', data.template.subject || '');
                opt.setAttribute('data-content', data.template.content);
                opt.textContent = data.template.name + ' (' + (data.template.category_label || data.template.category) + ')';
                quickSelect.appendChild(opt);

                closeSaveAsTemplateModal();
                showTemplateToast('বর্তমান মেসেজটি নতুন টেমপ্লেট হিসেবে সংরক্ষিত হয়েছে!');
            } else {
                alert(data.message || 'সংরক্ষণ ব্যর্থ হয়েছে।');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> সংরক্ষণ করুন';
            alert('টেমপ্লেট সংরক্ষণে ত্রুটি দেখা দিয়েছে।');
        });
    }

    function deleteCustomTemplate(id) {
        if (!confirm('আপনি কি নিশ্চিত যে এই কাস্টম টেমপ্লেটটি মুছে ফেলতে চান?')) return;

        fetch('{{ url("admin/email-templates") }}/' + id, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`.template-card[data-id="${id}"]`);
                if (card) card.remove();

                const opt = document.querySelector(`#quick_template_select option[value="${id}"]`);
                if (opt) opt.remove();

                showTemplateToast('টেমপ্লেট সফলভাবে মুছে ফেলা হয়েছে!');
            } else {
                alert(data.message || 'টেমপ্লেট মুছে ফেলতে ব্যর্থ হয়েছে।');
            }
        })
        .catch(err => {
            alert('টেমপ্লেট মুছতে সমস্যা হয়েছে।');
        });
    }
    </script>
</x-admin-layout>
