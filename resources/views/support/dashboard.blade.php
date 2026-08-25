<x-support-layout>
    <x-slot name="title">Support Agent Dashboard</x-slot>

    {{-- Stats Grid --}}
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
        <div class="stat-card" style="border-left: 4px solid #f59e0b">
            <div class="stat-icon" style="background:#fef3c7;color:#b45309">⏳</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#b45309">{{ $pendingCount }}</div>
                <div class="stat-label">Pending Tickets (Queue)</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #0284c7">
            <div class="stat-icon" style="background:#e0f2fe;color:#0369a1">💬</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#0369a1">{{ $myActiveCount }}</div>
                <div class="stat-label">My Active Live Chats</div>
            </div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #10b981">
            <div class="stat-icon" style="background:#d1fae5;color:#047857">✅</div>
            <div class="stat-info">
                <div class="stat-value" style="color:#047857">{{ $myResolvedCount }}</div>
                <div class="stat-label">My Resolved Tickets</div>
            </div>
        </div>
    </div>

    {{-- Ticket List Table --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <span class="card-title">🎧 Support Tickets Queue</span>

            {{-- Filter Tabs --}}
            <div style="display:flex;gap:6px">
                <a href="{{ route('support.dashboard', ['status' => 'ALL']) }}" class="btn btn-sm {{ $statusFilter === 'ALL' ? 'btn-primary' : 'btn-outline' }}">All</a>
                <a href="{{ route('support.dashboard', ['status' => 'PENDING']) }}" class="btn btn-sm {{ $statusFilter === 'PENDING' ? 'btn-primary' : 'btn-outline' }}">Pending Queue ({{ $pendingCount }})</a>
                <a href="{{ route('support.dashboard', ['status' => 'IN_PROGRESS']) }}" class="btn btn-sm {{ $statusFilter === 'IN_PROGRESS' ? 'btn-primary' : 'btn-outline' }}">Active Chats ({{ $myActiveCount }})</a>
                <a href="{{ route('support.dashboard', ['status' => 'CLOSED']) }}" class="btn btn-sm {{ $statusFilter === 'CLOSED' ? 'btn-primary' : 'btn-outline' }}">Closed</a>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ticket No</th>
                        <th>User Name & Contact</th>
                        <th>Department</th>
                        <th>Subject & Issue</th>
                        <th>Submitted At</th>
                        <th>Status</th>
                        <th>Agent</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <td>
                            <strong style="color:#0284c7">{{ $t->ticket_no }}</strong>
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
                            <div style="font-size:12px;color:#64748b;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $t->problem_details }}
                            </div>
                        </td>
                        <td style="font-size:12px;color:#64748b">
                            {{ $t->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td>
                            @if($t->status === 'PENDING')
                                <span class="badge badge-pending">⏳ Pending Queue</span>
                            @elseif($t->status === 'IN_PROGRESS')
                                <span class="badge badge-running">🟢 Live Chat Active</span>
                            @else
                                <span class="badge badge-secondary">🔒 Closed</span>
                            @endif
                        </td>
                        <td style="font-size:12px">
                            @if($t->assignedAgent)
                                <strong>{{ $t->assignedAgent->name }}</strong>
                            @else
                                <span style="color:#94a3b8;font-style:italic">Unassigned</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            @if($t->status === 'PENDING')
                                <form method="POST" action="{{ route('support.tickets.accept', $t->uuid) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        ⚡ Accept & Chat
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('support.chat', $t->uuid) }}" class="btn btn-primary btn-sm">
                                    💬 Open Chat Window
                                </a>
                            @endif
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
</x-support-layout>
