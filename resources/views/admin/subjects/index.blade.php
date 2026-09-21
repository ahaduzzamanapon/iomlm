<x-admin-layout>
    <x-slot name="title">Subjects & Modules</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush', sans-serif">বিষয় ও মডিউল ব্যবস্থাপনা (Subjects & Modules)</h1>
            <p>সকল কোর্সের বিষয়, ক্রেডিট, পাস মার্ক এবং শিক্ষাক্রমের ধারাবাহিক মডিউল পরিচালনা করুন</p>
        </div>
        <div class="page-header-actions" style="display:flex;gap:8px">
            <a href="{{ route('admin.subject-categories.index') }}" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-tags"></i> বিষয় ক্যাটাগরি
            </a>
            <button class="btn btn-primary" onclick="openModal('addSubjectModal')">
                <i class="fa-solid fa-plus"></i> নতুন বিষয় যোগ করুন
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card" style="margin-bottom:16px;padding:14px">
        <form method="GET" action="{{ route('admin.subjects.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <input type="text" name="search" class="form-control" placeholder="বিষয়ের নাম বা কোড দিয়ে অনুসন্ধান..." value="{{ $search ?? '' }}">
            </div>
            <div style="width:220px">
                <select name="category_id" class="form-control">
                    <option value="">সকল ক্যাটাগরি (All Categories)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ ($categoryId ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:8px 16px">
                <i class="fa-solid fa-filter"></i> ফিল্টার
            </button>
            @if(!empty($search) || !empty($categoryId))
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline" style="padding:8px 14px">
                    <i class="fa-solid fa-rotate-left"></i> রিসেট
                </a>
            @endif
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:100px">Code</th>
                        <th>Subject Name</th>
                        <th>Category</th>
                        <th>Credit</th>
                        <th>Full / Pass Marks</th>
                        <th>Modules</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subj)
                    <tr>
                        <td><span class="badge badge-secondary no-dot"><strong>{{ $subj->code }}</strong></span></td>
                        <td class="td-primary">
                            <a href="{{ route('admin.subjects.show', $subj) }}" style="font-weight:600;color:var(--blue)">{{ $subj->name }}</a>
                        </td>
                        <td>
                            @if($subj->category)
                                <span class="badge badge-secondary no-dot" style="background:#eef2ff;color:#4338ca;font-weight:600">
                                    <i class="fa-solid fa-tag" style="margin-right:3px"></i> {{ $subj->category->name }}
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $subj->credit }} Credit</td>
                        <td class="td-muted">{{ $subj->full_marks }} / {{ $subj->pass_marks }}</td>
                        <td>
                            <a href="{{ route('admin.subjects.show', $subj) }}" class="badge badge-scheduled no-dot" style="text-decoration:none">
                                <i class="fa-solid fa-folder-tree"></i> {{ $subj->modules_count }} Modules
                            </a>
                        </td>
                        <td>
                            @if($subj->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-outline btn-sm" onclick='openEditSubjectModal(@json($subj))' title="Edit Subject">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-outline btn-sm" onclick='openCloneSubjectModal({{ $subj->id }}, "{{ addslashes($subj->name) }}", "{{ $subj->code }}")' title="কপি করুন (Clone Subject)" style="color:#059669;border-color:#a7f3d0">
                                <i class="fa-solid fa-copy"></i> কপি
                            </button>
                            <a href="{{ route('admin.subjects.show', $subj) }}" class="btn btn-outline btn-sm" title="মডিউল পরিচালনা">
                                Manage Modules
                            </a>
                            <form method="POST" action="{{ route('admin.subjects.destroy', $subj) }}" style="display:inline" onsubmit="return confirm('Delete this subject?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">No subjects found. Click "New Subject" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Subject Modal -->
    <div class="modal-overlay" id="addSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Subject</span>
                <button class="modal-close" onclick="closeModal('addSubjectModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subjects.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject Code <span class="required">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. CSE-101" required>
                        </div>
                        <div class="form-group">
                            <label>Category (ক্যাটাগরি)</label>
                            <select name="category_id" class="form-control">
                                <option value="">-- কোনো ক্যাটাগরি নেই --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Programming Fundamentals" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Credit <span class="required">*</span></label>
                            <input type="number" name="credit" class="form-control" value="3" min="1" max="10" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Marks <span class="required">*</span></label>
                            <input type="number" name="full_marks" class="form-control" value="100" required>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" class="form-control" value="40" required>
                        </div>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked> Active Subject
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSubjectModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal-overlay" id="editSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Subject</span>
                <button class="modal-close" onclick="closeModal('editSubjectModal')">&times;</button>
            </div>
            <form method="POST" id="editSubjectForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject Code <span class="required">*</span></label>
                            <input type="text" name="code" id="es_code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Category (ক্যাটাগরি)</label>
                            <select name="category_id" id="es_category_id" class="form-control">
                                <option value="">-- কোনো ক্যাটাগরি নেই --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject Name <span class="required">*</span></label>
                            <input type="text" name="name" id="es_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Subject Credit <span class="required">*</span></label>
                            <input type="number" name="credit" id="es_credit" class="form-control" min="1" max="10" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Marks <span class="required">*</span></label>
                            <input type="number" name="full_marks" id="es_full_marks" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" id="es_pass_marks" class="form-control" required>
                        </div>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="is_active" id="es_is_active" value="1"> Active Subject
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editSubjectModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Clone Subject Modal --}}
    <div class="modal-overlay" id="cloneSubjectModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">বিষয় ক্লোন করুন (Clone Subject &amp; Modules)</span>
                <button class="modal-close" onclick="closeModal('cloneSubjectModal')">&times;</button>
            </div>
            <form method="POST" id="cloneSubjectForm" action="">
                @csrf
                <div class="modal-body">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px">
                        <i class="fa-solid fa-copy"></i> এই বিষয়ের সমস্ত মডিউল ও লার্নিং রিসোর্স হুবহু নতুন বিষয়ে কপি করা হবে।
                    </div>
                    <div class="form-group">
                        <label>মূল বিষয়</label>
                        <input type="text" id="clone_orig_name" class="form-control" readonly style="background:#f8fafc">
                    </div>
                    <div class="form-group">
                        <label>নতুন বিষয়ের কোড (New Code)</label>
                        <input type="text" name="new_code" id="clone_new_code" class="form-control" placeholder="ফাঁকা রাখলে স্বয়ংক্রিয় কোড তৈরি হবে">
                    </div>
                    <div class="form-group">
                        <label>নতুন বিষয়ের নাম (New Name)</label>
                        <input type="text" name="new_name" id="clone_new_name" class="form-control" placeholder="ফাঁকা রাখলে মূল নামের শেষে (Copy) যুক্ত হবে">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('cloneSubjectModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#059669;border-color:#059669">
                        <i class="fa-solid fa-clone"></i> ক্লোন সম্পন্ন করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditSubjectModal(subj) {
        document.getElementById('editSubjectForm').action = '/admin/subjects/' + subj.id;
        document.getElementById('es_code').value = subj.code;
        document.getElementById('es_category_id').value = subj.category_id || '';
        document.getElementById('es_credit').value = subj.credit;
        document.getElementById('es_name').value = subj.name;
        document.getElementById('es_full_marks').value = subj.full_marks;
        document.getElementById('es_pass_marks').value = subj.pass_marks;
        document.getElementById('es_is_active').checked = !!subj.is_active;
        openModal('editSubjectModal');
    }

    function openCloneSubjectModal(id, name, code) {
        document.getElementById('cloneSubjectForm').action = '/admin/subjects/' + id + '/clone';
        document.getElementById('clone_orig_name').value = code + ' - ' + name;
        document.getElementById('clone_new_code').value = code + '-COPY';
        document.getElementById('clone_new_name').value = name + ' (Copy)';
        openModal('cloneSubjectModal');
    }
    </script>
    @endpush
</x-admin-layout>
