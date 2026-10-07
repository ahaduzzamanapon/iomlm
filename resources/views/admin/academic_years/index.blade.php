<x-admin-layout>
    <x-slot name="title">Academic Years & Sessions</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Academic Years & Sessions</h1>
            <p>Manage institute calendar years and admission intake sessions</p>
        </div>
        <div class="page-header-actions" style="display:flex;gap:10px;">
            <button class="btn btn-outline" onclick="openCreateSessionModal()" style="font-family:'Kalpurush',sans-serif;font-weight:600">
                <i class="fa-solid fa-plus"></i> New Session (নতুন সেশন)
            </button>
            <button class="btn btn-primary" onclick="openModal('addYearModal')" style="font-family:'Kalpurush',sans-serif;font-weight:600">
                <i class="fa-solid fa-calendar-plus"></i> New Academic Year (নতুন শিক্ষাবর্ষ)
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;font-family:'Kalpurush',sans-serif">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

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
                            একটি একাডেমিক বর্ষের অধীনে শিক্ষার্থী ভর্তির নির্দিষ্ট সময়কাল বা ইনটেক (যেমন: <strong>স্প্রিং / জানুয়ারি সেশন</strong> অথবা <strong>ফল / জুলাই সেশন</strong>)। প্রতিটি সেশনের নিজস্ব শুরু ও সমাপ্তির তারিখ থাকে এবং প্রতিটি ব্যাচ নির্দিষ্ট সেশনের অন্তর্ভুক্ত থাকে।
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;border-bottom:2px solid #e2e8f0;padding-bottom:2px;font-family:'Kalpurush',sans-serif">
        <button type="button" id="tabBtnYears" onclick="switchMainTab('years')" style="padding:8px 20px;border:none;border-bottom:2px solid #047857;background:transparent;color:#047857;font-weight:700;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;margin-bottom:-4px;">
            <i class="fa-solid fa-calendar-days"></i> একাডেমিক বর্ষ তালিকা (Academic Years)
            <span style="font-size:11px;background:#dcfce7;color:#15803d;padding:1px 8px;border-radius:12px;font-weight:700">{{ $academicYears->count() }}</span>
        </button>
        <button type="button" id="tabBtnSessions" onclick="switchMainTab('sessions')" style="padding:8px 20px;border:none;border-bottom:2px solid transparent;background:transparent;color:#64748b;font-weight:600;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;margin-bottom:-4px;">
            <i class="fa-solid fa-user-graduate"></i> সকল সেশন ব্যবস্থাপনা (All Intake Sessions)
            <span style="font-size:11px;background:#e2e8f0;color:#334155;padding:1px 8px;border-radius:12px;font-weight:700">{{ $allSessions->count() }}</span>
        </button>
    </div>

    {{-- Section 1: Academic Years Table --}}
    <div class="card" id="yearsTableCard">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Academic Year</th>
                        <th>Duration</th>
                        <th>Sessions (সেশনসমূহ)</th>
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
                                <div style="display:inline-flex;align-items:center;margin-right:6px;margin-bottom:4px;background:#f8fafc;border:1px solid #cbd5e1;padding:3px 9px;border-radius:16px;font-size:11.5px;font-family:'Kalpurush',sans-serif">
                                    <span style="width:7px;height:7px;border-radius:50%;background:{{ $sess->is_active ? '#10b981' : '#94a3b8' }};display:inline-block;margin-right:6px;" title="{{ $sess->is_active ? 'Active' : 'Inactive' }}"></span>
                                    <strong style="color:#0f172a">{{ $sess->name }}</strong>
                                    @if($sess->start_date && $sess->end_date)
                                        <span style="color:#64748b;font-size:10px;margin-left:5px">({{ \Carbon\Carbon::parse($sess->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($sess->end_date)->format('d M') }})</span>
                                    @endif
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
                            <button class="btn btn-outline btn-sm" onclick='openYearSessionsManageModal({{ $year->id }}, @json($year->name), @json($year->sessions))' title="Manage sessions for this academic year">
                                <i class="fa-solid fa-sliders"></i> Session
                            </button>
                            <button class="btn btn-outline btn-sm" onclick='openEditYearModal(@json($year))'>
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
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

    {{-- Section 2: All Intake Sessions Table --}}
    <div class="card" id="sessionsTableCard" style="display:none;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Session Name (সেশন নাম)</th>
                        <th>Academic Year (শিক্ষাবর্ষ)</th>
                        <th>Start Date (শুরুর তারিখ)</th>
                        <th>End Date (সমাপ্তির তারিখ)</th>
                        <th>Status (স্ট্যাটাস)</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allSessions as $session)
                    <tr>
                        <td class="td-primary">
                            <div style="display:flex;align-items:center;gap:8px;font-family:'Kalpurush',sans-serif">
                                <i class="fa-solid fa-user-graduate" style="color:#059669;font-size:14px"></i>
                                <strong>{{ $session->name }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($session->academicYear)
                                <span class="badge badge-secondary no-dot" style="font-weight:600">
                                    {{ $session->academicYear->name }}
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($session->start_date)
                                <span style="font-family:monospace;font-size:12px;color:#0f172a">
                                    {{ \Carbon\Carbon::parse($session->start_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($session->end_date)
                                <span style="font-family:monospace;font-size:12px;color:#0f172a">
                                    {{ \Carbon\Carbon::parse($session->end_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="td-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.academic-years.session.toggle-status', $session) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" style="background:none;border:none;padding:0;cursor:pointer" title="Click to toggle session active/inactive">
                                    @if($session->is_active)
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
                            <button class="btn btn-outline btn-sm" onclick='openEditSessionModal(@json($session))' title="Edit Session">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <form method="POST" action="{{ route('admin.academic-years.session.destroy', $session) }}" style="display:inline" onsubmit="return confirm('Remove session {{ $session->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red" title="Delete Session">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);font-family:'Kalpurush',sans-serif">
                            কোনো ভর্তি সেশন পাওয়া যায়নি। উপরে <strong>New Session</strong> বাটনে ক্লিক করে নতুন সেশন তৈরি করুন।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Academic Year Modal -->
    <div class="modal-overlay" id="addYearModal">
        <div class="modal" style="font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title">New Academic Year (নতুন শিক্ষাবর্ষ)</span>
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
                    <label class="form-check" style="cursor:pointer;font-weight:600">
                        <input type="checkbox" name="is_active" value="1" checked style="width:16px;height:16px;accent-color:#047857"> Set as Active Academic Year (সক্রিয় রাখুন)
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addYearModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:#047857;border-color:#047857">Save Academic Year</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Academic Year Modal -->
    <div class="modal-overlay" id="editYearModal">
        <div class="modal" style="font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title">Edit Academic Year (শিক্ষাবর্ষ পরিবর্তন)</span>
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
                    <label class="form-check" style="cursor:pointer;font-weight:600">
                        <input type="checkbox" name="is_active" id="edit_year_is_active" value="1" style="width:16px;height:16px;accent-color:#047857"> Set as Active Academic Year (সক্রিয় রাখুন)
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editYearModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:#047857;border-color:#047857">Update Academic Year</button>
                </div>
            </form>
        </div>
    </div>

    <!-- General Create Session Modal -->
    <div class="modal-overlay" id="createSessionModal">
        <div class="modal" style="max-width:520px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title" style="display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-user-graduate" style="color:#047857"></i>
                    <span>New Intake Session (নতুন ভর্তি সেশন তৈরি)</span>
                </span>
                <button class="modal-close" onclick="closeModal('createSessionModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.academic-years.session.store-direct') }}">
                @csrf
                <div class="modal-body" style="padding:16px 20px">
                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-weight:600;font-size:13px">Academic Year (শিক্ষাবর্ষ) <span class="required">*</span></label>
                        <select name="academic_year_id" id="create_session_year_id" class="form-control" required style="height:38px">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>
                                    {{ $ay->name }} ({{ \Carbon\Carbon::parse($ay->start_date)->format('Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-weight:600;font-size:13px">Session Name (সেশন নাম) <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="যেমন: Spring 2026, Fall 2026, জানুয়ারি সেশন" required>
                    </div>

                    <div class="form-row" style="margin-bottom:14px">
                        <div class="form-group" style="flex:1;margin-bottom:0">
                            <label style="font-weight:600;font-size:13px">Start Date (শুরুর তারিখ)</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="form-group" style="flex:1;margin-bottom:0">
                            <label style="font-weight:600;font-size:13px">End Date (সমাপ্তির তারিখ)</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>

                    <label class="form-check" style="cursor:pointer;font-weight:600;margin-top:6px">
                        <input type="checkbox" name="is_active" value="1" checked style="width:16px;height:16px;accent-color:#047857">
                        সক্রিয় সেশন হিসেবে রাখুন (Active Session)
                    </label>
                    <div style="font-size:12px;color:#047857;margin-top:4px;">
                        <i class="fa-solid fa-circle-info"></i> একই শিক্ষাবর্ষে একই সাথে একাধিক সেশন সক্রিয় রাখা যাবে না। এটি সক্রিয় করলে ঐ বর্ষের অন্য সেশন স্বয়ংক্রিয়ভাবে নিষ্ক্রিয় হবে এবং নতুন ভর্তি এই সেশনে জমা হবে।
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createSessionModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#047857;border-color:#047857">
                        <i class="fa-solid fa-check"></i> সেশন সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Session Modal -->
    <div class="modal-overlay" id="editSessionModal">
        <div class="modal" style="max-width:520px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title" style="display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-pen-to-square" style="color:#047857"></i>
                    <span>Edit Intake Session (সেশন তথ্য পরিবর্তন)</span>
                </span>
                <button class="modal-close" onclick="closeModal('editSessionModal')">&times;</button>
            </div>
            <form method="POST" id="editSessionForm">
                @csrf @method('PUT')
                <div class="modal-body" style="padding:16px 20px">
                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-weight:600;font-size:13px">Academic Year (শিক্ষাবর্ষ) <span class="required">*</span></label>
                        <select name="academic_year_id" id="edit_session_academic_year_id" class="form-control" required style="height:38px">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-weight:600;font-size:13px">Session Name (সেশন নাম) <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_session_name" class="form-control" required>
                    </div>

                    <div class="form-row" style="margin-bottom:14px">
                        <div class="form-group" style="flex:1;margin-bottom:0">
                            <label style="font-weight:600;font-size:13px">Start Date (শুরুর তারিখ)</label>
                            <input type="date" name="start_date" id="edit_session_start_date" class="form-control">
                        </div>
                        <div class="form-group" style="flex:1;margin-bottom:0">
                            <label style="font-weight:600;font-size:13px">End Date (সমাপ্তির তারিখ)</label>
                            <input type="date" name="end_date" id="edit_session_end_date" class="form-control">
                        </div>
                    </div>

                    <label class="form-check" style="cursor:pointer;font-weight:600;margin-top:6px">
                        <input type="checkbox" name="is_active" id="edit_session_is_active" value="1" style="width:16px;height:16px;accent-color:#047857">
                        সক্রিয় সেশন হিসেবে রাখুন (Active Session)
                    </label>
                    <div style="font-size:12px;color:#047857;margin-top:4px;">
                        <i class="fa-solid fa-circle-info"></i> একই শিক্ষাবর্ষে একই সাথে একাধিক সেশন সক্রিয় রাখা যাবে না। এটি সক্রিয় করলে ঐ বর্ষের অন্য সেশন স্বয়ংক্রিয়ভাবে নিষ্ক্রিয় হবে এবং নতুন ভর্তি এই সেশনে জমা হবে।
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editSessionModal')">বাতিল</button>
                    <button type="submit" class="btn btn-primary" style="background:#047857;border-color:#047857">
                        <i class="fa-solid fa-check"></i> সেশন আপডেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Year Sessions Modal (Row Action) -->
    <div class="modal-overlay" id="manageYearSessionsModal">
        <div class="modal" style="max-width:700px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title" style="display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-sliders" style="color:#047857"></i>
                    <span id="manageYearSessionsTitle">Session Management</span>
                </span>
                <button class="modal-close" onclick="closeModal('manageYearSessionsModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding:18px 22px">
                {{-- Existing Sessions List --}}
                <div style="margin-bottom:18px">
                    <h4 style="font-size:13.5px;font-weight:700;color:#0f172a;margin:0 0 10px 0;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-list-check" style="color:#059669"></i>
                        <span>এই শিক্ষাবর্ষের সেশনসমূহ:</span>
                    </h4>
                    <div id="yearSessionsListContainer" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
                        <!-- Populated by JS -->
                    </div>
                </div>

                {{-- Add New Session Form for This Year --}}
                <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:10px;padding:14px 16px;">
                    <h5 style="font-size:13px;font-weight:700;color:#065f46;margin:0 0 10px 0;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>নতুন সেশন যোগ করুন (Add Session)</span>
                    </h5>
                    <form method="POST" id="yearAddSessionForm">
                        @csrf
                        <div class="form-row" style="margin-bottom:10px">
                            <div class="form-group" style="flex:2;margin-bottom:0">
                                <label style="font-size:12px;font-weight:600">সেশন নাম <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="যেমন: Spring 2026, Fall 2026" required style="height:34px;font-size:12.5px">
                            </div>
                            <div class="form-group" style="flex:1;margin-bottom:0">
                                <label style="font-size:12px;font-weight:600">শুরুর তারিখ</label>
                                <input type="date" name="start_date" class="form-control" style="height:34px;font-size:12.5px">
                            </div>
                            <div class="form-group" style="flex:1;margin-bottom:0">
                                <label style="font-size:12px;font-weight:600">সমাপ্তির তারিখ</label>
                                <input type="date" name="end_date" class="form-control" style="height:34px;font-size:12.5px">
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px">
                            <label class="form-check" style="cursor:pointer;font-weight:600;font-size:12.5px;margin-bottom:0">
                                <input type="checkbox" name="is_active" value="1" checked style="width:16px;height:16px;accent-color:#047857">
                                সক্রিয় সেশন (Active)
                            </label>
                            <button type="submit" class="btn btn-primary btn-sm" style="background:#047857;border-color:#047857;font-size:12px;padding:5px 14px">
                                <i class="fa-solid fa-plus"></i> সেশন যোগ করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer" style="padding:10px 20px">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('manageYearSessionsModal')">বন্ধ করুন</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function switchMainTab(tab) {
        const tabBtnYears = document.getElementById('tabBtnYears');
        const tabBtnSessions = document.getElementById('tabBtnSessions');
        const yearsTableCard = document.getElementById('yearsTableCard');
        const sessionsTableCard = document.getElementById('sessionsTableCard');

        if (tab === 'years') {
            yearsTableCard.style.display = 'block';
            sessionsTableCard.style.display = 'none';
            tabBtnYears.style.borderColor = '#047857';
            tabBtnYears.style.color = '#047857';
            tabBtnYears.style.fontWeight = '700';
            tabBtnSessions.style.borderColor = 'transparent';
            tabBtnSessions.style.color = '#64748b';
            tabBtnSessions.style.fontWeight = '600';
        } else {
            yearsTableCard.style.display = 'none';
            sessionsTableCard.style.display = 'block';
            tabBtnSessions.style.borderColor = '#047857';
            tabBtnSessions.style.color = '#047857';
            tabBtnSessions.style.fontWeight = '700';
            tabBtnYears.style.borderColor = 'transparent';
            tabBtnYears.style.color = '#64748b';
            tabBtnYears.style.fontWeight = '600';
        }
    }

    function openCreateSessionModal(defaultYearId = null) {
        if (defaultYearId) {
            const select = document.getElementById('create_session_year_id');
            if (select) select.value = defaultYearId;
        }
        openModal('createSessionModal');
    }

    function openEditSessionModal(session) {
        const form = document.getElementById('editSessionForm');
        form.action = '/admin/academic-years/sessions/' + session.id;
        document.getElementById('edit_session_academic_year_id').value = session.academic_year_id;
        document.getElementById('edit_session_name').value = session.name;
        document.getElementById('edit_session_start_date').value = session.start_date ? session.start_date.split('T')[0] : '';
        document.getElementById('edit_session_end_date').value = session.end_date ? session.end_date.split('T')[0] : '';
        document.getElementById('edit_session_is_active').checked = !!session.is_active;
        openModal('editSessionModal');
    }

    function openYearSessionsManageModal(yearId, yearName, sessions) {
        document.getElementById('manageYearSessionsTitle').innerText = yearName + ' — সেশন ব্যবস্থাপনা';
        document.getElementById('yearAddSessionForm').action = '/admin/academic-years/' + yearId + '/session';

        const container = document.getElementById('yearSessionsListContainer');
        if (!sessions || sessions.length === 0) {
            container.innerHTML = '<div style="padding:16px;text-align:center;color:#64748b;font-size:12.5px">এই শিক্ষাবর্ষের অধীনে এখনও কোনো সেশন তৈরি করা হয়নি। নিচে নতুন সেশন তৈরি করুন।</div>';
        } else {
            let html = '<table style="width:100%;margin:0;font-size:12.5px"><thead><tr style="background:#f1f5f9;color:#475569">' +
                '<th style="padding:8px 12px;text-align:left">সেশন নাম</th>' +
                '<th style="padding:8px 12px;text-align:left">শুরু ও সমাপ্তি তারিখ</th>' +
                '<th style="padding:8px 12px;text-align:center">স্ট্যাটাস</th>' +
                '<th style="padding:8px 12px;text-align:right">অ্যাকশন</th></tr></thead><tbody>';

            sessions.forEach(s => {
                const startStr = s.start_date ? s.start_date.split('T')[0] : '—';
                const endStr = s.end_date ? s.end_date.split('T')[0] : '—';
                const activeBadge = s.is_active
                    ? '<span class="badge badge-active" style="font-size:11px">● Active</span>'
                    : '<span class="badge badge-secondary" style="font-size:11px">○ Inactive</span>';

                html += `<tr style="border-top:1px solid #e2e8f0">
                    <td style="padding:8px 12px"><strong>${s.name}</strong></td>
                    <td style="padding:8px 12px;font-family:monospace;font-size:11.5px;color:#334155">${startStr} হতে ${endStr}</td>
                    <td style="padding:8px 12px;text-align:center">
                        <form method="POST" action="/admin/academic-years/sessions/${s.id}/toggle-status" style="display:inline">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'}">
                            <input type="hidden" name="_method" value="PATCH">
                            <button type="submit" style="background:none;border:none;cursor:pointer;padding:0" title="Click to toggle status">
                                ${activeBadge}
                            </button>
                        </form>
                    </td>
                    <td style="padding:8px 12px;text-align:right">
                        <button type="button" class="btn btn-outline btn-sm" onclick='closeModal("manageYearSessionsModal");openEditSessionModal(${JSON.stringify(s)})' style="padding:2px 8px;font-size:11px">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                        <form method="POST" action="/admin/academic-years/sessions/${s.id}" style="display:inline" onsubmit="return confirm('Remove session ${s.name}?')">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-ghost btn-sm text-red" style="padding:2px 8px;font-size:11px" title="Delete Session">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        openModal('manageYearSessionsModal');
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

