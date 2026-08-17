<x-admin-layout>
    <x-slot name="title">প্রশ্ন ব্যাংক ব্যবস্থাপনা</x-slot>

    <!-- KaTeX CSS & JS for LaTeX Math Rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body);"></script>

    <style>
        .qb-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
        .qb-title { display: flex; align-items: center; gap: 8px; font-size: 22px; font-weight: 700; color: #1e293b; }
        .qb-subtitle { font-size: 13px; color: #64748b; margin-top: 2px; }
        .qb-actions { display: flex; gap: 10px; align-items: center; }

        .btn-purple { background: #6366f1; color: #fff; border: none; border-radius: 8px; padding: 9px 16px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; cursor: pointer; transition: all 0.2s; }
        .btn-purple:hover { background: #4f46e5; color: #fff; }

        .btn-ghost-purple { background: #fff; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 16px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; cursor: pointer; }
        .btn-ghost-purple:hover { background: #f8fafc; border-color: #94a3b8; }

        .filter-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .search-input-wrap { position: relative; flex: 1; min-width: 260px; }
        .search-input-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .search-input { width: 100%; padding-left: 38px; padding-right: 12px; height: 40px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; }
        .search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

        .q-card-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .q-card-table th { background: #f8fafc; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .q-card-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .q-card-table tr:last-child td { border-bottom: none; }

        .q-id { font-weight: 700; font-size: 13px; color: #64748b; }
        .q-text { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 10px; line-height: 1.5; }

        .explanation-box { background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 10px 14px; margin-top: 10px; font-size: 12px; color: #92400e; line-height: 1.5; }
        .explanation-title { font-weight: 700; font-size: 11px; text-transform: uppercase; color: #b45309; margin-bottom: 3px; display: flex; align-items: center; gap: 4px; }

        .subject-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: #fef3c7; color: #d97706; margin-bottom: 6px; }
        .subject-sub-badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; background: #e0e7ff; color: #4338ca; }

        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; min-width: 320px; }
        .option-item { padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; }
        .option-item.correct { background: #dcfce7; border-color: #86efac; color: #166534; font-weight: 700; }

        .correct-badge { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #10b981; color: #fff; font-weight: 700; font-size: 12px; }

        .btn-delete { color: #ef4444; background: none; border: none; cursor: pointer; padding: 6px; border-radius: 6px; }
        .btn-delete:hover { background: #fef2f2; color: #dc2626; }
    </style>

    {{-- Page Header --}}
    <div class="qb-header">
        <div>
            <div class="qb-title">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e0e7ff;color:#4f46e5;font-size:16px">❓</span>
                প্রশ্ন ব্যাংক ব্যবস্থাপনা
            </div>
            <div class="qb-subtitle">নতুন MCQ প্রশ্ন যোগ করুন, সংশোধন করুন এবং কাটেক্স (KaTeX) ফর্মুলা প্রিভিউ করুন</div>
        </div>
        <div class="qb-actions">
            <button class="btn-ghost-purple" onclick="openModal('bulkUploadModal')">
                📤 বাল্ক JSON আপলোড
            </button>
            <button class="btn-purple" onclick="openModal('createQuestionModal')">
                + নতুন প্রশ্ন তৈরি
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
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

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('admin.questions.index') }}" class="filter-card">
        <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" class="search-input" placeholder="প্রশ্ন দিয়ে অনুসন্ধান করুন..." value="{{ $search }}">
        </div>

        <select name="subject_id" class="form-control" style="width:220px;height:40px;border-radius:8px;font-size:13px">
            <option value="">সকল বিষয় ও সেক্টর (All Subjects)</option>
            @foreach($subjects as $sub)
                <option value="{{ $sub->id }}" {{ $subjectId == $sub->id ? 'selected' : '' }}>{{ $sub->name }} ({{ $sub->code }})</option>
            @endforeach
        </select>

        <button type="submit" class="btn-purple" style="height:40px">ফিল্টার করুন</button>
        @if($search || $subjectId)
            <a href="{{ route('admin.questions.index') }}" class="btn-ghost-purple" style="height:40px">Reset</a>
        @endif
    </form>

    {{-- Questions Table --}}
    <table class="q-card-table">
        <thead>
            <tr>
                <th style="width:70px">#</th>
                <th>প্রশ্ন (LATEX TEXT)</th>
                <th style="width:220px">বিষয় ও সেক্টর</th>
                <th style="width:360px">অপশনসমূহ</th>
                <th style="width:90px;text-align:center">সঠিক উত্তর</th>
                <th style="width:60px;text-align:center">অ্যাকশন</th>
            </tr>
        </thead>
        <tbody>
            @forelse($questions as $q)
            <tr>
                <td class="q-id">{{ $q->id }}</td>
                <td>
                    <div class="q-text">{!! e($q->question_text) !!}</div>
                    @if($q->explanation)
                    <div class="explanation-box">
                        <div class="explanation-title">💡 ব্যাখ্যা (EXPLANATION):</div>
                        <div>{!! e($q->explanation) !!}</div>
                    </div>
                    @endif
                </td>
                <td>
                    @if($q->subject)
                        <div class="subject-badge">🎯 {{ $q->subject->name }}</div><br>
                        <span class="subject-sub-badge">{{ $q->subject->code }}</span>
                    @elseif($q->subject_code)
                        <span class="subject-sub-badge">{{ $q->subject_code }}</span>
                    @else
                        <span style="color:#94a3b8;font-size:12px">সাধারণ / Unassigned</span>
                    @endif
                    <div style="margin-top:6px">
                        <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;background:{{ $q->difficulty === 'easy' ? '#dcfce7' : ($q->difficulty === 'medium' ? '#fef3c7' : '#fee2e2') }};color:{{ $q->difficulty === 'easy' ? '#166534' : ($q->difficulty === 'medium' ? '#92400e' : '#991b1b') }};text-transform:uppercase">
                            {{ $q->difficulty }}
                        </span>
                    </div>
                </td>
                <td>
                    <div class="options-grid">
                        @foreach($q->options ?? [] as $opt)
                        @php
                            $optId   = strtolower($opt['id'] ?? '');
                            $isRight = $optId === strtolower($q->correct_option_id);
                        @endphp
                        <div class="option-item {{ $isRight ? 'correct' : '' }}">
                            <strong>{{ strtoupper($optId) }}:</strong> {{ $opt['text'] ?? '' }}
                        </div>
                        @endforeach
                    </div>
                </td>
                <td style="text-align:center">
                    <div class="correct-badge">{{ strtoupper($q->correct_option_id) }}</div>
                </td>
                <td style="text-align:center">
                    <form method="POST" action="{{ route('admin.questions.destroy', $q) }}" onsubmit="return confirm('প্রশ্নটি নিশ্চিতভাবে মুছে ফেলতে চান?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-delete" title="Delete Question">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:#94a3b8">
                    <p style="font-size:15px;font-weight:600">কোনো প্রশ্ন পাওয়া যায়নি।</p>
                    <p style="font-size:12px;margin-top:4px">"+ নতুন প্রশ্ন তৈরি" অথবা "বাল্ক JSON আপলোড" বাটনে ক্লিক করে প্রশ্ন যোগ করুন।</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:20px">
        {{ $questions->links() }}
    </div>

    {{-- Create Single Question Modal --}}
    <div class="modal-overlay" id="createQuestionModal" style="display:none">
        <div class="modal" style="max-width:650px">
            <div class="modal-header">
                <span class="modal-title">+ নতুন MCQ প্রশ্ন যোগ করুন</span>
                <button class="modal-close" onclick="closeModal('createQuestionModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.questions.store') }}">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>বিষয় (Subject)</label>
                            <select name="subject_id" class="form-control">
                                <option value="">-- বিষয় নির্বাচন করুন --</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>কঠিনতার মাত্রা (Difficulty)</label>
                            <select name="difficulty" class="form-control" required>
                                <option value="easy">Easy (সহজ)</option>
                                <option value="medium">Medium (মধ্যম)</option>
                                <option value="hard">Hard (কঠিন)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>প্রশ্ন (Question Text) <span class="required">*</span></label>
                        <textarea name="question_text" class="form-control" rows="3" placeholder="যেমন: ব্যবস্থাপনার জনক (Father of Modern Management) কাকে বলা হয়?" required></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>অপশন A <span class="required">*</span></label>
                            <input type="text" name="option_a" class="form-control" required placeholder="হেনরি ফেওল">
                        </div>
                        <div class="form-group">
                            <label>অপশন B <span class="required">*</span></label>
                            <input type="text" name="option_b" class="form-control" required placeholder="এফ. ডব্লিউ. টেলর">
                        </div>
                        <div class="form-group">
                            <label>অপশন C <span class="required">*</span></label>
                            <input type="text" name="option_c" class="form-control" required placeholder="এলটন মেও">
                        </div>
                        <div class="form-group">
                            <label>অপশন D <span class="required">*</span></label>
                            <input type="text" name="option_d" class="form-control" required placeholder="পিটার এফ. ড্রাকার">
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

                    <div class="form-group">
                        <label>ব্যাখ্যা (Explanation)</label>
                        <textarea name="explanation" class="form-control" rows="3" placeholder="ফরাসি প্রকৌশলী হেনরি ফেওল ১৯১৬ সালে..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost-purple" onclick="closeModal('createQuestionModal')">বাতিল</button>
                    <button type="submit" class="btn-purple">💾 প্রশ্ন সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk JSON Upload Modal --}}
    <div class="modal-overlay" id="bulkUploadModal" style="display:none">
        <div class="modal" style="max-width:650px">
            <div class="modal-header">
                <span class="modal-title">📤 বাল্ক JSON আপলোড</span>
                <button class="modal-close" onclick="closeModal('bulkUploadModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.questions.bulk-upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>JSON ফাইল সিলেক্ট করুন (.json / .txt)</label>
                        <input type="file" name="json_file" class="form-control" accept=".json,.txt">
                    </div>

                    <div style="text-align:center;color:#94a3b8;font-size:12px;font-weight:700">— অথবা নিচে JSON কোড পেস্ট করুন —</div>

                    <div class="form-group">
                        <label>JSON Data Paste</label>
                        <textarea name="json_text" class="form-control" rows="8" style="font-family:monospace;font-size:12px" placeholder='[
  {
    "subject_id": "sub-hsc-business-org",
    "question_text": "ব্যবস্থাপনার জনক (Father of Modern Management) কাকে বলা হয়?",
    "options": [
      {"id": "a", "text": "হেনরি ফেওল (Henri Fayol)"},
      {"id": "b", "text": "এফ. ডব্লিউ. টেলর (F. W. Taylor)"},
      {"id": "c", "text": "এলটন মেও (Elton Mayo)"},
      {"id": "d", "text": "পিটার এফ. ড্রাকার (Peter Drucker)"}
    ],
    "correct_option_id": "a",
    "explanation": "ফরাসি প্রকৌশলী হেনরি ফেওল ১৯১৬ সালে...",
    "difficulty": "easy"
  }
]'></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ghost-purple" onclick="closeModal('bulkUploadModal')">বাতিল</button>
                    <button type="submit" class="btn-purple">⚡ আপলোড ও প্রসেস করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    </script>
</x-admin-layout>
