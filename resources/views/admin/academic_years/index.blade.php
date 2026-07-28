<x-admin-layout>
    <x-slot name="title">Academic Years</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Academic Years & Sessions</h1>
            <p>Manage institute calendar years and sessions</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addYearModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Academic Year
            </button>
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
                                <span class="badge badge-secondary no-dot" style="margin-right:4px">{{ $sess->name }}</span>
                            @empty
                                <span class="td-muted">No sessions</span>
                            @endforelse
                        </td>
                        <td>
                            @if($year->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <button class="btn btn-outline btn-sm" onclick="openSessionModal({{ $year->id }}, '{{ $year->name }}')">+ Session</button>
                            <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" style="display:inline" onsubmit="return confirm('Delete this academic year?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-red">Delete</button>
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
                        <input type="text" name="name" class="form-control" placeholder="e.g. Academic Year 2026" required>
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
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked> Active Session
                    </label>
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
    </script>
    @endpush
</x-admin-layout>
