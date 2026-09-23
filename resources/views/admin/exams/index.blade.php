<x-admin-layout>
    <x-slot name="title">Exams & Results</x-slot>

    <style>
        .dropdown { position: relative; display: inline-block; }
        .dropdown-menu {
            position: absolute;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            min-width: 190px;
            z-index: 9999;
            display: none;
            overflow: hidden;
            padding: 6px 0;
            text-align: left;
        }
        .dropdown-menu.open { display: block !important; }
        .table-wrapper:has(.dropdown-menu.open),
        .card:has(.dropdown-menu.open),
        td:has(.dropdown-menu.open) {
            overflow: visible !important;
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            font-size: 13px;
            color: #1e293b;
            text-decoration: none;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.15s;
            font-family: 'Kalpurush', sans-serif;
            font-weight: 600;
        }
        .dropdown-item:hover {
            background: #f8fafc;
            color: #0f172a;
        }
        .dropdown-item.danger {
            color: #dc2626;
        }
        .dropdown-item.danger:hover {
            background: #fef2f2;
            color: #b91c1c;
        }
        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 4px 0;
        }
    </style>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Exams & Evaluation Management</h1>
            <p>Schedule subject examinations and review student results</p>
        </div>
        <div class="page-header-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap">
            <a href="{{ route('admin.result-book.index') }}" class="btn" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; font-weight:800; display:inline-flex; align-items:center; gap:7px; border-radius:8px; padding:8px 16px; text-decoration:none; font-size:13.5px">
                <i class="fa-solid fa-book-bookmark"></i> রেজাল্ট বুক (Result Book)
            </a>
            <button class="btn btn-primary" onclick="openModal('addExamModal')">
                <i class="fa-solid fa-plus"></i> Schedule Exam
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
                {{-- Batch Select --}}
                <div style="min-width:170px">
                    <select name="batch_id" id="exam_filter_batch" class="form-control" onchange="this.form.submit()"
                            style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;background:#fff;padding:0 10px;cursor:pointer">
                        <option value="">-- সকল ব্যাচ (All Batches) --</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" data-course-id="{{ $b->course_id }}" {{ ($batchId ?? '') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->course->name ?? 'কোর্স' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Semester Select --}}
                <div style="min-width:170px">
                    <select name="semester_id" id="exam_filter_semester" class="form-control" onchange="this.form.submit()"
                            style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;background:#fff;padding:0 10px;cursor:pointer">
                        <option value="">-- সকল সেমিস্টার (All Semesters) --</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" data-course-id="{{ $sem->course_id }}" data-course-name="{{ $sem->course->name ?? '' }}" data-name="{{ $sem->name }}" {{ ($semesterId ?? '') == $sem->id ? 'selected' : '' }}>
                                {{ $sem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Exam Type Select --}}
                <div style="min-width:160px">
                    <select name="type" class="form-control" onchange="this.form.submit()"
                            style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;background:#fff;padding:0 10px;cursor:pointer">
                        <option value="">-- পরীক্ষার ধরন (All Types) --</option>
                        <option value="FINAL" {{ strtoupper($examType ?? '') === 'FINAL' ? 'selected' : '' }}>🏆 Final (ফাইনাল)</option>
                        <option value="MIDTERM" {{ strtoupper($examType ?? '') === 'MIDTERM' ? 'selected' : '' }}>📝 Midterm (মিডটার্ম)</option>
                        <option value="QUIZ" {{ strtoupper($examType ?? '') === 'QUIZ' ? 'selected' : '' }}>⚡ Class Test / Quiz (সিটি)</option>
                        <option value="RETAKE" {{ strtoupper($examType ?? '') === 'RETAKE' ? 'selected' : '' }}>🔄 Retake (রিটেক)</option>
                        <option value="PRACTICAL" {{ strtoupper($examType ?? '') === 'PRACTICAL' ? 'selected' : '' }}>🔬 Practical (ব্যবহারিক)</option>
                    </select>
                </div>

                {{-- Subject Select --}}
                <div style="min-width:180px">
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

                {{-- Status Select --}}
                <div style="min-width:150px">
                    <select name="status" class="form-control" onchange="this.form.submit()"
                            style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;background:#fff;padding:0 10px;cursor:pointer">
                        <option value="">-- স্ট্যাটাস --</option>
                        <option value="SCHEDULED" {{ $currStatus === 'SCHEDULED' ? 'selected' : '' }}>● Scheduled</option>
                        <option value="RUNNING" {{ $currStatus === 'RUNNING' ? 'selected' : '' }}>● Running</option>
                        <option value="COMPLETED" {{ $currStatus === 'COMPLETED' ? 'selected' : '' }}>● Completed</option>
                        <option value="CANCELLED" {{ $currStatus === 'CANCELLED' ? 'selected' : '' }}>● Cancelled</option>
                    </select>
                </div>

                {{-- Search Input --}}
                <div style="flex:1;min-width:190px;position:relative">
                    <input type="text" name="search" value="{{ $currSearch }}" class="form-control"
                           placeholder="পরীক্ষার নাম দিয়ে খুঁজুন..."
                           style="height:38px;border-radius:8px;font-size:13px;border:1px solid #cbd5e1;padding-left:34px;padding-right:12px;width:100%">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:11px;top:12px;color:#94a3b8;font-size:13px"></i>
                </div>

                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 16px;border-radius:8px;font-size:13px;font-weight:700">
                    <i class="fa-solid fa-filter"></i> ফিল্টার
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
                        <td class="td-primary">
                            <strong>{{ $exam->subject->name ?? '—' }}</strong>
                            <div style="font-size:11px;color:#64748b">{{ $exam->subject->code ?? '' }}</div>
                        </td>
                        <td>
                            <div style="font-weight:700;color:#0f172a;margin-bottom:4px">{{ $exam->title }}</div>
                            <div style="display:flex;gap:4px;flex-wrap:wrap">
                                @if($exam->has_mcq)
                                    <span class="badge badge-primary no-dot" style="font-size:10px;padding:2px 7px">MCQ: {{ (int)$exam->mcq_marks }}</span>
                                @endif
                                @if($exam->has_written)
                                    <span class="badge badge-danger no-dot" style="font-size:10px;padding:2px 7px">লিখিত: {{ (int)$exam->written_marks }}</span>
                                @endif
                                @if($exam->has_tamrin)
                                    <span class="badge badge-warning no-dot" style="font-size:10px;padding:2px 7px">তামরিন: {{ (int)$exam->tamrin_marks }}</span>
                                @endif
                                @if($exam->has_viva)
                                    <span class="badge badge-success no-dot" style="font-size:10px;padding:2px 7px">ভাইভা: {{ (int)$exam->viva_marks }}</span>
                                @endif
                            </div>
                        </td>
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
                        <td>
                            <strong style="font-size:14px;color:#0f172a">{{ $exam->full_marks }}</strong>
                            <span style="font-size:11px;color:#64748b">/ পাস: {{ $exam->pass_marks }}</span>
                        </td>
                        <td><span class="badge badge-{{ strtolower($exam->status) }}">{{ ucfirst(strtolower($exam->status)) }}</span></td>
                        <td style="text-align:right; white-space:nowrap">
                            <a href="{{ route('admin.exams.show', $exam) }}" class="btn btn-sm" style="background:#2563eb; color:#fff; font-weight:800; border-radius:8px; padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-right:6px; box-shadow:0 1px 3px rgba(37,99,235,0.25)">
                                <i class="fa-solid fa-trophy" style="color:#fef08a"></i> মেধা তালিকা ও মার্কশীট
                            </a>
                            <div class="dropdown" style="display:inline-block;position:relative">
                                <button type="button" class="btn btn-outline btn-sm" onclick="toggleDropdown('eact-{{ $exam->id }}')" style="gap:6px;display:inline-flex;align-items:center;font-family:'Kalpurush',sans-serif;font-weight:700;padding:5px 12px">
                                    <i class="fa-solid fa-ellipsis-vertical" style="font-size:12px"></i>
                                    অ্যাকশন
                                    <i class="fa-solid fa-chevron-down" style="font-size:9px"></i>
                                </button>
                                <div class="dropdown-menu" id="eact-{{ $exam->id }}" style="right:0;min-width:210px">
                                    <a href="{{ route('admin.exams.show', $exam) }}" class="dropdown-item" style="font-weight:800; color:#1e40af; background:#eff6ff">
                                        <i class="fa-solid fa-trophy" style="color:#eab308;width:16px"></i>
                                        মেধা তালিকা ও মার্কশীট (Merit)
                                    </a>
                                    <a href="{{ route('admin.exams.builder', $exam) }}" class="dropdown-item">
                                        <i class="fa-solid fa-puzzle-piece" style="color:#4f46e5;width:16px"></i>
                                        Paper Builder (প্রশ্নপত্র)
                                    </a>
                                    <button type="button" class="dropdown-item" onclick='openEditExamModal(@json($exam));toggleDropdown("eact-{{ $exam->id }}")'>
                                        <i class="fa-solid fa-pen-to-square" style="color:#0284c7;width:16px"></i>
                                        কাঠামো এডিট (Edit Setup)
                                    </button>
                                    <a href="{{ route('admin.exams.test-exam', $exam) }}" class="dropdown-item" target="_blank">
                                        <i class="fa-solid fa-vial-circle-check" style="color:#f59e0b;width:16px"></i>
                                        Test Exam (টেস্ট পরীক্ষা)
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই পরীক্ষাটি মুছে ফেলতে চান?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item danger">
                                            <i class="fa-solid fa-trash" style="color:#dc2626;width:16px"></i>
                                            মুছে ফেলুন (Delete)
                                        </button>
                                    </form>
                                </div>
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

                    {{-- 📋 পরীক্ষার মূল্যায়ন কাঠামো নির্বাচন (Assessment Structure) --}}
                    <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px; margin-bottom:18px; font-family:'Kalpurush',sans-serif">
                        <div style="font-weight:700; font-size:13.5px; color:#1e293b; margin-bottom:4px; display:flex; align-items:center; gap:8px">
                            <i class="fa-solid fa-clipboard-list" style="color:#2563eb"></i>
                            <span>পরীক্ষার মূল্যায়ন কাঠামো নির্বাচন (Assessment Structure)</span>
                        </div>
                        <div style="font-size:11.5px; color:#64748b; margin-bottom:10px">
                            প্রতিটি উপাদানের জন্য স্বাধীন চেকবক্স ও পূর্ণমান ইনপুট (টিক দেওয়া উপাদানগুলোর মোট যোগফল নিচে স্বয়ংক্রিয়ভাবে ফুল মার্কস হবে):
                        </div>

                        <div style="border:1px solid #cbd5e1; border-radius:8px; overflow:hidden">
                            <table style="width:100%; border-collapse:collapse; font-size:12px; background:#fff">
                                <thead>
                                    <tr style="background:#f1f5f9; border-bottom:1px solid #cbd5e1">
                                        <th style="padding:7px 10px; text-align:left; font-weight:700; color:#334155">কম্পোনেন্ট</th>
                                        <th style="padding:7px 10px; text-align:center; width:80px; font-weight:700; color:#334155">সক্রিয়?</th>
                                        <th style="padding:7px 10px; text-align:center; width:100px; font-weight:700; color:#334155">পূর্ণমান</th>
                                        <th style="padding:7px 10px; text-align:left; font-weight:700; color:#334155">কাজের ধরন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom:1px solid #f1f5f9">
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-list-check" style="color:#4f46e5; margin-right:4px"></i> MCQ (বহুনির্বাচনী)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_mcq" id="add_has_mcq" value="1" checked onchange="calcAssessmentMarks('add')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="mcq_marks" id="add_mcq_marks" value="40" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('add')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">অনলাইন টাইমারযুক্ত এমসিকিউ পরীক্ষা</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #f1f5f9">
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-pen-nib" style="color:#db2777; margin-right:4px"></i> লিখিত (Written)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_written" id="add_has_written" value="1" checked onchange="calcAssessmentMarks('add')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="written_marks" id="add_written_marks" value="40" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('add')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">খাতায় লিখে ছবি আপলোড বা টেক্সট উত্তর</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #f1f5f9">
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-hand-holding-hand" style="color:#d97706; margin-right:4px"></i> তামরিন (হাতের কাজ)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_tamrin" id="add_has_tamrin" value="1" checked onchange="calcAssessmentMarks('add')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="tamrin_marks" id="add_tamrin_marks" value="10" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('add')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">হোমওয়ার্ক/অ্যাসাইনমেন্ট/হাতে লেখার মার্ক</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-microphone" style="color:#059669; margin-right:4px"></i> ভাইভা (মৌখিক)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_viva" id="add_has_viva" value="1" checked onchange="calcAssessmentMarks('add')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="viva_marks" id="add_viva_marks" value="10" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('add')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">মৌখিক পরীক্ষা/তেলাওয়াত শুনে মূল্যায়ন</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>মোট পূর্ণমান (Full Marks) <span class="required">*</span></label>
                            <input type="number" name="full_marks" id="add_full_marks" class="form-control" value="100" required>
                            <span style="font-size:11px;color:#059669;margin-top:2px;display:block">💡 সক্রিয় উপাদানগুলোর মোট নম্বর স্বয়ংক্রিয়ভাবে হিসাব হবে</span>
                        </div>
                        <div class="form-group">
                            <label>Pass Marks <span class="required">*</span></label>
                            <input type="number" name="pass_marks" id="add_pass_marks" class="form-control" value="40" required>
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

    <!-- Edit Exam Assessment Structure Modal -->
    <div class="modal-overlay" id="editExamModal">
        <div class="modal" style="max-width:650px">
            <div class="modal-header">
                <span class="modal-title" id="editModalTitle">মূল্যায়ন কাঠামো ও তথ্য পরিবর্তন</span>
                <button class="modal-close" onclick="closeModal('editExamModal')">&times;</button>
            </div>
            <form method="POST" id="editExamForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:13px; color:#1e40af">
                        বিষয়: <strong id="editExamSubjectName">—</strong> &middot; ধরন: <span id="editExamTypeBadge" class="badge badge-secondary no-dot">MIDTERM</span>
                    </div>

                    <div class="form-group">
                        <label>Exam Title <span class="required">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>

                    {{-- 📋 পরীক্ষার মূল্যায়ন কাঠামো নির্বাচন (Assessment Structure) --}}
                    <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px; margin-bottom:18px; font-family:'Kalpurush',sans-serif">
                        <div style="font-weight:700; font-size:13.5px; color:#1e293b; margin-bottom:4px; display:flex; align-items:center; gap:8px">
                            <i class="fa-solid fa-clipboard-list" style="color:#2563eb"></i>
                            <span>পরীক্ষার মূল্যায়ন কাঠামো নির্বাচন (Assessment Structure)</span>
                        </div>
                        <div style="font-size:11.5px; color:#64748b; margin-bottom:10px">
                            প্রতিটি উপাদানের জন্য স্বাধীন চেকবক্স ও পূর্ণমান নির্ধারণ করুন:
                        </div>

                        <div style="border:1px solid #cbd5e1; border-radius:8px; overflow:hidden">
                            <table style="width:100%; border-collapse:collapse; font-size:12px; background:#fff">
                                <thead>
                                    <tr style="background:#f1f5f9; border-bottom:1px solid #cbd5e1">
                                        <th style="padding:7px 10px; text-align:left; font-weight:700; color:#334155">কম্পোনেন্ট</th>
                                        <th style="padding:7px 10px; text-align:center; width:80px; font-weight:700; color:#334155">সক্রিয়?</th>
                                        <th style="padding:7px 10px; text-align:center; width:100px; font-weight:700; color:#334155">পূর্ণমান</th>
                                        <th style="padding:7px 10px; text-align:left; font-weight:700; color:#334155">কাজের ধরন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom:1px solid #f1f5f9">
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-list-check" style="color:#4f46e5; margin-right:4px"></i> MCQ (বহুনির্বাচনী)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_mcq" id="edit_has_mcq" value="1" onchange="calcAssessmentMarks('edit')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="mcq_marks" id="edit_mcq_marks" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('edit')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">অনলাইন টাইমারযুক্ত এমসিকিউ পরীক্ষা</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #f1f5f9">
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-pen-nib" style="color:#db2777; margin-right:4px"></i> লিখিত (Written)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_written" id="edit_has_written" value="1" onchange="calcAssessmentMarks('edit')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="written_marks" id="edit_written_marks" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('edit')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">খাতায় লিখে ছবি আপলোড বা টেক্সট উত্তর</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #f1f5f9">
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-hand-holding-hand" style="color:#d97706; margin-right:4px"></i> তামরিন (হাতের কাজ)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_tamrin" id="edit_has_tamrin" value="1" onchange="calcAssessmentMarks('edit')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="tamrin_marks" id="edit_tamrin_marks" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('edit')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">হোমওয়ার্ক/অ্যাসাইনমেন্ট/হাতে লেখার মার্ক</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-weight:600; color:#1e293b">
                                            <i class="fa-solid fa-microphone" style="color:#059669; margin-right:4px"></i> ভাইভা (মৌখিক)
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="checkbox" name="has_viva" id="edit_has_viva" value="1" onchange="calcAssessmentMarks('edit')" style="width:16px;height:16px;cursor:pointer;accent-color:#2563eb">
                                        </td>
                                        <td style="padding:7px 10px; text-align:center">
                                            <input type="number" step="1" name="viva_marks" id="edit_viva_marks" class="form-control" style="height:30px; font-size:12px; text-align:center; font-weight:700" oninput="calcAssessmentMarks('edit')">
                                        </td>
                                        <td style="padding:7px 10px; color:#64748b; font-size:11px">মৌখিক পরীক্ষা/তেলাওয়াত শুনে মূল্যায়ন</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>মোট পূর্ণমান (Full Marks) <span class="required">*</span></label>
                            <input type="number" name="full_marks" id="edit_full_marks" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>পাস মার্কস (Pass Marks) <span class="required">*</span></label>
                            <input type="number" name="pass_marks" id="edit_pass_marks" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>স্ট্যাটাস (Status)</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="SCHEDULED">SCHEDULED</option>
                            <option value="RUNNING">RUNNING</option>
                            <option value="COMPLETED">COMPLETED</option>
                            <option value="CANCELLED">CANCELLED</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editExamModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">পরিবর্তন সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function calcAssessmentMarks(prefix) {
        const hasMcq = document.getElementById(prefix + '_has_mcq')?.checked ?? false;
        const hasWritten = document.getElementById(prefix + '_has_written')?.checked ?? false;
        const hasTamrin = document.getElementById(prefix + '_has_tamrin')?.checked ?? false;
        const hasViva = document.getElementById(prefix + '_has_viva')?.checked ?? false;

        const mcqInput = document.getElementById(prefix + '_mcq_marks');
        const writtenInput = document.getElementById(prefix + '_written_marks');
        const tamrinInput = document.getElementById(prefix + '_tamrin_marks');
        const vivaInput = document.getElementById(prefix + '_viva_marks');

        if (mcqInput) mcqInput.disabled = !hasMcq;
        if (writtenInput) writtenInput.disabled = !hasWritten;
        if (tamrinInput) tamrinInput.disabled = !hasTamrin;
        if (vivaInput) vivaInput.disabled = !hasViva;

        let total = 0;
        if (hasMcq) total += parseFloat(mcqInput.value || 0);
        if (hasWritten) total += parseFloat(writtenInput.value || 0);
        if (hasTamrin) total += parseFloat(tamrinInput.value || 0);
        if (hasViva) total += parseFloat(vivaInput.value || 0);

        const fullMarksInput = document.getElementById(prefix + '_full_marks');
        if (fullMarksInput && total > 0) {
            fullMarksInput.value = Math.round(total);
        }
    }

    function openEditExamModal(exam) {
        document.getElementById('editExamForm').action = "{{ url('admin/exams') }}/" + exam.id;
        document.getElementById('edit_title').value = exam.title || '';
        document.getElementById('editExamSubjectName').textContent = exam.subject ? (exam.subject.code + ': ' + exam.subject.name) : '—';
        document.getElementById('editExamTypeBadge').textContent = exam.type || '';

        document.getElementById('edit_has_mcq').checked = !!exam.has_mcq;
        document.getElementById('edit_mcq_marks').value = exam.mcq_marks ?? 0;

        document.getElementById('edit_has_written').checked = !!exam.has_written;
        document.getElementById('edit_written_marks').value = exam.written_marks ?? 0;

        document.getElementById('edit_has_tamrin').checked = !!exam.has_tamrin;
        document.getElementById('edit_tamrin_marks').value = exam.tamrin_marks ?? 0;

        document.getElementById('edit_has_viva').checked = !!exam.has_viva;
        document.getElementById('edit_viva_marks').value = exam.viva_marks ?? 0;

        document.getElementById('edit_full_marks').value = exam.full_marks || 100;
        document.getElementById('edit_pass_marks').value = exam.pass_marks || 40;
        document.getElementById('edit_status').value = exam.status || 'SCHEDULED';

        calcAssessmentMarks('edit');
        openModal('editExamModal');
    }

    function setupBatchSemesterCascading(batchSelectId, semesterSelectId, defaultSemText) {
        const batchSelect = typeof batchSelectId === 'string' ? document.getElementById(batchSelectId) : batchSelectId;
        const semSelect = typeof semesterSelectId === 'string' ? document.getElementById(semesterSelectId) : semesterSelectId;
        if (!batchSelect || !semSelect) return;

        const allSemesterData = [];
        Array.from(semSelect.options).forEach(opt => {
            if (!opt.value) return;
            allSemesterData.push({
                value: opt.value,
                courseId: String(opt.getAttribute('data-course-id') || ''),
                courseName: opt.getAttribute('data-course-name') || '',
                name: opt.getAttribute('data-name') || opt.text
            });
        });

        function update() {
            const selectedOption = batchSelect.options[batchSelect.selectedIndex];
            const selectedCourseId = selectedOption ? String(selectedOption.getAttribute('data-course-id') || '') : '';
            const currentSemVal = String(semSelect.value || '');

            semSelect.innerHTML = '';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = defaultSemText || '-- সকল সেমিস্টার (All Semesters) --';
            semSelect.appendChild(defaultOpt);

            if (selectedCourseId) {
                const filtered = allSemesterData.filter(s => s.courseId === selectedCourseId);
                filtered.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.value;
                    opt.textContent = s.name;
                    opt.setAttribute('data-course-id', s.courseId);
                    opt.setAttribute('data-name', s.name);
                    if (currentSemVal === String(s.value)) {
                        opt.selected = true;
                    }
                    semSelect.appendChild(opt);
                });
            } else {
                allSemesterData.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.value;
                    opt.textContent = s.name + (s.courseName ? ` (${s.courseName})` : '');
                    opt.setAttribute('data-course-id', s.courseId);
                    opt.setAttribute('data-name', s.name);
                    if (currentSemVal === String(s.value)) {
                        opt.selected = true;
                    }
                    semSelect.appendChild(opt);
                });
            }
        }

        batchSelect.addEventListener('change', update);
        update();
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupBatchSemesterCascading('exam_filter_batch', 'exam_filter_semester', '-- সকল সেমিস্টার (All Semesters) --');
    });
    </script>
    @endpush
</x-admin-layout>
