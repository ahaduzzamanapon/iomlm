<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat — Support Ticket #{{ $ticket->ticket_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Hind Siliguri', 'Segoe UI', sans-serif;
            background: #f1f5f9; margin: 0; color: #0f172a;
        }

        .chat-header-bar {
            background: #081726; color: #fff; height: 60px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .chat-header-title { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .chat-header-sub { font-size: 12px; color: #94a3b8; }

        .chat-container {
            max-width: 900px; margin: 24px auto; padding: 0 16px;
            display: flex; flex-direction: column; height: calc(100vh - 110px);
        }

        .chat-box {
            background: #ffffff; border-radius: 12px; border: 1px solid #cbd5e1;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08); display: flex; flex-direction: column;
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
            background: #0284c7; color: #fff; border: none; border-radius: 6px;
            padding: 0 20px; font-weight: 700; height: 44px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-send:hover { background: #0369a1; }

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
                💬 IOM Live Support
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
                ✓ {{ session('success') }}
            </div>
        @endif

        <div class="chat-box">
            {{-- Ticket Status Banner --}}
            <div style="padding:12px 20px;background:#e0f2fe;border-bottom:1px solid #bae6fd;display:flex;justify-content:space-between;align-items:center;font-size:13px">
                <div>
                    <strong>বিষয়:</strong> {{ $ticket->subject }}
                </div>
                <div id="agentStatusBadge">
                    @if($ticket->status === 'CLOSED')
                        <span style="background:#fee2e2;color:#991b1b;padding:4px 10px;border-radius:12px;font-weight:700">🔒 সার্ভিস সম্পন্ন (Closed)</span>
                    @elseif($ticket->assignedAgent)
                        <span style="background:#dcfce7;color:#166534;padding:4px 10px;border-radius:12px;font-weight:700">🟢 প্রতিনিধি {{ $ticket->assignedAgent->name }} সংযুক্ত আছেন</span>
                    @else
                        <span style="background:#fef3c7;color:#b45309;padding:4px 10px;border-radius:12px;font-weight:700">⏳ প্রতিনিধি অপেক্ষায় আছে...</span>
                    @endif
                </div>
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
                        <label style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;padding:0 14px;display:inline-flex;align-items:center;cursor:pointer" title="ফাইল/ছবি যোগ করুন">
                            📷 <input type="file" id="userAttachmentInput" accept="image/*,.pdf" style="display:none" onchange="previewUserFile(this)">
                        </label>
                        <button type="submit" class="btn-send">
                            পাঠান ↗
                        </button>
                    </form>
                    <div id="userFilePreview" style="display:none;font-size:12px;color:#0284c7;font-weight:600;margin-top:6px"></div>
                </div>
            @else
                {{-- Rating Form on Close --}}
                <div class="rating-box">
                    <div style="font-weight:700;font-size:15px;margin-bottom:8px">আপনার অনলাইন সাপোর্ট অভিজ্ঞতাটি কেমন ছিল? (রেটিং দিন)</div>
                    @if($ticket->rating)
                        <div style="color:#f59e0b;font-size:24px;margin-bottom:6px">
                            @for($i=1; $i<=5; $i++)
                                {{ $i <= $ticket->rating ? '★' : '☆' }}
                            @endfor
                        </div>
                        <div style="font-size:13px">আপনার মূল্যায়নের জন্য ধন্যবাদ! ({{ $ticket->rating }}/5)</div>
                    @else
                        <form method="POST" action="{{ route('online-support.rate', $ticket->uuid) }}">
                            @csrf
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" title="5 stars">★</label>
                                <input type="radio" id="star4" name="rating" value="4"/><label for="star4" title="4 stars">★</label>
                                <input type="radio" id="star3" name="rating" value="3"/><label for="star3" title="3 stars">★</label>
                                <input type="radio" id="star2" name="rating" value="2"/><label for="star2" title="2 stars">★</label>
                                <input type="radio" id="star1" name="rating" value="1"/><label for="star1" title="1 star">★</label>
                            </div>
                            <div style="margin-top:10px">
                                <input type="text" name="feedback" placeholder="মতামত লিখুন (ঐচ্ছিক)..." style="width:100%;max-width:400px;height:36px;padding:0 12px;border:1px solid #fde68a;border-radius:6px;font-size:13px">
                            </div>
                            <button type="submit" class="btn-send" style="margin-top:10px;height:36px;font-size:13px">
                                রেটিং জমা দিন
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
    const ticketUuid = @json($ticket->uuid);
    const feed = document.getElementById('userChatFeed');

    function fetchUserMessages() {
        fetch(`/online-support/messages/${ticketUuid}`)
            .then(res => res.json())
            .then(data => {
                renderUserFeed(data.messages);
            })
            .catch(err => console.error(err));
    }

    function renderUserFeed(messages) {
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
            } else if (m.sender_type === 'USER') {
                html += `
                    <div style="display:flex;flex-direction:column;align-items:flex-end;margin-bottom:8px">
                        <div style="font-size:11px;color:#64748b;margin-bottom:2px">You &middot; ${m.time}</div>
                        <div style="background:#0284c7;color:#fff;padding:10px 14px;border-radius:12px 12px 0 12px;max-width:80%;font-size:14px;line-height:1.4">
                            ${m.message}
                            ${m.attachment ? `<div style="margin-top:6px"><a href="${m.attachment}" target="_blank" style="color:#fff;text-decoration:underline">📎 চিত্র/ফাইল দেখুন ↗</a></div>` : ''}
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div style="display:flex;flex-direction:column;align-items:flex-start;margin-bottom:8px">
                        <div style="font-size:11px;color:#64748b;margin-bottom:2px">Support Agent (${m.sender_name}) &middot; ${m.time}</div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;color:#0f172a;padding:10px 14px;border-radius:12px 12px 12px 0;max-width:80%;font-size:14px;line-height:1.4">
                            ${m.message}
                            ${m.attachment ? `<div style="margin-top:6px"><a href="${m.attachment}" target="_blank" style="color:#0284c7;text-decoration:underline">📎 চিত্র/ফাইল দেখুন ↗</a></div>` : ''}
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
            p.style.display = 'block';
            p.innerText = '📎 সংযুক্ত: ' + input.files[0].name;
        } else {
            p.style.display = 'none';
        }
    }

    fetchUserMessages();
    setInterval(fetchUserMessages, 3000);
    </script>
</body>
</html>
