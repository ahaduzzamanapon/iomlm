<x-admin-layout>
    <x-slot name="title">{{ $subject->name }} — Modules</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px">
                <a href="{{ route('admin.subjects.index') }}"><i class="fa-solid fa-arrow-left"></i> Back to Subjects</a>
            </div>
            <h1 style="font-family:'Kalpurush', sans-serif">{{ $subject->code }}: {{ $subject->name }}</h1>
            <p>
                Credit: {{ $subject->credit }} · Full Marks: {{ $subject->full_marks }} · Pass Marks: {{ $subject->pass_marks }}
                @if($subject->category)
                    · <span class="badge badge-secondary no-dot" style="background:#eef2ff;color:#4338ca">{{ $subject->category->name }}</span>
                @endif
            </p>
        </div>
        <div class="page-header-actions" style="display:flex;gap:8px">
            <form method="POST" action="{{ route('admin.subjects.clone', $subject) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-outline" style="color:#059669;border-color:#a7f3d0" title="এই বিষয় ও সকল মডিউল ক্লোন করুন">
                    <i class="fa-solid fa-copy"></i> বিষয় ক্লোন করুন
                </button>
            </form>
            <button class="btn btn-primary" onclick="openModal('addModuleModal')">
                <i class="fa-solid fa-plus"></i> মডিউল যোগ করুন
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    <!-- Modules List (Folder / Group Organization) -->
    @php
        $groupedModules = $subject->modules->groupBy(fn($m) => $m->folder_name ?: 'General');
    @endphp

    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span class="card-title">Subject Modules (Sequential Learning Engine)</span>
            <span class="badge badge-secondary no-dot">{{ $subject->modules->count() }} Modules ({{ $groupedModules->count() }}টি ফোল্ডার)</span>
        </div>
        <div style="padding:16px">
            @forelse($groupedModules as $folder => $mods)
                <div style="margin-bottom:24px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
                    <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 16px;display:flex;align-items:center;justify-content:space-between">
                        <div style="font-weight:700;font-size:13px;color:#334155;display:flex;align-items:center;gap:8px">
                            <i class="fa-solid fa-folder-open" style="color:#6366f1"></i>
                            <span>{{ $folder }}</span>
                            <span class="badge badge-secondary no-dot" style="font-size:11px">{{ $mods->count() }}টি মডিউল</span>
                        </div>
                    </div>
                    <div class="module-list" style="padding:8px 12px">
                        @foreach($mods as $mod)
                        <div class="module-item" style="display:flex;align-items:center;justify-content:space-between;padding:12px;border-bottom:1px solid #f1f5f9;gap:12px">
                            <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0">
                                <div class="module-seq" style="{{ $mod->is_hidden ? 'background:#94a3b8' : '' }}">{{ $mod->sequence_no }}</div>
                                <div style="flex:1;min-width:0">
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                        @if($mod->category)
                                            <span class="badge badge-secondary no-dot" style="font-size:11px;background:rgba(59,130,246,.1);color:var(--blue)">
                                                <i class="fa-solid fa-tag" style="margin-right:4px"></i> {{ $mod->category }}
                                            </span>
                                        @endif

                                        <span class="module-title" style="{{ $mod->is_hidden ? 'color:#64748b;text-decoration:line-through' : '' }}">
                                            {{ $mod->title }}
                                        </span>

                                        @if($mod->is_hidden)
                                            <span class="badge" style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700">
                                                <i class="fa-solid fa-eye-slash"></i> লুকানো (Hidden)
                                            </span>
                                        @endif

                                        {{-- Attachments & Links badges --}}
                                        @if($mod->file_path)
                                            <a href="{{ asset('storage/' . $mod->file_path) }}" target="_blank" class="badge badge-secondary no-dot" style="background:#f1f5f9;color:#334155;text-decoration:none;font-size:11px" title="ডাউনলোড / দেখুন">
                                                <i class="fa-solid fa-file-pdf" style="color:#ef4444"></i> সংযুক্তি ফাইল
                                            </a>
                                        @endif

                                        @if($mod->drive_link)
                                            <a href="{{ $mod->drive_link }}" target="_blank" class="badge badge-secondary no-dot" style="background:#ecfdf5;color:#047857;text-decoration:none;font-size:11px" title="Google Drive Link">
                                                <i class="fa-brands fa-google-drive"></i> ড্রাইভ লিংক
                                            </a>
                                        @endif

                                        @if($mod->has_recorded_videos)
                                            <span class="badge badge-secondary no-dot" style="background:#fdf2f8;color:#be185d;font-size:11px;font-weight:700" title="{{ $mod->video_count }}টি রেকর্ডেড ক্লাস রয়েছে">
                                                <i class="fa-solid fa-video"></i> {{ $mod->video_count }}টি ক্লাস রেকর্ড
                                            </span>
                                        @endif
                                    </div>
                                    @if($mod->description)
                                        <div class="module-sub" style="margin-top:4px;color:var(--text-muted);font-size:12px">
                                            {{ $mod->description }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;margin-left:12px">
                                {{-- Toggle Hidden Form --}}
                                <form method="POST" action="{{ route('admin.modules.toggle-hidden', $mod) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm" style="{{ $mod->is_hidden ? 'color:#b45309;border-color:#fde68a;background:#fffbeb' : 'color:#059669;border-color:#a7f3d0' }}" title="{{ $mod->is_hidden ? 'ক্লিক করে শিক্ষার্থীদের জন্য দৃশ্যমান করুন' : 'ক্লিক করে শিক্ষার্থীদের কাছ থেকে লুকান' }}">
                                        <i class="fa-solid {{ $mod->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                        {{ $mod->is_hidden ? 'দৃশ্যমান করুন' : 'লুকান' }}
                                    </button>
                                </form>

                                {{-- Clone Module Form --}}
                                <form method="POST" action="{{ route('admin.modules.clone', $mod) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm" style="color:#059669;border-color:#a7f3d0" title="মডিউল কপি করুন (Clone)">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </form>

                                {{-- Edit Button --}}
                                <button type="button" class="btn btn-outline btn-sm"
                                    onclick='openEditModal(@json($mod))' title="সম্পাদনা">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                {{-- Delete Form --}}
                                <form method="POST" action="{{ route('admin.modules.destroy', $mod) }}" style="display:inline" onsubmit="return confirm('এই মডিউলটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-red" title="মুছে ফেলুন"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>No modules created yet. Click "মডিউল যোগ করুন" to create the first module!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ── Add Module Modal ── -->
    <div class="modal-overlay" id="addModuleModal">
        <div class="modal" style="max-width:760px;width:95%">
            <div class="modal-header">
                <span class="modal-title">মডিউল যোগ করুন — {{ $subject->code }}</span>
                <button class="modal-close" onclick="closeModal('addModuleModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subjects.modules.store', $subject) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>ফোল্ডার / গ্রুপ (Folder Name)</label>
                            <input type="text" name="folder_name" class="form-control" placeholder="যেমন: অধ্যায় ১, লেকচার নোটস, রেফারেন্স">
                        </div>
                        <div class="form-group">
                            <label>টপিক / ক্যাটাগরি</label>
                            <input type="text" name="category" class="form-control" placeholder="যেমন: ফিক্বহ, আক্বিদা">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>ক্রমিক নম্বর (Sequence No.) <span class="required">*</span></label>
                            <input type="number" name="sequence_no" class="form-control" value="{{ $subject->modules->count() + 1 }}" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>মডিউলের শিরোনাম (Title) <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="যেমন: মডিউল ১: মৌলিক পরিভাষা" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>সংক্ষিপ্ত বিবরণ (Description)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="মডিউলের পাঠ্যসূচির রূপরেখা..."></textarea>
                    </div>
                    
                    {{-- Uploads & Media --}}
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:12px">
                        <div style="font-weight:700;font-size:12px;color:#334155;margin-bottom:8px">
                            <i class="fa-solid fa-paperclip"></i> স্টাডি মেটেরিয়াল ও সংযুক্তি
                        </div>
                        <div class="form-group">
                            <label>ফাইল আপলোড (PDF, Doc, Image - সর্বোচ্চ ৫০MB)</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Google Drive লিংক</label>
                            <input type="url" name="drive_link" class="form-control" placeholder="https://drive.google.com/...">
                        </div>
                    </div>

                    {{-- Multiple Recorded Class Videos --}}
                    <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;padding:14px;margin-bottom:14px">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px">
                            <div style="font-weight:700;font-size:13px;color:#9d174d;display:flex;align-items:center;gap:6px">
                                <i class="fa-solid fa-video"></i> রেকর্ডেড ক্লাস ভিডিওসমূহ (Multiple Recorded Class Videos)
                            </div>
                            <button type="button" class="btn btn-sm" onclick="addVideoRow('add')" 
                                    style="background:#be185d;color:#fff;border:none;border-radius:6px;font-size:12px;font-family:'Kalpurush',sans-serif;font-weight:700;padding:5px 12px;display:inline-flex;align-items:center;gap:5px;cursor:pointer">
                                <i class="fa-solid fa-plus"></i> + নতুন ক্লাস রেকর্ড যোগ করুন
                            </button>
                        </div>
                        <p style="font-size:11.5px;color:#9d174d;margin:0 0 10px;opacity:0.85">
                            প্রতিটি ক্লাসের নির্দিষ্ট নাম/শিরোনাম দিয়ে একাধিক ভিডিও লিংক (YouTube, Vimeo, Drive), সরাসরি ভিডিও ফাইল বা আইফ্রেম কোড যুক্ত করুন।
                        </p>
                        <div id="add_videos_container" style="display:flex;flex-direction:column;gap:12px"></div>
                    </div>

                    <label class="form-check" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_hidden" value="1">
                        <span style="font-weight:600;color:#b45309">শিক্ষার্থীদের কাছ থেকে এই মডিউলটি লুকিয়ে রাখুন (Hide Module)</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addModuleModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">মডিউল সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Edit Module Modal ── -->
    <div class="modal-overlay" id="editModuleModal">
        <div class="modal" style="max-width:760px;width:95%">
            <div class="modal-header">
                <span class="modal-title">মডিউল সম্পাদনা করুন</span>
                <button class="modal-close" onclick="closeModal('editModuleModal')">&times;</button>
            </div>
            <form method="POST" id="editModuleForm" action="" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>ফোল্ডার / গ্রুপ (Folder Name)</label>
                            <input type="text" name="folder_name" id="em_folder_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>টপিক / ক্যাটাগরি</label>
                            <input type="text" name="category" id="em_category" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>ক্রমিক নম্বর (Sequence No.) <span class="required">*</span></label>
                            <input type="number" name="sequence_no" id="em_sequence_no" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>মডিউলের শিরোনাম (Title) <span class="required">*</span></label>
                            <input type="text" name="title" id="em_title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>সংক্ষিপ্ত বিবরণ (Description)</label>
                        <textarea name="description" id="em_description" class="form-control" rows="2"></textarea>
                    </div>

                    {{-- Uploads & Media --}}
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:12px">
                        <div style="font-weight:700;font-size:12px;color:#334155;margin-bottom:8px">
                            <i class="fa-solid fa-paperclip"></i> স্টাডি মেটেরিয়াল ও সংযুক্তি
                        </div>
                        <div class="form-group">
                            <label>নতুন ফাইল আপলোড (বিদ্যমান ফাইল প্রতিস্থাপন করতে)</label>
                            <input type="file" name="attachment" class="form-control">
                            <div id="em_current_file" style="font-size:11px;color:#64748b;margin-top:4px"></div>
                        </div>
                        <div class="form-group">
                            <label>Google Drive লিংক</label>
                            <input type="url" name="drive_link" id="em_drive_link" class="form-control">
                        </div>
                    </div>

                    {{-- Multiple Recorded Class Videos --}}
                    <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;padding:14px;margin-bottom:14px">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px">
                            <div style="font-weight:700;font-size:13px;color:#9d174d;display:flex;align-items:center;gap:6px">
                                <i class="fa-solid fa-video"></i> রেকর্ডেড ক্লাস ভিডিওসমূহ (Multiple Recorded Class Videos)
                            </div>
                            <button type="button" class="btn btn-sm" onclick="addVideoRow('edit')" 
                                    style="background:#be185d;color:#fff;border:none;border-radius:6px;font-size:12px;font-family:'Kalpurush',sans-serif;font-weight:700;padding:5px 12px;display:inline-flex;align-items:center;gap:5px;cursor:pointer">
                                <i class="fa-solid fa-plus"></i> + নতুন ক্লাস রেকর্ড যোগ করুন
                            </button>
                        </div>
                        <p style="font-size:11.5px;color:#9d174d;margin:0 0 10px;opacity:0.85">
                            পূর্বে আপলোডকৃত ক্লাস রেকর্ডগুলো সম্পাদনা করুন অথবা নতুন ক্লাস রেকর্ড যোগ করুন।
                        </p>
                        <div id="edit_videos_container" style="display:flex;flex-direction:column;gap:12px"></div>
                    </div>

                    <label class="form-check" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_hidden" id="em_is_hidden" value="1">
                        <span style="font-weight:600;color:#b45309">শিক্ষার্থীদের কাছ থেকে এই মডিউলটি লুকিয়ে রাখুন (Hide Module)</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editModuleModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">আপডেট সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let videoIndexTracker = 0;

    function createVideoRowHtml(prefix, index, data = {}) {
        const title = data.title || '';
        const url = data.url || '';
        const embedCode = data.embed_code || '';
        const existingFile = data.file_path || '';
        const vidId = data.id || ('vid_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5));

        let fileInfoHtml = '';
        if (existingFile) {
            fileInfoHtml = `<div style="font-size:11.5px;color:#047857;margin-top:4px;font-weight:600">
                <i class="fa-solid fa-circle-check"></i> ভিডিও ফাইল সংযুক্ত আছে (${existingFile.split('/').pop()})
            </div>`;
        }

        return `
        <div class="video-item-card" id="${prefix}_video_row_${index}" style="background:#ffffff;border:1px solid #f472b6;border-radius:8px;padding:12px;position:relative;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
            <input type="hidden" name="videos[${index}][id]" value="${vidId}">
            <input type="hidden" name="videos[${index}][existing_file]" value="${existingFile}">
            
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;border-bottom:1px dashed #fbcfe8;padding-bottom:6px">
                <span style="font-weight:700;font-size:12.5px;color:#be185d">
                    <i class="fa-solid fa-circle-play"></i> ক্লাস রেকর্ড আইটেম
                </span>
                <button type="button" class="btn btn-ghost btn-sm" onclick="removeVideoRow('${prefix}_video_row_${index}')" style="color:#e11d48;padding:2px 8px;font-size:12px;font-weight:700;cursor:pointer" title="এই ভিডিওটি মুছে ফেলুন">
                    <i class="fa-solid fa-trash-can"></i> মুছুন
                </button>
            </div>

            <div class="form-group" style="margin-bottom:8px">
                <label style="font-weight:700;font-size:12px;color:#334155">ক্লাসের নির্দিষ্ট নাম / শিরোনাম (Video Title) <span class="required" style="color:#e11d48">*</span></label>
                <input type="text" name="videos[${index}][title]" class="form-control" value="${title.replace(/"/g, '&quot;')}" placeholder="যেমনঃ ক্লাস ০১: পরিচিতি ও পাঠ্যসূচি" required style="font-family:'Kalpurush',sans-serif;font-weight:600">
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:12px">
                <div>
                    <div class="form-group" style="margin-bottom:8px">
                        <label style="font-size:11.5px;font-weight:600;color:#475569">ভিডিও লিংক (YouTube / Vimeo / Drive URL)</label>
                        <input type="text" name="videos[${index}][url]" class="form-control" value="${url.replace(/"/g, '&quot;')}" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label style="font-size:11.5px;font-weight:600;color:#475569">অথবা সরাসরি ভিডিও ফাইল আপলোড (MP4, WebM)</label>
                        <input type="file" name="videos[${index}][file]" class="form-control" accept="video/mp4,video/webm,video/*" style="font-size:11.5px">
                        ${fileInfoHtml}
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label style="font-size:11.5px;font-weight:600;color:#475569">অথবা আইফ্রেম এম্বেড কোড (Embed iframe Code)</label>
                    <textarea name="videos[${index}][embed_code]" class="form-control" rows="3" placeholder="<iframe src='...' ...></iframe>" style="font-size:12px">${embedCode}</textarea>
                </div>
            </div>
        </div>`;
    }

    function addVideoRow(prefix, data = {}) {
        videoIndexTracker++;
        const container = document.getElementById(prefix + '_videos_container');
        if (!container) return;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = createVideoRowHtml(prefix, videoIndexTracker, data);
        container.appendChild(tempDiv.firstElementChild);
    }

    function removeVideoRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) row.remove();
    }

    function openEditModal(mod) {
        document.getElementById('editModuleForm').action = '/admin/modules/' + mod.id;
        document.getElementById('em_title').value        = mod.title;
        document.getElementById('em_category').value     = mod.category || '';
        document.getElementById('em_folder_name').value  = mod.folder_name || 'General';
        document.getElementById('em_sequence_no').value  = mod.sequence_no;
        document.getElementById('em_description').value  = mod.description || '';
        document.getElementById('em_drive_link').value   = mod.drive_link || '';
        document.getElementById('em_is_hidden').checked  = !!mod.is_hidden;

        const currentFileEl = document.getElementById('em_current_file');
        if (mod.file_path) {
            currentFileEl.innerHTML = '<span style="color:#059669">✓ বর্তমান ফাইল সংযুক্ত আছে</span>';
        } else {
            currentFileEl.innerHTML = '';
        }

        // Populate multiple recorded videos
        const editContainer = document.getElementById('edit_videos_container');
        editContainer.innerHTML = '';
        let videos = mod.videos || [];
        if ((!videos || videos.length === 0) && (mod.recorded_url || mod.embed_code)) {
            videos = [{
                id: 'legacy_' + mod.id,
                title: mod.title + ' (রেকর্ডেড ক্লাস)',
                url: mod.recorded_url || '',
                embed_code: mod.embed_code || '',
                file_path: ''
            }];
        }

        if (videos && videos.length > 0) {
            videos.forEach(v => addVideoRow('edit', v));
        } else {
            addVideoRow('edit');
        }

        openModal('editModuleModal');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize 1 default row in Add Module Modal
        addVideoRow('add');
    });
    </script>
    @endpush

</x-admin-layout>
