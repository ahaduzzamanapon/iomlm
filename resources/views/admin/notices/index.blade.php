<x-admin-layout>
    <x-slot name="title">Central Notice Board</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Central Notice Board &amp; Announcements</h1>
            <p>Publish announcements, exam notices, and emergency alerts for students &amp; teachers</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('createNoticeModal')">
                + Publish New Notice
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Notice Cards List --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        @forelse($notices as $n)
        @php
            $priorityClass = match($n->priority) {
                'URGENT' => 'border-left:5px solid #e11d48;background:#fff5f5',
                'IMPORTANT' => 'border-left:5px solid #f59e0b;background:#fffbeb',
                default => 'border-left:5px solid #3b82f6;background:#fff'
            };
        @endphp
        <div class="card" style="{{ $priorityClass }}">
            <div style="padding:20px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px">
                    <div>
                        <span class="badge {{ $n->priority === 'URGENT' ? 'badge-danger' : ($n->priority === 'IMPORTANT' ? 'badge-warning' : 'badge-info') }} no-dot" style="font-size:10px">
                            {{ $n->priority }}
                        </span>
                        <span class="badge badge-secondary no-dot" style="font-size:10px;margin-left:4px">
                            Target: {{ $n->target_audience }}
                        </span>
                        @if($n->batch)
                            <span class="badge badge-primary no-dot" style="font-size:10px;margin-left:4px">
                                Batch: {{ $n->batch->name }}
                            </span>
                        @endif
                        @if($n->semester)
                            <span class="badge badge-active no-dot" style="font-size:10px;margin-left:4px;background:#10b981">
                                Semester: {{ $n->semester->name }}
                            </span>
                        @endif
                        <h3 style="font-size:16px;font-weight:700;margin:6px 0 2px;color:#0f172a">{{ $n->title }}</h3>
                        <div style="font-size:11px;color:#64748b">
                            Published {{ $n->created_at->diffForHumans() }} ({{ $n->created_at->format('d M Y, h:i A') }}) by {{ $n->creator->name ?? 'Admin' }}
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <button type="button" class="btn btn-outline btn-sm" style="color:#0284c7;border-color:#bae6fd;" onclick="openEditNotice({{ json_encode($n) }})">
                            <i class="fa-solid fa-pen-to-square"></i> এডিট
                        </button>
                        <form method="POST" action="{{ route('admin.notices.destroy', $n) }}" onsubmit="return confirm('এই নোটিশটি মুছে ফেলতে চান?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444">
                                <i class="fa-solid fa-trash"></i> ডিলিট
                            </button>
                        </form>
                    </div>
                </div>
                <div style="font-size:14px;color:#334155;line-height:1.6;white-space:pre-line;margin-top:10px;border-top:1px solid rgba(0,0,0,0.05);padding-top:10px">
                    {{ $n->content }}
                </div>
            </div>
        </div>
        @empty
        <div class="card" style="padding:40px;text-align:center;color:#94a3b8">
            No notices published yet. Click "+ Publish New Notice" above.
        </div>
        @endforelse
    </div>

    <div style="margin-top:20px">
        {{ $notices->links() }}
    </div>

    {{-- Create Notice Modal --}}
    <div class="modal-overlay" id="createNoticeModal">
        <div class="modal" style="max-width:650px">
            <div class="modal-header">
                <span class="modal-title">+ Publish Announcement Notice</span>
                <button class="modal-close" onclick="closeModal('createNoticeModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.notices.store') }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>Notice Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Midterm Examination Schedule & Guidelines" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Target Audience <span class="required">*</span></label>
                            <select name="target_audience" class="form-control" required>
                                <option value="ALL">ALL (সকলের জন্য)</option>
                                <option value="STUDENTS">STUDENTS (শিক্ষার্থীদের জন্য)</option>
                                <option value="TEACHERS">TEACHERS (শিক্ষকদের জন্য)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Priority Level <span class="required">*</span></label>
                            <select name="priority" class="form-control" required>
                                <option value="NORMAL">NORMAL (সাধারণ)</option>
                                <option value="IMPORTANT">IMPORTANT (গুরুত্বপূর্ণ)</option>
                                <option value="URGENT">URGENT (জরুরি)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Specific Batch (ঐচ্ছিক)</label>
                            <select name="batch_id" class="form-control">
                                <option value="">All Batches (সকল ব্যাচ)</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Specific Semester (সেমিস্টার)</label>
                            <select name="semester_id" class="form-control">
                                <option value="">All Semesters (সকল সেমিস্টার)</option>
                                @foreach($semesters as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notice Description / Details <span class="required">*</span></label>
                        <textarea name="content" class="form-control" rows="5" placeholder="Enter full announcement notice text..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createNoticeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Publish Notice</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Notice Modal --}}
    <div class="modal-overlay" id="editNoticeModal">
        <div class="modal" style="max-width:650px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title" style="color:#0284c7">
                    <i class="fa-solid fa-pen-to-square"></i> নোটিশ সংশোধন / এডিট করুন (Edit Notice)
                </span>
                <button class="modal-close" onclick="closeModal('editNoticeModal')">&times;</button>
            </div>
            <form method="POST" id="editNoticeForm">
                @csrf @method('PUT')
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>নোটিশের শিরোনাম (Notice Title) <span class="required">*</span></label>
                        <input type="text" name="title" id="edit_notice_title" class="form-control" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>লক্ষ্য দর্শক (Target Audience) <span class="required">*</span></label>
                            <select name="target_audience" id="edit_notice_audience" class="form-control" required>
                                <option value="ALL">ALL (সকলের জন্য)</option>
                                <option value="STUDENTS">STUDENTS (শিক্ষার্থীদের জন্য)</option>
                                <option value="TEACHERS">TEACHERS (শিক্ষকদের জন্য)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>গুরুত্ব (Priority Level) <span class="required">*</span></label>
                            <select name="priority" id="edit_notice_priority" class="form-control" required>
                                <option value="NORMAL">NORMAL (সাধারণ)</option>
                                <option value="IMPORTANT">IMPORTANT (গুরুত্বপূর্ণ)</option>
                                <option value="URGENT">URGENT (জরুরি)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>নির্দিষ্ট ব্যাচ (Specific Batch - ঐচ্ছিক)</label>
                            <select name="batch_id" id="edit_notice_batch" class="form-control">
                                <option value="">All Batches (সকল ব্যাচ)</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>নির্দিষ্ট সেমিস্টার (Specific Semester - ঐচ্ছিক)</label>
                            <select name="semester_id" id="edit_notice_semester" class="form-control">
                                <option value="">All Semesters (সকল সেমিস্টার)</option>
                                @foreach($semesters as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>নোটিশের মূল বিবরণ (Notice Description) <span class="required">*</span></label>
                        <textarea name="content" id="edit_notice_content" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editNoticeModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#0284c7">আপডেট সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditNotice(notice) {
        document.getElementById('editNoticeForm').action = '/admin/notices/' + notice.id;
        document.getElementById('edit_notice_title').value = notice.title || '';
        document.getElementById('edit_notice_audience').value = notice.target_audience || 'ALL';
        document.getElementById('edit_notice_priority').value = notice.priority || 'NORMAL';
        document.getElementById('edit_notice_batch').value = notice.batch_id || '';
        document.getElementById('edit_notice_semester').value = notice.semester_id || '';
        document.getElementById('edit_notice_content').value = notice.content || '';
        openModal('editNoticeModal');
    }
    </script>
    @endpush
</x-admin-layout>
