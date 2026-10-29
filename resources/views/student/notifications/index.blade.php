<x-student-layout>
    <x-slot name="title">নোটিফিকেশন ও ক্লাস রিমাইন্ডার</x-slot>

    <div class="page-header" style="font-family:'Kalpurush',sans-serif">
        <div class="page-header-left">
            <h1 style="font-size:22px;color:#064e3b;font-weight:800;display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-bell" style="color:#047857"></i> নোটিফিকেশন ও ক্লাস রিমাইন্ডার
            </h1>
            <p style="color:#64748b;font-size:13px">আপনার ক্লাসের রিমাইন্ডার, নোটিশ ও অ্যাকাডেমিক বার্তার বিস্তারিত তালিকা</p>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn btn-outline btn-sm" onclick="markAllNotificationsRead()" style="border-color:#10b981;color:#047857;font-weight:700">
                <i class="fa-solid fa-check-double"></i> সব পঠিত হিসেবে চিহ্নিত করুন
            </button>
        </div>
    </div>

    <div class="card" style="font-family:'Kalpurush',sans-serif">
        @if($notifications->isEmpty())
            <div style="padding:60px 20px;text-align:center;color:#64748b">
                <div style="width:60px;height:60px;border-radius:50%;background:#ecfdf5;color:#047857;display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 14px">
                    <i class="fa-solid fa-bell-slash"></i>
                </div>
                <div style="font-size:16px;font-weight:700;color:#1e293b">কোনো নোটিফিকেশন পাওয়া যায়নি</div>
                <p style="font-size:13px;color:#94a3b8;margin-top:4px">আপনার জন্য বর্তমানে কোনো নতুন ক্লাস রিমাইন্ডার বা নোটিশ নেই।</p>
            </div>
        @else
            <div style="padding:0">
                @foreach($notifications as $n)
                    @php
                        $user = auth()->user();
                        $isUnread = !$user->isNotificationRead($n->id, $n->created_at);
                    @endphp
                    <div id="notif-row-{{ $n->id }}" style="padding:18px 22px;border-bottom:1px solid #f1f5f9;display:flex;gap:16px;align-items:flex-start;{{ $isUnread ? 'background:#f0fdf4;border-left:4px solid #047857;' : 'background:#ffffff;' }};transition:all 0.2s">
                        <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px;{{ $isUnread ? 'background:#dcfce7;color:#047857;' : 'background:#f1f5f9;color:#94a3b8;' }}">
                            <i class="fa-solid {{ $n->recipient_type === 'CLASS_REMINDER' ? 'fa-calendar-check' : 'fa-bell' }}"></i>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:6px">
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                    <strong style="font-size:15px;color:#0f172a">{{ $n->title }}</strong>
                                    @if($isUnread)
                                        <span class="badge" style="background:#dcfce7;color:#166534;font-size:11px;font-weight:700">নতুন</span>
                                    @endif
                                </div>
                                <span style="font-size:12px;color:#94a3b8">
                                    <i class="fa-regular fa-clock"></i> {{ $n->created_at ? $n->created_at->diffForHumans() : '' }}
                                </span>
                            </div>
                            <div style="font-size:13.5px;color:#334155;line-height:1.65;white-space:pre-line;margin-bottom:10px">
                                {{ $n->message }}
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                                @if($n->action_url)
                                    <a href="{{ $n->action_url }}" class="btn btn-primary btn-sm" style="font-size:12px;padding:5px 14px;font-weight:700">
                                        👉 ক্লাসে প্রবেশ করুন / দেখুন
                                    </a>
                                @endif
                                @if($isUnread)
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="markSingleNotificationRead({{ $n->id }})" style="font-size:12px;color:#64748b">
                                        <i class="fa-solid fa-check"></i> পঠিত হিসেবে চিহ্নিত করুন
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notifications->hasPages())
                <div style="padding:16px 20px;border-top:1px solid #f1f5f9">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

    @push('scripts')
    <script>
    function markSingleNotificationRead(id) {
        fetch('{{ url("student/notifications") }}/' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            const row = document.getElementById('notif-row-' + id);
            if (row) {
                row.style.background = '#ffffff';
                row.style.borderLeftColor = 'transparent';
                const badge = row.querySelector('.badge');
                if (badge) badge.remove();
            }
        });
    }

    function markAllNotificationsRead() {
        fetch('{{ route("student.notifications.read-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            location.reload();
        });
    }
    </script>
    @endpush
</x-student-layout>
