<x-admin-layout>
    <x-slot name="title">All Support Tickets</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>🎧 Support Tickets Overview</h1>
            <p>Monitor all incoming support tickets, live chats, and department performance</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.support-departments.index') }}" class="btn btn-outline">⚙️ Manage Departments & Agents</a>
            <a href="{{ route('support.dashboard') }}" class="btn btn-primary">💬 Open Agent Panel</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Overall Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 24px;">
        <div class="stat-card" style="border-left: 4px solid #6366f1">
            <div class="stat-icon" style="background:#e0e7ff;color:#4338ca">📋</div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalCount }}</div>
                <div class="stat-label">Total Tickets</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #f59e0b">
            <div class="stat-icon" style="background:#fef3c7;color:#b45309">⏳</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#b45309">{{ $pendingCount }}</div>
                <div class="stat-label">Pending Queue</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #0284c7">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1">💬</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#0369a1">{{ $activeCount }}</div>
                <div class="stat-label">Active Chats</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #10b981">
            <div class="stat-icon" style="background:#d1fae5;color:#047857">✅</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#047857">{{ $closedCount }}</div>
                <div class="stat-label">Resolved / Closed</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #ec4899">
            <div class="stat-icon" style="background:#fce7f3;color:#be185d">★</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#be185d">{{ $avgRating }} / 5</div>
                <div class="stat-label">Avg User Rating</div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Box --}}
    <div class="card" style="margin-bottom:20px;padding:16px">
        <form method="GET" action="{{ route('admin.support-tickets.index') }}" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <div style="flex:1;min-width:220px">
                <input type="text" name="search" class="form-control" placeholder="Search Ticket No, Phone, Name, Email or Subject..." value="{{ $search }}">
            </div>

            <div style="width:200px">
                <select name="department_id" class="form-control">
                    <option value="">-- All Departments --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ $departmentId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="width:160px">
                <select name="status" class="form-control">
                    <option value="ALL" {{ $status === 'ALL' ? 'selected' : '' }}>All Status</option>
                    <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>Pending Queue</option>
                    <option value="IN_PROGRESS" {{ $status === 'IN_PROGRESS' ? 'selected' : '' }}>Active Chat</option>
                    <option value="CLOSED" {{ $status === 'CLOSED' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.support-tickets.index') }}" class="btn btn-outline">Clear</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="card" style="overflow:visible">
        <div class="table-wrapper" style="overflow:visible">
            <table>
                <thead>
                    <tr>
                        <th>Ticket No</th>
                        <th>User Name & Contact</th>
                        <th>Department</th>
                        <th>Subject & Issue</th>
                        <th>Assigned Agent</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <td>
                            <strong style="color:#0284c7">{{ $t->ticket_no }}</strong>
                            <div style="font-size:11px;color:#64748b">{{ $t->created_at->format('d M Y, h:i A') }}</div>
                        </td>
                        <td>
                            <strong style="font-size:14px">{{ $t->name }}</strong>
                            <div style="font-size:12px;color:#64748b">
                                📞 {{ $t->phone }} &middot; ✉️ {{ $t->email }}
                                @if($t->student_id)
                                    &middot; Roll: <strong>{{ $t->student_id }}</strong>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-secondary no-dot">🏢 {{ $t->department->name ?? '—' }}</span>
                        </td>
                        <td>
                            <div style="font-weight:700;font-size:13px;color:#0f172a">{{ $t->subject }}</div>
                            <div style="font-size:12px;color:#64748b;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $t->problem_details }}
                            </div>
                        </td>
                        <td>
                            @if($t->assignedAgent)
                                <strong style="color:#0f172a">{{ $t->assignedAgent->name }}</strong>
                            @else
                                <span style="color:#94a3b8;font-style:italic">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @if($t->status === 'PENDING')
                                <span class="badge badge-pending">⏳ Pending</span>
                            @elseif($t->status === 'IN_PROGRESS')
                                <span class="badge badge-running">🟢 Active Chat</span>
                            @else
                                <span class="badge badge-secondary">🔒 Closed</span>
                            @endif
                        </td>
                        <td>
                            @if($t->rating)
                                <span style="color:#f59e0b;font-weight:700">★ {{ $t->rating }}/5</span>
                            @else
                                <span style="color:#94a3b8;font-size:12px">—</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="dropdown" style="display:inline-block">
                                <button class="btn btn-outline btn-sm" onclick="toggleDropdown('stact-{{ $t->id }}')" style="gap:4px">
                                    Actions
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="dropdown-menu" id="stact-{{ $t->id }}" style="right:0;min-width:170px">
                                    <a href="{{ route('support.chat', $t->uuid) }}" target="_blank" class="dropdown-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        Open Live Chat
                                    </a>
                                    <button class="dropdown-item" onclick='openReassignModal(@json($t))'>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        Reassign Agent/Dept
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:#94a3b8">
                            কোনো সাপোর্ট টিকিট পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div style="padding:16px">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

    <!-- Reassign Modal -->
    <div class="modal-overlay" id="reassignModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Reassign Ticket</span>
                <button class="modal-close" onclick="closeModal('reassignModal')">&times;</button>
            </div>
            <form method="POST" id="reassignForm">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Department <span class="required">*</span></label>
                        <select name="department_id" id="re_dept_id" class="form-control" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assign Agent</label>
                        <select name="assigned_agent_id" id="re_agent_id" class="form-control">
                            <option value="">-- Unassigned (Keep in Queue) --</option>
                            @foreach($supportAgents as $sa)
                                <option value="{{ $sa->id }}">{{ $sa->name }} ({{ $sa->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('reassignModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Assignment</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openReassignModal(ticket) {
        document.getElementById('reassignForm').action = '/admin/support-tickets/' + ticket.id + '/reassign';
        document.getElementById('re_dept_id').value = ticket.department_id;
        document.getElementById('re_agent_id').value = ticket.assigned_agent_id || '';
        openModal('reassignModal');
    }
    </script>
    @endpush

</x-admin-layout>
