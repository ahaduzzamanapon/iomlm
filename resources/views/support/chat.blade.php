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
                <div style="display:flex;gap:8px">
                    @if($ticket->status !== 'CLOSED')
                        <button type="button" class="btn btn-outline btn-sm" onclick="openModal('transferDeptModal')" style="color:#38bdf8;border-color:#38bdf8">
                            🔄 Pass to Other Dept
                        </button>
                        <form method="POST" action="{{ route('support.tickets.close', $ticket->uuid) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই সাপোর্ট টিকিটটি বন্ধ করতে চান?')" style="display:inline">
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
                            <button type="button" class="btn btn-outline" style="height:44px;color:#f59e0b;border-color:#fde047;background:#fefce8" onclick="openModal('cannedModal')" title="কাস্টম মেসেজ নির্বাচন করুন">
                                ⚡ Quick Reply
                            </button>
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

    {{-- Modal 1: Transfer Department Modal --}}
    <div class="modal-overlay" id="transferDeptModal">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">🔄 Pass Ticket to Other Department</span>
                <button class="modal-close" onclick="closeModal('transferDeptModal')">&times;</button>
            </div>
            <form method="POST" action="{{ route('support.tickets.transfer', $ticket->uuid) }}">
                @csrf
                <div class="modal-body">
                    <div style="font-size:13px;color:#64748b;margin-bottom:12px">
                        বর্তমান ডিপার্টমেন্ট: <strong>{{ $ticket->department->name }}</strong>
                    </div>

                    <div class="form-group">
                        <label>নতুন ডিপার্টমেন্ট নির্বাচন করুন <span class="required">*</span></label>
                        <select name="new_department_id" class="form-control" required>
                            <option value="">-- Select Target Department --</option>
                            @foreach($departments as $d)
                                @if($d->id !== $ticket->department_id)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>স্থানান্তরের কারণ / নোট (ঐচ্ছিক)</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. স্টুডেন্টের সমস্যাটি ভাইদের ভর্তি সংক্রান্ত..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('transferDeptModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background:#0284c7">স্থানান্তর করুন (Transfer)</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal 2: Quick Replies / Canned Messages Modal --}}
    <div class="modal-overlay" id="cannedModal">
        <div class="modal" style="max-width:550px">
            <div class="modal-header">
                <span class="modal-title">⚡ Select Quick Reply (কাস্টম মেসেজ)</span>
                <button class="modal-close" onclick="closeModal('cannedModal')">&times;</button>
            </div>
            <div class="modal-body" style="max-height:380px;overflow-y:auto">
                <div style="display:flex;flex-direction:column;gap:10px">
                    @forelse($cannedMessages as $cm)
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:8px;display:flex;justify-content:space-between;align-items:center">
                            <div>
                                <strong style="color:#0284c7;font-size:14px">{{ $cm->title }}</strong>
                                <div style="font-size:12px;color:#475569;margin-top:2px;white-space:pre-line;max-height:60px;overflow:hidden">{{ $cm->message }}</div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary btn-sm" onclick='insertCannedText(@json($cm->message))'>
                                    Select &amp; Fill
                                </button>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:30px;color:#94a3b8">
                            আপনার কোনো কাস্টম মেসেজ তৈরি করা নেই। <br>
                            <a href="{{ route('support.canned-messages.index') }}" target="_blank" style="color:#0284c7;font-weight:700">এখানে ক্লিক করে কাস্টম মেসেজ যুক্ত করুন →</a>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer" style="display:flex;justify-content:space-between">
                <a href="{{ route('support.canned-messages.index') }}" target="_blank" style="font-size:12px;color:#0284c7;font-weight:700">⚙️ Manage My Quick Replies</a>
                <button type="button" class="btn btn-outline" onclick="closeModal('cannedModal')">Close</button>
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

    function getAgentAttachmentHtml(url, isAgent) {
        if (!url) return '';
        const cleanUrl = url.split('?')[0].split('#')[0];
        const isImg = /\.(jpg|jpeg|png|gif|webp|bmp|jfif)$/i.test(cleanUrl);
        const linkColor = isAgent ? '#ffffff' : '#0284c7';
        if (isImg) {
            return `
                <div style="margin-top:8px">
                    <a href="${url}" target="_blank" title="Click to view full image">
                        <img src="${url}" 
                             style="max-width:260px;max-height:240px;border-radius:8px;border:1px solid rgba(0,0,0,0.15);display:block;object-fit:cover;background:#e2e8f0" 
                             alt="Chat Attachment Image"
                             onerror="this.onerror=null; this.parentNode.innerHTML='<a href=\\'${url}\\' target=\\'_blank\\' style=\\'color:${linkColor};text-decoration:underline;font-weight:600\\'>📄 View Attachment File ↗</a>';">
                    </a>
                </div>
            `;
        }
        return `<div style="margin-top:6px"><a href="${url}" target="_blank" style="color:${linkColor};text-decoration:underline;font-weight:600">📄 View Attachment File ↗</a></div>`;
    }

    let lastAgentMessageFingerprint = '';

    function renderAgentFeed(messages) {
        const fingerprint = messages.map(m => m.id + '_' + m.message + '_' + (m.attachment || '')).join('|');
        if (fingerprint === lastAgentMessageFingerprint) {
            return; // No new changes, do not re-render DOM!
        }
        lastAgentMessageFingerprint = fingerprint;

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
                            ${getAgentAttachmentHtml(m.attachment, true)}
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div style="display:flex;flex-direction:column;align-items:flex-start;margin-bottom:8px">
                        <div style="font-size:11px;color:#64748b;margin-bottom:2px">${m.sender_name} &middot; ${m.time}</div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;padding:10px 14px;border-radius:12px 12px 12px 0;max-width:80%;font-size:14px;line-height:1.4">
                            ${m.message}
                            ${getAgentAttachmentHtml(m.attachment, false)}
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
            const file = input.files[0];
            const isImage = file.type.startsWith('image/');
            p.style.display = 'block';

            if (isImage) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    p.innerHTML = `
                        <div style="display:inline-flex;align-items:center;gap:10px;background:#f1f5f9;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;margin-top:6px">
                            <img src="${e.target.result}" style="width:55px;height:55px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1" alt="Preview">
                            <div>
                                <div style="font-size:12px;color:#0284c7;font-weight:700">📷 ${file.name}</div>
                                <div style="font-size:10px;color:#64748b">${(file.size/1024).toFixed(1)} KB &middot; Ready to send</div>
                            </div>
                            <button type="button" onclick="clearAgentFile()" style="border:none;background:#ef4444;color:#fff;border-radius:50%;width:22px;height:22px;cursor:pointer;margin-left:8px;font-size:13px;font-weight:700" title="Remove file">&times;</button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                p.innerHTML = `
                    <div style="display:inline-flex;align-items:center;gap:10px;background:#f1f5f9;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;margin-top:6px">
                        <span style="font-size:22px">📄</span>
                        <div>
                            <div style="font-size:12px;color:#0284c7;font-weight:700">${file.name}</div>
                            <div style="font-size:10px;color:#64748b">${(file.size/1024).toFixed(1)} KB</div>
                        </div>
                        <button type="button" onclick="clearAgentFile()" style="border:none;background:#ef4444;color:#fff;border-radius:50%;width:22px;height:22px;cursor:pointer;margin-left:8px;font-size:13px;font-weight:700" title="Remove file">&times;</button>
                    </div>
                `;
            }
        } else {
            p.style.display = 'none';
            p.innerHTML = '';
        }
    }

    function clearAgentFile() {
        const input = document.getElementById('agentAttachmentInput');
        if (input) input.value = '';
        previewAgentFile(input);
    }

    function insertCannedText(text) {
        const input = document.getElementById('agentMsgInput');
        if (input) {
            input.value = text;
            input.focus();
        }
        closeModal('cannedModal');
    }

    fetchAgentMessages();
    setInterval(fetchAgentMessages, 3000);
    </script>
    @endpush
</x-support-layout>
