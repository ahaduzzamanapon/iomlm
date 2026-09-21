<x-teacher-layout>
    <x-slot name="title">Teacher Assignments</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1 style="font-family:'Kalpurush', sans-serif">অ্যাসাইনমেন্ট পরিচালনা (Assignments)</h1>
            <p>আপনার দায়িত্বপ্রাপ্ত বিষয়ের অ্যাসাইনমেন্ট তৈরি করুন ও শিক্ষার্থীদের খাতা মূল্যায়ন করুন</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('createAssignmentModal')">
                <i class="fa-solid fa-plus"></i> নতুন অ্যাসাইনমেন্ট
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
        <form method="GET" action="{{ route('teacher.assignments.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <select name="subject_id" class="form-control">
                    <option value="">সকল দায়িত্বপ্রাপ্ত বিষয়</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ ($subjectId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                    @endforeach
                </select>
            </div>
            <div style="width:220px">
                <select name="batch_id" class="form-control">
                    <option value="">সকল ব্যাচ</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ ($batchId ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:8px 16px">
                <i class="fa-solid fa-filter"></i> ফিল্টার
            </button>
            @if(!empty($subjectId) || !empty($batchId))
                <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline" style="padding:8px 14px">
                    <i class="fa-solid fa-rotate-left"></i> রিসেট
                </a>
            @endif
        </form>
    </div>

    {{-- Assignments Table --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span class="card-title">অ্যাসাইনমেন্ট তালিকা</span>
            <span class="badge badge-secondary no-dot">{{ $assignments->total() }}টি অ্যাসাইনমেন্ট</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>অ্যাসাইনমেন্ট শিরোনাম</th>
                        <th>বিষয়</th>
                        <th>ব্যাচ</th>
                        <th>পূর্ণমান</th>
                        <th>শেষ সময় (Deadline)</th>
                        <th>জমা পড়েছে</th>
                        <th style="text-align:right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                    <tr>
                        <td class="td-primary">
                            <a href="{{ route('teacher.assignments.show', $a) }}" style="font-weight:700;color:var(--blue);font-size:14px">
                                {{ $a->title }}
                            </a>
                            @if($a->file_path)
                                <div style="margin-top:2px">
                                    <a href="{{ asset('storage/' . $a->file_path) }}" target="_blank" style="font-size:11px;color:#64748b;text-decoration:none">
                                        <i class="fa-solid fa-paperclip"></i> ফাইল সংযুক্ত
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary no-dot" style="font-weight:600">
                                {{ $a->subject?->code }} - {{ $a->subject?->name }}
                            </span>
                        </td>
                        <td>
                            @if($a->batch)
                                <span class="badge badge-secondary no-dot">{{ $a->batch->name }}</span>
                            @else
                                <span class="badge badge-scheduled no-dot">সকল ব্যাচ</span>
                            @endif
                        </td>
                        <td><strong>{{ $a->total_marks }}</strong> নম্বর</td>
                        <td>
                            @if($a->isExpired())
                                <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:11px">
                                    {{ \Carbon\Carbon::parse($a->due_datetime)->format('d M Y, h:i A') }} (সমাপ্ত)
                                </span>
                            @else
                                <span class="badge" style="background:#ecfdf5;color:#047857;font-size:11px">
                                    {{ \Carbon\Carbon::parse($a->due_datetime)->format('d M Y, h:i A') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('teacher.assignments.show', $a) }}" class="badge badge-primary no-dot" style="text-decoration:none">
                                <i class="fa-solid fa-inbox"></i> {{ $a->submissions->count() }}টি জমা
                            </a>
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('teacher.assignments.show', $a) }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-check-to-slot"></i> খাতা মূল্যায়ন
                            </a>
                            <form method="POST" action="{{ route('teacher.assignments.destroy', $a) }}" style="display:inline" onsubmit="return confirm('এই অ্যাসাইনমেন্ট মুছে ফেলতে চান?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                            কোনো অ্যাসাইনমেন্ট পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Assignment Modal --}}
    <div class="modal-overlay" id="createAssignmentModal">
        <div class="modal" style="max-width:650px">
            <div class="modal-header">
                <span class="modal-title">নতুন অ্যাসাইনমেন্ট তৈরি করুন</span>
                <button class="modal-close" onclick="closeModal('createAssignmentModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('teacher.assignments.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>বিষয় <span class="required">*</span></label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">-- বিষয় নির্বাচন করুন --</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>নির্দিষ্ট ব্যাচ (ঐচ্ছিক)</label>
                            <select name="batch_id" class="form-control">
                                <option value="">-- সকল ব্যাচের জন্য উন্মুক্ত --</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>অ্যাসাইনমেন্টের শিরোনাম <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="যেমন: অ্যাসাইনমেন্ট ১: বিষয়ভিত্তিক গবেষণা" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>মোট নম্বর <span class="required">*</span></label>
                            <input type="number" name="total_marks" class="form-control" value="20" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>শুরুর সময়</label>
                            <input type="datetime-local" name="start_datetime" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="form-group">
                            <label>জমার শেষ সময় <span class="required">*</span></label>
                            <input type="datetime-local" name="due_datetime" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>নির্দেশনাবলী</label>
                        <textarea name="instructions" class="form-control" rows="3" placeholder="অ্যাসাইনমেন্টের জন্য প্রয়োজনীয় নির্দেশনা..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>প্রশ্ন বা তথ্য ফাইল (PDF/Doc/Image)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createAssignmentModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">অ্যাসাইনমেন্ট প্রকাশ করুন</button>
                </div>
            </form>
        </div>
    </div>
</x-teacher-layout>
