<x-admin-layout>
    <x-slot name="title">Support Ticket — {{ $ticket->ticket_no }}</x-slot>

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
        <div class="page-header-left">
            <h1 style="display:flex;align-items:center;gap:10px">
                🎧 Ticket: {{ $ticket->ticket_no }}
                @if($ticket->status === 'PENDING')
                    <span class="badge badge-pending">⏳ Pending Queue</span>
                @elseif($ticket->status === 'IN_PROGRESS')
                    <span class="badge badge-running">🟢 Active Chat</span>
                @else
                    <span class="badge badge-secondary">🔒 Closed</span>
                @endif
            </h1>
            <p>Submitted {{ $ticket->created_at->diffForHumans() }} ({{ $ticket->created_at->format('d M Y, h:i A') }})</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <a href="{{ route('support.chat', $ticket->uuid) }}" target="_blank" class="btn btn-primary">
                💬 Open Agent Chat Window ↗
            </a>
            <a href="{{ route('admin.support-tickets.index') }}" class="btn btn-secondary">
                ← Back to All Tickets
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
        {{-- Left: Ticket Details & Messages --}}
        <div>
            {{-- Ticket Subject & Issue Box --}}
            <div class="card" style="margin-bottom:20px;padding:20px">
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);margin-bottom:8px">Subject & Issue Details</div>
                <h3 style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:12px">{{ $ticket->subject }}</h3>
                <div style="font-size:14px;color:#334155;line-height:1.6;white-space:pre-line;background:#f8fafc;padding:14px;border-radius:8px;border:1px solid #e2e8f0">
                    {{ $ticket->problem_details }}
                </div>
            </div>

            {{-- Chat History --}}
            <div class="card" style="padding:20px">
                <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--blue);margin-bottom:16px">💬 Conversation History</div>
                <div style="display:flex;flex-direction:column;gap:12px;max-height:500px;overflow-y:auto;padding-right:6px">
                    @forelse($ticket->messages as $msg)
                        @php
                            $isUser = $msg->sender_type === 'USER';
                            $isSys  = $msg->sender_type === 'SYSTEM';
                        @endphp
                        @if($isSys)
                            <div style="text-align:center;margin:6px 0">
                                <span style="display:inline-block;background:#f1f5f9;color:#64748b;font-size:11.5px;padding:4px 12px;border-radius:20px;border:1px solid #e2e8f0">
                                    ⚙️ {{ $msg->message }}
                                </span>
                            </div>
                        @else
                            <div style="display:flex;justify-content:{{ $isUser ? 'flex-start' : 'flex-end' }}">
                                <div style="max-width:80%;border-radius:12px;padding:10px 14px;font-size:13px;line-height:1.5;background:{{ $isUser ? '#f8fafc' : '#eff6ff' }};border:1px solid {{ $isUser ? '#e2e8f0' : '#bfdbfe' }}">
                                    <div style="font-size:11px;font-weight:700;margin-bottom:3px;color:{{ $isUser ? '#475569' : '#1d4ed8' }}">
                                        {{ $isUser ? ($ticket->name . ' (Student/User)') : ($msg->sender->name ?? 'Support Agent') }}
                                        <span style="font-weight:400;color:#94a3b8;margin-left:6px">{{ $msg->created_at->format('h:i A') }}</span>
                                    </div>
                                    <div style="color:#1e293b;white-space:pre-line">{{ $msg->message }}</div>
                                    @if($msg->attachment_path)
                                        <div style="margin-top:6px">
                                            <a href="{{ $msg->attachment_path }}" target="_blank" style="font-size:11.5px;color:#2563eb;text-decoration:underline">📎 View Attachment</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <div style="text-align:center;padding:30px;color:#94a3b8;font-size:13px">
                            No messages exchanged in this ticket yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: User Info & Department Manager --}}
        <div>
            {{-- Department & Assignment Manager Card --}}
            <div class="card" style="margin-bottom:20px;padding:20px;border-top:4px solid #3b82f6">
                <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:14px">🏢 Department &amp; Assignment</div>
                <form method="POST" action="{{ route('admin.support-tickets.reassign', $ticket) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-group" style="margin-bottom:14px">
                        <label style="font-size:12.5px;font-weight:600">Assigned Department <span class="required">*</span></label>
                        <select name="department_id" class="form-control" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ $ticket->department_id == $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label style="font-size:12.5px;font-weight:600">Assigned Agent</label>
                        <select name="assigned_agent_id" class="form-control">
                            <option value="">-- Unassigned (Keep in Queue) --</option>
                            @foreach($supportAgents as $sa)
                                <option value="{{ $sa->id }}" {{ $ticket->assigned_agent_id == $sa->id ? 'selected' : '' }}>
                                    {{ $sa->name }} ({{ $sa->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                        ✓ Update Department &amp; Agent
                    </button>
                </form>
            </div>

            {{-- User Details Card --}}
            <div class="card" style="padding:20px">
                <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:14px">👤 Requester Information</div>
                <table class="table" style="font-size:13px">
                    <tr><th style="color:var(--text-muted);width:100px">Name:</th><td><strong>{{ $ticket->name }}</strong></td></tr>
                    <tr><th style="color:var(--text-muted)">Phone:</th><td>{{ $ticket->phone }}</td></tr>
                    <tr><th style="color:var(--text-muted)">Email:</th><td>{{ $ticket->email }}</td></tr>
                    @if($ticket->student_id)
                        <tr><th style="color:var(--text-muted)">Student ID:</th><td><strong style="color:var(--blue)">{{ $ticket->student_id }}</strong></td></tr>
                    @endif
                    <tr>
                        <th style="color:var(--text-muted)">Rating:</th>
                        <td>
                            @if($ticket->rating)
                                <span style="color:#f59e0b;font-weight:700">★ {{ $ticket->rating }}/5</span>
                                @if($ticket->feedback)
                                    <div style="font-size:11px;color:#64748b;margin-top:2px">"{{ $ticket->feedback }}"</div>
                                @endif
                            @else
                                <span style="color:#94a3b8">—</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
