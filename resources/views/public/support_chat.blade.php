<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Chat — Ticket #{{ $ticket->ticket_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Kalpurush', 'Hind Siliguri', 'Segoe UI', sans-serif;
            background: #f8fafc; margin: 0; color: #0f172a;
        }

        .chat-header-bar {
            background: #064e3b; color: #fff; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); border-bottom: 2px solid #047857;
        }
        .chat-header-title { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .chat-header-sub { font-size: 12px; color: #a7f3d0; }

        .chat-container {
            max-width: 900px; margin: 24px auto; padding: 0 16px;
            display: flex; flex-direction: column; height: calc(100vh - 110px);
        }

        .chat-box {
            background: #ffffff; border-radius: 12px; border: 1px solid #d1fae5;
            box-shadow: 0 10px 25px rgba(6,78,59,0.06); display: flex; flex-direction: column;
            flex: 1; overflow: hidden;
        }

        .chat-feed {
            flex: 1; padding: 20px; overflow-y: auto; background: #f8fafc;
            display: flex; flex-direction: column; gap: 12px;
        }

        .chat-input-bar {
            padding: 16px; background: #ffffff; border-top: 1px solid #e2e8f0;
        }

        .btn-send {
            background: #047857; color: #fff; border: none; border-radius: 6px;
            padding: 0 20px; font-weight: 700; height: 44px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;
        }
        .btn-send:hover { background: #064e3b; }

        /* Rating Modal / Box */
        .rating-box {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
            padding: 16px; margin-top: 12px; text-align: center; color: #92400e;
        }
        .star-rating { display: inline-flex; gap: 6px; flex-direction: row-reverse; font-size: 24px; cursor: pointer; }
        .star-rating input { display: none; }
        .star-rating label { color: #cbd5e1; transition: color 0.2s; }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #f59e0b; }
    </style>
</head>
<body>

    {{-- Top Bar --}}
    <div class="chat-header-bar">
        <div>
            <div class="chat-header-title">
                <i class="fa-solid fa-headset" style="margin-right:6px"></i> আইওএম অনলাইন সাপোর্ট (IOM Support)
            </div>
            <div class="chat-header-sub">
                Ticket #{{ $ticket->ticket_no }} &middot; Dept: {{ $ticket->department->name }}
            </div>
        </div>
        <div>
            <a href="{{ route('online-support.index') }}" style="color:#94a3b8;text-decoration:none;font-size:13px;font-weight:600">
                ← ফিরে যান
            </a>
        </div>
    </div>

    <div class="chat-container">
        @if(session('success'))
            <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px;border-radius:6px;margin-bottom:12px;font-size:13px;font-weight:600">
                {{ session('success') }}
            </div>
        @endif

        <div class="chat-box">
            {{-- Ticket Status Banner --}}
            <div style="padding:12px 20px;background:#ecfdf5;border-bottom:1px solid #d1fae5;display:flex;justify-content:space-between;align-items:center;font-size:13px">
                <div>
                    <strong>বিষয়:</strong> {{ $ticket->subject }}
                </div>
                <div id="agentStatusBadge">
                    @if($ticket->status === 'CLOSED')
                        <span style="background:#fee2e2;color:#991b1b;padding:4px 10px;border-radius:12px;font-weight:700">সার্ভিস সম্পন্ন (Closed)</span>
                    @elseif($ticket->status === 'SOLVED')
                        <span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:12px;font-weight:700">✓ সমস্যার সমাধান প্রদান করা হয়েছে (Solved)</span>
                    @elseif($ticket->assignedAgent)
                        <span style="background:#dbeafe;color:#1d4ed8;padding:4px 10px;border-radius:12px;font-weight:700">প্রতিনিধি {{ $ticket->assignedAgent->name }} সংযুক্ত আছেন</span>
                    @else
                        <span style="background:#fef3c7;color:#b45309;padding:4px 10px;border-radius:12px;font-weight:700">⏳ প্রতিনিধি অপেক্ষায় আছে...</span>
                    @endif
                </div>
            </div>

            {{-- Instructive Notice Banner --}}
            <div style="background:#eff6ff;border-bottom:1px solid #bfdbfe;color:#1e40af;padding:12px 20px;display:flex;align-items:center;gap:12px;font-size:14px;font-weight:600">
                <i class="fa-solid fa-clock-rotate-left" style="font-size:18px;color:#2563eb;flex-shrink:0"></i>
                <span>আপনার সমস্যার সমাধান করে ২৪ ঘণ্টার মধ্যে রিপ্লাই দেওয়া হবে। অনুগ্রহ করে অপেক্ষা করুন।</span>
            </div>

            {{-- Chat Messages Feed --}}
            <div class="chat-feed" id="userChatFeed">
                {{-- Dynamic via JS --}}
            </div>

            {{-- Input Bar --}}
            @if($ticket->status !== 'CLOSED')
                <div class="chat-input-bar">
                    <form id="userMessageForm" onsubmit="sendUserMessage(event)" style="display:flex;gap:10px">
                        <input type="text" id="userMsgInput" class="form-control" placeholder="আপনার মেসেজ লিখুন..." autocomplete="off" style="flex:1;height:44px;font-size:14px;border:1px solid #cbd5e1;border-radius:6px;padding:0 14px">
                        <label style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;padding:0 14px;display:inline-flex;align-items:center;cursor:pointer;gap:6px" title="ফাইল/ছবি যোগ করুন">
                            <i class="fa-solid fa-paperclip" style="font-size:16px;color:#64748b"></i>
                            <input type="file" id="userAttachmentInput" accept="image/*,.pdf" style="display:none" onchange="previewUserFile(this)">
                        </label>
                        <button type="submit" class="btn-send">
                            পাঠান ↗
                        </button>
                    </form>
                    <div id="userFilePreview" style="display:none;font-size:12px;color:#0284c7;font-weight:600;margin-top:6px"></div>
                </div>
            @else
                {{-- Rating Form on Close --}}
                <div class="rating-box" style="font-family:'Kalpurush',sans-serif">
                    <div style="font-weight:700;font-size:16px;margin-bottom:8px">আপনার অনলাইন সাপোর্ট অভিজ্ঞতাটি কেমন ছিল? (রেটিং ও মতামত দিন)</div>
                    @if($ticket->rating)
                        <div style="color:#f59e0b;font-size:24px;margin-bottom:6px">
                            @for($i=1; $i<=5; $i++)
                                <i class="fa-{{ $i <= $ticket->rating ? 'solid' : 'regular' }} fa-star"></i>
                            @endfor
                        </div>
                        <div style="font-size:14px;color:#334155;font-weight:600">আপনার মূল্যায়নের জন্য ধন্যবাদ! ({{ $ticket->rating }}/5)</div>
                        @if($ticket->feedback)
                            <div style="font-size:13px;color:#475569;margin-top:6px;font-style:italic">"{{ $ticket->feedback }}"</div>
                        @endif
                    @else
                        <form method="POST" action="{{ route('online-support.rate', $ticket->uuid) }}" style="display:flex;flex-direction:column;align-items:center;gap:12px">
                            @csrf
                            <div class="star-rating" style="display:inline-flex;gap:8px;font-size:28px;cursor:pointer">
                                <span class="star-item" data-value="1" onclick="setRating(1)" style="color:#f59e0b" title="1 Star"><i class="fa-solid fa-star"></i></span>
                                <span class="star-item" data-value="2" onclick="setRating(2)" style="color:#f59e0b" title="2 Stars"><i class="fa-solid fa-star"></i></span>
                                <span class="star-item" data-value="3" onclick="setRating(3)" style="color:#f59e0b" title="3 Stars"><i class="fa-solid fa-star"></i></span>
                                <span class="star-item" data-value="4" onclick="setRating(4)" style="color:#f59e0b" title="4 Stars"><i class="fa-solid fa-star"></i></span>
                                <span class="star-item" data-value="5" onclick="setRating(5)" style="color:#f59e0b" title="5 Stars"><i class="fa-solid fa-star"></i></span>
                            </div>
                            <input type="hidden" name="rating" id="ratingScoreInput" value="5">
                            <div style="width:100%;max-width:440px">
                                <input type="text" name="feedback" placeholder="আপনার মূল্যবান মতামত লিখুন (ঐচ্ছিক)..." style="width:100%;height:40px;padding:0 14px;border:1px solid #fde68a;border-radius:8px;font-size:14px;font-family:'Kalpurush',sans-serif">
                            </div>
                            <button type="submit" class="btn-send" style="height:40px;font-size:14px;padding:0 24px;background:#047857;color:#fff;border-radius:8px;font-family:'Kalpurush',sans-serif">
                                <i class="fa-solid fa-paper-plane" style="margin-right:4px"></i> রেটিং ও ফিডব্যাক জমা দিন
                            </button>
                        </form>
                        <script>
                        function setRating(val) {
                            document.getElementById('ratingScoreInput').value = val;
                            const items = document.querySelectorAll('.star-item');
                            items.forEach((item, index) => {
                                if (index < val) {
                                    item.style.color = '#f59e0b';
                                } else {
                                    item.style.color = '#cbd5e1';
                                }
                            });
                        }
                        </script>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
    const ticketUuid = @json($ticket->uuid);
    const feed = document.getElementById('userChatFeed');
    let currentTicketStatus = @json($ticket->status);

    function fetchUserMessages() {
        fetch(`/online-support/messages/${ticketUuid}`)
            .then(res => res.json())
            .then(data => {
                updateBadgeAndInput(data);
                renderUserFeed(data.messages);
            })
            .catch(err => console.error(err));
    }

    function updateBadgeAndInput(data) {
        const badge = document.getElementById('agentStatusBadge');
        if (!badge) return;

        if (data.status === 'CLOSED') {
            badge.innerHTML = `<span style="background:#fee2e2;color:#991b1b;padding:4px 10px;border-radius:12px;font-weight:700">সার্ভিস সম্পন্ন (Closed)</span>`;
            if (currentTicketStatus !== 'CLOSED') {
                currentTicketStatus = 'CLOSED';
                setTimeout(() => location.reload(), 800);
            }
        } else if (data.status === 'SOLVED') {
            badge.innerHTML = `<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:12px;font-weight:700">✓ সমস্যার সমাধান প্রদান করা হয়েছে (Solved)</span>`;
        } else if (data.agent_name && data.agent_name !== 'অপেক্ষা করা হচ্ছে...') {
            badge.innerHTML = `<span style="background:#dbeafe;color:#1d4ed8;padding:4px 10px;border-radius:12px;font-weight:700">প্রতিনিধি ${data.agent_name} সংযুক্ত আছেন</span>`;
        } else {
            badge.innerHTML = `<span style="background:#fef3c7;color:#b45309;padding:4px 10px;border-radius:12px;font-weight:700">⏳ প্রতিনিধি অপেক্ষায় আছে...</span>`;
        }
    }

    function getAttachmentHtml(url, isUser) {
        if (!url) return '';
        const cleanUrl = url.split('?')[0].split('#')[0];
        const isImg = /\.(jpg|jpeg|png|gif|webp|bmp|jfif)$/i.test(cleanUrl);
        const linkColor = isUser ? '#ffffff' : '#047857';
        if (isImg) {
            return `
                <div style="margin-top:8px">
                    <a href="${url}" target="_blank" title="ক্লিক করে বড় ছবি দেখুন">
                        <img src="${url}" 
                             style="max-width:260px;max-height:240px;border-radius:8px;border:1px solid rgba(0,0,0,0.15);display:block;object-fit:cover;background:#e2e8f0" 
                             alt="Chat Image Attachment"
                             onerror="this.onerror=null; this.parentNode.innerHTML='<a href=\\'${url}\\' target=\\'_blank\\' style=\\'color:${linkColor};text-decoration:underline;font-weight:600\\'>চিত্র/ফাইল দেখুন ↗</a>';">
                    </a>
                </div>
            `;
        }
        return `<div style="margin-top:6px"><a href="${url}" target="_blank" style="color:${linkColor};text-decoration:underline;font-weight:600">ডকুমেন্ট/ফাইল দেখুন ↗</a></div>`;
    }

    let lastUserMessageFingerprint = '';

    function renderUserFeed(messages) {
        const fingerprint = messages.map(m => m.id + '_' + m.message + '_' + (m.attachment || '')).join('|');
        if (fingerprint === lastUserMessageFingerprint) {
            return; // No new changes, do not re-render DOM!
        }
        lastUserMessageFingerprint = fingerprint;

        const isScrolledBottom = feed.scrollHeight - feed.clientHeight <= feed.scrollTop + 50;

        let html = '';
        messages.forEach(m => {
            if (m.sender_type === 'SYSTEM') {
                html += `
                    <div style="text-align:center;margin:8px 0">
                        <span style="background:#e2e8f0;color:#475569;font-size:11px;padding:4px 12px;border-radius:12px;display:inline-block">
                            ${m.message} &middot; ${m.time}
                        </span>
                    </div>
                `;
            } else if (m.sender_type === 'USER') {
                html += `
                    <div style="display:flex;flex-direction:column;align-items:flex-end;margin-bottom:8px">
                        <div style="font-size:11px;color:#64748b;margin-bottom:2px">You &middot; ${m.time}</div>
                        <div style="background:#047857;color:#fff;padding:10px 14px;border-radius:12px 12px 0 12px;max-width:80%;font-size:14px;line-height:1.4">
                            ${m.message}
                            ${getAttachmentHtml(m.attachment, true)}
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div style="display:flex;flex-direction:column;align-items:flex-start;margin-bottom:8px">
                        <div style="font-size:11px;color:#64748b;margin-bottom:2px">Support Agent (${m.sender_name}) &middot; ${m.time}</div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;padding:10px 14px;border-radius:12px 12px 12px 0;max-width:80%;font-size:14px;line-height:1.4">
                            ${m.message}
                            ${getAttachmentHtml(m.attachment, false)}
                        </div>
                    </div>
                `;
            }
        });

        feed.innerHTML = html;
        if (isScrolledBottom) feed.scrollTop = feed.scrollHeight;
    }

    function sendUserMessage(e) {
        e.preventDefault();
        const input = document.getElementById('userMsgInput');
        const fileInput = document.getElementById('userAttachmentInput');
        const msg = input.value.trim();

        if (!msg && !fileInput.files[0]) return;

        const formData = new FormData();
        formData.append('message', msg);
        if (fileInput.files[0]) {
            formData.append('attachment', fileInput.files[0]);
        }

        const csrfToken = '{{ csrf_token() }}';

        fetch(`/online-support/chat/${ticketUuid}/message`, {
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
            document.getElementById('userFilePreview').style.display = 'none';
            fetchUserMessages();
        });
    }

    function previewUserFile(input) {
        const p = document.getElementById('userFilePreview');
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
                                <div style="font-size:12px;color:#0284c7;font-weight:700">${file.name}</div>
                                <div style="font-size:10px;color:#64748b">${(file.size/1024).toFixed(1)} KB &middot; ছবি রেডি আছে</div>
                            </div>
                            <button type="button" onclick="clearUserFile()" style="border:none;background:#ef4444;color:#fff;border-radius:50%;width:22px;height:22px;cursor:pointer;margin-left:8px;font-size:13px;font-weight:700" title="ফাইল মুছুন">&times;</button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                p.innerHTML = `
                    <div style="display:inline-flex;align-items:center;gap:10px;background:#f1f5f9;padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;margin-top:6px">
                        <span style="font-size:22px"><i class="fa-solid fa-file-pdf" style="color:#ef4444"></i></span>
                        <div>
                            <div style="font-size:12px;color:#0284c7;font-weight:700">${file.name}</div>
                            <div style="font-size:10px;color:#64748b">${(file.size/1024).toFixed(1)} KB</div>
                        </div>
                        <button type="button" onclick="clearUserFile()" style="border:none;background:#ef4444;color:#fff;border-radius:50%;width:22px;height:22px;cursor:pointer;margin-left:8px;font-size:13px;font-weight:700" title="ফাইল মুছুন">&times;</button>
                    </div>
                `;
            }
        } else {
            p.style.display = 'none';
            p.innerHTML = '';
        }
    }

    function clearUserFile() {
        const input = document.getElementById('userAttachmentInput');
        if (input) input.value = '';
        previewUserFile(input);
    }

    fetchUserMessages();
    setInterval(fetchUserMessages, 3000);
    </script>
</body>
</html>
