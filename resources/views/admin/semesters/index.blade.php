<x-admin-layout>
    <x-slot name="title">Semesters</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>সেমিস্টার ব্যবস্থাপনা (Semester Management)</h1>
            <p>সেমিস্টার-ভিত্তিক কোর্সের ক্রম ও ডাইনামিক গ্রুপিং (লিঙ্গভিত্তিক / বিভাজনভিত্তিক) কনফিগারেশন</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addSemesterModal')">
                <i class="fa-solid fa-plus"></i> নতুন সেমিস্টার
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>কোর্স (Course)</th>
                        <th>সেমিস্টার নাম</th>
                        <th>ক্রমিক (Seq)</th>
                        <th>গ্রুপিং কনফিগারেশন</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semesters as $sem)
                    <tr>
                        <td class="td-primary"><strong>{{ $sem->course->name ?? '—' }}</strong></td>
                        <td>{{ $sem->name }}</td>
                        <td><span class="badge badge-secondary no-dot">সেমিস্টার #{{ $sem->sequence_no }}</span></td>
                        <td>
                            @if($sem->has_groups && $sem->group_type === 'GENDER')
                                <span class="badge" style="background:#0284c7;color:#fff;font-size:11px;padding:3px 8px">
                                    <i class="fa-solid fa-venus-mars"></i> ভাই ও বোন শাখা (Gender)
                                </span>
                            @elseif($sem->has_groups && $sem->group_type === 'SPLIT')
                                <span class="badge" style="background:#8b5cf6;color:#fff;font-size:11px;padding:3px 8px">
                                    <i class="fa-solid fa-users-line"></i> বিভাজন শাখা ({{ $sem->split_count ?? 2 }} দল)
                                </span>
                            @else
                                <span class="badge badge-secondary no-dot" style="font-size:11px;color:var(--text-muted)">
                                    <i class="fa-solid fa-users"></i> যৌথ / সাধারণ (No Group)
                                </span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <button type="button" class="btn btn-ghost btn-sm text-primary" onclick="openModal('editSemesterModal_{{ $sem->id }}')">
                                <i class="fa-solid fa-pen-to-square"></i> এডিট
                            </button>
                            <form method="POST" action="{{ route('admin.semesters.destroy', $sem) }}" style="display:inline" onsubmit="return confirm('সেমিস্টারটি ডিলিট করতে চান?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red"><i class="fa-solid fa-trash"></i> মুছুন</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Semester Modal -->
                    <div class="modal-overlay" id="editSemesterModal_{{ $sem->id }}">
                        <div class="modal">
                            <div class="modal-header">
                                <span class="modal-title">সেমিস্টার কনফিগারেশন এডিট — {{ $sem->name }}</span>
                                <button class="modal-close" onclick="closeModal('editSemesterModal_{{ $sem->id }}')">&times;</button>
                            </div>
                            <form method="POST" action="{{ route('admin.semesters.update', $sem) }}">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>ক্রমিক নম্বর (Sequence No.) <span class="required">*</span></label>
                                            <input type="number" name="sequence_no" class="form-control" value="{{ $sem->sequence_no }}" min="1" required>
                                        </div>
                                        <div class="form-group">
                                            <label>সেমিস্টার নাম <span class="required">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $sem->name }}" required>
                                        </div>
                                    </div>

                                    <div style="background:var(--bg-subtle, #f8fafc);border:1px solid var(--border-color, #e2e8f0);border-radius:8px;padding:14px;margin-top:12px">
                                        <label style="font-weight:600;display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:8px">
                                            <input type="checkbox" name="has_groups" value="1" id="edit_has_groups_{{ $sem->id }}" {{ $sem->has_groups ? 'checked' : '' }} onchange="toggleGroupOptions('edit_group_sec_{{ $sem->id }}', this.checked)">
                                            <span>এই সেমিস্টারে গ্রুপ শাখা থাকবে (Dynamic Grouping)</span>
                                        </label>
                                        <div id="edit_group_sec_{{ $sem->id }}" style="display: {{ $sem->has_groups ? 'block' : 'none' }};margin-top:10px;padding-top:10px;border-top:1px dashed var(--border-color, #cbd5e1)">
                                            <label style="font-size:12px;font-weight:600;margin-bottom:6px;display:block">গ্রুপিং ক্রাইটেরিয়া (Criteria)</label>
                                            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
                                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                                    <input type="radio" name="group_type" value="GENDER" {{ $sem->group_type === 'GENDER' ? 'checked' : '' }}>
                                                    <span><strong>লিঙ্গভিত্তিক (Gender-based)</strong> — ভাই শাখা ও বোন শাখা আলাদা রুটিন ও ক্লাস</span>
                                                </label>
                                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                                    <input type="radio" name="group_type" value="SPLIT" {{ $sem->group_type === 'SPLIT' ? 'checked' : '' }}>
                                                    <span><strong>বিভাজন ভিত্তিক (Split-based)</strong> — গ্রুপ ক, গ্রুপ খ ইত্যাদি দল</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline" onclick="closeModal('editSemesterModal_{{ $sem->id }}')">বাতিল</button>
                                    <button type="submit" class="btn btn-primary">আপডেট করুন</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">কোনো সেমিস্টার তৈরি করা হয়নি। নতুন সেমিস্টার বাটনে ক্লিক করুন।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Semester Modal -->
    <div class="modal-overlay" id="addSemesterModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">নতুন সেমিস্টার তৈরি</span>
                <button class="modal-close" onclick="closeModal('addSemesterModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.semesters.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>কোর্স নির্বাচন করুন <span class="required">*</span></label>
                        <select name="course_id" class="form-control" required>
                            <option value="">-- কোর্স বাছাই করুন --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>ক্রমিক নম্বর (Sequence No.) <span class="required">*</span></label>
                            <input type="number" name="sequence_no" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>সেমিস্টার নাম <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="যেমন: প্রথম সেমিস্টার (1st Semester)" required>
                        </div>
                    </div>

                    <div style="background:var(--bg-subtle, #f8fafc);border:1px solid var(--border-color, #e2e8f0);border-radius:8px;padding:14px;margin-top:12px">
                        <label style="font-weight:600;display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:8px">
                            <input type="checkbox" name="has_groups" value="1" id="add_has_groups" onchange="toggleGroupOptions('add_group_sec', this.checked)">
                            <span>এই সেমিস্টারে গ্রুপ শাখা থাকবে (Dynamic Grouping)</span>
                        </label>
                        <div id="add_group_sec" style="display:none;margin-top:10px;padding-top:10px;border-top:1px dashed var(--border-color, #cbd5e1)">
                            <label style="font-size:12px;font-weight:600;margin-bottom:6px;display:block">গ্রুপিং ক্রাইটেরিয়া (Criteria)</label>
                            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                    <input type="radio" name="group_type" value="GENDER" checked>
                                    <span><strong>লিঙ্গভিত্তিক (Gender-based)</strong> — ভাই শাখা ও বোন শাখা আলাদা রুটিন ও ক্লাস</span>
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                                    <input type="radio" name="group_type" value="SPLIT">
                                    <span><strong>বিভাজন ভিত্তিক (Split-based)</strong> — গ্রুপ ক, গ্রুপ খ ইত্যাদি দল</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSemesterModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">সেমিস্টার সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleGroupOptions(containerId, isChecked) {
        const el = document.getElementById(containerId);
        if (el) el.style.display = isChecked ? 'block' : 'none';
    }
    </script>
</x-admin-layout>
