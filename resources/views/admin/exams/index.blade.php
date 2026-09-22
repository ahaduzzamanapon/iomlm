<x-admin-layout>
    <x-slot name="title">Exams & Results</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Exams & Evaluation Management</h1>
            <p>Schedule subject examinations and review student results</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addExamModal')">
                Schedule Exam
            </button>
        </div>
    </div>
    @php
        $currStatus  = strtoupper(request('status', ''));
        $currSubject = request('subject_id', '');
        $currSearch  = request('search', '');
    @endphp

    {{-- Filter Bar --}}
    <div class="card" style="margin-bottom:18px;padding:14px 18px;border:1px solid #e2e8f0;border-radius:10px;font-family:'Kalpurush',sans-serif">
        <form method="GET" action="{{ route('admin.exams.index') }}" id="examFilterForm">
            {{-- Row 1: Quick Status Badges / Pills --}}
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;border-bottom:1px solid #f1f5f9;padding-bottom:12px">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span style="font-size:12.5px;font-weight:700;color:#64748b;display:flex;align-items:center;gap:4px">
                        <i class="fa-solid fa-filter" style="color:#2563eb"></i> স্ট্যাটাস ফিল্টার:
                    </span>
                    @php
                        $statusTabs = [
                            ''          => ['label' => 'সকল পরীক্ষা', 'key' => 'ALL'],
                            'SCHEDULED' => ['label' => 'নির্ধারিত (Scheduled)', 'key' => 'SCHEDULED'],
                            'RUNNING'   => ['label' => 'চলমান (Running)', 'key' => 'RUNNING'],
                            'COMPLETED' => ['label' => 'সম্পন্ন (Completed)', 'key' => 'COMPLETED'],
                            'CANCELLED' => ['label' => 'বাতিল (Cancelled)', 'key' => 'CANCELLED'],
                        ];
                    @endphp

                    @foreach($statusTabs as $sVal => $sMeta)
                        @php
                            $isActive = ($currStatus === $sVal) || ($sVal === '' && empty($currStatus));
                            $count = $statusCounts[$sMeta['key']] ?? 0;
                        @endphp
                        <a href="{{ route('admin.exams.index', array_merge(request()->except('status'), $sVal ? ['status' => $sVal] : [])) }}"
                           style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;text-decoration:none;transition:all 0.15s;
                                  {{ $isActive 
                                      ? 'background:#2563eb;color:#ffffff;box-shadow:0 2px 6px rgba(37,99,235,0.25);border:1px solid #2563eb;' 
                                      : 'background:#ffffff;color:#475569;border:1px solid #cbd5e1;' }}">
                            <span>{{ $sMeta['label'] }}</span>
                            <span style="font-size:11px;padding:1px 6px;border-radius:10px;
                                  {{ $isActive ? 'background:rgba(255,255,255,0.25);color:#fff;' : 'background:#f1f5f9;color:#64748b;' }}">
                                {{ $count }}
                            </span>
                        </a>
                    @endforeach
                </div>

                @if($currStatus || $currSubject || $currSearch)
                    <a href="{{ route('admin.exams.index') }}" 
                       style="font-size:12px;color:#ef4444;text-decoration:none;font-weight:700;display:inline-flex;align-items:center;gap:4px">
                        <i class="fa-solid fa-rotate-left"></i> ফিল্টার রিসেট করুন
                    </a>
                @endif
            </div>

            {{-- Row 2: Dropdowns & Search Filter Controls --}}
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                {{-- Status Select --}}
                <div style="min-width:180px">
                    <select name="status" class="form-control" onchange="this.form.submit()"
                            style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;background:#fff;padding:0 10px;cursor:pointer">
                        <option value="">-- সকল স্ট্যাটাস (All Status) --</option>
                        <option value="SCHEDULED" {{ $currStatus === 'SCHEDULED' ? 'selected' : '' }}>● Scheduled (নির্ধারিত)</option>
                        <option value="RUNNING" {{ $currStatus === 'RUNNING' ? 'selected' : '' }}>● Running (চলমান)</option>
                        <option value="COMPLETED" {{ $currStatus === 'COMPLETED' ? 'selected' : '' }}>● Completed (সম্পন্ন)</option>
                        <option value="CANCELLED" {{ $currStatus === 'CANCELLED' ? 'selected' : '' }}>● Cancelled (বাতিল)</option>
                    </select>
                </div>

                {{-- Subject Select --}}
                <div style="min-width:200px">
                    <select name="subject_id" class="form-control" onchange="this.form.submit()"
                            style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;background:#fff;padding:0 10px;cursor:pointer">
                        <option value="">-- সকল বিষয় (All Subjects) --</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ $currSubject == $s->id ? 'selected' : '' }}>
                                {{ $s->code ? "[{$s->code}] " : '' }}{{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Input --}}
                <div style="flex:1;min-width:220px;position:relative">
                    <input type="text" name="search" value="{{ $currSearch }}" class="form-control"
                           placeholder="পরীক্ষার নাম বা কোড দিয়ে খুঁজুন..."
                           style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;padding-left:34px;padding-right:12px;width:100%">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:11px;top:12px;color:#94a3b8;font-size:13px"></i>
                </div>

                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 18px;border-radius:8px;font-size:13px;font-weight:700">
                    <i class="fa-solid fa-search"></i> খুঁজুন
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Title</th>
                        <th>Type</th>
                        <th>Date & Duration</th>
                        <th>Marks (Full/Pass)</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                    <tr>
                        <td class="td-primary"><strong>{{ $exam->subject->name ?? '—' }}</strong></td>
                        <td>{{ $exam->title }}</td>
                        <td><span class="badge badge-secondary no-dot">{{ $exam->type }}</span></td>
                        <td class="td-muted">
                            <div><i class="fa-solid fa-calendar-day"></i> {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                                @if($exam->end_date && $exam->end_date != $exam->exam_date)
                                    - {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}
                                @endif
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                @if($exam->start_time)
                                    <i class="fa-regular fa-clock"></i> {{ date('h:i A', strtotime($exam->start_time)) }}
                                    @if($exam->end_time) - {{ date('h:i A', strtotime($exam->end_time)) }} @endif
                                    &middot;
                                @endif
                                ⏱️ {{ $exam->duration_minutes ?? 90 }} মি.
                            </div>
                        </td>
                        <td>{{ $exam->full_marks }} / {{ $exam->pass_marks }}</td>
                        <td><span class="badge badge-{{ strtolower($exam->status) }}">{{ ucfirst(strtolower($exam->status)) }}</span></td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <a href="{{ route('admin.exams.builder', $exam) }}" class="btn btn-outline btn-sm" style="color:#4f46e5;border-color:#c7d2fe;font-weight:600">
                                    <i class="fa-solid fa-puzzle-piece"></i> Paper Builder
                                </a>
                                <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-outline btn-sm">Inspect Exam</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px 20px;color:var(--text-muted);font-family:'Kalpurush',sans-serif">
                            <i class="fa-solid fa-file-circle-question" style="font-size:32px;color:#cbd5e1;margin-bottom:10px;display:block"></i>
                            <strong>কোনো পরীক্ষা পাওয়া যায়নি।</strong>
                            @if(request('status') || request('subject_id') || request('search'))
                                <div style="margin-top:6px;font-size:12.5px">
                                    বর্তমান ফিল্টার অনুযায়ী কোনো পরীক্ষার রেকর্ড নেই। <a href="{{ route('admin.exams.index') }}" style="color:#2563eb;font-weight:700">ফিল্টার রিসেট করুন</a>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Exam Modal -->
    <div class="modal-overlay" id="addExamModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Schedule New Exam</span>
                <button class="modal-close" onclick="closeModal('addExamModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.exams.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Subject <span class="required">*</span></label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">-- Choose Subject --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->code }}: {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Exam Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Midterm Examination 2026" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Exam Type <span class="required">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="FINAL">FINAL</option>
                                <option value="MIDTERM">MIDTERM</option>
                                <option value="RETAKE">RETAKE</option>
                                <option value="QUIZ">QUIZ</option>
                                <option value="PRACTICAL">PRACTICAL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>শুরুর তারিখ (Start Date) <span class="required">*</span></label>
                            <input type="date" name="exam_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>শেষের তারিখ (End Date)</label>
                            <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>সময়কাল (মিনিট) <span class="required">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control" value="60" min="5" max="360" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>শুরুর সময় (Start Time)</label>
                            <input type="time" name="start_time" class="form-control" value="10:00">
                        </div>
                        <div class="form-group">
                            <label>শেষের সময় (End Time)</label>
                            <input type="time" name="end_time" class="form-control" value="12:00">
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

                    <div class="form-group">
                        <label>নেগেটিভ মার্কিং (MCQ প্রতি ভুল উত্তরের জন্য কর্তন)</label>
                        <input type="number" step="0.25" name="negative_marking" class="form-control" value="0.00" min="0" max="5">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addExamModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Exam</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
