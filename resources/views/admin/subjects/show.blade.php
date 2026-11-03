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
            <button class="btn btn-primary" onclick="openAddModuleForFolder('')" style="font-family:'Kalpurush', sans-serif">
                <i class="fa-solid fa-plus"></i> মডিউল যোগ করুন
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    <style>
        @media (max-width: 768px) {
            .module-item {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
            }
            .module-actions {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: flex-start !important;
                gap: 8px !important;
                margin-left: 0 !important;
                padding-top: 10px;
                border-top: 1px dashed #e2e8f0;
            }
            .module-actions .btn {
                flex: 1 1 auto !important;
                justify-content: center !important;
                text-align: center !important;
            }
        }
    </style>

    <!-- Modules List (Folder / Group Organization) -->
    @php
        $groupedModules = $subject->modules->groupBy(fn($m) => $m->folder_name ?: 'General');
    @endphp

    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <div style="display:flex;align-items:center;gap:10px">
                <span class="card-title" style="font-family:'Kalpurush', sans-serif">Subject Modules (Sequential Learning Engine)</span>
                <span class="badge badge-secondary no-dot" style="font-family:'Kalpurush', sans-serif">{{ $subject->modules->count() }} Modules ({{ $groupedModules->count() }}টি ফোল্ডার)</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllFolders(true)" style="font-size:11px;padding:4px 10px;font-family:'Kalpurush', sans-serif" title="সব ফোল্ডার বিস্তার করুন">
                    <i class="fa-solid fa-angles-down"></i> সবগুলো খুলুন
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllFolders(false)" style="font-size:11px;padding:4px 10px;font-family:'Kalpurush', sans-serif" title="সব ফোল্ডার কোলাপ্স করুন">
                    <i class="fa-solid fa-angles-up"></i> সবগুলো বন্ধ করুন
                </button>
            </div>
        </div>
        <div style="padding:16px">
            @forelse($groupedModules as $folder => $mods)
                @php $folderIdx = $loop->index; @endphp
                <div class="folder-card" id="folder-card-{{ $folderIdx }}" style="margin-bottom:20px;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.03);background:#fff">
                    {{-- Folder Header --}}
                    <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                        {{-- Left: Clickable Title + Chevron --}}
                        <div onclick="toggleFolderCollapse({{ $folderIdx }})" style="display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none;flex:1;min-width:200px">
                            <i class="fa-solid fa-chevron-down folder-chevron" id="folder-chevron-{{ $folderIdx }}" style="font-size:12px;color:#64748b;transition:transform 0.2s ease"></i>
                            <i class="fa-solid fa-folder-open" style="color:#059669;font-size:16px"></i>
                            <span style="font-weight:700;font-size:14px;color:#1e293b;font-family:'Kalpurush', sans-serif">{{ $folder }}</span>
                            <span class="badge badge-secondary no-dot" style="font-size:11px;font-family:'Kalpurush', sans-serif">{{ $mods->count() }}টি মডিউল</span>
                        </div>

                        {{-- Right: Actions for this Folder --}}
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                            {{-- Quick Add File/Module to this folder --}}
                            <button type="button" class="btn btn-outline btn-sm" onclick="openAddModuleForFolder('{{ addslashes($folder) }}')" style="color:#1d4ed8;border-color:#bfdbfe;background:#eff6ff;font-family:'Kalpurush', sans-serif" title="এই ফোল্ডারে সরাসরি ফাইল/মডিউল যোগ করুন">
                                <i class="fa-solid fa-plus"></i> <span style="font-family:'Kalpurush', sans-serif">ফাইল যোগ</span>
                            </button>

                            {{-- Rename Folder --}}
                            <button type="button" class="btn btn-outline btn-sm" onclick="openRenameFolderModal('{{ addslashes($folder) }}')" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff;font-family:'Kalpurush', sans-serif" title="ফোল্ডারের নাম পরিবর্তন করুন">
                                <i class="fa-solid fa-pen-to-square"></i> <span style="font-family:'Kalpurush', sans-serif">নাম এডিট</span>
                            </button>

                            {{-- Clone Folder --}}
                            <button type="button" class="btn btn-outline btn-sm" onclick="openCloneFolderModal('{{ addslashes($folder) }}', {{ $mods->count() }})" style="color:#059669;border-color:#a7f3d0;background:#ecfdf5;font-family:'Kalpurush', sans-serif" title="সম্পূর্ণ ফোল্ডার ও এর মডিউল কপি করুন">
                                <i class="fa-solid fa-copy"></i> <span style="font-family:'Kalpurush', sans-serif">কপি</span>
                            </button>

                            {{-- Toggle Button --}}
                            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleFolderCollapse({{ $folderIdx }})" title="ফোল্ডার কোলাপ্স/বিস্তার করুন" style="color:#64748b;padding:4px 8px">
                                <i class="fa-solid fa-chevron-up folder-btn-icon" id="folder-btn-icon-{{ $folderIdx }}"></i>
                            </button>
                        </div>
                    </div>
                    <div class="module-list folder-body" id="folder-body-{{ $folderIdx }}" style="padding:8px 12px">
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

                            <div class="module-actions" style="display:flex;align-items:center;gap:6px;flex-shrink:0;margin-left:12px">
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
                            <label style="font-family:'Kalpurush', sans-serif">ফোল্ডার / গ্রুপ (Folder Name)</label>
                            <input type="text" name="folder_name" id="am_folder_name" class="form-control" placeholder="যেমন: অধ্যায় ১, লেকচার নোটস, রেফারেন্স">
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

                    <label class="form-check" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_hidden" id="em_is_hidden" value="1">
                        <span style="font-weight:600;color:#b45309">শিক্ষার্থীদের কাছ থেকে এই মডিউলটি লুকিয়ে রাখুন (Hide Module)</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editModuleModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="font-family:'Kalpurush', sans-serif">আপডেট সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Rename Folder Modal ── -->
    <div class="modal-overlay" id="renameFolderModal">
        <div class="modal" style="max-width:480px;width:95%">
            <div class="modal-header">
                <span class="modal-title" style="font-family:'Kalpurush', sans-serif">ফোল্ডারের নাম পরিবর্তন করুন</span>
                <button class="modal-close" onclick="closeModal('renameFolderModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subjects.folders.rename', $subject) }}">
                @csrf
                <input type="hidden" name="old_folder_name" id="rfm_old_folder_name">
                <div class="modal-body" style="font-family:'Kalpurush', sans-serif">
                    <div class="form-group">
                        <label>বর্তমান নাম</label>
                        <input type="text" id="rfm_display_old_name" class="form-control" readonly style="background:#f1f5f9;color:#64748b">
                    </div>
                    <div class="form-group" style="margin-top:12px">
                        <label>নতুন ফোল্ডারের নাম <span class="required">*</span></label>
                        <input type="text" name="new_folder_name" id="rfm_new_folder_name" class="form-control" required placeholder="যেমন: কোর্স সিলেবাস, ক্লাস নোটস">
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:8px">
                        <i class="fa-solid fa-circle-info"></i> এই ফোল্ডারের সকল মডিউল নতুন ফোল্ডারের নামের অধীনে স্থানান্তরিত হবে।
                    </div>
                </div>
                <div class="modal-footer" style="font-family:'Kalpurush', sans-serif">
                    <button type="button" class="btn btn-outline" onclick="closeModal('renameFolderModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">নাম পরিবর্তন করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Clone Folder Modal ── -->
    <div class="modal-overlay" id="cloneFolderModal">
        <div class="modal" style="max-width:500px;width:95%">
            <div class="modal-header">
                <span class="modal-title" style="font-family:'Kalpurush', sans-serif">ফোল্ডার কপি করুন (Duplicate Folder)</span>
                <button class="modal-close" onclick="closeModal('cloneFolderModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subjects.folders.clone', $subject) }}">
                @csrf
                <input type="hidden" name="source_folder_name" id="cfm_source_folder_name">
                <div class="modal-body" style="font-family:'Kalpurush', sans-serif">
                    <div class="form-group">
                        <label>মূল ফোল্ডার</label>
                        <input type="text" id="cfm_display_source_name" class="form-control" readonly style="background:#f1f5f9;color:#64748b">
                    </div>
                    <div class="form-group" style="margin-top:12px">
                        <label>নতুন ফোল্ডারের নাম <span class="required">*</span></label>
                        <input type="text" name="new_folder_name" id="cfm_new_folder_name" class="form-control" required placeholder="যেমন: কোর্স সিলেবাস (কপি)">
                    </div>
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:8px;font-size:12px;margin-top:12px">
                        <i class="fa-solid fa-copy"></i> এই ফোল্ডারের অন্তর্ভুক্ত <span id="cfm_module_count" style="font-weight:700"></span>টি মডিউল ও স্টাডি মেটেরিয়াল নতুন ফোল্ডারে কপি হবে।
                    </div>
                </div>
                <div class="modal-footer" style="font-family:'Kalpurush', sans-serif">
                    <button type="button" class="btn btn-outline" onclick="closeModal('cloneFolderModal')">বাতিল</button>
                    <button type="submit" class="btn btn-success" style="background:#059669;border-color:#059669;color:#fff">
                        <i class="fa-solid fa-copy"></i> কপি ও তৈরি করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openAddModuleForFolder(folderName) {
        const folderInput = document.getElementById('am_folder_name');
        if (folderInput) {
            folderInput.value = folderName || '';
        }
        openModal('addModuleModal');
    }

    function openRenameFolderModal(folderName) {
        document.getElementById('rfm_old_folder_name').value = folderName;
        document.getElementById('rfm_display_old_name').value = folderName;
        document.getElementById('rfm_new_folder_name').value = folderName;
        openModal('renameFolderModal');
        setTimeout(() => {
            const inp = document.getElementById('rfm_new_folder_name');
            if (inp) { inp.focus(); inp.select(); }
        }, 150);
    }

    function openCloneFolderModal(folderName, count) {
        document.getElementById('cfm_source_folder_name').value = folderName;
        document.getElementById('cfm_display_source_name').value = folderName;
        document.getElementById('cfm_new_folder_name').value = folderName + ' (কপি)';
        document.getElementById('cfm_module_count').textContent = count;
        openModal('cloneFolderModal');
        setTimeout(() => {
            const inp = document.getElementById('cfm_new_folder_name');
            if (inp) { inp.focus(); inp.select(); }
        }, 150);
    }

    function toggleFolderCollapse(idx) {
        const body = document.getElementById('folder-body-' + idx);
        const chevron = document.getElementById('folder-chevron-' + idx);
        const btnIcon = document.getElementById('folder-btn-icon-' + idx);
        if (!body) return;

        if (body.style.display === 'none') {
            body.style.display = 'block';
            if (chevron) { chevron.style.transform = 'rotate(0deg)'; }
            if (btnIcon) { btnIcon.className = 'fa-solid fa-chevron-up folder-btn-icon'; }
        } else {
            body.style.display = 'none';
            if (chevron) { chevron.style.transform = 'rotate(-90deg)'; }
            if (btnIcon) { btnIcon.className = 'fa-solid fa-chevron-down folder-btn-icon'; }
        }
    }

    function toggleAllFolders(expand) {
        const bodies = document.querySelectorAll('.folder-body');
        const chevrons = document.querySelectorAll('.folder-chevron');
        const btnIcons = document.querySelectorAll('.folder-btn-icon');

        bodies.forEach(b => {
            b.style.display = expand ? 'block' : 'none';
        });
        chevrons.forEach(c => {
            c.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
        });
        btnIcons.forEach(i => {
            i.className = expand ? 'fa-solid fa-chevron-up folder-btn-icon' : 'fa-solid fa-chevron-down folder-btn-icon';
        });
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

        openModal('editModuleModal');
    }
    </script>
    @endpush

</x-admin-layout>
