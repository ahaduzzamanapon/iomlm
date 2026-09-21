<x-admin-layout>
    <x-slot name="title">বিষয় ক্যাটাগরি — Subject Categories</x-slot>

    <div class="page-header" style="margin-bottom:20px">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush', sans-serif">বিষয় ক্যাটাগরি ব্যবস্থাপনা (Subject Categories)</h1>
            <p style="color:var(--text-muted);font-size:13px">
                আলিম, দাওরায়ে হাদিস, মক্তব, হিফজ ইত্যাদি বিভিন্ন প্রোগ্রামের বিষয়ের শ্রেণিবিভাগ নির্ধারণ ও পরিচালনা করুন
            </p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addCategoryModal')">
                <i class="fa-solid fa-plus"></i> নতুন ক্যাটাগরি যোগ করুন
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span class="card-title">ক্যাটাগরি তালিকা (Category List)</span>
            <span class="badge badge-secondary no-dot">{{ $categories->count() }}টি ক্যাটাগরি</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:70px"># ক্রম</th>
                        <th>ক্যাটাগরির নাম (Category Name)</th>
                        <th>কোড (Code)</th>
                        <th>বিবরণ (Description)</th>
                        <th>মোট বিষয় (Subjects Count)</th>
                        <th>স্ট্যাটাস</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $idx => $cat)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong style="color:var(--blue);font-size:14px">{{ $cat->name }}</strong>
                        </td>
                        <td>
                            @if($cat->code)
                                <span class="badge badge-secondary no-dot" style="font-weight:700">{{ $cat->code }}</span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td class="td-muted" style="max-width:300px">
                            {{ $cat->description ?? '—' }}
                        </td>
                        <td>
                            <a href="{{ route('admin.subjects.index', ['category_id' => $cat->id]) }}" class="badge badge-scheduled no-dot" style="text-decoration:none">
                                <i class="fa-solid fa-book"></i> {{ $cat->subjects_count }}টি বিষয়
                            </a>
                        </td>
                        <td>
                            @if($cat->is_active)
                                <span class="badge badge-active">সক্রিয় (Active)</span>
                            @else
                                <span class="badge badge-secondary">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-outline btn-sm" onclick='openEditCategoryModal(@json($cat))'>
                                <i class="fa-solid fa-pen-to-square"></i> এডিট
                            </button>
                            <form method="POST" action="{{ route('admin.subject-categories.destroy', $cat) }}" style="display:inline" onsubmit="return confirm('এই ক্যাটাগরিটি মুছে ফেলতে চান? এতে যুক্ত বিষয়গুলোর ক্যাটাগরি আনলিংক হবে।')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red">
                                    <i class="fa-solid fa-trash"></i> মুছুন
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                            কোনো ক্যাটাগরি তৈরি করা হয়নি। নতুন ক্যাটাগরি যুক্ত করুন।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Category Modal --}}
    <div class="modal-overlay" id="addCategoryModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">নতুন বিষয় ক্যাটাগরি যোগ করুন</span>
                <button class="modal-close" onclick="closeModal('addCategoryModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.subject-categories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>ক্যাটাগরির নাম <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="যেমন: আলিম (Alim) বা দাওরায়ে হাদিস" required>
                    </div>
                    <div class="form-group">
                        <label>কোড (Short Code)</label>
                        <input type="text" name="code" class="form-control" placeholder="যেমন: ALIM, DAWRAH, MAKTAB">
                    </div>
                    <div class="form-group">
                        <label>বিবরণ (Description)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="ক্যাটাগরি সম্পর্কে সংক্ষিপ্ত বিবরণ..."></textarea>
                    </div>
                    <label class="form-check" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>সক্রিয় (Active)</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addCategoryModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">ক্যাটাগরি সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Category Modal --}}
    <div class="modal-overlay" id="editCategoryModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">ক্যাটাগরি সম্পাদনা করুন</span>
                <button class="modal-close" onclick="closeModal('editCategoryModal')">&times;</button>
            </div>
            <form method="POST" id="editCategoryForm" action="">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>ক্যাটাগরির নাম <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_cat_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>কোড (Short Code)</label>
                        <input type="text" name="code" id="edit_cat_code" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>বিবরণ (Description)</label>
                        <textarea name="description" id="edit_cat_description" class="form-control" rows="3"></textarea>
                    </div>
                    <label class="form-check" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_active" id="edit_cat_is_active" value="1">
                        <span>সক্রিয় (Active)</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editCategoryModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">আপডেট সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditCategoryModal(cat) {
        document.getElementById('editCategoryForm').action = '/admin/subject-categories/' + cat.id;
        document.getElementById('edit_cat_name').value = cat.name;
        document.getElementById('edit_cat_code').value = cat.code || '';
        document.getElementById('edit_cat_description').value = cat.description || '';
        document.getElementById('edit_cat_is_active').checked = !!cat.is_active;
        openModal('editCategoryModal');
    }
    </script>
</x-admin-layout>
