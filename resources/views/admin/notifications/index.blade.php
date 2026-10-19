<x-admin-layout>
    <x-slot name="title">Notification Broadcast History</x-slot>

    <div class="page-header">
        <div class="page-header-left">
            <h1>Notification Broadcast Center</h1>
            <p>Send and manage Push Notifications &amp; Email broadcasts to students and faculty</p>
        </div>
        <div>
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-lg">
                Send New Notification
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <span class="card-title">Sent Notifications History</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>তারিখ ও সময়</th>
                            <th>শিরোনাম ও বার্তা</th>
                            <th>মাধ্যম (Channel)</th>
                            <th>প্রাপক (Audience)</th>
                            <th>স্ট্যাটাস (Status)</th>
                            <th>প্রেরক</th>
                            <th style="text-align:right">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $n)
                            @php
                                $isReadRow = auth()->user()?->isNotificationRead($n->id, $n->created_at);
                            @endphp
                            <tr id="table-notif-row-{{ $n->id }}" class="table-notif-row {{ $isReadRow ? 'row-read' : 'row-unread' }}"
                                style="transition:all .2s;{{ $isReadRow ? 'background:#f8fafc;opacity:0.82;' : 'background:#ffffff;' }}">
                                <td>
                                    <div style="font-weight:600;{{ $isReadRow ? 'color:#64748b;' : 'color:#1e293b;' }}">{{ $n->created_at->format('M d, Y') }}</div>
                                    <div style="font-size:12px;color:#94a3b8">{{ $n->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                                        <span class="table-notif-title" style="font-size:13.5px;{{ $isReadRow ? 'font-weight:500;color:#64748b;' : 'font-weight:700;color:#0f172a;' }}">
                                            {{ $n->title }}
                                        </span>
                                        @if(!$isReadRow)
                                            <span class="table-notif-badge badge" style="background:#dcfce7;color:#166534;font-size:9.5px;padding:1px 5px;font-weight:700">নতুন</span>
                                        @endif
                                    </div>
                                    <div style="font-size:13px;max-width:350px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;{{ $isReadRow ? 'color:#94a3b8;' : 'color:#475569;' }}">
                                        {{ Str::limit(strip_tags($n->message), 80) }}
                                    </div>
                                    @if($n->image_url)
                                        <div style="margin-top:4px">
                                            <a href="{{ $n->image_url }}" target="_blank" style="font-size:11px;color:#2563eb;font-weight:600">
                                                <i class="fa-solid fa-image"></i> ছবি ব্যানার
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($n->channel === 'ALL')
                                        <span class="badge" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-weight:700;">
                                            <i class="fa-solid fa-layer-group"></i> ALL (PUSH+EMAIL+SMS)
                                        </span>
                                    @elseif($n->channel === 'SMS')
                                        <span class="badge" style="background:#fdf4ff;color:#a21caf;border:1px solid #f0abfc;font-weight:700;">
                                            <i class="fa-solid fa-comment-sms"></i> SMS ONLY
                                        </span>
                                    @elseif($n->channel === 'PUSH_SMS')
                                        <span class="badge" style="background:#faf5ff;color:#7e22ce;border:1px solid #e9d5ff;font-weight:700;">
                                            <i class="fa-solid fa-mobile-screen"></i> PUSH + SMS
                                        </span>
                                    @elseif($n->channel === 'EMAIL_SMS')
                                        <span class="badge" style="background:#ecfeff;color:#0e7490;border:1px solid #a5f3fc;font-weight:700;">
                                            <i class="fa-solid fa-envelope-circle-check"></i> EMAIL + SMS
                                        </span>
                                    @elseif($n->channel === 'BOTH')
                                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:700;">
                                            <i class="fa-solid fa-bell"></i> PUSH + EMAIL
                                        </span>
                                    @elseif($n->channel === 'PUSH')
                                        <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-weight:700;">
                                            <i class="fa-solid fa-bell"></i> PUSH ONLY
                                        </span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-weight:700;">
                                            <i class="fa-solid fa-envelope"></i> EMAIL ONLY
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9;color:#334155">
                                        {{ str_replace('_', ' ', $n->recipient_type) }}
                                    </span>
                                    <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ $n->sent_count }} জন প্রাপক</div>
                                    @if($n->sms_count > 0 || in_array($n->channel, ['ALL', 'SMS', 'PUSH_SMS', 'EMAIL_SMS']))
                                        <div style="font-size:10.5px;color:#a21caf;margin-top:2px;font-weight:600;">
                                            <i class="fa-solid fa-comment-sms"></i> SMS: {{ $n->sms_count ?? 0 }} টি
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($n->status === 'SCHEDULED')
                                        <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">
                                            ⏰ শিডিউল্ড
                                        </span>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                            {{ $n->scheduled_at ? $n->scheduled_at->format('d M, h:i A') : '—' }}
                                        </div>
                                    @else
                                        <span class="badge" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
                                            ✓ প্রেরিত (SENT)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size:13px;{{ $isReadRow ? 'color:#64748b;font-weight:500;' : 'color:#1e293b;font-weight:600;' }}">{{ $n->sender->name ?? 'System' }}</div>
                                </td>
                                <td style="text-align:right">
                                    <div style="display:inline-flex;align-items:center;gap:6px">
                                        @if(!$isReadRow)
                                            <button type="button" class="btn btn-sm btn-outline btn-mark-table-read"
                                                    style="color:#047857;border-color:#a7f3d0;background:#ffffff;font-size:11px;padding:3px 8px;font-weight:700"
                                                    onclick="markSingleNotificationAsRead(event, {{ $n->id }}, this)"
                                                    title="পঠিত হিসেবে চিহ্নিত করুন">
                                                <i class="fa-solid fa-check"></i> রিড
                                            </button>
                                        @else
                                            <span style="font-size:11px;color:#94a3b8;font-weight:600"><i class="fa-solid fa-check-double"></i> পঠিত</span>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline" style="color:#0284c7;border-color:#bae6fd;" onclick="viewNotificationDetails({{ $n->id }})">
                                            <i class="fa-solid fa-eye"></i> বিস্তারিত
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px;color:#94a3b8">
                                    কোনো ব্রডকাস্ট নোটিফিকেশন পাওয়া যায়নি।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notifications->hasPages())
                <div style="padding:16px">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- View Notification Details Modal --}}
    <div class="modal-overlay" id="viewNotificationModal">
        <div class="modal" style="max-width:620px;font-family:'Kalpurush',sans-serif">
            <div class="modal-header">
                <span class="modal-title" style="color:#0284c7;display:flex;align-items:center;gap:8px">
                    <i class="fa-solid fa-bullhorn"></i> ব্রডকাস্ট নোটিফিকেশন সম্পূর্ণ বিবরণ
                </span>
                <button class="modal-close" onclick="closeModal('viewNotificationModal')">&times;</button>
            </div>
            <div class="modal-body" style="display:flex;flex-direction:column;gap:14px">
                <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span id="vn_channel" class="badge" style="font-size:11px;"></span>
                        <span id="vn_status" class="badge" style="font-size:11px;"></span>
                    </div>
                    <h3 id="vn_title" style="font-size:16px;font-weight:700;color:#0f172a;margin:4px 0 6px;"></h3>
                    <div style="font-size:12px;color:#64748b" id="vn_meta"></div>
                    
                    {{-- Delivery Statistics Breakdown --}}
                    <div id="vn_delivery_stats" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;padding-top:8px;border-top:1px dashed #cbd5e1;font-size:12px;">
                        <span id="vn_stat_push" class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;">
                            <i class="fa-solid fa-bell"></i> পুশ ডেলিভারি: <b id="vn_val_push">0</b>
                        </span>
                        <span id="vn_stat_email" class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;">
                            <i class="fa-solid fa-envelope"></i> ইমেইল ডেলিভারি: <b id="vn_val_email">0</b>
                        </span>
                        <span id="vn_stat_sms" class="badge" style="background:#fdf4ff;color:#a21caf;border:1px solid #f0abfc;">
                            <i class="fa-solid fa-comment-sms"></i> এসএমএস ডেলিভারি: <b id="vn_val_sms">0</b>
                        </span>
                    </div>
                </div>

                {{-- Banner image if available --}}
                <div id="vn_image_container" style="display:none;text-align:center;background:#000;border-radius:8px;overflow:hidden;max-height:260px;">
                    <img id="vn_image" src="" alt="Banner" style="max-width:100%;max-height:260px;object-fit:contain;">
                </div>

                {{-- Full Message Text --}}
                <div>
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">নোটিফিকেশন বার্তা (Message Body):</label>
                    <div id="vn_message" style="background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:14px;font-size:14px;color:#1e293b;line-height:1.6;white-space:pre-wrap;max-height:280px;overflow-y:auto;"></div>
                </div>

                {{-- Dedicated SMS Message Box if applicable --}}
                <div id="vn_sms_container" style="display:none;background:#fdf4ff;border:1px solid #f0abfc;border-radius:8px;padding:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label style="font-weight:700;font-size:13px;color:#86198f;display:flex;align-items:center;gap:6px;margin-bottom:0;">
                            <i class="fa-solid fa-comment-sms"></i> প্রেরিত মোবাইল এসএমএস বার্তা (SMS Message):
                        </label>
                        <span id="vn_sms_count_badge" class="badge" style="background:#fae8ff;color:#86198f;font-size:11px;"></span>
                    </div>
                    <div id="vn_sms_message" style="background:#ffffff;border:1px solid #f5d0fe;border-radius:6px;padding:10px 12px;font-size:13px;color:#1e293b;line-height:1.6;white-space:pre-wrap;"></div>
                </div>

                {{-- Action URL button if exists --}}
                <div id="vn_action_container" style="display:none;margin-top:4px;">
                    <a id="vn_action_btn" href="#" target="_blank" class="btn btn-sm btn-outline" style="display:inline-flex;align-items:center;gap:6px;color:#0284c7;border-color:#0284c7;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> অ্যাকশন লিংক খুলুন (Open Action URL)
                    </a>
                </div>
            </div>
            <div class="modal-footer" style="display:flex;justify-content:space-between;align-items:center">
                <div>
                    <button type="button" id="btn_modal_mark_read" class="btn btn-sm btn-outline" style="color:#047857;border-color:#a7f3d0;display:none" onclick="markCurrentModalNotifAsRead()">
                        <i class="fa-solid fa-check"></i> পঠিত হিসেবে চিহ্নিত করুন
                    </button>
                    <span id="modal_already_read_badge" style="display:none;font-size:12px;color:#94a3b8;font-weight:600">
                        <i class="fa-solid fa-check-double"></i> ইতোমধ্যে পঠিত
                    </span>
                </div>
                <button type="button" class="btn btn-primary" onclick="closeModal('viewNotificationModal')">বন্ধ করুন</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    let currentModalNotifId = null;

    function viewNotificationDetails(id) {
        currentModalNotifId = id;
        fetch('/admin/notifications/' + id + '/json')
            .then(res => res.json())
            .then(n => {
                document.getElementById('vn_title').textContent = n.title;
                document.getElementById('vn_message').textContent = n.message;
                document.getElementById('vn_meta').textContent = `প্রেরক: ${n.sender_name} | প্রাপক: ${n.sent_count} জন (${n.recipient_type}) | তারিখ: ${n.created_at}`;

                // Delivery Stats values
                document.getElementById('vn_val_push').textContent = n.push_count || 0;
                document.getElementById('vn_val_email').textContent = n.email_count || 0;
                document.getElementById('vn_val_sms').textContent = n.sms_count || 0;

                // Channel badge styling
                const ch = document.getElementById('vn_channel');
                ch.textContent = n.channel;
                if (n.channel === 'ALL') {
                    ch.style.background = '#f0fdf4';
                    ch.style.color = '#15803d';
                    ch.style.border = '1px solid #bbf7d0';
                } else if (n.channel === 'SMS') {
                    ch.style.background = '#fdf4ff';
                    ch.style.color = '#a21caf';
                    ch.style.border = '1px solid #f0abfc';
                } else if (n.channel === 'PUSH_SMS') {
                    ch.style.background = '#faf5ff';
                    ch.style.color = '#7e22ce';
                    ch.style.border = '1px solid #e9d5ff';
                } else if (n.channel === 'EMAIL_SMS') {
                    ch.style.background = '#ecfeff';
                    ch.style.color = '#0e7490';
                    ch.style.border = '1px solid #a5f3fc';
                } else if (n.channel === 'BOTH') {
                    ch.style.background = '#eff6ff';
                    ch.style.color = '#1d4ed8';
                    ch.style.border = '1px solid #bfdbfe';
                } else if (n.channel === 'PUSH') {
                    ch.style.background = '#fef3c7';
                    ch.style.color = '#b45309';
                    ch.style.border = '1px solid #fde68a';
                } else {
                    ch.style.background = '#f1f5f9';
                    ch.style.color = '#334155';
                    ch.style.border = '1px solid #cbd5e1';
                }

                // Dedicated SMS container in modal
                const smsContainer = document.getElementById('vn_sms_container');
                const smsMessageBox = document.getElementById('vn_sms_message');
                const smsBadge = document.getElementById('vn_sms_count_badge');
                const hasSms = ['ALL', 'SMS', 'PUSH_SMS', 'EMAIL_SMS'].includes(n.channel) || (n.sms_count && n.sms_count > 0) || n.sms_message;

                if (hasSms) {
                    const smsText = n.sms_message || n.message.replace(/<[^>]*>/g, '').trim();
                    smsMessageBox.textContent = smsText;
                    smsBadge.textContent = `${n.sms_count || 0} টি এসএমএস প্রেরিত`;
                    smsContainer.style.display = 'block';
                } else {
                    smsContainer.style.display = 'none';
                }

                // Status badge
                const st = document.getElementById('vn_status');
                if (n.status === 'SCHEDULED') {
                    st.textContent = `⏰ শিডিউল্ড: ${n.scheduled_at || 'শীঘ্রই'}`;
                    st.style.background = '#fef3c7';
                    st.style.color = '#92400e';
                } else {
                    st.textContent = '✓ প্রেরিত (SENT)';
                    st.style.background = '#dcfce7';
                    st.style.color = '#166534';
                }

                // Image Banner
                const imgContainer = document.getElementById('vn_image_container');
                const img = document.getElementById('vn_image');
                if (n.image_url) {
                    img.src = n.image_url;
                    imgContainer.style.display = 'block';
                } else {
                    imgContainer.style.display = 'none';
                }

                // Action Link
                const actContainer = document.getElementById('vn_action_container');
                const actBtn = document.getElementById('vn_action_btn');
                if (n.action_url) {
                    actBtn.href = n.action_url;
                    actContainer.style.display = 'block';
                } else {
                    actContainer.style.display = 'none';
                }

                // Read / Unread in modal
                const btnMark = document.getElementById('btn_modal_mark_read');
                const badgeRead = document.getElementById('modal_already_read_badge');
                if (n.is_read) {
                    if (btnMark) btnMark.style.display = 'none';
                    if (badgeRead) badgeRead.style.display = 'inline-flex';
                } else {
                    if (btnMark) {
                        btnMark.style.display = 'inline-flex';
                        btnMark.disabled = false;
                        btnMark.innerHTML = '<i class="fa-solid fa-check"></i> পঠিত হিসেবে চিহ্নিত করুন';
                    }
                    if (badgeRead) badgeRead.style.display = 'none';
                }

                openModal('viewNotificationModal');
            })
            .catch(err => {
                console.error(err);
                alert('নোটিফিকেশন তথ্য লোড করতে সমস্যা হয়েছে।');
            });
    }

    function markCurrentModalNotifAsRead() {
        if (!currentModalNotifId) return;
        const btn = document.getElementById('btn_modal_mark_read');
        if (typeof markSingleNotificationAsRead === 'function') {
            markSingleNotificationAsRead(null, currentModalNotifId, btn);
            btn.style.display = 'none';
            const badgeRead = document.getElementById('modal_already_read_badge');
            if (badgeRead) badgeRead.style.display = 'inline-flex';
        }
    }
    </script>
    @endpush
    </div>
</x-admin-layout>
