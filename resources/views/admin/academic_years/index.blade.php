<x-admin-layout>
    <x-slot name="title">Academic Years</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Academic Years & Sessions</h1>
            <p>Manage institute calendar years and sessions</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addYearModal')">
                New Academic Year
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            {{ session('success') }}
        </div>
    {{-- Academic Year & Session Bengali Clarification Guide --}}
    <div class="card" style="background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border:1px solid #bbf7d0;padding:16px 20px;border-radius:12px;margin-bottom:20px;font-family:'Kalpurush',sans-serif">
        <div style="display:flex;align-items:flex-start;gap:14px">
            <div style="width:38px;height:38px;border-radius:8px;background:#059669;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div style="flex:1">
                <h3 style="font-size:15px;font-weight:700;color:#065f46;margin:0 0 8px 0">একাডেমিক বর্ষ ও ভর্তি সেশন নির্দেশিকা (Guide)</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:14px;font-size:13px">
                    <div style="background:#fff;padding:12px 14px;border-radius:8px;border:1px solid #a7f3d0;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
                        <strong style="color:#065f46;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-calendar-days"></i> ১. একাডেমিক বর্ষ (Academic Year):
                        </strong>
                        <div style="margin-top:5px;color:#334155;line-height:1.5">
                            মাদরাসার পূর্ণ ১২ মাসের শিক্ষা বর্ষ বা ক্যালেন্ডার বছর (যেমন: <strong>২০২৫-২০২৬ শিক্ষাবর্ষ</strong>)। শুরু ও শেষের তারিখ দিয়ে পুরো বছরের শিক্ষা ক্যালেন্ডার নির্ধারণ করা হয়।
                        </div>
                    </div>
                    <div style="background:#fff;padding:12px 14px;border-radius:8px;border:1px solid #a7f3d0;box-shadow:0 1px 3px rgba(0,0,0,0.03)">
                        <strong style="color:#065f46;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-user-graduate"></i> ২. ভর্তি সেশন (Intake Session):
                        </strong>
                        <div style="margin-top:5px;color:#334155;line-height:1.5">
                            একটি একাডেমিক বর্ষের অধীনে শিক্ষার্থী ভর্তির নির্দিষ্ট সময়কাল বা ইনটেক (যেমন: <strong>স্প্রিং / জানুয়ারি সেশন</strong> অথবা <strong>ফল / জুলাই সেশন</strong>)। প্রতিটি নতুন ব্যাচ একটি নির্দিষ্ট সেশনের অন্তর্ভুক্ত থাকে।
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Academic Year</th>
                        <th>Duration</th>
                        <th>Sessions</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($academicYears as $year)
                    <tr>
                        <td class="td-primary">
                            <strong>{{ $year->name }}</strong>
                        </td>
                        <td class="td-muted">
                            {{ \Carbon\Carbon::parse($year->start_date)->format('d M Y') }} — {{ \Carbon\Carbon::parse($year->end_date)->format('d M Y') }}
                        </td>
                        <td>
                            @forelse($year->sessions as $sess)
                                <div style="display:inline-flex;align-items:center;margin-right:6px;margin-bottom:4px">
                                    <span class="badge badge-secondary no-dot" style="margin-right:2px">{{ $sess->name }}</span>
                                    <form method="POST" action="{{ route('admin.academic-years.session.destroy', $sess) }}" style="display:inline" onsubmit="return confirm('Remove session {{ $sess->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Remove Session" style="border:none;background:none;color:var(--red);cursor:pointer;font-size:11px;padding:0 2px"></button>
                                    </form>
                                </div>
                            @empty
                                <span class="td-muted">No sessions</span>
                            @endforelse
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.academic-years.toggle-status', $year) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" style="background:none;border:none;padding:0;cursor:pointer" title="Click to toggle status (Active / Inactive)">
                                    @if($year->is_active)
                                        <span class="badge badge-active" style="cursor:pointer;transition:transform .15s" onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
                                            ● Active
                                        </span>
                                    @else
                                        <span class="badge badge-secondary" style="cursor:pointer;transition:transform .15s" onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
                                            ○ Inactive
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-outline btn-sm" onclick="openSessionModal({{ $year->id }}, '{{ $year->name }}')">Session</button>
                            <button class="btn btn-outline btn-sm" onclick='openEditYearModal(@json($year))'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" style="display:inline" onsubmit="return confirm('Delete this academic year?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">No academic years found. Click "New Academic Year" to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Academic Year Modal -->
    <div class="modal-overlay" id="addYearModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Academic Year</span>
                <button class="modal-close" onclick="closeModal('addYearModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.academic-years.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Academic Year Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Academic Year 2026-27" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date <span class="required">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>End Date <span class="required">*</span></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked> Set as Active Academic Year
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addYearModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Academic Year</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Academic Year Modal -->
    <div class="modal-overlay" id="editYearModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Academic Year</span>
                <button class="modal-close" onclick="closeModal('editYearModal')">&times;</button>
            </div>
            <form method="POST" id="editYearForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Academic Year Name <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_year_name" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date <span class="required">*</span></label>
                            <input type="date" name="start_date" id="edit_year_start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>End Date <span class="required">*</span></label>
                            <input type="date" name="end_date" id="edit_year_end_date" class="form-control" required>
                        </div>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" name="is_active" id="edit_year_is_active" value="1"> Set as Active Academic Year
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editYearModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Academic Year</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Session Modal -->
    <div class="modal-overlay" id="addSessionModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title" id="sessionModalTitle">Add Session</span>
                <button class="modal-close" onclick="closeModal('addSessionModal')">&times;</button>
            </div>
            <form method="POST" id="sessionForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Session Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Spring 2026, Fall 2026" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addSessionModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Session</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openSessionModal(yearId, yearName) {
        document.getElementById('sessionModalTitle').innerText = 'Add Session to ' + yearName;
        document.getElementById('sessionForm').action = '/admin/academic-years/' + yearId + '/session';
        openModal('addSessionModal');
    }

    function openEditYearModal(year) {
        document.getElementById('editYearForm').action = '/admin/academic-years/' + year.id;
        document.getElementById('edit_year_name').value = year.name;
        document.getElementById('edit_year_start_date').value = year.start_date ? year.start_date.split('T')[0] : '';
        document.getElementById('edit_year_end_date').value = year.end_date ? year.end_date.split('T')[0] : '';
        document.getElementById('edit_year_is_active').checked = !!year.is_active;
        openModal('editYearModal');
    }
    </script>
    @endpush
</x-admin-layout>
