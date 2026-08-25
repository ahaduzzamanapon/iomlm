<x-support-layout>
    <x-slot name="title">Live Chat — {{ $ticket->ticket_no }}</x-slot>

    <div style="display:grid;grid-template-columns: 1fr 340px;gap:20px;align-items:start">

        {{-- Left: Live Chat Window --}}
        <div class="card" style="display:flex;flex-direction:column;height:calc(100vh - 140px)">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;background:#0f172a;color:#fff;border-radius:12px 12px 0 0">
                <div>
                    <div style="font-weight:700;font-size:15px">💬 Live Chat: {{ $ticket->name }}</div>
                    <div style="font-size:11px;color:#94a3b8">Ticket: <strong>{{ $ticket->ticket_no }}</strong> &middot; Dept: {{ $ticket->department->name }}</div>
                </div>
                <div>
                    @if($ticket->status !== 'CLOSED')
                        <form method="POST" action="{{ route('support.tickets.close', $ticket->uuid) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই সাপোর্ট টিকিটটি বন্ধ করতে চান?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" style="background:#e11d48">
                                🔒 Close Ticket
                            </button>
                        </form>
                    @else
                        <span class="badge badge-secondary">🔒 Ticket Closed</span>
                    @endif
                </div>
            </div>

            {{-- Message Feed Box --}}
            <div id="agentChatFeed" style="flex:1;padding:20px;overflow-y:auto;background:#f8fafc;display:flex;flex-direction:column;gap:12px">
                {{-- Messages rendered via JS / Blade --}}
            </div>

            {{-- Agent Reply Bar --}}
            @if($ticket->status !== 'CLOSED')
                <div style="padding:16px;background:#fff;border-top:1px solid #e2e8f0;border-radius:0 0 12px 12px">
                    <form id="agentMessageForm" onsubmit="sendAgentMessage(event)" style="display:flex;flex-direction:column;gap:10px">
                        <div style="display:flex;gap:10px;align-items:center">
                            <input type="text" id="agentMsgInput" class="form-control" placeholder="উত্তর লিখুন..." autocomplete="off" style="flex:1;height:44px;font-size:14px">
                            <label class="btn btn-outline" style="cursor:pointer;height:44px;display:inline-flex;align-items:center" title="Attach file/image">
                                📷 <input type="file" id="agentAttachmentInput" accept="image/*,.pdf" style="display:none" onchange="previewAgentFile(this)">
                            </label>
                            <button type="submit" class="btn btn-primary" style="height:44px;padding:0 24px;background:#0284c7">
                                ✉️ Send Reply
                            </button>
                        </div>
                        <div id="agentFilePreview" style="display:none;font-size:12px;color:#0284c7;font-weight:600"></div>
                    </form>
                </div>
            @else
                <div style="padding:16px;background:#fff1f2;color:#991b1b;border-top:1px solid #fecdd3;text-align:center;font-size:13px;font-weight:600">
                    🔒 এই সাপোর্ট টিকিটটি বন্ধ করা হয়েছে।
                </div>
            @endif
        </div>

        {{-- Right: User Info & Ticket Meta Sidebar --}}
        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="card">
                <div class="card-header" style="background:#f1f5f9">
                    <span class="card-title">👤 User / Student Details</span>
                </div>
                <div class="card-body" style="font-size:13px">
                    <table class="table" style="margin:0">
                        <tr><th style="color:#64748b;width:100px">Name:</th><td><strong>{{ $ticket->name }}</strong></td></tr>
                        <tr><th style="color:#64748b">Phone:</th><td><a href="tel:{{ $ticket->phone }}" style="color:#0284c7;font-weight:700">📞 {{ $ticket->phone }}</a></td></tr>
                        <tr><th style="color:#64748b">Email:</th><td>{{ $ticket->email }}</td></tr>
                        <tr><th style="color:#64748b">Gender:</th><td>{{ $ticket->gender }}</td></tr>
                        <tr><th style="color:#64748b">Student ID:</th><td>{{ $ticket->student_id ?? '—' }}</td></tr>
                        <tr><th style="color:#64748b">Reference:</th><td>{{ $ticket->reference ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background:#f1f5f9">
                    <span class="card-title">📋 Issue Details</span>
                </div>
                <div class="card-body" style="font-size:13px">
                    <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase">Subject:</div>
                    <div style="font-weight:700;color:#0f172a;margin-bottom:10px;font-size:14px">{{ $ticket->subject }}</div>

                    <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase">Problem Details:</div>
                    <div style="background:#f8fafc;padding:10px;border-radius:6px;border:1px solid #e2e8f0;white-space:pre-line;color:#334155;margin-top:4px">
                        {{ $ticket->problem_details }}
                    </div>

                    @if($ticket->rating)
                        <div style="margin-top:14px;padding:12px;background:#fefce8;border:1px solid #fde047;border-radius:8px">
                            <div style="font-weight:700;color:#854d0e;font-size:12px">User Feedback Rating:</div>
                            <div style="color:#eab308;font-size:18px">
                                @for($i=1; $i<=5; $i++)
                                    {{ $i <= $ticket->rating ? '★' : '☆' }}
                                @endfor
                                <span style="font-size:13px;color:#854d0e;font-weight:700">({{ $ticket->rating }}/5)</span>
                            </div>
                            @if($ticket->feedback)
                                <div style="font-size:12px;color:#713f12;margin-top:4px;font-style:italic">"{{ $ticket->feedback }}"</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    const ticketUuid = @json($ticket->uuid);
    const feed = document.getElementById('agentChatFeed');

    function fetchAgentMessages() {
        fetch(`/online-support/messages/${ticketUuid}`)
            .then(res => res.json())
            .then(data => {
                renderAgentFeed(data.messages);
            })
            .catch(err => console.error(err));
    }

    function renderAgentFeed(messages) {
        const isScrolledBottom = feed.scrollHeight - feed.clientHeight <= feed.scrollTop + 50;

        let html = '';
        messages.forEach(m => {
            if (m.sender_type === 'SYSTEM') {
                html += `
                    <div style="text-align:center;margin:8px 0">
                        <span style="background:#e2e8f0;color:#475569;font-size:11px;padding:4px 12px;border-radius:12px;display:inline-block">
                            ⚙️ ${m.message} &middot; ${m.time}
                        </span>
                    </div>
                `;
            } else if (m.sender_type === 'AGENT') {
                html += `
                    <div style="display:flex;flex-direction:column;align-items:flex-end;margin-bottom:8px">
                        <div style="font-size:11px;color:#64748b;margin-bottom:2px">You (${m.sender_name}) &middot; ${m.time}</div>
                        <div style="background:#0284c7;color:#fff;padding:10px 14px;border-radius:12px 12px 0 12px;max-width:80%;font-size:14px;line-height:1.4">
                            ${m.message}
                            ${m.attachment ? `<div style="margin-top:6px"><a href="${m.attachment}" target="_blank" style="color:#fff;text-decoration:underline">📎 Attachment View ↗</a></div>` : ''}
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div style="display:flex;flex-direction:column;align-items:flex-start;margin-bottom:8px">
                        <div style="font-size:11px;color:#64748b;margin-bottom:2px">${m.sender_name} &middot; ${m.time}</div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;padding:10px 14px;border-radius:12px 12px 12px 0;max-width:80%;font-size:14px;line-height:1.4">
                            ${m.message}
                            ${m.attachment ? `<div style="margin-top:6px"><a href="${m.attachment}" target="_blank" style="color:#0284c7;text-decoration:underline">📎 Attachment View ↗</a></div>` : ''}
                        </div>
                    </div>
                `;
            }
        });

        feed.innerHTML = html;
        if (isScrolledBottom) feed.scrollTop = feed.scrollHeight;
    }

    function sendAgentMessage(e) {
        e.preventDefault();
        const input = document.getElementById('agentMsgInput');
        const fileInput = document.getElementById('agentAttachmentInput');
        const msg = input.value.trim();

        if (!msg && !fileInput.files[0]) return;

        const formData = new FormData();
        formData.append('message', msg);
        if (fileInput.files[0]) {
            formData.append('attachment', fileInput.files[0]);
        }

        const csrfToken = '{{ csrf_token() }}';

        fetch(`/support/tickets/${ticketUuid}/message`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            input.value = '';
            fileInput.value = '';
            document.getElementById('agentFilePreview').style.display = 'none';
            fetchAgentMessages();
        });
    }

    function previewAgentFile(input) {
        const p = document.getElementById('agentFilePreview');
        if (input.files && input.files[0]) {
            p.style.display = 'block';
            p.innerText = '📎 Selected: ' + input.files[0].name;
        } else {
            p.style.display = 'none';
        }
    }

    fetchAgentMessages();
    setInterval(fetchAgentMessages, 3000);
    </script>
    @endpush
</x-support-layout>
