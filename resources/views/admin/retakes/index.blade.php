<x-admin-layout>
    <x-slot name="title">Subject Retakes</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Subject Retake Engine</h1>
            <p>Register retakes for failed subjects. Admin sets the Retake Fee upon approval.</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addRetakeModal')">
                New Subject Retake
            </button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Retake Type</th>
                        <th>Retake Fee</th>
                        <th>Registered Date</th>
                        <th>Status</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($retakes as $ret)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $ret->student->name ?? '—' }}</strong><br>
                            <span class="td-muted">{{ $ret->student->student_code ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $ret->subject->name ?? '—' }}</td>
                        <td>
                            @if($ret->retake_type === 'EXAM_ONLY')
                                <span class="badge badge-secondary no-dot">Exam Only</span>
                            @elseif($ret->retake_type === 'CLASS_EXAM')
                                <span class="badge badge-scheduled no-dot">Class + Exam</span>
                            @else
                                <span class="badge badge-cancelled no-dot">Full Subject Restart</span>
                            @endif
                        </td>
                        <td>
                            @if($ret->retake_fee)
                                <strong>৳{{ number_format($ret->retake_fee, 0) }}</strong>
                            @else
                                <span class="td-muted">Pending</span>
                            @endif
                        </td>
                        <td class="td-muted">{{ $ret->created_at->format('d M Y') }}</td>
                        <td><span class="badge badge-{{ strtolower($ret->status) }}">{{ ucfirst(strtolower($ret->status)) }}</span></td>
                        <td style="text-align:right">
                            @if($ret->status === 'PENDING')
                                <button class="btn btn-success btn-sm"
                                    onclick="openApproveRetake({{ $ret->id }}, @json($ret->student->name ?? ''), @json($ret->subject->name ?? ''))">
                                    Approve & Set Fee
                                </button>
                            @else
                                <span class="td-muted" style="font-size:12px">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">No subject retakes registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Retake Modal -->
    <div class="modal-overlay" id="addRetakeModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Register Subject Retake</span>
                <button class="modal-close" onclick="closeModal('addRetakeModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.retakes.store') }}">
                @csrf
                <div class="modal-body" style="font-family: 'Kalpurush', sans-serif;">
                    <!-- Student Live Search -->
                    <div class="form-group" style="position:relative;">
                        <label style="font-weight:600;display:flex;justify-content:space-between;">
                            <span>শিক্ষার্থী নির্বাচন (Student Roll / Name) <span class="required">*</span></span>
                            <span id="search_spinner" style="display:none;font-size:12px;color:var(--primary)">অনুসন্ধান হচ্ছে...</span>
                        </label>
                        <input type="text" id="retake_student_search_input" class="form-control" 
                               placeholder="রোল নম্বর (যেমন: 20240101 বা 2024-01-01), নাম বা ফোন লিখে খুঁজুন..." 
                               autocomplete="off">
                        <input type="hidden" name="student_id" id="retake_student_id" required>

                        <!-- Search Dropdown Results -->
                        <div id="retake_student_results" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:1050;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);max-height:220px;overflow-y:auto;margin-top:4px;">
                        </div>

                        <!-- Selected Student Card -->
                        <div id="retake_selected_student_card" style="display:none;margin-top:8px;padding:10px 12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <span id="selected_st_badge" style="background:#16a34a;color:#fff;font-size:11px;padding:2px 6px;border-radius:4px;font-weight:bold;"></span>
                                    <strong id="selected_st_name" style="margin-left:6px;color:#166534;font-size:14px;"></strong>
                                    <div style="font-size:12px;color:#374151;margin-top:2px;" id="selected_st_meta"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline" onclick="clearSelectedRetakeStudent()" style="padding:2px 8px;font-size:11px;color:#dc2626;border-color:#fca5a5;">✕ মুছুন</button>
                            </div>
                        </div>
                    </div>

                    <!-- Failed Subjects Quick Picker (if applicable) -->
                    <div id="failed_subjects_container" style="display:none;margin-bottom:12px;padding:8px 10px;background:#fef2f2;border:1px dashed #fca5a5;border-radius:6px;">
                        <label style="font-size:12px;font-weight:600;color:#991b1b;margin-bottom:4px;display:block;">⚠️ শিক্ষার্থীর ফেল করা বিষয়সমূহ (ক্লিক করে নির্বাচন করুন):</label>
                        <div id="failed_subjects_list" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
                    </div>

                    <div class="form-group">
                        <label>বিষয় নির্বাচন (Select Subject) <span class="required">*</span></label>
                        <select name="subject_id" id="retake_subject_id" class="form-control" required>
                            <option value="">-- বিষয় নির্বাচন করুন --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->code }}: {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>রিটেক ধরন (Retake Mode) <span class="required">*</span></label>
                        <select name="retake_type" class="form-control" required>
                            <option value="EXAM_ONLY">EXAM_ONLY — শুধুমাত্র পরীক্ষায় অংশগ্রহণ (ক্লাস ছাড়া)</option>
                            <option value="CLASS_EXAM">CLASS_EXAM — পুনরায় ক্লাস + পরীক্ষায় অংশগ্রহণ</option>
                            <option value="FULL_RESTART">FULL_RESTART — সম্পূর্ণ নতুনভাবে মডিউল পুনরাবৃত্তি</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>নোট / মন্তব্য (Notes)</label>
                        <textarea name="notes" class="form-control" placeholder="রিটেক সংক্রান্ত অ্যাডমিন নোট বা কারণ লিখুন..."></textarea>
                    </div>
                    <div class="alert alert-info" style="margin-top:8px;font-size:12px;">
                        ℹ️ রিটেক ফি অ্যাডমিন কর্তৃক অনুমোদনের সময় নির্ধারণ করা হবে এবং শিক্ষার্থীর একাউন্টে ইনভয়েস তৈরি হবে।
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addRetakeModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary">রিটেক রেজিস্ট্রেশন করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approve Retake Modal -->
    <div class="modal-overlay" id="approveRetakeModal">
        <div class="modal" style="max-width:450px;font-family: 'Kalpurush', sans-serif;">
            <div class="modal-header">
                <span class="modal-title">রিটেক অনুমোদন ও ফি নির্ধারণ</span>
                <button class="modal-close" onclick="closeModal('approveRetakeModal')">&times;</button>
            </div>
            <form method="POST" id="approveRetakeForm">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div style="background:#f8fafc;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px">
                        <strong id="ar_student"></strong> — <span id="ar_subject" style="color:var(--text-muted)"></span>
                    </div>
                    <div class="form-group">
                        <label>রিটেক ফি (৳ টাকা) <span class="required">*</span></label>
                        <input type="number" name="retake_fee" class="form-control" min="0" placeholder="যেমন: ১৫০০" required>
                        <small style="color:var(--text-muted);font-size:12px">এই পরিমাণ টাকা শিক্ষার্থীর একাউন্টে ইনভয়েস হিসেবে যুক্ত হবে।</small>
                    </div>
                    <div class="form-group">
                        <label>নোট (ঐচ্ছিক)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="অনুমোদন সংক্রান্ত নোট..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('approveRetakeModal')">বাতিল</button>
                    <button type="submit" class="btn btn-success">অনুমোদন ও ইনভয়েস তৈরি করুন</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openApproveRetake(id, student, subject) {
        document.getElementById('approveRetakeForm').action = '/admin/retakes/' + id + '/approve';
        document.getElementById('ar_student').textContent = student;
        document.getElementById('ar_subject').textContent = subject;
        openModal('approveRetakeModal');
    }

    // Retake student live search functionality
    (function() {
        const searchInput = document.getElementById('retake_student_search_input');
        const resultsDiv = document.getElementById('retake_student_results');
        const hiddenIdInput = document.getElementById('retake_student_id');
        const cardDiv = document.getElementById('retake_selected_student_card');
        const spinner = document.getElementById('search_spinner');
        const failedContainer = document.getElementById('failed_subjects_container');
        const failedList = document.getElementById('failed_subjects_list');
        const subjectSelect = document.getElementById('retake_subject_id');

        let debounceTimer = null;

        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 1) {
                resultsDiv.style.display = 'none';
                resultsDiv.innerHTML = '';
                return;
            }

            if (spinner) spinner.style.display = 'inline';

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('admin.students.search-api') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (spinner) spinner.style.display = 'none';
                        resultsDiv.innerHTML = '';
                        if (data.length === 0) {
                            resultsDiv.innerHTML = '<div style="padding:10px 14px;color:#6b7280;font-size:13px;">কোনো শিক্ষার্থী পাওয়া যায়নি</div>';
                            resultsDiv.style.display = 'block';
                            return;
                        }

                        data.forEach(item => {
                            const row = document.createElement('div');
                            row.style.padding = '8px 12px';
                            row.style.cursor = 'pointer';
                            row.style.borderBottom = '1px solid #f1f5f9';
                            row.style.display = 'flex';
                            row.style.justifyContent = 'space-between';
                            row.style.alignItems = 'center';
                            row.innerHTML = `
                                <div>
                                    <strong style="color:#1e293b;font-size:13px;">[${item.student_code}] ${item.name}</strong>
                                    <div style="font-size:11px;color:#64748b;">কোর্স: ${item.course_name} | ব্যাচ: ${item.batch_name}</div>
                                </div>
                                <div>
                                    ${item.failed_subjects && item.failed_subjects.length > 0 
                                        ? `<span style="background:#fee2e2;color:#b91c1c;font-size:11px;padding:2px 6px;border-radius:4px;">ফেল: ${item.failed_subjects.length} বিষয়</span>` 
                                        : ''}
                                </div>
                            `;
                            row.addEventListener('mouseenter', () => row.style.background = '#f8fafc');
                            row.addEventListener('mouseleave', () => row.style.background = '#ffffff');
                            row.addEventListener('click', () => selectRetakeStudent(item));
                            resultsDiv.appendChild(row);
                        });

                        resultsDiv.style.display = 'block';
                    })
                    .catch(err => {
                        if (spinner) spinner.style.display = 'none';
                        console.error(err);
                    });
            }, 250);
        });

        window.selectRetakeStudent = function(student) {
            hiddenIdInput.value = student.id;
            document.getElementById('selected_st_badge').textContent = student.student_code;
            document.getElementById('selected_st_name').textContent = student.name;
            document.getElementById('selected_st_meta').textContent = `কোর্স: ${student.course_name} | ব্যাচ: ${student.batch_name} | ফোন: ${student.phone || '—'}`;
            
            cardDiv.style.display = 'block';
            resultsDiv.style.display = 'none';
            searchInput.value = '';
            searchInput.style.display = 'none';

            // Failed subjects handling
            failedList.innerHTML = '';
            if (student.failed_subjects && student.failed_subjects.length > 0) {
                failedContainer.style.display = 'block';
                student.failed_subjects.forEach(fs => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm';
                    btn.style.cssText = 'background:#fee2e2;color:#991b1b;border:1px solid #f87171;padding:3px 8px;font-size:11px;border-radius:4px;cursor:pointer;';
                    btn.textContent = `🎯 ${fs.code}: ${fs.name}`;
                    btn.addEventListener('click', () => {
                        if (subjectSelect) {
                            subjectSelect.value = fs.id;
                        }
                    });
                    failedList.appendChild(btn);
                });

                if (student.failed_subjects.length === 1 && subjectSelect) {
                    subjectSelect.value = student.failed_subjects[0].id;
                }
            } else {
                failedContainer.style.display = 'none';
            }
        };

        window.clearSelectedRetakeStudent = function() {
            hiddenIdInput.value = '';
            cardDiv.style.display = 'none';
            failedContainer.style.display = 'none';
            failedList.innerHTML = '';
            searchInput.style.display = 'block';
            searchInput.value = '';
            searchInput.focus();
        };

        document.addEventListener('click', function(e) {
            if (!resultsDiv.contains(e.target) && e.target !== searchInput) {
                resultsDiv.style.display = 'none';
            }
        });
    })();
    </script>
    @endpush
</x-admin-layout>
