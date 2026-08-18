<x-admin-layout>
    <x-slot name="title">প্রশ্ন ব্যাংক ব্যবস্থাপনা</x-slot>

    <!-- KaTeX for LaTeX rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body);"></script>

    <style>
        .qb-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
        .qb-title  { display:flex; align-items:center; gap:8px; font-size:22px; font-weight:700; color:#1e293b; }
        .qb-subtitle { font-size:13px; color:#64748b; margin-top:2px; }
        .qb-actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }

        .btn-purple     { background:#6366f1; color:#fff; border:none; border-radius:8px; padding:9px 16px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px; text-decoration:none; cursor:pointer; transition:all .2s; }
        .btn-purple:hover { background:#4f46e5; color:#fff; }
        .btn-green      { background:#10b981; color:#fff; border:none; border-radius:8px; padding:9px 16px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px; text-decoration:none; cursor:pointer; }
        .btn-green:hover { background:#059669; color:#fff; }
        .btn-ghost-purple { background:#fff; color:#334155; border:1px solid #cbd5e1; border-radius:8px; padding:9px 16px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px; text-decoration:none; cursor:pointer; }
        .btn-ghost-purple:hover { background:#f8fafc; border-color:#94a3b8; }
        .btn-teal       { background:#0d9488; color:#fff; border:none; border-radius:8px; padding:9px 16px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px; text-decoration:none; cursor:pointer; }
        .btn-teal:hover { background:#0f766e; color:#fff; }

        .filter-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; margin-bottom:20px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .search-input-wrap { position:relative; flex:1; min-width:220px; }
        .search-input-wrap svg { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; }
        .search-input { width:100%; padding-left:38px; padding-right:12px; height:40px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px; outline:none; }
        .search-input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }

        .q-card-table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; }
        .q-card-table th { background:#f8fafc; font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; padding:12px 16px; border-bottom:1px solid #e2e8f0; text-align:left; }
        .q-card-table td { padding:14px 16px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
        .q-card-table tr:last-child td { border-bottom:none; }

        .q-id   { font-weight:700; font-size:13px; color:#64748b; }
        .q-text { font-size:14px; font-weight:700; color:#0f172a; line-height:1.5; }

        .type-badge-mcq     { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; background:#e0e7ff; color:#4338ca; margin-bottom:6px; }
        .type-badge-written { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; background:#fce7f3; color:#9d174d; margin-bottom:6px; }

        .subject-badge     { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fef3c7; color:#d97706; margin-bottom:6px; }
        .subject-sub-badge { display:inline-block; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600; background:#e0e7ff; color:#4338ca; }

        .options-grid  { display:grid; grid-template-columns:1fr 1fr; gap:8px; min-width:280px; }
        .option-item   { padding:8px 12px; border-radius:6px; font-size:12px; font-weight:500; background:#f8fafc; border:1px solid #e2e8f0; color:#334155; }
        .option-item.correct { background:#dcfce7; border-color:#86efac; color:#166534; font-weight:700; }

        .correct-badge { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:50%; background:#10b981; color:#fff; font-weight:700; font-size:12px; }
        .written-col   { color:#94a3b8; font-size:12px; font-style:italic; }

        .btn-delete { color:#ef4444; background:none; border:none; cursor:pointer; padding:6px; border-radius:6px; }
        .btn-delete:hover { background:#fef2f2; color:#dc2626; }

        .tab-btns { display:flex; gap:6px; }
        .tab-btn { padding:6px 14px; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #e2e8f0; background:#f8fafc; color:#334155; cursor:pointer; text-decoration:none; }
        .tab-btn.active { background:#6366f1; color:#fff; border-color:#6366f1; }
    </style>

    {{-- Page Header --}}
    <div class="qb-header">
        <div>
            <div class="qb-title">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e0e7ff;color:#4f46e5;font-size:16px">❓</span>
                প্রশ্ন ব্যাংক ব্যবস্থাপনা
            </div>
            <div class="qb-subtitle">MCQ ও Written — দুই ধরনের প্রশ্ন যোগ করুন, Exam-এ সংযুক্ত করুন</div>
        </div>
        <div class="qb-actions">
            <a href="{{ route('admin.questions.template-download') }}" class="btn-teal">
                ⬇️ CSV Template
            </a>
            <button class="btn-ghost-purple" onclick="openModal('bulkUploadModal')">
                📤 Bulk CSV Upload
            </button>
            <button class="btn-green" onclick="openModal('createWrittenModal')">
                ✏️ Written প্রশ্ন
            </button>
            <button class="btn-purple" onclick="openModal('createMcqModal')">
                + MCQ প্রশ্ন
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.questions.index') }}" class="filter-card">
        <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" class="search-input" placeholder="প্রশ্ন দিয়ে অনুসন্ধান করুন..." value="{{ $search }}">
        </div>

        <select name="subject_id" class="form-control" style="width:200px;height:40px;border-radius:8px;font-size:13px">
            <option value="">সকল বিষয়</option>
            @foreach($subjects as $sub)
                <option value="{{ $sub->id }}" {{ $subjectId == $sub->id ? 'selected' : '' }}>{{ $sub->name }} ({{ $sub->code }})</option>
            @endforeach
        </select>

        <div class="tab-btns">
            <a href="{{ route('admin.questions.index', array_merge(request()->except('type'), ['type'=>''])) }}"
               class="tab-btn {{ !$typeFilter ? 'active' : '' }}">সব</a>
            <a href="{{ route('admin.questions.index', array_merge(request()->except('type'), ['type'=>'MCQ'])) }}"
               class="tab-btn {{ $typeFilter === 'MCQ' ? 'active' : '' }}">MCQ</a>
            <a href="{{ route('admin.questions.index', array_merge(request()->except('type'), ['type'=>'WRITTEN'])) }}"
               class="tab-btn {{ $typeFilter === 'WRITTEN' ? 'active' : '' }}">Written</a>
        </div>

        <button type="submit" class="btn-purple" style="height:40px">ফিল্টার</button>
        @if($search || $subjectId || $typeFilter)
            <a href="{{ route('admin.questions.index') }}" class="btn-ghost-purple" style="height:40px">Reset</a>
        @endif
    </form>

    {{-- Questions Table --}}
    <table class="q-card-table">
        <thead>
            <tr>
                <th style="width:70px">#</th>
                <th>প্রশ্ন</th>
                <th style="width:200px">বিষয় ও কঠিনতা</th>
                <th style="width:320px">অপশন / ধরন</th>
                <th style="width:80px;text-align:center">সঠিক</th>
                <th style="width:60px;text-align:center">অ্যাকশন</th>
            </tr>
        </thead>
        <tbody>
            @forelse($questions as $q)
            <tr>
                <td class="q-id">{{ $q->id }}</td>
                <td>
                    @if($q->question_type === 'WRITTEN')
                        <span class="type-badge-written">✏️ Written</span>
                    @else
                        <span class="type-badge-mcq">🔵 MCQ</span>
                    @endif
                    <div class="q-text">{!! e($q->question_text) !!}</div>
                </td>
                <td>
                    @if($q->subject)
                        <div class="subject-badge">🎯 {{ $q->subject->name }}</div><br>
                        <span class="subject-sub-badge">{{ $q->subject->code }}</span>
                    @else
                        <span style="color:#94a3b8;font-size:12px">Unassigned</span>
                    @endif
                    <div style="margin-top:6px">
                        <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;background:{{ $q->difficulty === 'easy' ? '#dcfce7' : ($q->difficulty === 'medium' ? '#fef3c7' : '#fee2e2') }};color:{{ $q->difficulty === 'easy' ? '#166534' : ($q->difficulty === 'medium' ? '#92400e' : '#991b1b') }};text-transform:uppercase">
                            {{ $q->difficulty }}
                        </span>
                    </div>
                </td>
                <td>
                    @if($q->question_type === 'WRITTEN')
                        <span class="written-col">📝 ছাত্র হাতে লিখে Image Upload করবে</span>
                    @else
                        <div class="options-grid">
                            @foreach($q->options ?? [] as $opt)
                            @php $isRight = strtolower($opt['id'] ?? '') === strtolower($q->correct_option_id); @endphp
                            <div class="option-item {{ $isRight ? 'correct' : '' }}">
                                <strong>{{ strtoupper($opt['id']) }}:</strong> {{ $opt['text'] ?? '' }}
                            </div>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td style="text-align:center">
                    @if($q->question_type === 'MCQ')
                        <div class="correct-badge">{{ strtoupper($q->correct_option_id) }}</div>
                    @else
                        <span style="color:#94a3b8;font-size:11px">Teacher</span>
                    @endif
                </td>
                <td style="text-align:center">
                    <form method="POST" action="{{ route('admin.questions.destroy', $q) }}" onsubmit="return confirm('প্রশ্নটি মুছে ফেলতে চান?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-delete" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:#94a3b8">
                    <p style="font-size:15px;font-weight:600">কোনো প্রশ্ন পাওয়া যায়নি।</p>
                    <p style="font-size:12px;margin-top:4px">উপরের বাটনে ক্লিক করে প্রশ্ন যোগ করুন।</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:20px">{{ $questions->links() }}</div>

    {{-- Create MCQ Modal --}}
    <div class="modal-overlay" id="createMcqModal">
        <div class="modal" style="max-width:650px">
            <div class="modal-header">
                <span class="modal-title">🔵 নতুন MCQ প্রশ্ন যোগ করুন</span>
                <button class="modal-close" onclick="closeModal('createMcqModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.questions.store') }}">
                @csrf
                <input type="hidden" name="question_type" value="MCQ">
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>বিষয় (Subject)</label>
                            <select name="subject_id" class="form-control">
                                <option value="">-- বিষয় নির্বাচন করুন --</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>কঠিনতা <span class="required">*</span></label>
                            <select name="difficulty" class="form-control" required>
                                <option value="easy">Easy (সহজ)</option>
                                <option value="medium">Medium (মধ্যম)</option>
                                <option value="hard">Hard (কঠিন)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>প্রশ্ন (Question Text) <span class="required">*</span></label>
                        <textarea name="question_text" class="form-control" rows="3" required placeholder="প্রশ্ন লিখুন..."></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>অপশন A <span class="required">*</span></label>
                            <input type="text" name="option_a" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>অপশন B <span class="required">*</span></label>
                            <input type="text" name="option_b" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>অপশন C <span class="required">*</span></label>
                            <input type="text" name="option_c" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>অপশন D <span class="required">*</span></label>
                            <input type="text" name="option_d" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>সঠিক অপশন <span class="required">*</span></label>
                        <select name="correct_option_id" class="form-control" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost-purple" onclick="closeModal('createMcqModal')">বাতিল</button>
                    <button type="submit" class="btn-purple">💾 MCQ সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Create Written Modal --}}
    <div class="modal-overlay" id="createWrittenModal">
        <div class="modal" style="max-width:550px">
            <div class="modal-header">
                <span class="modal-title">✏️ নতুন Written প্রশ্ন যোগ করুন</span>
                <button class="modal-close" onclick="closeModal('createWrittenModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.questions.store') }}">
                @csrf
                <input type="hidden" name="question_type" value="WRITTEN">
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="background:#fce7f3;border:1px solid #fbcfe8;border-radius:8px;padding:10px 14px;font-size:12px;color:#9d174d">
                        💡 Written প্রশ্নে কোনো অপশন নেই। ছাত্র হাতে লিখে Answer-এর Image Upload করবে। Teacher সেই Image দেখে Marks দেবে।
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>বিষয় (Subject)</label>
                            <select name="subject_id" class="form-control">
                                <option value="">-- বিষয় নির্বাচন করুন --</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>কঠিনতা <span class="required">*</span></label>
                            <select name="difficulty" class="form-control" required>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>প্রশ্ন (Question Text) <span class="required">*</span></label>
                        <textarea name="question_text" class="form-control" rows="4" required placeholder="Written প্রশ্ন লিখুন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost-purple" onclick="closeModal('createWrittenModal')">বাতিল</button>
                    <button type="submit" class="btn-green">💾 Written প্রশ্ন সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk CSV Upload Modal --}}
    <div class="modal-overlay" id="bulkUploadModal">
        <div class="modal" style="max-width:600px">
            <div class="modal-header">
                <span class="modal-title">📤 Bulk CSV Upload</span>
                <button class="modal-close" onclick="closeModal('bulkUploadModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.questions.bulk-upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;font-size:13px;color:#166534">
                        <strong>📋 CSV ফরম্যাট:</strong><br>
                        <code style="font-size:11px">question_type, subject_code, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty</code><br><br>
                        • MCQ: সব column fill করুন<br>
                        • Written: option_a-d ও correct_option ফাঁকা রাখুন
                    </div>

                    <div style="text-align:center">
                        <a href="{{ route('admin.questions.template-download') }}" class="btn-teal" style="font-size:12px">
                            ⬇️ Template Download করুন
                        </a>
                    </div>

                    <div class="form-group">
                        <label>CSV ফাইল নির্বাচন করুন <span class="required">*</span></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost-purple" onclick="closeModal('bulkUploadModal')">বাতিল</button>
                    <button type="submit" class="btn-purple">⚡ আপলোড ও Import করুন</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
