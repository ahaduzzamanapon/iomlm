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
                            <tr>
                                <td>
                                    <div style="font-weight:600">{{ $n->created_at->format('M d, Y') }}</div>
                                    <div style="font-size:12px;color:#64748b">{{ $n->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:700;color:#1e293b;margin-bottom:3px">{{ $n->title }}</div>
                                    <div style="font-size:13px;color:#475569;max-width:350px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
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
                                    @if($n->channel === 'BOTH')
                                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe">
                                            PUSH + EMAIL
                                        </span>
                                    @elseif($n->channel === 'PUSH')
                                        <span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a">
                                            PUSH ONLY
                                        </span>
                                    @else
                                        <span class="badge" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                                            EMAIL ONLY
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9;color:#334155">
                                        {{ str_replace('_', ' ', $n->recipient_type) }}
                                    </span>
                                    <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ $n->sent_count }} জন প্রাপক</div>
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
                                    <div style="font-size:13px;font-weight:600">{{ $n->sender->name ?? 'System' }}</div>
                                </td>
                                <td style="text-align:right">
                                    <button type="button" class="btn btn-sm btn-outline" style="color:#0284c7;border-color:#bae6fd;" onclick="viewNotificationDetails({{ $n->id }})">
                                        <i class="fa-solid fa-eye"></i> সম্পূর্ণ দেখুন
                                    </button>
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

                {{-- Action URL button if exists --}}
                <div id="vn_action_container" style="display:none;margin-top:4px;">
                    <a id="vn_action_btn" href="#" target="_blank" class="btn btn-sm btn-outline" style="display:inline-flex;align-items:center;gap:6px;color:#0284c7;border-color:#0284c7;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> অ্যাকশন লিংক খুলুন (Open Action URL)
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="closeModal('viewNotificationModal')">বন্ধ করুন</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function viewNotificationDetails(id) {
        fetch('/admin/notifications/' + id + '/json')
            .then(res => res.json())
            .then(n => {
                document.getElementById('vn_title').textContent = n.title;
                document.getElementById('vn_message').textContent = n.message;
                document.getElementById('vn_meta').textContent = `প্রেরক: ${n.sender_name} | প্রাপক: ${n.sent_count} জন (${n.recipient_type}) | তারিখ: ${n.created_at}`;

                // Channel badge
                const ch = document.getElementById('vn_channel');
                ch.textContent = n.channel;
                ch.style.background = n.channel === 'BOTH' ? '#eff6ff' : (n.channel === 'PUSH' ? '#fef3c7' : '#f0fdf4');
                ch.style.color = n.channel === 'BOTH' ? '#1d4ed8' : (n.channel === 'PUSH' ? '#b45309' : '#15803d');

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

                openModal('viewNotificationModal');
            })
            .catch(err => {
                console.error(err);
                alert('নোটিফিকেশন তথ্য লোড করতে সমস্যা হয়েছে।');
            });
    }
    </script>
    @endpush
    </div>
</x-admin-layout>
