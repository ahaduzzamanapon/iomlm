<x-admin-layout>
    <x-slot name="title">Fee Heads — Settings</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Fee Heads</h1>
            <p>Manage fee categories used in Course Fee Packages. Admission Fee and Retake Fee are system defaults and cannot be deleted.</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addFeeHeadModal')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Fee Head
            </button>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fee Head Name</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feeHeads as $i => $head)
                <tr>
                    <td class="td-muted">{{ $i + 1 }}</td>
                    <td class="td-primary">
                        <strong>{{ $head->name }}</strong>
                        @if($head->is_static)
                            <span class="badge badge-secondary no-dot" style="margin-left:6px;font-size:11px">System Default</span>
                        @endif
                    </td>
                    <td class="td-muted" style="font-family:monospace;font-size:12px">{{ $head->slug }}</td>
                    <td>
                        @if($head->is_static)
                            <span class="badge badge-scheduled no-dot">Static</span>
                        @else
                            <span class="badge badge-active no-dot">Custom</span>
                        @endif
                    </td>
                    <td>
                        @if($head->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if(!$head->is_static)
                            <button class="btn btn-outline btn-sm"
                                onclick="openEditFeeHead({{ $head->id }}, @json($head->name), {{ $head->is_active ? 1 : 0 }})">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.fee-heads.destroy', $head) }}" style="display:inline" onsubmit="return confirm('Delete this fee head?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--red)">Delete</button>
                            </form>
                        @else
                            <span class="td-muted" style="font-size:12px">Protected</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted)">No fee heads found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Fee Head Modal -->
    <div class="modal-overlay" id="addFeeHeadModal">
        <div class="modal" style="max-width:420px">
            <div class="modal-header">
                <span class="modal-title">Add New Fee Head</span>
                <button class="modal-close" onclick="closeModal('addFeeHeadModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.fee-heads.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Fee Head Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Tuition Fee, Mid Term Fee, Annual Fee" required>
                        <small style="color:var(--text-muted);font-size:12px">This will be available in Course Fee Packages.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addFeeHeadModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Fee Head</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Fee Head Modal -->
    <div class="modal-overlay" id="editFeeHeadModal">
        <div class="modal" style="max-width:420px">
            <div class="modal-header">
                <span class="modal-title">Edit Fee Head</span>
                <button class="modal-close" onclick="closeModal('editFeeHeadModal')">&times;</button>
            </div>
            <form method="POST" id="editFeeHeadForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Fee Head Name <span class="required">*</span></label>
                        <input type="text" name="name" id="efh_name" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <label class="form-check" style="cursor:pointer">
                            <input type="checkbox" name="is_active" id="efh_is_active" value="1"> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editFeeHeadModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditFeeHead(id, name, isActive) {
        document.getElementById('editFeeHeadForm').action = '/admin/fee-heads/' + id;
        document.getElementById('efh_name').value = name;
        document.getElementById('efh_is_active').checked = isActive === 1;
        openModal('editFeeHeadModal');
    }
    </script>
    @endpush
</x-admin-layout>
