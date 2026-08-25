<x-admin-layout>
    <x-slot name="title">Support Departments & Agents</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>🎧 Support Setup</h1>
            <p>Manage Support Departments and Assign Support Agents</p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" onclick="openModal('addDeptModal')">+ New Department</button>
            <button class="btn btn-success" onclick="openModal('addAgentModal')">+ New Support Agent</button>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="grid-2">
        {{-- Departments List --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">🏢 Support Departments ({{ $departments->count() }})</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Department Name</th>
                            <th>Agents</th>
                            <th>Tickets</th>
                            <th>Status</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $dept)
                        <tr>
                            <td>{{ $dept->sort_order }}</td>
                            <td>
                                <strong>{{ $dept->name }}</strong>
                                <div style="font-size:11px;color:#64748b">{{ $dept->description }}</div>
                            </td>
                            <td><span class="badge badge-scheduled no-dot">{{ $dept->agents_count }} Agents</span></td>
                            <td><span class="badge badge-secondary no-dot">{{ $dept->tickets_count }} Tickets</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.support-departments.toggle', $dept) }}" style="display:inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" style="border:none;background:none;cursor:pointer">
                                        @if($dept->is_active)
                                            <span class="badge badge-active">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="text-align:right">
                                <button class="btn btn-outline btn-sm" onclick='openEditDeptModal(@json($dept))'>Edit</button>
                                <form method="POST" action="{{ route('admin.support-departments.destroy', $dept) }}" style="display:inline" onsubmit="return confirm('Delete department?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-red">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8">No departments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Support Agents List --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">👥 Support Agents & Users ({{ $supportUsers->count() }})</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Agent Name & Email</th>
                            <th>Assigned Departments</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supportUsers as $u)
                        <tr>
                            <td>
                                <strong>{{ $u->name }}</strong>
                                <div style="font-size:11px;color:#64748b">{{ $u->email }}</div>
                            </td>
                            <td>
                                @forelse($u->supportDepartments as $d)
                                    <span class="badge badge-secondary no-dot" style="margin-right:2px;margin-bottom:2px">{{ $d->name }}</span>
                                @empty
                                    <span style="font-size:11px;color:#94a3b8;font-style:italic">No departments</span>
                                @endforelse
                            </td>
                            <td style="text-align:right">
                                <button class="btn btn-outline btn-sm" onclick='openEditAgentModal(@json($u), @json($u->supportDepartments->pluck("id")))'>Edit / Depts</button>
                                <form method="POST" action="{{ route('admin.support-agents.destroy', $u) }}" style="display:inline" onsubmit="return confirm('Delete support agent user?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm text-red">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:#94a3b8">No support users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    <!-- Add Dept Modal -->
    <div class="modal-overlay" id="addDeptModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">New Support Department</span>
                <button class="modal-close" onclick="closeModal('addDeptModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.support-departments.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Department Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. পেমেন্ট" required>
                    </div>
                    <div class="form-group">
                        <label>Description / Help Guidance</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Department responsibility details..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addDeptModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Dept Modal -->
    <div class="modal-overlay" id="editDeptModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Support Department</span>
                <button class="modal-close" onclick="closeModal('editDeptModal')">&times;</button>
            </div>
            <form method="POST" id="editDeptForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Department Name <span class="required">*</span></label>
                        <input type="text" name="name" id="ed_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description / Help Guidance</label>
                        <textarea name="description" id="ed_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="ed_sort_order" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editDeptModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Agent Modal -->
    <div class="modal-overlay" id="addAgentModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Create Support Agent User</span>
                <button class="modal-close" onclick="closeModal('addAgentModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.support-agents.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Agent Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Tanvir Ahmed">
                    </div>
                    <div class="form-group">
                        <label>Email Address (Login ID) <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="agent@iom.edu.bd">
                    </div>
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="******">
                    </div>
                    <div class="form-group">
                        <label>Assign Departments (Select multiple)</label>
                        <div style="display:flex;flex-direction:column;gap:6px;max-height:160px;overflow-y:auto;background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0">
                            @foreach($departments as $d)
                                <label style="font-size:13px;display:flex;align-items:center;gap:8px">
                                    <input type="checkbox" name="departments[]" value="{{ $d->id }}"> {{ $d->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addAgentModal')">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Support Agent</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Agent Modal -->
    <div class="modal-overlay" id="editAgentModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Edit Support Agent User</span>
                <button class="modal-close" onclick="closeModal('editAgentModal')">&times;</button>
            </div>
            <form method="POST" id="editAgentForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Agent Full Name <span class="required">*</span></label>
                        <input type="text" name="name" id="ea_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" id="ea_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label>Assign Departments</label>
                        <div id="editAgentDeptList" style="display:flex;flex-direction:column;gap:6px;max-height:160px;overflow-y:auto;background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0">
                            @foreach($departments as $d)
                                <label style="font-size:13px;display:flex;align-items:center;gap:8px">
                                    <input type="checkbox" name="departments[]" value="{{ $d->id }}" class="ea-dept-cb"> {{ $d->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editAgentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Support Agent</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditDeptModal(dept) {
        document.getElementById('editDeptForm').action = '/admin/support-departments/' + dept.id;
        document.getElementById('ed_name').value = dept.name;
        document.getElementById('ed_description').value = dept.description || '';
        document.getElementById('ed_sort_order').value = dept.sort_order || 0;
        openModal('editDeptModal');
    }

    function openEditAgentModal(user, deptIds) {
        document.getElementById('editAgentForm').action = '/admin/support-agents/' + user.id;
        document.getElementById('ea_name').value = user.name;
        document.getElementById('ea_email').value = user.email;

        document.querySelectorAll('.ea-dept-cb').forEach(cb => {
            cb.checked = deptIds.includes(parseInt(cb.value));
        });

        openModal('editAgentModal');
    }
    </script>
    @endpush
</x-admin-layout>
